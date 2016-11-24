yii2-oauth2-server
==================

A wrapper for implementing an OAuth2 Server(https://github.com/bshaffer/oauth2-server-php)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist dixonsatit/yii2-oauth2-server "*"
```

or add

```json
"dixonsatit/yii2-oauth2-server": "~2.0"
```

to the require section of your composer.json.

To use the latest features (Like JWT tokens), you need to use 2.0.1 branch.
Edit your compose.json and add

```json
"dixonsatit/yii2-oauth2-server": "2.0.1.x-dev"
```

To use this extension,  simply add the following code in your application configuration:

```php
'bootstrap' => ['oauth2'],
'modules' => [
    'oauth2' => [
        'class' => 'dixonsatit\yii2\oauth2server\Module',
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
    ],
```
If you want to get Json Web Token (JWT) instead of convetional token, you will need to set `'useJwtToken' => true` in module and then define two more configurations:
`'public_key' => 'app\storage\PublicKeyStorage'` which is the class that implements [PublickKeyInterface](https://github.com/bshaffer/oauth2-server-php/blob/develop/src/OAuth2/Storage/PublicKeyInterface.php) and `'access_token' => 'app\storage\JwtAccessToken'` which implements [JwtAccessTokenInterface.php](https://github.com/bshaffer/oauth2-server-php/blob/develop/src/OAuth2/Storage/JwtAccessTokenInterface.php)

For Oauth2 base library provides the default [access_token](https://github.com/bshaffer/oauth2-server-php/blob/develop/src/OAuth2/Storage/JwtAccessToken.php) which works great except that it tries to save the token in the database. So I decided to inherit from it and override the part that tries to save (token size is too big and crashes with VARCHAR(40) in the database.

TL;DR, here are the sample classes
**access_token**
```php
<?php

namespace app\storage;

/**
 *
 * @author Stefano Mtangoo <mwinjilisti at gmail dot com>
 */
class JwtAccessToken extends \OAuth2\Storage\JwtAccessToken
{  
    public function setAccessToken($oauth_token, $client_id, $user_id, $expires, $scope = null)
    {

    }

    public function unsetAccessToken($access_token)
    {

    }
}

```

and **public_key**

```php
<?php
namespace app\storage;

class PublicKeyStorage implements \OAuth2\Storage\PublicKeyInterface{


    private $pbk =  null;
    private $pvk =  null;

    public function __construct()
    {
        //files should be in same directory as this file
        //keys can be generated using OpenSSL tool with command:
        /*
          private key:
          openssl genrsa -out privkey.pem 2048

          public key:
          openssl rsa -in privkey.pem -pubout -out pubkey.pem
        */
        $this->pbk =  file_get_contents('privkey.pem', true);
        $this->pvk =  file_get_contents('pubkey.pem', true);
    }

    public function getPublicKey($client_id = null){
        return  $this->pbk;
    }

    public function getPrivateKey($client_id = null){
        return  $this->pvk;
    }

    public function getEncryptionAlgorithm($client_id = null){
        return 'HS256';
    }

}

```
**NOTE:** You will need [this](https://github.com/bshaffer/oauth2-server-php/pull/690) PR applied or you can patch it yourself by checking changes in [this diff](https://github.com/hosannahighertech/oauth2-server-php/commit/ec79732663547065c041e279109137a423eac0cb). The other part of PR is only if you want to use firebase JWT library (which is not mandatory anyway).

Also, extend ```common\models\User``` - user model - implementing the interface ```\OAuth2\Storage\UserCredentialsInterface```, so the oauth2 credentials data stored in user table.
You should implement:
- findIdentityByAccessToken()
- checkUserCredentials()
- getUserDetails()

You can extend the model if you prefer it (please, remember to update the config files) :
```
use Yii;

class User extends common\models\User implements \OAuth2\Storage\UserCredentialsInterface
{

    /**
     * Implemented for Oauth2 Interface
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        /** @var \filsh\yii2\oauth2server\Module $module */
        $module = Yii::$app->getModule('oauth2');
        $token = $module->getServer()->getResourceController()->getToken();
        return !empty($token['user_id'])
                    ? static::findIdentity($token['user_id'])
                    : null;
    }

    /**
     * Implemented for Oauth2 Interface
     */
    public function checkUserCredentials($username, $password)
    {
        $user = static::findByUsername($username);
        if (empty($user)) {
            return false;
        }
        return $user->validatePassword($password);
    }

    /**
     * Implemented for Oauth2 Interface
     */
    public function getUserDetails($username)
    {
        $user = static::findByUsername($username);
        return ['user_id' => $user->getId()];
    }
}
```

Additional OAuth2 Flags:

```enforceState``` - Flag that switch that state controller should allow to use "state" param in the "Authorization Code" Grant Type

```allowImplicit``` - Flag that switch that controller should allow the "implicit" grant type

The next step your shold run migration

```php
yii migrate --migrationPath=@vendor/dixonsatit/yii2-oauth2-server/migrations
```

this migration create the oauth2 database scheme and insert test user credentials ```testclient:testpass``` for ```http://fake/```

add url rule to urlManager

```php
'urlManager' => [
    'enablePrettyUrl' => true, //only if you want to use petty URLs
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
use dixonsatit\yii2\oauth2server\filters\ErrorToExceptionFilter;
use dixonsatit\yii2\oauth2server\filters\auth\CompositeAuth;

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

        /** @var $module \dixonsatit\yii2\oauth2server\Module */
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
### JWT Tokens (2.0.1 branch only)
If you want to get Json Web Token (JWT) instead of convetional token, you will need to set `'useJwtToken' => true` in module and then define two more configurations:
`'public_key' => 'app\storage\PublicKeyStorage'` which is the class that implements [PublickKeyInterface](https://github.com/bshaffer/oauth2-server-php/blob/develop/src/OAuth2/Storage/PublicKeyInterface.php) and `'access_token' => 'OAuth2\Storage\JwtAccessToken'` which implements [JwtAccessTokenInterface.php](https://github.com/bshaffer/oauth2-server-php/blob/develop/src/OAuth2/Storage/JwtAccessTokenInterface.php)

For Oauth2 base library provides the default [access_token](https://github.com/bshaffer/oauth2-server-php/blob/develop/src/OAuth2/Storage/JwtAccessToken.php) which works great except. Just use it and everything will be fine.

and **public_key**

```php
<?php
namespace app\storage;

class PublicKeyStorage implements \OAuth2\Storage\PublicKeyInterface{


    private $pbk =  null;
    private $pvk =  null;

    public function __construct()
    {
        $this->pvk =  file_get_contents('privkey.pem', true);
        $this->pbk =  file_get_contents('pubkey.pem', true);
    }

    public function getPublicKey($client_id = null){
        return  $this->pbk;
    }

    public function getPrivateKey($client_id = null){
        return  $this->pvk;
    }

    public function getEncryptionAlgorithm($client_id = null){
        return 'RS256';
    }

}

```


For more, see https://github.com/bshaffer/oauth2-server-php
