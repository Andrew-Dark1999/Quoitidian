<?php

/**
 * HistoryContainerModel
 */
class HistoryContainerModel{


    private static $_history_model_list = array();



    public static function addToHistoryModelList($history_model){
        if(empty($history_model)) return;

        self::$_history_model_list[] = $history_model;
    }


    /**
     * Список типов сообщений. Задает порядок для сохранения
     * @return array
     */
    public static function getMhIndexList(){
        return array(
            \HistoryMessagesModel::MT_CREATED,
            \HistoryMessagesModel::MT_OPERATION_CREATED_TASK,
            //\HistoryMessagesModel::MT_OPERATION_CREATED_NOTIFICATION,
            \HistoryMessagesModel::MT_PROCESS_RELATE_OBJECT_EMPTY,
            \HistoryMessagesModel::MT_PROCESS_MUST_APPOINT_RESPONSIBLE,
            \HistoryMessagesModel::MT_OPERATION_REJECTED,

            \HistoryMessagesModel::MT_OPERATION_MUST_CREATED_RECORD,
            \HistoryMessagesModel::MT_OPERATION_MUST_CHANGED_RECORD,

            \HistoryMessagesModel::MT_RESPONSIBLE_APPOINTED,
            \HistoryMessagesModel::MT_STATUS_CHANGED,
            \HistoryMessagesModel::MT_DATE_ENDING_CHANGED,

            \HistoryMessagesModel::MT_COMMENT_CREATED,
            \HistoryMessagesModel::MT_COMMENT_CHANGED,

            \HistoryMessagesModel::MT_FILE_UPLOADED,
            \HistoryMessagesModel::MT_FILE_DELETED,

            \HistoryMessagesModel::MT_COMMENT_DELETED,

            \HistoryMessagesModel::MT_DELETED,

            \HistoryMessagesModel::MT_ENABLE_MODULE_ACCESS,
            \HistoryMessagesModel::MT_DISABLE_MODULE_ACCESS,
            \HistoryMessagesModel::MT_CHANGED_MODULE_ACCESS,
        );
    }


    /**
     * save
     */
    public static function save($loggin_respponsible_only = false){
        if(empty(self::$_history_model_list)) return;

        $mh = self::getMhIndexList();

        foreach($mh as $hm_index){
            foreach(self::$_history_model_list as $history_model){
                if($hm_index === (integer)$history_model->history_messages_index){
                    $history_model
                        ->setPrepareParams(false)
                        ->setLogginResponsibleOnly($loggin_respponsible_only)
                        ->save();
                }
            }
        }

        self::$_history_model_list = array();
    }


}
