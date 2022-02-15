<?php
/**
 * ResponsibleBpmUsersModel - контроль пользователей, что выбраны в качестве ответсвенного за процесс
 *
 * @autor Alex R.
 */

namespace Process\models;


use Process\extensions\ElementMaster\Schema;

class ResponsibleBpmUsersModel extends ResponsibleBpmFactoryModel{

    protected static $_responsible_title_number = 0;



    /**
     * actionCheck
     */
    protected function actionCheck(){
        $this->_be_error = true;

        $this->sendMessageToProcessResponsible();
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

        $group_data = \ParticipantModel::PARTICIPANT_UG_TYPE_USER;

        $data = array(
            'base_ug_id' => $this->_vars['base_ug_id'],
            'base_ug_type' => $this->_vars['base_ug_type'],
            'ug_id' => $this->_vars['attributes']['ug_id'],
            'ug_type' => $this->_vars['attributes']['ug_type'],
            'html_values' => $participant_model->getHtmlValues($group_data, $vars['process_id'], null, null, true),
            'html_active_responsible' => '',
            'rbr_model' => $this,
        );



        if(!empty($this->_vars['attributes']['ug_id']) && !empty($this->_vars['attributes']['ug_type'])){
            $html_active_responsible = $participant_model->getHtmlValues(
                \ParticipantModel::PARTICIPANT_UG_TYPE_USER,
                $this->_vars['process_id'],
                $this->_vars['attributes']['ug_id'],
                $this->_vars['attributes']['ug_type'],
                true
            );
            if(!empty($html_active_responsible)){
                $data['html_active_responsible'] = $html_active_responsible[0]['html'];
            }
        }

        list($process_controller) = \Yii::app()->createController('Process/ListView');
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
        static::$_responsible_title_number++;

        return \Yii::t('base', 'Responsible') . ' (' . static::$_responsible_title_number . ')';
    }



}
