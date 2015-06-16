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
        $oauthServer = Yii::$app->getModule('oauth2')->getServer();
        $oauthRequest = Yii::$app->getModule('oauth2')->getRequest();
        $oauthServer->verifyResourceRequest($oauthRequest);
        
        return parent::beforeAction($action);
    }
}