<?php
/**
 * TimeZonesModel
 * @author Alex R.
 */

class TimeZonesModel extends ActiveRecord
{
    public static $_setted_time_zone = false;

    public $tableName = 'time_zones';


    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }




    /**
     * установка time_zone для базы
     */
    public static function setTimeZone(){
        if(self::$_setted_time_zone) return;


        if(\Yii::app()->params['db']['set_default_timezone'] == false){
            self::$_setted_time_zone = true;
            return;
        }

        $time_zone = \Yii::app()->params['db']['time_zone'];

        \Yii::app()->db->createCommand("set time_zone = '".$time_zone."'")->execute();

        self::$_setted_time_zone = true;
    }



}
