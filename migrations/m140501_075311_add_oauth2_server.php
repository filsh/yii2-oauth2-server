<?php

use yii\db\Schema;

class m140501_075311_add_oauth2_server extends \yii\db\Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $transaction = $this->db->beginTransaction();
        try {
            $this->createTable('{{%oauth_clients}}', [
                'client_id' => Schema::TYPE_STRING . '(32) NOT NULL',
                'client_secret' => Schema::TYPE_STRING . '(32) DEFAULT NULL',
                'redirect_uri' => Schema::TYPE_STRING . '(1000) NOT NULL',
                'grant_types' => Schema::TYPE_STRING . '(100) NOT NULL',
                'scope' => Schema::TYPE_STRING . '(2000) DEFAULT NULL',
                'user_id' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
                'PRIMARY KEY (`client_id`)'
            ], $tableOptions);
            
            $this->createTable('{{%oauth_access_tokens}}', [
                'access_token' => Schema::TYPE_STRING . '(40) NOT NULL',
                'client_id' => Schema::TYPE_STRING . '(32) NOT NULL',
                'user_id' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
                'expires' => Schema::TYPE_TIMESTAMP . ' NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                'scope' => Schema::TYPE_STRING . '(2000) DEFAULT NULL',
                'PRIMARY KEY (`access_token`)',
                'FOREIGN KEY (`client_id`) REFERENCES {{%oauth_clients}} (`client_id`) ON DELETE CASCADE ON UPDATE CASCADE',
            ], $tableOptions);
            
            $this->createTable('{{%oauth_refresh_tokens}}', [
                'refresh_token' => Schema::TYPE_STRING . '(40) NOT NULL',
                'client_id' => Schema::TYPE_STRING . '(32) NOT NULL',
                'user_id' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
                'expires' => Schema::TYPE_TIMESTAMP . ' NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                'scope' => Schema::TYPE_STRING . '(2000) DEFAULT NULL',
                'PRIMARY KEY (`refresh_token`)',
                'FOREIGN KEY (`client_id`) REFERENCES {{%oauth_clients}} (`client_id`) ON DELETE CASCADE ON UPDATE CASCADE',
            ], $tableOptions);
            
            $this->createTable('{{%oauth_authorization_codes}}', [
                'authorization_code' => Schema::TYPE_STRING . '(40) NOT NULL',
                'client_id' => Schema::TYPE_STRING . '(32) NOT NULL',
                'user_id' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
                'redirect_uri' => Schema::TYPE_STRING . '(1000) NOT NULL',
                'expires' => Schema::TYPE_TIMESTAMP . ' NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                'scope' => Schema::TYPE_STRING . '(2000) DEFAULT NULL',
                'PRIMARY KEY (`authorization_code`)',
                'FOREIGN KEY (`client_id`) REFERENCES {{%oauth_clients}} (`client_id`) ON DELETE CASCADE ON UPDATE CASCADE',
            ], $tableOptions);
            
            $this->createTable('{{%oauth_scopes}}', [
                'scope' => Schema::TYPE_STRING . '(2000) NOT NULL',
                'is_default' => Schema::TYPE_BOOLEAN . ' NOT NULL',
            ], $tableOptions);
            
            $this->createTable('{{%oauth_jwt}}', [
                'client_id' => Schema::TYPE_STRING . '(32) NOT NULL',
                'subject' => Schema::TYPE_STRING . '(80) DEFAULT NULL',
                'public_key' => Schema::TYPE_STRING . '(2000) DEFAULT NULL',
                'PRIMARY KEY (`client_id`)',
            ], $tableOptions);
            
            $this->createTable('{{%oauth_users}}', [
                'username' => Schema::TYPE_STRING . '(255) NOT NULL',
                'password' => Schema::TYPE_STRING . '(2000) DEFAULT NULL',
                'first_name' => Schema::TYPE_STRING . '(255) DEFAULT NULL',
                'last_name' => Schema::TYPE_STRING . '(255) DEFAULT NULL',
                'PRIMARY KEY (`username`)',
            ], $tableOptions);
            
            // insert client data
            $this->batchInsert('{{%oauth_clients}}', ['client_id', 'client_secret', 'redirect_uri', 'grant_types'], [
                ['testclient', 'testpass', 'http://fake/', 'client_credentials authorization_code password implicit'],
            ]);
            
            $transaction->commit();
        } catch (Exception $e) {
            echo 'Exception: ' . $e->getMessage() . '\n';
            $transaction->rollback();

            return false;
        }

        return true;
    }

    public function down()
    {
        $transaction = $this->db->beginTransaction();
        try {
            $this->dropTable('{{%oauth_users}}');
            $this->dropTable('{{%oauth_jwt}}');
            $this->dropTable('{{%oauth_scopes}}');
            $this->dropTable('{{%oauth_authorization_codes}}');
            $this->dropTable('{{%oauth_refresh_tokens}}');
            $this->dropTable('{{%oauth_access_tokens}}');
            $this->dropTable('{{%oauth_clients}}');
            
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollback();
            echo $e->getMessage();
            echo "\n";
            echo get_called_class() . ' cannot be reverted.';
            echo "\n";

            return false;
        }

        return true;
    }
}
