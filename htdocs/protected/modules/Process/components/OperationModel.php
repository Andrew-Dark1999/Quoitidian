<?php


namespace Process\components;

use Process\extensions\ElementMaster\Schema;
use Process\models\OperationsModel;
use Process\models\ParticipantModel;

abstract class OperationModel {

    const STATUS_UNACTIVE     = 'unactive';
    const STATUS_ACTIVE       = 'active';
    const STATUS_DONE         = 'done';

    protected $_status;
    protected $_operations_model = null;
    protected $_title;

    protected $_be_error = false;
    protected $_validate_messages = array();
    protected $_validate_elements = false;

    // возможна связь со связанным обьектом
    protected $_is_possibly_bo = false;



    public function __construct(){
        $this->setTitle();
    }


    public static function getInstance(){
        return new static();
    }



    public function setStatus($status){
        $this->_status = $status;
        return $this;
    }




    public function getStatus(){
        return $this->_status;
    }


    abstract protected function setTitle();


    public function getTitle(){
        return $this->_title;

    }

    public function setOperationsModel($operations_model){
        if(empty($operations_model)) return $this;

        $this->_operations_model = $operations_model;

        $this->setParamsFromOperationModel();

        return $this;
    }




    public function getOperationsModel(){
        return $this->_operations_model;
    }



    // возвращает статус, что оператор может быть связан с Связанным модулем
    public function getIsPossiblyBO(){
        return $this->_is_possibly_bo;
    }


    /**
     * thereIsSettedBindingObject - проверяет схему оператора и возвращает результат сравнения связанного объекта
     * @param string|integer $compare_value - величина для сравнения
     */
    /*
    public static function thereIsSettedBindingObject($schema, $compare_value){
        return false;
    }
    */



    public static function getParentElement($schema, $type){
        if(empty($schema)) return;
        foreach($schema as $element){
            if($element['type'] == $type)
                return $element;
        }
    }


    /**
     * findOperationModel - ищет и возвращает конечную модель оператора
     * @param array $params
     * $params = array('operations_id') || $params = array('process_id', 'unique_index');
     */
    public static function findOperationModel(array $params){
        if(empty($params)) return;

        $operations_model = null;
        if(array_key_exists('operations_id', $params)){
            $operations_model = OperationsModel::model()->findByPk($params['operations_id']);
        } elseif(array_key_exists(array('operations_id', 'unique_index'), $params)){
            $operations_model = OperationsModel::findByParams($params['process_id'], $params['unique_index']);
        }

        if(empty($operations_model)) return;

        $class = OperationsModel::getOperationClassName($operations_model->element_name);
        $operation_model = $class::getInstance()->setOperationsModel($operations_model);

        return $operation_model;
    }


    public function setParamsFromOperationModel(){
        $this->setStatus($this->_operations_model->getStatus());
    }



    /**
     * дополняем схему оператора дефолтными данными
     */
    public function addDefaultDataForOperatorSchema($element_schema){
        $schema_default = $this->_operations_model->getSchemaDefault($this->_operations_model->element_name);
        if(!empty($element_schema))
            $schema = \Helper::arrayMerge($schema_default, $element_schema);

        return $schema;
    }


    /**
     * возвращает список элементов для параметра
     * @return mixed
     */
    public function getElementHtml($element_schema){
        return \Yii::app()->controller->widget('\Process\extensions\ElementMaster\BPM\Operations\Operations',
            array(
                'operation_model' => $this,
                'operations_model' => $this->_operations_model,
                'view_type' => 'params',
                'element_name' => $this->_operations_model->element_name,
                'element_schema' => $element_schema,
            ),
            true);
    }


    /**
     * checkIsResponsibleRole - проверка, це является ли ответственный группой
     * @param null $schema
     * @return bool
     */
    public function checkIsResponsibleRole($schema = null){
        $responsible = Schema::getInstance()->getOperationResponsible($schema, $this->_operations_model->unique_index);
        if($responsible && $responsible['ug_type'] == \ParticipantModel::PARTICIPANT_UG_TYPE_GROUP){
            return true;
        }

        return false;
    }



    /**
     * checkIsSetResponsibleUser - проверка, присутвует ли ответственный пользователь в системе
     * @param null $schema
     * @return bool
     */
    public function checkIsSetResponsibleUser($schema = null){
        $responsible = Schema::getInstance()->getOperationResponsible($schema, $this->_operations_model->unique_index);
        if($responsible && $responsible['ug_type'] == false){
            return false;
        }

        $responsible_model = \ParticipantModel::model()->getEntityDataByParams($responsible['ug_id'], $responsible['ug_type']);

        if($responsible_model == false){
            return false;
        }

        if(
            $responsible['ug_id'] == \ParticipantConstModel::TC_RELATE_RESPONSIBLE &&
            $responsible['ug_type'] == \ParticipantModel::PARTICIPANT_UG_TYPE_CONST &&
            $this->getOperationsModel()->process->related_module == false
        ){
            return false;
        }

        return true;
    }





    public function checkExecution(){
        return $this;
    }






    public function setValidateElements($validate){
        $this->_validate_elements = $validate;
        return $this;
    }


    public function getBeError(){
        return $this->_be_error;
    }


    protected function addValidateMessage($name, $text, $append = false){
        $this->_be_error = true;
        if($append == false){
            $this->_validate_messages[$name] = $text;
        } else {
            if(!empty($this->_validate_messages[$name])){
                if(is_array($this->_validate_messages[$name])){
                    $this->_validate_messages[$name][] = $text;
                } else {
                    $this->_validate_messages[$name] = (array)$this->_validate_messages[$name];
                    $this->_validate_messages[$name][] = $text;
                }
            } else {
                $this->_validate_messages[$name] = $text;
            }
        }

        return $this;
    }


    public function getValidateMessage($name, $delivery = '</br>'){
        if(isset($this->_validate_messages[$name])){
            $messages = $this->_validate_messages[$name];
            if(is_array($messages)){
                $messages = implode($delivery, $messages);
            }
            return $messages;
        }
    }


    public function getValidateMessages(){
        return $this->_validate_messages;
    }



    protected function validate(){
        return $this;
    }




    public function getDateEnding(){
        return $this->_operations_model->parentOperationsMaxDateEnding();
    }









    /*************************************
     * ACTIONS
     *************************************/


    /**
     * actionGetSchemaPrepared - вызывается при запросе схемы оператора при доп. обработке
     */
    public function actionGetSchemaPrepared(){
        return $this->_operations_model->getSchema(true);
    }




    /**
     * actionValidateBeforeSave - проверка перед сохранение схемы оператора
     */
    public function actionValidateBeforeSave(){
        return true;
    }



    /**
     * actionBeforeSave - вызывается перед сохранение схемы оператора
     */
    public function actionBeforeSave(){
        return $this;
    }



    /**
     * actionBeforeSaveGetSchema - вызывается перед сохранение схемы оператора после actionBeforeSave
     */
    public function actionBeforeSaveGetSchema(){
        return json_encode($this->_operations_model->getSchema(true));
    }



    /**
     * actionAfterSave - вызывается после сохранение схемы оператора
     */
    public function actionAfterSave(){
        return $this;
    }



    /**
     * actionBeforeSaveGetSchema - вызывается перез созранение схемы оператора после actionBeforeSave
     */
    public function actionAfterDelete(){
        return json_encode($this->_operations_model->getSchema(true));
    }




    /**
     * actionAddNewOperationByDefault - вызывается при добавлении нового оператора после сохранение параметров оператора
     */
    public function actionAddNewOperationByDefault($vars = null){
        return $this;
    }


    /**
     * actionPrepareDataForNewOperation Подготовка данных  оператора перед сохранением
     * @return $this
     */
    public function actionPrepareDataForNewOperation(){
        return $this;
    }


    /**
     * actionCloneDataBeforeSave - клонирование данных перед сохранением
     * @param null $vars
     * @return $this
     */
    public function actionCloneDataBeforeSave($vars = null){
        return $this;
    }



    /**
     * actionCloneDataBeforeSave - клонирование данных после сохранения
     * @param null $vars
     * @return $this
     */
    public function actionCloneDataAfterSave($vars = null){
        return $this;
    }


    /**
     * actionDelete - удаляет оператор из БД
     */
    public function actionDelete(){
        return  $this->_operations_model->delete();
    }




    /**
     * actionReturnHtmlResultWhereError - вызывается перед запросом HTML страницы в случае, если перед сохранением были ошибки
     */
    public function actionReturnHtmlResultWhileError(){
        return $this;
    }



    /**
     * actionOperationSetActive - вызывается когда оператор становится в Статус "Активный"
     */
    public function actionOperationSetActive(){
        return $this;
    }




    /**
 * ACTIONS  (end)
 */


}

