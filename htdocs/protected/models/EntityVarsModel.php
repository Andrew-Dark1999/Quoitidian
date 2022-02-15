<?php
/**
 * EntityVarsModel
 * @author Alex R.
 */

class EntityVarsModel{


    private $_vars = array();


    public function getVars(){
        return $this->_vars;
    }


    public function getParentKey(){
        return \Yii::app()->request->getParam('entity_parent_key');
    }


    public function getParentEventId(){
        return \Yii::app()->request->getParam('entity_parent_event_id');
    }


    public function prepareModuleVars(array $vars = null){
        $default_vars = array(
            'copy_id' => \Yii::app()->request->getParam('copy_id'),
            'pci' => \Yii::app()->request->getParam('pci'),
            'pdi' => \Yii::app()->request->getParam('pdi'),
            'this_template' => (int)\Yii::app()->request->getParam('this_template', \EditViewModel::THIS_TEMPLATE_MODULE),
            'finished_object' => (int)\Yii::app()->request->getParam('finished_object'),
        );

        $properties = array(
            'id',
            'parent_copy_id',
            'parent_data_id',
            'relate_template',
            'template_data_id',
        );

        foreach($properties as $property_name){
            if(array_key_exists($property_name, $_POST)){
                $default_vars[$property_name] = \Yii::app()->request->getParam('pci');
            }
        }


        if($vars){
            foreach($vars as $key => $value){
                if($value === ""){
                    $value = null;
                }
                if(is_numeric($value) || is_bool($value)){
                    $value = (integer)$value;
                }

                $default_vars[$key] = $value;
            }
        }


        foreach($default_vars as $key => &$value){
            if($value === ""){
                $value = null;
            }
            if(is_numeric($value)){
                $value = (integer)$value;
            }
        }


        $this->_vars['module'] = $default_vars;

        return $this;
    }













}
