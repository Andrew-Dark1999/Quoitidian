<?php

class EditViewController extends EditView{
    
    
    public function actionEdit(){
        list($controller) = Yii::app()->createController('Reports/constructor');
        $controller->actionView();
    }
    
    
}

