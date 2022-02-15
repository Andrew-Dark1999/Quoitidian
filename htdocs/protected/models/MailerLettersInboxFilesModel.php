<?php



class MailerLettersInboxFilesModel extends ActiveRecord{

    private $_error = false;
    private $_messages = array();

    private $_result = array();

    private $_attachment_files = array();

    public $tableName = 'mailer_letters_inbox_files';


    public static function model($className=__CLASS__){
        return parent::model($className);
    }





    public function rules(){
        return array(
            array('mailer_id, uploads_id', 'safe'),
        );
    }



    public function addError($message, $params = array()){
        $this->_messages[] = Yii::t('communications', $message, $params);
        $this->_error = true;
        return $this;
    }



    public function getStatus(){
        return $this->_error ? false : true;
    }



    public function getResult(){
        if(!empty($this->_result['messages'])){
            array_merge($this->_messages, $this->_result['messages']);
            unset($this->_result['messages']);
        }
        return array(
                'status' => $this->getStatus(),
                'messages' => $this->_messages,
            ) + $this->_result;
    }



    public function getMessages(){
        return $this->_messages;
    }



    public function addMessage($message, $params = array()){
        $this->_messages[] = Yii::t('communications', $message, $params);
        return $this;
    }


    public function setAttachmentFiles($attachment_files){
        $this->_attachment_files = $attachment_files;
        return $this;
    }


    public function getAttachmentFiles(){
        return $this->_attachment_files;
    }



    public function verifyParameters($params){
        if(empty($params)){
            $this->addError('Params must have value');
            return false;
        }
        return true;
    }



    /**
     * Сохранение массива прикрепленных файлов
     */
    public function saveAttachmentFiles($params){

        $prepare_params = $this->prepareAttachmentFiles($params);
        if($prepare_params === false){
            return $this;
        }

        $this->setAttachmentFiles($prepare_params);

        $this->saveFilesToUploads();
    }



    /**
     * Подготовка параметров для сохранения
     */
    private function prepareAttachmentFiles($params){
        if($this->verifyParameters($params) === false){
            return false;
        }

        $files = array();

        foreach ($params['letter_attachments'] as $item) {
            if(!($is_model = (is_a($item, 'UploadsModel')))){
                $charset = mb_detect_encoding($item);
                $item = iconv($charset, "UTF-8", $item);
            }

            $path_tmp = $is_model ? $item->getFullFileName(true) : ($item);
            $file_name = $is_model ? $item->file_title : (new SplFileInfo($item))->getFilename();

            $file = array(
                'mailer_id' => $params['mailer_id'],
                'origin_file_name' => $is_model ? $item->file_title : $file_name,
                'tmp_file_name' => $is_model ? $item->file_name : $file_name,
                'path' => $path_tmp,
                'mime_type' => mime_content_type($path_tmp),
                'size' => filesize($path_tmp),
                'source_name' => 'email',
                'relate_key' => md5(date('YmdHis') . microtime(true) . mt_rand(1, 1000) . $file_name),
                'relate_path' => md5(md5(date('YmdHis') . microtime(true) . mt_rand(1, 1000) . $file_name)),
            );
            $files[] = $file;
        }
        return $files;
    }



    /**
     * Записываем прикрепленный файл в папку static/uploads/communications
     * и делаем соответствующую запись в таблицу Uploads
     */
    private function saveFilesToUploads(){

        foreach($this->_attachment_files as $file){

            $source_name = $file['source_name'];
            $path_temp = $file['path'];

            $uploads_model = new UploadsModel();
            $uploads_model->setScenario(self::getScenario());
            $path_upload = $uploads_model->getCommunicationsPath(true);

            if(!file_exists($path_upload)){
                if(!mkdir($path_upload, 0777)){
                    $this->addError('Error on creating directory "{dir}"', ["{dir}" => $path_upload]);
                    return false;
                }
            }

            $path_upload .= DIRECTORY_SEPARATOR . $source_name;
            if(!file_exists($path_upload)){
                if(!mkdir($path_upload, 0777)){
                    $this->addError('Error on creating directory "{dir}"', ["{dir}" => $path_upload]);
                    return false;
                }
            }

            if(!file_exists($path_temp)){
                $this->addError('"File {file}" not exist', ["{file}" => $file['origin_file_name']]);
                return false;
            }

            $uploads_model->file_path_copy = $path_temp;

            $uploads_model->relate_key = $file['relate_key'];
            $uploads_model->file_source = UploadsModel::SOURCE_COMMUNICATIONS;
            $uploads_model->file_path = $source_name . DIRECTORY_SEPARATOR . $file['relate_path'];
            $uploads_model->file_name = $file['origin_file_name'];
            $uploads_model->file_title = $file['origin_file_name'];
            $uploads_model->status = 'asserted';
            $uploads_model->copy_id = ExtensionCopyModel::MODULE_COMMUNICATIONS;

            if($uploads_model->save()){
                $file['uploads_id'] = (int)$uploads_model->id;

                if(!$this->saveNewFile($file)){
                    $this->addError('Error saving in table. Letter not saved and not sent');
                    $uploads_model->delete();
                    return $this;
                }
            }else{
                $this->addError('Error saving in table. Letter not saved and not sent');
                return $this;
            }
        }
        return $this;
    }



    private function saveNewFile($file){
        $file_model = new self();
        $file_model->mailer_id = $file['mailer_id'];
        $file_model->uploads_id = $file['uploads_id'];
        return $file_model->save();
    }



    public function getAttachmentModelsByMailerId($mailer_id){

        $attachments_model = self::findAll('mailer_id = :mailer_id', array(':mailer_id' => $mailer_id));

        if($attachments_model === null){
            return false;
        }

        $file_models = array();
        foreach ($attachments_model as $attachment_model){
            $file_models[] = UploadsModel::model()->findByPk($attachment_model->uploads_id);
        }

        return $file_models;
    }



    /**
     * Проверка общего размера загружаемых в письме файлов
     */
    public function checkAttachmentsSumSize($file_list = array()){
        $sum_size = 0;
        foreach ($file_list as $file_id){
            $file_model = (new UploadsModel())->findByPk($file_id);
            $sum_size += $file_model->getFileSize();
        }
        return ($sum_size < ParamsModel::getValueFromModel('mailer_max_size_of_attachments'));
    }



    public function copyModuleFiles($letter_data){
        if(!empty($letter_data)){

            foreach ($letter_data as $uploads_id){
                $uploads_model = UploadsModel::model()->findByPk($uploads_id);
                if($uploads_model !== null){

                    $path_upload = $uploads_model->getCommunicationsPath(true);

                    if(!file_exists($path_upload)){
                        if(!mkdir($path_upload, 0777)){
                            $this->addError('Error on creating directory "{dir}"', ["{dir}" => $path_upload]);
                            return false;
                        }
                    }

                    $path_upload .= DIRECTORY_SEPARATOR . $letter_data['type_comment'];
                    if(!file_exists($path_upload)){
                        if(!mkdir($path_upload, 0777)){
                            $this->addError('Error on creating directory "{dir}"', ["{dir}" => $path_upload]);
                            return false;
                        }
                    }

                    $new_uploads_model = new UploadsModel();
                    $new_uploads_model->setScenario(UploadsModel::SCENARIO_EMAIL_COPY_TO);
                    $new_uploads_model->relate_key = md5(date('YmdHis') . microtime(true) . mt_rand(1, 1000) . $uploads_model->file_name);
                    $new_uploads_model->file_path = $letter_data['type_comment'] . DIRECTORY_SEPARATOR . md5(md5(date('YmdHis') . microtime(true) . mt_rand(1, 1000) . $uploads_model->file_name));
                    $new_uploads_model->file_source = UploadsModel::SOURCE_COMMUNICATIONS;
                    $new_uploads_model->file_name = $uploads_model->file_name;
                    $new_uploads_model->file_title = $uploads_model->file_title;
                    $new_uploads_model->file_date_upload = $uploads_model->file_date_upload;
                    $new_uploads_model->date_create = $uploads_model->date_create;
                    $new_uploads_model->user_create = $uploads_model->user_create;
                    $new_uploads_model->status = 'asserted';
                    $new_uploads_model->file_path_copy = $uploads_model->getFullFileName(true);
                    if($new_uploads_model->save()){
                        $attributes = array();
                        $attributes['mailer_id']  = $letter_data['mailer_id'];
                        $attributes['uploads_id'] = $new_uploads_model->id;
                        if(!$this->saveNewFile($attributes)){
                            return false;
                        }
                    }else{
                        return false;
                    }
                }
            }
        }
    }

}
