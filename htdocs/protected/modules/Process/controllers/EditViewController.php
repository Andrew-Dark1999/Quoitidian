<?php

class EditViewController extends \EditView{


    /**
     * сохранение самого процесса
     */
    public function actionEdit(){
        $is_new_template = false;
        // если новая и из шаблона
        if(
            !empty($_POST['EditViewModel']) &&
            isset($_POST['this_template']) &&
            $_POST['this_template'] == EditViewModel::THIS_TEMPLATE_TEMPLATE &&
            empty($_POST['id'])
        ){
            $is_new_template = true;
        }

        $action_model = new EditViewActionModel();
        $result = $action_model
                        ->run(EditViewActionModel::ACTION_RUN_AUTO, $_POST)
                        ->getResult(false);

        if(!empty($result)){
            if($is_new_template && !empty($result['id'])){
                $process_model = \Process\models\ProcessModel::getInstance($result['id']);
                $process_model->setAttribute('schema', json_encode(\Process\extensions\ElementMaster\Schema::getInstance()->getDefaultSchema()));
                $process_model->save();
                \Process\models\ProcessModel::getInstance($result['id'])->refresh();

                if(!empty($result['status']) && $result['status'] == \EditViewActionModel::STATUS_SAVE){
                    \Process\models\OperationsModel::model()->saveNewOperations($_POST['id'], $result['id'], $process_model->getSchema(), false);
                }
            }


            if(!empty($result['id'])){
               // для запуска процесса
                \Process\models\ProcessModel::getInstance($result['id']);
                \Process\models\SchemaModel::getInstance()->setOperationsExecutionStatus();
            }

        }

        $this->renderJson($result);
    }




    /**
     * Сохранение результатов InLine редактирования
     */
    public function actionInLineSave($copy_id){
        ob_start();
        parent::actionInLineSave($copy_id);
        $result = ob_get_contents();
        if(!empty($result)){
            $result = json_decode($result, true);

            // для запуска процесса
            if(!empty($result['id'])){
                \Process\models\ProcessModel::getInstance($result['id']);
                \Process\models\SchemaModel::getInstance()->setOperationsExecutionStatus();
            }
        }
        ob_clean();
        ob_flush();

        $this->renderJson($result);
    }




    /**
     * добавление данних в EditView в пре-выбором - новый/из шаблона
     */
    public function actionEditSelect(){
        $this->data['process_id'] = \Yii::app()->request->getParam('process_id');
        $this->data['process_title'] = \Process\models\ProcessModel::getInstance($this->data['process_id'])->getProcessTitle();

        if($this->data['process_id']){
            $bpm_vars = array(
                'action' => \Process\models\BpmParamsModel::ACTION_CHECK,
                'process_id' => $this->data['process_id'],
                'objects' => array(
                    'binding_object' => null,
                    'participants' => null,
                ),
            );
            $this->data['bpm_params_html'] = (new \Process\models\BpmParamsModel())
                                                    ->setVars($bpm_vars)
                                                    ->validate()
                                                    ->run(true, true)
                                                    ->getDialogHtml(true);
            ;
        }

        \ViewList::setViews(array('dialogs/editViewAdd' => '/dialogs/edit-view-add'));

        parent::actionEditSelect();
    }




}
