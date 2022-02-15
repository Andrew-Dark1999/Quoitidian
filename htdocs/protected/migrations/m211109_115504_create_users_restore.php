<?php

class m211109_115504_create_users_restore extends CDbMigration
{
    public function up()
    {
        $this->createTable('users_restore', array(
            'id' => 'int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'users_id'      => 'integer(11) NOT NULL',
            'token' => 'varchar(32) NOT NULL',
            'created_at'=> 'datetime',
        ));
        $this->addForeignKey('fk-users_restore-id', 'users_restore', 'users_id',
            'users', 'users_id', 'CASCADE', 'CASCADE');
    }

	public function down()
	{
		echo "m211109_115504_create_users_restore does not support migration down.\n";
		return false;
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}