<?php
/**
 * @author Alex R.
 */

namespace Process\models;

use Process\extensions\ElementMaster\Schema;

class ParticipantModel extends \ParticipantModel{


    const ACTION_ADD    = 'add';
    const ACTION_CHANGE = 'change';
    //const ACTION_DELETE_PARTICIPANT_ROLE = 'delete_participant_role';

    private $_apply_exception = false;
    private $_exception_list = array();

    private $_vars = array();


    public function setVars($vars){
        $this->_vars = $vars;
        return $this;
    }



    public function setApplyException($apply_exception){
        $this->_apply_exception = $apply_exception;
        return $this;
    }




    public function setExceptionList(){
        $this->_exception_list = \Process\extensions\ElementMaster\Schema::getInstance()->getResponsibleList();

        return $this;
    }


    public function setActiveUgId($active_ug_id){
        $this->_active_ug_id = $active_ug_id;
        return $this;
    }



    public function setActiveUgType($active_ug_type){
        $this->_active_ug_type = $active_ug_type;
        return $this;
    }


    public function isSetResponsibleInExcetion($ug_id, $ug_type){
        if(empty($this->_exception_list)) return false;

        $result = false;

        foreach($this->_exception_list as $item){
            if($this->_vars['action'] == self::ACTION_CHANGE){
                if($item['ug_id'] == $ug_id && $item['ug_type'] == $ug_type && $item['ug_id'] == $this->_vars['ug_id'] && $item['ug_type'] == $this->_vars['ug_type']){
                    return false;
                } elseif($item['ug_id'] == $ug_id && $item['ug_type'] == $ug_type){
                    $result = true;
                }
            } else
            if($this->_vars['action'] == self::ACTION_ADD){
                if($item['ug_id'] == $ug_id && $item['ug_type'] == $ug_type){
                    $result = true;
                }
            }
            if($result == true) break;
        }

        return $result;
    }



    /**
     * getHtmlValues - возвращает список участников (Роли, струдники)
     */
    public function getHtmlValues($group_data = null, $pdi = null, $ug_id = null, $ug_type = null, $user_roles_id_list = null){
        $participant_list = \ParticipantModel::getParticipantList($group_data, \ExtensionCopyModel::MODULE_PROCESS, $pdi, $ug_id, $ug_type, $user_roles_id_list);

        if(!empty($group_data) && !is_array($group_data)){
            $group_data = array($group_data);
        }

        if($group_data === null){
            $type_const_list = parent::getTypeConstList(\ExtensionCopyModel::MODULE_PROCESS, $pdi);
            $participant_list = array_merge($this->getParticipantConstList(null, $type_const_list), $participant_list);
        } else if(in_array(self::PARTICIPANT_UG_TYPE_CONST, $group_data)){
            $type_const_list = (array)$ug_id;
            $participant_list = array_merge($this->getParticipantConstList(null, $type_const_list), $participant_list);
        }

        if(empty($participant_list)) return;

        $result = array();
        $index = 0;
        foreach($participant_list as $participant){
            if($participant['ug_type'] == self::PARTICIPANT_UG_TYPE_USER && !\ParticipantModel::checkAccessParticipantForModule(\ExtensionCopyModel::MODULE_PROCESS, $participant['ug_id'])) continue;
            if($participant['ug_type'] == self::PARTICIPANT_UG_TYPE_GROUP && !\ParticipantModel::checkAccessRoleForModule(\ExtensionCopyModel::MODULE_PROCESS, $participant['ug_id'])) continue;
            if($this->_apply_exception && $this->isSetResponsibleInExcetion($participant['ug_id'], $participant['ug_type']) == true) continue;

            if($participant['ug_type'] == \ParticipantModel::PARTICIPANT_UG_TYPE_USER){
                $result[$index]['html'] = \DataValueModel::getInstance()
                                                ->setFileLink(false)
                                                ->getRelateValuesToHtml($participant, array(
                                                    'relate_field' => array('sur_name', 'first_name', 'father_name'),
                                                    'relate_module_copy_id' => \ExtensionCopyModel::MODULE_STAFF));
            } elseif($participant['ug_type'] == \ParticipantModel::PARTICIPANT_UG_TYPE_GROUP){
                $result[$index]['html'] = \DataValueModel::getInstance()
                                                ->setFileLink(false)
                                                ->setAvatarSrc(\RolesModel::getAvatarSrc())
                                                ->getRelateValuesToHtml($participant, array(
                                                    'relate_field' => array('module_title'),
                                                    'relate_module_copy_id' => \ExtensionCopyModel::MODULE_ROLES));
            } elseif($participant['ug_type'] == \ParticipantModel::PARTICIPANT_UG_TYPE_CONST){
                $avatar = (new \AvatarModel())
                                ->setSrc((new ParticipantConstModel())->getImageSrc())
                                ->getAvatar();

                $result[$index]['html'] =   $avatar . $participant['title'];
            }


            $result[$index]['ug_id'] = $participant['ug_id'];
            $result[$index]['ug_type'] = $participant['ug_type'];
            $index++;
        }

        return $result;
    }



    /**
     * getHtmlValuesUserRoles - возвращает список участников (сотрудников) опеределенной роли
     */
    public function getHtmlValuesUserRoles($roles_id = null){
        $result = array();

        if($roles_id == false){
            return $result;
        }

        $users_list = \RolesModel::getRolesModel($roles_id)->getUsersModelList();
        if(empty($users_list)){
            return $result;
        }

        $index = 0;
        foreach($users_list as $user_model){
            if(!\ParticipantModel::checkAccessParticipantForModule(\ExtensionCopyModel::MODULE_PROCESS, $user_model->users_id)) continue;
            if($this->_apply_exception && $this->isSetResponsibleInExcetion($user_model->users_id, self::PARTICIPANT_UG_TYPE_USER) == true) continue;

            $result[$index]['html'] = \DataValueModel::getInstance()
                                            ->setFileLink(false)
                                            ->getRelateValuesToHtml($user_model->getAttributes(), array(
                                                'relate_field' => array('sur_name', 'first_name', 'father_name'),
                                                'relate_module_copy_id' => \ExtensionCopyModel::MODULE_STAFF));

            $result[$index]['ug_id'] = $user_model['users_id'];
            $result[$index]['ug_type'] = self::PARTICIPANT_UG_TYPE_USER;
            $index++;
        }

        return $result;
    }





    public static function getParticipant($copy_id, $data_id, $responsible = null){
        if($responsible === null){
            return \ParticipantModel::model()->find('copy_id=:copy_id AND data_id=:data_id',
                array(
                    ':copy_id' => $copy_id,
                    ':data_id' => $data_id,
                ));
        }
        return \ParticipantModel::model()->find('copy_id=:copy_id AND data_id=:data_id AND responsible=:responsible',
            array(
                ':copy_id' => $copy_id,
                ':data_id' => $data_id,
                ':responsible' => $responsible,
            ));
    }





    /**
     * replaceParticipantConstToParticipantUser - подменяет Константу участником из связанного обьекта
     */
    public static function replaceParticipantConstToParticipantUser($participant_model_const, $participant_model_related){
        if(empty($participant_model_related)){
            return;
        }

        $flag = (new ParticipantConstModel())->getProcessFlagByConstType($participant_model_const->ug_id);

        $participant_model_const->setAttributes([
            'ug_id' => $participant_model_related->ug_id,
            'ug_type' => $participant_model_related->ug_type,
        ]);

        if($participant_model_const->save()){
            $participant_model_const->insertProcessFlag($flag);
        }
    }








    /**
     * updateOperationsParticipant - оновляет участников для свазяннах карточек операторов
     */
    public static function updateOperationsParticipant($copy_id, $data_id, $participant_vars){
        $has_flag = $participant_vars['to']['flag'];

        $condition = array(
            'copy_id=:copy_id',
            'data_id=:data_id',
        );

        $criteria = new \CDbCriteria();
        $criteria->params = array(
            ':copy_id' => $copy_id,
            ':data_id' => $data_id,
            ':ug_type' => $participant_vars['to']['ug_type'],
        );

        if($has_flag){
            $condition[] = 'ug_type=:ug_type';
            $condition[] = 'participantFlags.flag=:flag';
            $criteria->params[':flag'] = $participant_vars['to']['flag'];
        } else {
            $condition[] = 'ug_id=:ug_id';
            $condition[] = 'ug_type=:ug_type';
            $condition[] = 'participantFlags.flag is null';
            $criteria->params[':ug_id'] = $participant_vars['to']['ug_id'];
        }

        $criteria->addCondition(implode(' AND ', $condition));

        $participant_model = \ParticipantModel::model()->with('participantFlags')->find($criteria);

        if(!empty($participant_model) && $participant_model->responsible === "1"){
            if($has_flag == false && $participant_model->ug_id == $participant_vars['to']['ug_id']){
                return false;
            }

            if(
                $participant_model->ug_id == $participant_vars['to']['ug_id'] &&
                $participant_model->ug_type == $participant_vars['to']['ug_type'] &&
                $participant_model->ug_type == static::PARTICIPANT_UG_TYPE_USER
            ){
                return false;
            }
        }


        $params = array(
            ':copy_id' => $copy_id,
            ':data_id' => $data_id,
        );

        /*
        $responsible_model = (new \DataModel())
                                ->setFrom('{{participant}}')
                                ->setWhere('copy_id=:copy_id AND data_id=:data_id AND responsible="1"', $params)
                                ->find();
        */

        (new \DataModel())->Update('{{participant}}', array('responsible' => '0'), 'copy_id=:copy_id AND data_id=:data_id', $params);


        // удаляем участники-константы. она должна быть одна
        $criteria = new \CDbCriteria();
        $criteria->addCondition('copy_id=:copy_id');
        $criteria->addCondition('data_id=:data_id');
        $criteria->addCondition('ug_type=:ug_type');

        $criteria->params = [
            ':copy_id' => $copy_id,
            ':data_id' => $data_id,
            ':ug_type' =>  \ParticipantModel::PARTICIPANT_UG_TYPE_USER,
        ];

        if($participant_vars['to']['flag']){
            $criteria->addCondition('participantFlags.flag!=:flag');
            $criteria->params[':flag'] = $participant_vars['to']['flag'];
        } else {
            $criteria->addInCondition('participantFlags.flag', (new \ParticipantFlagsModel())->getFlagsListFull());
        }

        $criteria->addCondition('(SELECT count(*) FROM {{participant}} WHERE copy_id=:copy_id AND data_id=:data_id AND ug_id=t.ug_id AND ug_type=:ug_type) > 1');

        $pm_list = \ParticipantModel::model()->with('participantFlags')->findAll($criteria);
        if($pm_list){
            foreach($pm_list as $pm){
                $pm->delete();
            }
        }


        //
        $responsible = '1';
        if(in_array($participant_vars['to']['ug_type'], [static::PARTICIPANT_UG_TYPE_GROUP])){
            $responsible = '0';
        }

        if(!empty($participant_model)){
            $participant_model->ug_id = $participant_vars['to']['ug_id'];
            if((boolean)$responsible){
                $participant_model->setAttribute('responsible', $responsible);
            }
            $participant_model->save();
        } else {
            if($responsible){
                (new \ParticipantModel())->updateAll(
                    array('responsible' => '0'),
                    array(
                        'condition' => 'copy_id=:copy_id AND data_id=:data_id AND ug_type =:ug_type AND responsible=:responsible',
                        'params' => array(
                            ':copy_id' => $copy_id,
                            ':data_id' => $data_id,
                            ':ug_type' => $participant_vars['to']['ug_type'],
                            'responsible' => '1',
                        )
                    )
                );
            }

            $attributes = array(
                'copy_id' => $copy_id,
                'data_id' => $data_id,
                'ug_id' => $participant_vars['to']['ug_id'],
                'ug_type' => $participant_vars['to']['ug_type'],
                'responsible' => $responsible,
            );

            $process_model = new \ParticipantModel();
            $process_model->setMyAttributes($attributes);
            $process_model->insert();

            if($has_flag){
                $process_model->insertProcessFlag($participant_vars['to']['flag']);
            }
        }

        return true;
    }






    /**
     * updateOperationsParticipant - оновляет участников для свазяннах карточек операторов
     */
    public static function replaceOperationsParticipant($copy_id, $data_id, $participant_vars){
        if($participant_vars['from']['ug_type'] != \ParticipantModel::PARTICIPANT_UG_TYPE_CONST){
            return static::updateOperationsParticipant($copy_id, $data_id, $participant_vars);
        }

        $criteria = new \CDbCriteria();
        $criteria->addCondition('copy_id=:copy_id AND data_id=:data_id AND ug_id=:ug_id AND ug_type=:ug_type');
        $criteria->params = array(
                                ':copy_id' => $copy_id,
                                ':data_id' => $data_id,
                                ':ug_id' => $participant_vars['from']['ug_id'],
                                ':ug_type' => $participant_vars['from']['ug_type'],
                            );

        $participant_model = \ParticipantModel::model()->with('participantFlags')->find($criteria);

        if($participant_model == false){
            return false;
        }

        $participant_model->setAttributes(array(
                'ug_id' => $participant_vars['to']['ug_id'],
                'ug_type' => $participant_vars['to']['ug_type'],
            )
        );

        if($participant_model->save()){
            $participant_model->insertProcessFlag($participant_vars['to']['flag']);
            return true;
        }

        return false;
    }





    /**
     * getActiveResponsibleRolesForReplace - проверка оперраторов процесса и поиск Роли в качестве ответсвенного
     * или проверка на несуществующего ответственного
     */
    public static function getActiveResponsibleRolesForReplace($process_model){
        $result = array();

        if(in_array($process_model->getBStatus(), array(ProcessModel::B_STATUS_STOPED, ProcessModel::B_STATUS_ZERO, ProcessModel::B_STATUS_IN_WORK))){
            $schema = SchemaModel::getInstance()->getSchema();
            $result = ResponsibleBpmRoleCheckWorkingModel::getInstance(true)
                                    ->setSchema($schema)
                                    ->run()
                                    ->getResult();
        }

        return $result;
    }





    protected function getTypeConstList($copy_id, $data_id){
        return (array)\ParticipantConstModel::TC_RELATE_RESPONSIBLE;

        /*
        $result = array();

        if($data_id == false){
            return $result;
        }

        $process_model = ProcessModel::model()->findByPk($data_id);

        if($process_model->this_template == false){
            return $result;
        }

        if($process_model->related_module){
            $result[] = \ParticipantConstModel::TC_RELATE_RESPONSIBLE
        }

        return $result;
        */
    }





    public static function findTypeConstByEntity($copy_id, $data_id){
        return static::model()->find(array(
           'condition' => 'copy_id=:copy_id AND data_id=:data_id AND ug_id in('.implode(',', \ParticipantConstModel::getTypeConstListFull()).') AND ug_type=:ug_type',
           'params' => array(
               ':copy_id' => $copy_id,
               ':data_id' => $data_id,
               ':ug_type' => \ParticipantModel::PARTICIPANT_UG_TYPE_CONST,
           ),
        ));

    }

}
