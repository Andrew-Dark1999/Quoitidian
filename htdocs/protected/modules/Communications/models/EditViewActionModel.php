<?php
/**
 * EditViewActionModel
 */

namespace Communications\models;


class EditViewActionModel extends \EditViewActionModel{



    public function getInstanceEditViewModel($alias = null, $dinamicParams = array(), $is_new_record = false, $cache_new_record = true){
        return EditViewModel::modelR($alias, $dinamicParams, $is_new_record);
    }



}
