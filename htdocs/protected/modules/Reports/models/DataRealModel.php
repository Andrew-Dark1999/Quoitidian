<?php
/**
 * DataRealModel
 * @author Alex R.
 * @version 1.0
 */
namespace Reports\models;

class DataRealModel{





    private static function isDateTimeAllDay($params, $value_data){
        $field_name = $params['name'] . '_ad';
        $value = $value_data[$field_name];

        return (bool)$value;
    }



    public static function getElementDataReal($extension_copy, $params, $value_data, $relate_add_avatar){

        //numeric, string
        if($params['type'] == 'numeric'){
            $data = $value_data[$params['name']];
            return \Helper::TruncateEndZero($data);
        }

        //string
        elseif($params['type'] == 'string' || $params['type'] == 'display' || $params['type'] == 'relate_string'){
            //while type = password
            $attr_value = $value_data[$params['name']];
            if(isset($params['input_attr'])){
                $attr_tmp = json_decode($params['input_attr'], true);
                if(!empty($attr_tmp)){
                    if(in_array('password', $attr_tmp)) $attr_value = '';
                }
            }
            return $attr_value;
        }

        //file, file_image
        elseif($params['type'] == 'file' || $params['type'] == 'file_image'){
            if(!empty($value_data[$params['name']])){
                if($value_data[$params['name']]){
                    $criteria=new \CDbCriteria;
                    $criteria->condition='relate_key=:relate_key';
                    $criteria->params=array(':relate_key' => $value_data[$params['name']]);
                    $criteria->limit = 1;
                    $criteria->order = 'file_date_upload desc';
                    $upload_model = \UploadsModel::model()->find($criteria);

                }
                if(empty($upload_model)) return;
                return $upload_model->getFileTitle();
            }
        }

        //datetime
        elseif($params['type'] == 'datetime'){
            if($params['type_view'] == \Fields::TYPE_VIEW_BUTTON_DATE_ENDING){
                if(self::isDateTimeAllDay($params, $value_data)){
                    $full_date = \DateTimeOperations::getFullDateStr(\Helper::formatDate($value_data[$params['name']]));
                } else {
                    $full_date = \DateTimeOperations::getFullDateStr(\Helper::formatDateTimeShort($value_data[$params['name']]), true, true, false);
                }
            } else {
                $full_date = \DateTimeOperations::getFullDateStr(\Helper::formatDateTimeShort($value_data[$params['name']]), true);
            }

            return $full_date;
        }

        //logical
        elseif($params['type'] == 'logical'){
            $logical = \Fields::getInstance()->getLogicalData();
            if(isset($logical[$value_data[$params['name']]]))
                return $logical[$value_data[$params['name']]];
        }

        //select
        elseif($params['type'] == 'select' && $params['type_view'] == \Fields::TYPE_VIEW_DEFAULT){
            return $value_data[$extension_copy->prefix_name . '_' . $params['name'] . '_title'];
        }

        //select (button)
        elseif($params['type'] == 'select' && $params['type_view'] == \Fields::TYPE_VIEW_BUTTON_STATUS){
            if(!isset($value_data[$extension_copy->prefix_name . '_' . $params['name'] . '_title'])){
                $value_data[$extension_copy->prefix_name . '_' . $params['name'] . '_title'] = '';
            }
            return $value_data[$extension_copy->prefix_name . '_' . $params['name'] . '_title'];
        }

        //access
        elseif($params['type'] == 'access'){
            $data = array('id'=> $value_data[$params['name']], 'type' => $value_data[$params['name'] . '_type']);
            return \AccessModel::getInstance()->getAccessTitle($data);
        }

        //relate
        elseif($params['type'] == 'relate'){
            $relate_extension_copy = \ExtensionCopyModel::model()->findByPk($params['relate_module_copy_id']);
            $relate_module_table = \ModuleTablesModel::model()->find(array(
                'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_one"' ,
                'params' => array(
                    ':copy_id'=>$extension_copy->copy_id,
                    ':relate_copy_id'=>$params['relate_module_copy_id'])));

            $relate_data_id_list = \DataModel::getInstance()
                ->setFrom('{{' . $relate_module_table->table_name. '}}')
                ->setWhere($extension_copy->prefix_name . '_id = :id',
                    array(':id' => $value_data[$extension_copy->prefix_name.'_id']))
                ->findAll();
            $html = array();
            $relate_id_list = array();
            if(!empty($relate_data_id_list)){
                if((boolean)$params['relate_many_select'] == true && count($relate_data_id_list) > 1){

                    $html[] = count($relate_data_id_list);
                    foreach($relate_data_id_list as $relate_data_id)
                        $relate_id_list[] = $relate_data_id[$relate_extension_copy->prefix_name . '_id'];
                }  else {

                    foreach($relate_data_id_list as $relate_data_id){
                        $relate_data = \DataModel::getInstance()
                            ->setExtensionCopy($relate_extension_copy)
                            ->setFromModuleTables(true);
                        $relate_data
                            ->setFromFieldTypes();
                        //responsible
                        if($relate_extension_copy->isResponsible())
                            $relate_data->setFromResponsible();
                        //participant
                        if($relate_extension_copy->isParticipant())
                            $relate_data->setFromParticipant();
                        $relate_data
                            ->setCollectingSelect()
                            ->andWhere(array('AND', $relate_extension_copy->getTableName() . '.' . $relate_extension_copy->prefix_name . '_id' . '=:' . $relate_extension_copy->prefix_name . '_id'),
                                array(':'.$relate_extension_copy->prefix_name . '_id' => $relate_data_id[$relate_extension_copy->prefix_name . '_id']));
                        $relate_data->setGroup($relate_extension_copy->prefix_name . '_id');
                        $relate_data = $relate_data->findAll();

                        if(!empty($relate_data)){
                            foreach($relate_data as $relate_value){
                                $html[] = \DataValueModel::getInstance()->getRelateValuesToHtml($relate_value, $params, $relate_add_avatar);
                            }
                        }
                        $relate_id_list[] = $relate_data_id[$relate_extension_copy->prefix_name . '_id'];
                    }
                }
            }
            return implode(' ', $html);
        }

        //relate_this
        elseif($params['type'] == 'relate_this'){
            $id = $value_data[$params['name']];
            if($id){
                $relate_data = \DataModel::getInstance()
                    ->setFrom($extension_copy->getTableName())
                    ->setWhere($extension_copy->prefix_name . '_id = :id', array(':id' => $id))
                    ->findAll();
            }

            $html = array();
            if(!empty($relate_data))
                foreach($relate_data as $relate_value){
                    $html[] = \DataValueModel::getInstance()->getRelateValuesToHtml($relate_value, $params, $relate_add_avatar);
                }
            return implode(' ', $html);
        }

        //responsible
        elseif($params['type'] == 'relate_participant' && ($params['type_view'] == \Fields::TYPE_VIEW_BUTTON_RESPONSIBLE || $params['type_view'] ==  \Fields::TYPE_VIEW_BLOCK_PARTICIPANT)) {
            $html = '';
            if(!empty($value_data['participant_ug_id'])){
                if((integer)$value_data['participant_ug_type'] == \ParticipantModel::PARTICIPANT_UG_TYPE_USER){
                    $relate_select_list = \DataModel::getInstance()
                        ->setFrom('{{users}}')
                        ->setWhere('users_id = ' . $value_data['participant_ug_id'])
                        ->findAll();
                    if(!empty($relate_select_list))
                        $html = \DataValueModel::getInstance()
                            ->setFileLink(false)
                            ->getRelateValuesToHtml($relate_select_list[0], array(
                                'relate_field'=>array('sur_name', 'first_name', 'father_name'),
                                'relate_module_copy_id' => 5), $relate_add_avatar); //берем данные из пользователей
                }elseif((integer)$value_data['participant_ug_type'] == \ParticipantModel::PARTICIPANT_UG_TYPE_GROUP){

                }
            }

            return $html;
        }

    }





}


