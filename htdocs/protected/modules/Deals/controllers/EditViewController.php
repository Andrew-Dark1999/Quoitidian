<?php

class EditViewController extends \EditView{
    
    public function actionEdit(){
        $action_model = new EditViewActionModel();
        $action_model
            ->setEditViewBuilder(new \Deals\extensions\ElementMaster\EditViewBuilder())
            ->run(EditViewActionModel::ACTION_RUN_AUTO, $_POST)
            ->getResult();
    }
    
}
