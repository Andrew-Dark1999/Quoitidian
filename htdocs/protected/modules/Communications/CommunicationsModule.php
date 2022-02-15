<?php


class CommunicationsModule extends \Module{

    protected $_destroy = false;
    public $clone = false;
    public $auto_table_name = false;
    public static $table_name = 'communications';

    public $user_params = false;
    protected $_page_interface_type = self::PAGE_IT_COMMUNICATIONS;



    public function __construct($id, $parent, $config=null){
        parent::__construct($id, $parent, $config);

        \Yii::import('application.models.EditViewModel');
        \Yii::import('application.models.EditViewActionModel');
        \Yii::import('application.models.ParticipantActionsModel');
        \Yii::import('application.extensions.ElementMaster.ParticipantItemListBulder');

        \Yii::import('Communications.models.*');
        \Yii::import('application.modules.Communications.extensions.*');

        (new CommunicationsSourceModel(\Communications\models\ServiceModel::SERVICE_NAME_EMAIL, null, WebUser::getUserId()));

        $this->user_params = (new CommunicationsServiceParamsModel())->getUserParams();
    }


    public function setModuleName(){
        $this->_moduleName = 'Communications';
    }



    public function setModuleVersion(){
        $this->_moduleVersion = '1.0';
    }


    public function setConstructorTitle(){
        $this->_constructor_title = Yii::t('base', 'New module');
    }


    public function getSourceList($only_active = true){
        return (new \Communications\models\SourceModel())->getSourceList();
    }

    public function getConstructorFields(){
        return array_merge($this->_constructor_fields, array('datetime_activity'));
    }


    /**
     * getProcessViewCardDataListAppendToSelect - добавление условия select при формировании запроса на выборку списка карточек
     */
    public function getProcessViewCardDataListAppendToSelect(){
        return '(select  max(date_create) from {{activity_messages}} where( copy_id = '.$this->extensionCopy->copy_id.' and data_id = {{communications.communications_id}})) as activity_last_date';
    }

    public function initListViewBtnActionList(){
        parent::initListViewBtnActionList();
        $buttons = $this->list_view_btn_actions;
        unset($buttons['copy']);
        $this->list_view_btn_actions = $buttons;

        return $this;
    }



}
