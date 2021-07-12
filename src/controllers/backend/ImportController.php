<?php

namespace portalium\user\controllers\backend;

use portalium\base\Exception;
use portalium\user\models\GroupSearch;
use portalium\user\models\UserGroup;
use portalium\user\Module;
use Yii;
use portalium\site\models\Setting;
use portalium\web\Controller as WebController;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;
use portalium\user\models\ImportForm;

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
        if (!Yii::$app->user->can('importUser'))
            throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to import User"));
        $roles = [];
        foreach (Yii::$app->authManager->getRoles() as $item) {
            $roles[$item->name] = $item->name;
        }
        $model = new ImportForm();
        $model->first_name = "firstname";
        $model->last_name = "lastname";
        $model->username = "username";
        $model->email = "email";
        $model->password = "password";
        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($model->file==null) {
                Yii::$app->session->setFlash('error', Module::t('Please choose file.'));
                return $this->redirect(['index']);
            }
            $fileName = $this->upload($model->file);
            $path = realpath(Yii::$app->basePath . '/../data');
            $users = array();
            if (Setting::findOne(['name' => 'page::signup'])->value) {
                $filePath = $path . '/' . $fileName;

                $csv = array_map(function ($v) {
                    return str_getcsv($v, ";");
                }, file($filePath, FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES));
                $keys = array_shift($csv);

                foreach ($csv as $i => $row) {
                        $csv[$i] = array_combine($keys, $row);
                }
                $model->first_name = (array_search($model->first_name, $keys)) ? $model->first_name : null;
                $model->last_name = (array_search($model->last_name, $keys)) ? $model->last_name : null;
                $model->username = (array_search($model->username, $keys)) ? $model->username : null;
                $model->email = (array_search($model->email, $keys)) ? $model->email : null;
                $model->password = (array_search($model->password, $keys)) ? $model->password : null;
                if ($model->email==null || $model->username==null){
                    Yii::$app->session->setFlash('error', Module::t('You entered the username or e-mail columns.'));
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
            }

            $usersDB = Yii::$app->db->createCommand()->batchInsert("user",
                    ["first_name", "last_name", "username", "email", "auth_key", "password_hash", "password_reset_token", "access_token", "status"], $users)
                    ->rawSql . ' RETURNING id';
            $usersDB = 'INSERT IGNORE' . mb_substr($usersDB, strlen('INSERT'));
            $userIds = Yii::$app->db->createCommand($usersDB)->queryColumn();
            $role = Yii::$app->authManager->getRole($model->role);
            $userGroups = [];
            foreach ($userIds as $id) {
                ($model->role != null) ? Yii::$app->authManager->assign($role, $id) : null;
                if ($model->group != null) {
                    array_push($userGroups, [$id, $model->group, date('Y-m-d H:i:s')]);
                }
            }
            if ($model->group != null) {
                Yii::$app->db->createCommand()->batchInsert("user_group",
                    ["user_id", "group_id", "created_at"], $userGroups)
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

