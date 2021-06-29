<?php

namespace portalium\user\controllers\backend;

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
        $model = new ImportForm();
        $model->first_name = "first_name";
        $model->last_name = "last_name";
        $model->username = "username";
        $model->email = "email";
        $model->password = "password";
        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            $fileName = $this->upload($model->file);
            $path = realpath(Yii::$app->basePath . '/../data');
            $users = array();
            if (Setting::findOne(['name' => 'page::signup'])->value) {
                $filePath = $path . '/' . $fileName;

                $csv = array_map("str_getcsv", file($filePath, FILE_SKIP_EMPTY_LINES));
                $keys = array_shift($csv);

                foreach ($csv as $i => $row) {
                    $csv[$i] = array_combine($keys, $row);
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
            Yii::$app->db->createCommand()->batchInsert("user",
                ["first_name", "last_name", "username", "email", "auth_key", "password_hash", "password_reset_token", "access_token", "status"], $users)
                ->execute();

        }
        return $this->render('index', [
            'model' => $model,
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

