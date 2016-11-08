<?php

namespace filsh\yii2\oauth2server\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use filsh\yii2\oauth2server\filters\ErrorToExceptionFilter;

class RestController extends \yii\rest\Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'exceptionFilter' => [
                'class' => ErrorToExceptionFilter::className()
            ],
        ]);
    }

    public function actionToken()
    {
        /** @var $response \OAuth2\Response */
        $response = $this->module->getServer()->handleTokenRequest();
        return $response->getParameters();
    }
    
    public function actionRevoke()
    {
        /** @var $response \OAuth2\Response */
        $response = $this->module->getServer()->handleRevokeRequest();
        return $response->getParameters();
    }

    public function actionUserInfo()
    {
        $response = $this->module->getServer()->handleUserInfoRequest();
        return $response->getParameters();
    }
}
