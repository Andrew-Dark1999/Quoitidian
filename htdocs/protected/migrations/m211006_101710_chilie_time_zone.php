<?php

class m211006_101710_chilie_time_zone extends CDbMigration
{
	public function up()
	{
        $this->update('{{params}}', ['value' => '25'], 'title = "time_zones_id"');
    }

	public function down()
	{
        return false;
	}

}
