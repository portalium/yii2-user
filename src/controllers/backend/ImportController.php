<?php

namespace portalium\user\controllers\backend;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\filters\Cors;
use portalium\site\models\SignupForm;
use portalium\site\models\Setting;
use portalium\web\Controller as WebController;
use yii\web\UploadedFile;
use portalium\site\models\LoginForm;
use portalium\user\models\ImportForm;
class ImportController extends WebController
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionImport()
    {

        $model = new ImportForm();
        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            $fileName = $this->upload($model->file);
            $path = realpath(Yii::$app->basePath . '/../data');
            if (!Yii::$app->user->isGuest) {
                if (Setting::findOne(['name' => 'page::signup'])->value) {
                    $fileHandler = fopen($path . '/' . $fileName, 'r');
                    if ($fileHandler) {
                        while ($line = fgetcsv($fileHandler, 1000)) {
                            $model = new ImportForm();
                            $model->first_name = $line[0];
                            $model->last_name = $line[1];
                            $model->username = $line[2];
                            $model->email = $line[3];
                            $model->password = $line[4];
                            if ($model->createUser()) {
                            } else {
                                return "User Already Registered";
                            }
                        }
                    }
                }
            }
        }
        return $this->render('import', [
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
