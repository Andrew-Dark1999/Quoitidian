<?php

use Tasks\extensions\ElementMaster\EditView\Elements\Edit;
use Tasks\models\DataListModel;

class EditViewController extends EditView{




    public function actionEdit(){
        ViewList::setViews(array('ext.ElementMaster.EditView.Elements.Edit.Edit' => '\Tasks\extensions\ElementMaster\EditView\Elements\Edit\Edit'));
        parent::actionEdit();
    }

}
