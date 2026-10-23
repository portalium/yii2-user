<?php

namespace portalium\user\controllers\api;

use portalium\rest\Controller as RestController;
use portalium\user\components\UserImporter;
use portalium\user\components\UserImportException;
use portalium\user\models\Group;
use portalium\user\Module;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;

/**
 * CSV user import. The uploaded file is read from its temporary location and not kept.
 */
class ImportController extends RestController
{
    protected function verbs()
    {
        return [
            'options' => ['GET'],
            'columns' => ['POST'],
            'run' => ['POST'],
        ];
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (!Yii::$app->user->can('userApiImportIndex')) {
            throw new ForbiddenHttpException(Module::t('Sorry you are not allowed to import User'));
        }

        return true;
    }

    /**
     * Roles and groups the imported users can be given.
     */
    public function actionOptions()
    {
        return [
            'fields' => UserImporter::FIELDS,
            'roles' => array_keys(Yii::$app->authManager->getRoles()),
            'groups' => Group::find()->select(['id_group', 'name'])->orderBy('name')->asArray()->all(),
        ];
    }

    /**
     * Multipart `file` (CSV) → the header columns, the detected delimiter and the number of data rows.
     */
    public function actionColumns()
    {
        try {
            $csv = UserImporter::read($this->uploadedPath());
        } catch (UserImportException $e) {
            return $this->error(['file' => $e->getMessage()]);
        }

        return ['columns' => $csv['columns'], 'delimiter' => $csv['delimiter'], 'rows' => count($csv['rows'])];
    }

    /**
     * Multipart `file` (CSV), `mapping[<field>]` = column name, optional `role` and `group` (id_group).
     */
    public function actionRun()
    {
        $request = Yii::$app->request;
        $group = $request->getBodyParam('group');

        try {
            $csv = UserImporter::read($this->uploadedPath());
            return UserImporter::import(
                $csv['rows'],
                (array) $request->getBodyParam('mapping', []),
                $request->getBodyParam('role') ?: null,
                $group === null || $group === '' ? null : (int) $group
            );
        } catch (UserImportException $e) {
            return $this->error(['file' => $e->getMessage()]);
        }
    }

    private function uploadedPath(): string
    {
        $file = UploadedFile::getInstanceByName('file');
        if ($file === null || $file->hasError || strtolower($file->extension) !== 'csv') {
            throw new UserImportException(Module::t('Please choose a CSV file.'));
        }

        return $file->tempName;
    }
}
