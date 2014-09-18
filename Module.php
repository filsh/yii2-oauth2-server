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
            $storages = $this->createStorages();
            $server = new \OAuth2\Server($storages, $this->options);
            
            $server->addGrantType(new \OAuth2\GrantType\AuthorizationCode($storages['authorization_code']));
            $server->addGrantType(new \OAuth2\GrantType\UserCredentials($storages['user_credentials']));
            $server->addGrantType(new \OAuth2\GrantType\RefreshToken($storages['refresh_token'], [
                'always_issue_new_refresh_token' => true
            ]));
            
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

    /**
     * Set status, headers of current \yii\web\Response by given \OAuth2\Response's, return Response's data.
     *
     * @param \OAuth2\Response $response
     * @param boolean $throwException whether to throw an exception if the given alias is invalid.
     * @return mixed
     * @throws \yii\web\HttpException if response status should throw a http exception while $throwException is true.
     */
    public function setResponse($response, $throwException = true)
    {
        $status = $response->getStatusCode();

        $yiiResponse = Yii::$app->getResponse();
        $yiiResponse->setStatusCode($status, $response->getStatusText());

        foreach ($response->getHttpHeaders() as $name => $header) {
            $yiiResponse->getHeaders()->set($name, $header);
        }

        if ($throwException) {
            $isValid = $response->isInformational() || $response->isSuccessful() || $response->isRedirection();
            if(!$isValid) {
                // TODO: необходимо также пробрасывать error_uri
                $message = $response->getParameter('error_description');
                if($message === null) {
                    $message = Yii::t('yii', 'An internal server error occurred.');
                }
                throw new \yii\web\HttpException($status, $message);
            }
        }

        return $response->getParameters();
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