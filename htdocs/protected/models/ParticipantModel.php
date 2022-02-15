<?php


class ParticipantModel extends ActiveRecord{



    const PARTICIPANT_UG_TYPE_USER  = 'user';
    const PARTICIPANT_UG_TYPE_GROUP = 'group';
    const PARTICIPANT_UG_TYPE_CONST = 'const';


    public $tableName = 'participant';

    public $responsible = "0";

    // разрешает/запрещает изменения отметки Ответсвенный
    private static $_change_responsible = true;





    public static function model($className=__CLASS__){
		return parent::model($className);
	}


	public function __construct($scenario='insert'){
        ExtensionModel::model()->scopeModuleName('Staff')->find()->getModule();
        ExtensionModel::model()->scopeModuleName('Roles')->find()->getModule();

        parent::__construct($scenario);
    }


    public function rules(){
		return array(
			array('participant_id,copy_id,data_id,ug_id,ug_type,responsible', 'safe'),
		);
	}




    public function relations(){
        return array(
            'participantFlags' => array(self::HAS_MANY, 'ParticipantFlagsModel', 'participant_id'),
            'users' => array(self::HAS_ONE, 'UsersModel', array('users_id' => 'ug_id'), 'on' => 't.ug_type = "' . self::PARTICIPANT_UG_TYPE_USER . '"', 'together' => true),
            'roles' => array(self::HAS_ONE, 'RolesModel', array('roles_id' => 'ug_id'), 'on' => 't.ug_type = "' . self::PARTICIPANT_UG_TYPE_GROUP . '"', 'together' => true),
        );
    }




    protected function beforeSave(){
        if($this->isNewRecord){
            $this->date_create = new CDbExpression('now()');
            if(empty($this->user_create)){
                $this->user_create = WebUser::getUserId();
            }
            $this->date_edit = null;
            $this->user_edit = null;
        } else {
            $this->date_edit = new CDbExpression('now()');
            $this->user_edit = WebUser::getUserId();
        }
        return true;
    }
    
    
    
    public function setMyAttributes($data){
        foreach($this->getAttributes() as $key => $value){
            if(isset($data[$key])){
                if($key == 'participant_id') continue;
                
                if($key == 'ug_id')
                    $this->ug_id = $data[$key];
                elseif($key == 'ug_type')
                    $this->{$key} = $this->getUgType($data[$key]);
                elseif($key == 'responsible')
                    $this->{$key} = (string)$data[$key];
                else 
                    $this->{$key} = $data[$key];
            }
        }
    }
    
    
    

    /**
     * исключает ИД из запроса
     */
    public function scopeExceptionParticipantId($participant_id){
        if(empty($participant_id)) return $this;
        $criteria = new CDbCriteria();
        $criteria->addNotInCondition('participant_id', $participant_id);
        $this->getDbCriteria()->mergeWith($criteria);

        return $this;
    }




    /**
     * setChangeResponsible
     */
    public static function setChangeResponsible($change_responsible){
        self::$_change_responsible = $change_responsible;
    }



    /**
     * showLinkMakeResponsible
     */
    public static function getChangeResponsible($extension_copy = null, $edit_view_model = null, $prepare = false){
        $change_responsible = static::$_change_responsible;

        if($prepare == false){
            return $change_responsible;
        }

        if($extension_copy->copy_id == \ExtensionCopyModel::MODULE_PROCESS){
            return true;
        }

        if($edit_view_model == false){
            return true;
        }

        /* отключено
        $isset = (new \DataModel())
            ->setFrom('{{process_operations}}')
            ->setWhere(
                'copy_id=:copy_id AND card_id=:card_id',
                array(
                    ':copy_id' => $extension_copy->copy_id,
                    ':card_id' => $edit_view_model->getPrimaryKey(),
                )
            )
            ->findCount();

        if($isset){
            return false;
        }
        */

        return true;
    }




    /**
     * showLinkMakeResponsible
     */
    public static function showLinkResponsible($participant_data){
        if(self::$_change_responsible == false){
            return false;
        }

        if(in_array($participant_data['ug_type'], [ParticipantModel::PARTICIPANT_UG_TYPE_GROUP])){
            return false;
        }

        /* отключено
        if($participant_data['participant_id']){
            $participant_model = ParticipantModel::model()->findByPk($participant_data['participant_id']);
            if($participant_model && $participant_model->copy_id != \ExtensionCopyModel::MODULE_PROCESS){
                $isset = (new \DataModel())
                                ->setFrom('{{process_operations}}')
                                ->setWhere(
                                    'copy_id=:copy_id AND card_id=:card_id',
                                    array(
                                        ':copy_id' => $participant_model->copy_id,
                                        ':card_id' => $participant_model->data_id,
                                    )
                                )
                                ->findCount();

                if($isset){
                    return false;
                }
            }
        }
        */

        if(in_array($participant_data['ug_type'], [ParticipantModel::PARTICIPANT_UG_TYPE_USER, ParticipantModel::PARTICIPANT_UG_TYPE_CONST])){
            return true;
        }

        return false;
    }



    /**
     * getParticipantSaved - возвращает список сохраненный участников
     */
    public static function getParticipantSaved($copy_id, $data_id){
        $participant_model = ParticipantModel::model()
                                ->with(array('users' => array('select'=>false), 'roles' => array('select'=>false)))
                                ->findAll(array(
                                    'select' => 't.*',
                                    'condition' => 'copy_id =:copy_id AND data_id =:data_id',
                                    'params' => array(
                                        ':copy_id' => $copy_id,
                                        ':data_id' => $data_id,
                                    ),
                                    'order' => 'ug_type desc, roles.module_title, users.sur_name, users.first_name',
                                ));

        return $participant_model;
    }



    /**
     * getParticipantSavedCount - возвращает количество сохраненный участников
     */
    public static function getParticipantSavedCount($copy_id, $data_id, $with_type_groups = false){
        if($with_type_groups == false){
            $condition_groups = ' AND ug_type = "'.\ParticipantModel::PARTICIPANT_UG_TYPE_USER.'"';
        }

        $participant_model = ParticipantModel::model()
            ->with(array('users' => array('select'=>false), 'roles' => array('select'=>false)))
            ->count(array(
                'select' => 't.*',
                'condition' => 'copy_id =:copy_id AND data_id =:data_id' . $condition_groups,
                'params' => array(
                    ':copy_id' => $copy_id,
                    ':data_id' => $data_id,
                )
            ));

        return $participant_model;
    }




    /**
     * Возвращает список учасников (пользователи и группы)
     * $param null|true|array| $user_roles_id_list - дополняется списком пользователей ролей:
     *                                               null - не дополнять
     *                                               true - пользователи ролей из первого блока $data_model1
     *                                               array - список ИД ролей
     *
     */    
    public static function getParticipantList($group_data = null, $pci = null, $pdi = null, $ug_id = null, $ug_type = null, $user_roles_id_list = null, $exception_list = null){
        if(!empty($group_data) && !is_array($group_data)) $group_data = array($group_data);

        $data = array();
        $data_models = array();

        // 1. groups or const
        if($group_data === null || in_array(self::PARTICIPANT_UG_TYPE_GROUP, $group_data) || in_array(self::PARTICIPANT_UG_TYPE_CONST, $group_data)){

            /*
            if($group_data){
                $group_value = (in_array(self::PARTICIPANT_UG_TYPE_GROUP, $group_data) ? self::PARTICIPANT_UG_TYPE_GROUP : (in_array(self::PARTICIPANT_UG_TYPE_CONST, $group_data) ? self::PARTICIPANT_UG_TYPE_CONST : null));
            } else {
                $group_value = self::PARTICIPANT_UG_TYPE_GROUP;
            }
            */

            $group_value = self::PARTICIPANT_UG_TYPE_GROUP;

            $data_model = new \DataModel();
            $data_model
                ->setSelect('{{roles}}.roles_id as ug_id, concat("' . $group_value . '") as ug_type, "' . RolesModel::getAvatarSrc() . '" AS ehc_image1, {{roles}}.module_title, null AS sur_name, null AS first_name, null AS father_name, 1 AS order_index')
                ->setFrom('{{roles}}');

            if(!empty($pci) && !empty($pdi)){
                $data_model
                    ->join('participant', '{{participant}}.ug_id = {{roles}}.roles_id', array(), 'right')
                    ->andWhere('{{participant}}.copy_id=:pci AND {{participant}}.data_id=:pdi AND ug_type = "' . $group_value . '"', array(':pci' => $pci, ':pdi' => $pdi));
            }
            if(!empty($ug_id) && !empty($ug_type) && $ug_type == $group_value){
                if(empty($pci) && empty($pdi)){
                    $data_model
                        ->join('participant', '{{participant}}.ug_id = {{roles}}.roles_id', array(), 'right');
                }
                $data_model->andWhere('{{participant}}.ug_id=:ug_id AND {{participant}}.ug_type="'.$group_value.'"', array(':ug_id' => $ug_id));
            }

            if($exception_list !== null && !empty($exception_list[$group_value])) {
                if (count($exception_list[$group_value]) == 1) {
                    $data_model->andWhere('{{roles}}.roles_id != ' . addslashes($exception_list[$group_value][0]));
                } else {
                    $data_model->andWhere('{{roles}}.roles_id not in (' . addslashes(implode(',', $exception_list[$group_value])) . ')');
                }
            }

            $data_models[] = $data_model;
        }




        // 2. users
        if($group_data === null || in_array(self::PARTICIPANT_UG_TYPE_USER, $group_data)){
            $data_model = new DataModel();
            $data_model
                ->setSelect('{{users}}.users_id as ug_id, concat("' . self::PARTICIPANT_UG_TYPE_USER . '") as ug_type, ehc_image1, null AS module_title, {{users}}.sur_name, {{users}}.first_name, {{users}}.father_name, 2 AS order_index')
                ->setFrom('{{users}}')
                ->andWhere('{{users}}.active = "1"');

            if(!empty($pci) && !empty($pdi)){
                $data_model
                    ->join('participant', '{{participant}}.ug_id = {{users}}.users_id', array(), 'right')
                    ->andWhere('{{participant}}.copy_id=:pci AND {{participant}}.data_id=:pdi AND ug_type = "' . self::PARTICIPANT_UG_TYPE_USER . '"', array(':pci' => $pci, ':pdi' => $pdi));
            }

            if(!empty($ug_id) && !empty($ug_type) && $ug_type == self::PARTICIPANT_UG_TYPE_USER){
                if(empty($pci) && empty($pdi)){
                    $data_model
                        ->join('participant', '{{participant}}.ug_id = {{users}}.users_id', array(), 'right');
                }

                $data_model->andWhere('{{participant}}.ug_id=:ug_id AND {{participant}}.ug_type="' . self::PARTICIPANT_UG_TYPE_USER . '"', array(':ug_id' => $ug_id));
            }

            // исключение
            if($exception_list !== null && !empty($exception_list[self::PARTICIPANT_UG_TYPE_USER])){
                if(count($exception_list[self::PARTICIPANT_UG_TYPE_USER]) == 1)
                    $data_model->andWhere('{{users}}.users_id != ' . addslashes($exception_list[self::PARTICIPANT_UG_TYPE_USER][0]));
                else
                    $data_model->andWhere('{{users}}.users_id not in (' . addslashes(implode(',', $exception_list[self::PARTICIPANT_UG_TYPE_USER])) . ')');
            }

            $data_models[] = $data_model;


            // 3. если дополнительно отбираем сотридников из ролей в $data_model1
            if($user_roles_id_list === true){
                $data_model = new \DataModel();
                $data_model
                    ->setSelect('{{users}}.users_id as ug_id, concat("' . self::PARTICIPANT_UG_TYPE_USER . '") as ug_type, ehc_image1, null AS module_title, {{users}}.sur_name, {{users}}.first_name, {{users}}.father_name, 2 AS order_index')
                    ->setFrom('{{users}}')
                    ->andWhere('{{users}}.active = "1"');

                if(!empty($pci) && !empty($pdi)){
                    $data_model
                        ->join('users_roles', '{{users_roles}}.users_id = {{users}}.users_id', array(), 'right')
                        ->join('participant', '{{participant}}.ug_id = {{users_roles}}.roles_id', array(), 'right')
                        ->andWhere('{{participant}}.copy_id=:pci AND {{participant}}.data_id=:pdi  AND ug_type = "' . self::PARTICIPANT_UG_TYPE_GROUP . '" AND {{users}}.users_id IS NOT NULL', array(':pci' => $pci, ':pdi' => $pdi));
                }

                if(!empty($ug_id) && !empty($ug_type)){
                    if(empty($pci) && empty($pdi)){
                        $data_model
                            ->join('users_roles', '{{users_roles}}.users_id = {{users}}.users_id', array(), 'right')
                            ->join('participant', '{{participant}}.ug_id = {{users_roles}}.roles_id', array(), 'right');
                    }
                    if($ug_type == self::PARTICIPANT_UG_TYPE_GROUP){
                        $data_model->andWhere('{{participant}}.ug_id=:ug_id AND {{participant}}.ug_type="' . self::PARTICIPANT_UG_TYPE_GROUP . '"', array(':ug_id' => $ug_id));
                    } elseif($ug_type == self::PARTICIPANT_UG_TYPE_USER){
                        $data_model->andWhere('{{users}}.users_id=:users_id', array(':users_id' => $ug_id));
                    }
                }

                if($exception_list !== null && !empty($exception_list[self::PARTICIPANT_UG_TYPE_USER])){
                    if(count($exception_list[self::PARTICIPANT_UG_TYPE_USER]) == 1)
                        $data_model->andWhere('{{users}}.users_id != ' . addslashes($exception_list[self::PARTICIPANT_UG_TYPE_USER][0]));
                    else
                        $data_model->andWhere('{{users}}.users_id not in (' . addslashes(implode(',', $exception_list[self::PARTICIPANT_UG_TYPE_USER])) . ')');
                }

                $data_models[] = $data_model;
            }


            // 4. если дополнительно отбираем сотрудников из ролей
            if(!empty($user_roles_id_list) && is_array($user_roles_id_list)){
                $data_model = new DataModel();
                $data_model
                    ->setSelect('{{users}}.users_id as ug_id, concat("' . self::PARTICIPANT_UG_TYPE_USER . '") as ug_type, ehc_image1, null AS module_title, {{users}}.sur_name, {{users}}.first_name, {{users}}.father_name, 2 AS order_index')
                    ->setFrom('{{users}}')
                    ->andWhere('{{users}}.users_id IS NOT NULL AND active = "1" AND {{users_roles}}.roles_id in (' . addslashes(implode(',', $user_roles_id_list)) . ')')
                    ->join('users_roles', '{{users_roles}}.users_id = {{users}}.users_id', array(), 'right');

                if(!empty($pci) && !empty($pdi) && $ug_type == self::PARTICIPANT_UG_TYPE_USER){
                    $data_model->andWhere('{{users}}.users_id=:users_id', array(':users_id' => $ug_id));
                }

                if($exception_list !== null && !empty($exception_list[self::PARTICIPANT_UG_TYPE_USER])){
                    if(count($exception_list[self::PARTICIPANT_UG_TYPE_USER]) == 1)
                        $data_model->andWhere('{{users}}.users_id != ' . addslashes($exception_list[self::PARTICIPANT_UG_TYPE_USER][0]));
                    else
                        $data_model->andWhere('{{users}}.users_id not in (' . addslashes(implode(',', $exception_list[self::PARTICIPANT_UG_TYPE_USER])) . ')');
                }

                $data_models[] = $data_model;
            }
        }

        // собираем запрос и исполняем
        $sql = array();
        $params = array();
        $query = null;
        if(!empty($data_models)){
            if(count($data_models) == 1){
                $query = $data_models[0]->getText();
                $params = $data_models[0]->getParams();
            } else {
                foreach($data_models as $data_model){
                    $sql[] = '(' . $data_model->getText() . ')';
                    $params = array_merge($params, $data_model->getParams());
                }
                $query = implode(' UNION ', $sql);
            }
        } else {
            return $data;
        }

        $data_model = new \DataModel();
        $data_model
            ->setFrom('('.$query.') as DATA')
            ->setParams($params);

        $data_model->setOrder('order_index, module_title, sur_name, first_name, father_name');
        if(count($data_models) > 1){
            $data_model->setGroup('ug_id, ug_type');
        }

        $data = $data_model->findAll();

        return $data;
    }    




    
    /**
     * Возвращает тип сущности из его текстового представления
     */
    public static function getUgType($type_name){
        switch($type_name){
            case 'user' : 
                return self::PARTICIPANT_UG_TYPE_USER;
            case 'group' : 
                return self::PARTICIPANT_UG_TYPE_GROUP;
            case 'const' :
                return self::PARTICIPANT_UG_TYPE_CONST;
        }
    }




    /**
     * формирует и возвращает данние об учаснике (пользователь) 
     */
    private function getUsersPreparedData($data_model, $participant_model, $add_father_name = false){
        $result = array();

        if(!empty($data_model)){
            $result = $data_model->getAttributes();

            $name_list = array($result['sur_name'], $result['first_name']);
            if($add_father_name) $name_list[] = $result['father_name'];

            $result['full_name'] = implode(' ' , $name_list);
            $result['ug_id'] = $result['users_id'];
            $result['ug_type'] = self::PARTICIPANT_UG_TYPE_USER;
            $result['responsible'] = ($participant_model !== null ? (boolean)$participant_model->responsible : false);
            $result['participant_id'] = ($participant_model !== null ? $participant_model->participant_id : null);
        }

        return $result;
    }






    /**
     * формирует и возвращает данние о группе
     */
    private function getGroupPreparedData($data, $participant_model){
        $result = array();

        if(!empty($data)){
            $result = $data->getAttributes();

            $result['full_name'] = $data->getModuleTitle();
            $result['ug_id'] = $result['roles_id'];
            $result['ug_type'] = self::PARTICIPANT_UG_TYPE_GROUP;
            $result['responsible'] = ($participant_model !== null ? (boolean)$participant_model->responsible : false);
            $result['participant_id'] = ($participant_model !== null ? $participant_model->participant_id : null);
            $result['ehc_image1'] = \RolesModel::getAvatarSrc();
        }

        return $result;
    }



    /**
     * формирует и возвращает данние об учаснике (пользователь)
     */
    private function getConstPreparedData($data_model, $participant_model){
        $result = array();

        if(!empty($data_model)){
            $result = $data_model;

            $result['full_name'] = $result['title'];
            $result['responsible'] = ($participant_model !== null ? $participant_model->responsible : null);
            $result['participant_id'] = ($participant_model !== null ? $participant_model->participant_id : null);
        }

        return $result;
    }




    /**
     * формирует и возвращает данние
     */
    private function getPreparedData($data, $participant_model = null, $add_father_name = false){
        $result = array();

        if(!empty($data)){
            $result = $data;

            $full_name = '';
            if($data['ug_type'] == self::PARTICIPANT_UG_TYPE_USER){
                $name_list = array($result['sur_name'], $result['first_name']);
                if($add_father_name) $name_list[] = $result['father_name'];
                $full_name = implode(' ' , $name_list);
            } elseif($data['ug_type'] == self::PARTICIPANT_UG_TYPE_GROUP){
                $full_name = $result['module_title'];
            } elseif($data['ug_type'] == self::PARTICIPANT_UG_TYPE_CONST){
                $full_name = $result['title'];
            }


            $result['full_name'] = $full_name;
            $result['responsible'] = ($participant_model !== null ? (boolean)$participant_model->responsible : false);
            $result['participant_id'] = ($participant_model !== null ? $participant_model->participant_id : null);
        }

        return $result;
    }


    /**
     * Возвращает данные сушности
     */
    public function getEntityData(\ParticipantModel $participant_model = null){
        $result = array();

        if($participant_model === null){
            $participant_model = $this;
        }

        switch($participant_model->ug_type){
            case self::PARTICIPANT_UG_TYPE_USER :
                $result = $this->getUsersPreparedData(StaffModel::model()->findByPk($participant_model->ug_id), $participant_model);
                break;
            case self::PARTICIPANT_UG_TYPE_GROUP :
                $result = $this->getGroupPreparedData(RolesModel::model()->findByPk($participant_model->ug_id), $participant_model);
                break;
            case self::PARTICIPANT_UG_TYPE_CONST :
                $result = $this->getConstPreparedData((new ParticipantConstModel())->getConstTypeTitleByTC($participant_model->ug_id), $participant_model);
                break;
        }
        return $result;
    }





    /**
     * Возвращает данные сушности исходя из параметров ug_id и ug_type
     */
    public function getEntityDataByParams($ug_id, $ug_type, $participant_model = null){
        $result = '';
        switch($ug_type){
            case self::PARTICIPANT_UG_TYPE_USER :
                $result = $this->getUsersPreparedData(StaffModel::model()->findByPk($ug_id), $participant_model);
                break;
            case self::PARTICIPANT_UG_TYPE_GROUP :
                $result = $this->getGroupPreparedData(RolesModel::model()->findByPk($ug_id), $participant_model);
                break;
            case self::PARTICIPANT_UG_TYPE_CONST :
                $result = $this->getConstPreparedData((new ParticipantConstModel())->getConstTypeTitleByTC($ug_id), $participant_model);
                break;
        }

        return $result;
    }



    /**
      СО - связанный ответственный
      ОП - ответственный за процесс

      Список для выбора:

      Сущность Процессы-шаблоны
          - модуль - Процессы
          - шаблон
          - выбран СДМ "Связанный модуль"

      о.Задачи, о.Согласование, +Ответственного
          - модуль - не Процессы
          - сущность - связанна с процессом (оператор процесса), оператор не активный
          - Процесс - еще шаблон
          - для ОС - выбран СДМ "Связанный модуль"
     */


    /**
     * getTypeConstList - возврашает список участников-констант исходя из параметров (данных) сущности
     * @param $copy_id
     * @param $data_id
     * @return array
     */
    protected function getTypeConstList($copy_id, $data_id){
        $result = array();

        if($data_id == false){
            return $result;
        }

        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule(false);

        if($copy_id != \ExtensionCopyModel::MODULE_PROCESS){
            $edit_view_model = \EditViewModel::findEntity($copy_id, $data_id);

            if(empty($edit_view_model)){
                return $result;
            }

            $operations_model = \Process\models\OperationsModel::findOperationsModelByEntityParams($copy_id, $data_id);

            if($operations_model == false){
                return $result;
            }

            $process_model = $operations_model->process;
        } else {
            $process_model = \Process\models\ProcessModel::model()->findByPk($data_id);
        }


        if($process_model->this_template == false){
            return $result;
        }

        if($process_model->related_module){
            $result[] = \ParticipantConstModel::TC_RELATE_RESPONSIBLE;
        }

        //$result[] = \ParticipantConstModel::TC_RESPONSIBLE_FOR_PROCESS;

        return $result;
    }





    protected function getParticipantConstList($exception_list, $type_const_list){
        $type_const_list = (new ParticipantConstModel())
                                    ->setTypeConstList($type_const_list)
                                    ->getTypeConstTitleListFull();

        if($type_const_list == false){
            return array();
        }

        $result = array();
        foreach($type_const_list as $type_const){
            if(!empty($exception_list[self::PARTICIPANT_UG_TYPE_CONST]) && in_array($type_const['ug_id'], $exception_list[self::PARTICIPANT_UG_TYPE_CONST])){
                continue;
            }
            $result[] = $this->getPreparedData($type_const, null);
        }

        return $result;
    }


    /**
     * Возвращает массив конечных сущностей обьекта (пользователей, групп). Используется в списках Участников
     * @param array $exception_list_id - список ИД для исключения
     */
    public function getOtherEntities($exception_list_id = null, $copy_id = null, $data_id = null, $pci = null, $pdi = null, $group_data = null){
        $exception_list = array(
                            self::PARTICIPANT_UG_TYPE_USER => array(),
                            self::PARTICIPANT_UG_TYPE_GROUP => array(),
                            self::PARTICIPANT_UG_TYPE_CONST => array()
                        );
        if(!empty($exception_list_id)){
            foreach($exception_list_id as $data){
                switch($data['ug_type']){
                    case self::PARTICIPANT_UG_TYPE_USER:
                        $exception_list[self::PARTICIPANT_UG_TYPE_USER][] = $data['ug_id'];
                        break;
                    case self::PARTICIPANT_UG_TYPE_GROUP:
                        $exception_list[self::PARTICIPANT_UG_TYPE_GROUP][] = $data['ug_id'];
                        break;
                    case self::PARTICIPANT_UG_TYPE_CONST:
                        $exception_list[self::PARTICIPANT_UG_TYPE_CONST][] = $data['ug_id'];
                        break;
                }
            }
        } else {
            $exception_list = null;
        }

        $user_roles_id_list = null;
        if(!empty($pci) && !empty($pdi)) $user_roles_id_list = true;

        //get participant data
        $participant_list = \ParticipantModel::getParticipantList($group_data, $pci, $pdi, null, null, $user_roles_id_list, $exception_list);


        $result = array();
        if(!empty($participant_list)){
            foreach($participant_list as $participant){
                // проверка доступа к модулю
                //*****
                if($participant['ug_type'] == self::PARTICIPANT_UG_TYPE_GROUP && self::checkAccessRoleForModule($copy_id, $participant['ug_id']) == false) continue;
                if($participant['ug_type'] == self::PARTICIPANT_UG_TYPE_USER && self::checkAccessParticipantForModule($copy_id, $participant['ug_id']) == false) continue;

                $result[] = $this->getPreparedData($participant, null);
            }
        }

        $type_const_list = $this->getTypeConstList($copy_id, $data_id);
        $result = array_merge($this->getParticipantConstList($exception_list, $type_const_list), $result);

        return $result;
    }







    /**
     * проверка участника на доступ к модулю
     */
    public static function checkAccessParticipantForModule($copy_id, $users_id){
        if(Access::moduleAdministrativeAccess($copy_id)){
            $access = Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION, false);
        } else {
            $access = Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $copy_id, Access::ACCESS_TYPE_MODULE, $users_id, false);
        }
        
        return $access;
    }



    /**
     * проверка участника на доступ к модулю
     */
    public static function checkAccessRoleForModule($copy_id, $roles_id){
        if(Access::moduleAdministrativeAccess($copy_id)){
            $access = Access::checkAccessRole(PermissionModel::PERMISSION_DATA_VIEW, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION);
        } else {
            $access = Access::checkAccessRole(PermissionModel::PERMISSION_DATA_VIEW, $copy_id, Access::ACCESS_TYPE_MODULE, $roles_id);
        }

        return $access;
    }



    /**
     * возвращает данные пользователя о подписке
     * @return array()
     */
    public function getUserSubscription($copy_id, $data_id, $ug_id){
        // дописать опеределение пользователя по его группе
        return \ParticipantModel::model()->find(array(
                                    'condition' => 'copy_id=:copy_id AND data_id=:data_id AND ug_id=:ug_id AND ug_type =:ug_type',
                                    'params' => array(
                                                ':copy_id' => $copy_id,
                                                ':data_id' => $data_id,
                                                ':ug_id'   => $ug_id,
                                                ':ug_type' => \ParticipantModel::PARTICIPANT_UG_TYPE_USER,
                                    ),
        ));
    }






    /**
     * checkParentModuleData - проверка связи с данными родительского модуля
     * @return array()
     */
    private function checkParentModuleData($copy_id, $data_id){
        $parent_copy_id = \ModuleTablesModel::getParentModuleCopyId($copy_id);
        if(empty($parent_copy_id)){
            return true;
        }

        $parent_extension_copy = ExtensionCopyModel::model()->findByPk($parent_copy_id);

        if($parent_extension_copy->dataIfParticipant() == false){
            return true;
        }
        if($parent_extension_copy->isParticipant() == false && $parent_extension_copy->isResponsible() == false){
            return true;
        }

        $relate_model = \ModuleTablesModel::model()->find(array(
                'condition' => 'copy_id =:copy_id AND relate_copy_id =:relate_copy_id AND `type` in ("relate_module_one", "relate_module_many")',
                'params' => array(
                    ':copy_id' => $parent_copy_id,
                    ':relate_copy_id' => $copy_id,
                )
            )
        );

        if(empty($relate_model)){
            return false;
        }

        $id = \DataModel::getInstance()
                        ->setSelect($relate_model->parent_field_name)
                        ->setFrom('{{' . $relate_model->table_name . '}}')
                        ->setWhere($relate_model->relate_field_name . '=:relate_field_name', array(':relate_field_name' => $data_id))
                        ->findScalar();

        if(empty($id)){
            return true;
        }

        $us = \Access::checkAccessDataOnParticipant($parent_copy_id, $id);
        if(empty($us)){
            return false;
        }

        return true;
    }





    /**
     * checkParentModuleProcessData - проверка связи с данными учасника процесса
     * @return array()
     */
    private function checkParentModuleProcessData($copy_id, $data_id){
        if($copy_id != \ExtensionCopyModel::MODULE_TASKS) return false;

        $extension_copy = \ExtensionCopyModel::model()->findByPk($copy_id);
        $count = \DataModel::getInstance()
                        ->setFrom($extension_copy->getTableName())
                        ->setWhere($extension_copy->prefix_name . '_id=:data_id AND is_bpm_operation is not NULL', array(':data_id' => $data_id))
                        ->findCount();

        if($count == false){
            return false;
        }

        $process_id = \DataModel::getInstance()
            ->setSelect('process_id')
            ->setFrom('{{process_operations}}')
            ->setWhere('copy_id=' . \ExtensionCopyModel::MODULE_TASKS . ' AND card_id=:card_id', array(':card_id' => $data_id))
            ->findScalar();


        if(empty($process_id)){
            return false;
        }

        $us = \Access::checkAccessDataOnParticipant(\ExtensionCopyModel::MODULE_PROCESS, $process_id);
        if(empty($us)){
            return false;
        }

        return true;
    }





    /**
     * checkSubscriptionAccess - проверка доступа на изменение подписчиков
     */
    public function checkSubscriptionAccess(){
        //return $this;
        if($this->isBadStatus() === true) return $this;

        //проверка доступа на изменение подписчиков
        if(!$this->_edit_model->isNewRecord && $this->_extension_copy->isParticipant()){
            if(!ParticipantModel::model()->checkUserSubscription(
                $this->_extension_copy->copy_id,
                $this->_edit_model->{$this->_extension_copy->prefix_name . '_id'},
                $this->_edit_model)
            )
            {
                $this->_validate->addValidateResult('e', Yii::t('messages', 'Access denied! You are not a owner or member'));
                $this->setStatus(self::STATUS_ERROR);
                return $this;
            }
        }

        return $this;
    }




    /**
     * checkUserSubscription - возвращает статус пользователя о подписке: true/false
     * Пользователь должен быть владельцем записи или участником
     * @return boolean 
     */
    public function checkUserSubscription($copy_id, $data_id, $edit_view_model = null){
        if(empty($data_id)){
            return false;
        }

        // если владелец карточки
        if(!empty($edit_view_model) && $edit_view_model->user_create == WebUser::getUserId()){
            return true;
        }

        // котроль влюченного параметра "отображать только участников"
        $dip = \ExtensionCopyModel::model()->findByPk($copy_id)->dataIfParticipant();

        //2. если участник
        $us = \Access::checkAccessDataOnParticipant($copy_id, $data_id);
        if(($dip && !empty($us))){
            return true;
        }

        //3. если участник через связь полем Название
        $us = $this->checkParentModuleData($copy_id, $data_id);
        if((!$dip && $us)){
            return true;
        }

        //4. если участник через оператор в Процессе
        $us = $this->checkParentModuleProcessData($copy_id, $data_id);
        if($us){
            return true;
        }

        return false;
    }




    /**
     * getParticipants
     */
    public static function getParticipants($copy_id, $data_id, $ug_type = null, $responsible_only = false, $return_one = false){
        if($responsible_only == false){
            $condition = 'copy_id=:copy_id AND data_id=:data_id' . (!empty($ug_type) ? ' AND ug_type=:ug_type' : '');
        } else {
            $condition = 'copy_id=:copy_id AND data_id=:data_id '. (!empty($ug_type) ? ' AND ug_type=:ug_type' : '') . ' AND responsible = "1"';
        }
        $params = array(
            ':copy_id' => $copy_id,
            ':data_id' => $data_id
        );

        if(!empty($ug_type)){
            $params[':ug_type'] = $ug_type;
        }


        if($ug_type == false){
            $condition .= ' AND (ug_type = "'.self::PARTICIPANT_UG_TYPE_GROUP.'" OR (ug_type = "'.self::PARTICIPANT_UG_TYPE_USER.'" AND EXISTS (SELECT * FROM {{users}} WHERE users_id = t.ug_id)))';
        } elseif($ug_type == self::PARTICIPANT_UG_TYPE_USER){
            $condition .= ' AND EXISTS (SELECT * FROM {{users}} WHERE users_id = t.ug_id)';
        }

        if($return_one){
            return \ParticipantModel::model()->find(
                $condition,
                $params
            );
        } else {
            return \ParticipantModel::model()->findAll(
                $condition,
                $params
            );
        }
    }




    /**
     * getParticipantsByUserId
     */
    public static function getParticipantsByUserId($user_id, $copy_id = null, $responsible = false, $data_id = null){

        $criteria = new \CDbCriteria();
        $criteria->condition = "ug_id=:ug_id";
        $criteria->params = array(
            ':ug_id' => $user_id
        );

        if($copy_id){
           $criteria->condition .= " AND copy_id=:copy_id";
           $criteria->params[':copy_id'] = $copy_id;
        }

        if($responsible === true){
           $criteria->condition .= " AND responsible = 1";
        } elseif(is_numeric($responsible)){
            $criteria->condition .= ' AND responsible = "' . $responsible . '"';
        }

        if($data_id){
           $criteria->condition .= " AND data_id =:data_id";
           $criteria->params[':data_id'] = $data_id;
        }

        return \ParticipantModel::model()->findAll($criteria);
    }







    /**
     * deletePrepareParticipants
     */
    public static function deletePrepareParticipants($copy_id, $data_id, $users_id, $in = true){
        if(!$users_id || !is_array($users_id) || empty($users_id)){
            return 0;
        }


        $data_model = new DataModel();
        $data_model
            ->setSelect('participant_id')
            ->setFrom('{{participant}}')
            ->andWhere('copy_id=:copy_id AND data_id=:data_id', array(':copy_id' => $copy_id, ':data_id' => $data_id));

        if(!empty($users_id)){
        if(is_array($users_id)) $users_id = implode(',', $users_id);
            if($in){
                $data_model->andWhere('ug_id in (' . $users_id . ')');
            } else {
                $data_model->andWhere('ug_id not in (' . $users_id . ')');
            }
        }

        $data_participant = $data_model->findAll();
        if(!empty($data_participant)){
            foreach($data_participant as $participant){
                \QueryDeleteModel::getInstance()
                    ->setDeleteModelParams('participant', \QueryDeleteModel::D_TYPE_DATA, array('table_name' => 'participant', 'primary_field_name' => 'participant_id'))
                    ->appendValues('participant', \QueryDeleteModel::D_TYPE_DATA, $participant['participant_id']);
            }
        }
    }





    /**
     * deleteParticipants
     */
    public static function deleteParticipants($copy_id, $data_id, $users_id, $in = true){

        if(!$users_id || !is_array($users_id) || empty($users_id)){
            return 0;
        }

        $criteria = new CDbCriteria();
        $criteria->condition = "copy_id = :copy_id AND data_id = :data_id";
        $criteria->params = array(':copy_id' => $copy_id, ":data_id" => $data_id);
        if($in) {
            $criteria->addInCondition('ug_id', $users_id);
        } else {
            $criteria->addNotInCondition('ug_id', $users_id);
        }

        \ParticipantModel::model()->deleteAll($criteria);
    }





    /**
     * @param $copy_id
     * @param $data_id
     * @param $user_id
     */
    public static function deleteParticipantsFromChildrenModules($copy_id, $data_id, $user_id, $only_prepare = false){
        if($copy_id && $data_id && $user_id){
            $child = ModuleTablesModel::getChildrenModuleInfo($copy_id, $data_id);
            if(!empty($child)){
                $method = 'deleteParticipants';
                if($only_prepare == true) $method = 'deletePrepareParticipants';
                foreach($child as $c){
                    self::$method($c['pci'], $c['pdi'], array($user_id));
                }
            }
        }
    }




    /**
     * hasElementParticipant - возвращает статус наличия блока участники или ответсвенного
     * @param $copy_id
     * @return bool
     */
    public static function hasElementParticipant($copy_id){
        $status = false;
        $extension_copy = \ExtensionCopyModel::model()->findByPk($copy_id);

        if($extension_copy){
            if($extension_copy->isResponsible() || $extension_copy->isParticipant()){
                $status = true;
            }
        }

        return $status;
    }


    /**
     * hasParticipant
     */
    public static function hasParticipant($copy_id, $data_id, $ug_id, $ug_type){
        $count = static::model()->count(array(
            'condition' => 'copy_id=:copy_id AND data_id=:data_id AND ug_id=:ug_id AND ug_type=:ug_type',
            'params' => array(
                ':copy_id' => $copy_id,
                ':data_id' => $data_id,
                ':ug_id' => $ug_id,
                ':ug_type' => $ug_type,
            ),
        ));

        return $count ? true : false;
    }


    /**
     * hasParticipantEmail
     */
    public static function hasParticipantEmail($copy_id, $data_id, $ug_type, $email){
        $count = static::model()->with('users')->count(array(
                        'condition' => 'copy_id=:copy_id AND data_id=:data_id AND ug_type=:ug_type AND users.email=:email',
                        'params' => array(
                            ':copy_id' => $copy_id,
                            ':data_id' => $data_id,
                            ':ug_type' => $ug_type,
                            ':email' => $email
                        ),
                    ));

        return $count ? true : false;
    }




    /**
     * checkCorrectParticipantUsersIfExistParentModule
     */
    public static function checkCorrectParticipantUsersIfExistParentModule($copy_id, $data_id, $parent = null){

        if(!$parent || !is_array($parent) || empty($parent) || !isset($parent['pci']) || !isset($parent['pdi'])) {
            $parent = ModuleTablesModel::getParentModuleInfo($copy_id, $data_id);
        }

        if(!empty($parent)){
            $users_in = self::getParticipants(
                $parent['pci'],
                $parent['pdi'],
                self::PARTICIPANT_UG_TYPE_USER
            );
            if(!empty($users_in)){
                return self::deleteParticipants(
                    $copy_id,
                    $data_id,
                    array_keys(CHtml::listData($users_in, 'ug_id', '')),
                    false
                );
            }
        }

        return 0;

    }



    /**
     * insertProcessFlag
     */
    public function insertProcessFlag($flag){
        if($this->participant_id == false){
            return;
        }

        $particioant_flag_model = new \ParticipantFlagsModel();
        $particioant_flag_model->setAttributes(array(
            'participant_id' => $this->participant_id,
            'flag' => $flag,
        ));

        $particioant_flag_model->insert();
    }



}
