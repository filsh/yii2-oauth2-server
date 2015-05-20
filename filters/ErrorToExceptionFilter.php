<?php

namespace filsh\yii2\oauth2server\filters;

use Yii;
use yii\base\Controller;

class ErrorToExceptionFilter extends \yii\base\Behavior
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

        $isValid = true;
        if($response !== null) {
            $isValid = $response->isInformational() || $response->isSuccessful() || $response->isRedirection();
        }
        if(!$isValid) {
            $status = $response->getStatusCode();
            // TODO: необходимо также пробрасывать error_uri
            $message = Yii::t('oauth2server', $response->getParameter('error_description'));
            if($message === null) {
                $message = Yii::t('yii', 'An internal server error occurred.');
            }
            throw new \yii\web\HttpException($status, $message);
        }
    }
}
