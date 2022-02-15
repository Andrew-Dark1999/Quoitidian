<?php

/**
 * DocumentsModel
 * 
 * @copyright 2016
 */
class DocumentsModel
{

    const DATA_TABLE_NAME = 'documents_templates_data';

    /**
     * По relate key ищем запись и правим таблицу соответсвия документов
     */
    public static function updateUploadsParents($model){
        
        $extension_copy = \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_DOCUMENTS);
        $field = DocumentsModel::getField($extension_copy);
        
        if($field) {
            $data = DataModel::getInstance()
                ->setFrom($extension_copy->getTableName())
                ->setWhere($field . '=:data_id', array(':data_id' => $model->relate_key))
                ->FindRow();
            
            if(!empty($data)) {
                if($data['this_template']) {
                    //удаление шаблона
                    $upload_data = UploadsParentsModel::model()->findAllByAttributes(array('parent_upload_id'=>$model->id));
                    if(count($upload_data)>0) {
                        //пробегаем по всему массиву конечных привязанных документов и указываем id записи 
                        foreach($upload_data as $v){
                            if($v->upload_id){
                                $upload = UploadsModel::model()->findByPK($v->upload_id);
                                if($upload !== null) {
                                    $data_children = DataModel::getInstance()
                                        ->setFrom($extension_copy->getTableName())
                                        ->setWhere($field . '=:data_id', array(':data_id' => $upload->relate_key))
                                        ->FindRow();
                                    if(!empty($data_children)) {
                                        //правим таблицу соответствия
                                        $v->parent_upload_id = 0;
                                        $v->doc_id = $data_children[$extension_copy->prefix_name . '_id'];
                                        $v->save();
                                    }
                                }
                            }
                        }

                    }
                }else {
                    //удаление конечного документа
                    $upload_data = UploadsParentsModel::model()->findByAttributes(array('upload_id'=>$model->id));
                    //правим таблицу, указываем вместо upload_id id записи
                    if($upload_data !== null){
                        $upload_data->upload_id = 0;
                        $upload_data->doc_id = $data[$extension_copy->prefix_name . '_id'];
                        $upload_data->save();
                    }    
                }
            }
        }   
    }
    
    
    /**
     * Получаем название поля с генерируемым документом
     */
    public static function getField($extension_copy){
        
        $schema_parse = $extension_copy->getSchemaParse();
        $field = false;
        
        //по схеме ищем элемент типа Файл со скрытым атрибутом
        foreach($schema_parse['elements'] as $value){
            if(isset($value['field']) && $value['field']['params']['type']=='file') {
                if(isset($value['field']['params']['file_generate'])) {
                    if($value['field']['params']['file_generate']) {
                        $field = $value['field']['params']['name'];
                        break;
                    }
                }
            }
        }
        
        return $field;  
    }
    

    /**
     * Копирование конечного файла из таблицы соответсвия
     */
    public static function copyDocumentFromExtensionCopy($extension_copy, $extension_data){
        
        $result = false;
        $field = DocumentsModel::getField($extension_copy);
        
        if($field) {
        
            $doc_id = $extension_data->{$extension_copy->prefix_name . '_id'};
            $upload_params = UploadsParentsModel::model()->findByAttributes(array('doc_id'=>$doc_id));
            
            if($upload_params !== null) {
                if($upload_params->parent_doc_id) {
                    
                    $parent_data = DataModel::getInstance()
                        ->setFrom($extension_copy->getTableName())
                        ->setWhere($extension_copy->prefix_name . '_id=:data_id', array(':data_id' => $upload_params->parent_doc_id))
                        ->FindRow();
                    
                    if(!empty($parent_data)) {
                        if(isset($parent_data[$field])){
                            if($parent_data[$field]) {
                                
                                $parent_upload = UploadsModel::model()->setRelateKey($parent_data[$field])->find();
                                if($parent_upload!==null) {
                                
                                    //есть запись родительского шаблона
                                    $relate_key = date('YmdHis') . microtime(true) . mt_rand(1, 1000) . $parent_upload->file_name;
                                    $relate_key = md5($relate_key);
                                    
                                    $uploads_model = new UploadsModel();
                                    $uploads_model->relate_key = $relate_key;
                                       
                                    $uploads_model->file_source = 'module';
                                    $uploads_model->file_name = $parent_upload->file_name;
                                    $uploads_model->file_title = $parent_upload->file_title;
                                    $uploads_model->status = 'temp';
                                    $uploads_model->copy_id = $extension_copy->copy_id;
                                    
                                    $uploads_model->save();
                                    
                                    $path_module = UploadsModel::getUploadPathModule();
                                    
                                    if(copy($path_module . DIRECTORY_SEPARATOR . $parent_upload->file_path . DIRECTORY_SEPARATOR . $parent_upload->file_name, $path_module . DIRECTORY_SEPARATOR . $uploads_model->file_path . DIRECTORY_SEPARATOR . $uploads_model->file_name)){

                                        //файл был сохранен
                                        //отмечаем в записи relate key
                                        $data_model = new \DataModel();
                                        $data_model
                                            ->setText('UPDATE ' . $extension_copy->getTableName() . ' SET '. $field .' = "'. $relate_key .'" WHERE ' . $extension_copy->prefix_name . '_id=' . $doc_id)
                                            ->execute();
                                        
                                        //правим таблицу соответсвий
                                        $upload_params->upload_id = $uploads_model->id;
                                        $upload_params->save();
                                        
                                        $result = $uploads_model;

                                    }else
                                        $uploads_model->delete();
                                }
                            }
                        }
                    }    
                } 
            }
        }
        
        return $result;
       
    }
    
    
    /**
     * Для загружаемого документа, если это шаблон модудя Документы правим таблицу соответствий загрузок
     */
    public static function updateUploadsChilds($files){
        
        $extension_copy = \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_DOCUMENTS);
        $field = DocumentsModel::getField($extension_copy);
        
        if($field) {

            if(count($files)>0){
                foreach($files as $file){
                    if($file->copy_id == \ExtensionCopyModel::MODULE_DOCUMENTS) {
                        $upload = UploadsModel::model()->findByPK($file->id);

                        if($upload !== null){
                            //запись в таблице загрузок найдена, ищем id экземпляра модуля
                            $data = \DataModel::getInstance()
                                ->setFrom($extension_copy->getTableName())
                                ->setWhere($field . " = '" . $upload->relate_key . "'")
                                ->FindRow();
    
                            if(!empty($data)) {
                                //запись найдена
                                if($data['this_template']=='0') {
                                    //это конечный документ
                                    UploadsParentsModel::model()->updateAll(
                                        array('upload_id'=>$file->id), 'doc_id = ' . $data[$extension_copy->prefix_name . '_id']
                                    );
                                }
                                if($data['this_template']=='1') {
                                    //это шаблон
                                    UploadsParentsModel::model()->updateAll(
                                        array('parent_upload_id'=>$file->id), 'parent_doc_id = ' . $data[$extension_copy->prefix_name . '_id']
                                    );
                                }
                            } 
                        }
                    }
                }
            }
        }
    }
    
    
    /**
     * Запись содержимого документа в БД
     */
    public static function setDBData($text, $type, $upload_id){

        $data = \DataModel::getInstance()->setFrom('{{' . self::DATA_TABLE_NAME . '}}')->setWhere('upload_id = ' . $upload_id)->findRow();
        
        if($data) {
            //есть, перезаписываем
            \DataModel::getInstance()->Update('{{' . self::DATA_TABLE_NAME . '}}', array('data' => $text, 'timestamp_edit' => null, 'user_id_edit' => null),  'upload_id = ' . $upload_id);
        }else {
            //нет, добавляем
            \DataModel::getInstance()->Insert('{{' . self::DATA_TABLE_NAME . '}}', array('data' => $text, 'type' => $type, 'timestamp_edit' => null, 'user_id_edit' => null, 'upload_id' => $upload_id)); 
        }
        
    }

    
    /**
     * Получаем текст документа
     */
    public static function getDataByUploadID($upload_id){
    
        return \DataModel::getInstance()->setFrom('{{' . self::DATA_TABLE_NAME . '}}')->setWhere('upload_id = ' . $upload_id)->findRow();

    }
    
    
    /**
     * update
     */
    public static function saveDocumentData($upload_id, $data, $type){

        \DataModel::getInstance()->Update('{{' . self::DATA_TABLE_NAME . '}}', array('data' => $data, 'timestamp_edit' => time(), 'user_id_edit' => WebUser::getUserId()),  'upload_id = ' . $upload_id);

        //для html повторно генерируем pdf файл
        if($type == 'html') {
            $uploadModel = \UploadsModel::model()->findByPK($upload_id);
            $path = \ParamsModel::model()->titleName('upload_path_module')->find()->getValue() .  DIRECTORY_SEPARATOR . $uploadModel->file_path;
            $file = $path . DIRECTORY_SEPARATOR . $uploadModel->file_name;
            \SmartyGenerate::getInstance()->saveToPDF($file, $data);
            
            $time = date('Y-m-d H:i:s');
            \DataModel::getInstance()->Update('{{uploads}}', array('file_date_upload' => $time, 'date_create' => $time, 'user_create' => WebUser::getUserId()), 'id = ' . $uploadModel->id);
        }
        
        return array(
            'status' => true,
            'message' => \Yii::t('DocumentsModule.messages', 'The document has been saved'),
        );
        
    }
}
