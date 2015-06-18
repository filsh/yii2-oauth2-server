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
    
    public $grantTypes = [];
    
    public $tokenParamName;
    
    public $tokenAccessLifetime;
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->registerComponents();
        $this->registerTranslations();
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
    
    protected function registerComponents()
    {
        $this->setComponents([
            'server' => $this->createServer(),
            'request' => Request::createFromGlobals(),
            'response' => new Response()
        ]);
    }
    
    /**
     * Register translations for this module
     * 
     * @return array
     */
    protected function registerTranslations()
    {
        if(!isset(Yii::$app->get('i18n')->translations['modules/oauth2/*'])) {
            Yii::$app->get('i18n')->translations['modules/oauth2/*'] = [
                'class'    => PhpMessageSource::className(),
                'basePath' => __DIR__ . '/messages',
            ];
        }
    }
    
    protected function createServer()
    {
        $storages = [];
        foreach(array_keys($this->storageMap) as $name) {
            $storages[$name] = \Yii::$container->get($name);
        }
        $server = \Yii::$container->get(Server::className(), [
            $storages,
            [
                'token_param_name' => $this->tokenParamName,
                'access_lifetime' => $this->tokenAccessLifetime,
                /** add more ... */
            ]
        ]);

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
        
        return $server;
    }
}
