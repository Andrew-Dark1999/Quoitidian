<?php

class EditViewController extends EditView{



    /**
    * Просмотр/добавление/редактирование данних в EditView
    */     
    public function actionEdit(){
        if(!empty($_POST['EditViewModel'])){
            if(isset($_POST['EditViewModel']['password']) && $_POST['EditViewModel']['password'] == '')
                unset($_POST['EditViewModel']['password']);    
        }
        
        parent::actionEdit();
    }


}