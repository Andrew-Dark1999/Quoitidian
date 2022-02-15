<?php
/**
 * @author Alex R.
 */

namespace Process\models;


class OperationBeginModel extends OperationChangeElementModel{

    const ELEMENT_PREVIOUS_PROCESS  = 'previous_process';
    const ELEMENT_START_ON_TIME     = 'start_on_time';                  //Запуск по времени (новое - Автозапуск)
    const ELEMENT_PERIODICITY       = 'periodicity';                    //При регулярном - периодичность

    const START_ON_TIME_DISABLED        = 'start_on_time_disabled';         // отключен
    const START_ON_TIME_ONE             = 'start_on_time_disposable_start'; // один
    const START_ON_TIME_DETERMINED      = 'start_on_time_determined';       // через определенное время
    const START_ON_TIME_REGULAR         = 'start_on_time_regular_start';    // регулярный
    const START_ON_BEFORE_TIME          = 'start_on_before_time';           // до указанного времени
    const START_ON_AFTER_TIME           = 'start_on_after_time';            // после указаного времени
    const START_ON_AFTER_CREATED_ENTITY = 'start_on_after_created_entity';  // после изменения создания модуля
    const START_ON_AFTER_CHANGED_ENTITY = 'start_on_after_changed_entity';  // после изменения сущности модуля

   // для ELEMENT_PERIODICITY
    const PERIODICITY_YEAR          = 'periodicity_year';
    const PERIODICITY_QUARTER       = 'periodicity_quarter';
    const PERIODICITY_MONTH         = 'periodicity_month';
    const PERIODICITY_WEEK          = 'periodicity_week';
    const PERIODICITY_DAY           = 'periodicity_day';

    const ELEMENT_DATE              = 'date';
    const ELEMENT_QUARTER           = 'quarter';
    const ELEMENT_DAY_IN_MONTH      = 'day_in_month';
    const ELEMENT_WEEK              = 'week';
    const ELEMENT_TIME              = 'time';
    const ELEMENT_SUB_TIME          = 'sub_time';
    const ELEMENT_HOUR              = 'hour';
    const ELEMENT_MINUTES           = 'minutes';
    const ELEMENT_DAYS              = 'days';

    const ELEMENT_LABEL_ADD_DATA    = 'label_add_date';



    protected static $_start_time_run = true;

    public static $_validate_element_count = 1;


    protected $_is_possibly_bo = true;


    protected function setTitle(){
        $this->_title = \Yii::t('ProcessModule.base', 'Begin');
    }



    public static function setStartTimeRun($start_time_run){
        self::$_start_time_run = $start_time_run;
    }




    public function validateElementsCountClear(){
        self::$_validate_element_count = 1;
        return $this;
    }


    public static function getParamsDataStartOnTime(){
        return array(
            self::START_ON_TIME_DISABLED => \Yii::t('ProcessModule.base', 'Disabled'),
            self::START_ON_TIME_ONE => \Yii::t('ProcessModule.base', 'Disposable start'),
            self::START_ON_TIME_REGULAR => \Yii::t('ProcessModule.base', 'Regular start'),
            self::START_ON_AFTER_CREATED_ENTITY => \Yii::t('ProcessModule.base', 'Start after data creation'),
            self::START_ON_AFTER_CHANGED_ENTITY=> \Yii::t('ProcessModule.base', 'Start after changing data'),
        );
    }




    public static function getParamsDataPeriodicity(){
        return array(
            self::PERIODICITY_YEAR => \Yii::t('ProcessModule.base', 'Every year'),
            self::PERIODICITY_QUARTER => \Yii::t('ProcessModule.base', 'Every quarter'),
            self::PERIODICITY_MONTH => \Yii::t('ProcessModule.base', 'Every month'),
            self::PERIODICITY_WEEK => \Yii::t('ProcessModule.base', 'Every week'),
            self::PERIODICITY_DAY => \Yii::t('ProcessModule.base', 'Every day'),
        );
    }





    public function getElementValue($schema, $type){
        if(empty($schema)) return;
        foreach($schema as $element){
            if($element['type'] == $type)
                return $element['value'];
        }
    }




    public function getBuildedParamsContent($schema = null){
        if(empty($this->_operations_model)) return;

        if($schema === null){
            if(empty($this->_operations_model)) return;
            $schema = $this->_operations_model->getSchema();
        }

        if(empty($schema)) return;

        $content = $this->getBuildedParamsContentElementTimers($schema);

        return $content;
    }



    public function getBuildedParamsContentElementTimers($schema = null){
        $schema = $this->addDefaultDataForOperatorSchema($schema);

        $this->prepareBaseEntities($schema);
        $this->refreshSchema($schema);

        $start_to_time = self::getParentElement($schema, self::ELEMENT_START_ON_TIME);
        if(empty($start_to_time['elements'])) return;

        $elements = $start_to_time['elements'];


        if($this->_validate_elements){
            $this->validate();
        }

        $content = '';
        $this->validateElementsCountClear();

        foreach($elements as $element_schema){
            $content.= $this->getElementHtml($element_schema);
            if(in_array($element_schema['type'], $this->getDinamicElementList())){
                OperationBeginModel::$_validate_element_count++;
            }
        }

        $this->validateElementsCountClear();

        return $content;
    }





    /**
     * changeParamsContent
     * @param $action
     * @param $params
     * @return array
     */
    public function changeParamsContent($action, $params){
        $result = null;

        switch($action){
            case 'changed_object_name':
            case 'changed_relate_module':
            case 'changed_field_name':
                $result = $this->getBuildedParamsContent($params['schema_operation']);

                break;
            case 'changed_label_add_value':
                $result = $this->cpLabelAddValue($params);
                break;

        }

        return $result;
    }






    public function getElementObjectNameTitle(){
        return \Yii::t('ProcessModule.base', 'Module name');
    }



    /**
     * getObjectNameList - возвращает список сущностей объекта-параметра для поля
     * @return array
     */
    public function getObjectNameList($unique_index){
        $extension_copy_list = \ExtensionCopyModel::getModulesList();

        if($extension_copy_list == false){
            return;
        }

        foreach($extension_copy_list as $copy_id => $title){
            $result['{"type":"' . OperationDataRecordModel::ELEMENT_RECORD_NAME_TYPE_RO . '","copy_id":"'.$copy_id .'"}'] = $title;
        }


        if(!empty($result)){
            $result = array(null => '') + $result;
        } else {
            $result = array(null => '');
        }

        return $result;
    }





    /**
     * getStartDate - возвращает стартовую дату для START_ON_BEFORE_TIME и START_ON_AFTER_TIME для проверки выполнения
     */
    public function getStartDate(){
        if(empty($this->_operations_model)) return;

        $schema = $this->_operations_model->getSchema();

        if(empty($schema)) return;

        $this->prepareBaseEntities($schema);
        $this->refreshSchema($schema);

        $extension_copy = $this->getExtensionCopy();
        $edit_model = null;

        if(!empty($this->_active_card_id) && !empty($extension_copy) && !empty($this->_active_field_name)){
            $edit_model = $this->getCardData();
        }

        if($edit_model == false){
            return;
        }

        $edit_date = $edit_model->{$this->_active_field_name};
        if($edit_date == '') $edit_date = null;

        return $edit_date;
    }





    /**
     * getBaseParamsElementList - параметры для вызрузки в JS объект
     */
    public function getBaseParamsElementList(){
        $result = array();

        $elements = array(
                        self::ELEMENT_PERIODICITY,
                        self::ELEMENT_DATE,
                        self::ELEMENT_QUARTER,
                        self::ELEMENT_DAY_IN_MONTH,
                        self::ELEMENT_WEEK,
                        self::ELEMENT_TIME,
                        self::ELEMENT_HOUR,
                        self::ELEMENT_MINUTES,
                        self::ELEMENT_DAYS,
                        self::ELEMENT_LABEL_ADD_DATA,
                    );

        if($this instanceof OperationBeginModel){
            $elements = array_merge($elements, array(self::ELEMENT_OBJECT_NAME, self::ELEMENT_FIELD_NAME, self::ELEMENT_VALUE_SCALAR, self::ELEMENT_LABEL_ADD_VALUE));
        }

        if($this instanceof OperationTimerModel){
            $elements = array_merge($elements, array(self::ELEMENT_OBJECT_NAME, self::ELEMENT_RELATE_MODULE, self::ELEMENT_FIELD_NAME));
        }

        foreach($elements as $element_type){
            $element_schema = $this->getDefaultElementSchema($element_type);
            $result[$element_type] = $this->getElementHtml($element_schema);
        }

        return $result;
    }



    /**
     * getDinamicElementList
     */
    public function getDinamicElementList(){
        return array(
            self::ELEMENT_DATE,
            self::ELEMENT_QUARTER,
            self::ELEMENT_DAY_IN_MONTH,
            self::ELEMENT_WEEK,
            self::ELEMENT_TIME,
            self::ELEMENT_HOUR,
            self::ELEMENT_MINUTES,
            self::ELEMENT_DAYS,
        );
    }





    private function getDefaultElementSchema($element_type){
        $schema = array(
            'title' => '',
            'type' => $element_type,
            'value' => null,
        );

        switch($element_type){
            case self::ELEMENT_PERIODICITY :
                $schema['title'] = \Yii::t('ProcessModule.base', 'Periodicity');
                break;
            case self::ELEMENT_DATE :
                $schema['title'] = \Yii::t('ProcessModule.base', 'Start date');
                break;
            case self::ELEMENT_QUARTER :
                $schema['title'] = \Yii::t('ProcessModule.base', 'Number quarter');
                break;
            case self::ELEMENT_DAY_IN_MONTH :
                $schema['title'] = \Yii::t('ProcessModule.base', 'Day in month');
                break;
            case self::ELEMENT_WEEK :
                $schema['title'] = \Yii::t('ProcessModule.base', 'Week name');
                break;
            case self::ELEMENT_TIME :
                $schema['title'] = \Yii::t('ProcessModule.base', 'Time');
                break;
            case self::ELEMENT_HOUR :
                $schema['title'] = \Yii::t('ProcessModule.base', 'Hours');
                break;
            case self::ELEMENT_MINUTES :
                $schema['title'] = \Yii::t('ProcessModule.base', 'Minutes');
                break;
            case self::ELEMENT_DAYS :
                $schema['title'] = \Yii::t('ProcessModule.base', 'Days');
                break;
            case self::ELEMENT_LABEL_ADD_DATA :
                $schema['title'] = \Yii::t('ProcessModule.base', 'Add date start');
                break;
        }

        return $schema;
    }





    /**
     * checkExecution - проверка выполнения, установка статуса
     * @return $this
     */
    public function checkExecution(){
        $process_model = ProcessModel::getInstance();
        if($process_model->getMode() == ProcessModel::MODE_CONSTRUCTOR) return $this;

        $b_status = $process_model->getBStatus();

        //B_STATUS_STOPED
        if($b_status == ProcessModel::B_STATUS_STOPED) return $this;

        //B_STATUS_IN_WORK
        if($b_status == ProcessModel::B_STATUS_IN_WORK){
            if($this->checkIsResponsibleRole()){
                return $this;
            }
            if($this->checkIsSetResponsibleUser() == false){
                return $this;
            }

            // запуск оператора - простой...
            if($this->getStatus() == OperationsModel::STATUS_UNACTIVE){
                $this->setStatus(OperationsModel::STATUS_ACTIVE);
            }

            // запуск оператора - простой...
            if($this->getStatus() == OperationsModel::STATUS_ACTIVE){
                $this->setStatus(OperationsModel::STATUS_DONE);

                if(self::$_start_time_run){
                    (new StartTimeModel())
                        ->setOperationsModel($this->_operations_model)
                        ->startSchedule();
                }
            }

            //B_STATUS_TERMINATED
        } elseif($b_status == ProcessModel::B_STATUS_TERMINATED){
        }


        return $this;
    }








    public function validate($schema = null){
        if($schema === null){
            $schema = $this->_operations_model->getSchema();
            $this->validateElementsCountClear();
        }

        foreach($schema as $elements){
            if(isset($elements['type'])){
                switch($elements['type']){
                    case self::ELEMENT_START_ON_TIME:
                        $sot_list = array(
                            self::START_ON_TIME_ONE,
                            self::START_ON_TIME_DETERMINED,
                            self::START_ON_TIME_REGULAR,
                            self::START_ON_BEFORE_TIME,
                            self::START_ON_AFTER_TIME,
                            self::START_ON_AFTER_CREATED_ENTITY,
                            self::START_ON_AFTER_CHANGED_ENTITY,
                        );
                        if(in_array($elements['value'], $sot_list) && !empty($elements['elements'])){
                            $this->validate($elements['elements']);
                        }
                        break;

                    case self::ELEMENT_OBJECT_NAME:
                        if(empty($elements['value']) || empty($elements['value'][0]) || empty($elements['value'][1])){
                            $this->addValidateMessage($elements['type'], \Yii::t('messages', 'You must fill field'));
                        }
                        break;

                    case self::ELEMENT_FIELD_NAME:
                        if(empty($elements['value']) || empty($elements['value'][0]) || empty($elements['value'][1])){
                            $this->addValidateMessage($elements['type'], \Yii::t('messages', 'You must fill field'));
                        }
                        break;

                    case self::ELEMENT_DATE:
                    case self::ELEMENT_QUARTER:
                    case self::ELEMENT_DAY_IN_MONTH:
                    case self::ELEMENT_WEEK:
                        if(empty($elements['value']) || empty($elements['value'][0]) || empty($elements['value'][1])){
                            $this->addValidateMessage($elements['type'] . self::$_validate_element_count, \Yii::t('messages', 'You must fill field'));
                        }
                        self::$_validate_element_count++;
                        break;

                    case self::ELEMENT_TIME:
                    case self::ELEMENT_HOUR:
                    case self::ELEMENT_MINUTES:
                    case self::ELEMENT_DAYS:
                        if(empty($elements['value']) && $elements['value'] !== '0'){
                            $this->addValidateMessage($elements['type'] . self::$_validate_element_count, \Yii::t('messages', 'You must fill field'));
                        }
                        self::$_validate_element_count++;
                        break;
                }
            }
        }

        return $this;
    }





    protected function inRecordNameList($unique_index, $value){
        return true;
    }



    /**
     * validateBeforeSave - проверка перед сохранение схемы оператора
     */
    public function actionValidateBeforeSave(){
        $this
            ->validateElementsCountClear()
            ->validate();

        return !$this->getBeError();
    }



    private function getActionNameByAfterEntity($start_on_after_entity){
        switch($start_on_after_entity){
            case self::START_ON_AFTER_CREATED_ENTITY:
                return ProcessAutostartByEntityModel::ACTION_NAME_CREATE;
            case self::START_ON_AFTER_CHANGED_ENTITY:
                return ProcessAutostartByEntityModel::ACTION_NAME_CHANGE;
        }
    }




    /**
     * checkConditionEditModel - проверка условия для сущности
     */
    public function checkConditionForEntity($edit_model){
        $schema = $this->_operations_model->getSchema();
        $this->prepareBaseEntities($schema);
        $this->refreshSchema($schema);

        foreach($schema as $elements){
            if(!array_key_exists('elements', $elements)){
                continue;
            }
            foreach($elements['elements'] as $element){
                if(!in_array($element['type'], array(
                    self::ELEMENT_VALUE_SCALAR,
                    self::ELEMENT_VALUE_SELECT,
                    self::ELEMENT_VALUE_DATETIME,
                    self::ELEMENT_VALUE_RELATE))
                ){
                    continue;
                }

                $status = $this->checkDirect($element, $edit_model);

                if($status){
                    return true;
                }
            }
        }

        return false;
    }




    /**
     * setProcessRelatedObject - сохраненение связи с связанным обьектом исходя из связанного модуля по параметру автозапуска процесса
     * @param $relate_data_id
     * @return bool
     */
    public static function setProcessRelatedObject($relate_data_id){
        if(ProcessModel::getInstance()->related_module == false){
            return false;
        }

        $vars = array(
            'action' => BpmParamsModel::ACTION_UPDATE,
            'process_id' => ProcessModel::getInstance()->process_id,
            'objects' => array(
                BpmParamsModel::OBJECT_BINDING_OBJECT => array(
                    'attributes' => array(
                        'copy_id' => ProcessModel::getInstance()->related_module,
                        'data_id' => $relate_data_id,
                    ),
                ),
            ),
        );

        $modules_table_model = (new \ModuleTablesModel())->getRelateModel(
            ProcessModel::getInstance()->related_module,
            \ExtensionCopyModel::MODULE_PROCESS,
            \ModuleTablesModel::TYPE_RELATE_MODULE_ONE
        );

        $is = (new BindingObjectModel())
            ->setVars($vars+$vars['objects']['binding_object'])
            ->isSetRelateByDataId();

        if($modules_table_model && $is){
            return false;
        }

        $status = (new BpmParamsModel())
                            ->setVars($vars)
                            ->setRunIfProcessRunning(true)
                            ->validate()
                            ->run()
                            ->getStatus();

        return $status;
    }





    public function getDateEnding(){
        return ProcessModel::getInstance()->date_create;
    }







    /*************************************
     * ACTIONS
     *************************************/






    /**
     * actionAfterSave -  - вызывается после сохранение схемы оператора
     */
    public function actionAfterSave(){
        $schema = $this->_operations_model->getSchema(false);
        $start_on_time = $this->getElementValue($schema, self::ELEMENT_START_ON_TIME);

        switch($start_on_time){
            //1
            case self::START_ON_AFTER_CREATED_ENTITY:
            case self::START_ON_AFTER_CHANGED_ENTITY:
                $this->actionAfterSaveStartOnAfterEntity();
                break;
            //2
            default:
                $this->actionAfterSaveDefault();
        }

        return $this;
    }




    /**
     * actionAfterSave -  - вызывается после сохранение схемы оператора - по умолчанию
     */
    private function actionAfterSaveDefault(){
        (new StartTimeModel())
            ->setOperationsModel($this->_operations_model)
            ->updateSchedule();

        (new ProcessAutostartByEntityModel())->deleteAll('operations_id = ' . $this->_operations_model->getPrimaryKey());
    }




    /**
     * actionAfterSave -  вызывается после сохранение схемы оператора,
     *                    если Автозапуск = START_ON_AFTER_CREATED_ENTITY или START_ON_AFTER_CHANGED_ENTITY
     */
    private function actionAfterSaveStartOnAfterEntity(){
        $schema = $this->_operations_model->getSchema(false);

        $on_schema = SchemaModel::getInstance()->getElementsFromSchema($schema, array('only_first' => true, 'type' => self::ELEMENT_OBJECT_NAME));
        if(empty($on_schema['value'])){
            return;
        }

        $on_schema = json_decode($on_schema['value'], true);
        if(empty($on_schema['copy_id'])){
            return;
        }

        $copy_id = $on_schema['copy_id'];

        $this->setProcessAutostartByEntity($copy_id, $schema);
        $this->setProcessRelatedModule($copy_id);
    }




    private function setProcessAutostartByEntity($copy_id, $schema){
        $autostart_model = ProcessAutostartByEntityModel::model()->find('operations_id=' . $this->_operations_model->getPrimaryKey());
        if(!$autostart_model){
            $autostart_model = new ProcessAutostartByEntityModel();
        }

        $autostart_model->setAttributes(array(
            'operations_id' => $this->_operations_model->getPrimaryKey(),
            'copy_id' => $copy_id,
            'action_name' => $this->getActionNameByAfterEntity($this->getElementValue($schema, self::ELEMENT_START_ON_TIME)),
        ));
        $autostart_model->save();
    }



    private function setProcessRelatedModule($copy_id){
        $process_model = ProcessModel::getInstance();

        if($process_model->related_module == $copy_id){
            return;
        }

        $process_model->related_module = $copy_id;
        $process_model->save();
    }




    /**
     * actionCloneDataBeforeSave
     */
    public function actionPrepareDataForNewOperation(){
        $schema = $this->_operations_model->getSchema();

        \Process\extensions\ElementMaster\Schema::getInstance()->unactiveSheduledInOperationBegin($schema);

        $this->_operations_model->setSchema($schema);

        return $this;
    }










}
