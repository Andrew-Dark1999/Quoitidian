<?php
/**
 * ResponsibleBpmRoleModel - контроль ролей, что выбраны в качестве ответсвенного за процесс
 *
 * @autor Alex R.
 */

namespace Process\models;


use Process\extensions\ElementMaster\Schema;

class ResponsibleBpmRoleModel extends ResponsibleBpmFactoryModel{


    /**
     * actionCheck
     */
    protected function actionCheck(){
        if($this->_vars['ug_type'] == \ParticipantModel::PARTICIPANT_UG_TYPE_GROUP){
            $this->_be_error = true; // если есть ошибки

            $this->sendMessageToProcessResponsible();
        }
    }




    /**
     * getDialogHtml - возвращает верстку со списком значений
     */
    public function getDialogHtml($li_only = false){
        $sapi_type = php_sapi_name();
        if($sapi_type == 'cli'){
            return;
        }

        $vars = $this->_vars;
        $vars['action'] = \Process\models\ParticipantModel::ACTION_CHANGE;

        $participant_model = new \Process\models\ParticipantModel();
        $participant_model
                ->setVars($vars)
                ->setApplyException(true)
                ->setExceptionList();

        $data = array(
            'base_ug_id' => $this->_vars['base_ug_id'],
            'base_ug_type' => $this->_vars['base_ug_type'],
            'ug_id' => $this->_vars['attributes']['ug_id'],
            'ug_type' => $this->_vars['attributes']['ug_type'],
            'html_values' => $participant_model->getHtmlValuesUserRoles($this->_vars['base_ug_id']),
            'html_active_responsible' => '',
            'rbr_model' => $this,
        );

        list($process_controller) = \Yii::app()->createController('Process/ListView');

        if(!empty($this->_vars['attributes']['ug_id']) && !empty($this->_vars['attributes']['ug_type'])){
            $html_active_responsible = $participant_model->getHtmlValues(
                \ParticipantModel::PARTICIPANT_UG_TYPE_USER,
                $this->_vars['process_id'],
                $this->_vars['attributes']['ug_id'],
                $this->_vars['attributes']['ug_type'],
                array($this->_vars['base_ug_id'])
            );
            if(!empty($html_active_responsible)){
                $data['html_active_responsible'] = $html_active_responsible[0]['html'];
            }
        }

        $data['li_html'] = $process_controller->renderPartial('/dialogs/li-participant', $data, true);


        if($li_only){
            return $data['li_html'];
        }

        $html = $process_controller->renderPartial('/dialogs/participant', $data, true);

        return $html;
    }





    /**
     * getLableTitle - подпись для элемента ввода
     */
    public function getLableTitle(){
        if(empty($this->_vars['base_ug_id'])) return;

        $title = \RolesModel::getRolesModel($this->_vars['base_ug_id'])->getModuleTitle();

        return \Yii::t('ProcessModule.base', 'Responsible for the role of "{s}"', array('{s}' => $title));
    }



}
