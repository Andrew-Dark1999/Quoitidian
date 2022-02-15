<?php
/**
 * Created by PhpStorm.
 * User: rom
 * Date: 19.06.17
 * Time: 13:13
 */

class MailerLettersSourcesModel extends ActiveRecord{


    const SOURCE_GENERAL        = 'general';
    const SOURCE_COMMUNICATIONS = 'communications';


    public $tableName = 'mailer_letters_sources';

    private $_params;



    public static function model($className=__CLASS__){
        return parent::model($className);
    }



    public function relations(){
        return array(
            'mailerOutbox' => array(self::BELONGS_TO, 'MailerLettersOutboxModel', 'mailer_id'),
        );
    }


    public function setParams($params){
        $this->_params = $params;
    }



    public function addNewLetter($params){
        if (empty($params)) {
            return false;
        }

        $this->setParams($params);

        return $this->saveModel();
    }



    public function getModelByMailerId($mailer_id){
        return self::find('mailer_id = :mailer_id', array(':mailer_id' => $mailer_id));
    }



    private function saveModel(){

        $this->mailer_id = $this->_params['mailer_id'];
        $this->source = $this->_params['source'];
        if(!empty($this->_params['params']['service_params_id'])){
            $this->params = $this->_params['params']['service_params_id'];
        }
        return $this->save();
    }



    public function getMessageSourceName(){
        if(($mailer_outbox_model = $this->mailerOutbox) !== null){
            if(($mailer_outbox_relate_model = $mailer_outbox_model->mailerOutboxRelate) !== null){
                switch ($mailer_outbox_relate_model->resource_type){
                    case MailerLettersOutboxRelateModel::RESOURCE_TYPE_ACTIVITY :
                        if(($activity_model = $mailer_outbox_relate_model->activityMessages) !== null){
                            return $activity_model->type_comment;
                        }
                        break;
                    default :
                        break;
                }
            }
        }
        return false;
    }


}
