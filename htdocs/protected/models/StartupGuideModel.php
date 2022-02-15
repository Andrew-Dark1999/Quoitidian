<?php
/**
 * StartupGuideModel
 * @autor Alex R.
 */

class StartupGuideModel{

    const ACTION_STEP1_SAVE             = 'step1_save';
    const ACTION_STEP3_SAVE             = 'step3_save';
    const ACTION_MEMORIES_ACTIVE_STEP   = 'memories_active_step';
    const ACTION_FLUSH                  = 'flush';
    const ACTION_STEPS_LIST             = 'steps_list';


    private $_error = false;
    private $_messages = array();
    private $_result = array();




    public function addError($key, $message, $params = array()){
        $this->_messages[$key] = Yii::t('startup-guide', $message, $params);
        $this->_error = true;

        return $this;
    }



    protected function getStatus(){
        return $this->_error ? false : true;
    }



    public function getResult(){
        $result = array(
                'status' => $this->getStatus(),
                'messages' => $this->_messages,
            );

        if($this->_result){
            $result += $this->_result;
        }

        return $result;
    }




    public function runAction($action, $vars){
        $vars = $vars;
        switch($action){
            case self::ACTION_STEP1_SAVE:
                $this->actionStep1Save($vars);
                break;
            case self::ACTION_STEP3_SAVE:
                $this->actionStep3Save($vars);
                break;
            case self::ACTION_MEMORIES_ACTIVE_STEP:
                $this->actionMemoriesActiveStep($vars);
                break;
            case self::ACTION_FLUSH:
                $this->actionFlush();
                $this->restoreUsersStoragePageSize();
                break;
            case self::ACTION_STEPS_LIST:
                $this->actionStepsStatus($vars);
                break;
        }

        return $this;
    }






    /**
     * getSteps
     */
    public static function getSteps($check_is_view = true){
        if($check_is_view){
            $params = \ParamsModel::getValueFromModel('startup_guide');
            if($params == false){
                if(\Yii::app()->request->getParam('startup_guide') == false){
                    return false;
                }
            }
        }

        self::memoryUsersStoragePageSize();

        $result = array();
        $result_vars = (array(
            'step1' => array(
                'elements' => array(
                    'panel' => \Yii::app()->controller->renderPartial('//dialogs/startup-guide', ['key' => 'step1_panel'], true),
                    'dialog' => \Yii::app()->controller->renderPartial('//dialogs/startup-guide', ['key' => 'step1_dialog', 'attributes' => static::getStep1Attributes(), 'element_disable' => static::getStep1ElementDisableStatus()], true),
                ),
            ),
            'step2' => array(
                'elements' => array(
                    'panel' => \Yii::app()->controller->renderPartial('//dialogs/startup-guide', ['key' => 'step2_panel'], true),
                    'content' => \Yii::app()->controller->renderPartial('//dialogs/startup-guide', ['key' => 'step2_content'], true),
                )
            ),
            'step3' => array(
                'elements' => array(
                    'panel' => \Yii::app()->controller->renderPartial('//dialogs/startup-guide', ['key' => 'step3_panel'], true),
                    'dialog' => \Yii::app()->controller->renderPartial('//dialogs/startup-guide', ['key' => 'step3_dialog', 'content' => self::getStep3SelectListContent()], true),
                    'select_item' => \Yii::app()->controller->renderPartial('//dialogs/startup-guide', ['key' => 'step3_select_item', 'select_params' => ['remove' => 1]], true),
                )
            ),
            'step4' => array(
                'elements' => array(
                    'panel' => \Yii::app()->controller->renderPartial('//dialogs/startup-guide', ['key' => 'step4_panel'], true),
                    'content' => \Yii::app()->controller->renderPartial('//dialogs/startup-guide', ['key' => 'step4_content'], true),
                )
            ),
            'step5' => array(
                'elements' => array(
                    'panel' => \Yii::app()->controller->renderPartial('//dialogs/startup-guide', ['key' => 'step5_panel'], true),
                    'content' => \Yii::app()->controller->renderPartial('//dialogs/startup-guide', ['key' => 'step5_content'], true),
                )
            ),
            'finish' => \Yii::app()->controller->renderPartial('//dialogs/startup', null, true)
        ));


        //startup_guide_vars
        $guide_vars = \ParamsModel::getValueArrayFromModel('startup_guide_vars');
        $result['active_step'] = $guide_vars['active_step'];

        // url`s

        //step1
        $vars = array(
            'module' => array('copy_id' => \ExtensionCopyModel::MODULE_USERS),
            'action_after' =>  array(\ContentReloadModel::CR_ACTION_AFTER_SWITCH_MENU, \ContentReloadModel::CR_ACTION_AFTER_SHOW_LEFT_MENU),
        );
        $cr_model = (new \ContentReloadModel(3, ['_use_auto_pci_pdi'=> false]))->addVars($vars)->prepare();
        $result_vars['step1']['content_reload'] = [
            'key' => $cr_model->getKey(),
            'vars' => \ContentReloadModel::getContentVars(false, false, $cr_model->getKey())[$cr_model->getKey()],

        ];


        //step2
        $vars = array(
            'index' => self::getConstructorUrl(),
            'action_after' =>  array(\ContentReloadModel::CR_ACTION_AFTER_SWITCH_MENU, \ContentReloadModel::CR_ACTION_AFTER_SHOW_LEFT_MENU),
        );
        $cr_model = (new \ContentReloadModel(1))->addVars($vars)->prepare();
        $result_vars['step2']['content_reload'] = [
            'key' => $cr_model->getKey(),
            'vars' => \ContentReloadModel::getContentVars(false, false, $cr_model->getKey())[$cr_model->getKey()],
        ];

        //step3
        $vars = array(
            'index' => self::getConstructorUrl(),
            'action_after' =>  array(\ContentReloadModel::CR_ACTION_AFTER_SWITCH_MENU, \ContentReloadModel::CR_ACTION_AFTER_SHOW_LEFT_MENU),
        );
        $cr_model = (new \ContentReloadModel(1))->addVars($vars)->prepare();
        $result_vars['step3']['content_reload'] = [
            'key' => $cr_model->getKey(),
            'vars' => \ContentReloadModel::getContentVars(false, false, $cr_model->getKey())[$cr_model->getKey()],
        ];

        //step4
        $vars = array(
            'url' => \Yii::app()->controller->createUrl('/profile-notification-settings'),
            'action_after' => array(\ContentReloadModel::CR_ACTION_AFTER_SWITCH_MENU, \ContentReloadModel::CR_ACTION_AFTER_HIDE_LEFT_MENU),
        );
        $cr_model = (new \ContentReloadModel(4))->addVars($vars)->prepare();
        $result_vars['step4']['content_reload'] = [
            'key' => $cr_model->getKey(),
            'vars' => \ContentReloadModel::getContentVars(false, false, $cr_model->getKey())[$cr_model->getKey()],
        ];

        //step5
        $vars = array(
            'url' => \Yii::app()->controller->createUrl('/profile-overview'),
            'action_after' => array(\ContentReloadModel::CR_ACTION_AFTER_SWITCH_MENU, \ContentReloadModel::CR_ACTION_AFTER_HIDE_LEFT_MENU),
        );
        $cr_model = (new \ContentReloadModel(4))->addVars($vars)->prepare();
        $result_vars['step5']['content_reload'] = [
            'key' => $cr_model->getKey(),
            'vars' => \ContentReloadModel::getContentVars(false, false, $cr_model->getKey())[$cr_model->getKey()],
        ];


        $deals_extension_copy = self::getDealsExtensionCopy();

        // step3
        if($deals_extension_copy == false){
            $result_vars['step3'] = false;
        }

        // last_step
        if(!empty($guide_vars['last_step'])){
            $step5_content_reload = $guide_vars['last_step'];
            $result_vars['step5']['last_step'] = (is_array($step5_content_reload) ? json_encode($step5_content_reload) : $step5_content_reload);
        } else {
            //step5_end
            if($deals_extension_copy){
                $vars = array(
                    'module' => array(
                        'copy_id' => $guide_vars['deals_copy_id'],
                        'destination' => 'listView',
                        'params' => array(
                            'this_template' => 0,
                        ),
                    ),
                    'action_after' => array(\ContentReloadModel::CR_ACTION_AFTER_SWITCH_MENU, \ContentReloadModel::CR_ACTION_AFTER_HIDE_LEFT_MENU),
                );
                $cr_model = (new \ContentReloadModel(6, ['_use_auto_pci_pdi' => false]))->addVars($vars)->prepare();
            } else{
                $cr_model = (new \ContentReloadModel(7))->prepare();
                $result_vars['step3'] = false;
            }

            $result_vars['step5']['content_reload_end'] = [
                'key' => $cr_model->getKey(),
                'vars' => \ContentReloadModel::getContentVars(false, false, $cr_model->getKey())[$cr_model->getKey()],
            ];
        }

        $result['vars'] = $result_vars;

        return $result;
    }




    private function actionStepsStatus($vars){
        $step_list = self::getSteps(false);

        if($step_list == false || empty($step_list['vars'])){
            return;
        }

        $result = [];

        if(empty($vars['only_status'])){
            $this->_result['steps'] = $step_list['vars'];
            return;
        }

        foreach($step_list['vars'] as $step_name => $value){
            if($value == false){
                $result[$step_name] = false;
            } else {
                $result[$step_name] = true;
            }
        }

        $this->_result['steps'] = $result;
    }


    /**
     * actionStep1Save
     * @param $attributes
     * @return $this|StartupGuideModel
     */
    private function actionStep1Save($attributes){
        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_USERS)->getModule(false);
        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_ROLES)->getModule(false);

        if($attributes == false || is_array($attributes) == false){
            return $this->addError('user1', 'You must include at least one user');
        }

        $users_model_list = array();
        $i = 1;

        foreach($attributes as $field_name => $email){
            if(empty($email)){
                continue;
            }

            $attr = [
                'sur_name' => (string)$i,
                'first_name' => \Yii::t('startup-guide', 'User'),
                'password' => '1',
                'email' => $email,
            ];

            $users_model = new \UsersModel();
            $users_model->setScenario('registration');
            $users_model->setAttributes($attr);

            if($users_model->validate()){
                $users_model_list[] = $users_model;
            } else {
                foreach($users_model->getErrorsList() as $message){
                    $this->addError($field_name, $message);
                }
                continue;
            }
        }


        if($this->getStatus() == false){
            return $this;
        }


        if($users_model_list == false){
            return $this->addError('user1', 'You must include at least one user');
        }

        foreach($users_model_list as $users_model){
            $users_model->save();

            $guide_vars = \ParamsModel::getValueArrayFromModel('startup_guide_vars');

            if($guide_vars['role_id'] && RolesModel::model()->findByPk($guide_vars['role_id'])){
                $users_roles_model = new UsersRolesModel();
                $users_roles_model->setAttribute('roles_id', $guide_vars['role_id']);
                $users_roles_model->setAttribute('users_id', $users_model->users_id);
                $users_roles_model->save();
            }
        }

        $this->setStep1Attributes($attributes);

        return $this;
    }




    private static function getDealsExtensionCopy($check_access = true){
        $guide_vars = \ParamsModel::getValueArrayFromModel('startup_guide_vars');
        if(empty($guide_vars['deals_copy_id'])){
            return;
        }

        $extension_copy = \ExtensionCopyModel::model()->findByPk($guide_vars['deals_copy_id']);




        if($extension_copy){
            if($check_access){
                if(
                    (!Yii::app()->user->isGuest && !Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, $extension_copy->copy_id, Access::ACCESS_TYPE_MODULE)) ||
                    $extension_copy->isActive() == false
                ){
                    return;
                }

                return $extension_copy;
            }
        }
    }








    /**
     * actionStep3Save
     * @param $attributes
     * @return $this
     */
    private function actionStep3Save($attributes){
        $extension_copy = self::getDealsExtensionCopy();

        if($extension_copy == false){
            return $this;
        }

        $b_status_schema = $extension_copy->getStatusField();

        if($b_status_schema == false){
            return $this;
        }

        $field_name = $b_status_schema['params']['name'];
        $field_name_id = $b_status_schema['params']['name'] . '_id';

        $table_name = $extension_copy->getTableName($field_name);


        $id_list = [];

        $select_list = (new \DataModel())
                            ->setSelect($field_name . '_id')
                            ->setFrom($table_name)
                            ->findCol();


        //function_change_keys
        $function_change_keys = function(&$attributes) use ($field_name){
            $list = [];
            foreach($attributes as $key => $value){
                $list[$field_name . '_' .$key] = $value;
            }
            $attributes = $list;
        };


        foreach($attributes as $key => &$attribute){
            $attribute_arc = $attribute;
            $function_change_keys($attribute);

            if($attribute[$field_name_id] && $select_list){
                $id_list[] = $attribute[$field_name_id];
                (new \DataModel())->Update($table_name, [$field_name . '_title' => $attribute[$field_name . '_title']], $field_name_id.'='.$attribute[$field_name_id]);
            } else{
                unset($attribute[$field_name_id]);
                (new \DataModel())->Insert($table_name, $attribute);
                $last_id = (new \DataModel())->setSelect('LAST_INSERT_ID()')->setFrom($table_name)->findScalar();
                $attribute_arc['id'] = $last_id;
            }

            $attributes[$key] = $attribute_arc;
        }


        if($select_list && $id_list){
            foreach($select_list as $key => $select_id){
                if(in_array($select_id, $id_list)){
                    unset($select_list[$key]);
                }
            }
        }
        if($select_list){
            (new \DataModel())->setText('UPDATE ' . $extension_copy->getTableName() . ' SET ' . $field_name . ' = null WHERE ' . $field_name . ' in (' . addslashes(implode(', ', $select_list)) . ')')->execute();
            (new \DataModel())->setText('DELETE FROM ' . $table_name . ' WHERE ' . $field_name . '_id in (' . addslashes(implode(', ', $select_list)) . ')')->execute();
        }

        $this->_result['attributes'] = $attributes;

        return $this;
    }






    /**
     * getStep3SelectListContent
     * @return string
     */
    private static function getStep3SelectListContent(){
        $html = '';

        $guide_vars = \ParamsModel::getValueArrayFromModel('startup_guide_vars');
        if(empty($guide_vars['deals_copy_id'])){
            return $html;
        }


        $extension_copy = \ExtensionCopyModel::model()->findByPk($guide_vars['deals_copy_id']);

        if($extension_copy == false){
            return $html;
        }

        $b_status_schema = $extension_copy->getStatusField();

        if($b_status_schema == false){
            return $html;
        }

        $field_name = $b_status_schema['params']['name'];
        $table_name = $extension_copy->getTableName($field_name);

        $select_data_list = (new \DataModel())
                        ->setFrom($table_name)
                        ->findAll();

        $select_data_list = $select_data_list;

        if($select_data_list == false){
            return $html;
        }

        foreach($select_data_list as $select_data){
            $select_params = array(
                'id' => $select_data[$field_name . '_id'],
                'remove' => $select_data[$field_name . '_remove'],
                'finished_object' => $select_data[$field_name . '_finished_object'],
                'sort' => $select_data[$field_name . '_sort'],
                'color' => $select_data[$field_name . '_color'],
                'slug' => $select_data[$field_name . '_slug'],
                'value' => $select_data[$field_name . '_title'],
            );

            $params = array(
                'key' => 'step3_select_item',
                'select_params' => $select_params,
            );

            $html .= \Yii::app()->controller->renderPartial('//dialogs/startup-guide', $params, true);
        }

        return $html;
    }


    /**
     * actionFlush
     */
    private function actionFlush(){
        //deactiveGuide
        $params_model = \ParamsModel::model()->find('title = "startup_guide"');
        if($params_model){
            $params_model->value = '0';
            $params_model->save();
        }


        //step1FlushMemoriesUsers
        $guide_vars = \ParamsModel::getValueArrayFromModel('startup_guide_vars');
        if(!empty($guide_vars['step1_attributes'])){
            unset($guide_vars['step1_attributes']);
        }
        if(!empty($guide_vars['step1_disable'])){
            unset($guide_vars['step1_disable']);
        }

        $guide_vars['active_step'] = 'step1';

        \ParamsModel::InsertOrUpdateData('startup_guide_vars', $guide_vars);

    }


    /**
     * actionMemoriesActiveStep
     */
    private function actionMemoriesActiveStep($step){
        $guide_vars = \ParamsModel::getValueArrayFromModel('startup_guide_vars');
        $guide_vars['active_step'] = $step;
        \ParamsModel::InsertOrUpdateData('startup_guide_vars', $guide_vars);
    }





    /**
     * setStep1Attributes
     */
    private function setStep1Attributes($attributes){
        $guide_vars = \ParamsModel::getValueArrayFromModel('startup_guide_vars');
        $guide_vars['step1_attributes'] = $attributes;
        $guide_vars['step1_disable'] = 1;

        \ParamsModel::InsertOrUpdateData('startup_guide_vars', $guide_vars);
    }



    /**
     * getStep1Attributes
     */
    private static function getStep1Attributes(){
        $guide_vars = \ParamsModel::getValueArrayFromModel('startup_guide_vars');
        if(!empty($guide_vars['step1_attributes'])){
            return $guide_vars['step1_attributes'];
        }
    }



    /**
     * getStep1ActiveStatus
     */
    private static function getStep1ElementDisableStatus(){
        $status = false;
        $guide_vars = \ParamsModel::getValueArrayFromModel('startup_guide_vars');
        if(!empty($guide_vars['step1_disable']) && $guide_vars['step1_disable']){
            $status = true;
        }

        return $status;
    }



    /**
     * getConstructorUrl
     */
    private static function getConstructorUrl(){
        return 'constructor?page_size=50';
    }



    /**
     * memoryUsersStoragePageSize
     */
    private static function memoryUsersStoragePageSize(){
        $users_storage = (new History())->getUserStorage(UsersStorageModel::TYPE_LIST_PAGINATION, 'constructor');
        if($users_storage && !empty($users_storage['page_size'])){
            $guide_vars = \ParamsModel::getValueArrayFromModel('startup_guide_vars');
            if(empty($guide_vars['construstor_page_size'])){
                $guide_vars['construstor_page_size'] = $users_storage['page_size'];
                \ParamsModel::InsertOrUpdateData('startup_guide_vars', $guide_vars);
            }
        }
    }


    /**
     * restoreUsersStoragePageSize
     */
    private static function restoreUsersStoragePageSize(){
        $guide_vars = \ParamsModel::getValueArrayFromModel('startup_guide_vars');

        if($guide_vars && !empty($guide_vars['construstor_page_size'])){
            $page_size = $guide_vars['construstor_page_size'];

            $users_storage = (new History())->getUserStorage(UsersStorageModel::TYPE_LIST_PAGINATION, 'constructor');
            $users_storage['page_size'] = $page_size;
            (new History())->setUserStorage(UsersStorageModel::TYPE_LIST_PAGINATION, 'constructor', $users_storage);

            unset($guide_vars['construstor_page_size']);
            \ParamsModel::InsertOrUpdateData('startup_guide_vars', $guide_vars);
        }
    }


}
