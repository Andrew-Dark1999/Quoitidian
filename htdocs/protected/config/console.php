<?php

    $ar = [];
    $ar[] = require("general.php");
    $ar[] = require("local.php");
    $ar[] = require("import.php");
    $ar[] = require("params.php");
    $ar[] = require("params-plugins.php");
    $ar[] = require("params-communications.php");
    $ar[] = require("params-calls.php");
    $ar[] = (file_exists('./config/deploy.php') ? require('deploy.php') : []);
    $ar[] = array(
            "commandMap" => array(
                "migrate" => array(
                    "class" => "system.cli.commands.MigrateCommand",
                    "migrationPath" => "application.migrations",
                    "migrationTable" => "{{yii_migration}}",
                    "connectionID" => "db",
                ),
            ),

            'components'=>array(
                    'widgetFactory'=>array(
                    'class'=>'CWidgetFactory',
                ),
                    'themeManager'=>array(
                    'class'=>'CThemeManager',
                ),
            ),

            'behaviors'=>array(
                'templater'=>'ConsoleApplicationTemplater',
            ),
        );


    $array = [];
    foreach($ar as $r){
        if($r == false) continue;
        $array = CMap::mergeArray($array, $r);
    }

    return $array;
