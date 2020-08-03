<?php

namespace filsh\yii2\oauth2server\exceptions;

class HttpException extends \yii\web\HttpException
{
    /**
     * @var string Uri for details of exception
     */
    public $errorUri;

    /**
     * Constructor.
     * @param integer $status HTTP status code, such as 404, 500, etc.
     * @param string $message error message
     * @param string $errorUri error uri
     * @param integer $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($status, $message = null, $errorUri = null, $code = 0, \Exception $previous = null)
    {
        $this->errorUri = $errorUri;
        parent::__construct($status, $message, $code, $previous);
    }
}