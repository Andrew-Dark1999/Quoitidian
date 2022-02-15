<?php

class EditViewController extends EditView{






    /**
    * ��������/����������/�������������� ������ � EditView
    */
    public function actionEdit(){

        ViewList::setViews(array('ext.ElementMaster.EditView.Elements.Edit.Edit' => 'Participant.extensions.ElementMaster.EditView.Elements.Edit.Edit'));
        ViewList::setViews(array('ext.ElementMaster.EditView.Elements.Panel.Panel' => 'Participant.extensions.ElementMaster.EditView.Elements.Panel.Panel'));

        $extension_copy = $this->module->extensionCopy;
        $schema_parser = $extension_copy->getSchemaParse();

        $alias = 'evm_' . $extension_copy->copy_id;
        $dinamic_params = array(
            'tableName' => $extension_copy->getTableName(null, false),
            'params' => Fields::getInstance()->getActiveRecordsParams($schema_parser),
        );

        if(!empty($_POST['id'])){

            $extension_data = \EditViewModel::modelR($alias, $dinamic_params)->findByPk($_POST['id']);
            $extension_data->scenario = 'update';
        } else
            $extension_data = \EditViewModel::modelR($alias, $dinamic_params, true);
            
       $extension_data->setElementSchema($schema_parser);
       $extension_data->extension_copy = $extension_copy;

        // ���� ���������
        if(!empty($_POST['EditViewModel'])){
            unset($_POST['EditViewModel']['this_template']);
            $extension_data->setMyAttributes($_POST['EditViewModel']);
            $extension_data->copy_id = $_POST['pci'];
            $extension_data->data_id = $_POST['pdi'];
            $extension_data->ug_id = $_POST['element_responsible'][0]['ug_id'];
            $extension_data->ug_type = $_POST['element_responsible'][0]['ug_type'];

            $_POST['element_responsible'] = array();
            $_POST['element_participant'] = array();
            $_POST['element_participant_id'] = array();
    
            if($extension_data->save()){
                if($extension_data->responsible == '1'){
                    ParticipantModel::model()->updateAll(array(
                                                                'responsible' => '0',
                                                            ),
                                                            'copy_id = :copy_id AND data_id = :data_id AND participant_id != :participant_id',
                                                            array(
                                                            ':copy_id' => $extension_data->copy_id,
                                                            ':data_id' => $extension_data->data_id,
                                                            ':participant_id' => $extension_data->participant_id,
                                                            )
                                                            );    
                }
                
                return $this->renderJson(array(
                                            'status' => 'save',
                                            'id' => $extension_data->primaryKey,
                ));  
            }
        }
        
        $schema = $extension_copy->getSchema();
      
        $parent_copy_id = array('pci' => '', 'parent_copy_id' => '');
        $parent_data_id = array('pdi' => '', 'parent_data_id' => '');
        foreach($_POST as $key => $value){
            if($key == 'pci' || $key == 'parent_copy_id') $parent_copy_id[$key] = $value;
            if($key == 'pdi' || $key == 'parent_data_id') $parent_data_id[$key] = $value;
        }
      
      
        if(empty($schema)) return $this->renderTextOnly(Yii::t('messages', 'Not defined schema module'));
        return $this->renderJson(array(
            'status' => 'data',
            'data' => $this->renderPartial(ViewList::getView('site/editView'), array(
                                                'extension_copy' => $extension_copy,
                                                'extension_data' => $extension_data,
                                                'parent_copy_id' => $parent_copy_id, 
                                                'parent_data_id' => $parent_data_id,
                                                'pci' => Yii::app()->request->getParam('pci'),
                                                'pdi' => Yii::app()->request->getParam('pdi'),
                                                'id'=> (!empty($_POST['id']) ? $_POST['id'] : null),
                                                'this_template' => EditViewModel::THIS_TEMPLATE_MODULE,
                                                'template_data_id' => '',
                                                'content' => (new \EditViewBuilder())
                                                                        ->setDataId((!empty($_POST['id']) ? $_POST['id'] : null))
                                                                        ->setRelate((!empty($_POST['element_relate']) ? $_POST['element_relate'] : null))
                                                                        ->setParentCopyId($parent_copy_id)
                                                                        ->setParentDataId($parent_data_id)
                                                                        ->setDefaultData((!empty($_POST['default_data']) ? $_POST['default_data'] :  null))
                                                                        ->setExtensionCopy($extension_copy)
                                                                        ->setExtensionData($extension_data)
                                                                        ->buildEditViewPage($schema), 
                                           )
            , true)
        ));
    }








    /**
    * ���������� ����������� InLine ��������������
    */     
    public function actionInLineSave($copy_id){
        ViewList::setViews(array('ext.ElementMaster.ListView.Elements.TData.TData' => 'Participant.extensions.ElementMaster.ListView.Elements.TData.TData'));
        
        $validate = new Validate();

        if(empty($_POST['EditViewModel'])) $validate->addValidateResult('e', Yii::t('messages', 'Lack of data for conservation'));
        if($validate->error_count > 0){
            return $this->renderJson(array(
                'status' => 'error',
                'messages' => $validate->getValidateResultHtml(),
            ));
        }
        
        // ��� ����
        $attrubutes = $_POST;
        $parent_extension_copy = ExtensionCopyModel::model()->findByPk($attrubutes['parent_copy_id']);

        $alias = 'evm_' . $parent_extension_copy->copy_id;
        $dinamic_params = array(
            'tableName'=> $parent_extension_copy->getTableName(null, false)
        );

        $parent_model = EditViewModel::modelR($alias, $dinamic_params, true);
        $parent_data_model = $parent_model->findByPk($attrubutes['parent_data_id']);
        $parent_module_title =  $parent_data_model->getModuleTitle($parent_extension_copy);
        unset($parent_model);
        

        

        $extension_copy = $this->module->extensionCopy;
        $schema_parser = $extension_copy->getSchemaParse($extension_copy->getSchema());

        $alias = 'evm_' . $extension_copy->copy_id;
        $dinamic_params = array(
            'tableName' => $extension_copy->getTableName(null, false),
            'params' => Fields::getInstance()->getActiveRecordsParams($schema_parser),
        );

        if(!empty($_POST['id'])){
            $extension_data = EditViewModel::modelR($alias, $dinamic_params);
            $extension_data = $extension_data->findByPk($_POST['id']);
        } else {
            $extension_data = EditViewModel::modelR($alias, $dinamic_params, true);
        }
        
        
        $extension_data->setElementSchema($schema_parser);
        $extension_data->setMyAttributes($_POST['EditViewModel']);
        $extension_data->setExtensionCopy($extension_copy);
        $extension_data->setElementSchema($extension_copy->getSchemaParse());
            
        if($extension_data->save()){
            $params = array();
            if(isset($schema_parser['elements']))
            foreach($schema_parser['elements'] as $key => $value){
                if(!isset($value['field'])) continue;
                if($value['field']['params']['type'] == 'display_none') continue;
                $denied_relate = SchemaOperation::getDeniedRelateCopyId(array($value['field']['params']));    
                if($denied_relate['be_fields'] == false) continue;
                
                
                $value['field']['params']['title'] = $value['field']['title']; 
                $params[] = $value['field']['params'];
            }
            
            $id = (!empty($_POST['id']) ? $_POST['id'] : $extension_data->{$extension_copy->prefix_name . '_id'});  

            $module_data = new DataModel();
            $module_data
                ->setExtensionCopy($extension_copy)
                ->setFromModuleTables();

            
            $module_data
                ->setWhere($extension_copy->getTableName() . '.' . $extension_copy->prefix_name . '_id = :id', array(':id'=> $id))
                ->setCollectingSelect();

            $inline_elemets = ListViewBulder::getInstance($extension_copy)->buildHtmlListView($params, $module_data->findRow());
            if($extension_data->responsible == '1'){
                /**
                History::getInstance()->addToHistory(HistoryMessagesModel::MT_RESPONSIBLE_APPOINTED,
                                                 $attrubutes['parent_copy_id'],
                                                 $attrubutes['parent_data_id'], 
                                                 array('{module_data_title}' => $parent_module_title, '{user_id}' => $extension_data->ug_id)
                                                 );
                */
                
                ParticipantModel::model()->updateAll(array(
                                                    'responsible' => '0',
                                                ),
                                                'copy_id = :copy_id AND data_id = :data_id AND participant_id != :participant_id',
                                                array(
                                                ':copy_id' => $extension_data->copy_id,
                                                ':data_id' => $extension_data->data_id,
                                                ':participant_id' => $extension_data->participant_id,
                                                )
                                                );    
            }


            return $this->renderJson(array(
                                        'status' => 'save',
                                        'element_data' => $inline_elemets,
                                        'id' => $id,
            ));  
        } else {
            return $this->renderJson(array(
                                        'status' => 'error_save',
                                        'messages' => $extension_data->getErrorsHtml()
            ));  
        }

        
        if($validate->error_count > 0){
            return $this->renderJson(array(
                'status' => 'error',
                'messages' => $validate->getValidateResultHtml(),
            ));
        }
               
    }





    /**
    * ������ ����������� InLine ��������������
    */     
    public function actionInLineCancel(){
        ViewList::setViews(array('ext.ElementMaster.ListView.Elements.TData.TData' => 'Participant.extensions.ElementMaster.ListView.Elements.TData.TData'));
        
        parent::actionInLineCancel();
    }







}
