<?php

class UtilityCommand extends ConsoleCommand
{
    /**
     * ProcessStartAllSchedules - выполнение расписания по процессах
     */
    public function actionProcessStartAllSchedules()
    {
        echo 'Starting "ProcessStartAllSchedules"...';

        if (\ConsoleQueueModel::checkRunning('ProcessStartAllSchedules') == true) {
            echo PHP_EOL . 'Error! Other process is running. Done' . PHP_EOL;

            return;
        }

        ConsoleQueueModel::saveRunning('ProcessStartAllSchedules');

        restore_error_handler();
        try {
            \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule();
            (new \Process\models\StartTimeModel())->startAllSchedules();
            echo 'Done';
        } catch (Exception $e) {
            ConsoleQueueModel::saveDone('ProcessStartAllSchedules');
            echo PHP_EOL . 'Exception Error! Done';
        }
        ConsoleQueueModel::saveDone('ProcessStartAllSchedules');
        echo(PHP_EOL);
    }

    /**
     * ProcessStartAllSchedules - выполнение расписания по процессах
     */
    public function actionProcessActionsRun($properties)
    {
        echo 'ProcessRunActions is starting...';

        //ConsoleQueueModel::saveRunning('ProcessRunActions');

        if (empty($properties)) {
            //ConsoleQueueModel::saveDone('ProcessRunActions');
            echo PHP_EOL;
            echo 'Error! Properties not found' . PHP_EOL;
            echo 'Done' . PHP_EOL;

            //ConsoleQueueModel::saveDone('ProcessRunActions');
            return;
        }

        Logging::getInstance()
            ->setLogName('process-actions-run')
            ->toFile('ProcessRunActions is starting...')
            ->toFile('Properties:')
            ->toFile($properties);

        if (is_string($properties)) {
            $properties = json_decode($properties, true);
        }

        if (key_exists(0, $properties) == false) {
            $properties = [$properties];
        }

        foreach ($properties as $property) {
            Logging::getInstance()
                ->setLogName('process-actions-run')
                ->toFile('Run property:')
                ->toFile(json_encode($property));

            $result = (new ProcessActions())
                ->setVars($property['vars'])
                ->setEnv()
                ->setActionName($property['action_name'])
                ->run()
                ->getResult();

            Logging::getInstance()
                ->setLogName('process-actions-run')
                ->toFile('Result:' . json_encode($property));

            if ($result['messages'] && is_array($result['messages'])) {
                foreach ($result['messages'] as $message) {
                    echo PHP_EOL . $message;
                }
            }
        }

        //ConsoleQueueModel::saveDone('ProcessRunActions');

        echo PHP_EOL;
        echo 'Done' . PHP_EOL;

        Logging::getInstance()
            ->setLogName('process-actions-run')
            ->toFile('Done.');

        return;
    }

    /**
     * HistoryNotificationDeliveryRun - отсылка уведомлений
     * used ConsoleQueueModel
     */
    public function actionHistoryNotificationDeliveryRun()
    {
        ini_set('max_execution_time', 300); // 5min.

        echo 'Starting "HistoryNotificationDeliveryRun"...';

        if (\ConsoleQueueModel::checkRunning('HistoryNotificationDeliveryRun') == true) {
            echo PHP_EOL . 'Error! Other process is running. Done' . PHP_EOL;

            return;
        }
        ConsoleQueueModel::saveRunning('HistoryNotificationDeliveryRun');

        restore_error_handler();

        $result['status'] = true;

        try {
            $result = \HistoryNotificationDelivery::getInstance()
                ->setController($this)
                ->run()
                ->getResult();

        } catch (Exception $e) {
            ConsoleQueueModel::saveDone('HistoryNotificationDeliveryRun');
        }
        ConsoleQueueModel::saveDone('HistoryNotificationDeliveryRun');

        if ($result['status']) {
            echo(PHP_EOL . 'Sending completed. Done');
        } else {
            echo(PHP_EOL . 'Error! Done');
        }

        echo(PHP_EOL);
    }

    /**
     * actionHistoryNotificationRunDateEndingBecome - Создание уведомлений ответственным для карточек, в которых "Дата окончания" равна сегодняшней дате
     */
    public function actionHistoryNotificationRunDateEndingBecome($to_minutes = null)
    {
        ini_set('max_execution_time', 300); // 5min.

        echo 'Starting "HistoryNotificationRunDateEndingBecome"...';

        if (\ConsoleQueueModel::checkRunning('HistoryNotificationRunDateEndingBecome') == true) {
            echo PHP_EOL . 'Error! Other process is running. Done' . PHP_EOL;

            return;
        }
        ConsoleQueueModel::saveRunning('HistoryNotificationRunDateEndingBecome');

        restore_error_handler();

        $result['status'] = true;

        try {
            (new HistoryActionsModel())
                ->setHistoryModel(new \History())
                ->setToMinutes($to_minutes)
                ->run(HistoryActionsModel::ACTION_DATE_ENDING_BECOME);

            echo(PHP_EOL . 'Process completed.');

        } catch (Exception $e) {
            ConsoleQueueModel::saveDone('HistoryNotificationRunDateEndingBecome');
            echo(PHP_EOL . 'Error! Done.');
        }
        ConsoleQueueModel::saveDone('HistoryNotificationRunDateEndingBecome');

        echo(PHP_EOL);
    }

    /**
     * @param int $hour_old
     * @throws CDbException
     */
    public function actionDeleteTempFiles($hour_old = 12)
    {

        echo 'Starting "DeleteTempFiles"...';

        if (!is_numeric($hour_old)) {
            echo PHP_EOL . 'Error! Hour parameter not numeric. Done';
            echo(PHP_EOL);

            return;
        }

        if ($hour_old < 0) {
            echo PHP_EOL . 'Error! Hour parameter. Done';
            echo(PHP_EOL);

            return;
        }

        $date_create = date('Y-m-d H:i:s', strtotime('-' . $hour_old . ' hours'));

        $model = UploadsModel::model()->findAll(
            'status = "temp" AND date_create <= :date_create',
            [':date_create' => $date_create]
        );

        if (empty($model)) {
            echo(PHP_EOL . 'Error! Files not found. Done');
        } else {

            echo(PHP_EOL . 'Find ' . count($model) . ' files. ');
            $lich = 0;

            foreach ($model as $model_data) {
                /**@var $model_data UploadsModel */
                if (!$model_data->delete()) {
                    echo(PHP_EOL . 'Error! File name:' . $model_data->file_path . '/' . $model_data->file_name);
                } else {
                    $lich++;
                }

            }

            echo(PHP_EOL . 'Deleted ' . $lich . ' files. Done');
        }
        echo(PHP_EOL);
    }

    /**
     * @param int $hour_old
     * @throws CDbException
     */
    public function actionDeleteTempActivity($hour_old = 12)
    {

        echo 'Starting "DeleteTempActivity"...';

        if (!is_numeric($hour_old)) {
            echo PHP_EOL . 'Error! Hour parameter not numeric. Done';
            echo PHP_EOL;

            return;
        }

        if ($hour_old < 0) {
            echo PHP_EOL . 'Error! Hour parameter. Done';
            echo PHP_EOL;

            return;
        }

        $date_create = date('Y-m-d H:i:s', strtotime('-' . $hour_old . ' hours'));

        $model = ActivityMessagesModel::model()->findAll(
            'status = "temp" AND date_create <= :date_create',
            [':date_create' => $date_create]
        );

        if (empty($model)) {
            echo(PHP_EOL . 'Error! Activity message not found. Done');
        } else {

            echo(PHP_EOL . 'Find ' . count($model) . ' activity messages. ');
            $lich = 0;

            foreach ($model as $model_data) {
                if (!$model_data->delete()) {
                    echo(PHP_EOL . 'Error! Activity id:' . $model_data->getPrimaryKey());
                } else {
                    $lich++;
                }

            }

            echo(PHP_EOL . 'Deleted ' . $lich . ' activity messages. Done');
        }
        echo(PHP_EOL);
    }

    /**
     * Postproccessing
     */
    public function actionPostproccessing()
    {
        \AdditionalProccessingModel::getInstance()->daily();
    }

    /**
     * BackUpDB - делает архив базы данных.
     *            Параметры в {{params}}, value = console_back_up_db
     */
    public function actionBackUpDB()
    {
        echo 'Starting "BackUpDB"...' . PHP_EOL;

        $config = ParamsModel::model()->titleName('console_back_up_db')->find()->getValueJson();
        $site_url = ParamsModel::getValueFromModel('site_url');

        $company_name = preg_replace('~((http|https):\/\/|(http|https):)~', '', $site_url);
        $company_name = str_replace('.', '_', $company_name);

        $filename_pattern = 'db_<company_name>_<date>.sql.bz2';

        $db_name = null;
        $filename = str_replace(['<company_name>', '<date>'], [$company_name, date('Ymd_His')], $filename_pattern);
        $path = $config['path'] . '/' . $company_name . '/';

        $connection_string = Yii::app()->db->connectionString;
        $connection_string = explode(';', $connection_string);
        foreach ($connection_string as $item) {
            $tmp_cs = explode('=', $item);
            if ($tmp_cs[0] == 'dbname') {
                $db_name = $tmp_cs[1];
                break;
            }
        }

        if (empty($db_name)) {
            echo 'Error! Not differen params (db_name)' . PHP_EOL;
            echo 'Done' . PHP_EOL;

            return;
        }

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $cs = Yii::app()->db->connectionString;
        $cs = explode(';', $cs);
        $cs = explode(':', $cs[0]);
        $host = explode('=', $cs[1]);

        if (empty($host[1])) {
            echo 'Error! Bad host' . PHP_EOL;
            echo 'Done' . PHP_EOL;

            return;
        }

        $command = 'mysqldump -h' . $host[1] . ' -u' . Yii::app()->db->username . ' -p' . Yii::app()->db->password . ' ' . $db_name . ' | bzip2 > ' . $path . '/' . $filename;

        // execute dump
        exec($command);

        // удаляем файлы, если их накопилось больше граничного размера
        if (!empty($config['files_max_count'])) {
            $filenames = [];
            foreach (scandir($path) as $file) {
                if ($file == '..' || $file == '.') {
                    continue;
                }
                $filename = $path . '/' . $file;
                if (is_file($filename)) {
                    $filenames[] = $filename;
                }
            }

            if (count($filenames) <= $config['files_max_count']) {
                echo 'Done' . PHP_EOL;

                return;
            }

            $freshFilenames = array_reverse($filenames);
            array_splice($freshFilenames, $config['files_max_count']);
            $oldFilenames = array_diff($filenames, $freshFilenames);

            foreach ($oldFilenames as $filename) {
                unlink($filename);
            }
        }

        echo 'Done' . PHP_EOL;
    }

    /**
     * actionClearParticipants - Удаляет участников, что не пренадлежат ни одной записи модуля
     */
    public function actionClearParticipants()
    {
        echo 'Starting "ClearParticipants"...';

        $extension_copy_list = \DataModel::getInstance()->setText('SELECT * FROM {{extension_copy}}')->findAll();

        foreach ($extension_copy_list as $extension_copy) {
            $module_table = \DataModel::getInstance()->setText('SELECT * FROM {{module_tables}} WHERE copy_id = ' . $extension_copy['copy_id'] . ' AND type = "parent"')->findRow();
            if (empty($module_table)) {
                continue;
            }

            $sql = 'SELECT data_id
                    from {{participant}} as t1
                    WHERE
                    copy_id = ' . $extension_copy['copy_id'] . ' AND
                    NOT EXISTS (SELECT ' . $extension_copy['prefix_name'] . '_id FROM {{' . $module_table['table_name'] . '}} t2 WHERE t1.data_id = t2.' . $extension_copy['prefix_name'] . '_id)
                ';

            $participants = \DataModel::getInstance()->setText($sql)->findCol();

            if (empty($participants)) {
                continue;
            }

            $data_id = implode(',', $participants);
            \DataModel::getInstance()->setText('DELETE FROM {{participant}} WHERE copy_id = ' . $extension_copy['copy_id'] . ' AND data_id in(' . $data_id . ')')->execute();
            echo PHP_EOL . 'copy_id - ' . $extension_copy['copy_id'] . ', count rows - ' . count($participants);
        }
        echo 'Done' . PHP_EOL;
    }

    /**
     * actionSendLetter - Отправка писем
     */
    public function actionSendLetter($mailer_letter_name, array $vars = null)
    {
        echo 'Starting "SendLetter"...';

        MailerLettersTemplatesModel::sendLetter($mailer_letter_name, $vars);

        echo 'Done' . PHP_EOL;
    }

    /**
     * actionClearAllLogs
     *
     * @param array ...$properties
     */
    public function actionClearAllLogs($properties)
    {
        echo 'Starting "ClearAllLogs"...' . PHP_EOL;

        ConsoleQueueModel::saveRunning('ClearAllLogs');

        if (empty($properties)) {
            echo 'Error! Properties not found. Done' . PHP_EOL;

            ConsoleQueueModel::saveDone('ClearAllLogs');

            return;
        }

        restore_error_handler();

        if (is_string($properties)) {
            $properties = json_decode($properties, true);
        }

        if ($properties && is_array($properties)) {
            foreach ($properties as $item) {
                try {
                    $limit_date = date('Y-m-d H:i:s', strtotime('-' . $item['limit_min'] . 'minutes'));
                    $class_name = $item['class_name'];
                    $class_name::deleteOldRecords($limit_date);
                } catch (Exception $e) {
                    echo 'Exception Error! ' . $e->getMessage();
                }
            }
        }

        ConsoleQueueModel::saveDone('ClearAllLogs');

        echo 'Done' . PHP_EOL;
    }

    /**
     * actionRunAll - Запуск всех запланированных действий (расписаний), прописаных в console_executor
     */
    public function actionRunAll()
    {
        echo 'Starting "RunAll"...' . PHP_EOL;

        $action_list = \DataModel::getInstance()
            ->setSelect()
            ->setFrom('{{console_executor}}')
            ->setWhere('status = "enable"')
            ->setOrder('sort')
            ->findAll();

        if ($action_list == false) {
            echo 'Warning! Action(s) not found. Done' . PHP_EOL;

            return;
        }
        echo '************************' . PHP_EOL;

        $count = 0;

        restore_error_handler();

        foreach ($action_list as $action) {
            try {
                echo '************************' . PHP_EOL;
                echo 'Execution action ' . $action['method_name'] . PHP_EOL;
                if ((new ConsoleStartTimeModel())->startSchedule($action['id']) == false) {
                    echo 'Time has not come. Done' . PHP_EOL;
                    continue;
                }

                $method_name = $action['method_name'];
                $properties = null;

                if ($action['properties']) {
                    try {
                        $properties = json_decode($action['properties'], true);
                        if (!$properties || !is_array($properties)) {
                            $properties = $action['properties'];
                        }
                    } catch (Exception $e) {
                        echo 'Exception Error. Convert JSON array. Done' . PHP_EOL;
                        continue;
                    }
                }

                if ($properties) {
                    $this->{$method_name}($properties);
                } else {
                    $this->{$method_name}();
                }

                echo 'Done' . PHP_EOL;
                $count++;
            } catch (Exception $e) {
                echo 'Exception Error! ' . $e->getMessage();
            }
        }

        echo PHP_EOL;
        echo '**********************************************************' . PHP_EOL;
        echo 'Executed ' . $count . ' action(s)' . PHP_EOL;
        echo 'Done' . PHP_EOL;
    }

    /**
     * actionCSLoadEmailLetters - Коммуникации. Загрузка писем из внешних источников
     */
    public function actionCSLoadEmailLetters()
    {
        echo 'actionCSLoadEmailLetters is starting...';

        if (\ConsoleQueueModel::checkRunning('CS_LoadEmailLetters') == true) {
            echo PHP_EOL . 'Error! Other process is running. Done';
            echo PHP_EOL;

            return;
        }

        ConsoleQueueModel::saveRunning('CS_LoadEmailLetters');

        $result = (new CommunicationsServiceExternalActions())
            ->run(CommunicationsServiceExternalActions::ACTION_LOAD_EMAIL_LETTERS)
            ->getResult();

        if ($result['messages']) {
            echo PHP_EOL;
            foreach ($result['messages'] as $message) {
                echo $message . PHP_EOL;
            }
        }

        ConsoleQueueModel::saveDone('CS_LoadEmailLetters');

        echo 'Done' . PHP_EOL;
    }

    /**
     * actionCSLoadEmailLetters - Коммуникации. Перегрузка писем из внешних источников.
     *
     * @param string $last_getting_messages_date - дата, от которой будут перегрудены письма
     * @param string $last_getting_uid
     */
    public function actionCSReloadEmailLetters($last_getting_messages_date, $last_getting_uid)
    {
        echo 'actionCSLoadEmailLetters is starting...';
        echo PHP_EOL;

        if (\ConsoleQueueModel::checkRunning('CS_ReloadEmailLetters') == true) {
            echo PHP_EOL . 'Error! Other process is running. Done';
            echo PHP_EOL;

            return;
        }

        if (empty($last_getting_messages_date)) {
            echo 'Error! Property "$last_getting_messages_date" not found. Done' . PHP_EOL;
            ConsoleQueueModel::saveDone('CS_ReloadEmailLetters');

            return;
        }

        if ($last_getting_uid == false) {
            echo 'Error! Property "$last_getting_uid" is bad date' . PHP_EOL;
            ConsoleQueueModel::saveDone('CS_ReloadEmailLetters');

            return;
        }

        ConsoleQueueModel::saveRunning('CS_ReloadEmailLetters');

        // delete email, if none action messages
        $criteria = new CDbCriteria();
        $criteria->addCondition('mailerInboxRelate.mailer_id is null AND date_upload>=:date_upload');
        $criteria->params = [
            ':date_upload' => $last_getting_messages_date,
        ];
        $criteria->order = 'user_create, date_upload';

        $letters_inbox_model_list = \MailerLettersInboxModel::model()
            ->with([
                'mailerInboxRelate' => [
                    'select' => false,
                ],
            ])
            ->findAll($criteria);

        $count = count($letters_inbox_model_list);

        echo 'Find total - ' . $count . PHP_EOL;

        if ($count) {
            echo PHP_EOL;
            echo 'Delete letters:' . PHP_EOL;
        }
        foreach (($letters_inbox_model_list) as $letters_inbox_model) {
            echo 'Delete user ' . $letters_inbox_model->user_create . '. #' . $letters_inbox_model->mailer_id . ' - ' . $letters_inbox_model->date_upload . PHP_EOL;
            $letters_inbox_model->delete();
        }

        echo PHP_EOL;
        echo 'Load letters:' . PHP_EOL;

        // load emails
        $vars = [
            'last_getting_uid'                       => $last_getting_uid,
            'update_last_getting_messages_uid_in_db' => false,
        ];

        $result = (new CommunicationsServiceExternalActions())
            ->setVars($vars)
            ->run(CommunicationsServiceExternalActions::ACTION_LOAD_EMAIL_LETTERS)
            ->getResult();

        if ($result['messages']) {
            echo PHP_EOL;
            foreach ($result['messages'] as $message) {
                echo $message . PHP_EOL;
            }
        }

        ConsoleQueueModel::saveDone('CS_ReloadEmailLetters');

        echo 'Done' . PHP_EOL;
    }

    /**
     * actionCSDeleteEmailLetters - Коммуникации. Удаление писем из внешних источников
     */
    public function actionCSDeleteEmailLetters()
    {
        echo 'actionCSDeleteEmailLetters is starting...';

        if (\ConsoleQueueModel::checkRunning('CS_DeleteEmailLetters') == true) {
            echo PHP_EOL . 'Error! Other process is running. Done';
            echo PHP_EOL;

            return;
        }
        ConsoleQueueModel::saveRunning('CS_DeleteEmailLetters');

        $result = (new CommunicationsServiceExternalActions())
            ->run(CommunicationsServiceExternalActions::ACTION_DELETE_EMAIL_LETTERS)
            ->getResult();

        if ($result['messages'] && is_array($result['messages'])) {
            foreach ($result['messages'] as $message) {
                echo PHP_EOL . $message;
            }
        }

        ConsoleQueueModel::saveDone('CS_DeleteEmailLetters');

        echo 'Done' . PHP_EOL;
    }

    /**
     * actionCSSetSeenEmailLetters - Коммуникации. Установка отметки о прочтении писем из внешних источников
     */
    public function actionCSSetSeenEmailLetters()
    {
        echo 'actionCSSetSeenEmailLetters is starting...';

        if (\ConsoleQueueModel::checkRunning('CS_SetSeenEmailLetters') == true) {
            echo PHP_EOL . 'Error! Other process is running. Done';
            echo PHP_EOL;

            return;
        }
        ConsoleQueueModel::saveRunning('CS_SetSeenEmailLetters');

        $result = (new CommunicationsServiceExternalActions())
            ->run(CommunicationsServiceExternalActions::ACTION_SET_SEEN_EMAIL_LETTERS)
            ->getResult();

        if ($result['messages'] && is_array($result['messages'])) {
            foreach ($result['messages'] as $message) {
                echo PHP_EOL . $message;
            }
        }

        ConsoleQueueModel::saveDone('CS_SetSeenEmailLetters');

        echo 'Done' . PHP_EOL;
    }

    /**
     * actionCSSyncDeletedEmailLetters - Коммуникации. Синхронизация удаленных писем
     */
    public function actionCSSyncDeletedEmailLetters()
    {
        echo 'actionCSSyncDeletedEmailLetters is starting...';

        if (\ConsoleQueueModel::checkRunning('CS_SyncDeletedEmailLetters') == true) {
            echo PHP_EOL . 'Error! Other process is running. Done';
            echo PHP_EOL;

            return;
        }
        ConsoleQueueModel::saveRunning('CS_SyncDeletedEmailLetters');

        $result = (new CommunicationsServiceExternalActions())
            ->run(CommunicationsServiceExternalActions::ACTION_SYNCHRONIZATION_DELETED_EMAIL_LETTERS)
            ->getResult();

        if ($result['messages'] && is_array($result['messages'])) {
            foreach ($result['messages'] as $message) {
                echo PHP_EOL . $message;
            }
        }

        ConsoleQueueModel::saveDone('CS_SyncDeletedEmailLetters');

        echo 'Done' . PHP_EOL;
    }

    /**
     * actionCSRunAll - Коммуникации. Исполнение всех действий
     */
    public function actionCSRunAll($communications_service_params_id = null)
    {
        echo 'actionCSRunAll is starting' . PHP_EOL;

        $command_name = 'CS_RunAll' . ($communications_service_params_id ? '_' . $communications_service_params_id : '');

        if (\ConsoleQueueModel::checkRunning($command_name) == true) {
            echo PHP_EOL . 'Error! Other process is running' . PHP_EOL;
            echo 'Done' . PHP_EOL;
            echo PHP_EOL;

            return;
        }
        ConsoleQueueModel::saveRunning($command_name);

        $action_name_list = [
            CommunicationsServiceExternalActions::ACTION_SET_SEEN_EMAIL_LETTERS,
            CommunicationsServiceExternalActions::ACTION_DELETE_EMAIL_LETTERS,
            CommunicationsServiceExternalActions::ACTION_LOAD_EMAIL_LETTERS,
        ];

        $vars = [
            'communications_service_params_id' => $communications_service_params_id,
        ];

        foreach ($action_name_list as $action_name) {
            echo 'Run action "' . $action_name . '"' . PHP_EOL;

            $result = (new CommunicationsServiceExternalActions())
                ->setVars($vars)
                ->run($action_name)
                ->getResult();

            if ($result['messages'] && is_array($result['messages'])) {
                foreach ($result['messages'] as $message) {
                    echo $message . PHP_EOL;
                }
            }

            echo 'Done' . PHP_EOL;
        }

        ConsoleQueueModel::saveDone($command_name);
    }

    /**
     * actionCSRunAll - Коммуникации. Асинхронное исполнение всех действий
     */
    public function actionCSRunAllAsync()
    {
        echo 'actionCSRunAllAsync is starting' . PHP_EOL;

        ConsoleQueueModel::saveRunning('CS_RunAllAsync');

        $communications_service_params_list = CommunicationsServiceParamsModel::model()->findAll();

        if ($communications_service_params_list) {
            foreach ($communications_service_params_list as $communications_service_params) {
                echo 'Run #' . $communications_service_params->id . PHP_EOL;
                exec("php yiic utility cSRunAll --communications_service_params_id=" . $communications_service_params->id . " > /dev/null 2>/dev/null &");
            }
        }

        echo 'Done' . PHP_EOL;

        ConsoleQueueModel::saveDone('CS_RunAllAsync');
    }

    /**
     * SendMailerLetters - отсылка неотправленных писем
     * used ConsoleQueueModel
     *
     * @param int $hour_old
     */
    public function actionSendMailerLetters($hour_old = 0)
    {

        echo 'Starting "actionSendMailerLetters"...';

        if (!is_numeric($hour_old)) {
            echo PHP_EOL . 'Error! Hour parameter not numeric. Done';
            echo PHP_EOL;

            return;
        }

        if ($hour_old < 0) {
            echo PHP_EOL . 'Error! Hour parameter. Done';
            echo PHP_EOL;

            return;
        }

        if (\ConsoleQueueModel::checkRunning('SendMailerLetters') == true) {
            echo PHP_EOL . 'Error! Other process is running. Done';
            echo PHP_EOL;

            return;
        }
        ConsoleQueueModel::saveRunning('SendMailerLetters');

        try {
            $criteria = new CDbCriteria();
            $criteria->condition = 'status = :status';
            $criteria->params = [':status' => MailerLettersOutboxModel::STATUS_SEND];

            if ($hour_old) {
                $date_create = date('Y-m-d H:i:s', strtotime('-' . $hour_old . ' hours'));
                $criteria->condition .= ' AND date_create <= :date_create';
                $criteria->params[':date_create'] = $date_create;
            }

            $letter_model_list = MailerLettersOutboxModel::model()->findAll($criteria);
            MailerLettersOutboxModel::model()->updateAll(['status' => \MailerLettersOutboxModel::STATUS_IS_SENT], $criteria);

            if (empty($letter_model_list)) {
                echo PHP_EOL . 'Warning! Not sended letters not found. Done';
            } else {

                echo PHP_EOL . 'Find ' . count($letter_model_list) . ' not sended letters';
                $lich = 0;

                foreach ($letter_model_list as $letter_model) {
                    $source_model = $letter_model->mailerOutboxSources;

                    switch ($source_model->source) {
                        //SOURCE_GENERAL
                        case MailerLettersSourcesModel::SOURCE_GENERAL:
                            $mailer = new Mailer();
                            if (!$mailer->sendByModel($letter_model)) {
                                $mailer->setMarkSend();
                                echo PHP_EOL . 'Error send letter. Letter id:' . $letter_model->getPrimaryKey();
                            } else {
                                $mailer->setMarkSended();
                                $lich++;
                            }
                            break;

                        //SOURCE_COMMUNICATIONS
                        case MailerLettersSourcesModel::SOURCE_COMMUNICATIONS:
                            if ($source_model->params) {
                                $service_params_model = CommunicationsServiceParamsModel::model()->find('id = :id', [':id' => $source_model->params]);
                            } else {
                                $source_name = $source_model->getMessageSourceName();
                                if ($source_name) {
                                    $params = CommunicationsServiceParamsModel::model()->getUserParamsModel($source_name, $letter_model->user_create);
                                    if ($params !== false) {
                                        $json_params = json_decode($params->params);
                                        $letter_model->letter_from = $json_params->user_login;
                                        $letter_model->save();

                                        $mailer_sources_model = $letter_model->mailerOutboxSources;
                                        $mailer_sources_model->params = $params->id;
                                        $mailer_sources_model->save();
                                    } else {
                                        $letter_model->status = MailerLettersOutboxModel::STATUS_SEND;
                                        $letter_model->save();
                                        echo PHP_EOL . 'Error send. #' . $letter_model->getPrimaryKey() . '. Not service params';
                                        continue;
                                    }
                                }
                            }

                            if ($service_params_model == false) {
                                echo PHP_EOL . 'Error send. #' . $letter_model->getPrimaryKey() . '. Not service params';
                                continue;
                            }

                            // ACTION_SEND_MESSAGE
                            $result = (new CommunicationsSourceModel($service_params_model->source_name, $service_params_model->service_name, $service_params_model->user_id))
                                ->runAction(SourceFactory::ACTION_SEND_MESSAGE, $letter_model)
                                ->getResult();

                            if ($result['status'] == false) {
                                $letter_model->status = MailerLettersOutboxModel::STATUS_SEND;
                                $letter_model->save();

                                echo PHP_EOL . 'Error send. #' . $letter_model->getPrimaryKey() . '. ' . implode(' || ', $result['messages']);
                            } else {
                                $letter_model->status = MailerLettersOutboxModel::STATUS_SENDED;
                                $letter_model->save();

                                ActivityMessagesModel::updateDateEditByLetter($letter_model);

                                $lich++;
                            }
                            break;
                    }
                }

                echo PHP_EOL . 'Sended ' . $lich . ' letters. Done';
            }

            $criteria->params = [':status' => MailerLettersOutboxModel::STATUS_IS_SENT];
            MailerLettersOutboxModel::model()->updateAll(['status' => \MailerLettersOutboxModel::STATUS_SEND], $criteria);

        } catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
            ConsoleQueueModel::saveDone('SendMailerLetters');
        }
        ConsoleQueueModel::saveDone('SendMailerLetters');

        echo(PHP_EOL);
    }
}
