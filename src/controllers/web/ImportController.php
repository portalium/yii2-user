<?php

namespace portalium\user\controllers\web;

use Yii;
use portalium\site\models\Setting;
use portalium\web\Controller as WebController;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;
use portalium\user\models\ImportForm;
use portalium\base\Exception;
use portalium\user\models\GroupSearch;
use portalium\user\models\UserGroup;
use portalium\user\Module;

class ImportController extends WebController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        if (!Yii::$app->user->can('userWebImportIndex'))
            throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to import User"));

        $model = new ImportForm(["first_name" => "firstname", "last_name" => "lastname", "username" => "username", "password" => "password", "email" => "email"]);

        $roles = [];
        foreach (Yii::$app->authManager->getRoles() as $item) {
            $roles[$item->name] = $item->name;
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($model->file == null) {
                Yii::$app->session->addFlash('error', Module::t('Please choose file.'));
                return $this->redirect(['index']);
            }
            $fileName = $this->upload($model->file);

            $users = [];
            $filePath = realpath(Yii::$app->basePath . '/../data') . '/' . $fileName;
            $GLOBALS['filepath'] = $filePath;
            $csv = array_map(function ($v) {
                return str_getcsv($v, $this->detectDelimiter($GLOBALS['filepath']));
            }, file($filePath, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES));
            $keys = array_shift($csv);

            foreach ($csv as $i => $row) {
                $csv[$i] = array_combine($keys, $row);
            }

            foreach ($model->attributes() as $attribute) {
                if ($attribute != "file") {
                    $model[$attribute] = (array_search($model[$attribute], $keys)) ? $model[$attribute] : null;
                }
            }

            if ($model->email == null || $model->username == null) {
                Yii::$app->session->addFlash('error', Module::t('You entered the username or e-mail columns.'));
                return $this->redirect(['index']);
            }

            foreach ($csv as $line) {
                $user = array();
                if (isset($line[$model->username], $line[$model->email])) {
                    array_push($user, ($model->first_name != null) ? $line[$model->first_name] : "");
                    array_push($user, ($model->last_name != null) ? $line[$model->last_name] : "");
                    array_push($user, $line[$model->username]);
                    array_push($user, $line[$model->email]);
                    array_push($user, Yii::$app->security->generateRandomString());
                    array_push($user, Yii::$app->security->generatePasswordHash(($model->password != null) ? $line[$model->password] : Yii::$app->security->generateRandomKey(6), 4));
                    array_push($user, Yii::$app->security->generateRandomString() . '_' . time());
                    array_push($user, Yii::$app->security->generateRandomString());
                    array_push($user, 10);
                    array_push($users, $user);
                }
            }

            $usersDB = Yii::$app->db->createCommand()->batchInsert(
                "user",
                ["first_name", "last_name", "username", "email", "auth_key", "password_hash", "password_reset_token", "access_token", "status"],
                $users
            )
                ->rawSql;
            $usersDB = 'INSERT IGNORE' . mb_substr($usersDB, strlen('INSERT'));
            Yii::$app->db->createCommand($usersDB)->queryColumn();
            //get user id from username
            $userNames = array();
            foreach ($users as $user) {
                //if filter sql injection
                if (preg_match('/[\'\/\\\^£$%&*()}{@#~?><>,|=_+¬-]/', $user[2])) {
                    //create flash message error
                    Yii::$app->session->addFlash('error', Module::t('Username contains invalid characters.'));
                    continue;
                }
                array_push($userNames, $user[2]);
            }


            $userIds = Yii::$app->db->createCommand("SELECT id FROM user WHERE username IN ('" . implode("','", $userNames) . "')")->queryColumn();

            $role = Yii::$app->authManager->getRole($model->role);
            $userGroups = [];
            foreach ($userIds as $id) {
                if ($model->role != null)
                    Yii::$app->authManager->assign($role, $id);
                if ($model->group != null) {
                    array_push($userGroups, [$id, $model->group, date('Y-m-d H:i:s')]);
                }
            }
            if ($model->group != null) {
                Yii::$app->db->createCommand()->batchInsert(
                    "user_group",
                    ["id_user", "id_group", "date_create"],
                    $userGroups
                )
                    ->execute();
            }
        }

        return $this->render('index', [
            'model' => $model,
            'roles' => $roles,
        ]);
    }


    public function upload($file)
    {
        $path = realpath(Yii::$app->basePath . '/../data');
        $filename = md5(rand()) . ".csv";

        if ($file->saveAs($path . '/' . $filename)) {

            return $filename;
        }

        return false;
    }
}
