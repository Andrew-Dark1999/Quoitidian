<?php


/**
 * ValidateSubModule
 * 
 * @package crm
 * @author alex1
 * @copyright 2015
 * @version $Id$
 * @access public
 */
class ValidateSubModule  extends  Validate{
    
    
    

    public static function getInstance(){
        return new self();
    } 

    
    
    /**
     * проверка статуса значений на форме editView перед выполением определенного действия в сабмодуле: создать, прикрепить, удалить
     */
    public function check(array $vars, $code_action){
        $this->setConfirmButtonsDefault(false);
        $this->setButtonsConfirm(array(array('name'=>'Save', 'class' => 'btn btn-default confirm-yes-button'), array('name'=>'Cancel', 'class' => 'btn btn-default close-button')));
        $this->setParams($vars);
        
        // проверка 3 - сохранены ли изменения

        if(empty($vars['parent_data_id'])){
            $this->addValidateResultConfirm('c', Yii::t('messages', 'Necessary to save change'), $code_action);
            return $this;
        }         

        if(!empty($vars['parent_relate_data_list']) && (!isset($vars['this_template']) || $vars['this_template'] == EditViewModel::THIS_TEMPLATE_MODULE)){
            foreach($vars['parent_relate_data_list'] as $copy_id => $data_id){
            
                // проверка 2
                if(!empty($vars['primary_entities']) && $vars['primary_entities']['primary_pci'] && $vars['primary_entities']['primary_pci'] == $copy_id){
                    if(empty($vars['primary_entities']['primary_pdi'])){
                        $this->setButtons();
                        $parent_extension_copy = ExtensionCopyModel::model()->findByPk($vars['parent_copy_id']);
                        $parent_schema = $parent_extension_copy->getFieldSchemaParamsByType('relate');
                        $this->addValidateResult('w', Yii::t('messages', 'Field "{s}" is not filled', array('{s}'=> $parent_schema['title'] )));
                        return $this;
                    }
                }
                
                if($data_id == false) continue; // пропускаем "пустые" элементы СДМ
                // проверка 3
                $relate_table = ModuleTablesModel::model()->find(array(
                                                        'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_one"' ,
                                                        'params' => array(
                                                                        ':copy_id' => $vars['parent_copy_id'],
                                                                        ':relate_copy_id' => $copy_id,
                                                        )));

                if($relate_table == false){
                    continue;
                }
            
                $data_model = new DataModel();
                $data_model
                    ->setSelect($relate_table->relate_field_name)
                    ->setFrom('{{'.$relate_table->table_name.'}}')
                    ->setWhere($relate_table->parent_field_name . '=:id', array(':id'=> $vars['parent_data_id']));
                
                $data = $data_model->findAll();
                if(empty($data)){
                    $this->addValidateResultConfirm('c', Yii::t('messages', 'Necessary to save change'), $code_action);
                    return $this;
                }
                $data = array_unique(array_keys(CHtml::listData($data, $relate_table->relate_field_name, '')));
                if(!in_array($data_id, $data)){
                    $this->addValidateResultConfirm('c', Yii::t('messages', 'Necessary to save change'), $code_action);
                    return $this;
                }
            }
        }        

        return $this;
    }
    
    
    
    
    
    
}
