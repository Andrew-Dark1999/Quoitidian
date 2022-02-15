<?php
/**
 * OperationsModel widget
 * @author Alex R.
 */

namespace Process\models;

class OperationsModel extends \ActiveRecord{


    const ELEMENT_BEGIN         = 'begin';          //Начало
    const ELEMENT_END           = 'end';            //Конец
    const ELEMENT_CONDITION     = 'condition';      //Условие
    const ELEMENT_AND           = 'and';            //И
    const ELEMENT_TIMER         = 'timer';          //Таймер
    const ELEMENT_TASK          = 'task';           //Задача
    const ELEMENT_AGREETMENT    = 'agreetment';     //Согласование
    const ELEMENT_NOTIFICATION  = 'notification';   //Оповещение
    const ELEMENT_DATA_RECORD   = 'data_record';    //Запись данных
    const ELEMENT_SCENARIO      = 'scenario';       //Сценарий

    const STATUS_ACTIVE         = 'active';
    const STATUS_UNACTIVE       = 'unactive';
    const STATUS_PAUSE          = 'pause';          // используется для задач
    const STATUS_DONE           = 'done';

    const MODE_CONSTRUCTOR      = 'constructor';
    const MODE_RUN              = 'run';
    const MODE_RUN_BLOCKED      = 'run_blocked';


    private $_mode = self::MODE_CONSTRUCTOR;

    public $status = self::STATUS_UNACTIVE;


    public static function model($className=__CLASS__){
        return parent::model($className);
    }


    public function tableName(){
        return '{{process_operations}}';
    }

    public function rules(){
        return array(
            array('process_id, unique_index', 'required'),
            array('process_id, copy_id, card_id', 'numerical', 'integerOnly'=>true),
            array('unique_index, element_name, status', 'length', 'max'=>255),
            array('schema', 'length', 'max'=>65536),

        );
    }




    public function relations(){
        return array(
            'process' => array(self::BELONGS_TO, '\Process\models\ProcessModel', 'process_id'),
        );
    }



    public function setStatus($status){
        $this->status = $status;
        return $this;
    }



    public function getStatus(){
        return $this->status;
    }



    public function saveStatus(){
        $this->status = $this->getStatus();
        $this->save();
        $this->refresh();
    }


    /**
     * getArrowStatus - Возвращает статус стрелки
     */
    public function getArrowStatus($index){
        if(ProcessModel::getInstance()->getBStatus() == ProcessModel::B_STATUS_TERMINATED){
            return ArrowModel::STATUS_UNACTIVE;
        }

        switch($this->element_name){
            case self::ELEMENT_CONDITION :
                $status = OperationConditionModel::getInstance()
                                ->setOperationsModel($this)
                                ->getArrowStatus($index);
                break;

            default :
                $status = ArrowModel::STATUS_UNACTIVE;
                if($this->getStatus() == self::STATUS_DONE || $this->getStatus() == self::STATUS_PAUSE){
                    $status = ArrowModel::STATUS_ACTIVE;
                }
        }


        return $status;
    }



    public function setMode($mode){
        $this->_mode = $mode;
        return $this;
    }




    public function getMode($refresh_mode = false){
        if($refresh_mode) $this->initMode();

        return $this->_mode;
    }


    /**
     * Установка режима виполнения оператора. Используєтся для управления элементами отображения
     */
    protected function initMode(){
        $process_model = ProcessModel::getInstance();

        switch($process_model->getBStatus()){
            //B_STATUS_STOPED
            case ProcessModel::B_STATUS_STOPED :
                if($this->getStatus() == self::STATUS_UNACTIVE){
                    $this->_mode = self::MODE_CONSTRUCTOR;
                } elseif($this->getStatus() == self::STATUS_ACTIVE){
                    $this->_mode = self::MODE_RUN_BLOCKED;
                } elseif($this->getStatus() == self::STATUS_DONE){
                    $this->_mode = self::MODE_RUN_BLOCKED;
                }
                break;
            //B_STATUS_IN_WORK
            case ProcessModel::B_STATUS_IN_WORK :
                if($this->getStatus() == self::STATUS_UNACTIVE){
                    $this->_mode = self::MODE_CONSTRUCTOR;
                } elseif($this->getStatus() == self::STATUS_ACTIVE){
                    $this->_mode = self::MODE_RUN;
                } elseif($this->getStatus() == self::STATUS_DONE){
                    $this->_mode = self::MODE_RUN_BLOCKED;
                }
                break;
            //B_STATUS_TERMINATED
            case ProcessModel::B_STATUS_TERMINATED :
                $this->_mode = self::MODE_RUN_BLOCKED;
                break;
            default :
                $this->_mode = self::MODE_CONSTRUCTOR;
        }

        return $this;
    }


    public static function getOperationsList(){
        return array(
            self::ELEMENT_BEGIN,
            self::ELEMENT_END,
            self::ELEMENT_CONDITION,
            self::ELEMENT_AND,
            self::ELEMENT_TIMER,
            self::ELEMENT_TASK,
            self::ELEMENT_AGREETMENT,
            self::ELEMENT_NOTIFICATION,
            self::ELEMENT_DATA_RECORD,
            self::ELEMENT_SCENARIO,
        );
    }




    public static function getOperationClassName($element_name){
        switch($element_name){
            case self::ELEMENT_BEGIN :      return '\Process\models\OperationBeginModel';
            case self::ELEMENT_END :        return '\Process\models\OperationEndModel';
            case self::ELEMENT_CONDITION :  return '\Process\models\OperationConditionModel';
            case self::ELEMENT_AND :        return '\Process\models\OperationAndModel';
            case self::ELEMENT_TIMER :      return '\Process\models\OperationTimerModel';
            case self::ELEMENT_TASK :       return '\Process\models\OperationTaskModel';
            case self::ELEMENT_AGREETMENT : return '\Process\models\OperationAgreetmentModel';
            case self::ELEMENT_NOTIFICATION:return '\Process\models\OperationNotificationFactoryModel';
            case self::ELEMENT_DATA_RECORD: return '\Process\models\OperationDataRecordModel';
            case self::ELEMENT_SCENARIO :   return '\Process\models\OperationScenarioModel';
        }
    }





    public function getOperationModel(){
        $class_name = self::getOperationClassName($this->element_name);
        $operations_model = $class_name::getInstance();
        $operations_model->setOperationsModel($this);

        return $operations_model;
    }



    /**
     * getChildrenModel - возвращает производную модель класса оператора
     * @param $element_name
     * @return mixed
     */
    public static function getChildrenModel($element_name){
        $class = self::getOperationClassName($element_name);

        return $class::getInstance();
    }





    /**
     * getChildrenModel - возвращает производную модель класса оператора
     * @param $element_name
     * @return mixed
     */
    public static function getChildrenModelByOperationsId($operations_id){
        $operations_model = static::model()->findByPk($operations_id);
        if($operations_model == false){
            return;
        }

        $model = $operations_model->getOperationModel();

        return $model;
    }




    /**
     * getChildrenModel - возвращает производную модель класса оператора
     * @param $element_name
     * @return mixed
     */
    public static function getChildrenModelByUniqueIndex($unique_index, $process_id = null){
        if($process_id === null){
            $process_id = ProcessModel::getInstance()->process_id;
        }

        if($process_id == false && $unique_index == false){
            return;
        }

        $operations_model = static::model()->find(array(
            'condition' => 'process_id=:process_id AND unique_index=:unique_index',
            'params' => array(
                ':process_id' => $process_id,
                ':unique_index' => $unique_index,
            ),
        ));

        if($operations_model == false){
            return;
        }

        $model = $operations_model->getOperationModel();

        return $model;
    }




    public static function isSetOperator($operator_name){
        $result = false;
        if(empty($operator_name)) return false;
        if(in_array($operator_name, static::getOperationsList()))
            $result = true;

        return $result;
    }





    /**
     * Возвращает модель оператора
     */
    public static function findByParams($process_id, $unique_index){
        $model = static::model()->find(array(
            'condition' => 'process_id=:process_id AND unique_index=:unique_index',
            'params' => array(
                ':process_id' => $process_id,
                ':unique_index' => $unique_index,
            ),

        ));

        return $model;
    }





    /**
     * Возвращает сприсок верстки операторов
     */
    public static function getElements($view_type, $element_name, $delete_line_feeds = true){
        $result = array();
        if($element_name == 'all'){
            foreach(self::getOperationsList() as $element_name){
                $html = self::getElementHtml($view_type, $element_name);
                if($delete_line_feeds){
                    $html = \Helper::deleteLinefeeds($html);
                }
                $result[$element_name] = $html;
            }
        } else {
            $html = self::getElementHtml($view_type, $element_name);
            if($delete_line_feeds){
                $html = \Helper::deleteLinefeeds($html);
            }
            $result[$element_name] = $html;
        }

        return $result;
    }




    /**
     * Возвращает верстку оператора
     */
    private static function getElementHtml($view_type, $element_name){
        return \Yii::app()->controller->widget('\Process\extensions\ElementMaster\BPM\Operations\Operations',
            array(
                'view_type' => $view_type,
                'element_name' => $element_name,
            ),
            true);
    }






    public function setSchema($schema, $json_encode = true){
        if($json_encode){
            $schema = json_encode($schema);
        }
        $this->schema = $schema;
        return $this;
    }




    public function getSchema($only_from_db = false){
        if(!empty($this->schema)){
            if($only_from_db){
                $schema = json_decode($this->schema, true);
            } else {
                $schema = self::getChildrenModel($this->element_name)
                                    ->setOperationsModel($this)
                                    ->actionGetSchemaPrepared();
            }
        } else {
            $schema = $this->getSchemaDefault($this->element_name);
        }

        return $schema;
    }



    public function getSchemaDefault($element_name){
        return \Process\extensions\ElementMaster\Schema::getInstance()->getDefaultSchemaOperation($element_name);
    }


    /**
     * getParamsHtml - формирует и возвращает html страницу параметров оператора
     */
    public function getParamsHtml(){
        $data = $this->getOperationParamsData();

        switch($this->element_name){
            case self::ELEMENT_DATA_RECORD:
                if($this->getMode() != self::MODE_CONSTRUCTOR){
                    /*
                    if(OperationDataRecordModel::$_none_copy_id){
                        return \Yii::app()->controller->renderPartial('/dialogs/bpm/operation-params/' . $this->element_name, $data, true);
                    }
                    */
                    return \Yii::app()->controller->renderPartial('/dialogs/bpm/operation-run/' . $this->element_name, $data, true);
                } else {
                    return \Yii::app()->controller->renderPartial('/dialogs/bpm/operation-params/' . $this->element_name, $data, true);
                }
                break;

            default:
                return \Yii::app()->controller->renderPartial('/dialogs/bpm/operation-params/' . $this->element_name, $data, true);

        }

    }





    /**
     * getOperationParamsData - возвращает данные для формирования страницы
     */
    private function getOperationParamsData(){
        $this->initMode();


        $operation_model = self::getChildrenModel($this->element_name)
            ->setOperationsModel($this)
            ->setStatus($this->getStatus());

        $result = array(
            'operations_model' => $operation_model,
            'operation_model' => $operation_model,
            'content' => null,
            'vars' => null,
            'js_settings' => array(
                'unique_index' => $this->unique_index,
                'status' => $this->getStatus())
        );



        // content
        switch($this->element_name){
            case self::ELEMENT_BEGIN :
            case self::ELEMENT_END :
            case self::ELEMENT_AND :
            case self::ELEMENT_CONDITION :
            case self::ELEMENT_DATA_RECORD :
            case self::ELEMENT_TIMER :
            case self::ELEMENT_NOTIFICATION :
            case self::ELEMENT_SCENARIO :
                $result['content'] = $operation_model->getBuildedParamsContent();
                break;
        }

        //$js_settings - параметры для вызрузки в JS объект
        switch($this->element_name){
            case self::ELEMENT_BEGIN :
            case self::ELEMENT_TIMER :
                $result['js_settings']['elements'] = $operation_model->getBaseParamsElementList();
                break;
        }

        // vars - Другие параметры
        switch($this->element_name){
            case self::ELEMENT_TASK :
            case self::ELEMENT_AGREETMENT :
                $result['vars']['edit_view'] = $operation_model->getEditViewDataForShow();
                break;

            case self::ELEMENT_DATA_RECORD :
                $result['vars']['edit_view'] = $operation_model->getEditViewDataForShow();
                $result['vars']['user_message'] = $operation_model->getUserMessage();
                break;

            case self::ELEMENT_NOTIFICATION :
                $result['vars'] = $operation_model->getOtherVars();
                break;

        }

        return $result;
    }






    /**
     * getNewOperationModel
     */
    private function getNewOperationModel($element_name, $clone, $process_id = null, $unique_index = null){
        // clone schema
        if($clone){
            $operations_model = OperationsModel::findByParams($process_id, $unique_index);
            $operations_model->setIsNewRecord(true);

            $operations_model = self::getChildrenModel($element_name)
                                        ->setOperationsModel($operations_model)
                                        ->actionCloneDataBeforeSave()
                                        ->actionPrepareDataForNewOperation()
                                        ->getOperationsModel();

        // new default schema
        } else {
            $schema = \Process\extensions\ElementMaster\Schema::getInstance()->getDefaultSchemaOperation($element_name);

            $operations_model = OperationsModel::model();
            $operations_model->element_name = $element_name;
            $operations_model->setSchema($schema);

            $operations_model = self::getChildrenModel($element_name)
                                        ->setOperationsModel($operations_model)
                                        ->actionPrepareDataForNewOperation()
                                        ->getOperationsModel();
        }


        return $operations_model;
    }





    /**
     * saveNewOperations - Добавляет в БД новые операторы, или клонирует старые
     * @param $process_id_old
     * @param $process_id_new
     * @param $schema_process
     * @param bool $clone
     */
    public function saveNewOperations($process_id_old, $process_id_new, $schema_process, $clone = false, $set_operation_status_default = true){
        $operations = \Process\extensions\ElementMaster\Schema::getInstance()->getOperations($schema_process);
        if(empty($operations)) return;

        $element_names = array();

        foreach($operations as $operation){
            if(empty($operation['unique_index'])) continue;

            // возвращает модель нового оператора. Может проводиться доп. обработка схемы..
            $operations_model = $this->getNewOperationModel($operation['name'], $clone, $process_id_old, $operation['unique_index']);

            $operation_status = self::STATUS_UNACTIVE;
            if($set_operation_status_default == false){
                $operation_status = $operations_model->status;
            }


            $operation_schema = $operations_model->getSchema();

            $operations_model_new = new OperationsModel();
            $operations_model_new->setAttributes(array(
                                    'process_id' => $process_id_new,
                                    'unique_index' => $operation['unique_index'],
                                    'element_name' => $operation['name'],
                                    'schema' => json_encode($operation_schema),
                                    'copy_id' => $operations_model->copy_id,
                                    'card_id' => $operations_model->card_id,
                                    'status' => $operation_status,
                                ));

            // сохранием
            $operations_model_new->save();

            $element_names[] = $operations_model_new->element_name;
        }

        // обработка новых операторов после записи в новом операторе
        if($clone == true){
            $vars = array(
                'process_id_old' => $process_id_old,
                'process_id_new' => $process_id_new,
            );

            foreach(array_unique($element_names) as $element_name){
                self::getChildrenModel($element_name)
                               ->actionCloneDataAfterSave($vars);
            }
        }
    }







    /**
     * updateFromProcessSchema - дополнительная обработка схемы
     */
    public function updateFromProcessSchema($process_id = null, $schema_process = null){
        if($process_id === null){
            $process_id = ProcessModel::getInstance()->process_id;
        }
        if($schema_process === null){
            $schema_process = ProcessModel::getInstance()->getSchema();
        }

        // 1. удаляем из базы удаленные операторы
        $this->deleteOperations($process_id, $schema_process, true);

        // 2. другие операции: установка удаленного ответственного, добавление в БД нового оператора
        $operations = \Process\extensions\ElementMaster\Schema::getInstance()->getOperations($schema_process);
        if(empty($operations)){
            return;
        }

        //список ответвенных всех операторов
        $operation_responsible_list = \Process\extensions\ElementMaster\Schema::getInstance()->getOperationResponsibleList($schema_process);

        foreach($operations as $operation){
            $participant_vars = array(
                'to' => array(
                    'ug_id' => $operation_responsible_list[$operation['unique_index']]['ug_id'],
                    'ug_type' => $operation_responsible_list[$operation['unique_index']]['ug_type'],
                    'flag' => $operation_responsible_list[$operation['unique_index']]['flag'],
                ),
            );

            //update participant - обновление ответственного
            $this->updateParticipantInOperation($process_id, $operation, $participant_vars);

            //add new operations
            $vars = array(
                'participant' => array(
                    'ug_id' => $operation_responsible_list[$operation['unique_index']]['ug_id'],
                    'ug_type' => $operation_responsible_list[$operation['unique_index']]['ug_type'],
                    'flag' => $operation_responsible_list[$operation['unique_index']]['flag'],
                )
            );
            $this->addNewOperationByDefault($process_id, $operation, $vars);
        }
    }



    /**
     * удаляет из базы удаленные операторы
     * @param integer $process_id
     * @param array $schema
     */
    private function deleteOperations($process_id, $schema){
        $operation_ui_list = \Process\extensions\ElementMaster\Schema::getInstance()->getOperationsUniqueIndex($schema);
        if(empty($operation_ui_list)) return;

        foreach($operation_ui_list as &$item){
            $item = '"' . $item . '"';
        }
        unset($item);

        $model = static::model();
        $model->deleteAll(array(
            'condition' => 'process_id=:process_id AND unique_index not in (' . implode(',', $operation_ui_list) . ')',
            'params' => array(
                ':process_id' => $process_id,
            ),
        ));

    }




    /**
     * updateParticipantInOperation - Обновляет ответсвенного в операторе
     * @param $process_id
     * @param $schema_process
     */
    public function updateParticipantInOperation($process_id, $operation, $paticipant_vars){
        if(!in_array($operation['name'], array(self::ELEMENT_TASK, self::ELEMENT_AGREETMENT))) return;
        if(empty($operation['unique_index'])) return;

        $operations_model = OperationsModel::getChildrenModelByUniqueIndex($operation['unique_index'], $process_id);

        if($operations_model == false){
            return;
        }

        $status = ParticipantModel::updateOperationsParticipant(
                                $operations_model->getRelateCopyId(),
                                $operations_model->getIdCardFromSchema(),
                                $paticipant_vars
                            );

        // history
        if($status && in_array($operations_model->getCardBStatus(), [OperationTaskBaseModel::B_STATUS_IN_WORK, OperationTaskBaseModel::B_STATUS_STOPED])){
            $operations_model->makeHistory($operations_model->getIdCardFromSchema(), true, [\HistoryMessagesModel::MT_RESPONSIBLE_APPOINTED]);
        }
    }





    /**
     * replaceParticipantInOperation - Обновляет ответсвенного в операторе
     * @param $process_id
     * @param $schema_process
     */
    public function replaceParticipantInOperation($process_id, $operation, $paticipant_vars){
        if(!in_array($operation['name'], array(self::ELEMENT_TASK, self::ELEMENT_AGREETMENT/*, self::ELEMENT_NOTIFICATION*/))) return;
        if(empty($operation['unique_index'])) return;

        $operations_model = OperationsModel::getChildrenModelByUniqueIndex($operation['unique_index'], $process_id);

        if($operations_model == false){
            return;
        }

        $status = ParticipantModel::replaceOperationsParticipant(
                                $operations_model->getRelateCopyId(),
                                $operations_model->getIdCardFromSchema(),
                                $paticipant_vars
                            );

        // history
        if($status && in_array($operations_model->getCardBStatus(), [OperationTaskBaseModel::B_STATUS_IN_WORK, OperationTaskBaseModel::B_STATUS_STOPED])){
            $operations_model->makeHistory($operations_model->getIdCardFromSchema(), true, [\HistoryMessagesModel::MT_RESPONSIBLE_APPOINTED]);
        }
    }










    /**
     * deleteParticipantRoleInOperations - Удаляет роли с операторов,  что были в качестве ответственной
     */
    /*
    public function deleteParticipantRoleInOperations($process_id, $schema_process = null, $ug_id, $ug_type){
        if($process_id === null) $process_id = ProcessModel::getInstance()->process_id;
        if($schema_process === null) $schema_process = ProcessModel::getInstance()->getSchema();

        // другие операции
        $operations = \Process\extensions\ElementMaster\Schema::getInstance()->getOperations($schema_process);
        if(empty($operations)) return;
        $operations_responsible = \Process\extensions\ElementMaster\Schema::getInstance()->getOperationsByResponsible($ug_id,$ug_type, $schema_process);

        foreach($operations as $operation){
            if(empty($operations_responsible) || !in_array($operation['unique_index'], $operations_responsible)) continue;
            $this->deleteParticipantInOperation($process_id, $operation, $ug_id, $ug_type);
        }
    }
    */



    /**
     * Удаляет участника в операторе
     * @param $process_id
     * @param $schema_process
     */
    public function deleteParticipantInOperation($process_id, $operation, $ug_id, $ug_type){
        if(!in_array($operation['name'], array(self::ELEMENT_TASK, self::ELEMENT_AGREETMENT/*, self::ELEMENT_NOTIFICATION*/))) return;
        if(empty($operation['unique_index'])) return;

        $operations_model = OperationsModel::findByParams($process_id, $operation['unique_index']);
        if(empty($operations_model)) return;

        $model = self::getChildrenModel($operations_model->element_name)->setOperationsModel($operations_model);

        ParticipantModel::model()->deleteAll(array(
            'condition' => 'copy_id=:copy_id AND data_id=:data_id AND ug_id=:ug_id AND ug_type =:ug_type',
            'params' => array(
                        ':copy_id' => $model->getRelateCopyId(),
                        ':data_id' => $model->getIdCardFromSchema(),
                        ':ug_id' => $ug_id,
                        ':ug_type' => $ug_type,
                    )));
    }






    /**
     * addNewOperationByDefault - проверяет и добавляет в БД новый оператор
     * @param $process_id
     * @param $schema_process
     */
    private function addNewOperationByDefault($process_id, $operation, $vars){
        if(empty($operation['unique_index'])) return;

        $count = \DataModel::getInstance()
            ->setFrom($this->tableName())
            ->setWhere(
                'process_id=:process_id AND unique_index=:unique_index',
                array(
                    ':process_id' => $process_id,
                    ':unique_index' => $operation['unique_index'],
                )
            )->findCount();

        if($count) return;

        $operations_model = $this->getNewOperationModel($operation['name'], false);

        // insert
        \DataModel::getInstance()
            ->insert(
                $this->tableName(),
                array(
                    'process_id' => $process_id,
                    'unique_index' => $operation['unique_index'],
                    'element_name' => $operation['name'],
                    'schema' => json_encode($operations_model->getSchema(true)),
                    'status' => self::STATUS_UNACTIVE,
                ));



        $operations_model = OperationsModel::findByParams($process_id, $operation['unique_index']);

        self::getChildrenModel($operations_model->element_name)
                ->setOperationsModel($operations_model)
                ->actionAddNewOperationByDefault($vars);
    }



    /**
     * Удаления оператора и всех его зависимостей
     */
    public function deleteOperation(){
        $result = self::getChildrenModel($this->element_name)
                            ->setOperationsModel($this)
                            ->actionDelete();

        return $result;
    }





    /**
     * updateGeneralSchema - обновление данных оператора в главной схеме
     */
    /*
    public function updateGeneralSchema($params){
        switch($params['element_name']){
            case self::ELEMENT_TASK :
                OperationTaskModel::getInstance()
                    ->setOperationsModel($this)
                    ->updateGeneralSchema($params);
                break;
            case self::ELEMENT_AGREETMENT :
                OperationAgreetmentModel::getInstance()
                    ->setOperationsModel($this)
                    ->updateGeneralSchema($params);
                break;
        }

        return $this;
    }
    */









    /**
     * проверка выполнения оператора
     */
    public function checkExecution(){
        $class = self::getOperationClassName($this->element_name);
        if($class){
            $status = $class::getInstance()
                ->setOperationsModel($this)
                ->checkExecution()
                ->getStatus();

           $this
                ->setStatus($status)
                ->saveStatus();
        }
    }




    /**
     * Возвращает статус выполнения стрелки предшествующего оператора
     */
    private function parentArrowOperationsIsDone($operations_model){
        $result = false;

        $operation_schema = SchemaModel::getInstance()->getElementsFromSchema(
            SchemaModel::getInstance()->getSchema(false, false),
            array(
                'type' => \Process\models\SchemaModel::ELEMENT_TYPE_OPERATION,
                'unique_index' => $operations_model->unique_index,
            ));

        if(empty($operation_schema)) return $result;

        $index = 0;
        foreach($operation_schema[$operations_model->unique_index]['arrows'] as $arrow){
            if($arrow['unique_index'] == $this->unique_index){
                $arrow_status = $operations_model->getArrowStatus($index);
                if($arrow_status == ArrowModel::STATUS_ACTIVE){
                    $result = true;
                    break;
                }
            }

            $index++;
        }

        return $result;
    }


    /**
     * Возвращает статус выполнение всех предшествующих операторов
     */
    public function parentOperationsIsDone(){
        $result = true;

        $parent_ui_list = ArrowModel::getInstance()->getUniqueIndexParent($this->unique_index);

        if(empty($parent_ui_list)) return $result;

        $operations_models = OperationsExecutionStatusModel::getInstance()->getOperationsModels();

        $lich = 0;
        foreach($parent_ui_list as $unique_index){
            $status = $operations_models[$unique_index]->getStatus();
            if($status != self::STATUS_DONE){
                $lich++;
            } else
            if($this->parentArrowOperationsIsDone($operations_models[$unique_index]) == false){
                $lich++;
            }
        }

        if($this->element_name == self::ELEMENT_CONDITION && count($parent_ui_list) > 1 && count($parent_ui_list) == $lich){
            $result = false;
        } elseif($this->element_name == self::ELEMENT_CONDITION && count($parent_ui_list) == 1 && $lich > 0){
            $result = false;
        } elseif($this->element_name != self::ELEMENT_CONDITION && $lich > 0){
            $result = false;
        }

        return $result;
    }






    /**
     * Возвращает статус выполнение всех предшествующих операторов
     */
    public function parentOperationsMaxDateEnding(){
        $date_list = [];

        $parent_ui_list = ArrowModel::getInstance()->getUniqueIndexParent($this->unique_index);

        if(empty($parent_ui_list)) return;

        $operations_models = OperationsExecutionStatusModel::getInstance()->getOperationsModels();

        foreach($parent_ui_list as $unique_index){
            $operation_model = $operations_models[$unique_index]->getOperationModel();
            $date_list[] = $operation_model->getDateEnding();
        }

        $date_list = array_unique($date_list);
        if($date_list == false){
            return;
        }

        \DateTimeOperations::sortDateArray($date_list);

        return array_pop($date_list);
    }



    /**
     * saveOperation - сохранение оператора. Используется поделью OperationSaveModel
     */
    public function saveOperation(){
        if($this->validateBeforeSave() == false){
            return false;
        }

        // run before
        $this->runBeforeSave();

        // save
        $result = $this->save();

        // run after
        if($result){
            $this->runAfterSave();
        }

        return $result;
    }




    /**
     * validateBeforeSave
     */
    private function validateBeforeSave(){
        $result = self::getChildrenModel($this->element_name)
                        ->setOperationsModel($this)
                        ->actionValidateBeforeSave();

        return $result;
    }




    /**
     * runBeforeSave
     */
    private function runBeforeSave(){
        $schema = self::getChildrenModel($this->element_name)
                        ->setOperationsModel($this)
                        ->actionBeforeSave()
                        ->actionBeforeSaveGetSchema();

        $this->schema = $schema;

        return true;
    }



    /**
     * runAfterSave
     */
    private function runAfterSave(){
        self::getChildrenModel($this->element_name)
                ->setOperationsModel($this)
                ->actionAfterSave();
    }






    /**
     * findOperationsModelByEntityParams
     */
    public static function findOperationsModelByEntityParams($copy_id, $card_id){
        return OperationsModel::model()->find(array(
                    'condition' => 'copy_id=:copy_id AND card_id=:card_id',
                    'params' => array(
                        ':copy_id'=>$copy_id,
                        ':card_id'=>$card_id),
                ));
    }




    /**
     * checkShowOperation - проверка режима просмотра процесса
     */
    public function checkShowOperation(){
        if(ProcessModel::getInstance()->getModeChange() == \Process\models\ProcessModel::MODE_CHANGE_EDIT){
            return true;
        }

        if(in_array($this->element_name, array(self::ELEMENT_TASK, self::ELEMENT_AGREETMENT, self::ELEMENT_DATA_RECORD))){
            return true;
        }

        $status = $this->getStatus();
        if(!in_array($status, array(self::STATUS_ACTIVE, self::STATUS_DONE))){
            return false;
        }

        return false;
    }


    /**
     * getResponsible - Возвращает ответственного по оператору
     * @return mixed
     */
    public function getResponsible(){
        $responsible_list = \Process\extensions\ElementMaster\Schema::getInstance()->getOperationResponsibleList();

        if(!empty($responsible_list) && array_key_exists($this->unique_index, $responsible_list)){
            return $responsible_list[$this->unique_index];
        }
    }


    /**
     * Установка статуса "выполнен" для оператора (Уведомление)
     */
    public function setOperationDone(){
        if($this->element_name != self::ELEMENT_NOTIFICATION) return;
        if($this->getMode(true) == \Process\models\OperationsModel::MODE_CONSTRUCTOR) return;

        $this
            ->setStatus(self::STATUS_DONE)
            ->saveStatus();
        return $this;
    }




    /**
     * getOperationNamesPossiblyBindingObject - возвращат список операторов, которые могут быть связаны с Связанным модулем
     */
    /*
    public static function getOperationModelsPossiblyBindingObject(){
        $element_list = array();

        foreach(self::getOperationsList() as $element_name){
            $operation_model = self::getChildrenModel($element_name);
            if($operation_model->getIsPossiblyBO()){
                $element_list[] = $element_name;
            }
        }

        return $element_list;
    }
    */





    /**
     * thereIsSettedBindingObjectOperations - проверяет и возвращает статус наличия связаннях обьектов в операторах
     * @return bool
     */
    /*
    public static function thereIsSettedBindingObjectOperations($copy_id){
        $operation_list = \Process\models\OperationsModel::getOperationModelsPossiblyBindingObject();
        if(!$operation_list) return;
        foreach($operation_list as &$element){
            $element = '"' . $element . '"';
        }
        $operation_list = implode(',', $operation_list);

        $query = "
            SELECT t1.element_name, t1.schema
            FROM
                {{process_operations}} t1
            LEFT JOIN 
                {{process}} t2 ON t1.process_id = t2.process_id
            WHERE 
                t2.related_module = $copy_id AND t1.element_name in ($operation_list)
            ORDER BY t1.element_name
        ";

        $operation_list = \DataModel::getInstance()->setText($query)->findAll();

        if($operation_list == false) return;

        foreach($operation_list as $operation){
            $class_name = self::getOperationClassName($operation['element_name']);
            $check = $class_name::thereIsSettedBindingObject($operation['schema'], $copy_id);

            if($check){
                return true;
            }
        }

        return false;
    }
    */







}
