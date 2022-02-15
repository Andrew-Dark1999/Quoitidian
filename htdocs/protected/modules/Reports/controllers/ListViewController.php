<?php


class ListViewController extends \ListView{




    public function actionShow(){
        ViewList::setViews(array('site/listView' => '/site/list-view'));
        parent::actionShow();
    }


    public function actionDelete($copy_id){
        parent::actionDelete($copy_id);

        $id_list = \Yii::app()->request->getParam('id');
        if($id_list){
            \Reports\models\ReportsFilterModel::model()->deleteAll('reports_id in (:reports_id)', array(':reports_id' => implode(',', $id_list)));
            \Reports\models\ReportsUsersStorageModel::model()->deleteAll('reports_id in (:reports_id)', array(':reports_id' => implode(',', $id_list)));
        }
    }



}
