<?php

namespace portalium\user\components;

use portalium\base\Event;
use portalium\user\models\User;
use portalium\user\models\UserGroup;
use portalium\user\Module;
use Yii;
use yii\db\Connection;
use yii\db\IntegrityException;

/**
 * Deletes a user, along with their data across modules, inside a single transaction.
 *
 * Records belonging to modules listed in `$deleteModules` are deleted; records in every other
 * module are transferred to `$defaultUser`. If any module fails, nothing changes and
 * UserDeleteException is thrown.
 */
class UserDeleter
{
    /**
     * Deletion order: modules whose records are referenced by other modules go last
     * (app_flow and console_package → flow, storage → data, everything → workspace).
     */
    private const ORDER = [
        'app' => 10,
        'flow' => 20,
        'storage' => 30,
        'workspace' => 40,
    ];

    /** @var callable[]|null jobs to run after commit while a delete is in progress; null = no delete running */
    private static ?array $afterCommit = null;

    public static function delete(int $idUser, array $deleteModules = [], ?int $defaultUser = null): void
    {
        $user = User::findOne($idUser);
        if ($user === null) {
            throw new UserDeleteException(Module::t('The requested page does not exist.'));
        }
        if (Yii::$app->has('user') && !Yii::$app->user->isGuest && (int) Yii::$app->user->id === $idUser) {
            throw new UserDeleteException(Module::t('You cannot delete your own account.'));
        }
        self::assertNotLastAdmin($idUser);
        if ($defaultUser === $idUser) {
            throw new UserDeleteException(Module::t('Records cannot be transferred to the user being deleted.'));
        }
        if ($defaultUser !== null && !User::find()->where(['id_user' => $defaultUser, 'status' => User::STATUS_ACTIVE])->exists()) {
            throw new UserDeleteException(Module::t('The user to transfer records to was not found.'));
        }

        self::$afterCommit = [];
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach (self::handlerModules() as $id => $module) {
                $action = in_array($id, $deleteModules, true) ? UserDataHandler::ACTION_DELETE : UserDataHandler::ACTION_TRANSFER;
                Event::trigger($module::className(), Module::EVENT_USER_DELETE_BEFORE, new Event(['payload' => [
                    'id' => $idUser,
                    'action' => $action,
                    'default_user' => $defaultUser,
                ]]));
            }

            UserGroup::deleteAll(['id_user' => $idUser]);
            Yii::$app->authManager->revokeAll($idUser);

            if ($user->delete() === false) {
                throw new UserDeleteException(Module::t('Could not delete a record in {table}.', ['table' => 'user_user']));
            }

            $transaction->commit();
            $jobs = self::$afterCommit;
            self::$afterCommit = null;
            self::runAfterOuterCommit($jobs);
        } catch (IntegrityException $e) {
            self::$afterCommit = null;
            $transaction->rollBack();
            Yii::warning($e->getMessage(), __METHOD__);
            throw new UserDeleteException(self::describe($e), 0, $e);
        } catch (\Throwable $e) {
            self::$afterCommit = null;
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Runs a non-database, irreversible job (e.g. deleting a file from disk) once the delete is
     * final. If the delete is rolled back, the job never runs.
     */
    public static function afterCommit(callable $job): void
    {
        if (self::$afterCommit !== null) {
            self::$afterCommit[] = $job;
            return;
        }
        self::runAfterOuterCommit([$job]);
    }

    /**
     * Runs jobs once the outermost transaction commits; drops them if that transaction rolls back.
     *
     * @param callable[] $jobs
     */
    private static function runAfterOuterCommit(array $jobs): void
    {
        if ($jobs === []) {
            return;
        }

        $run = static function () use ($jobs) {
            foreach ($jobs as $job) {
                try {
                    $job();
                } catch (\Throwable $e) {
                    Yii::warning($e->getMessage(), __METHOD__);
                }
            }
        };

        $db = Yii::$app->db;
        if ($db->getTransaction() === null) {
            $run();
            return;
        }

        $onCommit = null;
        $onRollback = null;
        $detach = static function () use ($db, &$onCommit, &$onRollback) {
            $db->off(Connection::EVENT_COMMIT_TRANSACTION, $onCommit);
            $db->off(Connection::EVENT_ROLLBACK_TRANSACTION, $onRollback);
        };
        $onCommit = static function () use ($run, $detach) {
            $detach();
            $run();
        };
        $onRollback = static function () use ($detach) {
            $detach();
        };
        $db->on(Connection::EVENT_COMMIT_TRANSACTION, $onCommit);
        $db->on(Connection::EVENT_ROLLBACK_TRANSACTION, $onRollback);
    }

    /**
     * The last active user in the admin role cannot be deleted; otherwise no one could sign in
     * as an administrator anymore.
     */
    private static function assertNotLastAdmin(int $idUser): void
    {
        $auth = Yii::$app->authManager;
        $role = Yii::$app->has('setting') ? Yii::$app->setting->getValue('site::admin_role') : null;
        $role = $role ?: 'admin';
        if ($auth === null || $auth->getAssignment($role, $idUser) === null) {
            return;
        }

        $otherIds = array_diff(array_map('intval', $auth->getUserIdsByRole($role)), [$idUser]);
        $otherAdmins = $otherIds === [] ? 0 : (int) User::find()
            ->where(['id_user' => $otherIds, 'status' => User::STATUS_ACTIVE])
            ->count();
        if ($otherAdmins === 0) {
            throw new UserDeleteException(Module::t('The last administrator cannot be deleted.'));
        }
    }

    /**
     * Modules listening to the `user-delete-before` event, in deletion order.
     *
     * @return array<string, \yii\base\Module>
     */
    public static function handlerModules(): array
    {
        $modules = [];
        foreach (array_keys(Yii::$app->getModules()) as $id) {
            $module = Yii::$app->getModule($id);
            if ($module !== null && Event::hasHandlers($module::className(), Module::EVENT_USER_DELETE_BEFORE)) {
                $modules[$id] = $module;
            }
        }

        uksort($modules, static fn ($a, $b) => (self::ORDER[$a] ?? 0) <=> (self::ORDER[$b] ?? 0));

        return $modules;
    }

    /**
     * Extracts the table that blocked the deletion from a foreign key error.
     */
    private static function describe(IntegrityException $e): string
    {
        if (preg_match('/fails \(`[^`]+`\.`([^`]+)`/', $e->getMessage(), $match)) {
            return Module::t('The user could not be deleted: records in {table} still belong to this user or to their data.', [
                'table' => $match[1],
            ]);
        }

        return Module::t('Please delete related models for delete user.');
    }
}
