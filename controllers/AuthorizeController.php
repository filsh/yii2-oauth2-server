<?php

namespace dixonsatit\yii2\oauth2server\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use dixonsatit\yii2\oauth2server\filters\ErrorToExceptionFilter;
use dixonsatit\yii2\oauth2server\models\AuthorizeForm;
use dixonsatit\yii2\oauth2server\models\OauthClients;

class AuthorizeController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['authorize'],
                'rules' => [
                    [
                        'actions' => ['authorize'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ]
        ];
    }

    public function actionAuthorize()
    {
        $is_authorized = 'no';
        $request  = $this->module->getRequest();
        $response = $this->module->getResponse();

        // validate the authorize request
        if (!$this->module->getServer()->validateAuthorizeRequest($request, $response)) {
            $response->send();
            die;
        }

        $client = OauthClients::findOne(Yii::$app->request->get('client_id'));
        $model = new AuthorizeForm();

        if (Yii::$app->request->isPost) {

          $is_authorized = Yii::$app->request->post('authorized-button') == 'yes' ? true:false;

          $this->module->getServer()->handleAuthorizeRequest($request, $response, $is_authorized, Yii::$app->user->id);
          // if($is_authorized){
          //   $code = substr($response->getHttpHeader('Location'), strpos($response->getHttpHeader('Location'), 'code=')+5, 40);
          //   //exit("SUCCESS! Authorization Code: $code");
          // }
          return $response->send();
        } else {

            return $this->render('authorize', [
                'model' => $model,
                'client'=> $client
            ]);
        }
    }
}
