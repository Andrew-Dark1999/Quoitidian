<?php
/**
*  EditViewCopyModel
*  
*  @author Alex R.
*/


class EditViewCopyModel{
    
    private $_parent_this_template = null;
    private $_this_template = null;
    private $_this_template_only_first = false;
    
    private $_primary_pci = null;                           // copy_id первичного модуля
    private $_primary_sub_modules = array();                // массив сабмодулей первичного модуля
    private $_primary_sub_modules_data_list = array();      // массив новых данных (ИД) сабмодулей первичного модуля
    private $_parent_module_is_primary = false;             // яказывает, что родительский модуль является первичным
    private $_list_data_for_relate = array();               // список карточек модулей, между котоорыми надо создать связь. Связи создаются меджу новыми карточками, корорые вычисляются на основании данних в массиве.
    private $_make_loggin = true;
    private $_copy_from_process = false;

    private $_count_cards = 0;

    private $_id_list = [];
    private $_id_list_all = [];


    // Указывает, что при создании(копировании, изменении) ответсвенным должен стать Активный пользователь
    private $_set_responsible_is_active_user = false;


    public function __construct($extension_copy){
        $this->initPrimaryPci($extension_copy);
        $this->initPrimarySubModules($extension_copy);
    }
    

    public static function getInstance($extension_copy){
        return new self($extension_copy);
    }


    public function setParentThisTemplate($this_template){
        $this->_parent_this_template = $this_template;
        return $this;
    }


    public function setThisTemplate($this_template){
        $this->_this_template = $this_template;
        return $this;
    }

    public function setThisTemplateOnlyFirst($param = false){
        $this->_this_template_only_first = $param;
        return $this;
    }


    public function setMakeLoggin($make_loggin){
        $this->_make_loggin = $make_loggin;
        return $this;
    }

    public function setCopyFromProcess($copy_from_process){
        $this->_copy_from_process = $copy_from_process;
        return $this;
    }


    public function setResponsibleIsActiveUser($param){
        $this->_set_responsible_is_active_user = $param;
        return $this;
    }


    private function addIdListAll($copy_id, $data_id){
        if(array_key_exists($copy_id, $this->_id_list_all)){
            $this->_id_list_all[$copy_id][] = $data_id;
        } else {
            $this->_id_list_all[$copy_id] = array(
                $data_id,
            );
        }
        return $this;
    }



    public function getIdListAll(){
        return $this->_id_list_all;
    }




    /**
     * createProcessAfterCreatedEntity - создание нового Процесса исходя из действия - создание сущности
     * @param $process_action_name
     */
    public function createProcessAfterCreatedEntity(){

        if($this->_this_template && $this->_this_template != EditViewModel::THIS_TEMPLATE_MODULE){
            return $this;
        }

        if($this->_id_list_all == false){
            return $this;
        }

        foreach($this->_id_list_all as $copy_id => $data_id_list){
            $properties = array(
                'properties' => array(
                    'action_name' => ProcessActions::ACTION_CREATE_PROCESS_AFTER_CREATED_ENTITY,
                    'vars' => array(
                        'copy_id' => $copy_id,
                        'data_id_list' => $data_id_list,
                    ),
                ),
            );

            (new \ConsoleRunAsync())
                ->setCommandProperties($properties)
                ->setActionName('processActionsRun')
                ->exec();


            /*

            $vars = array(
                'copy_id' => $copy_id,
                'data_id_list' => $data_id_list,
            );

            (new ProcessActions())
                ->setVars($vars)
                ->setActionName(ProcessActions::ACTION_CREATE_PROCESS_AFTER_CREATED_ENTITY)
                ->run()
                ->getResult();
            */

        }

        return $this;
    }



    /**
     * поиска copy_id первичного модуля
     */
    private function getPrimaryPci($extension_copy){
        $relate_model = new EditViewRelateModel();
        $relate_model
            ->setVars(array('extension_copy' => $extension_copy))
            ->setAutoPci();
        if($pci = $relate_model->getPci()){
            if($extension_copy->copy_id == $pci) $this->_parent_module_is_primary = true;
            return $pci;
        } else {
            $this->_parent_module_is_primary = true;
            return $extension_copy->copy_id;
        }
    }



    /**
     * устновка copy_id первичного модуля
     */
    private function initPrimaryPci($extension_copy){
        $this->_primary_pci = $this->getPrimaryPci($extension_copy);

        return $this;
    }



    /**
     * возвращает все СМ первичного модуля
     */
    private function initPrimarySubModules($extension_copy){
        if(empty($this->_primary_pci)) return;
        if($extension_copy->copy_id != $this->_primary_pci)
            $extension_copy = ExtensionCopyModel::model()->findByPk($this->_primary_pci);
            
        $this->_primary_sub_modules = SchemaOperation::getSubModules($extension_copy->getSchemaParse()) ;
        
        return $this;
    }



    /**
     * поиск Сабмодуля в первичном модуле
     */
    private function isSetPrimarySubModules($copy_id){
        if(empty($this->_primary_sub_modules)) return false;
        foreach($this->_primary_sub_modules as $sub_module){
            if($sub_module['sub_module']['params']['relate_module_copy_id'] == $copy_id) return true;
        }
        return false;
    }    



    /**
     * возвращает ИД новой карточки, скопированой в первичном СМ
     */
    private function getSubModuleNewId($copy_id, $old_data_id){
        if(empty($this->_primary_sub_modules_data_list[$copy_id][$old_data_id])) return false;

        return $this->_primary_sub_modules_data_list[$copy_id][$old_data_id]; 
    }



    /**
     * дополнение списока карточек модулей, между котоорыми надо создать связь
     */
    private function setListDataForRelate($pci, $pdi_old, $pdi_new, $rci){
        // проверяем, есть ли обратная связь между модулями
        $relate_table = ModuleTablesModel::model()->find(array(
                                                        'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type` = "relate_module_one"',
                                                        'params' => array(
                                                            ':copy_id' => $rci,
                                                            ':relate_copy_id' => $pci,
                                                        )));

        if(!empty($relate_table)) return;
        
        if(isset($this->_list_data_for_relate[$pci])){
            $this->_list_data_for_relate[$pci]['rci'][] = $rci;
        } else {
            $this->_list_data_for_relate[$pci] = array('pdi_old' => $pdi_old, 'pdi_new' => $pdi_new, 'rci' => array($rci));
        }
        return $this;
    }






    public function getResult(){
        $result = array(
            'status' => true,
            'count' => $this->_count_cards,
            'id' => $this->_id_list,
        );

        return $result;
    }

    /**
    * Непосредственное копирование данных субмодуля в ListView
    */
    public function copy($data_id_list, $extension_copy, $add_copy_word = true, $parent_extension_copy = null){
        $data_id_list = (array)$data_id_list;

        foreach($data_id_list as $data_id){
            //копия основных данных
            $last_record = $this->copyAll($extension_copy, $data_id, null, $add_copy_word, $this->_this_template, false, $parent_extension_copy);

            // логирование            
            if(!empty($last_record)){
                $this->_count_cards++;
                if(!empty($last_record['id'])){
                    $this->_id_list[] = $last_record['id'];
                }
            }
        }
        
        $this->createRelateFromList();

        return $this;
    }





    /**
     * копируем все данные 
     */
    public function copyAll($extension_copy, $id, $parent_copy_id = null, $add_copy_word, $this_template, $cycle_steep = false, $parent_extension_copy = null){
        //копия основных данных
        $set_responsible_is_active_user = $this->_set_responsible_is_active_user;
        if($cycle_steep == true){
            $set_responsible_is_active_user = false;
        }

        $last_record = $this->copyData($extension_copy, $id, $add_copy_word, $this_template, $set_responsible_is_active_user);

        if($this->_this_template_only_first){
            $this->_this_template_only_first = null;
            $this_template = null; 
        }
            
        //копия связаных данных                 
        if(!empty($last_record['id'])){
            $this->copySubModule($extension_copy, $id, $last_record['id'], $cycle_steep, $parent_extension_copy);
            $this->copyRelate($extension_copy, $id, $parent_copy_id, $last_record['id'], $cycle_steep, array(), $parent_extension_copy);
            $this->copyRelateString($extension_copy, $id, $last_record['id']);

            $this->addIdListAll($extension_copy->copy_id, $last_record['id']);
        }
        
        return $last_record;
    }




    



    /**
     * копируем связаные данные элемента СДМ 
     */
    private function copyRelate($extension_copy, $id, $parent_relate_module_copy_id, $new_id, $cycle_steep = false, $only_relate_module_copy_id = array(), $parent_extension_copy = null){
        $relates = SchemaOperation::getRelates($extension_copy->getSchemaParse()) ; 
        if(!empty($relates)){
            foreach($relates as $relate){
                if(!empty($only_relate_module_copy_id) && in_array($relate['params']['relate_module_copy_id'], $only_relate_module_copy_id) == false) continue;
                if($parent_relate_module_copy_id !== null && $parent_relate_module_copy_id == $relate['params']['relate_module_copy_id']) continue; //если модули связаны обратной связью - чтобы небыло удвоения связи    
                if(!empty($parent_extension_copy) && $parent_extension_copy->copy_id == $relate['params']['relate_module_copy_id']) continue;
                
                $relate_table = ModuleTablesModel::model()->find(array(
                                                                'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type` = "relate_module_one"',
                                                                'params' => array(
                                                                    ':copy_id' => $extension_copy->copy_id,
                                                                    ':relate_copy_id' => $relate['params']['relate_module_copy_id'],
                                                                )));
                
                $relate_one = ModuleTablesModel::model()->findAll(array(
                                                                'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type` = "relate_module_one"',
                                                                'params' => array(
                                                                    ':copy_id' => $relate['params']['relate_module_copy_id'],
                                                                    ':relate_copy_id' => $extension_copy->copy_id,
                                                                )));
                if(!empty($relate_one)) continue; // пропускаем, если есть связь ОКО
                
                
                
                // устанавливаем новые связи
                $data_model = new DataModel();
                $relate_data = $data_model
                                    ->setFrom('{{' . $relate_table->table_name . '}}')
                                    ->setWhere($relate_table->parent_field_name . '=:id', array(':id'=> $id))
                                    ->findAll();

                if(!empty($relate_data)){
                    foreach($relate_data as $data_value){
                        $data_value['id'] = null;
                        $data_value[$relate_table->parent_field_name] = $new_id;

                        if($cycle_steep && $this->_parent_module_is_primary == true){ // если автоматически копирутся данные в связаном модуле 2-го и более уровня. Родительская скопированая карточка из первичного молудя    
                            $relate_primary_pci = $this->getPrimaryPci($extension_copy);
                            if($relate_primary_pci && $relate_primary_pci == $this->_primary_pci && $this->isSetPrimarySubModules($relate['params']['relate_module_copy_id'])){
                                if($sm_new_id = $this->getSubModuleNewId($relate['params']['relate_module_copy_id'], $data_value[$relate_table->relate_field_name])){
                                    $data_value[$relate_table->relate_field_name] = $sm_new_id;
                                } else {
                                    $this->setListDataForRelate($extension_copy->copy_id, $id, $new_id, $relate['params']['relate_module_copy_id']);
                                    // если в модуле нет обратной связи - 
                                    continue;
                                }
                            } 
                        } else { 
                            // проверяем, есть ли обратная связь между модулями
                            $relate_one = ModuleTablesModel::model()->find(array(
                                                                            'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type` = "relate_module_one"',
                                                                            'params' => array(
                                                                                ':copy_id' => $relate['params']['relate_module_copy_id'],
                                                                                ':relate_copy_id' => $extension_copy->copy_id,
                                                                            )));
                    
                            if(!empty($relate_one)) continue;
                        }

                        $this->insertRelateLink($relate_table->table_name, $data_value);
                    }
                }
            }
        }
    }
    
    
    
    /**
     * установка связей из _list_data_for_relate
     */
    private function createRelateFromList(){
        if(empty($this->_list_data_for_relate)) return;
        //$this->_parent_module_is_primary = true; // чтобы пропустил контроль
        foreach($this->_list_data_for_relate as $parent_copy_id => $relate_value){
            $parent_copy_id =  ExtensionCopyModel::model()->findByPk($parent_copy_id);
            $this->copyRelate($parent_copy_id, $relate_value['pdi_old'], null, $relate_value['pdi_new'], true, $relate_value['rci']);
        } 
    }
    
    

    /**
     * копируем данные Сабмодуля
     */
    private function copySubModule($extension_copy, $id, $new_id, $cycle_steep = false, $parent_extension_copy = null){
        $sub_modules = SchemaOperation::getSubModules($extension_copy->getSchemaParse()) ; 

        if(!empty($sub_modules)){
            foreach($sub_modules as $module){
                $module = $module['sub_module'];
                
                if(!empty($parent_extension_copy) && $parent_extension_copy->copy_id == $module['params']['relate_module_copy_id']) continue;
                
                $relate_extension_copy = ExtensionCopyModel::model()->findByPk($module['params']['relate_module_copy_id']);
                if($cycle_steep){
                    $relate_primary_pci = $this->getPrimaryPci($relate_extension_copy);
                    if( $relate_primary_pci &&
                        $relate_primary_pci == $this->_primary_pci &&
                        $this->isSetPrimarySubModules($module['params']['relate_module_copy_id']) &&
                        isset($this->_primary_sub_modules_data_list[$module['params']['relate_module_copy_id']]) == false
                        ) continue; // пропускаем, если в первичном модуле есть сабмодуль, но данные в нем еще не обновлены - новые связи создадутся далее в СДМ... 
                }
                // поиск прямой связи
                $relate_table = ModuleTablesModel::model()->find(array(
                                                'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_many"' ,
                                                'params' => array(
                                                                ':copy_id' => $extension_copy->copy_id,
                                                                ':relate_copy_id' => $module['params']['relate_module_copy_id'])));
                
                if(empty($relate_table)) continue;
                
                // устанавливаем новые связи
                $data_model = new DataModel();
                $data_model = $data_model
                                    ->setFrom('{{' . $relate_table->table_name . '}}')
                                    ->setWhere($relate_table->parent_field_name . '=:id', array(':id'=> $id))
                                    ->findAll();
                        
                $this_template = $this->_this_template;            
                if(!empty($data_model)){
                    foreach($data_model as $data_value){
                        $relate_id = $data_value[$relate_table->relate_field_name]; // ИД на старую карточку
                        if($cycle_steep && $relate_primary_pci &&
                            $relate_primary_pci == $this->_primary_pci &&
                            $this->isSetPrimarySubModules($module['params']['relate_module_copy_id']) &&
                            $sm_new_id = $this->getSubModuleNewId($module['params']['relate_module_copy_id'], $data_value[$relate_table->relate_field_name]))
                        {
                                $relate_id = $sm_new_id; // ИД на карточку, скопированую в родительском СМ
                        } else {
                                // копия данных, так как связь МКО или модуль привязан как СМ-шаблон
                                $relate_one = ModuleTablesModel::model()->findAll(array( // поиск обратной связи
                                                                                'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type` = "relate_module_one"',
                                                                                'params' => array(
                                                                                    ':copy_id' => $module['params']['relate_module_copy_id'],
                                                                                    ':relate_copy_id' => $extension_copy->copy_id,
                                                                                )));
                                if(!empty($relate_one) || ($module['params']['relate_module_template'] == true && (boolean)$this->_parent_this_template)){
                                    $relate_new_id = $this->copyAll($relate_extension_copy, $data_value[$relate_table->relate_field_name], $extension_copy->copy_id,  false, $this_template, true, $extension_copy);
                                    if(!empty($relate_new_id['id'])){
                                        $this->_primary_sub_modules_data_list[$module['params']['relate_module_copy_id']][$relate_id] = $relate_new_id['id']; // массив новых данных (ИД) сабмодулей первичного модуля   
                                        $relate_id = $relate_new_id['id'];
                                    }
                                } 
                        }

                        $data_value['id'] = null;
                        $data_value[$relate_table->parent_field_name] = $new_id;
                        $data_value[$relate_table->relate_field_name] = $relate_id;
                        $this->insertRelateLink($relate_table->table_name, $data_value);
                    }
                }
            }
        }
    }
    





    /**
     * копируем данные модуля, связаные по полю "Название"
     */
    public function copyRelateString($extension_copy, $id, $new_id){
        //берем значение первичного поля и проверяем тип relate_string
        $relate_params = $extension_copy->getPrimaryField();
        if(empty($relate_params) || $relate_params['params']['type'] != 'relate_string') return;
        
        if(Access::checkAdvancedAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, (integer)$relate_params['params']['relate_module_copy_id']) == false) return;
        
        $relate_table = ModuleTablesModel::model()->find(array(
                                        'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_one"' ,
                                        'params' => array(
                                                        ':copy_id' => $extension_copy->copy_id,
                                                        ':relate_copy_id' => $relate_params['params']['relate_module_copy_id'])));
        if(!empty($relate_table)){
            // все данные модуля
            $data_model = new DataModel();
            $data_model
                ->setFrom('{{' . $relate_table->table_name . '}}')
                ->setWhere($relate_table->parent_field_name . '=:id', array(':id'=> $id));
            $data_model = $data_model->findAll();

            $relate_extension_copy = ExtensionCopyModel::model()->findByPk($relate_params['params']['relate_module_copy_id']);
            $this_template = $this->_this_template;            
            if(!empty($data_model)){
                foreach($data_model as $data_value){
                    $relate_new_id = $this->copyAll(
                                                $relate_extension_copy,
                                                $data_value[$relate_table->relate_field_name],
                                                $extension_copy->copy_id,
                                                false,
                                                $this_template,
                                                true,
                                                $extension_copy);
                    if(!empty($relate_new_id['id'])){
                        $relate_id = $relate_new_id['id'];
                    } else {
                        continue;
                    }

                    $data_value[$relate_table->parent_field_name] = $new_id;
                    $data_value[$relate_table->relate_field_name] = $relate_id;
                    $data_value['id'] = null;
                    $this->insertRelateLink($relate_table->table_name, $data_value);
                }
            }
        }
    }




    /**
     * вставляет новую связь в связующую таблицу 
     */
    private function insertRelateLink($table_name, $data_value){
        $data_insert_model = new DataModel();                        
        $data_insert_model->insert(
                            '{{' . $table_name . '}}',
                            $data_value
        );    
    }





    /**
     * Копия самих данных 
     */
    public function copyData($extension_copy, $id, $add_copy_word = true, $this_template, $set_responsible_is_active_user){
        $last_record = array();
        if(empty($extension_copy)) return $last_record;

        if(
            !Access::checkAdvancedAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $extension_copy->copy_id) &&
            !Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)
        ){
            return $last_record;
        }

        // EditViewModel
        $alias = 'evm_' . $extension_copy->copy_id;
        $dinamic_params = array(
            'tableName'=> $extension_copy->getTableName(null, false)
        );

        $edit_view_model = EditViewModel::modelR($alias, $dinamic_params)->findByPk($id);
        if(empty($edit_view_model)){
            //$edit_view_model->destroyInstance();
            return $last_record;
        }

        if($extension_copy->copy_id != ExtensionCopyModel::MODULE_USERS || $extension_copy->copy_id != ExtensionCopyModel::MODULE_STAFF) {
            $primary = $extension_copy->getPrimaryField();
            if(!empty($primary) && isset($primary['params']['name'])){
                $module_title = $edit_view_model->{$primary['params']['name']} . ($add_copy_word ? ' [' . Yii::t('messages', 'Copy') . ']' : '');
                $edit_view_model->{$primary['params']['name']} = $module_title;
            }
        }

        $edit_view_model->isNewRecord = true;
        $edit_view_model->scenario = 'copy';
        $edit_view_model->extension_copy = $extension_copy;
        $edit_view_model->setElementSchema($extension_copy->getSchemaParse());
        $edit_view_model->copy_files = true;
        $edit_view_model->copy_participant = true;
        $edit_view_model->setResponsibleIsActiveUser($set_responsible_is_active_user);
        $edit_view_model->copy_participant_model = ParticipantModel::model()->findAll(array(
                                                            'condition' => 'copy_id = :copy_id AND data_id = :data_id',
                                                            'params' => array(
                                                                        ':copy_id' => $extension_copy->copy_id,
                                                                        ':data_id' => $id,
                                                            )));

        if($extension_copy->copy_id == \ExtensionCopyModel::MODULE_TASKS && $this->_copy_from_process == false){
            $edit_view_model->is_bpm_operation = null;
        }
        if(in_array($extension_copy->copy_id, [\ExtensionCopyModel::MODULE_USERS, \ExtensionCopyModel::MODULE_STAFF])){
            $edit_view_model->allowed_to_removed = '1';
        }


        $edit_view_model->copy_activity = true;
        $edit_view_model->copy_activity_model = ActivityMessagesModel::model()->findAll(array(
                                                            'condition' => 'copy_id = :copy_id AND data_id = :data_id AND `status` = "asserted"',
                                                            'params' => array(
                                                                        ':copy_id' => $extension_copy->copy_id,
                                                                        ':data_id' => $id,
                                                            )));

        $edit_view_model->{$extension_copy->prefix_name . '_id'} = null;
        $edit_view_model->date_edit = null;
        $edit_view_model->setMakeLogging($this->_make_loggin);

        if($this_template !== null) $edit_view_model->this_template = $this_template;
        if($edit_view_model->save()){
            $last_record = array('id' => $edit_view_model->primaryKey, 'title' => $edit_view_model->getModuleTitle());
        }

        //$edit_view_model->destroyInstance();
            
        return $last_record;
    }








   
    
    
    
    
    
} 
