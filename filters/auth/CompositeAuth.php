<?php

namespace mobilejazz\yii2\oauth2server\filters\auth;

use \Yii;

class CompositeAuth extends \yii\filters\auth\CompositeAuth
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        //We call this before calling Yii::$app->getModule('oauth2')->getRequest(); because this method
        //cleans the request body
        $body = Yii::$app->getRequest()->getBodyParams();

        $oauthServer = Yii::$app->getModule('oauth2')->getServer();
        $oauthRequest = Yii::$app->getModule('oauth2')->getRequest();
        $oauthServer->verifyResourceRequest($oauthRequest);

        return parent::beforeAction($action);
    }
}