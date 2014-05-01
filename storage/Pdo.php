<?php

namespace filsh\yii2\oauth2server\storage;

class Pdo extends \OAuth2\Storage\Pdo
{
    public $dsn;
    
    public $username;
    
    public $password;
    
    public $connection = 'db';
    
    public function __construct($connection = null, $config = array())
    {
        if($connection === null) {
            if(!empty($this->connection)) {
                $connection = \Yii::$app->get($this->connection);
                if(!$connection->getIsActive()) {
                    $connection->open();
                }
                $connection = $connection->pdo;
            } else {
                $connection = [
                    'dsn' => $this->dsn,
                    'username' => $this->username,
                    'password' => $this->password
                ];
            }
        }
        
        parent::__construct($connection, $config);
    }
}