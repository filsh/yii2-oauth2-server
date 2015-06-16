<?php

namespace filsh\yii2\oauth2server;

use \Yii;
use yii\i18n\PhpMessageSource;

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
 *     'grantTypes' => [
 *         'client_credentials' => [
 *             'class' => '\OAuth2\GrantType\ClientCredentials',
 *             'allow_public_clients' => false
 *         ],
 *         'user_credentials' => [
 *             'class' => '\OAuth2\GrantType\UserCredentials'
 *         ],
 *         'refresh_token' => [
 *             'class' => '\OAuth2\GrantType\RefreshToken',
 *             'always_issue_new_refresh_token' => true
 *         ]
 *     ],
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
    
    
    
    
    public $options = [];
    
    public $grantTypes = [];
    
    private $_server;

    private $_request;
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
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
            $storages = [];
            foreach($this->storageMap as $name => $value) {
                $storages[$name] = \Yii::$container->get($name);
            }
            $server = new \OAuth2\Server($storages, $this->options);
            
            foreach($this->grantTypes as $name => $options) {
                if(!isset($storages[$name]) || empty($options['class'])) {
                    throw new \yii\base\InvalidConfigException('Invalid grant types configuration.');
                }
                
                $class = $options['class'];
                unset($options['class']);
                
                $reflection = new \ReflectionClass($class);
                $config = array_merge([0 => $storages[$name]], [$options]);

                $instance = $reflection->newInstanceArgs($config);
                $server->addGrantType($instance);
            }
            
            $this->_server = $server;
        }
        return $this->_server;
    }
    
    /**
     * Get oauth2 request instance from global variables
     * @return \OAuth2\Request
     */
    public function getRequest($force = false)
    {
        if ($this->_request === null || $force) {
            $this->_request = \OAuth2\Request::createFromGlobals();
        };
        return $this->_request;
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
     * Register translations for this module
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
    
    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('modules/oauth2/' . $category, $message, $params, $language);
    }
}
