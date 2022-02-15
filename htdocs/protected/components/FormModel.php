<?php

class FormModel extends CFormModel {
	
    /**
    *  Возвращает массив ошибок из класса Validate
    *  return Validate
    */
    public function getErrorsHtml(){
        if(!$this->hasErrors()) return;
        return Validate::getInstancegetInstance()->addValidateResultFromModel($this->getErrors())->getValidateResultHtml();
    }



    public function attributeLabelByName($name){
        $attributes = $this->attributeLabels();

        return $attributes[$name];
    }


}
