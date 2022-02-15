<?php

/**
* ProfileActivityBuilder - конструктор блока Активность в профиле пользователя 
* 
* @author Alex R.
* @version 1.0 
* 
*/
class ProfileActivity extends CWidget {

    public $history_data;

    private $_notification_position;
    
    private $_current_date = null;
    
    private $_html = [];
    private $_link_actions = [];

    public function init(){
        if(isset($_GET['last_date']) && !empty($_GET['last_date']))
            $this->_current_date = date('Y-m-d 00:00:00', strtotime($_GET['last_date']));
        if(isset($_GET['notification_position']) && !empty($_GET['notification_position']))
            $this->_notification_position = $_GET['notification_position'];
            
        $this->buildConstructorPage();

        return $this;
    }

    /**
    * строит елементы страницы Активности
    * @return string (html) 
    */
    public function buildConstructorPage(){
        if(empty($this->history_data)) return $this;
        foreach($this->history_data as $value_data){
            $this
                ->addPeriod($value_data)
                ->addNotification($value_data);
           
            $this->_current_date = $value_data->date_create;
        }
        Yii::app()->user->setFlash('notification_position', $this->_notification_position);

        return $this;
    }
    
    

    public function getResult(){
        return [
            'html' => ($this->_html ? implode('', $this->_html) : ''),
            'link_actions' => $this->_link_actions,
        ];
    }


    /**
     * Добавляем период
     */
    private function addPeriod($value_data){
        if(empty($value_data)) return $this;
        if(empty($value_data->date_create)) return $this;
        if($this->_current_date == null || !DateTimeOperations::isDateEquality($this->_current_date, $value_data->date_create)){
        
            $today = false;
            $date_str = Yii::t('UsersModule.base', 'Undefined');
            
            if(strtotime($value_data->date_create)){
                if(date('Y-m-d', strtotime($value_data->date_create)) == date('Y-m-d')){
                    $today = true;
                    $date_str = Yii::t('UsersModule.base', 'Today');
                } elseif(date('Y-m-d', strtotime($value_data->date_create)) == date('Y-m-d', strtotime('-1 day'))){
                    $date_str = Yii::t('UsersModule.base', 'Yesterday');
                } else
                    $date_str = DateTimeOperations::getFullDateStr($value_data->date_create);
            }
            
            $this->_html[] = $this->render('period', array(
                                        'today' => $today,
                                        'date_str' => $date_str,
                                     ), true
            );
        }
        
        return $this;
    }
    
    

    /**
     * возвращает расположение умедомления: левое/правое
     */
    private function getNotificationPosition(){
        if($this->_notification_position ===  null || $this->_notification_position == 'pull-right'){
            $this->_notification_position = 'pull-left';
            return 'pull-left';
        } elseif($this->_notification_position == 'pull-left') {
            $this->_notification_position = 'pull-right';
            return 'pull-right';
        } 
            
    }
    
    
    /**
     * возвращает параметры уведомления: цвет текста, иконки...
     */
    private function getNotificationInterfaceParams($message_data){
        $params = array(
                    'class_color' => $message_data['message_data']['ico'],
                    'icon' => $message_data['message_data']['class_color'],
        );

        return $params;
    }


    /**
     * prepareParams
     * @return bool
     */
    private function prepareParams($hitory_model, &$params_object){

        static $user_model = null;
        if(!$user_model){
            ExtensionCopyModel::model()->findByPk(1)->getModule(null);
            $user_model = UsersModel::model()->findByPk($hitory_model->user_create);
        }

        if(!$hitory_model->getParams($params_object)){
           return false;
        }

        if($user_model){

            $params_object['{user_full_name}'] = $user_model->getFullName();

            if($hitory_model->history_messages_index == HistoryMessagesModel::MT_RESPONSIBLE_APPOINTED && isset($params_object['{user_id}'])) {

               if(Yii::app()->user->id != $params_object['{user_id}']){

                   static $user_model_appointed = null;
                   static $user_id_appointed = 0;

                   if($user_id_appointed != $params_object['{user_id}']){
                      $user_model_appointed = UsersModel::model()->findByPk($params_object['{user_id}']);
                   }
                    
                   if($user_model_appointed)
                    $params_object['{user_full_name}'] = $user_model_appointed->getFullName();
               }

            }
        }

        return true;
    }



    /**
     * возвращает контент уведомления
     */
    private function getContent($hitory_model, $params){
        return null;
    }

    /**
     * Добавляем уведомления 
     */
    private function addNotification($hitory_model){

        $params = array();
        if(empty($hitory_model) || !$this->prepareParams($hitory_model, $params)){
           return $this;
        }

        $history_messages_model = HistoryMessagesModel::getInstance()
                                        ->setObjectName(HistoryMessagesModel::OBJECT_ACTIVITY)
                                        ->setMessageParams($params)
                                        ->setHistoryModel($hitory_model)
                                        ->prepare();

        $message_data = $history_messages_model->getResult();

        $position = $this->getNotificationPosition();
        $this->_html[] = $this->render('notification', array(
                                    'interface_params' => $this->getNotificationInterfaceParams($message_data),
                                    'position' => $position,
                                    'subject' => $message_data['message_data']['subject'],
                                    'message' => $message_data['message_data']['message'],
                                    'content' => $this->getNotificationContent($hitory_model, $params, $position),
                                    'datetime_old' => DateTimeOperations::getDateTimeOldStr($hitory_model->date_create),
                                 ), true
        );

        if($message_data['link_actions']){
            $this->_link_actions = array_merge($this->_link_actions, $message_data['link_actions']);
        }
        
        return $this;
    }
    

    private function getNotificationContent($hitory_model, $params, $position){
        return $this->getNotificationContentText($hitory_model, $params)  . $this->getNotificationContentFile($hitory_model, $params, $position);
        
    }

    /**
     * Добавляем текстовый контент 
     */
    private function getNotificationContentText($hitory_model, $params){
        $result = '';
        $content = $this->getContent($hitory_model, $params);
        if(empty($content)) return $result;

        $result = $this->render('notification-content-text', array(
                                    'content' => $content,
                                 ), true
        );
        
        return $result;
    }



    /**
     * Добавляем файлы к уведомлению
     */
    private function getNotificationContentFile($hitory_model, $params, $position){
        $result = '';
        switch($hitory_model->history_messages_index){
            // uploads
            case HistoryMessagesModel::MT_COMMENT_CREATED:
            case HistoryMessagesModel::MT_COMMENT_CHANGED:
            case HistoryMessagesModel::MT_FILE_UPLOADED:
                if(!empty($params['{file_title}'])) {
                    if (is_array($params['{file_title}'])) {
                        foreach ($params['{file_title}'] as $key => $name) {
                            $uploads_model = UploadsModel::model()->findByPk($params['{uploads_id}'][$key]);
                            if($uploads_model) {
                               $result .= $this->getContentFileUploads($uploads_model, $position);
                            } else {
                               $result .= $this->getContentFileDeleted($name, $position);
                            }
                        }
                    } else {
                        $uploads_model = UploadsModel::model()->findByPk($params['{uploads_id}']);
                        if($uploads_model) {
                           $result .= $this->getContentFileUploads($uploads_model, $position);
                        } else {
                           $result .= $this->getContentFileDeleted($params['{file_title}'], $position);
                        }
                    }
                }
                break;
            // deleted
            case HistoryMessagesModel::MT_FILE_DELETED:
                if(!empty($params['{file_title}'])) {
                    if (is_array($params['{file_title}'])) {
                        foreach ($params['{file_title}'] as $name) {
                            $result .= $this->getContentFileDeleted($name, $position);
                        }
                    } else {
                        $result .= $this->getContentFileDeleted($params['{file_title}'], $position);
                    }
                }
            break;
        }
                
        return $result;         
    }

    
    /**
     * @param $position
     * @param $uploads_model
     */
    private function getContentFileUploads($uploads_model, $position){
        $file_type = 'attachments';
        if(!empty($uploads_model) && $uploads_model->thumbs == '0') 
            $file_type = 'file';        
        
        return $this->render('notification-content-file-uploads', array(
                'upload_model' => $uploads_model,
                'position' => $position,
                'file_type' => $file_type,
            ), true
        );

    }
    



    /**
     * @param $position
     * @param $uploads_model
     */
    private function getContentFileDeleted($file_name, $position){

        if(!$file_name){
            return '';
        }

        $uploads_model = new UploadsModel();
        $type = $uploads_model->getFileType($file_name);
        $type_class = $uploads_model->getFileTypeClass($type);
        
        $file_data = array(
            'class_file_type' => $type_class,
            'file_type' => $type,
            'file_name' => $file_name,
        );
        
        return $this->render('notification-content-file-deleted', array('file_data' => $file_data, 'position' => $position), $file_data, true);
    }

    
 
    
    
}
