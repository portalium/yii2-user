<?php

namespace portalium\user\controllers\api;

use Yii;
use yii\web\ForbiddenHttpException;
use portalium\rest\ActiveController as RestActiveController;
use portalium\site\Module;
use portalium\site\models\SignupForm;
use portalium\user\models\UserSearch;

class UsersController extends RestActiveController
{
    public $modelClass = 'portalium\user\models\User';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions[ 'create' ]);
        $actions['index']['prepareDataProvider'] = function ($action) {
            $searchModel = new UserSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            if(!Yii::$app->user->can('userApiDefaultIndex')){
                $dataProvider->query->andWhere(['id_user'=>Yii::$app->user->id]);
            }
            return $dataProvider;
        };  
        return $actions;
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        switch ($action->id) {
            case 'view':
                if (!Yii::$app->user->can('userApiDefaultView')) 
                    throw new \yii\web\ForbiddenHttpException(Module::t('You do not have permission to view this menu.'));
                break;
            case 'create':
                if (!Yii::$app->user->can('userApiDefaultCreate')) 
                    throw new \yii\web\ForbiddenHttpException(Module::t('You do not have permission to create this menu.'));
                break;
            case 'update':
                if (!Yii::$app->user->can('userApiDefaultUpdate')) 
                    throw new \yii\web\ForbiddenHttpException(Module::t('You do not have permission to update this menu.'));
                break;
            case 'delete':
                if (!Yii::$app->user->can('userApiDefaultDelete'))
                    throw new \yii\web\ForbiddenHttpException(Module::t('You do not have permission to delete this menu.'));
                break;
            default:
                if (!Yii::$app->user->can('userApiDefaultIndex') && !Yii::$app->user->can('userApiDefaultIndexOwn'))
                    throw new \yii\web\ForbiddenHttpException(Module::t('You do not have permission to delete this menu.'));
                break;
        }
        
        return true;
    }

    public function actionCreate()
    {
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
