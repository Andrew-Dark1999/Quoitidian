<?php
/**
* TableColumn widget  
* @author Alex R.
* @version 1.0
*/ 

class TableColumn extends CWidget{

    // Схема
    public $schema;
    // Елемент формы (виджет) FieldType
    public $field_type;
    
    private function getfieldTypeSchema($schema){
        foreach($schema as $value){
            if(isset($value['type']) && $value['type'] == 'edit')
            if(isset($value['elements'])){
                return $value['elements'];
            } 
        }
        throw new ExceptionClass(Yii::t('messages', 'Not branch "Elements" is found'));
    }     
    
    
    public function init()
    {
        $this->render('element', array(
                                    'schema' => $this->schema,
                                    'field_type' => $controller->widget(ViewList::getView('ext.ElementMaster.Constructor.Elements.FieldType.FieldType'),
                                                       array(
                                                        'schema' => $this->getfieldTypeSchema($this->schema),
                                                       ),
                                                       true),
                                 )
        );
    }
 

}