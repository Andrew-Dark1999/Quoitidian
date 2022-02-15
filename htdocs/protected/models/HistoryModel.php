<?php

/**
* HistoryModel
* 
* @author Alex R.
*/


class HistoryModel extends ActiveRecord
{
    public $tableName = 'history';

    public $history_messages_group = 1;
    public $active = 1;

    private $_sep_group_params = null;
    private $_prepare_params = true; // false - перед сохранение из контейнера

    private $_loggin_responsible_only = false;

    private static $_same_time = false;
    private static $_same_time_date = null;

    private $user_create_is_null = false;

    // разрешает добавление доп. информации в связаные подчиненные таблицы
    private $_add_realte_history_data = true;

    /**
     *
     */
    public function __construct(){
        $this->_sep_group_params = '<-_MERGE_->'; //don`t delete if use group method
        parent::__construct();
    }



    /**
     * @param $same_time
     */
    public static function setSameTimeOnInsert($same_time){
        self::$_same_time = $same_time;
        if(!self::$_same_time){
            $_same_time_date = null;
        }
    }


    public function setPrepareParams($prepare_params){
        $this->_prepare_params = $prepare_params;
        return $this;
    }


    public function setUserCreateIsNull($user_create_is_null){
        $this->user_create_is_null = $user_create_is_null;
        return $this;
    }


    /**
     * логирует только для ответственных
     */
    public function setLogginResponsibleOnly($loggin_responsible_only){
        $this->_loggin_responsible_only = $loggin_responsible_only;
        return $this;
    }



    public function setAddRealteHistoryData($add_realte_history_data){
        $this->_add_realte_history_data = $add_realte_history_data;
        return $this;
    }


    /**
     * @param $same_time
     */
    public function updateSameTimeOnInsert(){
        if(self::$_same_time && !self::$_same_time_date){
            self::$_same_time_date = $this->getAttribute('date_create');
        }
    }



	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}



	public function rules(){
		return array(
			array('user_create, history_messages_index, copy_id, data_id', 'numerical', 'integerOnly'=>true),
			array('date_create, user_create, params', 'safe'),
		);
	}

    /**
     * @var null
     */
    public $historyMessages = null;

	public function relations(){
		return array(
            'extensionCopy' => array(self::BELONGS_TO, 'ExtensionCopyModel', 'copy_id'),
            'historyMarkView' => array(self::HAS_MANY, 'HistoryMarkViewModel', array('history_id' => 'history_id')),
            'processOperations' => array(self::HAS_ONE, '\Process\models\OperationsModel', array('copy_id' => 'copy_id'), 'on' => 'processOperations.card_id = t.data_id AND processOperations.element_name in ("task", "agreetment")'),
        );
	}


	public function attributeLabels(){
		return array();
	}

    public function scopes()
    {
        return array(
            'active' => array(
                'condition'=>'active = 1',
            )
        );
    }


    public function group() //scope group
    {
        Yii::app()->db->createCommand('SET @@group_concat_max_len = 1000000;')->execute();
        $this->getDbCriteria()->mergeWith(array(
            'select'=>
                '
                    MAX(history_id) as history_id,
                    MAX(t.date_create) as date_create,
                    t.user_create,
                    history_messages_index,
                    t.copy_id,
                    t.data_id,
                    t.active,
                    GROUP_CONCAT(t.params SEPARATOR "'.$this->_sep_group_params.'") as params
                ',
            'group' =>
                '
                    t.copy_id, t.data_id, t.history_messages_index,
		            t.date_create
                '
        ));
        return $this;
    }

    public function defaultScope()
    {
        return array(
            'order'=>'`t`.history_id desc'
        );
    }

    public function setScopeUserCreate($user_id){
        $this->getDbCriteria()->mergeWith(array(
            'condition' => 't.user_create=:user_create',
            'params' => array(':user_create' => $user_id),
        ));
        return $this;
    }


    public function prepareParams(){
        if(!empty($this->params)){
            $this->params = json_encode($this->params);
        }

        return $this;
    }



    public function decodeParams(){
        if(!empty($this->params)){
            $this->params = json_decode($this->params, true);
        }

        return $this;
    }


    /**
     * getParams
     */
    public function getParams(&$message_params){
        if(!$this->extensionCopy){
            $this->updateByPk($this->getPrimaryKey(), array('active' => 0));
            return false;
        }

        $message_params = array(
            '{data_id}' => $this->data_id,
            '{copy_id}' => $this->copy_id,
            '{module_title}' => $this->extensionCopy->title,
        );

        if(preg_match('~'.$this->_sep_group_params.'~', $this->params)){
            foreach(explode($this->_sep_group_params, $this->params) as $param){
                $object = json_decode($param, true);
                if(!$object){
                    continue;
                }
                foreach($object as $key => $value){
                    if(!isset($message_params[$key])) {
                        $message_params[$key] = $value;
                    } else {
                        if(is_string($message_params[$key])){
                            if($message_params[$key] != $value){
                                $message_params[$key] = array($message_params[$key], $value);
                            }
                        } else
                        if(is_array($message_params[$key])){
                           if(!in_array($value, $message_params[$key])){
                               $message_params[$key][] = $value;
                           }
                        }
                        if(is_array($message_params[$key])){
                           sort($message_params[$key]);
                        }
                    }

                }
            }
        } else {
            if(!empty($this->params)){
                $params = json_decode($this->params, true);
                $message_params = array_merge($message_params, $params);
            }
        }

        $user_model = UsersModel::model()->findByPk($message_params['{user_id}']);
        $message_params['{user_full_name}'] = (!empty($user_model) ? $user_model->getFullName() : '');
        if(!array_key_exists('{comment}', $message_params)) $message_params['{comment}'] = '';

        $this->getParentModuleEntity($message_params);


        return true;
    }



    private static $_cache_primary_pci = array();

    /**
     * @return array|bool|CActiveRecord|mixed|null
     */
    private function getParentPrimaryRelateModel(){
        if(array_key_exists($this->extensionCopy->copy_id, self::$_cache_primary_pci)){
            return self::$_cache_primary_pci[$this->extensionCopy->copy_id];
        }

        $pci = $this->extensionCopy->getParentPrimaryCopyId();
        if(!$pci) return false;

        $relate_tables = ModuleTablesModel::model()->find(array(
            'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_one"' ,
            'params' => array(
                ':copy_id' => $pci,
                ':relate_copy_id' => $this->extensionCopy->copy_id,
            )));

        self::$_cache_primary_pci[$this->extensionCopy->copy_id] = $relate_tables;

        return $relate_tables;
    }


    /**
     * @param $message_params
     */
    private function getParentModuleEntity(&$message_params){
        if(empty($this->data_id)) return;
        //if($this->extensionCopy->copy_id != \ExtensionCopyModel::MODULE_TASKS) return;

        $relate_tables = $this->getParentPrimaryRelateModel();
        if(empty($relate_tables)) return;

        $data_rt = \DataModel::getInstance()
                        ->setSelect($relate_tables['parent_field_name'])
                        ->setFrom('{{'.$relate_tables['table_name'].'}}')
                        ->setWhere($relate_tables['relate_field_name'] . ' = ' . $this->data_id)
                        ->findRow();

        if(empty($data_rt)) return;


        $data_m = \DataModel::getInstance()
                        ->setSelect('module_title')
                        ->setFrom(\ExtensionCopyModel::model()->findByPk($relate_tables['copy_id'])->getTableName())
                        ->setWhere($relate_tables['parent_field_name'] . ' = ' . $data_rt[$relate_tables['parent_field_name']])
                        ->findRow();
        $vars = array(
            'is_parent_entity' => true,
            'parent_entity' => array(
                'copy_id' => $relate_tables['copy_id'],
                'data_id' => $data_rt[$relate_tables['parent_field_name']],
                'module_data_title' => (!empty($data_m) ? $data_m['module_title'] : '<data nof found>'),
            )
        );

        $message_params['{parent_module_data}'] = $vars;
    }



    protected function beforeValidate(){
        if($this->_prepare_params == false) return true;

        $this->prepareParams();

        if($this->isNewRecord){
            if(self::$_same_time && self::$_same_time_date){
                $this->date_create = self::$_same_time_date;
            } else{
                $this->date_create = new CDbExpression('now()');
            }

            if($this->user_create_is_null == false){
                $this->user_create = WebUser::getUserId();
            }
        }

        return true;
    }





    /**
     * @return bool
     */
    public function afterValidate(){
        if($this->_prepare_params == false) return true;

        if($this->isNewRecord && $this->scenario == "insert") {
            switch ($this->getAttribute('history_messages_index')) {
                case HistoryMessagesModel::MT_FILE_DELETED:
                    if ($this->exists(
                        'copy_id=:copy_id AND data_id=:data_id AND history_messages_index=' . HistoryMessagesModel::MT_DELETED,
                        array(
                            ':copy_id' => $this->getAttribute('copy_id'),
                            ':data_id' => $this->getAttribute('data_id')
                        )
                    )
                    ) {
                        $this->setAttribute('active', '0');
                    }
                    break;
            }
        }

        return true;
    }



    /**
     *
     */
    protected function afterSave(){
        if($this->isNewRecord && $this->scenario == "insert" && $this->_add_realte_history_data) {
            $this->refresh();

            $participant = ParticipantModel::getParticipants($this->copy_id, $this->data_id, ParticipantModel::PARTICIPANT_UG_TYPE_USER, $this->_loggin_responsible_only);

            if(!empty($participant)) {
                foreach (array_unique(array_keys(CHtml::listData($participant, 'ug_id', 'data_id'))) as $user_id) {
                    if($this->copy_id!=ExtensionCopyModel::MODULE_COMMUNICATIONS) {
                        $history_mark = new HistoryMarkViewModel();
                        $history_mark->user_id = $user_id;
                        $history_mark->history_id = $this->history_id;

                        if (!$history_mark->save()) {
                            break;
                        }
                    }
                }
            }

            switch ($this->getAttribute('history_messages_index')) {
                case HistoryMessagesModel::MT_DELETED:
                    $this->setNotActive(HistoryMessagesModel::MT_DELETED);
                break;
                case HistoryMessagesModel::MT_FILE_DELETED:
                    $this->setNotActive(HistoryMessagesModel::MT_FILE_UPLOADED);
                break;
            }


            if($this->_prepare_params == false){
                $this->updateSameTimeOnInsert();
            };
        }

        return true;
    }


    /**
     * @param $type
     */
    private function setNotActive($type){

        $criteria = new CDbCriteria();
        $criteria->condition = 'copy_id=:copy_id AND data_id=:data_id AND active=1';
        $criteria->params = array(
            ':copy_id' => $this->getAttribute('copy_id'),
            ':data_id' => $this->getAttribute('data_id')
        );

        if($type == HistoryMessagesModel::MT_DELETED) {
            $histories = $this->findAll($criteria);
            if(!empty($histories)) {
                foreach ($histories as $history) {
                    if($this->getPrimaryKey() != $history->getPrimaryKey()) {
                       $history->updateByPk($history->getPrimaryKey(), array('active' => '0'));
                    }
                }
            }
        } else {

            $criteria->addInCondition('history_messages_index', array($type));
            $text = substr($this->getAttribute('params'), strrpos($this->getAttribute('params'), ','));
            $criteria->addSearchCondition('params', $text);

            $history = $this->find($criteria);
            if ($history) {
                $history->updateByPk($history->getPrimaryKey(), array('active' => '0'));
            }

        }

    }

}
