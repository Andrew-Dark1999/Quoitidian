<?php
/**
 * BpmParamsModel - параметры ВРМ провесса: связанный обьект и связанные участники (ответсвенные за процесс)
 * @autor Alex R.
 *
 */

namespace Process\models;


class BpmParamsModel{

    const ACTION_CHECK  = 'action_check';
    const ACTION_UPDATE = 'action_update';


    const OBJECT_BINDING_OBJECT = 'binding_object';
    const OBJECT_PARTICIPANT    = 'participants';


    private $_action;
    private $_object_models = array();
    private $_process_model;
    private $_group_data = array(
        self::OBJECT_BINDING_OBJECT => false,
        self::OBJECT_PARTICIPANT => false,
    );

    public $_vars;
    public $_be_error = false;

    private $_delivery_messages = false;
    private $_history_set_is_view = true;
    private $_run_if_process_running = false;



    public function setDeliveryMessages($delivery_messages){
        $this->_delivery_messages = $delivery_messages;
        return $this;
    }


    public function setHistorySetIsView($history_set_is_view){
        $this->_history_set_is_view = $history_set_is_view;
        return $this;
    }


    public function setRunIfProcessRunning($run_if_process_running){
        $this->_run_if_process_running = $run_if_process_running;
        return $this;
    }


    public function setVars($vars){
        $this->_vars = $vars;

        if(isset($vars['action'])){
            $this->_action = $vars['action'];
        }


        $this->_process_model = ProcessModel::getInstance($vars['process_id']);

        return $this;
    }


    private function prepareObjectModels(){
        if(empty($this->_object_models)) return;

        foreach($this->_object_models as $item => $model){
            if($this->_vars['action'] == self::ACTION_CHECK && $model->getStatus()){
                unset($this->_object_models[$item]);
                continue;
            }
            if($model instanceof BindingObjectModel){
                $this->_group_data[self::OBJECT_BINDING_OBJECT] = true;
            }
            elseif($model instanceof ResponsibleBpmRoleModel || $model instanceof ResponsibleBpmUsersModel){
                $this->_group_data[self::OBJECT_PARTICIPANT] = true;
            }
        }
    }

    public function getObjectModels(){
        return $this->_object_models;
    }


    /**
     * validate
     */
    public function validate(){
        if(empty($this->_process_model)){
            $this->_be_error = true;
        }

        switch($this->_action){
            case self::ACTION_UPDATE :
                break;
        }

        return $this;
    }




    public function run($only_check = false, $check_for_constructor = false){
        if($this->_be_error) return $this;

        if($check_for_constructor == false && $this->_process_model->getMode() == ProcessModel::MODE_CONSTRUCTOR) return $this;

        if($this->_run_if_process_running && $this->_process_model->getBStatus() === \Process\models\ProcessModel::B_STATUS_TERMINATED){
            $this->_be_error = true;
            return $this;
        }

        foreach($this->_vars['objects'] as $object_name => $vars){
            switch($object_name){
                case self::OBJECT_BINDING_OBJECT:
                    $this->runBindingObject($vars, $only_check);
                    break;
                case self::OBJECT_PARTICIPANT:
                    $this->runParticipants($vars, $only_check);
                    break;
            }
        }

        if($this->_vars['action'] == self::ACTION_UPDATE && $only_check == true && $this->_be_error == false){
            $this->run(false);
        }

        return $this;
    }


    /**
     * runBindingObject - отработка сущности "связанный обьект"
     * @param $vars
     * @param $only_check
     */
    private function runBindingObject($vars, $only_check){
        if(!$vars){
            $vars = array();
        }

        if($this->_action == self::ACTION_CHECK){
            $vars['action'] = \Process\models\BindingObjectModel::ACTION_CHECK;
        } elseif($this->_action == self::ACTION_UPDATE){
            $vars['action'] = \Process\models\BindingObjectModel::ACTION_UPDATE;
        }
        $vars['process_id'] = $this->_process_model->process_id;

        $model = \Process\models\BindingObjectModel::getInstance()
                        ->setVars($vars)
                        ->validate();

        if($model->getRelateCopyId() == false) return;

        if($model->getStatus()){
            $model
                ->setAccessUpdate(!$only_check)
                ->run();
        }

        if($this->_be_error == false){
            $this->_be_error = !$model->getStatus();
        }

        $this->_object_models[] = $model;
    }


    /**
     * runParticipants - запуск проверки (или сохранения) данных для ответственных
     * @param $vars
     * @param $only_check
     */
    private function runParticipants($vars, $only_check){
        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_USERS)->getModule(false);
        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_ROLES)->getModule(false);

        ResponsibleBpmFactoryModel::flush();

        // при сохранении
        if(!empty($vars) && is_array($vars)){
            foreach($vars as $var){

                switch($var['ug_type']){
                    case \ParticipantModel::PARTICIPANT_UG_TYPE_USER:
                        $this->runResponsibleUsers($var, $only_check);
                        break;
                    case \ParticipantModel::PARTICIPANT_UG_TYPE_GROUP:
                        $this->runParticipantRole($var, $only_check);
                        break;
                    case \ParticipantModel::PARTICIPANT_UG_TYPE_CONST:
                        $this->runParticipantConst($var, $only_check);
                        break;
                }
            }
        } else {
            // при контроле

                                // проверка оперраторов процесса и поиск Роли в качестве ответсвенного или проверка
                                // на несуществующего ответственного
            $responsible_data = ParticipantModel::getActiveResponsibleRolesForReplace($this->_process_model);

            if(empty($responsible_data)){
                return;
            }

            foreach($responsible_data as $values){
                $vars = array(
                    'ug_id' => $values['ug_id'],
                    'ug_type' => $values['ug_type'],
                    'attributes' => array(
                        'ug_id' => null,
                        'ug_type' => null,
                    ),
                );

                if($values['entity_is_bad']){
                    switch($values['ug_type']){
                        case ParticipantModel::PARTICIPANT_UG_TYPE_USER:
                            $this->runResponsibleUsers($vars, $only_check);
                            break;
                        case ParticipantModel::PARTICIPANT_UG_TYPE_CONST:
                            $this->runParticipantConst($vars, $only_check);
                            break;
                    }

                } else {
                    $this->runParticipantRole($vars, $only_check);
                }
            }
        }
    }



    /**
     * runResponsibleUsers - обработка в случае, если  ответственный был удален из процесса
     * @param $vars
     * @param $only_check
     */
    private function runResponsibleUsers($vars, $only_check){
        if(empty($vars)) return;

        if($this->_action == self::ACTION_CHECK){
            $vars['action'] = \Process\models\BindingObjectModel::ACTION_CHECK;
        } elseif($this->_action == self::ACTION_UPDATE){
            $vars['action'] = \Process\models\BindingObjectModel::ACTION_UPDATE;
        }
        $vars['process_id'] = $this->_process_model->process_id;

        //ResponsibleBpmUsersModel
        $model = (new \Process\models\ResponsibleBpmUsersModel())
                        ->setVars($vars)
                        ->setAccessUpdate(!$only_check)
                        ->setDeliveryMessages($this->_delivery_messages)
                        ->validate()
                        ->run();

        if($status = $model->getStatus() == false){
            $this->_be_error = true;
        }

        $this->_object_models[] = $model;
    }





    /**
     * runParticipantRole - обработка в случае, если  ответственный Роль
     * @param $vars
     * @param $only_check
     */
    private function runParticipantRole($vars, $only_check){
        if(empty($vars)) return;

        if($this->_action == self::ACTION_CHECK){
            $vars['action'] = \Process\models\BindingObjectModel::ACTION_CHECK;
        } elseif($this->_action == self::ACTION_UPDATE){
            $vars['action'] = \Process\models\BindingObjectModel::ACTION_UPDATE;
        }
        $vars['process_id'] = $this->_process_model->process_id;

        //ResponsibleBpmRoleModel
        $model = (new \Process\models\ResponsibleBpmRoleModel())
                        ->setVars($vars)
                        ->setAccessUpdate(!$only_check)
                        ->setDeliveryMessages($this->_delivery_messages)
                        ->validate()
                        ->run();

        if($status = $model->getStatus() == false){
            $this->_be_error = true;
        }

        $this->_object_models[] = $model;
    }




    /**
     * runParticipantConst - обработка в случае, если  ответственный Константа
     * @param $vars
     * @param $only_check
     */
    private function runParticipantConst($vars, $only_check){
        if(empty($vars)) return;

        if($this->_action == self::ACTION_CHECK){
            $vars['action'] = \Process\models\BindingObjectModel::ACTION_CHECK;
        } elseif($this->_action == self::ACTION_UPDATE){
            $vars['action'] = \Process\models\BindingObjectModel::ACTION_UPDATE;
        }
        $vars['process_id'] = $this->_process_model->process_id;


        \Process\models\ResponsibleBpmConstModel::flush();

        //ResponsibleBpmConstModel
        $model = (new \Process\models\ResponsibleBpmConstModel())
                        ->setVars($vars)
                        ->setAccessUpdate(!$only_check)
                        ->setDeliveryMessages($this->_delivery_messages)
                        ->validate()
                        ->run();

        if($status = $model->getStatus() == false){
            $this->_be_error = true;
        }

        $this->_object_models[] = $model;
    }




    public function getStatus(){
        return (($this->_be_error) ? false : true);
    }



    /**
     * getResult
     */
    public function getResult($add_process_schema = true, $li_only = false){
        $this->prepareObjectModels();

        $result = array(
            'status' => $this->getStatus(),
            'message' => null,
            'group_data' => $this->_group_data,
            'process_status' => ($this->_process_model ? $this->_process_model->getBStatus() : null),
            'params_repeat' => null,
        );

        if($this->_process_model == false){
            return $result;
        }



        if($add_process_schema){
            $result['schema'] = \Process\models\SchemaModel::getInstance()
                                                    ->setOperationsExecutionStatus()
                                                    ->reloadOtherParamsForSchema()
                                                    ->getSchema(true);
        }

        if($result['status'] == true && $this->_vars['action'] == self::ACTION_UPDATE){
            $vars = array(
                'action' => self::ACTION_CHECK,
                'process_id' => $this->_vars['process_id'],
                'objects' => array('participants' => null),
            );

            $result['params_repeat'] = (new \Process\models\BpmParamsModel())
                ->setVars($vars)
                ->validate()
                ->run(true)
                ->getResult(false);
        }


        if($result['status'] == false){
            if($this->_history_set_is_view){
                $this->setHistoryMessageMarkIsView();
            }
            $result['message'] = $this->getDialogHtml($li_only);
        }

        return $result;
    }




    /**
     * getResultMessages
     */
    public function getResultMessages(){
        $this->prepareObjectModels();

        $result = array(
            'status' => (($this->_be_error) ? false : true),
            'messages' => array(),
        );

        $objects_models = $this->getObjectModels();
        if(!empty($objects_models)){
            foreach($objects_models as $object_model){
                $messages = $object_model->getMessages();
                if(!empty($messages))
                    $result['messages'] = array_merge($result['messages'], $messages);
            }
        }

        return $result;

    }


    public function getTitle(){
        $title = \Yii::t('ProcessModule.base', 'Process params'); // на удаление

        if(count($this->_object_models) > 1) return $title;
        if($this->_group_data[self::OBJECT_BINDING_OBJECT]) return \Yii::t('ProcessModule.base', 'Bind to object'); // на удаление
        if($this->_group_data[self::OBJECT_PARTICIPANT]) return \Yii::t('ProcessModule.base', 'Choosing responsible'); // на удаление
    }


    /**
     * getDialogHtml
     */
    public function getDialogHtml($li_only = false){
        $sapi_type = php_sapi_name();
        if($sapi_type == 'cli'){
            return;
        }

        list($process_controller) = \Yii::app()->createController('Process/ListView');

        $data = array(
            'bpm_params_model' => $this,
        );

        $html = '';

        if($li_only){
            foreach($this->getObjectModels() as $object_model){
                $html .= $object_model->getDialogHtml(true);
            }
        } else {
            $models = $this->getObjectModels();
            if(!empty($models)){
                $html = $process_controller->renderPartial('/dialogs/bpm-params', $data, true);
            }
        }

        return $html;
    }





    /**
     * setHistoryMessageMarkIsView
     */
    private function setHistoryMessageMarkIsView(){
        \History::markHistoryIsView(\ExtensionCopyModel::MODULE_PROCESS, ProcessModel::getInstance()->process_id);
    }




}
