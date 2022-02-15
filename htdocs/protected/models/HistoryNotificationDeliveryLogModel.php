<?php

/**
 * HistoryNotificationDeliveryLogModel - планирование и отправка уведомлений пользователю

 * @author Alex R.
 */


class HistoryNotificationDeliveryLogModel{

    private $table_name = 'history_notification_delivery_log';


    public static function getInstance(){
        return new static();
    }


    public function getDate($frequency_sending, $next_period = false, $timestamp = null){
        $date = null;
        switch($frequency_sending){
            case NotificationSettingModel::ELEMENT_FS_INSTANTLY:
                return true;
            // hour
            case NotificationSettingModel::ELEMENT_FS_EVERY_HOUR:
                if($next_period){
                    if($timestamp){
                        $date = date('Y-m-d H:00:00', strtotime('+1 hour', $timestamp));
                    } else {
                        $date = date('Y-m-d H:00:00', strtotime('+1 hour'));
                    }
                } else {
                    if($timestamp){
                        $date = date('Y-m-d H:00:00', $timestamp);
                    } else {
                        $date = date('Y-m-d H:00:00');
                    }
                }
                break;
            // day
            case NotificationSettingModel::ELEMENT_FS_ONCE_OF_DAY:
                if($next_period){
                    if($timestamp){
                        $date = date('Y-m-d 00:00:00', strtotime('+1 day', $timestamp));
                    } else {
                        $date = date('Y-m-d 00:00:00', strtotime('+1 day'));
                    }
                } else {
                    if($timestamp){
                        $date = date('Y-m-d 00:00:00', $timestamp);
                    } else {
                        $date = date('Y-m-d 00:00:00');
                    }
                }
                break;
            // week
            case NotificationSettingModel::ELEMENT_FS_ONCE_OF_WEEK:
                if($next_period){
                    if($timestamp){
                        $date = date('Y-m-d 00:00:00', strtotime('+7 days', $timestamp));
                    } else {
                        $date = date('Y-m-d 00:00:00', strtotime('+7 days'));
                    }
                } else {
                    if($timestamp){
                        $date = date('Y-m-d 00:00:00', $timestamp);
                    } else {
                        $date = date('Y-m-d 00:00:00');
                    }
                }
                break;
        }

        return $date;
    }




    /**
     * checkSheduled - проверка расписания
     */
    public function checkSheduled($frequency_sending, $users_id){
        $nd_data = \DataModel::getInstance()
                        ->setFrom('{{' . $this->table_name . '}}')
                        ->setWhere('users_id = ' . $users_id)
                        ->findRow();


        $date = $this->getDate($frequency_sending, true, strtotime($nd_data['date_last_start']));

        if($date === null){
            return false;
        }
        if($date === true){
            return true;
        }

        if(strtotime($date) < strtotime(date('Y-m-d H:i:s'))){
            return true;
        }

        return false;
    }





    public function update($frequency_sending, $users_id = null, $time_stamp = null, $next_period = false){
        if($users_id === null) $users_id = WebUser::getUserId();

        $date = $this->getDate($frequency_sending, $next_period, $time_stamp);

        if($date === null || $date === true){
            $this->delete($users_id);
            return;
        }

        $nd_data = \DataModel::getInstance()
                        ->setFrom('{{' . $this->table_name . '}}')
                        ->setWhere('users_id = ' . $users_id)
                        ->findRow();


        if(empty($nd_data)){
            \DataModel::getInstance()->Insert(
                                        '{{' . $this->table_name . '}}',
                                        array('users_id' => $users_id, 'date_last_start' => $date));
        } else {
            \DataModel::getInstance()->Update(
                                        '{{' . $this->table_name . '}}',
                                        array('date_last_start' => $date),
                                        'users_id=' . $users_id);
        }
    }




    public function delete($users_id = null){
        if($users_id === null) $users_id = WebUser::getUserId();

        \DataModel::getInstance()->Delete('{{' . $this->table_name . '}}', 'users_id=' . $users_id);
    }



    public static function deleteOldRecords($limitDate){

        $model = self::getInstance();

        \DataModel::getInstance()->Delete('{{' . $model->table_name . '}}', 'date_last_start < "' . $limitDate . '"');

    }






}
