<?php

namespace portalium\user\components;

use yii\base\UserException;

/**
 * Thrown when a module's data could not be deleted or transferred while deleting a user.
 * Its message is safe to display to the user.
 */
class UserDeleteException extends UserException
{
}
