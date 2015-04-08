yii2-oauth2-server
==================

A wrapper for implementing an OAuth2 Server(https://github.com/bshaffer/oauth2-server-php)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist filsh/yii2-oauth2-server "*"
```

or add

```json
"filsh/yii2-oauth2-server": "*"
```

to the require section of your composer.json.

To use this extension,  simply add the following code in your application configuration:

```php
'oauth2' => [
    'class' => 'filsh\yii2\oauth2server\Module',
    'options' => [
        'token_param_name' => 'accessToken',
        'access_lifetime' => 3600 * 24
    ],
    'storageMap' => [
        'user_credentials' => 'common\models\User'
    ],
    'grantTypes' => [
        'client_credentials' => [
            'class' => 'OAuth2\GrantType\ClientCredentials',
            'allow_public_clients' => false
        ],
        'user_credentials' => [
            'class' => 'OAuth2\GrantType\UserCredentials'
        ],
        'refresh_token' => [
            'class' => 'OAuth2\GrantType\RefreshToken',
            'always_issue_new_refresh_token' => true
        ]
    ],
]
```

```common\models\User``` - user model implementing an interface ```\OAuth2\Storage\UserCredentialsInterface```, so the oauth2 credentials data stored in user table

The next step your shold run migration

```php
yii migrate --migrationPath=@vendor/filsh/yii2-oauth2-server/migrations
```

this migration create the oauth2 database scheme and insert test user credentials ```testclient:testpass``` for ```http://fake/```

add url rule to urlManager

```php
'urlManager' => [
    'rules' => [
        'POST oauth2/<action:\w+>' => 'oauth2/default/<action>',
        ...
    ]
]
```

Usage
-----

To use this extension,  simply add the behaviors for your base controller:

```php
use yii\helpers\ArrayHelper;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use filsh\yii2\oauth2server\filters\ErrorToExceptionFilter;
use filsh\yii2\oauth2server\filters\auth\CompositeAuth;

class Controller extends \yii\rest\Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'authenticator' => [
                'class' => CompositeAuth::className(),
                'authMethods' => [
                    ['class' => HttpBearerAuth::className()],
                    ['class' => QueryParamAuth::className(), 'tokenParam' => 'accessToken'],
                ]
            ],
            'exceptionFilter' => [
                'class' => ErrorToExceptionFilter::className()
            ],
        ]);
    }
}
```

For more, see https://github.com/bshaffer/oauth2-server-php
