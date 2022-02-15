<?php
/**
*  EditViewBulkEditModel
*  модель для множественного редактирования данных
*  @author Alex B.
*/


class EditViewBulkEditModel{
   
    private $extension_copy = null;
    private $schema_parser = null;
    
    //для формул
    private $expressions = array();
    
    //изменяемые поля
    private $data = array();
    
    //изменяемые карточки
    private $ids = array();
    

    public static function getInstance(){
        return new self();
    }
        
    public function setExtensionCopy($extension_copy){
        $this->extension_copy = $extension_copy;
        $this->schema_parser = $this->extension_copy->getSchemaParse();
        return $this;
    }
    
    public function prepareData($data){
        //удаляем пустые значения полей
        $this->data = array_diff($data, array(''));
        return $this;
    }
    
    public function prepareFormulaData(){
        //подготавливаем значения с учетом расчета по формулам. это актуально для числовых полей
        foreach($this->schema_parser['elements'] as $element) {
            if(isset($element['field']['params']['type']) && $element['field']['params']['type']=='numeric') {
                if(!empty($this->data[$element['field']['params']['name']]) && $this->data[$element['field']['params']['name']][0]=='=') {
 
                    //подготавливаем математическое выражение для расчета
                    $expression = \Math::getInstance()
                        ->setOperatorAfterEqual(true)
                        ->setRules($this->data[$element['field']['params']['name']])
                        ->preparedExpression()
                        ->getExpression();
                        
                    if($expression !== false) {
                        $this->expressions[$element['field']['params']['name']] = $expression;
                    }
                }
            }    
        }

        return $this;
    }
    
    
    public function setIds($ids, $edit_all_cards=false){
        
        
        if($edit_all_cards) {
            //все записи
            $global_params = array(
                'pci' => \Yii::app()->request->getParam('pci', null),
                'pdi' => \Yii::app()->request->getParam('pdi', null),
                'finished_object' => \Yii::app()->request->getParam('finished_object', null),
            );
            $cards = \DataListModel::getInstance()
                    ->setExtensionCopy($this->extension_copy)
                    ->setGlobalParams($global_params)
                    ->setGetAllData(true)
                    ->prepare(\DataListModel::TYPE_LIST_VIEW)
                    ->getData();
 
            if($cards) {
                foreach($cards as $card) {
                    if(!empty($card[$this->extension_copy->prefix_name . '_id']))
                       $this->ids[] = $card[$this->extension_copy->prefix_name . '_id'];
                }
            }

        }else {
            //отмеченные записи
            if(!empty($ids)) 
                $this->ids = array_keys($ids);
        }

        return $this;
    }
    

    public function edit(){
        
        if(!empty($this->ids)) {
                
            $alias = 'evm_' . $this->extension_copy->copy_id;
            $dinamic_params = array(
                'tableName' => $this->extension_copy->getTableName(null, false),
                'params' => Fields::getInstance()
                    ->setOnlyActiveRecordsFields(array_keys($this->data))
                    ->getActiveRecordsParams($this->schema_parser),
            );
            
                
            //изменение выбранных записей
            foreach($this->ids as $id) {
                $extension_data = EditViewModel::modelR($alias, $dinamic_params)->findByPk($id);
                $extension_data->scenario = 'bulk_edit';
                $extension_data->setElementSchema($this->schema_parser);
                $extension_data->extension_copy = $this->extension_copy;
                
                if(!empty($this->expressions)) {
                    foreach($this->expressions as $field_name => $expression) {

                        $card_data = $extension_data->getAttributes();
                        
                        $part = (!empty($card_data[$field_name])) ? $card_data[$field_name] : 0;
                        $value = \Math::getInstance()
                            ->setExpression($expression)
                            ->getCalculatedValue($part);
                        if($value !== false) {
                            $this->data[$field_name] = $value;
                        }
                    }
                }
                $extension_data->setMyAttributes($this->data);

                $extension_data->save();
            
            }

        }

    }




   
    
    
    
    
    
} 
