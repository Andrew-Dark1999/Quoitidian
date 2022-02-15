<?php
/**
* FileBlock widget  
* @author Alex R.
* @version 1.0
*/ 

class FileBlock extends CWidget{
    public $view = 'element';
    public $schema = array();
    public $upload_model;
    public $extension_copy = null;
    public $extension_data = null;
    public $upload_link_show = true;
    public $block_class = 'column';
    public $buttons = array('download_file', 'delete_file');
    public $remove_element_name = 'edit_view'; 
    public $upload_element_name = 'edit_view';
    public $thumb_size = 60;
    public $show_generate_url = false;

    // дополнительные параметры файла 
    public $set_params_from_schema = true;
    public $params = array(
        'file_type' => null,
        'field_name' => null,
    );


    /**
     * установка параметров файла исходя из переданой схемы  
     */
    private function setParamsFromSchema(){
        if($this->set_params_from_schema && $this->view != 'element_contact') {
            $this->params = array(
                'file_type' => $this->schema['params']['type'],
                'field_name' => $this->schema['params']['name'],
            );
        }
    }


    public function getFileParams(){
        $file_params = array(
            'status' => false,
            'id' => '',
            'file_full_name' => '',
            'file_name' => '',
            'file_type' => '',
            'file_url' => '',
            'file_thumb_url' => '',
            'file_type_class' => '',
            'file_date_upload' => '',
            'file_size' => '',
            'file_source' => UploadsModel::SOURCE_MODULE
        );

        $upload_model = $this->upload_model;

        if($upload_model){
            $file_params['status'] = true;
            $file_params['id'] = $upload_model->id;
            $file_params['file_url'] = $upload_model->getFileUrl();
            $file_params['file_thumb_url'] = $upload_model->setFileType($this->params['file_type'])->getFileThumbsUrl($this->thumb_size);
            $file_params['file_name'] = $upload_model->getFileName();
            $file_params['file_title'] = $upload_model->getFileTitle();
            $file_params['file_type'] = $upload_model->getFileType();
            $file_params['file_type_class'] = $upload_model->getFileTypeClass();
            $file_params['file_size'] = $upload_model->getFileSize();
            $file_params['file_date_upload'] = $upload_model->file_date_upload;
            $file_params['file_source'] = $upload_model->file_source;

        } else {
            if($this->show_generate_url){
                //модель пуста, но ссылку для генерации мы показываем, значит конечный документ был удален, проверяем таблицу соответствий загрузок
                if($this->extension_copy->copy_id == \ExtensionCopyModel::MODULE_DOCUMENTS) {
                    \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_DOCUMENTS)->getModule(false);
                    $upload_model = DocumentsModel::copyDocumentFromExtensionCopy($this->extension_copy, $this->extension_data);
                    if($upload_model) {
                        $file_params['status'] = true;
                        $file_params['id'] = $upload_model->id;
                        $file_params['file_url'] = $upload_model->getFileUrl();
                        $file_params['file_thumb_url'] = $upload_model->setFileType($this->params['file_type'])->getFileThumbsUrl($this->thumb_size);
                        $file_params['file_name'] = $upload_model->getFileName();
                        $file_params['file_title'] = $upload_model->getFileTitle();
                        $file_params['file_type'] = $upload_model->getFileType();
                        $file_params['file_type_class'] = $upload_model->getFileTypeClass();
                        $file_params['file_size'] = $upload_model->getFileSize();
                        $file_params['file_date_upload'] = $upload_model->file_date_upload;
                        $file_params['file_source'] = $upload_model->file_source;
                    }
                }
            }
        }

        return $file_params;
    }




    public function getFlexClass(){
        $file_params = $this->getFileParams();
        $flex_class = ($file_params['file_type_class'] == 'file_image' && $this->thumb_size == false  ? 'column' : 'row');

        return $flex_class;
    }




    public function getBlockClass(){
        return $this->block_class;
    }



    public function init(){
        $this->setParamsFromSchema();
        $access_check_params = array();
        
        if(!empty($this->extension_copy)){
            if(Access::moduleAdministrativeAccess($this->extension_copy->copy_id)){
                $access_check_params = array(
                    'access_id' => RegulationModel::REGULATION_SYSTEM_SETTINGS,
                    'access_id_type' => Access::ACCESS_TYPE_REGULATION,
                );
            } else {
                $access_check_params = array(
                    'access_id' => $this->extension_copy->copy_id,
                    'access_id_type' => Access::ACCESS_TYPE_MODULE,
                );
            }

            if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $access_check_params['access_id'], $access_check_params['access_id_type'])) {
                $this->buttons = array();
            }
        } 
        
        $this->render($this->view, array(
                                    'file_params' => $this->getFileParams(),
                                    'params' => $this->params,
                                    'upload_model' => $this->upload_model,
                                    'extension_copy' => $this->extension_copy,
                                    'extension_data' => $this->extension_data,
                                    'upload_link_show' => $this->upload_link_show,
                                    'buttons' => $this->buttons,
                                    'thumb_size' => $this->thumb_size,
                                    'remove_element_name' => $this->remove_element_name,
                                    'upload_element_name' => $this->upload_element_name,
                                    'access_check_params' => $access_check_params,
                                    'show_generate_url' => $this->show_generate_url,
                                    'hide_elements_block_file' => ((isset($_POST['parent_copy_id']) && isset($_POST['from_template']) && $this->show_generate_url)) ? true : false,
                                 )
        );
    }
 

}
