<?php

class EditViewController extends \EditView{
    
    public function actionEdit(){
        
        \ViewList::setViews(array('site/editView' => '/site/edit-view'));
        
        $action_model = new EditViewActionModel();
        $action_model
            ->setEditViewBuilder(new \Finances\extensions\ElementMaster\EditViewBuilder())
            ->run(EditViewActionModel::ACTION_RUN_AUTO, $_POST)
            ->getResult();
    }
    
}
