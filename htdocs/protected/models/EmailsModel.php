<?php
/**
 * EmailsModel
 */

class EmailsModel extends ActiveRecord{


    public $tableName = 'emails';


    public static function model($className=__CLASS__){
        return parent::model($className);
    }


    public function rules(){
        return array(
            array('email_id', 'safe', 'on'=>'insert, insert_communication'),
            array('email, title, ehc_image1', 'length', 'max'=>255, 'on'=>'insert, insert_communication'),
            array('email', 'required', 'on'=>'insert, insert_communication, validate'),
            array('email', 'unique', 'on'=>'insert, insert_communication'),
            array('email', 'email', 'on'=>'insert, validate'),
        );
    }




    public function relations(){
        return array(
            'usersStorageEmail' => array(self::HAS_MANY, 'UsersStorageEmailModel', 'email_id'),
            'participantEmail' => array(self::HAS_ONE, 'ParticipantEmailModel', array('email_id' => 'email_id'), 'joinType' => 'left join'),
            'participantEmailList' => array(self::HAS_MANY, 'ParticipantEmailModel', 'participant_email_id'),
        );
    }



    public function attributeLabels(){
        return array(
            'email' => Yii::t('base', 'Email'),
        );
    }


    public function scopeEmail($email = null){
        if($email === null){
            $email = $this->email;
        }

        if($email == false){
            return $this;
        }

        $criteria = new CDBCriteria();
        $criteria->condition = 'email=:email';
        $criteria->params = array(':email' => $email);
        $this->getDbCriteria()->mergeWith($criteria);

        return $this;
    }



    public function scopeWithOutEmailId($email_id_list){
        if($email_id_list == false){
            return $this;
        }

        $criteria = new CDBCriteria();
        $criteria->addNotInCondition('t.email_id', $email_id_list);
        $this->getDbCriteria()->mergeWith($criteria);

        return $this;
    }



    /**
     * saveUnique - сохраняет новый емейл, или возвращает существующий
     * @return $this
     */
    public function saveUnique(){
        if($this->save() == false){
            $emails_model = static::model()->scopeEmail($this->email)->find();
            if($emails_model){
                return $emails_model;
            }
        }

        return $this;
    }




    public static function findByEmailIdList($email_id_list){
        $email_id_list = (array)$email_id_list;

        $data_model = (new \DataModel())
            ->setSelect('email')
            ->setFrom('{{emails}}')
            ->setWhere(array('in', 'email_id', $email_id_list));

        return $data_model->findCol();
    }



    public static function findByEmail($email){
        $email_model = static::model()->find(array(
                                'condition' => 'email=:email',
                                'params' => array(':email' => $email),
                            ));

        return $email_model;
    }


    public function getAvatar($thumb_size = 32){
        $avatar = \Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ListView.Elements.Avatar.Avatar'),
            array(
                'use_init' => false,
                'data_array' => $this,
                'thumb_size' => $thumb_size,
            ))
            ->getAvatar();

        return $avatar;
    }


}
