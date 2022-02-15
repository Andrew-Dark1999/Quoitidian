<?php

class CommunicationsModel extends ActiveRecord{


    private $_error = false;
    private $_messages = array();

    private $_result = array();

    public $params_model = null;

    public $tableName = 'communications';

    public static function model($className=__CLASS__){
        return parent::model($className);
    }


    public function relations(){
        return array(
            'activityMessages' => array(self::HAS_MANY, 'ActivityMessagesModel',  array('data_id' => 'communications_id'), 'on' => 'copy_id='.ExtensionCopyModel::MODULE_COMMUNICATIONS),
        );
    }




    public function addError($message, $params = array()){
        $this->_messages[] = Yii::t('communications', $message, $params);
        $this->_error = true;
        return $this;
    }



    protected function getStatus(){
        return $this->_error ? false : true;
    }



    public function getResult(){
        return array(
                'status' => $this->getStatus(),
                'messages' => array_merge($this->_messages, (!empty($this->_result['messages'])) ? $this->_result['messages'] : array()),
            ) + $this->_result;
    }








    public function getData($extension_copy, $only_PK = false, $pci=null, $pdi=null, $finished_object, $template, $limit = null, $page_num = 1, $custom_sort=false){
        $global_params = array(
            'pci' => $pci,
            'pdi' => $pdi,
            'finished_object' => $finished_object,
        );

        $custom_order_field = '';
        if(empty($_GET['sort']) || $custom_sort){
            $custom_order_field = 'activity_last_date desc';
        }

        $result = \DataListModel::getInstance()
            ->setExtensionCopy($extension_copy)
            ->setFinishedObject($finished_object)
            ->setThisTemplate($template)
            ->setGlobalParams($global_params)
            ->setSortingToPk('desc')
            ->setAppendToSelect('(select  max(date_create) from {{activity_messages}} where( copy_id = ' . $extension_copy->copy_id . ' and data_id = {{communications.communications_id}})) as activity_last_date')
            ->addOrderField($custom_order_field)
            ->setDefinedPK($only_PK)
            ->setDataIfParticipant($extension_copy->dataIfParticipant())
            ->setLastCondition('user_create = :user_id 
                                            OR :user_id in (select ug_id FROM {{participant}} 
                                                WHERE copy_id=:copy_id
                                                AND data_id=data.communications_id 
                                                AND ug_type=:ug_type_user)
                                            OR :user_id in (SELECT users_id FROM {{users_roles}} 
                                                WHERE roles_id in( SELECT ug_id FROM {{participant}} 
                                                                WHERE copy_id=:copy_id 
                                                                AND data_id=data.communications_id 
                                                                AND ug_type=:ug_type_group ))
                                            ',
                                                array(
                                                    ':user_id' => \WebUser::getUserId(),
                                                    ':copy_id' => ExtensionCopyModel::MODULE_COMMUNICATIONS,
                                                    ':ug_type_user' => 'user',
                                                    ':ug_type_group' => 'group',
                                                )
            )
            ->setPaginationPage($page_num)
            ->setPaginationPageSize($limit)
            ->prepare(\DataListModel::TYPE_LIST_VIEW)
            ->getData();


        return $result;
    }



    public function getParticipantsCount(){
        $count = ParticipantModel::getParticipantSavedCount(ExtensionCopyModel::MODULE_COMMUNICATIONS, $this->communications_id) + ParticipantEmailModel::getParticipantSavedCount(ExtensionCopyModel::MODULE_COMMUNICATIONS, $this->communications_id);

        switch(strlen((string)$count)){
            case 1 : $tmp_count = $count; break;
            case 2 : $tmp_count = $count % 10; break;
            case 3 : $tmp_count = $count % 100; break;
            case 4 : $tmp_count = $count % 1000; break;
            case 5 : $tmp_count = $count % 10000; break;
        }

        $suffix = \Yii::t('communications', '1#participant|in_array(n,[2,3,4])#participants|in_array(n,[0,5,6,7,8,9])#participants', [$tmp_count]);

        return $count . ' ' . $suffix;
    }



    public function getCountNewMessages(){
        $activity_mesages_models = (new ActivityMessagesModel())->findAll('copy_id = :copy_id AND data_id = :data_id', array(
            ':copy_id' => ExtensionCopyModel::MODULE_COMMUNICATIONS,
            ':data_id' => $this->communications_id,
        ));

        $not_view_count = 0;
        foreach ($activity_mesages_models as $activity_mesages_model){
            $mark_view_model = $activity_mesages_model->communicationsActivityMarkView;
            if($mark_view_model && $mark_view_model->user_id == WebUser::getUserId()){
                $not_view_count += 1;
            }
        }
        return $not_view_count;
    }


}
