<?php

class TestController extends Controller
{

    /**
     * filter
     */
    public function filters()
    {
        return [
            'checkAccess',
        ];
    }

    /**
     * filter проверка доступа
     */
    public function filterCheckAccess($filterChain)
    {
        switch (Yii::app()->controller->action->id) {
            case 'saveOutcomingMessage':
            case 'messageSendByModel':
            case 'messageHeader':
            case 'messageBody':
            case 'sendNotSendedLetters':
            case 'testUtility':
            case 'getEmailMessage':
            case 'runProcessSheduled':
            case 'showStartTimeLog':
            case 'testEmptyStartTimeLog':
            case 'testNotificationRun':
            case 'testNotificationCreateDateEndingBecome':
            case 'NotificationSetScheduled':
            case 'sendEmail':
            case 'showUnisenderLog':
            case 'mazdaScript':
            case 'runAll':
            case 'clearAllLogs':
            case 'CSRunAllAsync':
            case 'processActionsRun' :
            case 'prepareLettersHtmlToText' :
            case 'reLoadEmailLetters' :
            case 'changeDocument':
            case 'messageTranslate':
                if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_DELETE, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)) {
                    return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
                }
                break;
        }

        $filterChain->run();
    }

    /**
     * actionCSRunAll - Коммуникации. Асинхронное исполнение всех действий
     */
    public function actionCSRunAllAsync()
    {
        echo 'actionCSRunAllAsync is starting<br>';

        $communications_service_params_list = CommunicationsServiceParamsModel::model()->findAll();

        if ($communications_service_params_list) {
            foreach ($communications_service_params_list as $communications_service_params) {
                echo 'Run #' . $communications_service_params->id . '<br>';
                exec("php yiic utility cSRunAll --communications_service_params_id=" . $communications_service_params->id . " > /dev/null 2>/dev/null &");
            }
        }

        echo 'Done';
    }

    public function actionSendMailerLetters()
    {
        echo 'actionSendMailerLetters is starting<br>';
        exec("cd protected && php yiic utility sendMailerLetters > /dev/null 2>/dev/null &");
        echo 'Done';
    }

    /**
     * СОХРАНЕНИЕ ИСХОДЯЩЕГО ПИСЬМА
     */
    public function actionSaveOutcomingMessage()
    {

        $vars = [
            'user_id'            => 53,
            'letter_to'          => ['romcrazy13@gmail.com', 'romcrazy13@rambler.ru'],
            'letter_to_name'     => '',
            //'letter_subject' => 'Send Четверг Последняя проба',
            'letter_body'        => 'Body Send Четверг Последняя проба',
            'letter_attachments' => [
                '/var/www/team.craft/htdocs/static/uploads/tmp/Выделение_003.png',
                '/var/www/team.craft/htdocs/static/uploads/tmp/images.jpg',
                '/var/www/team.craft/htdocs/static/uploads/tmp/images (1).jpg',
                '/var/www/team.craft/htdocs/static/uploads/tmp/images (2).jpg',
                '/var/www/team.craft/htdocs/static/uploads/tmp/без названия.jpg',
                '/var/www/team.craft/htdocs/static/uploads/tmp/без названия (1).jpg',
                '/var/www/team.craft/htdocs/static/uploads/tmp/без названия (2).jpg',
            ],
        ];

        $result = (new MailerLettersOutboxModel())->saveNewLetter($vars)->getResult();

        $a = $result;

        var_dump($a);
    }

    /**
     * ОТПРАВКА ПО МОДЕЛИ
     */
    public function actionMessageSendByModel()
    {
        $vars = MailerLettersOutboxModel::model()->findByPk(66);

        $result = (new CommunicationsSourceModel('email', null, 53))
            ->runAction(SourceFactory::ACTION_SEND_MESSAGE, $vars)
            ->getResult();

        $a = $result;

        var_dump($a);
    }

    /**
     * ПРИЕМ ХИДЕРОВ
     */
    public function actionMessageHeader()
    {

        $start_uid = 2170;
        $start_date = strtotime("-5 day");
        $answer_only = true;
        $incoming_only = true;

        $d = date('Y.m.d H:i:s', $start_date);

        $vars = [
            //            'start_uid' => $start_uid,
            'start_date' => $start_date,
            //                'answer_only' => $answer_only,
            //                'incoming_only' => $incoming_only,
        ];

        $result = (new CommunicationsSourceModel('email', null, 53))
            ->runAction(SourceFactory::ACTION_GET_MESSAGE_HEADER, $vars)
            ->getResult();

        $a = $result;

        var_dump($a);
    }

    /**
     * ПРИЕМ ТЕЛА ПИСЬМА С ВЛОЖЕНИЯМИ
     */
    public function actionMessageBody()
    {

        $vars = [
            'uploads' => [
                'uid'             => 3190,
                'mailer_box_name' => '{imap.gmail.com:993/imap/ssl}INBOX',
            ]
        ];

        $result = (new CommunicationsSourceModel('email', null, 53))
            ->runAction(SourceFactory::ACTION_GET_MESSAGE_BODY, $vars)
            ->getResult();

        $result = (new MailerLettersInboxModel())->saveNewEmailLetters($result)->getResult();

        $a = $result;

        var_dump($a);

    }

    public function actionSendNotSendedLetters($hour_old = 0)
    {
        echo 'Starting "SendNotSendedLetters"...';

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

        if (\ConsoleQueueModel::checkRunning('SendNotSendedLetters') == true) {
            echo PHP_EOL . 'Error! Other process is running. Done';
            echo PHP_EOL;

            return;
        }
        ConsoleQueueModel::saveRunning('SendNotSendedLetters');
        restore_error_handler();
        try {
            $criteria = new CDbCriteria();
            $criteria->condition = 'status = :status';
            $criteria->params = [':status' => MailerLettersOutboxModel::STATUS_SEND];

            if ($hour_old) {
                $date_create = date('Y-m-d H:i:s', strtotime('-' . $hour_old . ' hours'));
                $criteria->condition .= ' AND date_create <= :date_create';
                $criteria->params[':date_create'] = $date_create;
            }

            $letters = MailerLettersOutboxModel::model()->findAll($criteria);
            MailerLettersOutboxModel::model()->updateAll(['status' => \MailerLettersOutboxModel::STATUS_IS_SENT], $criteria);

            if (empty($letters)) {
                echo PHP_EOL . 'Warning! Not sended letters not found. Done';
            } else {
                echo PHP_EOL . 'Find ' . count($letters) . ' not sended letters';
                $lich = 0;

                foreach ($letters as $letter) {
                    $source_model = (new MailerLettersSourcesModel())->getModelByMailerId($letter->mailer_id);
                    switch ($source_model->source) {
                        case MailerLettersSourcesModel::SOURCE_GENERAL:
                            $mailer = new Mailer();
                            if (!$mailer->sendByModel($letter)) {
                                $mailer->setMarkSend();
                                echo PHP_EOL . 'Error send letter. Letter id:' . $letter->getPrimaryKey();
                            } else {
                                $mailer->setMarkSended();
                                $lich++;
                            }
                            break;
                        case MailerLettersSourcesModel::SOURCE_COMMUNICATIONS:
                            if (empty($source_model->params)) {
                                if ($source_name = $source_model->getMessageSourceName()) {
                                    $params = CommunicationsServiceParamsModel::model()->getUserParamsModel($source_name, $letter->user_create);
                                    if ($params !== false) {
                                        $json_params = json_decode($params->params);
                                        $letter->letter_from = $json_params->user_login;
                                        if (empty($letter->letter_body)) {
                                            $letter->letter_body = ' ';
                                        }
                                        $letter->save();
                                        $mailer_sources_model = $letter->mailerOutboxSources;
                                        $mailer_sources_model->params = $params->id;
                                        $mailer_sources_model->save();
                                    } else {
                                        $letter->status = MailerLettersOutboxModel::STATUS_SEND;
                                        $letter->save();
                                        continue;
                                    }
                                }
                            } else {
                                $params = CommunicationsServiceParamsModel::model()->find('id = :id', [':id' => $source_model->params]);
                            }

                            $result = (new CommunicationsSourceModel($params->source_name, $params->service_name, $params->user_id))
                                ->runAction(SourceFactory::ACTION_SEND_MESSAGE, $letter)
                                ->getResult();
                            if (!$result['status']) {
                                foreach ($result['messages'] as $message) {
                                    echo PHP_EOL . $message;
                                }
                                echo PHP_EOL . 'Error send letter. Letter id:' . $letter->getPrimaryKey();
                            } else {
                                $lich++;
                            }
                            break;
                    }
                }

                echo PHP_EOL . 'Sended ' . $lich . ' letters. Done';
            }

        } catch (Exception $e) {
            ConsoleQueueModel::saveDone('SendNotSendedLetters');
        }
        ConsoleQueueModel::saveDone('SendNotSendedLetters');

        echo(PHP_EOL);
    }

    public function actionGetEmailMessage()
    {
        echo '<br>actionGetEmailMessage is starting...<br>';

        if (\ConsoleQueueModel::checkRunning('GetEmailMessage') == true) {
            echo PHP_EOL . 'Error! Other process is running. Done';
            echo PHP_EOL;

            return;
        }
        ConsoleQueueModel::saveRunning('GetEmailMessage');

        $result = (new EmailMessageModel())->getAndSaveMessages()->getResult();

        if (!$result['messages'] && is_array($result['messages'])) {
            foreach ($result['messages'] as $message) {
                echo PHP_EOL . $message;
            }
        }

        ConsoleQueueModel::saveDone('GetEmailMessage');

        echo '<br>actionGetEmailMessage just finished<br>';
    }

    public function actioaRunProcessSheduled()
    {
        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule();
        (new \Process\models\StartTimeModel())->startAllSchedules();
        echo 'RunSheduled done. ' . date('d.m.Y H:i:s');
    }

    public function actionShowStartTimeLog()
    {
        $data_model = \ProcessStartTimeLogModel::model()->findAll();
        $str = '';

        if (empty($data_model)) {
            echo 'Not log data';

            return;
        }

        foreach ($data_model as $data) {
            $str .= "<tr><td>$data->process_id</td><td>|</td><td>$data->operations_id</td><td>|</td><td>$data->date_create</td><td>|</td><td>$data->notation</td></tr>";
        }

        echo '<table><tbody>' . $str . '</tbody></table>';
    }

    public function actionEmptyStartTimeLog()
    {
        \ProcessStartTimeLogModel::model()->deleteAll();
        echo 'Empty done. ' . date('d.m.Y H:i:s');
    }

    public function actionNotificationRun()
    {
        echo 'Отсылаем уведомления</br>';
        echo 'Start... ' . date('H:i:s');
        \HistoryNotificationDelivery::getInstance()
            ->setController($this)
            ->run()
            ->getResult();
        echo '</br>End. ' . date('H:i:s');
    }

    public function actionNotificationCreateDateEndingBecome()
    {
        echo 'Формируем уведомления...</br>';

        (new HistoryActionsModel())
            ->setExtensionCopyArray(ExtensionCopyModel::getUsersModule())
            ->setHistory(\History::getInstance())
            ->setHistoryMessageIndex(HistoryMessagesModel::MT_DATE_ENDING_BECOME)
            ->setResponsibleOnly(true)
            ->run(HistoryActionsModel::ACTION_DATE_ENDING_BECOME);

        echo '</br>Готово!. ' . date('H:i:s');
    }

    public function actionNotificationSetScheduled()
    {
        echo 'Устанавливаем дату расписания</br>';
        echo 'Start... ' . date('H:i:s');
        \HistoryNotificationDeliveryLogModel::getInstance()
            ->update(
                $_GET['frequency_sending'],
                $_GET['users_id'],
                strtotime($_GET['date'])
            );

        echo '</br>End. ' . date('H:i:s');
    }

    public function actionSendEmail($hour_old = 0)
    {
        echo 'Start process...';

        if (!is_numeric($hour_old)) {
            echo 'Error: Hour parameter not numeric. Done';

            return;
        }

        if ($hour_old < 0) {
            echo 'Error Hour parameter. Done';

            return;
        }

        $criteria = new CDbCriteria();
        $criteria->condition = 'status = :status';
        $criteria->params = [':status' => MailerLettersOutboxModel::STATUS_SEND];

        if ($hour_old) {
            $date_create = date('Y-m-d H:i:s', strtotime('-' . $hour_old . ' hours'));
            $criteria->condition .= ' AND date_create <= :date_create';
            $criteria->params[':date_create'] = $date_create;
        }

        $letters = MailerLettersOutboxModel::model()->findAll($criteria);
        MailerLettersOutboxModel::model()->updateAll(['status' => \MailerLettersOutboxModel::STATUS_IS_SENT], $criteria);

        if (empty($letters)) {
            echo(PHP_EOL . 'Not sended letters not found. Done');
        } else {

            echo(PHP_EOL . 'Find ' . count($letters) . ' not sended letters. ');
            $lich = 0;

            foreach ($letters as $letter) {

                /**@var $letter MailerLettersOutboxModel */

                $mailer = new Mailer();
                if (!$mailer->sendByModel($letter)) {
                    $mailer->setMarkSend();
                    echo(PHP_EOL . '   Error send letter. Letter id:' . $letter->getPrimaryKey());
                } else {
                    $mailer->setMarkSended();
                    $lich++;
                }

            }

            echo(PHP_EOL . 'Sended ' . $lich . ' letters. Done' . PHP_EOL);

        }

    }

    public function actionShowUnisenderLog()
    {
        $data_model = \DataModel::getInstance()
            ->setFrom('{{unisender_log}}');
        if (!empty($_POST['date'])) {
            $data_model->setWhere('date_create between "' . $_POST['date'] . ' 00:00:00" AND "' . $_POST['date'] . ' 23:59:59"');
        }
        $data = $data_model->findAll();

        if (empty($data)) {
            echo 'no data';

            return;
        }

        echo '<table border="1">';
        foreach ($data as $value) {
            echo '<tr>';
            foreach ($value as $k => $v) {
                echo '<td>' . $v . '</td>?';
            }
            echo '</tr>';
        }

        echo '</table>';
    }

    public function actionMazdaScript()
    {
        /*
        * Создание и отправка отчета по медиа активностям
        */
        $vars = [
            'module_id'  => 1013,
            'fields'     => ['stat_campaign', 'stat_media', 'stat_format', 'stat_date', 'stat_screen'],
            'date_start' => date('Y-m-d 00:00:00', strtotime('-14 days')),
            'date_end'   => date('Y-m-d H:i:s'),

            'path_uploads' => 'static/uploads/modules/ps1013',
            'path_logo'    => 'static/uploads/company/mazdaLogo.png',
            'file_name'    => 'report_' . (date('Ymd_His')) . '.pdf',
        ];

        $delivery_list = [
            ['email_to' => 'roichik.ov@gmail.com', 'email_to_name' => ''],
        ];

        $path_base = \YiiBase::getPathOfAlias('application') . '/..';

        if (is_dir($path_base . '/' . $vars['path_uploads']) == false) {
            mkdir($path_base . '/' . $vars['path_uploads']);
        }

        $extension_copy = \ExtensionCopyModel::model()->modulesActive()->findByPk($vars['module_id']);
        $extension_copy->setAddId();

        $getData = function ($extension_copy) use ($vars) {
            $global_params = [
                'pci'             => null,
                'pdi'             => null,
                'finished_object' => null,
            ];

            $data_base = \DataListModel::getInstance()
                ->setExtensionCopy($extension_copy)
                ->setFinishedObject($extension_copy->getModule(false)->finishedObject())
                ->setThisTemplate(false)
                ->setGlobalParams($global_params)
                ->setSortingToPk('desc')
                ->setDefinedPK(false)
                ->setDataIfParticipant(false)
                ->setCreateFilterController(false)
                ->setGetAllData(true)
                ->setBeforeCondition('{{ms_base_statistika}}.stat_date between "' . $vars['date_start'] . '" AND "' . $vars['date_end'] . '"')
                ->prepare(\DataListModel::TYPE_LIST_VIEW)
                ->getData();

            $schema_fields = $extension_copy->getSchemaFields();
            $data = \DataValueModel::getInstance()
                ->setSchemaFields($schema_fields)
                ->setExtensionCopy($extension_copy)
                ->setFileType(\DataValueModel::FILE_TYPE_IMAGE)
                ->setFileThumbsSize(false)
                ->setFileReturnModel(true)
                ->setAddAvatar(true)
                ->prepareData($data_base)
                ->getProcessedData()// без обьеденения значений
                ->getData();

            return $data;

        };

        $params_model = \ParamsModel::model()->findAll();
        $data = $getData($extension_copy);
        if (empty($data)) {
            return;
        }

        $content = '';
        $mpdf = new mPDF(0, '', 11, 'Tahoma', 3, 5, 5, 5, 5, 5, 'P');

        foreach ($data as $row) {
            $fn = $row['stat_screen']['files']['stat_screen'][0]->getImageFoPdf();
            $content .= '
                <tr>
                    <td style=" font-family: Calibri; font-size: 8px; border:1px solid #000; border-collapse: collapse;">' . (!empty($row['stat_campaign']['value'][0]['value']) ? $row['stat_campaign']['value'][0]['value'] : '') . '</td>
                    <td style=" font-family: Calibri; font-size: 8px; border:1px solid #000; border-collapse: collapse;">' . $row['stat_media']['value'] . '</td>
                    <td style=" font-family: Calibri; font-size: 8px; border:1px solid #000; border-collapse: collapse;">' . $row['stat_format']['value'] . '</td>
                    <td style=" font-family: Calibri; font-size: 8px; border:1px solid #000; border-collapse: collapse;">' . ($row['stat_date']['value'] ? date('d.m.Y', strtotime($row['stat_date']['value'])) : '') . '</td>
                    <td style=" font-family: Calibri; font-size: 8px; border:1px solid #000; border-collapse: collapse; padding:2px">' . ($row['stat_screen']['files']['stat_screen'][0] ? '<img src="' . $fn . '" style="max-width:600px; max-height:170px"/>' : '') . '</td>
                </tr>
            ';
        }

        $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
            </head>
            <body style="margin: 0; padding: 0 0 0 0px; color: #000; font-family: Tahoma;">
                <section id="container" style="padding: 25px 40px;">
                    <div class="reportsData_logotype" style="height: 65px;">
                        <table>
                            <tr>
                                <td width="20"></td>
                                <td style="width: 230px; text-align: left; vertical-align: top;">
                                    <div style="font-size: 5px">&nbsp;</div>
                                    <img src="' . \ParamsModel::getValueFromModel('site_url', $params_model) . '/' . $vars['path_logo'] . '" height="43" width="240" />
                                </td>
                                <td width="20"></td>
                                <td style=" font-size:8px; text-align: left;" valign="top">
                                    <table align="left">
                                        <tr><td height="10"></td></tr>
                                        <tr>
                                            <td><p style="font-size:9px; font-weight: bold;">Отчет по медиа активностям</p> <br></td>
                                        </tr>
                                        <tr>
                                            <td style="font-size:8px;">
                                                <p>
                                                    <b>Период отчета</b> &nbsp;&nbsp;&nbsp;&nbsp;
                                                    ' . date('d.m.Y', strtotime($vars['date_start'])) . ' - ' . date('d.m.Y', strtotime($vars['date_end'])) . '
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <table width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #000; font-size:8px; border-collapse: collapse;">
                        <thead>
                            <tr style="" >
                                <td width="140" style="font-weight: bold; border:1px solid #000; height: 25px;">Рекламная кампания</td>
                                <td width="75" style="font-weight: bold; border:1px solid #000; height: 25px;">Тип медиа</td>
                                <td width="75" style="font-weight: bold; border:1px solid #000; height: 25px;">Формат</td>
                                <td width="75" style="font-weight: bold; border:1px solid #000; height: 25px;">Дата</td>
                                <td style="font-weight: bold; border:1px solid #000; height: 25px;">Скриншот</td>
                            </tr>
                        </thead>
                        <tbody>' . $content . '</tbody>
                    </table>
                </section>
            </body>
            </html>
        ';

        $mpdf->WriteHTML($html, $data, true);
        $mpdf->output($path_base . '/' . $vars['path_uploads'] . '/' . $vars['file_name'], 'F');

        UploadsModel::flushTempFiles();

        $letter_data = [];
        $letter_data['subject'] = "Отчет по медиа активностям";
        $letter_data['header'] = '';
        $letter_data['body'] =
            '
            <span style="color:#646464; font-size:15px;">Здравствуйте,</span>
            <br /><br />
            <span style="color:#646464; font-size:15px;">Направляем вам отчет по медиа активностям за период ' . date('d.m.Y', strtotime($vars['date_start'])) . ' - ' . date('d.m.Y', strtotime($vars['date_end'])) . '</span>
            <br />
            <span style="color:#646464; font-size:15px;">Отчет можно скачать <a target="_blank" href="' . \ParamsModel::getValueFromModel('site_url', $params_model) . '/' . $vars['path_uploads'] . '/' . $vars['file_name'] . '" style="color:#009edb!important;">по ссылке</a></span>
            <br /><br />
            <span style="color:#646464; font-size:15px;">С уважением, </br>Команда Quotidian</span>
            <br /><br />
            ';

        foreach ($delivery_list as $delivery) {
            $mailer = new \Mailer();
            $mailer
                ->setTemplate(\Mailer::LETTER_TEMPLATE0)
                ->setLetterData($letter_data)
                ->setLetter(
                    \ParamsModel::getValueFromModel('sending_out', $params_model),
                    \ParamsModel::getValueFromModel('sending_out_name', $params_model),
                    $delivery['email_to'],
                    $delivery['email_to_name'],
                    null,
                    [
                        '{site_url}'      => \ParamsModel::getValueFromModel('site_url', $params_model),
                        '{company_name}'  => \ParamsModel::getValueFromModel('crm_name', $params_model),
                        '{sales_email}'   => \ParamsModel::getValueFromModel('sales_email', $params_model),
                        '{support_email}' => \ParamsModel::getValueFromModel('support_email', $params_model),
                    ],
                    \MailerLettersOutboxModel::STATUS_IS_SENT
                );

            $mailer
                ->prepareLettesFromIdArray()
                ->send()
                ->setMarkSended()
                ->setMarkSend();

        }

    }

    /**
     * actionRunAll - Запуск всех запланированных действий (расписаний)
     */
    public function actionRunAll()
    {
        echo 'Starting...' . '<br><br>';

        $count = 0;
        if (Yii::app()->params['console_run_all']) {
            $commands = \DataModel::getInstance()
                ->setSelect()
                ->setFrom('{{console_executor}}')
                ->setWhere('status = "enable"')
                ->setOrder('sort')
                ->findAll();

            foreach ($commands as $item) {
                try {
                    echo '  Execution action ' . $item['method_name'] . '<br><br>';
                    if ((new ConsoleStartTimeModel())->startSchedule($item['id']) == false) {
                        echo '  Done' . '<br><br>';
                        continue;
                    }

                    $command = $item['method_name'];
                    $properties = null;

                    if ($item['properties']) {
                        try {
                            $properties = json_decode($item['properties'], true);
                        } catch (Exception $e) {
                            echo '  Error. Convert JSON array. Done' . '<br><br>';

                            return;
                        }

                    }

                    if ($properties && is_array($properties)) {
                        $this->{$command}(...$properties);
                    } else {
                        $this->{$command}();
                    }
                    $count++;
                    echo '  Done' . '<br><br>';
                } catch (Exception $e) {
                    echo '  Error. Done' . '<br><br>';
                };
            }
        }
        echo 'Executed ' . $count . ' action(s)' . '<br><br>';
        echo 'Done' . '<br><br>';
    }

    /**
     * actionClearAllLogs
     *
     * @param array ...$properties
     */
    public function actionClearAllLogs(...$properties)
    {
        if (!isset($properties)) {
            echo 'Error! Properties not found. Done' . '<br><br>';

            return;
        }

        if ($properties && is_array($properties)) {
            foreach ($properties as $item) {
                $limit_date = date('Y-m-d H:i:s', strtotime('-' . $item['limit_min'] . 'minutes'));
                $class_name = $item['class_name'];
                $class_name::deleteOldRecords($limit_date);
            }
        }
    }

    public function actionProcessActionsRun()
    {
        //const ACTION_CREATE_PROCESS_AFTER_CREATED_ENTITY         = 'cp_after_created_entity';
        //const ACTION_CREATE_PROCESS_AFTER_CHENGED_ENTITY         = 'cp_after_changed_entity';
        $properties = [
            'properties' => [
                'action_name' => Yii::app()->request->getParam('action_name'),
                'vars'        => [
                    'copy_id' => Yii::app()->request->getParam('copy_id'),
                    'data_id' => Yii::app()->request->getParam('data_id'),
                ],
            ],
        ];

        print_r($properties);
        echo '</br>';

        $r = (new \ConsoleRunAsync())
            ->setCommandProperties($properties)
            ->setActionName('processActionsRun')
            //->setAsync(false)
            ->exec()
            ->getResult();

        print_r($r);
    }

    /**
     * выводит список отформатированных входящих писем
     */
    public function actionPrepareLettersHtmlToText()
    {
        $criteria = new CDbCriteria();
        //$criteria->addInCondition('mailer_id', [2207, 2210, 2212, 2215, 2220, 2222,]);
        $criteria->addInCondition('mailer_id', [1920, 1934, 1944]);

        $criteria->addCondition('user_create = 50');

        $letters_inbox_model_list = \MailerLettersInboxModel::model()->findAll($criteria);

        if ($letters_inbox_model_list == false) {
            echo 'exit';

            return;
        }

        $body_list = [];

        foreach ($letters_inbox_model_list as $letters_inbox_model) {
            $html = $letters_inbox_model->letter_body;

            $text = (new HelperEmail())
                ->setHtmlText($html)
                ->htmlToText()
                ->clearResponseText()
                ->getHtmlText();

            $body_list[$letters_inbox_model->mailer_id] = $text;

            echo '<br><br><br><br><br>------------------------------------------------<br>';
            echo '---' . $letters_inbox_model->mailer_id . '---';
            echo '<br>------------------------------------------------<br>';
            echo $text;
        }

    }

    /**
     * Тестирование класса создания файла из шаблона на основании данных процсса
     *
     * @param $processId
     */
    public function actionChangeDocument($processId)
    {
        (new ZaoobedinennoegHandleDocument($processId))->run();
    }

    public function actionMessageTranslate()
    {
        $count = \Yii::app()->request->getParam('count');

        switch(strlen((string)$count)){
            case 1 : $tmp_count = $count; break;
            case 2 : $tmp_count = $count % 10; break;
            case 3 : $tmp_count = $count % 100; break;
            case 4 : $tmp_count = $count % 1000; break;
            case 5 : $tmp_count = $count % 10000; break;
        }

        $suffix = \Yii::t('communications', '1#participant|in_array(n,[2,3,4])#participants|in_array(n,[0,5,6,7,8,9])#participants', [$tmp_count]);

        echo $count . ' ' . $suffix;
    }
}
