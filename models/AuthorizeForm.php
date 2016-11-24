<?php
namespace dixonsatit\yii2\oauth2server\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class AuthorizeForm extends Model
{
    public $authorized;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // rememberMe must be a boolean value
            ['authorized', 'boolean']
        ];
    }

}
