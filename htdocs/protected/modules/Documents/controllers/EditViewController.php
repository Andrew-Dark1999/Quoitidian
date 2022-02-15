<?php

//namespace Documents\controllers;

class EditViewController extends \EditView{
    
   
   /**
    * Просмотр/добавление/редактирование данних в EditView
    * 
    * Доступные сценарии: update, edit
    */     
    public function actionEdit(){
        $action_model = new \EditViewActionModel();
        
        $action_model
            ->setEditViewBuilder(new \Documents\extensions\ElementMaster\EditViewBuilder())
            ->run(EditViewActionModel::ACTION_RUN_AUTO, $_POST)
            ->getResult();

   }
   
}
