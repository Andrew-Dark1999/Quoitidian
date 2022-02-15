<?php

class m210928_085724_russian_locale_update extends CDbMigration
{
	public function up()
	{
        $this->update('{{language}}',[
            'active' => 0,
        ], 'name="ru"');

        $this->update('{{users_params}}',[
            'language' => 'es',
        ], 'language="ru"');
        $this->update('{{params}}', ['value' => 'es'], 'title = "language"');
	}

	public function down()
	{
		echo "m210928_085724_russian_locale_update does not support migration down.\n";
		return false;
	}
}
