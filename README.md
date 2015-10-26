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
"filsh/yii2-oauth2-server": "~2.0"
```

to the require section of your composer.json.

To use this extension,  simply add the following code in your application configuration:

```php
'bootstrap' => ['oauth2'],
'modules' => [
    'oauth2' => [
        'class' => 'filsh\yii2\oauth2server\Module',
        'tokenParamName' => 'accessToken',
        'tokenAccessLifetime' => 3600 * 24,
        'storageMap' => [
            'user_credentials' => 'common\models\User',
        ],
        'grantTypes' => [
            'user_credentials' => [
                'class' => 'OAuth2\GrantType\UserCredentials',
            ],
            'refresh_token' => [
                'class' => 'OAuth2\GrantType\RefreshToken',
                'always_issue_new_refresh_token' => true
            ]
        ]
    ]
]
```

```common\models\User``` - user model implementing an interface ```\OAuth2\Storage\UserCredentialsInterface```, so the oauth2 credentials data stored in user table

Additional OAuth2 Flags:

```enforceState``` - Flag that switch that state controller should allow to use "state" param in the "Authorization Code" Grant Type

```allowImplicit``` - Flag that switch that controller should allow the "implicit" grant type

The next step your shold run migration

```php
yii migrate --migrationPath=@vendor/filsh/yii2-oauth2-server/migrations
```

this migration create the oauth2 database scheme and insert test user credentials ```testclient:testpass``` for ```http://fake/```

add url rule to urlManager

```php
'urlManager' => [
    'rules' => [
        'POST oauth2/<action:\w+>' => 'oauth2/rest/<action>',
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

Create action authorize in site controller for Authorization Code

`https://api.mysite.com/authorize?response_type=code&client_id=TestClient&redirect_uri=https://fake/`

[see more](http://bshaffer.github.io/oauth2-server-php-docs/grant-types/authorization-code/)

```php
/**
 * SiteController
 */
class SiteController extends Controller
{
    /**
     * @return mixed
     */
    public function actionAuthorize()
    {
        if (Yii::$app->getUser()->getIsGuest())
            return $this->redirect('login');
    
        /** @var $module \filsh\yii2\oauth2server\Module */
        $module = Yii::$app->getModule('oauth2');
        $response = $module->handleAuthorizeRequest(!Yii::$app->getUser()->getIsGuest(), Yii::$app->getUser()->getId());
    
        /** @var object $response \OAuth2\Response */
        Yii::$app->getResponse()->format = \yii\web\Response::FORMAT_JSON;
    
        return $response->getParameters();
    }
}
```

Also if you set ```allowImplicit => true```  you can use Implicit Grant Type - [see more](http://bshaffer.github.io/oauth2-server-php-docs/grant-types/implicit/)

Request example:

`https://api.mysite.com/authorize?response_type=token&client_id=TestClient&redirect_uri=https://fake/cb`

With redirect response:

`https://fake/cb#access_token=2YotnFZFEjr1zCsicMWpAA&state=xyz&token_type=bearer&expires_in=3600`





For more, see https://github.com/bshaffer/oauth2-server-php
