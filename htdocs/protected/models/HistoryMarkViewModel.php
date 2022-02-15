<?php
/**
 * Created by PhpStorm.
 * User: kastiel
 * Date: 22.04.2015
 * Time: 12:09
 */

/**
 * Class HistoryExceptionModel
 * @property integer $user_id
 * @property integer $history_id
 */
class HistoryMarkViewModel extends ActiveRecord {

    public $tableName = 'history_mark_view';


    const DELIVERY_STATE_NONE   = 'none';
    const DELIVERY_STATE_SEND   = 'send';

    public $notification_delivery = self::DELIVERY_STATE_SEND;


    /**
     * @param string $className
     * @return CActiveRecord
     */
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }

    /**
     * @return array
     */
    public function relations(){
        return array(
            'history' => array(self::BELONGS_TO, 'HistoryModel', array('history_id' => 'history_id'))
        );
    }



    public function rules(){
        return array(
            array('user_id,history_id,is_view', 'numerical', 'integerOnly'=>true),
            array('notification_delivery', 'safe'),
        );
    }



    public static function setRead(){
        $update = \HistoryMarkViewModel::model()->updateAll(array('is_view' => '1'), 'user_id = ' . WebUser::getUserId());

        return (boolean)$update;
    }




}
