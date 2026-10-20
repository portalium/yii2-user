<?php

namespace portalium\user\components;

use portalium\user\Module;
use Yii;

/**
 * Shared operations used by modules' `user-delete-before` listeners.
 *
 * Payload: `id` the user being deleted, `action` `delete` or `transfer`, `default_user` the
 * user to transfer records to. A failed operation never fails silently — it throws
 * UserDeleteException, and UserDeleter rolls back the whole delete.
 */
class UserDataHandler
{
    public const ACTION_DELETE = 'delete';
    public const ACTION_TRANSFER = 'transfer';

    /**
     * @return array{0: int, 1: string, 2: int|null} [id_user, action, default_user]
     */
    public static function payload($event): array
    {
        $payload = $event->payload;
        $action = ($payload['action'] ?? null) === self::ACTION_DELETE ? self::ACTION_DELETE : self::ACTION_TRANSFER;
        $defaultUser = empty($payload['default_user']) ? null : (int) $payload['default_user'];

        return [(int) $payload['id'], $action, $defaultUser];
    }

    /**
     * Deletes or transfers the model's records belonging to the user, depending on the action.
     *
     * @param class-string<\yii\db\ActiveRecord> $modelClass
     */
    public static function apply(string $action, string $modelClass, int $idUser, ?int $defaultUser, string $column = 'id_user'): int
    {
        if ($action === self::ACTION_DELETE) {
            return self::delete($modelClass, $idUser, $column);
        }

        return self::transfer($modelClass, $idUser, $defaultUser, $column);
    }

    /**
     * Deletes records one by one, so the model's beforeDelete/afterDelete hooks run.
     *
     * @param class-string<\yii\db\ActiveRecord> $modelClass
     */
    public static function delete(string $modelClass, int $idUser, string $column = 'id_user'): int
    {
        if (!self::tableExists($modelClass::tableName())) {
            return 0;
        }

        $count = 0;
        foreach ($modelClass::find()->where([$column => $idUser])->all() as $model) {
            if ($model->delete() === false) {
                throw new UserDeleteException(Module::t('Could not delete a record in {table}.', [
                    'table' => self::rawTableName($modelClass::tableName()),
                ]));
            }
            $count++;
        }

        return $count;
    }

    /**
     * Changes the owner of the records. Validation is skipped; the old owner is about to be deleted anyway.
     *
     * @param class-string<\yii\db\ActiveRecord> $modelClass
     */
    public static function transfer(string $modelClass, int $idUser, ?int $defaultUser, string $column = 'id_user'): int
    {
        return self::transferTable($modelClass::tableName(), $idUser, $defaultUser, $column);
    }

    /**
     * Delete or transfer by action, for tables that have no model.
     */
    public static function applyTable(string $action, string $table, int $idUser, ?int $defaultUser, string $column = 'id_user'): int
    {
        if (!self::tableExists($table)) {
            return 0;
        }
        if ($action === self::ACTION_DELETE) {
            return Yii::$app->db->createCommand()->delete($table, [$column => $idUser])->execute();
        }

        return self::transferTable($table, $idUser, $defaultUser, $column);
    }

    private static function transferTable(string $table, int $idUser, ?int $defaultUser, string $column): int
    {
        if (!self::tableExists($table)) {
            return 0;
        }

        $exists = (new \yii\db\Query())->from($table)->where([$column => $idUser])->exists();
        if (!$exists) {
            return 0;
        }

        if ($defaultUser === null) {
            throw new UserDeleteException(Module::t('Select a user to transfer the records in {table} to.', [
                'table' => self::rawTableName($table),
            ]));
        }

        return Yii::$app->db->createCommand()->update($table, [$column => $defaultUser], [$column => $idUser])->execute();
    }

    /**
     * Some modules have a model but no corresponding table set up (e.g. app_collection_token
     * doesn't exist on suite); those are skipped.
     */
    private static function tableExists(string $table): bool
    {
        return Yii::$app->db->getTableSchema($table, true) !== null;
    }

    private static function rawTableName(string $table): string
    {
        return Yii::$app->db->getSchema()->getRawTableName($table);
    }
}
