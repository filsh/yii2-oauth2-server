<?php

namespace filsh\yii2\oauth2server\filters\auth;

use \Yii;

class CompositeAuth extends \yii\filters\auth\CompositeAuth
{

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $module = $this->getModuleNested('oauth2', Yii::$app);

        $server = $module->getServer();
        $server->verifyResourceRequest();

        return parent::beforeAction($action);
    }

    public function getModuleNested($needle, $app)
    {
        /** @var $module Module */
        if (($module = $app->getModule($needle)) !== null)
            return $module;

        foreach ($app->getModules() as $id => $module) {
            $server = $app->getModule($id)->getModule($needle);
            if ($server != null) {
                return $server;
            } else {
                $this->getModuleNested($module->getModules());
            }
        }

        return false;
    }

}
