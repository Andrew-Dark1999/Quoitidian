<?php
/**
 * Created by PhpStorm.
 * User: kastiel
 * Date: 04.05.2015
 * Time: 11:16
 */

class HistoryTaskMarkViewModel extends ActiveRecord {

    public $tableName = 'history_tasks_mark_view';

    /**
     * @param string $className
     * @return CActiveRecord
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}