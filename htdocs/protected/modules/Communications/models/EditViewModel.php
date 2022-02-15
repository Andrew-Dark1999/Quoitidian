<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 17.01.18
 * Time: 17:33
 */

namespace Communications\models;


class EditViewModel extends \EditViewModel {


    public static function model($className=__CLASS__){
        return parent::model($className);
    }


    public static function modelR($alias = null, $dinamicParams = array(), $is_new_record = false, $cache_new_record = true, $className=__CLASS__){
        return parent::modelR($alias, $dinamicParams, $is_new_record, $cache_new_record, $className);
    }


    public function getDefaultModuleTitle(){
        return \Yii::t('communications', 'New chat');
    }



}
