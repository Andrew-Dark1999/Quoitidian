<?php

class m211210_140821_clear_migration_table extends CDbMigration
{
    public function up()
    {
        $this->delete(
            '{{yii_migration}}',
            'version = "m230415_151700_params_changed_emails"'
        );
        $this->delete(
            '{{yii_migration}}',
            'version = "m230423_131621_translate_to_en"'
        );
    }

    public function down()
    {
        echo "m211210_140821_clear_migration_table does not support migration down.\n";

        return false;
    }
}
