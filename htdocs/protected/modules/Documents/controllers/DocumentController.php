<?php


class DocumentController extends \EditView{

    
    /**
     * Сохранение содержимого документа
     */
    public function actionSaveDocumentData($copy_id){
        
        $result = \DocumentsModel::saveDocumentData($_POST['upload_id'], $_POST['data'], $_POST['type']);
        
        return $this->renderJson(array(
            'status' => $result['status'],
            'message' => $result['message'],
        ));
    }

 



}
