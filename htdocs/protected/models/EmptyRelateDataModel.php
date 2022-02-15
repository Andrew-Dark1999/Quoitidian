<?php

/**
* EmptyRelateDataModel - очищает связаные с модулем данные
* 
* @author Alex R.
*/


class  EmptyRelateDataModel {
    
    
    /**
    * Удаляем файлы, что были связаны с полями модуля 
    */ 
    public static function dropFiles($file_keys_for_delete){
        FileOperations::getInstance()->deleteAllFilesByField($file_keys_for_delete);
    } 
 

    /**
    * Удаляем сообщения из блока Активность 
    */ 
    public static function dropActivityMessages($copy_id){
        $activity_model = ActivityMessagesModel::model()->findAll('copy_id = ' . $copy_id);
        foreach($activity_model as $value){
            $value->delete();
        }
    } 


    /**
    * Удаляем связаных участников 
    */ 
    public static function dropParticipant($copy_id, $responsible = null){
        if($responsible === null)
            $participant_model = ParticipantModel::model()->deleteAll('copy_id = ' . $copy_id);
        if($responsible === true)
            $participant_model = ParticipantModel::model()->deleteAll('copy_id = ' . $copy_id . ' AND responsible = "1"');
        if($responsible === false)
            $participant_model = ParticipantModel::model()->deleteAll('copy_id = ' . $copy_id . ' AND responsible = "0"');
    } 


    /**
    * Удаляем права (только при удалении самого модуля) 
    */ 
    /*
    public static function dropPermission(){
        
    } 
    */


    /**
    * Удаляем права (только при удалении самого модуля) 
    */ 
    /*
    public static function dropUsersStorage(){
        
    } 
    */


    
    
    
    
    
    
    
    
}

