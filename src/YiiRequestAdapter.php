<?php


namespace filsh\yii2\oauth2server;


use OAuth2\RequestInterface;

class YiiRequestAdapter implements RequestInterface
{
    private $request;

    public function __construct(\yii\web\Request $request)
    {
        $this->request = $request;
    }

    public function query($name, $default = null)
    {
        return $this->request->get($name, $default);
    }

    public function request($name, $default = null)
    {
        return $this->request->post($name, $default);
    }

    public function server($name, $default = null)
    {
        return isset($_SERVER[$name]) ? $_SERVER[$name] : $default;
    }

    public function headers($name, $default = null)
    {
        return $this->request->headers->get($name, $default);
    }

    public function getAllQueryParameters()
    {
        return $this->request->queryParams;
    }
}