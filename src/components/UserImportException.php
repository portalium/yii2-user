<?php

namespace portalium\user\components;

use yii\base\UserException;

/**
 * Thrown when an import file or its settings can't be used. Its message is safe to display to the user.
 */
class UserImportException extends UserException
{
}
