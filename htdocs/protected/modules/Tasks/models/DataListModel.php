<?php

/**
 * @author Alex R.
 */

namespace Tasks\models;


class DataListModel extends \DataListModel{




    protected function prepareSelectTypeList(){
        $schema_field = $this->_global_params['schema_field'];

        if($schema_field['name'] != 'todo_list'){
            return parent::prepareSelectTypeList();
        }

        if($this->_global_params['card_id'] && $this->_global_params['pci'] == false && $this->_global_params['pdi'] == false){
            $this->correctTodoListFindIds($this->_global_params['card_id']);
        }

        $select_list = array();

        if($this->_global_params['pci'] == false || $this->_global_params['pdi'] == false){
            return $select_list;
        }

        $group_data = $this->getGroupData();

        $query = '
                SELECT *
                FROM {{tasks_todo_list}} t0
                LEFT JOIN {{process_view_todo_list}} t1 ON t0.todo_list_id = t1.todo_list_id
                LEFT JOIN {{process_view_sorting_list}} t2 ON t1.sorting_list_id = t2.sorting_list_id
                WHERE t2.copy_id = '.$this->_extension_copy->copy_id.' AND
                      t2.pci = '.$this->_global_params['pci'].' AND
                      t2.pdi = '.$this->_global_params['pdi'].' AND
                      t2.group_data = "'.$group_data.'"
                ORDER BY t0.todo_list_title
        ';

        $data_list = (new \DataModel())->setText($query)->findAll();

        if($data_list){
            foreach($data_list as $data){
                $select_list[$data[$schema_field['name'] . '_id']] = $data[$schema_field['name'] . '_title'];
            }
        }


        if(!isset($schema_field['add_zero_value']) || (boolean)$schema_field['add_zero_value'] == true){
            $select_list = array('' => '') + $select_list;
        }

        $this->_data = $select_list;

        return $this;
    }





    private function getGroupData(){
        return \ProcessViewModel::getInstance()
                            ->setFinishedObject($this->_global_params['finished_object'])
                            ->setThisTemplate($this->_global_params['this_template'])
                            ->getGroupData();
    }





    /**
     * correctTodoListFindIds - поиск родительского связанного модуля, к которому привязан текущий через СДМ-Название
     *                          и установка pci && pdi, исходя из $card_id
     * @return bool
     */
    private function correctTodoListFindIds($card_id){
        $related_modules = \SchemaOperation::getInstance()->getElementsRelateParams($this->_extension_copy->getSchema());

        if(!empty($related_modules)){
            foreach ($related_modules as $related_module) {
                $copy_id = $related_module['relate_module_copy_id'];
                if ($copy_id) {
                    $module = \ExtensionCopyModel::model()->findByPk($copy_id);
                    if ($module) {
                        $related_modules_primary = \SchemaOperation::getInstance()->getElementsRelateParams($module->getSchema(), true);

                        foreach ($related_modules_primary as $related_module_primary) {
                            if($related_module_primary['relate_module_copy_id'] == $this->_extension_copy->copy_id){

                                $relate_table = \ModuleTablesModel::model()->find(array(
                                    'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_one"',
                                    'params' => array(
                                        ':copy_id' => $copy_id,
                                        ':relate_copy_id' => $this->_extension_copy->copy_id)));

                                $sub_module_data = new \DataModel();
                                $sub_module_data
                                    ->setFrom('{{' . $relate_table->table_name . '}}')
                                    ->setWhere($relate_table->relate_field_name . ' = ' . $card_id);

                                $sub_module_data = $sub_module_data->find();

                                $sub_module_data_id = array_unique(array_keys(\CHtml::listData($sub_module_data, $relate_table->parent_field_name, '')));

                                if(!empty($sub_module_data_id)){
                                    $this->_global_params['pci'] = $copy_id;
                                    $this->_global_params['pdi'] = array_shift($sub_module_data_id);

                                    return true;
                                }
                            }
                        }
                    }
                }

            }
        }

        return false;
    }






}
