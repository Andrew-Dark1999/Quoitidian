<?php
/**
 * ProcessValidateModel - проверка сущностей процесса
 */

namespace Process\models;


class ProcessValidateModel{

    private $_process_model;

    private $_error = false;

    private $_messages = array();



    public function setProcessModel($process_model){
        $this->_process_model = $process_model;
        return $this;
    }


    public function getResult(){
        return array(
            'status' => (!empty($this->_error) ? false : true),
            'messages' => $this->_messages,
        );
    }


    private function addMessageError($attribute, $message){
        $this->_error = true;
        $this->_messages[$attribute] = $message;

        return $this;
    }


    private function hasMessageByAttribute($attribute){
        return !(empty($this->_messages[$attribute]));
    }


    /**
     * checkCreateFromTemplate - проверка сущностей перед создание процесса из шаблона
     */
    public function checkCreateFromTemplateProcess(){
        $vars = $this->_process_model->getVars();

        $objects = array(
            'project_name',
            'template',
        );

        foreach($objects as $object_name){
            switch($object_name){
                case 'project_name' :
                    if(empty($vars['process']['module_title'])){
                        $this->addMessageError($object_name . '_block', \Yii::t('messages', 'You must fill in the "{s}"', array('{s}' => \Yii::t('ProcessModule.base', 'Process name'))));
                    }
                    break;
                case 'template':
                    if(empty($vars['process']['process_id'])){
                        $this->addMessageError($object_name . '_block', \Yii::t('messages', 'You must fill in the "{s}"', array('{s}' => \Yii::t('base', 'Templates'))));
                    }
                    break;
            }

        }

        return $this;
    }





    /**
     * checkCreateFromTemplate - проверка сущностей перед создание процесса из шаблона -
     */
    public function checkCreateFromTemplateBpmParams(){
        $vars = $this->_process_model->getVars();
        if(empty($vars['bpm_params'])) return $this;

        $result = (new \Process\models\BpmParamsModel())
                        ->setVars($vars['bpm_params'])
                        ->validate()
                        ->run(true, true)
                        ->getResultMessages();

        if($result['status'] == false){
            $this->_error = true;
            $this->_messages = array_merge($this->_messages, $result['messages']);
        }

        return $this;
    }




    /**
     * checkCreateFromTemplateParticipantTypeConst - проверка наличия контанты "Связанный ответсвенный" и самого
     *                                               ответственного в сущности связанного объекта
     *
     * @return $this
     */
    public function checkCreateFromTemplateParticipantTypeConst(){
        $vars = $this->_process_model->getVars();
        if(empty($vars['bpm_params'])){
            return $this;
        }

        $participant_model = ParticipantModel::findTypeConstByEntity(\ExtensionCopyModel::MODULE_PROCESS, $this->_process_model->process_id);
        if($participant_model == false){
            return $this;
        }

        if(empty($vars['bpm_params']['objects']) || !array_key_exists(BpmParamsModel::OBJECT_BINDING_OBJECT, $vars['bpm_params']['objects'])){
            return $this;
        }

        $copy_id = null;
        $data_id = null;
        if(!empty($vars['bpm_params']['objects'][BpmParamsModel::OBJECT_BINDING_OBJECT]['attributes']['copy_id'])){
            $copy_id = $vars['bpm_params']['objects'][BpmParamsModel::OBJECT_BINDING_OBJECT]['attributes']['copy_id'];
        }
        if(!empty($vars['bpm_params']['objects'][BpmParamsModel::OBJECT_BINDING_OBJECT]['attributes']['copy_id'])){
            $data_id = $vars['bpm_params']['objects'][BpmParamsModel::OBJECT_BINDING_OBJECT]['attributes']['data_id'];
        }


        if($copy_id == false || $data_id == false){
            if($this->hasMessageByAttribute('relate_object_block') == false){
                $this->addMessageError('relate_object_block', \Yii::t('ProcessModule.messages', 'In the entity of the related object is not defined responsible'));
            }
            return $this;
        }


        if($copy_id == false || $data_id == false){
            if($this->hasMessageByAttribute('relate_object_block') == false){
                $this->addMessageError('relate_object_block', \Yii::t('ProcessModule.messages', 'In the entity of the related object is not defined responsible'));
            }
            return $this;
        }

        if($this->_process_model->related_module){
            $count_models = \ParticipantModel::model()->count(array(
                    'condition' => 'copy_id=:copy_id AND data_id=:data_id AND responsible="1"',
                    'params' => array(
                        ':copy_id' => $copy_id,
                        ':data_id' => $data_id,
                    )
                )
            );
        }

        if($this->_process_model->related_module == false || $count_models == false){
            if($this->hasMessageByAttribute('relate_object_block') == false){
                $this->addMessageError('relate_object_block', \Yii::t('ProcessModule.messages', 'In the entity of the related object is not defined responsible'));
            }
        }

        return $this;
    }


}
