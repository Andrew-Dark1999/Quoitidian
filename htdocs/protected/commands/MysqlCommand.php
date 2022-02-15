<?php

/**
 * Class MysqlCommand
 *
 * @author Aleksandr Roik
 */
class MysqlCommand extends ConsoleCommand
{
    /**
     * Дамп баз  данных
     * @param $path Путь для файлов дампа
     */
    public function actionDumpAll($path, $username)
    {
        if(!file_exists($path)){
            echo "Path '$path' is not exists" . PHP_EOL;
            return;
        }

        echo 'Starting dump' . PHP_EOL;

        $dbList = (new DataModel())
            ->setText('show databases')
            ->findCol();

        if(!$dbList){
            echo 'databases not found' . PHP_EOL;
            return;
        }


        echo 'found ' . count($dbList) . ' databases' . PHP_EOL;


        if($path[strlen($path) - 1] === '/'){
            $path = substr($path, 0, -1);
        }
        if(!file_exists($path)){
            mkdir($path, 0775, true);
            echo 'mkdir: ' . $path . PHP_EOL;
        }

        foreach($dbList as $dbName){
            if(strpos($dbName, 'crm_') === false){
                continue;
            }
            $fileName = $dbName . '_' . date('Ymd_His') . '.sql';
            $cs = Yii::app()->db->connectionString;
            $cs = explode(';', $cs);
            $cs = explode(':', $cs[0]);
            $host = explode('=', $cs[1]);

            echo '- dump db name: ' . $dbName . PHP_EOL;
            // execute dump
            echo exec('mysqldump -h' . $host[1] . ' -u' . $username . ' -p ' . $dbName . ' > ' . $path . '/' . $fileName);
            // zip
            echo exec("gzip $path/$fileName");
        }

        echo 'Done' . PHP_EOL;
    }
}
