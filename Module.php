<?php

namespace filsh\yii2\oauth2server;

use \Yii;

/**
 * For example,
 * 
 * ```php
 * 'oauth2' => [
 *     'class' => 'filsh\yii2\oauth2server\Module',
 *     'options' => [
 *         'token_param_name' => 'accessToken',
 *         'access_lifetime' => 3600
 *     ],
 *     'storageMap' => [
 *         'user_credentials' => 'common\models\User'
 *     ],
 *     'storageOptions' => [
 *         'refresh_token' => [
 *             'always_issue_new_refresh_token' => true
 *         ]
 *     ]
 * ]
 * ```
 */
class Module extends \yii\base\Module
{
    public $options = [];
    
    public $storageMap = [];
    
    public $storageOptions = [];
    
    public $storageDefault = 'filsh\yii2\oauth2server\storage\Pdo';
    
    public $modelClasses = [];
    
    public $i18n;

    private $_server;
    
    private $_models = [];
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->modelClasses = array_merge($this->getDefaultModelClasses(), $this->modelClasses);
        $this->registerTranslations();
    }
    
    /**
     * Get oauth2 server instance
     * @param type $force
     * @return \OAuth2\Server
     */
    public function getServer($force = false)
    {
        if($this->_server === null || $force === true) {
            $storages = $this->createStorages();
            $server = new \OAuth2\Server($storages, $this->options);
            
            $server->addGrantType(new \OAuth2\GrantType\UserCredentials($storages['user_credentials']));
            $server->addGrantType(new \OAuth2\GrantType\RefreshToken($storages['refresh_token'], $this->getStorageOptions('refresh_token')));
            
            $this->_server = $server;
        }
        return $this->_server;
    }
    
    /**
     * Get oauth2 request instance from global variables
     * @return \OAuth2\Request
     */
    public function getRequest()
    {
        return \OAuth2\Request::createFromGlobals();
    }
    
    /**
     * Get oauth2 response instance
     * @return \OAuth2\Response
     */
    public function getResponse()
    {
        return new \OAuth2\Response();
    }
    
    /**
     * Create storages
     * @return type
     */
    public function createStorages()
    {
        $connection = Yii::$app->getDb();
        if(!$connection->getIsActive()) {
            $connection->open();
        }
        
        $storages = [];
        foreach($this->storageMap as $name => $storage) {
            $storages[$name] = Yii::createObject($storage);
        }
        
        $defaults = [
            'access_token',
            'authorization_code',
            'client_credentials',
            'client',
            'refresh_token',
            'user_credentials',
            'public_key',
            'jwt_bearer',
            'scope',
        ];
        foreach($defaults as $name) {
            if(!isset($storages[$name])) {
                $storages[$name] = Yii::createObject($this->storageDefault);
            }
        }
        
        return $storages;
    }
    
    /**
     * Get object instance of model
     * @param string $name
     * @param array $config
     * @return ActiveRecord
     */
    public function model($name, $config = [])
    {
        if(!isset($this->_models[$name])) {
            $className = $this->modelClasses[ucfirst($name)];
            $this->_models[$name] = Yii::createObject(array_merge(['class' => $className], $config));
        }
        return $this->_models[$name];
    }
    
    /**
     * Register translations for this module
     * @return array
     */
    public function registerTranslations()
    {
        Yii::setAlias('@oauth2server', dirname(__FILE__));
        if (empty($this->i18n)) {
            $this->i18n = [
                'class' => 'yii\i18n\PhpMessageSource',
                'basePath' => '@oauth2server/messages',
            ];
        }
        Yii::$app->i18n->translations['oauth2server'] = $this->i18n;
    }

    /**
     * Get default model classes
     * @return array
     */
    protected function getDefaultModelClasses()
    {
        return [
            'Clients' => 'filsh\yii2\oauth2server\models\OauthClients',
            'AccessTokens' => 'filsh\yii2\oauth2server\models\OauthAccessTokens',
            'AuthorizationCodes' => 'filsh\yii2\oauth2server\models\OauthAuthorizationCodes',
            'RefreshTokens' => 'filsh\yii2\oauth2server\models\OauthRefreshTokens',
            'Scopes' => 'filsh\yii2\oauth2server\models\OauthScopes',
        ];
    }
    
    /**
     * Get storage options by name
     * @param string $name name of storage name
     * @param array $default default options
     * @return array
     */
    protected function getStorageOptions($name, $default = [])
    {
        $options = isset($this->storageOptions[$name]) ? $this->storageOptions[$name] : [];
        return array_merge($default, $options);
    }
}