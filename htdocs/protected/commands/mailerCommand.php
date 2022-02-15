<?php

class mailerCommand extends ConsoleCommand
{
    /**
     * @param $fromName
     * @param $to
     * @param $subject
     * @param $body
     */
    public function actionSendAuto($from, $fromName, $to, $subject, $body)
    {
        echo 'Start send letter' . PHP_EOL;
        $mailer = (new Mailer())->createPhpMailer();
        //2
        $mailer->From = $from;
        $mailer->FromName = $fromName;
        $mailer->AddAddress($to);
        $mailer->Subject = $subject;
        $mailer->Body = $body;

        try {
            $errors = null;
            if ($mailer->Send()) {
                echo 'Letter sended' . PHP_EOL;
            }
        } catch (Exception $e) {
            $errors = 'Send error! ' . $e->getMessage();
        }

        if ($mailer->isError()) {
            $errors = $mailer->ErrorInfo;
        }

        if ($errors) {
            echo $errors . PHP_EOL;
        }

        echo 'Done' . PHP_EOL;
    }


    /**
     * @param $host
     * @param $port 465|587
     * @param $secure Options: '', 'ssl' or 'tls'
     * @param $userName
     * @param $pwd
     * @param $from
     * @param $fromName
     * @param $to
     * @param $subject
     * @param $body
     */
    public function actionSendSmtp($host, $port, $secure, $userName, $pwd, $from, $fromName, $to, $subject, $body)
    {
        echo 'Start send letter' . PHP_EOL;
        $mailer = new PHPMailer();
        //1
        $mailer->Host = $host;
        $mailer->Port = $port;
        $mailer->SMTPAuth = true;
        $mailer->Username = $userName;
        $mailer->Password = $pwd;
        $mailer->Mailer = 'smtp';
        $mailer->SMTPSecure = $secure;
        $mailer->CharSet = 'utf-8';
        $mailer->ContentType = "text/html";
        //2
        $mailer->From = $from;
        $mailer->FromName = $fromName;
        $mailer->AddAddress($to);
        $mailer->Subject = $subject;
        $mailer->Body = $body;

        try {
            $errors = null;
            if ($mailer->Send()) {
                echo 'Letter sended' . PHP_EOL;
            }
        } catch (Exception $e) {
            $errors = 'Send error! ' . $e->getMessage();
        }

        if ($mailer->isError()) {
            $errors = $mailer->ErrorInfo;
        }

        if ($errors) {
            echo $errors . PHP_EOL;
        }

        echo 'Done' . PHP_EOL;
    }

    /**
     * SendMailerLetters - отсылка неотправленных писем - ТЕСТОВАЯ. После отправки письво не ставиться в статус Отправно!!!!!!!!
     * used ConsoleQueueModel
     *
     * @param int $hour_old
     */
    public function actionSendMailerLettersTest($hour_old = 0)
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
                                //$mailer->setMarkSended();
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
