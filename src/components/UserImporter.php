<?php

namespace portalium\user\components;

use portalium\user\models\Group;
use portalium\user\models\User;
use portalium\user\Module;
use Yii;
use yii\rbac\Role;
use yii\validators\EmailValidator;

/**
 * Creates users from a CSV file. Each user is saved through the model, so user-create events run
 * (e.g. the new user's home workspace); existing usernames or emails are skipped, not overwritten.
 */
class UserImporter
{
    public const FIELDS = ['username', 'email', 'first_name', 'last_name', 'password'];

    public const MAX_ROWS = 5000;

    private const DELIMITERS = [',', ';', "\t", '|'];

    /**
     * @return array{delimiter: string, columns: string[], rows: array<int, array<string, string>>}
     *         rows are keyed by their record number in the file (the header is 1)
     */
    public static function read(string $path): array
    {
        $handle = @fopen($path, 'r');
        $header = $handle === false ? false : fgets($handle);
        if ($header === false || trim($header) === '') {
            throw new UserImportException(Module::t('The file is empty.'));
        }

        // fgetcsv correctly reads a delimiter or newline inside quotes; the delimiter is detected from the first line.
        $delimiter = self::detectDelimiter(preg_replace('/^\xEF\xBB\xBF/', '', $header));
        rewind($handle);
        $columns = array_map('trim', fgetcsv($handle, 0, $delimiter));
        $columns[0] = preg_replace('/^\xEF\xBB\xBF/', '', $columns[0]);

        $rows = [];
        $record = 1;
        while (($values = fgetcsv($handle, 0, $delimiter)) !== false) {
            $record++;
            if ($values === [null]) {
                continue;
            }
            if (count($rows) >= self::MAX_ROWS) {
                fclose($handle);
                throw new UserImportException(Module::t('A file can contain at most {count} users.', ['count' => self::MAX_ROWS]));
            }
            $values = array_slice($values, 0, count($columns));
            $rows[$record] = array_combine($columns, array_pad($values, count($columns), ''));
        }
        fclose($handle);

        return ['delimiter' => $delimiter, 'columns' => $columns, 'rows' => $rows];
    }

    /**
     * @param array<int, array<string, string>> $rows from read()
     * @param array<string, string|null> $mapping field (see FIELDS) => CSV column; username and email are required
     * @return array{created: int, skipped: int, errors: list<array{line: int, message: string}>}
     */
    public static function import(array $rows, array $mapping, ?string $roleName = null, ?int $groupId = null): array
    {
        foreach (['username', 'email'] as $field) {
            if (empty($mapping[$field])) {
                throw new UserImportException(Module::t('Choose the username and email columns.'));
            }
        }

        $role = null;
        if ($roleName !== null && $roleName !== '') {
            $role = Yii::$app->authManager->getRole($roleName);
            if ($role === null) {
                throw new UserImportException(Module::t('The selected role was not found.'));
            }
        }
        $group = null;
        if ($groupId !== null) {
            $group = Group::findOne($groupId);
            if ($group === null) {
                throw new UserImportException(Module::t('The selected group was not found.'));
            }
        }

        $result = ['created' => 0, 'skipped' => 0, 'errors' => []];
        $createdIds = [];
        $emailValidator = new EmailValidator();

        // An unexpected error in one row (e.g. a concurrent uniqueness conflict) only drops that row;
        // the users created up to that point are still added to the group.
        try {
            foreach ($rows as $line => $row) {
                try {
                    self::importRow($line, $row, $mapping, $role, $emailValidator, $result, $createdIds);
                } catch (\Throwable $e) {
                    Yii::error($e->getMessage(), __METHOD__);
                    $result['errors'][] = ['line' => $line, 'message' => Module::t('The row could not be saved.')];
                }
            }
        } finally {
            if ($group !== null && $createdIds !== []) {
                $group->scenario = Group::SCENARIO_INSERT_USERS;
                $group->setUserIds($createdIds);
                if (!$group->save()) {
                    $errors = $group->getFirstErrors();
                    $result['errors'][] = ['line' => 0, 'message' => (string) reset($errors)];
                }
            }
        }

        return $result;
    }

    /**
     * Opens one user; fills $result and $createdIds. Rows whose username or email already exists are skipped.
     */
    private static function importRow(
        int $line,
        array $row,
        array $mapping,
        ?Role $role,
        EmailValidator $emailValidator,
        array &$result,
        array &$createdIds
    ): void {
        $value = static fn (string $field): string => isset($mapping[$field], $row[$mapping[$field]]) ? trim($row[$mapping[$field]]) : '';
        $username = $value('username');
        $email = $value('email');
        $password = $value('password');

        if ($username === '' || !$emailValidator->validate($email)) {
            $result['errors'][] = ['line' => $line, 'message' => Module::t('A username and a valid email are required.')];
            return;
        }
        if ($password !== '' && mb_strlen($password) < 6) {
            $result['errors'][] = ['line' => $line, 'message' => Module::t('Password should contain at least 6 characters.')];
            return;
        }
        if (User::find()->where(['or', ['username' => $username], ['email' => $email]])->exists()) {
            $result['skipped']++;
            return;
        }

        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->first_name = $value('first_name');
        $user->last_name = $value('last_name');
        $user->setPassword($password !== '' ? $password : Yii::$app->security->generateRandomString(16));
        $user->generateAuthKey();
        $user->access_token = Yii::$app->security->generateRandomString();
        if (!$user->save()) {
            $errors = $user->getFirstErrors();
            $result['errors'][] = ['line' => $line, 'message' => (string) reset($errors)];
            return;
        }

        // The user-create event may already have given the default role.
        if ($role !== null && Yii::$app->authManager->getAssignment($role->name, $user->id_user) === null) {
            Yii::$app->authManager->assign($role, $user->id_user);
        }
        $createdIds[] = $user->id_user;
        $result['created']++;
    }

    private static function detectDelimiter(string $line): string
    {
        $best = ',';
        $bestCount = 0;
        foreach (self::DELIMITERS as $delimiter) {
            $count = count(str_getcsv($line, $delimiter));
            if ($count > $bestCount) {
                $best = $delimiter;
                $bestCount = $count;
            }
        }

        return $best;
    }
}
