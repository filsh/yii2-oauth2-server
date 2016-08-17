<?php

namespace dixonsatit\yii2\oauth2server;

trait BootstrapTrait
{
    /**
     * @var array Model's map
     */
    private $_modelMap = [
        'OauthClients'               => 'dixonsatit\yii2\oauth2server\models\OauthClients',
        'OauthAccessTokens'          => 'dixonsatit\yii2\oauth2server\models\OauthAccessTokens',
        'OauthAuthorizationCodes'    => 'dixonsatit\yii2\oauth2server\models\OauthAuthorizationCodes',
        'OauthRefreshTokens'         => 'dixonsatit\yii2\oauth2server\models\OauthRefreshTokens',
        'OauthScopes'                => 'dixonsatit\yii2\oauth2server\models\OauthScopes',
    ];

    /**
     * @var array Storage's map
     */
    private $_storageMap = [
        'access_token'          => 'dixonsatit\yii2\oauth2server\storage\Pdo',
        'authorization_code'    => 'dixonsatit\yii2\oauth2server\storage\Pdo',
        'client_credentials'    => 'dixonsatit\yii2\oauth2server\storage\Pdo',
        'client'                => 'dixonsatit\yii2\oauth2server\storage\Pdo',
        'refresh_token'         => 'dixonsatit\yii2\oauth2server\storage\Pdo',
        'user_credentials'      => 'dixonsatit\yii2\oauth2server\storage\Pdo',
        'public_key'            => 'dixonsatit\yii2\oauth2server\storage\Pdo',
        'jwt_bearer'            => 'dixonsatit\yii2\oauth2server\storage\Pdo',
        'scope'                 => 'dixonsatit\yii2\oauth2server\storage\Pdo',
    ];

    protected function initModule(Module $module)
    {
        $this->_modelMap = array_merge($this->_modelMap, $module->modelMap);
        foreach ($this->_modelMap as $name => $definition) {
            \Yii::$container->set("dixonsatit\\yii2\\oauth2server\\models\\" . $name, $definition);
            $module->modelMap[$name] = is_array($definition) ? $definition['class'] : $definition;
        }

        $this->_storageMap = array_merge($this->_storageMap, $module->storageMap);
        foreach ($this->_storageMap as $name => $definition) {
            \Yii::$container->set($name, $definition);
            $module->storageMap[$name] = is_array($definition) ? $definition['class'] : $definition;
        }
    }
}
