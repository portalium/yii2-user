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
        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            $fileName = $this->upload($model->file);
            $path = realpath(Yii::$app->basePath . '/../data');
            $users = array();
            if (Setting::findOne(['name' => 'page::signup'])->value) {
                $fileHandler = fopen($path . '/' . $fileName, 'r');
                if ($fileHandler) {
                    while ($line = fgetcsv($fileHandler, 1000)) {
                        $user = array();
                        if (isset($line[0], $line[1], $line[2], $line[3], $line[4])) {
                            array_push($user, $line[0]);
                            array_push($user, $line[1]);
                            array_push($user, $line[2]);
                            array_push($user, $line[3]);
                            array_push($user, Yii::$app->security->generateRandomString());
                            array_push($user, Yii::$app->security->generatePasswordHash($line[4], 4));
                            array_push($user, Yii::$app->security->generateRandomString() . '_' . time());
                            array_push($user, Yii::$app->security->generateRandomString());
                            array_push($user, 10);
                            array_push($users, $user);
                        }
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

