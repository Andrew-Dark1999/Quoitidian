<?php
/**
 * OperationScenarioModel -  оператор Сценарий
 *
 * @author Alex R.
 */

namespace Process\models;

class OperationScenarioModel extends \Process\components\OperationModel
{

    const ELEMENT_SCRIPT_TEXT = 'script_text';
    const ELEMENT_SCRIPT_TYPE = 'script_type';

    const SCRIPT_TYPE_PHP = 'php';

    const STATUS_NONE = 'none';
    const STATUS_EXECUTED = 'executed';
    const STATUS_ERROR = 'error';

    private $_script_text;

    private $_e_status = self::STATUS_NONE;

    private $_executed_result;

    private $_message;

    private $_schema_only_from_db = false;

    private $_validate_deny_functions = [
        'apache_child_terminate',
        'apache_setenv',
        'define_syslog_variables',
        'escapeshellarg',
        'escapeshellcmd',
        'eval',
        'exec',
        'shell_exec',
        'fp',
        'fput',
        'ftp_connect',
        'ftp_exec',
        'ftp_get',
        'ftp_login',
        'ftp_nb_fput',
        'ftp_put',
        'ftp_raw',
        'ftp_rawlist',
        'highlight_file',
        'ini_alter',
        'ini_set',
        'ini_get',
        'ini_get_all',
        'ini_restore',
        'inject_code',
        'mysql_pconnect',
        'openlog',
        'passthru',
        'php_uname',
        'phpAds_remoteInfo',
        'phpAds_XmlRpc',
        'phpAds_xmlrpcDecode',
        'phpAds_xmlrpcEncode',
        'popen',
        'posix_getpwuid',
        'posix_kill',
        'posix_mkfifo',
        'posix_setpgid',
        'posix_setsid',
        'posix_setuid',
        'posix_setuid',
        'posix_uname',
        'proc_close',
        'proc_get_status',
        'proc_nice',
        'proc_open',
        'proc_terminate',
        'shell_exec',
        'syslog',
        'system',
        'xmlrpc_entity_decode',
        'file_get_contents',
        'file_put_contents',
        'call_user_func',
    ];

    protected function setTitle()
    {
        $this->_title = \Yii::t('ProcessModule.base', 'Scenario');
    }

    public function getBuildedParamsContent()
    {
        if (empty($this->_operations_model)) {
            return;
        }

        $schema = $this->_operations_model->getSchema($this->_schema_only_from_db);
        if (empty($schema)) {
            return;
        }
        $schema = $this->addDefaultDataForOperatorSchema($schema);

        if ($this->_validate_elements) {
            $this->actionValidateBeforeSave();
        }

        $content = '';
        foreach ($schema as $element) {
            $content .= $this->getElementHtml($element);
        }

        return $content;
    }

    /**
     * checkExecution - проверка выполнения, установка статуса
     *
     * @return $this
     */
    public function checkExecution()
    {
        $process_model = ProcessModel::getInstance();
        if ($process_model->getMode() == ProcessModel::MODE_CONSTRUCTOR) {
            return $this;
        }

        $b_status = $process_model->getBStatus();

        //B_STATUS_STOPED
        if ($b_status == ProcessModel::B_STATUS_STOPED) {
            return $this;
        }

        //B_STATUS_IN_WORK
        if ($b_status == ProcessModel::B_STATUS_IN_WORK) {
            if ($this->_operations_model->parentOperationsIsDone() == false) {
                return $this;
            }

            if ($this->checkIsResponsibleRole()) {
                return $this;
            }
            if ($this->checkIsSetResponsibleUser() == false) {
                return $this;
            }

            // запуск оператора - простой...
            if ($this->getStatus() == OperationsModel::STATUS_UNACTIVE) {
                $this->setStatus(OperationsModel::STATUS_ACTIVE);

            }

            // запуск оператора - простой...
            if ($this->getStatus() == OperationsModel::STATUS_ACTIVE) {
                $this->runScript();
                $this->saveScenario();

                if ($this->_e_status == self::STATUS_EXECUTED) {
                    $this->setStatus(OperationsModel::STATUS_DONE);
                }
            }

            //B_STATUS_TERMINATED
        } elseif ($b_status == ProcessModel::B_STATUS_TERMINATED) {
        }

        return $this;
    }

    private function getScriptText($only_from_db = false)
    {
        $from_schema = SchemaModel::getInstance()->getElementsFromSchema($this->_operations_model->getSchema($only_from_db), ['only_first' => true, 'type' => self::ELEMENT_SCRIPT_TEXT]);
        if (!empty($from_schema['value'])) {
            return $from_schema['value'];
        }
    }

    private function getScriptType()
    {
        $from_schema = SchemaModel::getInstance()->getElementsFromSchema($this->_operations_model->getSchema(), ['only_first' => true, 'type' => self::ELEMENT_SCRIPT_TYPE]);
        if (!empty($from_schema['value'])) {
            return $from_schema['value'];
        }
    }

    private function runScript()
    {
        $this->refreshVariables();

        if ($this->validate() == false) {
            return;
        }

        $script_type = $this->getScriptType();

        switch ($script_type) {
            case self::SCRIPT_TYPE_PHP :
                $this->runScriptPhp();
        }
    }

    private function runScriptPhp()
    {
        $result = null;

        restore_error_handler();

        try {
            ob_start();
            $result = $this->evalExecute();
            ob_clean();
            ob_flush();

            if (is_bool($result)) {
                $result = (int)$result;
            }

            $this->setRunResult($result);
        } catch (\ParseError $e) {
            $this->setRunResult($result, $e);
        } catch (\Exception $e) {
            $this->setRunResult($result, $e);
        }
    }

    /**
     * Исполняет скрипт
     *
     * @return mixed
     */
    public function evalExecute()
    {
        $process = ProcessModel::getInstance();

        return eval($this->_script_text);
    }

    private function setRunResult($result = null, $e = null)
    {
        if ($e === null) {
            $this->_e_status = self::STATUS_EXECUTED;
            $this->_message = null;
            $this->_executed_result = $result;
        } else {
            if ($e) {
                $this->_e_status = self::STATUS_ERROR;
                if ($e->getCode() == 0) {
                    $this->_message = 'Script syntax error';
                } else {
                    $this->_message = $e->getMessage();
                }
            }
        }
    }

    private function saveScenario()
    {
        $operations_id = $this->_operations_model->operations_id;
        $attributes = [
            'script_text'     => $this->_script_text,
            'status'          => $this->_e_status,
            'message'         => (!empty($this->_message) ? $this->_message : null),
            'executed_result' => $this->_executed_result,
        ];

        $data = $this->selectFromTable();
        if ($data) {
            \DataModel::getInstance()->Update('{{process_scenario}}', $attributes, 'operations_id =' . $operations_id);
        } else {
            $attributes['operations_id'] = $operations_id;
            \DataModel::getInstance()->Insert('{{process_scenario}}', $attributes);
        }
    }

    private function selectFromTable()
    {
        $operations_id = $this->_operations_model->operations_id;
        if ($operations_id == false) {
            return;
        }

        $data_model = \DataModel::getInstance()
            ->setFrom('{{process_scenario}}')
            ->setWhere('operations_id = ' . $operations_id);

        return $data_model->findRow();
    }

    private function refreshVariables()
    {
        $data = $this->selectFromTable();
        if ($data == false) {
            return;
        }

        $this->_script_text = $data['script_text'];
        $this->_e_status = $data['status'];
        $this->_executed_result = $data['executed_result'];

        if ($this->_e_status == self::STATUS_ERROR) {
            $this->_message = $data['message'];
        }
    }

    private function getMessageText()
    {
        return (!empty($this->_message) ? $this->_message : null);
    }

    private function getExecuteValidate()
    {
        if ($this->_e_status == self::STATUS_ERROR) {
            $validate = new \Validate();
            $validate->addValidateResult('e', \Yii::t('ProcessModule.messages', $this->getMessageText()));

            return $validate;
        }
    }

    /**
     * getLastExecuteStatus - возвращает статус последнего выполнения оператора
     */
    public function getLastExecuteStatus()
    {
        $status = $this->getOperationsModel()->getStatus();
        if ($status == \Process\models\OperationsModel::STATUS_DONE) {
            return ['status' => true];
        }

        $this->refreshVariables();

        $result = [
            'status'   => ($this->_e_status == self::STATUS_ERROR ? false : true),
            'validate' => $this->getExecuteValidate(),
        ];

        return $result;
    }

    /**
     * getOperationSchemaClean
     */
    private function getOperationSchemaClean()
    {
        $schema = $this->_operations_model->getSchema();

        foreach ($schema as &$element) {
            switch ($element['type']) {
                //ELEMENT_SCRIPT_TEXT
                case self::ELEMENT_SCRIPT_TEXT:
                    $element['value'] = null;
                    break;
            }
        }
        unset($element);

        return $schema;
    }

    /**
     * getOperationSchemaAddEntities
     */
    private function getOperationSchemaAddEntities()
    {
        $schema = $this->_operations_model->getSchema(true);

        foreach ($schema as &$element) {
            switch ($element['type']) {
                //ELEMENT_SCRIPT_TEXT
                case self::ELEMENT_SCRIPT_TEXT:
                    $element['value'] = $this->_script_text;
                    break;
            }
        }
        unset($element);

        return $schema;
    }

    /**
     * cloneParams
     */
    private function cloneParams($operations_model, $process_id_old)
    {
        $query = "
            SELECT t1.*
            FROM {{process_scenario}} AS t1
            LEFT JOIN {{process_operations}} AS t2 ON t1.operations_id = t2.operations_id
            WHERE t2.process_id = $process_id_old AND t2.unique_index = '" . $operations_model['unique_index'] . "' AND t2.element_name = '" . OperationsModel::ELEMENT_SCENARIO . "'";

        $scenario_list = \DataModel::getInstance()->setText($query)->findAll();
        if (empty($scenario_list)) {
            return;
        }

        foreach ($scenario_list as &$scenario) {
            unset($scenario['scenario_id']);
            $scenario['operations_id'] = $operations_model['operations_id'];

            \DataModel::getInstance()->insert('{{process_scenario}}', $scenario);
        }

    }

    /**
     * Validate script
     */

    public function validate()
    {
        $this->validateTextScript();

        if ($this->getBeError() == false) {
            $this
                ->validateByFuntionList()
                ->validateByLinkMethodRun()
                ->validateByExecRun();
        }

        return !$this->getBeError();
    }

    private function validateTextScript()
    {
        if (empty($this->_script_text)) {
            $this->addValidateMessage(self::ELEMENT_SCRIPT_TEXT, \Yii::t('ProcessModule.messages', 'There is no script'), true);
        }
    }

    private function validateByFuntionList()
    {
        $functions = [];
        foreach ($this->_validate_deny_functions as $function) {
            if (preg_match('~(?i)' . $function . '\(.*?\)~', $this->_script_text, $message)) {
                $functions[] = $function . '()';
            }
        }

        if ($functions) {
            $this->addValidateMessage(self::ELEMENT_SCRIPT_TEXT, \Yii::t('ProcessModule.messages', 'The script contains forbidden functions') . ': ' . implode(', ', $functions), true);
        }

        return $this;
    }

    private function validateByLinkMethodRun()
    {
        $methods = [];
        if (preg_match('~\$+?.[^=]*?\(.*?\)~', $this->_script_text, $messages)) {
            foreach ($messages as $message) {
                if (mb_strpos($message, '->') === false) {
                    $methods[] = $message;
                }
            }
        }

        if ($methods) {
            $this->addValidateMessage(self::ELEMENT_SCRIPT_TEXT, \Yii::t('ProcessModule.messages', 'Calling a method via a variable is forbidden') . ': ' . implode(', ', $methods), true);
        }

        return $this;
    }

    private function validateByExecRun()
    {
        if (preg_match('~`.*?`~', $this->_script_text, $messages)) {
            $this->addValidateMessage(self::ELEMENT_SCRIPT_TEXT, \Yii::t('ProcessModule.messages', 'Calling external commands is forbidden') . ': ' . implode(', ', $messages), true);
        }

        return $this;
    }










    /**
     *  ACTIONS
     */

    /**
     * validateBeforeSave - проверка перед сохранение схемы оператора
     */
    public function actionValidateBeforeSave()
    {
        $this->refreshVariables();
        $this->_script_text = $this->getScriptText(true);
        $this->validate();

        return !$this->getBeError();
    }

    /**
     * actionBeforeSave
     */
    public function actionBeforeSave()
    {
        $this->refreshVariables();
        $this->_script_text = $this->getScriptText(true);
        $this->saveScenario();

        return $this;
    }

    /**
     * actionBeforeSaveGetSchema
     */
    public function actionBeforeSaveGetSchema()
    {
        return json_encode($this->getOperationSchemaClean());
    }

    /**
     * actionGetPreparedSchema
     */
    public function actionGetSchemaPrepared()
    {
        $this->refreshVariables();

        return $this->getOperationSchemaAddEntities();
    }

    /**
     * actionCloneDataAfterSave - клонирование параметров всех операторов в процессе
     */
    public function actionCloneDataAfterSave($vars = null)
    {
        $operations_models = OperationsModel::model()->findAll([
            'condition' => 'process_id=:process_id AND element_name=:element_name',
            'params'    => [
                ':process_id'   => $vars['process_id_new'],
                ':element_name' => OperationsModel::ELEMENT_SCENARIO,
            ],
        ]);

        if (empty($operations_models)) {
            return $this;
        }

        foreach ($operations_models as $operations_model) {
            $this->cloneParams($operations_model, $vars['process_id_old']);
        }

        return $this;
    }

    /**
     * actionAddNewOperationByDefault
     */
    public function actionAddNewOperationByDefault($vars = null)
    {
        $this->refreshVariables();
        $this->_script_text = $this->getScriptText(true);
        $this->saveScenario();

        return $this;
    }

    /**
     * actionReturnHtmlResult
     */
    public function actionReturnHtmlResult()
    {
        return parent::actionAfterDelete(); // TODO: Change the autogenerated stub
    }

    /**
     * actionReturnHtmlResultWhereError - вызывается перед запросом HTML страницы в случае, если перед сохранением были ошибки
     */
    public function actionReturnHtmlResultWhileError()
    {
        $this->_schema_only_from_db = true;

        return $this;
    }

    /**
     *  ACTIONS end
     */

}
