<?php

/**
 * Class ParticipantEmailModel
 */
class ParticipantEmailModel extends ActiveRecord{


    public $tableName = 'participant_email';

    public static function model($className=__CLASS__){
        return parent::model($className);
    }


    public function relations(){
        return array(
            'emails' => array(self::HAS_ONE, 'EmailsModel', array('email_id' => 'email_id')),
        );
    }



    public function rules(){
        return array(
            array('participant_email_id', 'safe'),
            array('copy_id,data_id,email_id', 'required'),

        );
    }



    public function scopeCardParams($copy_id, $data_id){
        $criteria = new CDBCriteria();
        $criteria->condition = 'copy_id=:copy_id AND data_id=:data_id';
        $criteria->params = array(
            ':copy_id' => $copy_id,
            ':data_id' => $data_id,
        );
        $this->getDbCriteria()->mergeWith($criteria);

        return $this;
    }




    /**
     * исключает ИД из запроса
     */
    public function scopeWithOutParticipantEmailId($participant_email_id){
        if($participant_email_id == false){
            return $this;
        }
        $criteria = new CDBCriteria();
        $criteria->addNotInCondition('participant_email_id', $participant_email_id);
        $this->getDbCriteria()->mergeWith($criteria);

        return $this;
    }





    public function setMyAttributes($data){
        foreach($this->getAttributes() as $key => $value){
            if(isset($data[$key])){
                if($key == 'participant_email_id') continue;

                $this->{$key} = $data[$key];
            }
        }
    }



    /**
     * Возвращает массив конечных сущностей обьекта. Используется в списках Участников
     * @param array $exception_list_id - список ИД для исключения
     */
    public function getOtherEntities($copy_id, $data_id, $exception_email_id_list = null){
        $criteria = '';
        $with = array(
            'usersStorageEmails.participantEmail' => array(
                'on' => 'copy_id=:copy_id AND data_id=:data_id',
                'params' => array(
                    ':copy_id' => $copy_id,
                    ':data_id' => $data_id,
                ),
            ),
            'usersStorageEmails.usersStorageEmail' => array(
                'select' => false,
                'order' => 'usersStorageEmail.date_affect desc',
            ),
        );

        if($exception_email_id_list){
            $criteria = new CDBCriteria();
            $criteria->addNotInCondition('usersStorageEmails.email_id', $exception_email_id_list);
            $this->getDbCriteria()->mergeWith($criteria);
        }

        $users_model = UsersModel::model()
                            ->with($with)
                            ->scopeAuthorizeUser()
                            ->find($criteria);

        if($users_model == false){
            return;
        }

        $emails_model_list = $users_model->usersStorageEmails;
        $result = array();
        if(!empty($emails_model_list)){
            foreach($emails_model_list as $emails_model){
                $result[] = $this->getPreparedData($emails_model);
            }
        }

        return $result;
    }



    /**
     * Возвращает данные сушности исходя из параметров
     */
    public function getEntityDataById($email_id){
        $data_model = EmailsModel::model()->findByPk($email_id);

        $result = $this->getPreparedData($data_model);


        return $result;
    }






    public function getEntityData(\ParticipantEmailModel $participant_model = null){
        $result = array();
        if($participant_model === null) $participant_model = $this;

        if(!empty($participant_model)){
            $result = array(
                'participant_email_id' => $participant_model->participant_email_id,
                'email_id' => $participant_model->emails->email_id,
                'email' => $participant_model->emails->email,
                'title' => $participant_model->emails->title,
                'ehc_image1' => $participant_model->emails->ehc_image1,
            );
        }

        return $result;

    }





    /**
     * формирует и возвращает данние
     */
    private function getPreparedData($email_model){
        $result = array();

        if(!empty($email_model)){
            $result = array(
                'participant_email_id' => ($email_model->participantEmail ? $email_model->participantEmail->participant_email_id : null),
                'email_id' => $email_model->email_id,
                'email' => $email_model->email,
                'title' => $email_model->title,
                'ehc_image1' => $email_model->ehc_image1,
            );
        }

        return $result;
    }








    /**
     * getParticipantSaved - возвращает список сохраненный участников
     */
    public static function getParticipantSaved($copy_id, $data_id){
        $participant_model = static::model()
            ->with(array('emails' => array('select'=>false)))
            ->findAll(array(
                'select' => 't.*',
                'condition' => 'copy_id =:copy_id AND data_id =:data_id',
                'params' => array(
                    ':copy_id' => $copy_id,
                    ':data_id' => $data_id,
                ),
                'order' => 'emails.email',
            ));

        return $participant_model;
    }






    /**
     * getParticipantSaved - возвращает список сохраненный участников
     */
    public static function getParticipantSavedCount($copy_id, $data_id){
        $count = static::model()
            ->with(array('emails' => array('select'=>false)))
            ->count(array(
                'select' => 't.*',
                'condition' => 'copy_id =:copy_id AND data_id =:data_id',
                'params' => array(
                    ':copy_id' => $copy_id,
                    ':data_id' => $data_id,
                ),
                'group' => 'emails.email'
            ));

        return $count;
    }



    /**
     * findEmails - возвращает список email адресов по рание заданым параметрам
     * @return array|null
     */
    public function findEmails(){
        $participant_email_list = $this->findAll(array('group' => 't.email_id'));

        $email_list = array();

        if($participant_email_list == false){
            return $email_list;
        }

        $email_list = array();

        foreach($participant_email_list as $participant_email){
            if($participant_email->emails){
                $email_list[] = $participant_email->emails->email;
            }
        }

        return $email_list;
    }




    public static function hasParticipant($copy_id, $data_id, $email_id){
        $count = static::model()->count(array(
            'condition' => 'copy_id=:copy_id AND data_id=:data_id AND email_id=:email_id',
            'params' => array(
                ':copy_id' => $copy_id,
                ':data_id' => $data_id,
                ':email_id' => $email_id,
            ),
        ));

        return $count ? true : false;
    }




    public static function hasParticipantEmail($copy_id, $data_id, $email){
        $criteria = new  CDbCriteria();
        $criteria->addCondition('copy_id=:copy_id AND data_id=:data_id');
        $criteria->params = array(
            ':copy_id' => $copy_id,
            ':data_id' => $data_id,
        );

        if($email){
            if(is_array($email)){
                $criteria->addInCondition('emails.email', $email);
            } else {
                $criteria->addCondition('emails.email=:email');
                $criteria->params+= array(
                    ':email' => $email,
                );
            }
        }

        $count = static::model()->with('emails')->count($criteria);

        return $count ? true : false;
    }




    /**
     * getEmailListIsExistsInCommunications
     * @param $participant_list
     * @param $participant_email_list
     * @return $this
     */
    public static function getEmailListIsExistsInCommunications($participant_list, $participant_email_list){
        // Проверяем емейл-адреса:
        // Пропускаем те адреса, что указаны в параметрах для коммуниккаций участникав данного канала
        $result = array();
        $communication_email_list = array();

        foreach($participant_list as $users_id){
            $communication_params = (new \CommunicationsServiceParamsModel())->getUserParams($users_id, 'email');
            if($communication_params['user_login']){
                $communication_email_list[] = $communication_params['user_login'];
            }
        }

        if($communication_email_list == false){
            return;
        }

        $communication_email_list = array_unique($communication_email_list);

        $email_model_list = \EmailsModel::model()->findAllByPk($participant_email_list);
        if($email_model_list == false){
            return;
        }

        foreach($email_model_list as $email_model){
            if(!in_array($email_model->email, $communication_email_list)){
                continue;
            }
            $result[] = $email_model->email_id;
        }

        return $result;
    }




}
