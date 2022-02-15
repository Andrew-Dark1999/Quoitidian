<?php

class ListViewController extends ListView{
    
    
    /**
    * Показываем содержимое Документа
    */
    public function actionShowDocument($copy_id){

        if(empty($copy_id) || empty($_GET['data_id'])) return $this->renderTextOnly(Yii::t('messages', 'Not defined parameters'));
        
        if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $copy_id, Access::ACCESS_TYPE_MODULE)){
            return $this->returnCheckMessage('w', Yii::t('messages', 'You do not have access to this object'));
        }

        if(!empty($_POST['upload_id']))
            \DocumentsModel::saveDocumentData($_POST['upload_id'], $_POST['text'], $_POST['type']);
        
        $data = \DocumentsModel::getDataByUploadID($_GET['data_id']);
        
        if(!$data){
            return $this->renderTextOnly(Yii::t('DocumentsModule.messages', 'Document data not found'));
        }
            
        $this->layout = '//layouts/default.min';
        $this->data['menu_main'] = array('index' => 'index');
        
        return $this->render('/site/show-document_' . $data['type'],
            array(
                'text' => $data['data'],
                'upload_id' => $data['upload_id'],
                'copy_id' => $copy_id,
            )
        );
        
    }     
    

}
