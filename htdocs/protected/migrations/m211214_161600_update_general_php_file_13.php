<?php

class m211214_161600_update_general_php_file_13 extends CDbMigration
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
        echo "m211214_161600_update_general_php_file_13 does not support migration down.\n";

        return false;
    }

    private function upType2()
    {
        $config_general = $this->getConfigGeneral();
        file_put_contents($this->_root_path . DIRECTORY_SEPARATOR . 'shared-content' . DIRECTORY_SEPARATOR . 'general.php', '<?php ' . $config_general);
    }

    private function initConfigType()
    {
        $path_current = Yii::app()->basePath . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'current';
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
                $this->_root_path = Yii::app()->basePath . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..';
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
    private function getConfigGeneralType2()
    {
        return <<<EOF
return
    [
        'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'current' . DIRECTORY_SEPARATOR . 'htdocs' . DIRECTORY_SEPARATOR . 'protected',
        'name' => 'CRM',
        'language' => 'en',
        'import' => [],
        'preload' => ['log'],
        'components' => [
            'user' => [
                'loginUrl' => ['login'],
            ],
            'authManager' => [
                'class' => 'CDbAuthManager',
                'connectionID' => 'db',
            ],
            'urlManager' => [
                    'urlFormat' => 'path',
                    'showScriptName' => false,
                ] + require(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'current' . DIRECTORY_SEPARATOR . 'htdocs' . DIRECTORY_SEPARATOR . 'protected' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . "router.php"),
            'db' => [
                'class' => 'application.components.db.DbConnection',
                'emulatePrepare' => true,
                'charset' => 'utf8',
            ],
            /*
            'cache'=>array(
                'class'=>'system.caching.CMemCache',
                'servers'=>array(
                    array('host'=>'localhost', 'port'=>11211),
                ),
            ),
            */
            'log' => [
                'class' => 'CLogRouter',
                'routes' => [
                    [
                        'class' => 'CFileLogRoute',
                        'levels' => 'trace, info, error',
                        'categories' => 'application',
                        'logFile' => 'application.log'
                    ],
                    [
                        'class' => 'CFileLogRoute',
                        'levels' => 'trace, info, error',
                        'categories' => 'com-load-email-letters',
                        'logFile' => 'communication-load-email-letters.log'
                    ],
                    [
                        'class' => 'CFileLogRoute',
                        'levels' => 'trace, info, error',
                        'categories' => 'webhook-init',
                        'logFile' => 'webhook-init.log'
                    ],
                    [
                        'class' => 'CFileLogRoute',
                        'levels' => 'trace, info, error',
                        'categories' => 'api-error',
                        'logFile' => 'api-error.log'
                    ],
                    [
                        'class' => 'CFileLogRoute',
                        'levels' => 'trace, info, error',
                        'categories' => 'api-request',
                        'logFile' => 'api-request.log'
                    ],
                    [
                        'class' => 'CFileLogRoute',
                        'levels' => 'trace, info, error',
                        'categories' => 'console-error',
                        'logFile' => 'console-error.log'
                    ],
                    [
                        'class' => 'CFileLogRoute',
                        'levels' => 'trace, info, error',
                        'categories' => 'system-error',
                        'logFile' => 'system-error.log'
                    ],
                ],
            ],
            'curl' => [
                'class' => 'Curl',
                'config' => [
                    'opt_timeout' => 25,
                    'opt_maxredirs' => 7,
                    'cookie_file_path' => __DIR__ . '/../runtime/cookies',
                    'encoding' => 'gzip',
                    'referer' => [
                        'enable' => false,
                        'url' => '',
                    ],
                    'headers' => [
                        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                        'Accept-Language: ru,en;q=0.8',
                    ],
                    'user_agents' => [
                        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 YaBrowser/14.8.1985.11875 Safari/537.36',
                        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.124 Safari/537.36',
                        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2145.4 Safari/537.36 OPR/26.0.1632.0',
                        'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:32.0) Gecko/20100101 Firefox/32.0',
                        'Mozilla/5.0 (Windows NT 5.1; WOW64) AppleWebKit/534.57.2 (KHTML, like Gecko) Version/5.1.7 Safari/534.57.2',
                        'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko',
                    ],
                    'proxy_server' => [
                        'enable' => false,
                        'address' => null,
                        'userpwd' => null,
                    ],
                ],
            ],
        ],
        'modules' => [
            'permission' => [
                'class' => 'application.modules.Permission.PermissionModule',
            ],
            'api' => [
                'class' => 'application.modules.Api.ApiModule',
                'import' => [
                    'api.extensions.*',
                ],
            ],
        ],
        'params' => [
            // PhpMailer Settings
            'phpMailer' => [
                'mailer' => 'smtp', //mail or smtp
                'host' => 'mail.quotidian.cl',
                'port' => 587, // 465 or 587
                'userName' => 'noreply@quotidian.cl',
                'password' => 'FIBWIJnjXy',
                'secure' => 'tls', //Options: '', 'ssl' or 'tls'
            ],
            'global' => [
                'intervals' => [
                    'notifications' => 60000,
                    'process_view_entities' => 6000,
                    'quick_view' => [
                        'block_communications' => 10000,
                        'block_calls' => 300000,
                    ],
                ],
                'ajax' => [
                    'global_timeout' => 300000,
                    'get_url_timeout' => 300000,
                    'global_timeout_import' => 3600000,
                ],
            ],
        ],
    ];
EOF;
    }
}
