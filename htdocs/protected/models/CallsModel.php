<?php

class CallsModel extends ActiveRecord{

    const CALLS_TYPE_INCOMING           = 1;           // incoming - входящий
    const CALLS_TYPE_OUTGOING           = 2;           // outgoing - исходящий
    const CALLS_TYPE_MISSED             = 3;             // missed - пропущенный
    const CALLS_TYPE_NOT_GET_THROUGH    = 4;    // not_get_through - недозвон


    public $tableName = 'calls';



    public static function model($className=__CLASS__){
        return parent::model($className);
    }


    public function rules(){
        return array(
            array('module_title, calls_to, calls_from, calls_duration', 'length', 'max'=>255),
            array('calls_type', 'numerical', 'allowEmpty'=>true, 'integerOnly' => true),
        );
    }



    public function relations(){
        return array(
            /*
            'usersStorageEmail' => array(self::HAS_MANY, 'UsersStorageEmailModel', 'email_id'),
            'participantEmail' => array(self::HAS_ONE, 'ParticipantEmailModel', array('email_id' => 'email_id'), 'joinType' => 'left join'),
            'participantEmailList' => array(self::HAS_MANY, 'ParticipantEmailModel', 'participant_email_id'),
            */
        );
    }



    public function attributeLabels(){
        return array();
    }




    public function getData($extension_copy, $only_PK = false, $pci=null, $pdi=null, $finished_object, $template, $limit = null, $page_num = 1){
        $global_params = array(
            'pci' => $pci,
            'pdi' => $pdi,
            'finished_object' => $finished_object,
        );

        $result = \DataListModel::getInstance()
            ->setExtensionCopy($extension_copy)
            ->setFinishedObject($finished_object)
            ->setThisTemplate($template)
            ->setGlobalParams($global_params)
            ->setSortingToPk('desc')
            ->setDefinedPK($only_PK)
            ->setDataIfParticipant($extension_copy->dataIfParticipant())
            //->setLastCondition()
            ->setPaginationPage($page_num)
            ->setPaginationPageSize($limit)
            ->prepare(\DataListModel::TYPE_LIST_VIEW)
            ->getData();

        return $result;
    }






    /*
    public function getParticipantsCount(){
        $count = ParticipantModel::getParticipantSavedCount(ExtensionCopyModel::MODULE_CALLS, $this->calls_id);

        switch(strlen((string)$count)){
            case 1 : $tmp_count = $count; break;
            case 2 : $tmp_count = $count % 10; break;
            case 3 : $tmp_count = $count % 100; break;
            case 4 : $tmp_count = $count % 1000; break;
            case 5 : $tmp_count = $count % 10000; break;
        }
        if(in_array($tmp_count, array(1))){
            $suffix = 'участник';
        }
        if(in_array($tmp_count, array(2, 3, 4))){
            $suffix = 'участника';
        }
        if(in_array($tmp_count, array(5, 6, 7, 8, 9, 0))){
            $suffix = 'участников';
        }
        return $count . ' ' . $suffix;
    }
    */


    public function getClientTitle(){
        return 'Is Client 1';
    }


    public function getContactTitle(){
        return 'Is Contact 1';
    }




}
