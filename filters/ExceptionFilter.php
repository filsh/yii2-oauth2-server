<?php

namespace filsh\yii2\oauth2server\filters;

use Yii;
use yii\base\Controller;

class ExceptionFilter extends yii\base\Behavior
{
    public function events()
    {
        return [Controller::EVENT_AFTER_ACTION => 'afterAction'];
    }
    
    /**
     * @param ActionEvent $event
     * @return boolean
     * @throws HttpException when the request method is not allowed.
     */
    public function afterAction($event)
    {
        $response = Yii::$app->getModule('oauth2')->getServer()->getResponse();
        if($response !== null && !$response->isSuccessful()) {
            $status = $response->getStatusCode();
            // TODO: необходимо также пробрасывать error_uri
            $message = $response->getParameter('error_description');
            if($message === null) {
                $message = P::t('yii', 'An internal server error occurred.');
            }
            throw new \yii\web\HttpException($status, $message);
        }
    }
}