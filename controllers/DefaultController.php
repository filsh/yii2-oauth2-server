<?php

namespace filsh\yii2\oauth2server\controllers;

use \Yii;
use yii\helpers\ArrayHelper;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;

use filsh\yii2\oauth2server\filters\ExceptionFilter;
use filsh\yii2\oauth2server\filters\auth\QueryParamAuth;

class DefaultController extends \yii\rest\Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'authenticator' => [
                'class' => CompositeAuth::className(),
                'authMethods' => [
                    HttpBearerAuth::className(),
                    QueryParamAuth::className(),
                ],
                'except' => ['token']
            ],
            'exceptionFilter' => [
                'class' => ExceptionFilter::className()
            ],
        ]);
    }
    
    public function actionToken()
    {
        $server = Yii::$app->getModule('oauth2')->getServer();
        $request = Yii::$app->getModule('oauth2')->getRequest();
        $response = $server->handleTokenRequest($request);
        
        return $response->getParameters();
    }
    
    public function actionResource()
    {
        $server = Yii::$app->getModule('oauth2')->getServer();
        $request = Yii::$app->getModule('oauth2')->getRequest();
        
        if (!$server->verifyResourceRequest($request)) {
            return $server->getResponse()->getParameters();
        }
        
        return ['success' => true, 'message' => 'You accessed my APIs!'];
    }
}