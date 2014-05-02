<?php

namespace filsh\yii2\oauth2server\filters\auth;

use \Yii;

class CompositeAuth extends \yii\filters\auth\CompositeAuth
{
    public function authenticate($user, $request, $response)
    {
        $oauthsServer = Yii::$app->getModule('oauth2')->getServer();
        $oauthRequest = Yii::$app->getModule('oauth2')->getRequest();
        if ($oauthsServer->verifyResourceRequest($oauthRequest)) {
            return parent::authenticate($user, $request, $response);
        }
        
        return null;
    }
}