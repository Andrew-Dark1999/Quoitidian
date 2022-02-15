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

        $history_model = new History();
        $history_model
            ->setPciFromParams($_POST)
            ->setPdiFromParams($_POST);


        $result = array(
            'status' => true,
            'url' => $history_model->getUserStorageUrl($_POST),
            'params' => array(
                'pci' => $history_model->getPci(),
                'pdi' => $history_model->getPdi(),
            ),

        );

        if($action_key = \Yii::app()->request->getParam('action_key')){
            $result['action_key'] = $action_key;
        }


        $this->renderJson($result);
    }
        




    /**
     * возвращает pci & pdi для модуля
     */
    public function getPciPdi($copy_id, $id){
        $result = array(); 

        $module_table_model = ModuleTablesModel::model()->findAll(array(
                                        'condition' => 'relate_copy_id=:relate_copy_id AND `type`="relate_module_one"' ,
                                        'params' => array(':relate_copy_id' => $copy_id)));
        
        if(empty($module_table_model)) return $result;
        
        foreach($module_table_model as $table_model){
            $primary_field = ExtensionCopyModel::model()->findByPk($table_model->copy_id)->getPrimaryField();
            if(empty($primary_field)) continue;


            if(empty($primary_field) || $primary_field['params']['type'] != 'relate_string') continue;
            if($copy_id != $primary_field['params']['relate_module_copy_id']) continue;
            
            // все данные модуля
            $sub_module_data = new DataModel();
            $sub_module_data
                ->setFrom('{{' . $table_model->table_name . '}}')
                ->setWhere($table_model->relate_field_name . ' = ' . $id);
            $sub_module_data = $sub_module_data->findAll();

            if(!empty($sub_module_data)){
                $result['pci'] = $table_model->copy_id;
                $result['pdi'] = $sub_module_data[0][$table_model->parent_field_name];
            }
            break;    
        }
        
        return $result;
    }




    /**
     * Возвращает урл на основании даных хранилища через родительский модуль.
     * Другими словами добавляет  к урлу параметры PCI & PDI
     */
    public function actionGetUserStorageUrlViaParent(){
        $validate = new Validate();
        if(!isset($_POST['copy_id'])){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined parameters'));
            return $this->renderJson(array(
                                        'status' => false,
                                        'messages' => $validate->getValidateResultHtml(),
                                    ));                        
        }
        $params = (is_array($_POST) ? $_POST : array());
        $params = Helper::arrayMerge($params, array('params' => $this->getPciPdi($_POST['copy_id'], $_POST['data_id'])));

        $history_model = new History();
        if(!empty($params['params']['pci']) && !empty($params['params']['pci'])){
            $history_model
                ->setPci($params['params']['pci'])
                ->setPdi($params['params']['pdi']);
        }

        $url = $history_model->getUserStorageUrl($params);
        
        return $this->renderJson(array(
                            'status' => true,
                            'url' => $url,
                        ));                        
    }    



    public function actionGetUserStorageUrlFromIndex(){
        $validate = new Validate();
        if(!isset($_POST['index'])){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined parameters'));
            return $this->renderJson(array(
                                        'status' => false,
                                        'messages' => $validate->getValidateResultHtml(),
                                    ));                        
        }
        $params = \History::getInstance()->getUserStorageUrlParams($_POST['index']);
        $url = Yii::app()->createUrl($_POST['index']);
        if(!empty($params)) $url.='?' . $params;
        
        return $this->renderJson(array(
                            'status' => true,
                            'url' => $url,
                        ));                        
    }    

        
        
    public function actionSetUserStorage(){
        $validate = new Validate();
        if(!isset($_POST['type']) || !isset($_POST['index'])){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined parameters'));
            return $this->renderJson(array(
                                        'status' => false,
                                        'messages' => $validate->getValidateResultHtml(),
                                    ));                        
        }
        if(empty($_POST['value'])){
            History::getInstance()->deleteFromUserStorage(
                        UsersStorageModel::model()->getType(\Yii::app()->request->getParam('type')),
                        \Yii::app()->request->getParam('index'),
                        \Yii::app()->request->getParam('pci'),
                        \Yii::app()->request->getParam('pdi')
                    );
            return $this->renderJson(array('status' => true));
        }

        History::getInstance()->setUserStorage(
                    UsersStorageModel::model()->getType(\Yii::app()->request->getParam('type')),
                    \Yii::app()->request->getParam('index'),
                    \Yii::app()->request->getParam('value'),
                    false,
                    \Yii::app()->request->getParam('pci'),
                    \Yii::app()->request->getParam('pdi')
                );

        return $this->renderJson(array('status' => true));                        
    }
    

        
    public function actionGetUserStorage(){
        $validate = new Validate();
        if(!isset($_POST['type']) || !isset($_POST['index'])){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined parameters'));
            return $this->renderJson(array(
                                        'status' => false,
                                        'messages' => $validate->getValidateResultHtml(),
                                    ));                        
        }
        
        $result = History::getInstance()->getUserStorage(
                            UsersStorageModel::model()->getType(\Yii::app()->request->getParam('type')),
                            \Yii::app()->request->getParam('index'),
                            \Yii::app()->request->getParam('pci'),
                            \Yii::app()->request->getParam('pdi')
                        );

        return $this->renderJson(array('status' => true, 'value' => $result));                        
    }    
    
    
    public function actionDeleteUserStorage(){
        $validate = new Validate();
        if(!isset($_POST['type']) || !isset($_POST['index'])){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined parameters'));
            return $this->renderJson(array(
                                        'status' => false,
                                        'messages' => $validate->getValidateResultHtml(),
                                    ));                        
        }
        
        History::getInstance()->deleteFromUserStorage(
                    UsersStorageModel::model()->getType(\Yii::app()->request->getParam('type')),
                    \Yii::app()->request->getParam('index'),
                    \Yii::app()->request->getParam('pci'),
                    \Yii::app()->request->getParam('pdi')
                );

        return $this->renderJson(array('status' => true));                        
    }


    /**
     * Масив соответствий "контроллер" -> "екшин":
        [
        'listView'      =>  ['show'],
        'processView'   =>  ['show'],
        'calendarView'  =>  ['show'],
        'profile'       =>  ['profile'],
        'constructor'   =>  [null],
        'site'          =>  ['parameters','plugins','mailingServices'],
        ];
     * @return string
     */
    public function actionSetUserStorageBackUrl(){
        $validate = new Validate();
        if(
            Yii::app()->request->getParam('controller_id') == false ||
            Yii::app()->request->getParam('action_id') == false ||
            Yii::app()->request->getParam('url') == false
        ){
            $validate->addValidateResult('e', Yii::t('messages', 'Not defined parameters'));
            return $this->renderJson(array(
                'status' => false,
                'messages' => $validate->getValidateResultHtml(),
            ));
        }

        History::setUserStorageBackUrl(
            Yii::app()->request->getParam('controller_id'),
            Yii::app()->request->getParam('action_id'),
            Yii::app()->request->getParam('url')
        );

        return $this->renderJson(array('status' => true));
    }
}
