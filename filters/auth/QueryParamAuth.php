<?php

namespace filsh\yii2\oauth2server\filters\auth;

use \Yii;

class QueryParamAuth extends \yii\filters\auth\QueryParamAuth
{
    /**
     * @inheritdoc
     */
    public $tokenParam = 'access_token';
    
    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $oauthsServer = Yii::$app->getModule('oauth2')->getServer();
        $oauthRequest = Yii::$app->getModule('oauth2')->getRequest();
        if (!$oauthsServer->verifyResourceRequest($oauthRequest)) {
            $this->handleFailure($response);
        } else {
            return parent::authenticate($user, $request, $response);
        }
    }
}