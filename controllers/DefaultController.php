<?php

namespace filsh\yii2\oauth2server\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use filsh\yii2\oauth2server\filters\ErrorToExceptionFilter;

class DefaultController extends \yii\rest\Controller
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
        $server = $this->module->getServer();
        $request = $this->module->getRequest();
        $response = $server->handleTokenRequest($request);

		//Set cache control headers
		Yii::$app->response->headers['Cache-Control'] = 'no-cache';
		Yii::$app->response->headers['Pragma'] = 'no-cache';


		return $response->getParameters();
    }
}