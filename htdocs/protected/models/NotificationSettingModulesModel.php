<?php


class NotificationSettingModulesModel extends \ActiveRecord{

    public $tableName = 'users_notification_setting_modules';


    public static function model($className=__CLASS__){
        return parent::model($className);
    }



    public function rules(){
        return array(
            array('notification_setting_id, copy_id', 'required'),
            array('notification_setting_id, copy_id', 'numerical', 'integerOnly'=>true),
        );
    }



    public function relations(){
        return array(
            'notificationSetting' => array(self::BELONGS_TO, 'ProfileNotificationSettingModel', 'notification_setting_id'),
        );
    }




    /**
     * deleteData
     */
    public static function deleteData($notification_setting_id){
        if(empty($notification_setting_id)) return;
        static::model()->deleteAll('notification_setting_id=' . $notification_setting_id);
    }




    /**
     * insertData
     */
    public static function insertData($notification_setting_id, array $copy_id_list){
        static::deleteData($notification_setting_id);

        if(empty($notification_setting_id)) return;
        if(empty($copy_id_list)) return;


        // записываем новые значения
        if(empty($copy_id_list)) return;

        foreach($copy_id_list as $copy_id){
            $values = array('notification_setting_id' => $notification_setting_id, 'copy_id' => $copy_id);
            $model = new ProfileNotificationSettingModulesModel();
            $model->setAttributes($values);
            $model->insert();
        }
    }





}
