<?php

namespace filsh\yii2\oauth2server;

use \Yii;

class Module extends \yii\base\Module
{
    public $options = [];
    
    public $storageMap = [];
    
    public $storageDefault = 'filsh\yii2\oauth2server\storage\Pdo';
    
    public $modelClasses = [];
    
    private $_server;
    
    private $_models = [];
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->modelClasses = array_merge($this->getDefaultModelClasses(), $this->modelClasses);
    }
    
    public function getServer($force = false)
    {
        if($this->_server === null || $force === true) {
            $storages = $this->createStorage();
            $server = new \OAuth2\Server($storages, $this->options);
            
            $server->addGrantType(new \OAuth2\GrantType\UserCredentials($storages['user_credentials']));
            
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
    
    protected function createStorage()
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
     * Get default model classes
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
    
}