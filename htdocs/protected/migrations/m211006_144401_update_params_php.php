<?php

class m211006_144401_update_params_php extends CDbMigration
{
    const CONFIG_TYPE_2 = 2; // production
    private $_config_type;

    private $_root_path;

    public function up()
    {
        $this->initConfigType();
        $this->initRootPath();

        echo 'Start Up' . PHP_EOL;

        if($this->_config_type == false){
            echo 'End Up' . PHP_EOL;

            return;
        }
        switch($this->_config_type){
            case self::CONFIG_TYPE_2:
                $this->upType2();
                break;
        }

        echo 'End Up' . PHP_EOL;
    }

	public function down()
	{
		echo "m211006_144401_update_params_php does not support migration down.\n";
		return false;
	}


    private function upType2()
    {
        $config_general = $this->getConfigGeneral();
        file_put_contents($this->_root_path . DIRECTORY_SEPARATOR . 'config'. DIRECTORY_SEPARATOR .'params.php', '<?php ' . $config_general);
    }

    private function initConfigType()
    {
        $path_current = Yii::app()->basePath . DIRECTORY_SEPARATOR . 'config';
        echo 'path_current is - ' . $path_current . PHP_EOL;
        if(file_exists($path_current)){
            echo 'is production config' . PHP_EOL;
            $this->_config_type = self::CONFIG_TYPE_2;
        } else{
            echo 'is general config' . PHP_EOL;

            return;
        }

        echo 'set content_type = "' . $this->_config_type . '"' . PHP_EOL;
    }

    private function initRootPath()
    {
        switch($this->_config_type){
            case self::CONFIG_TYPE_2:
                $this->_root_path = Yii::app()->basePath;
                break;
        }
    }

    private function getConfigGeneral()
    {
        switch($this->_config_type){
            case self::CONFIG_TYPE_2:
                return $this->getConfigGeneralType2();
        }
    }

    // for Production
    private function getConfigGeneralType2() {
        return <<<END
return
    [
        'params' => [
            'reg_background_url' => '',
            'global'             => [],
            'db'                 => [
                'set_default_timezone' => true,
                'time_zone'            => '-03:00',
            ],
            'cache'              => [
                'db' => [
                    'enabled'   => false,
                    'duration'  => 3600,
                    'ar_models' => [
                        'ExtensionCopyModel',
                        'ModuleTablesModel',
                    ],
                ],
            ],
            'reports'            => [
                'logging_query' => false,
            ],
            'process'            => [
                'start_time_log_enabled' => false,
            ],
            'console'            => [
                'back_up_db' => [
                    'path'             => '/var/backups_crm/',
                    'filename_pattern' => 'db_<date>.sql.bz2',
                    'max_files_count'  => 10,
                ],
            ],
            'noindex'            => true
        ],
    ];
END;
    }
}