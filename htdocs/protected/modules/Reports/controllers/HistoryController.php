<?php



class HistoryController extends Controller{
    

    /**
     * Возвращает урл на основании даных хранилища
     */
    public function actionGetUserStorageUrl(){
        $validate = new Validate();
        if(!isset($_POST['copy_id'])){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined parameters'));
            return $this->renderJson(array(
                                        'status' => false,
                                        'messages' => $validate->getValidateResultHtml(),
                                    ));                        
        }

        $url = \Reports\extensions\History::getInstance()->getUserStorageUrl($_POST);

        $result = array(
            'status' => true,
            'url' => $url,
        );

        if($action_key = \Yii::app()->request->getParam('action_key')){
            $result['action_key'] = $action_key;
        }


        $this->renderJson($result);
    }
        

}
