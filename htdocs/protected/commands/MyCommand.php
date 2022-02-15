<?php

class MyCommand extends ConsoleCommand{






    /**
     * actionDeployCopyCollectActivity - собираем активность копий СРМ систем (для My)
     *
    Структура параметров (пример):
    'deploy_copy_collect_activity' => array(
    'enabled' => true,
    'copy_id' => 264,
    'field_pk' => 'akkaunty_id',
    'field_name_domain' => 'accounts_domen',
    'field_name_bd' => 'accounts_begin',
    'field_name_lld' => 'last_login_date',
    'condition' => '',
    'base_path' => '/var/www',
    ),
     */
    public function actionDeployCopyCollectActivity(){
        echo 'Starting "DeployCopyCollectActivity"...';

        ConsoleQueueModel::saveRunning('DeployCopyCollectActivity');

        if(empty(\Yii::app()->params['console']['deploy_copy_collect_activity']['enabled'])){
            echo PHP_EOL . 'Action disabled . Done';
            echo PHP_EOL;
            ConsoleQueueModel::saveDone('DeployCopyCollectActivity');
            return;
        }

        $params = \Yii::app()->params['console']['deploy_copy_collect_activity'];

        $module_table_name = \DataModel::getInstance()
            ->setSelect('table_name')
            ->setFrom('{{module_tables}}')
            ->setWhere('copy_id = ' . $params['copy_id'] . ' AND type = "parent"')
            ->findScalar();


        if($module_table_name == false){
            echo PHP_EOL . 'Module table not found. Done';
            echo PHP_EOL;
            ConsoleQueueModel::saveDone('DeployCopyCollectActivity');
            return;
        }


        $module_model = \DataModel::getInstance()
            ->setSelect($params['field_pk'] . ', ' . $params['field_name_domain'] . ', ' . $params['field_name_bd'])
            ->setFrom('{{'.$module_table_name.'}}');

        if($params['condition']){
            $module_model->setWhere($params['condition']);
        }

        $domain_list = $module_model->findAll();

        if($domain_list == false){
            echo PHP_EOL . 'Warning! Acconts data not found . Done';
            echo PHP_EOL;
            ConsoleQueueModel::saveDone('DeployCopyCollectActivity');
            return;
        }

        $count = 0;
        $query_all = [];

        restore_error_handler();

        foreach($domain_list as $row){
            try {
                $host_params = @include($params['base_path'] . '/' . $row[$params['field_name_domain']] . '/htdocs/protected/config/local.php');
                if(empty($host_params['components']['db'])){
                    $host_params = @include($params['base_path'] . '/' . $row[$params['field_name_domain']] . '/current/htdocs/protected/config/local.php');
                    if(empty($host_params['components']['db'])) continue;
                }

                $host_params = $host_params['components']['db'];

                // select last date
                $connect = new CDbConnection($host_params['connectionString'], $host_params['username'], $host_params['password']);
                $connect->tablePrefix = $host_params['tablePrefix'];
                $connect->charset = $host_params['charset'];
                $connect->setActive(true);

                $query = 'SELECT max(date_update) FROM {{users_storage}} WHERE type = 10;';
                $command = new CDbCommand($connect, $query);
                $date_last = $command->queryScalar();

                if($row[$params['field_name_bd']] && $date_last && strtotime($date_last) <= strtotime($row[$params['field_name_bd']])){
                    $date_last =  'null';
                } else if($date_last){
                    $date_last = '"' . $date_last  . '"';
                } else {
                    $date_last =  'null';
                }

                // insert last date
                $query_update = 'UPDATE {{' . $module_table_name . '}} SET ' . $params['field_name_lld'] . ' = '.$date_last.'  WHERE ' . $params['field_pk'] . ' = ' . $row[$params['field_pk']] . '; ';
                $query_all[] = $query_update;
                \DataModel::getInstance()->setText($query_update)->execute();

                $count++;

            } catch (Exception $e){
                echo PHP_EOL;
                echo 'Exception Error. ' . $e->getMessage();
                echo PHP_EOL;
            }
        }

        ConsoleQueueModel::saveDone('DeployCopyCollectActivity');

        echo PHP_EOL . 'Updated ' . $count . ' rows';
        echo PHP_EOL . 'Done';
        echo PHP_EOL;
    }










    /**
     * actionDeployCopyConsoleRunAll - запускает консольную команду RunAll для всех площадок из модуля Аккаунты
     *
    Структура параметров (пример):
    'collect_activity_console_run_all' => array(
    'enabled' => true,
    'copy_id' => 264,
    'field_pk' => 'akkaunty_id',
    'field_name_domain' => 'accounts_domen',
    'condition' => '',
    'base_path' => '/var/www',
    ),
     */
    public function actionDeployCopyConsoleRunAll(){
        echo 'Starting "DeployCopyConsoleRunAll"...';
        echo PHP_EOL;

        ConsoleQueueModel::saveRunning('DeployCopyConsoleRunAll');

        if(empty(\Yii::app()->params['console']['collect_activity_console_run_all']['enabled'])){
            echo 'Action disabled. Done' . PHP_EOL;
            ConsoleQueueModel::saveDone('DeployCopyConsoleRunAll');
            return;
        }

        $params = \Yii::app()->params['console']['collect_activity_console_run_all'];


        $module_table_name = \DataModel::getInstance()
            ->setSelect('table_name')
            ->setFrom('{{module_tables}}')
            ->setWhere('copy_id = ' . $params['copy_id'] . ' AND type = "parent"')
            ->findScalar();


        if($module_table_name == false){
            echo 'Module table not found. Done' . PHP_EOL;
            ConsoleQueueModel::saveDone('DeployCopyConsoleRunAll');
            return;
        }


        $module_model = \DataModel::getInstance()
            ->setSelect($params['field_pk'] . ', ' . $params['field_name_domain'])
            ->setFrom('{{'.$module_table_name.'}}');

        if($params['condition']){
            $module_model->setWhere($params['condition']);
        }

        $domain_list = $module_model->findAll();

        if($domain_list == false){
            echo 'Warning! Acconts data not found. Done' . PHP_EOL;
            ConsoleQueueModel::saveDone('DeployCopyConsoleRunAll');
            return;
        }

        $count = 0;
        $lich = 0;

        echo 'Find ' . count($domain_list) . ' acounts.';
        echo PHP_EOL;

        restore_error_handler();

        foreach($domain_list as $row){
            $lich++;
            $path = null;
            $path_list = array(
                $params['base_path'] . '/' . $row[$params['field_name_domain']] . '/htdocs/protected',
                $params['base_path'] . '/' . $row[$params['field_name_domain']] . '/current/htdocs/protected',
            );

            echo 'Execute domain "'. $row[$params['field_name_domain']]  .'" ...';

            try {
                foreach($path_list as $path_i){
                    if(file_exists($path_i) == true){
                        $path = $path_i;
                        break;
                    }
                }

                if($path == false){
                    echo PHP_EOL;
                    echo  'Error. Path "'. $path.'" is bad. Done';
                    echo PHP_EOL;
                    continue;
                }

                try {
                    // execute
                    exec('cd ' . $path . ' && php yiic utility runAll > /dev/null 2>&1');

                    echo 'Done';
                    echo PHP_EOL;

                } catch (Exception $e) {
                    echo PHP_EOL;
                    echo 'Exception Error. ' . $e->getMessage();
                    echo PHP_EOL;
                }
            } catch (Exception $e) {
                echo PHP_EOL;
                echo 'Exception Error. ' . $e->getMessage();
                echo PHP_EOL;
            }

            $count++;
        }

        ConsoleQueueModel::saveDone('DeployCopyConsoleRunAll');

        echo PHP_EOL;
        echo 'Executed ' . $count . ' domain(s). Done';
        echo PHP_EOL;
    }








    /**
     * actionDeployCopyBackUpDbRunAll - запускает консольную команду архивации БД для всех задеплоенных площадок
     *
    Структура параметров (пример):
    'deploy_copy_back_up_db' => array(
        'enabled' => true,
        'copy_id' => 264,
        'field_pk' => 'akkaunty_id',
        'field_name_domain' => 'accounts_domen',
        'condition' => '',
        'base_path' => '/var/www',
    ),
     */
    public function actionDeployCopyBackUpDbRunAll(){
        echo 'Starting "DeployCopyBackUpDbRunAll"...';
        echo PHP_EOL;

        ConsoleQueueModel::saveRunning('DeployCopyBackUpDbRunAll');

        if(empty(\Yii::app()->params['console']['deploy_copy_back_up_db']['enabled'])){
            echo 'Action disabled. Done' . PHP_EOL;
            ConsoleQueueModel::saveDone('DeployCopyBackUpDbRunAll');
            return;
        }

        $params = \Yii::app()->params['console']['collect_activity_console_run_all'];


        $module_table_name = \DataModel::getInstance()
            ->setSelect('table_name')
            ->setFrom('{{module_tables}}')
            ->setWhere('copy_id = ' . $params['copy_id'] . ' AND type = "parent"')
            ->findScalar();


        if($module_table_name == false){
            echo 'Module table not found. Done' . PHP_EOL;
            ConsoleQueueModel::saveDone('DeployCopyBackUpDbRunAll');
            return;
        }


        $module_model = \DataModel::getInstance()
            ->setSelect($params['field_pk'] . ', ' . $params['field_name_domain'])
            ->setFrom('{{'.$module_table_name.'}}')
            ->setOrder($params['field_pk'] . ' desc');

        if($params['condition']){
            $module_model->setWhere($params['condition']);
        }

        $domain_list = $module_model->findAll();

        if($domain_list == false){
            echo 'Warning! Acconts data not found. Done' . PHP_EOL;
            ConsoleQueueModel::saveDone('DeployCopyBackUpDbRunAll');
            return;
        }

        $count = 0;
        $lich = 0;

        echo 'Find ' . count($domain_list) . ' acounts.';
        echo PHP_EOL;

        restore_error_handler();

        foreach($domain_list as $row){
            $lich++;
            $path = null;
            $path_list = array(
                $params['base_path'] . '/' . $row[$params['field_name_domain']] . '/htdocs/protected',
                $params['base_path'] . '/' . $row[$params['field_name_domain']] . '/current/htdocs/protected',
            );

            echo 'Execute domain "'. $row[$params['field_name_domain']]  .'" ...';

            try {
                foreach($path_list as $path_i){
                    if(file_exists($path_i) == true){
                        $path = $path_i;
                        break;
                    }
                }

                if($path == false){
                    echo PHP_EOL;
                    echo  'Error. Path "'. $path.'" is bad. Done';
                    echo PHP_EOL;
                    continue;
                }

                try {
                    // execute
                    exec('cd ' . $path . ' && php yiic utility backUpDb');

                    echo 'Done';
                    echo PHP_EOL;

                } catch (Exception $e) {
                    echo PHP_EOL;
                    echo 'Exception Error. ' . $e->getMessage();
                    echo PHP_EOL;
                }
            } catch (Exception $e) {
                echo PHP_EOL;
                echo 'Exception Error. ' . $e->getMessage();
                echo PHP_EOL;
            }

            $count++;
        }

        ConsoleQueueModel::saveDone('DeployCopyBackUpDbRunAll');

        echo PHP_EOL;
        echo 'Executed ' . $count . ' domain(s). Done';
        echo PHP_EOL;
    }









    /**
     * actionDeployCopyRepoPullAndUpdate - обновление репозиториев
     */
    public function actionDeployCopyRepoPullAndUpdate(){
        echo 'Starting "DeployCopyRepoPullAndUpdate"...' . PHP_EOL;

        $repo_params = Yii::app()->params['repo'];

        if(empty($repo_params)){
            echo 'Error! Repo params is empty. Done' . PHP_EOL;
            return;
        }

        $domain_list = $repo_params['domain_list'];

        if(empty($domain_list)){
            echo 'Error! Domain list is empty. Done' . PHP_EOL;
            return;
        }

        $root_path = $repo_params['root_path'];
        $last_path = $repo_params['last_path'];

        foreach($domain_list as $domain_name){
            echo 'Run domain "' . $domain_name . '". ';

            $path = $root_path . '/' . $domain_name . ($last_path ? '/' . $last_path : '');


            if(file_exists($path) == false){
                echo 'Error: path is bad. Done. ' . PHP_EOL;
                continue;
            }
            $command = 'cd ' . $path . ' && hg pull http://'.$repo_params['login'].':'.$repo_params['password'].'@'.$repo_params['host'].' -r '.$repo_params['branch'].' && hg update -C';

            exec($command);

            echo 'Done. ' . PHP_EOL;
        }

        echo 'Done' . PHP_EOL;
    }




    /**
     * actionRegularMailDispatch - регулянрная рассылка писем
     */
    public function actionRegularMailDispatch(){
        echo 'Starting "RegularMailDispatch"...' . PHP_EOL;

        ConsoleQueueModel::saveRunning('RegularMailDispatch');

        $params = Yii::app()->params['console']['regular_mail_dispatch'];

        if(empty($params)){
            echo 'Error! Params is empty. Done' . PHP_EOL;
            ConsoleQueueModel::saveDone('RegularMailDispatch');
            return;
        }

        $methods = $params['methods'];

        if(empty($methods)){
            ConsoleQueueModel::saveDone('RegularMailDispatch');
            echo 'Error! Method list is empty. Done' . PHP_EOL;
            return;
        }

        foreach($methods as $method_name){
            echo 'Run method "' . $method_name . '". ';

            $method_params = (array_key_exists($method_name, $params) ? $params[$method_name] : null);

            $this->{$method_name}($method_params);

            echo 'Done.' . PHP_EOL;
        }

        echo 'Done' . PHP_EOL;

        ConsoleQueueModel::saveDone('RegularMailDispatch');
    }




    /**
     * rmdAfterOneHour
     */
    private function rmdAfterOneHour($params){
        $date_time = new DateTime();
        $date_time->modify('-1 hour');
        $date_start = $date_time->format('Y-m-d H:i:00');
        $date_finish = $date_time->format('Y-m-d H:i:59');

        $query = '
            SELECT t0.akkaunty_id, t4.*
            FROM myteamms_base_akkaunty t0
              LEFT JOIN myteamms_base_klienty_akkaunty_5 t1 ON t0.akkaunty_id = t1.akkaunty_id
              LEFT JOIN myteamms_base_klienty t2 ON t1.klienty_id = t2.klienty_id
              LEFT JOIN myteamms_base_kontakty_klienty_1 t3 ON t2.klienty_id = t3.klienty_id
              LEFT JOIN myteamms_base_kontakty t4 ON t3.kontakty_id = t4.kontakty_id
            WHERE t0.date_create between "'.$date_start.'" AND "'.$date_finish.'"
            GROUP BY t0.accounts_domen
            ORDER BY t0.akkaunty_id
        ';

        $data_list = (new DataModel())->setText($query)->findAll();

        if($data_list == false){
            echo 'Data not found. ';
            return false;
        }

        $i = 0;

        foreach($data_list as $data){
            $letter_params = [
                '{user_name}' => $data['module_title'],
            ];
            $mailer_model = (new Mailer());
            $b = $mailer_model
                    ->setTemplate(Mailer::LETTER_TEMPLATE_TEXT)
                    ->setLetter(
                        $params['from'],
                        $params['from_name'],
                        $data['ehc_field3'],
                        $data['module_title'],
                        'my_after_one_hour',
                        $letter_params
                    );

            if($b){
                $i++;
            }
        }

        echo 'Sended ' . $i . ' letter(s). ';

        return false;
    }


    /**
     * rmdAfterOneWeek
     */
    private function rmdAfterOneWeek($params){
        $date_time = new DateTime();
        $date_time->modify('-168 hour');
        $date_start = $date_time->format('Y-m-d H:i:00');
        $date_finish = $date_time->format('Y-m-d H:i:59');

        $query = '
            SELECT t0.akkaunty_id, t4.*
            FROM myteamms_base_akkaunty t0
              LEFT JOIN myteamms_base_klienty_akkaunty_5 t1 ON t0.akkaunty_id = t1.akkaunty_id
              LEFT JOIN myteamms_base_klienty t2 ON t1.klienty_id = t2.klienty_id
              LEFT JOIN myteamms_base_kontakty_klienty_1 t3 ON t2.klienty_id = t3.klienty_id
              LEFT JOIN myteamms_base_kontakty t4 ON t3.kontakty_id = t4.kontakty_id
            WHERE t0.date_create between "'.$date_start.'" AND "'.$date_finish.'"
            GROUP BY t0.accounts_domen
            ORDER BY t0.akkaunty_id
        ';

        $data_list = (new DataModel())->setText($query)->findAll();

        if($data_list == false){
            echo 'Data not found. ';
            return false;
        }

        $i = 0;

        foreach($data_list as $data){
            $letter_params = [
                '{user_name}' => $data['module_title'],
            ];
            $mailer_model = (new Mailer());
            $b = $mailer_model
                ->setTemplate(Mailer::LETTER_TEMPLATE_TEXT)
                ->setLetter(
                    $params['from'],
                    $params['from_name'],
                    $data['ehc_field3'],
                    $data['module_title'],
                    'my_after_one_week',
                    $letter_params
                );

            if($b){
                $i++;
            }
        }

        echo 'Sended ' . $i . ' letter(s). ';

        return false;
    }




    public function actionRmdAfterSetStatusNedozvon($properties){
        echo 'Starting "RmdAfterSetStatusNedozvon"...' . PHP_EOL;
        //ConsoleQueueModel::saveRunning('RmdAfterSetStatusNedozvon');

        if(empty($properties)){
            echo 'Error! Properties is empty. Done' . PHP_EOL;
            //ConsoleQueueModel::saveDone('RmdAfterSetStatusNedozvon');
            return;
        }

        $params = [
            'data_id' => $properties,
        ];

        $this->rmdAfterSetStatusNedozvon($params);

        echo 'Done' . PHP_EOL;

        //ConsoleQueueModel::saveDone('RmdAfterSetStatusNedozvon');
    }




    /**
     * rmdAfterSetStatusNedozvon
     */
    private function rmdAfterSetStatusNedozvon($vars){
        $params = Yii::app()->params['console']['regular_mail_dispatch'];
        if($params == false){
            echo 'Params is empty(1). ';
            return;
        }

        $method_params = $params['rmdAfterSetStatusNedozvon'];
        if($method_params == false){
            echo 'Params is empty(2). ';
            return;
        }

        if($vars['data_id'] == false){
            echo 'Parameter "data_id" is empty. ';
            return false;
        }

        $query = '
            SELECT t0.sdelki_id, t0.deals_status, t4.*
            FROM myteamms_base_sdelki t0
              LEFT JOIN myteamms_base_sdelki_klienty_1 t1 ON t0.sdelki_id = t1.sdelki_id
              LEFT JOIN myteamms_base_klienty t2 ON t1.klienty_id = t2.klienty_id
              LEFT JOIN myteamms_base_kontakty_klienty_1 t3 ON t2.klienty_id = t3.klienty_id
              LEFT JOIN myteamms_base_kontakty t4 ON t3.kontakty_id = t4.kontakty_id
            WHERE t0.sdelki_id = ' . $vars['data_id'] . '
            GROUP BY t0.sdelki_id
        ';

        $data_model = (new \DataModel())->setText($query)->findRow();

        if($data_model == false){
            echo 'Data not found. ';
            return false;
        }

        if($data_model['deals_status'] != $method_params['deals_status']){
            echo 'Data not found: ' . $data_model['deals_status'] . '<>' . $method_params['deals_status'];
            return false;
        }

        $letter_params = [
            '{user_name}' => $data_model['module_title'],
        ];

        $mailer_model = (new \Mailer());
        $mailer_model
            ->setTemplate(\Mailer::LETTER_TEMPLATE_TEXT)
            ->setLetter(
                $method_params['from'],
                $method_params['from_name'],
                $data_model['ehc_field3'],
                $data_model['module_title'],
                'my_after_set_status_nedozvon',
                $letter_params
            );
    }



}
