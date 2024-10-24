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
use portalium\user\Module as UserModule;

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
    
    public function detectDelimiter($csvFile) 
    {
        $delimiters = [',', ';', "\t", '|']; 
        $firstLine = fgets(fopen($csvFile, 'r'));

        $result = ['delimiter' => null, 'count' => 0];

        foreach ($delimiters as $delimiter) {
            $fields = str_getcsv($firstLine, $delimiter);
            if (count($fields) > $result['count']) {
                $result = ['delimiter' => $delimiter, 'count' => count($fields)];
            }
        }

        return $result['delimiter'];
    }
    
    public function detectColumnMappings($csvFile)
    {
       $mappingRules = [
            'username' => ['username', 'userid', 'kullaniciadi', 'user_name'],
            'firstname' => ['firstname', 'first_name', 'ad'],
            'lastname' => ['lastname', 'soyisim', 'surname', 'last_name'],
            'email' => ['email', 'eposta', 'mail', 'email_address'],
            'password' => ['password', 'sifre', 'pwd'],
        ];
        
        $handle = fopen($csvFile, 'r');
        $firstLine = fgetcsv($handle);
        fclose($handle); 
        
        $columnMappings = [];
        
        foreach ($firstLine as $column) {
            $mapped = false;
            foreach ($mappingRules as $key => $aliases) {
                if (in_array(strtolower(trim($column)), $aliases)) {
                    $columnMappings[$key] = $column;
                    $mapped = true;
                    break;
                }
            }
            if (!$mapped) {
                $columnMappings['unknown'][] = $column;
            }
        }
        
        return $columnMappings;
    }

    

    public function actionIndex()
    {
        if (!Yii::$app->user->can('userWebImportIndex'))
            throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to import User"));

        $model = new ImportForm();

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
            $filePath = Yii::$app->basePath . '/../'.Yii::$app->setting->getValue('storage::path').'/'.$fileName;
            if (!file_exists($filePath)) {
                Yii::$app->session->addFlash('error', Module::t('File not found: ' . $filePath));
                return $this->redirect(['index']);
            }

            $csv = array_map(function ($v) use ($filePath) {
                return str_getcsv($v, $this->detectDelimiter($filePath));
            }, file($filePath, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES));
            $keys = array_shift($csv); 

             $columnMappings = $this->detectColumnMappings($filePath);
            
            $keys = array_map(function($key) use ($columnMappings) {
                return isset($columnMappings[$key]) ? $columnMappings[$key] : $key;
            }, $keys);

            foreach ($csv as $i => $row) {
                $csv[$i] = array_combine($keys, $row);
            }
           
            
            $users = [];

            foreach ($csv as $line) {
                $user = array();

                if (isset($columnMappings['username']) && isset($line[$columnMappings['username']]) && 
                    isset($columnMappings['email']) && isset($line[$columnMappings['email']])) {
                    
                    array_push($user, isset($line[$columnMappings['firstname']]) ? $line[$columnMappings['firstname']] : "");
                    array_push($user, isset($line[$columnMappings['lastname']]) ? $line[$columnMappings['lastname']] : "");
                    array_push($user, $line[$columnMappings['username']]);
                    array_push($user, $line[$columnMappings['email']]);
                    array_push($user, Yii::$app->security->generateRandomString());
                    array_push($user, Yii::$app->security->generatePasswordHash(
                        isset($line[$columnMappings['password']]) ? $line[$columnMappings['password']] : Yii::$app->security->generateRandomKey(6), 
                        4
                    ));
                    array_push($user, Yii::$app->security->generateRandomString() . '_' . time());
                    array_push($user, Yii::$app->security->generateRandomString());
                    array_push($user, 10);

                    array_push($users, $user);
                }
            }

            $usersDB = Yii::$app->db->createCommand()->batchInsert(
                UserModule::$tablePrefix . 'user',
                ["first_name", "last_name", "username", "email", "auth_key", "password_hash", "password_reset_token", "access_token", "status"],
                $users
            )->execute();
            
            $userNames = [];
            foreach ($users as $user) {
                if (preg_match('/[\'\/\\\^£$%&*()}{@#~?><>,|=_+¬-]/', $user[2])) {
                   Yii::$app->session->addFlash('error', Module::t('Username contains invalid characters: ' . $user[2]));
                    return $this->redirect(['index']); 
                }
                $userNames[] = $user[2];
            }

            if (empty($userNames)) {
                Yii::$app->session->addFlash('error', Module::t('No valid usernames found.'));
                return $this->redirect(['index']);
            }

            $userIds = Yii::$app->db->createCommand("
                SELECT id_user 
                FROM user_user 
                WHERE username IN ('" . implode("','", $userNames) . "')
            ")->queryColumn();

            if (empty($userIds)) {
                Yii::$app->session->addFlash('error', Module::t('No users found with the given usernames.'));
                return $this->redirect(['index']);
            }

            $role = Yii::$app->authManager->getRole($model->role);
            $userGroups = [];

            foreach ($userIds as $id) {
                if ($model->role != null) {
                    Yii::$app->authManager->assign($role, $id);
                }
                if ($model->group != null) {
                    $userGroups[] = [$id, $model->group, date('Y-m-d H:i:s')];
                }
            }

            if ($model->group != null) {
                Yii::$app->db->createCommand()->batchInsert(
                    "user_user_group",
                    ["id_user", "id_group", "date_create"],
                    $userGroups
                )->execute();
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
