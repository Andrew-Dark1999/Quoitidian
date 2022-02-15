<?php
/**
 * Class UsersStorageModel
 */

namespace Reports\models;

class ExtUsersStorageModel extends \UsersStorageModel{


    const TYPE_LIST_PAGINATION_REPORT  = 83;    // пагинация в ListView отчета


    public static function model($className=__CLASS__){
        return parent::model($className);
    }







}
