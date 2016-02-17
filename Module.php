<?php

namespace filsh\yii2\oauth2server;

use \Yii;
use yii\i18n\PhpMessageSource;
use  \array_key_exists;

/**
 * For example,
 * 
 * ```php
 * 'oauth2' => [
 *     'class' => 'filsh\yii2\oauth2server\Module',
 *     'tokenParamName' => 'accessToken',
 *     'tokenAccessLifetime' => 3600 * 24,
 *     'storageMap' => [
 *         'user_credentials' => 'common\models\User',
 *     ],
 *     'grantTypes' => [
 *         'user_credentials' => [
 *             'class' => 'OAuth2\GrantType\UserCredentials',
 *         ],
 *         'refresh_token' => [
 *             'class' => 'OAuth2\GrantType\RefreshToken',
 *             'always_issue_new_refresh_token' => true
 *         ]
 *     ]
 * ]
 * ```
 */
class Module extends \yii\base\Module
{
    const VERSION = '2.0.0';
    
    /**
     * @var array Model's map
     */
    public $modelMap = [];
    
    /**
     * @var array Storage's map
     */
    public $storageMap = [];
    
    /**
     * @var array GrantTypes collection
     */
    public $grantTypes = [];
    
    /**
     * @var array server options
     */
    public $options = [];

    /**
     * @var string name of access token parameter
     */
    public $tokenParamName;
    
    /**
     * @var type max access lifetime
     */
    public $tokenAccessLifetime;
    /**
     * @var whether to use JWT tokens
     */
    public $useJwtToken = false;//ADDED
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->registerTranslations();
    }
    
    /**
     * Gets Oauth2 Server
     * 
     * @return \filsh\yii2\oauth2server\Server
     * @throws \yii\base\InvalidConfigException
     */
    public function getServer()
    {
        if(!$this->has('server')) {
            $storages = [];
            
            if($this->useJwtToken)
            {
                if(!array_key_exists('access_token', $this->storageMap) || !array_key_exists('public_key', $this->storageMap)) {
                        throw new \yii\base\InvalidConfigException('access_token and public_key must be set or set useJwtToken to false');
                }
                //define dependencies when JWT is used instead of normal token
                \Yii::$container->clear('public_key'); //remove old definition
                \Yii::$container->set('public_key', $this->storageMap['public_key']);
                \Yii::$container->set('OAuth2\Storage\PublicKeyInterface', $this->storageMap['public_key']);

                \Yii::$container->clear('access_token'); //remove old definition
                \Yii::$container->set('access_token', $this->storageMap['access_token']);
            }
            
            foreach(array_keys($this->storageMap) as $name) {
                $storages[$name] = \Yii::$container->get($name);
            }
            
            $grantTypes = [];
            foreach($this->grantTypes as $name => $options) {
                if(!isset($storages[$name]) || empty($options['class'])) {
                    throw new \yii\base\InvalidConfigException('Invalid grant types configuration.');
                }

                $class = $options['class'];
                unset($options['class']);

                $reflection = new \ReflectionClass($class);
                $config = array_merge([0 => $storages[$name]], [$options]);

                $instance = $reflection->newInstanceArgs($config);
                $grantTypes[$name] = $instance;
            }
            
            $server = \Yii::$container->get(Server::className(), [
                $this,
                $storages,
                array_merge(array_filter([
                    'use_jwt_access_tokens' => $this->useJwtToken,//ADDED
                    'token_param_name' => $this->tokenParamName,
                    'access_lifetime' => $this->tokenAccessLifetime,
                    /** add more ... */
                ]), $this->options),
                $grantTypes
            ]);

            $this->set('server', $server);
        }
        return $this->get('server');
    }
    
    public function getRequest()
    {
        if(!$this->has('request')) {
            $this->set('request', Request::createFromGlobals());
        }
        return $this->get('request');
    }
    
    public function getResponse()
    {
        if(!$this->has('response')) {
            $this->set('response', new Response());
        }
        return $this->get('response');
    }

    /**
     * Register translations for this module
     * 
     * @return array
     */
    public function registerTranslations()
    {
        if(!isset(Yii::$app->get('i18n')->translations['modules/oauth2/*'])) {
            Yii::$app->get('i18n')->translations['modules/oauth2/*'] = [
                'class'    => PhpMessageSource::className(),
                'basePath' => __DIR__ . '/messages',
            ];
        }
    }
    
    /**
     * Translate module message
     * 
     * @param string $category
     * @param string $message
     * @param array $params
     * @param string $language
     * @return string
     */
    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('modules/oauth2/' . $category, $message, $params, $language);
    }
}
