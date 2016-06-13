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
        $optional = $event->action->controller->getBehavior('authenticator')->optional;
        $currentAction = $event->action->id;
        $isValid = true;
        if (!in_array($currentAction, $optional)) {
            if ($response !== null) {
                $isValid = $response->isInformational() || $response->isSuccessful() || $response->isRedirection();
            }
            if (!$isValid) {
                throw new HttpException($response->getStatusCode(), $this->getErrorMessage($response),
                    $response->getParameter('error_uri'));
            }
        }
    }
}
