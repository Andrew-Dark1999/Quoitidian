<?php
/**
 * HeaderNoticeController
 */

class HeaderNoticeController extends Controller {


    /**
     * actionGetEntities
     */
    public function actionGetEntities(){
        if(!Yii::app()->request->isAjaxRequest || !Yii::app()->request->isPostRequest){
            return $this->renderJson(['status' => false]);
        }

        $result = (new \HeaderNoticeModel())
                            ->setId(Yii::app()->request->getPost('id'))
                            ->setVars(Yii::app()->request->getPost('vars'))
                            ->prepare()
                            ->getResult();

        return $this->renderJson($result);
    }





    /**
     * actionSetMarkView
     */
    public function actionSetMarkView(){
        $result = array(
            'status' => false,
        );

        if(Yii::app()->request->isAjaxRequest && Yii::app()->request->isPostRequest) {
            $vars = Yii::app()->request->getPost('vars');
            $history_id = $vars['history_id'];

            $history_model = HistoryModel::model()->findByPk($history_id);

            if($history_model && $history_model->copy_id && $history_model->data_id){
                $model = History::markHistoryIsView($history_model->copy_id, $history_model->data_id);
                if($model){
                    $result['status'] = true;
                }
            } else {
                $mark = HistoryMarkViewModel::model()->find(
                    'user_id=:user_id and history_id=:history_id',
                    array(
                        ':user_id'    => WebUser::getUserId(),
                        ':history_id' => $history_id
                    )
                );

                if($mark){
                    $vars['notice_history_id_list'] = (new \HeaderNoticeModel())->getRelatedHistoryIdList($history_id);

                    $mark->setAttribute('is_view', 1);

                    if($mark->save()) {
                        $result['status'] = true;
                    }
                }
            }

        }

        if($result['status']){
            $result = (new \HeaderNoticeModel())
                                ->setId(Yii::app()->request->getPost('id'))
                                ->setVars($vars)
                                ->prepare()
                                ->getResult();
        }

        return $this->renderJson($result);
    }




    /**
     * actionSetMarkViewAll
     */
    public function actionSetMarkViewAll(){
        HistoryMarkViewModel::setRead();

        $result = (new \HeaderNoticeModel())
            ->setId(Yii::app()->request->getPost('id'))
            ->setVars(Yii::app()->request->getPost('vars'))
            ->prepare()
            ->getResult();

        return $this->renderJson($result);
    }

}
