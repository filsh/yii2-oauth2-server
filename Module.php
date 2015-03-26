<?php

namespace mobilejazz\yii2\oauth2server;

use \Yii;

class Module extends \yii\base\Module
{
    public $options = [];
    
    public $storageMap = [];
    
    public $storageDefault = 'mobilejazz\yii2\oauth2server\storage\Pdo';
    
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
    
    public function getServer($force = false)
    {
        if($this->_server === null || $force === true) {
            $storages = $this->createStorages();
            $server = new \OAuth2\Server($storages, $this->options);
            
            $server->addGrantType(new \OAuth2\GrantType\UserCredentials($storages['user_credentials']));
            $server->addGrantType(new \OAuth2\GrantType\RefreshToken($storages['refresh_token'], [
                'always_issue_new_refresh_token' => true
            ]));
			$server->addGrantType(new \OAuth2\GrantType\ClientCredentials($storages['client_credentials']));
            $server->addGrantType(new FacebookAuth($storages['public_key']));

            $this->_server = $server;
        }
        return $this->_server;
    }
    
    public function getRequest()
    {
        return \OAuth2\Request::createFromGlobals();
    }
    
    public function getResponse()
    {
        return new \OAuth2\Response();
    }
    
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
            'scope'
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
        // return object if already created
        if(!empty($this->_models[$name])) {
            return $this->_models[$name];
        }

        // create object
        $className = $this->modelClasses[ucfirst($name)];
        $this->_models[$name] = Yii::createObject(array_merge(["class" => $className], $config));
        return $this->_models[$name];
    }
    
    /**
     * Register translations for this module
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
     */
    protected function getDefaultModelClasses()
    {
        return [
            'Clients' => 'mobilejazz\yii2\oauth2server\models\OauthClients',
            'AccessTokens' => 'mobilejazz\yii2\oauth2server\models\OauthAccessTokens',
            'AuthorizationCodes' => 'mobilejazz\yii2\oauth2server\models\OauthAuthorizationCodes',
            'RefreshTokens' => 'mobilejazz\yii2\oauth2server\models\OauthRefreshTokens',
            'Scopes' => 'mobilejazz\yii2\oauth2server\models\OauthScopes',
        ];
    }
    
}