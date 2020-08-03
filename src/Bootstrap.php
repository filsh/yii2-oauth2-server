<?php

namespace filsh\yii2\oauth2server;

/**
 * Instead use bootstrap module
 * should be removed in v2.1 version
 * 
 * @deprecated v2.0.1
 */
class Bootstrap implements \yii\base\BootstrapInterface
{
    use BootstrapTrait;
    
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        /** @var $module Module */
        if ($app->hasModule('oauth2') && ($module = $app->getModule('oauth2')) instanceof Module) {
            $this->initModule($module);
            
            if ($app instanceof \yii\console\Application) {
                $module->controllerNamespace = 'filsh\yii2\oauth2server\commands';
            }
        }
    }
}
