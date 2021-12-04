<?php

namespace portalium\user\controllers\api;
use Yii;
use portalium\rest\ActiveController as RestActiveController;
use portalium\site\Module;
use portalium\site\models\SignupForm;
use yii\web\ForbiddenHttpException;

class UsersController extends RestActiveController
{
    public $modelClass = 'portalium\user\models\User';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions[ 'create' ]);

        return $actions;
    }

    public function actionCreate()
    {
        if (!Yii::$app->user->can('createUser'))
            throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to create User"));
        $model = new SignupForm();

        if($model->load(Yii::$app->getRequest()->getBodyParams(),'')) {
            if($user = $model->signup()){
                return ['access-token' => $user->access_token];
            }
            else
                return $this->modelError($model);
        }else{
            return $this->error(['SignupForm' => Module::t("Username (username), Password (password) and Email (email) required.")]);
        }
    }
}
