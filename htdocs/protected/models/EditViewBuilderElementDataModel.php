<?php

/**
 * EditViewBuilderElementDataModel
 * @author Alex R.
 */


class EditViewBuilderElementDataModel{




    /**
     * getDataRelateParticipant
     */
    public static function getDataRelateParticipant($vars, $get_default_title = true, $get_related_responsible=true){
        $pci = Yii::app()->request->getParam('pci');
        $pdi = Yii::app()->request->getParam('pdi');

        $select_list = ParticipantModel::getParticipantList(ParticipantModel::PARTICIPANT_UG_TYPE_USER);

        if($pci && $pdi){
            $parent_participant = ParticipantModel::getParticipants($pci, $pdi);
            if(!empty($parent_participant)) {
                foreach ($select_list as $key => $user) {
                    $find = false;
                    foreach ($parent_participant as $participant) {
                        if ($participant['ug_id'] == $user['ug_id']) {
                            $find = true;
                            break;
                        }
                    }
                    if(!$find){ unset($select_list[$key]);}
                }
            }
        }

        $ug_id = WebUser::getUserId();
        $ug_type = \ParticipantModel::PARTICIPANT_UG_TYPE_USER;
        $relate_data = false;
        $html = ($get_default_title ? Yii::t('base', 'Responsible') : '');
        $data_id = $vars['extension_data']->{$vars['extension_copy']->prefix_name.'_id'};


        if(!empty($vars['default_data'])){
            $ug_id = $vars['default_data']['ug_id'];
            $ug_type = $vars['default_data']['ug_type'];
        }

        if(!empty($data_id) && $get_related_responsible){
            $relate_data = ParticipantModel::model()->find(array(
                'condition' => 'copy_id=:copy_id AND data_id=:data_id AND responsible = "1"',
                'params' => array(
                    ':copy_id' => $vars['extension_copy']->copy_id,
                    ':data_id' => $data_id,
                )));

            if(!empty($relate_data)){
                $ug_id = $relate_data->ug_id;
                $ug_type = $relate_data->ug_type;
            }

            if(!empty($relate_data) && isset($_POST['from_template']) && (boolean)$_POST['from_template']){
                if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $vars['extension_copy']->copy_id, Access::ACCESS_TYPE_MODULE, $ug_id)){
                    $relate_data = null;
                }
            }
        }

        if(!empty($ug_id)){
            $relate_select_list = DataModel::getInstance()
                ->setFrom('{{users}}')
                ->setWhere('users_id = ' . $ug_id)
                ->findAll();

            if($ug_type == ParticipantModel::PARTICIPANT_UG_TYPE_USER){
                if(!empty($relate_select_list))
                    $html = DataValueModel::getInstance()
                        ->setFileLink(false)
                        ->getRelateValuesToHtml($relate_select_list[0], array(
                            'relate_field'=>array('sur_name', 'first_name', 'father_name'),
                            'relate_module_copy_id' => \ExtensionCopyModel::MODULE_STAFF)); //берем данные из пользователей
            } elseif($ug_type == ParticipantModel::PARTICIPANT_UG_TYPE_GROUP){

            }
        }

        $result = array(
            'ug_id' => $ug_id,
            'ug_type' => $ug_type,
            'html' => $html,
            'relate_data' => $relate_data,
            'select_list' => $select_list,
        );


        return $result;
    }



}
