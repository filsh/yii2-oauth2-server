<?php

namespace filsh\yii2\oauth2server\storage;

use Yii;

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

        $this->config = array_merge(array(
            'client_table' => Yii::$app->db->tablePrefix.'oauth_clients',
            'access_token_table' => Yii::$app->db->tablePrefix.'oauth_access_tokens',
            'refresh_token_table' => Yii::$app->db->tablePrefix.'oauth_refresh_tokens',
            'code_table' => Yii::$app->db->tablePrefix.'oauth_authorization_codes',
            'user_table' => Yii::$app->db->tablePrefix.'oauth_users',
            'jwt_table'  => Yii::$app->db->tablePrefix.'oauth_jwt',
            'jti_table'  => Yii::$app->db->tablePrefix.'oauth_jti',
            'scope_table'  => Yii::$app->db->tablePrefix.'oauth_scopes',
            'public_key_table'  => Yii::$app->db->tablePrefix.'oauth_public_keys',
        ), $config);
    }
}
