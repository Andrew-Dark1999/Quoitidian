<?php
/**
* Activity widget  
* @author Alex R.
* @version 1.0
*/ 

class Activity extends CWidget{

    // Содержание кнопки-переключателя "Комментарий"
    public $type_comment_list = array(ActivityMessagesModel::TYPE_COMMENT_GENERAL);
    public $module_title;

    public $edit_view_buider_model;

    private $access_check_params = array();
    
    public $view = 'block_activity';
    public $schema;
    public $extension_copy;
    public $data_id = null; // id записи родительского модуля
    public $activity_messages_model_list;
    public $activity_messages_model;
    public $upload_data = array();
    public $attachents_buttons = array('download_file');
    public $attachments_image_thumb_size = null;




    protected function getViewPathAlias(){
        return 'ext.ElementMaster.EditView.Elements.Activity.views.';
    }


    private function getView($view = null, $add_path_alias = true){
        if($view === null){
            $view = $this->view;
        }

        if($add_path_alias){
            return $this->getViewPathAlias() . $view;
        } else {
            return $view;
        }
    }






    public function init(){
        if(!empty($this->extension_copy)){
            if(Access::moduleAdministrativeAccess($this->extension_copy->copy_id)){
                $this->access_check_params = array(
                    'access_id' => RegulationModel::REGULATION_SYSTEM_SETTINGS,
                    'access_id_type' => Access::ACCESS_TYPE_REGULATION,
                );
            } else {
                $this->access_check_params = array(
                    'access_id' => $this->extension_copy->copy_id,
                    'access_id_type' => Access::ACCESS_TYPE_MODULE,
                );
            }
        }

        $html = '';
        switch($this->view){
            case 'block_activity' :
                $html = $this->getBlockActivity();
                break;
            case 'message_one' :
                $block_attachments = $this->getBlockAttachments($this->activity_messages_model);
                $data = array(
                    'activity_messages_model' => $this->activity_messages_model,
                    'extension_copy' => $this->extension_copy,
                    'block_attachments'=> $block_attachments,
                    'access_check_params' => $this->access_check_params,
                );
                $html = $this->getMessage($data); 
                break;
            case 'message_only' :
                $html = $this->activity_messages_model->message;
                break;
            case 'attachments' :
                $html = $this->getAttachments($this->activity_messages_model);
                break;
            case 'attachments_one' :
                $html = $this->getAttachmentsOne($this->upload_data);
                break;
            case 'block_messages' :
                $html = $this->getBlockMessages();
                break;
        }
        echo $html;        
    }
 





    //block_activity
    private function getBlockActivity(){
        $content = array(
            'extension_copy' => $this->extension_copy,
            'block_attachments' => $this->getBlockAttachments(null),
            'block_message' => $this->getBlockMessages(),
        );

        $editor_buttons = $this->getEditorButtons();
        $editors = $this->getEditors($content, $editor_buttons);

        return $this->render($this->getView('block_activity'), array(
                                        'editors' => $editors,
                                        'editor_buttons' => $editor_buttons,
                                        'content' => $content,
                                        ), true);
        
    }



     public function getBtnShowChannel(){
        return true;
     }


    /**
     * Возвращает блоки ввода сообщений
     */
    private function getEditors($content, $editor_buttons){
        $html = '';
        $active = true;
        foreach ($this->type_comment_list as $type_comment){
            $vars = [
                'active' => $active,
                'type_comment' => $type_comment,
                'content' => $content,
                'editor_buttons' => $editor_buttons,
            ];

            $html .=  $this->render($this->getView('editors'), $vars, true);

            $active = false;
        }
        return $html;
    }


    /**
     * getBtnChannelLastDataId - возвращает data_id сещности канала исходя из последнего отправленного уведомления пользователем
     */
    public function getBtnChannelLastDataId($channel_copy_id){
        if($this->activity_messages_model_list == false){
            return;
        }

        foreach($this->activity_messages_model_list as $activity_messages_model){
            if($activity_messages_model->copy_id != $channel_copy_id){
                continue;
            }


            if($activity_messages_model->mailerInboxRelate){
                continue;
            }

            if($activity_messages_model->data_id){
                return $activity_messages_model->data_id;
            }
            break;
        }
    }


    public function getBtnChannelHtml(){
        $vars = array(
            'extension_copy' => $this->extension_copy,
            'data_id' => $this->data_id,
        );

        $ddl_data = (new \DropDownListModel())
                        ->setActiveDataType(\DropDownListModel::DATA_TYPE_9)
                        ->setVars($vars)
                        ->setDefaultDataId($this->getBtnChannelLastDataId(DropDownListModel::getChannelCopyId()))
                        ->prepareHtml()
                        ->getResultHtml();

        if($ddl_data['status']){
            return $ddl_data['html'];
        }
    }




    public function getBtnSwitchTypeCommentTitleList(){
        return ActivityMessagesModel::getTypeCommentTitleList($this->type_comment_list);
    }




    public function showBtnSwitchTypeComment(){
        return (count($this->type_comment_list) <= 1 ? false : true);
    }



    /**
     * Возвращает кнопки блоков ввода сообщений
     */
    private function getEditorButtons(){
        return $this->render($this->getView('editor_buttons'), null, true);
    }



    //block_messages
    private function getBlockMessages(){
        $messages = '';
        if($this->activity_messages_model_list){
            foreach($this->activity_messages_model_list as $activity_messages_model){
                $this->attachents_buttons = array('download_file', 'delete_file');
                if($activity_messages_model->user_create != Yii::app()->user->id) $this->attachents_buttons = array('download_file',);
                
                $block_attachments = $this->getBlockAttachments($activity_messages_model);
                $data = array(
                    'activity_messages_model' => $activity_messages_model,
                    'extension_copy' => $this->extension_copy,
                    'block_attachments'=> $block_attachments,
                    'access_check_params' => $this->access_check_params,
                );
                $messages.= $this->getMessage($data);
            }
        }

        return $this->render(
                        $this->getView('block_messages'),
                        array('messages'=> $messages,),
                        true
                    );
    }



    //message  
    private function getMessage($data){
        return $this->render($this->getView('message'), $data, true);
    }



    private function getAttachmentsButtons($activity_messages_model){
            switch($activity_messages_model->type_comment){
            case ActivityMessagesModel::TYPE_COMMENT_GENERAL :
                $this->attachents_buttons = array('download_file', 'delete_file');
                break;
            case ActivityMessagesModel::TYPE_COMMENT_EMAIL :
                $this->attachents_buttons = array('download_file');
                break;
        }
    }



    //block_attachment
    private function getBlockAttachments($activity_messages_model){
        $attachments = $this->getAttachments($activity_messages_model);

        return $this->render(
                        $this->getView('block_attachments'),
                        array(
                            'attachments' => $attachments
                        ),
                        true
                    );
    }



    //attachment
    private function getAttachments($activity_messages_model){
        $attachments = '';
        if(!empty($activity_messages_model)){
            $relate_key = $activity_messages_model->attachment;
            $upload_model = null;

            if(!empty($relate_key)){
                    $upload_model = UploadsModel::model()->setRelateKey($relate_key)->findAll();
            } 
            
            switch ($activity_messages_model->type_comment){
                case $activity_messages_model::TYPE_COMMENT_GENERAL :
                    $thumb_size = $this->attachments_image_thumb_size;
                    $block_class = 'col-xs-12';
                    break;
                case $activity_messages_model::TYPE_COMMENT_EMAIL :
                    $thumb_size = 60;
                    $block_class = 'col-xs-6';
                    break;
                default :
                    $thumb_size = $this->attachments_image_thumb_size;
                    $block_class = 'col-xs-12';
                    break;
            }

            if(!empty($upload_model)){
                foreach($upload_model as $upload_value){
                    $this->getAttachmentsButtons($activity_messages_model);
                    $data = array(
                                'schema' => $this->schema,
                                'extension_copy' => $this->extension_copy,
                                'extension_data' => $activity_messages_model,
                                'upload_value' => $upload_value,
                                'attachments_image_thumb_size' => $thumb_size,
                                'attachments_image_block_class' => $block_class,
                            );
                    $attachments.= $this->render($this->getView('attachments'), $data, true);
                }
            }        
        }
        return $attachments;
    }



    //attachment_one
    private function getAttachmentsOne($upload_data){
        $data = array(
                    'schema' => $this->schema,
                    'extension_copy' => $this->extension_copy,
                    'extension_data' => null,
                    'upload_value' => $upload_data,
                    'attachments_image_thumb_size' => $this->attachments_image_thumb_size,
                    'attachments_image_block_class' => 'col-xs-6',
                );

        return $this->render($this->getView('attachments'), $data, true);
    }


}
