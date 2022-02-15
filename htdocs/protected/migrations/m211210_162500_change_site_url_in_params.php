<?php

class m211210_162500_change_site_url_in_params extends CDbMigration
{
	public function up()
	{
        $siteUrl = (new DataModel())
            ->setText('SELECT value FROM {{params}} where title = "site_url"')
            ->findScalar();

        if(!$siteUrl){
            return;
        }

        $this->update('{{params}}', ['value' => str_replace('http', 'https', $siteUrl)], 'title = "site_url"');
	}

	public function down()
	{
		echo "m211119_130956_change_site_url_in_params does not support migration down.\n";
		return false;
	}
}
