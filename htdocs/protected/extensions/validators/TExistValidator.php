<?php

/**
 * Autor Alex R.
 */
class TExistValidator extends CUniqueValidator{

    public static $active_alias = null;



    protected function getModel($className){
        if(self::$active_alias === null){
            return CActiveRecord::model($className);
        }

        return ActiveRecordClone::getModel(self::$active_alias);
    }


}
