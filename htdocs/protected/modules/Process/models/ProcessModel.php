<?php
/**
 * ProcessModel widget
 *
 * @author Alex R.
 */
namespace Process\models;

class ProcessModel extends \ActiveRecord
{

    const MODE_CONSTRUCTOR = 'constructor';
    const MODE_RUN = 'run';

    const MODE_CHANGE_VIEW = 'view';
    const MODE_CHANGE_EDIT = 'edit';

    const B_STATUS_ZERO = '';
    const B_STATUS_IN_WORK = '2'; // in_work
    const B_STATUS_STOPED = '3'; // stoped
    const B_STATUS_TERMINATED = '1'; // ending

    const ACTION_START = 'start';
    const ACTION_STOP = 'stop';
    const ACTION_TERMINATE = 'terminate';
    const ACTION_MC_EDIT = 'mc_edit';
    const ACTION_MC_VIEW = 'mc_view';

    const ELEMENT_MODULE_RELATE = 'module_relate';

    private $_mode;

    private $_mode_change;

    private static $_instance;

    private $_vars;

    private $_result = [];

    public function tableName()
    {
        return '{{process}}';
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * только один экзепляр класса
     */
    public static function getInstance($process_id = null, $refresh = false)
    {
        if ($refresh == true) {
            ArrowModel::setRefreshInstace();
        }

        if (static::$_instance === null && !empty($process_id)) {
            static::$_instance = static::model()->findByPk($process_id);

            return static::$_instance;
        } else {
            if ($refresh && !empty($process_id) && is_numeric($process_id)) {
                static::$_instance = static::model()->findByPk($process_id);

                return static::$_instance;
            } else {
                if (static::$_instance === null) {
                    static::$_instance = static::model();
                }

                return static::$_instance;
            }
        }
    }

    public function createInstance()
    {
        return static::getInstance($this->process_id, true);
    }

    public function rules()
    {
        return [
            ['process_id, parent_process_id', 'numerical', 'integerOnly' => true],
            ['module_title', 'length', 'max' => 255],
            ['schema', 'length', 'max' => 65536],
            ['b_status', 'checkBStatus'],
            ['date_create,date_edit,user_create,user_edit,this_template', 'safe'],
        ];
    }

    public function relations()
    {
        return [
            'processRelateModules' => [self::HAS_ONE, '\Process\models\ProcessRelateModulesModel', ['process_id' => 'process_id']],
            'operations'           => [self::HAS_MANY, '\Process\models\OperationsModel', 'process_id'],
        ];
    }

    /**
     * Режим процесса: конструктор / выполнение
     */
    public function setMode($mode)
    {
        $this->_mode = $mode;

        return $this;
    }

    /**
     * getMode
     */
    public function getMode()
    {
        if ($this->_mode === null) {
            $this->setModeFromThisTemplate();
        }

        return $this->_mode;
    }

    /**
     * setModeChange
     */
    public function setModeChange($mode_change = null, $auto = false)
    {
        if ($auto == true) {
            switch ($this->getMode()) {
                case self::MODE_CONSTRUCTOR:
                    $mode_change = self::MODE_CHANGE_EDIT;
                    if (!\Access::checkAccess(\PermissionModel::PERMISSION_DATA_RECORD_EDIT, \ExtensionCopyModel::MODULE_PROCESS, \Access::ACCESS_TYPE_MODULE)) {
                        $mode_change = self::MODE_CHANGE_VIEW;
                    }

                    break;
                case self::MODE_RUN:
                    $mode_change = self::MODE_CHANGE_VIEW;
                    break;
            }
        }

        $this->_mode_change = $mode_change;

        return $this;
    }

    /**
     * getModeChange
     */
    public function getModeChange()
    {
        if ($this->_mode_change === null) {
            $this->setModeChange(null, true);
        }

        return $this->_mode_change;
    }

    /**
     * getInstanceTemplate
     */
    private function getInstanceTemplate()
    {
        return ($this->this_template ? true : false);
    }

    /**
     * setModeFromThisTemplate
     */
    private function setModeFromThisTemplate()
    {
        $this->setMode($this->getInstanceTemplate() ? self::MODE_CONSTRUCTOR : self::MODE_RUN);

        return $this;
    }

    /**
     * setSchema
     */
    public function setSchema($schema)
    {
        $this->setAttribute('schema', json_encode($schema));

        return $this;
    }

    /**
     * getSchema
     */
    public function getSchema($json_decode = true)
    {
        if ($json_decode) {
            return json_decode($this->schema, true);
        } else {
            return $this->schema;
        }
    }

    public function getProcessTitle()
    {
        return $this->module_title;
    }

    public function setVars($vars)
    {
        $this->_vars = $vars;

        return $this;
    }

    public function getVars()
    {
        return $this->_vars;

    }

    public function getResult()
    {
        $result['status'] = !$this->hasErrors();

        if ($result['status'] == false) {
            $messages = $this->getErrors();
            if ($messages) {
                $result['messages'] = [];

                foreach ($messages as $attribute => $message_list) {
                    $result['messages'][$attribute] = $message_list;
                }
            }

        }

        if ($this->_result) {
            $result = array_merge($result, $this->_result);
        }

        return $result;
    }

    /**
     * checkBStatus - Проверка статуса
     */
    public function checkBStatus($attribute, $params)
    {
        if ($this->scenario != 'switch_process_status') {
            return true;
        }
        $b_status = $this->getAttribute($attribute);
        if ($b_status != static::B_STATUS_IN_WORK && $b_status != static::B_STATUS_STOPED && $b_status != static::B_STATUS_TERMINATED) {
            $this->addError($attribute, \Yii::t('ProcessModule.messages', 'Invalid parameter') . ' "' . $attribute . '"');

            return;
        }

        return true;
    }

    /**
     * getBStatus - возвращает статус процеса
     */
    public function getBStatus()
    {
        switch ($this->b_status) {
            case static::B_STATUS_IN_WORK:
            case static::B_STATUS_STOPED:
            case static::B_STATUS_TERMINATED:
                return $this->b_status;
            default :
                return static::B_STATUS_STOPED;
        }
    }

    /**
     * updateBStatus - оновляет статус процесса
     */
    public function updateBStatus($b_status)
    {
        $this->b_status = $b_status;
        $this->save();
        $this->refresh();
    }

    public function getProcessId()
    {
        if (property_exists($this, 'process_id')) {
            return $this->process_id;
        }
    }

    /**
     * getActions
     */
    public function getActions()
    {
        if ($this->this_template == \EditViewModel::THIS_TEMPLATE_TEMPLATE) {
            if (\Access::checkAccess(\PermissionModel::PERMISSION_DATA_RECORD_CREATE, \ExtensionCopyModel::MODULE_PROCESS, \Access::ACCESS_TYPE_MODULE)) //\Access::checkAccess(\PermissionModel::PERMISSION_DATA_RECORD_EDIT, \ExtensionCopyModel::MODULE_PROCESS, \Access::ACCESS_TYPE_MODULE))
            {
                $actions = [
                    [
                        'type'   => self::ACTION_START,
                        'title'  => \Yii::t('ProcessModule.base', 'Start'),
                        'active' => ($this->getBStatus() == static::B_STATUS_IN_WORK ? true : false),
                    ],
                ];
            }
        } else {
            $actions = [
                [
                    'type'   => self::ACTION_START,
                    'title'  => \Yii::t('ProcessModule.base', 'Start'),
                    'active' => ($this->getBStatus() == static::B_STATUS_IN_WORK ? true : false),
                ],
                [
                    'type'   => self::ACTION_STOP,
                    'title'  => \Yii::t('ProcessModule.base', 'Stop'),
                    'active' => ($this->getBStatus() == static::B_STATUS_STOPED ? true : false),
                ],
                [
                    'type'   => self::ACTION_TERMINATE,
                    'title'  => \Yii::t('ProcessModule.base', 'Terminate'),
                    'active' => ($this->getBStatus() == static::B_STATUS_TERMINATED ? true : false),
                ],

            ];
        }

        /*$actions[] = array(
            'type'=> 'separator',
            'title'=> null,
            'active'=> false,
        );*/

        $actions[] = [
            'type'   => self::ACTION_MC_VIEW,
            'title'  => \Yii::t('ProcessModule.base', 'Review'),
            'active' => ($this->getModeChange() == static::MODE_CHANGE_VIEW ? true : false),
        ];

        if (\Access::checkAccess(\PermissionModel::PERMISSION_DATA_RECORD_EDIT, \ExtensionCopyModel::MODULE_PROCESS, \Access::ACCESS_TYPE_MODULE)) {
            $actions[] = [
                'type'   => self::ACTION_MC_EDIT,
                'title'  => \Yii::t('ProcessModule.base', 'Edit'),
                'active' => ($this->getModeChange() == static::MODE_CHANGE_EDIT ? true : false),
            ];
        }

        return $actions;
    }

    private function getVersionSchema()
    {
        $process_schema_model = \Process\models\ProcessSchemaVersions::model()->scopeProcessId(ProcessModel::getInstance()->process_id)->find();

        if ($process_schema_model) {
            return $process_schema_model->version;
        }
    }

    /**
     * getServerParams - подготовка параметров для js-обьекта на верстке
     */
    public function getServerParams($result = [])
    {
        $result['version_schema'] = $this->getVersionSchema();
        $result['process_id'] = $this->process_id;
        $result['process_status'] = $this->getBStatus();
        $result['this_template'] = $this->getInstanceTemplate();
        $result['mode'] = $this->getMode();
        $result['mode_change'] = $this->getModeChange();
        $result['binding_object_check'] = (BindingObjectModel::getInstance()->setVars(['process_id' => $this->process_id])->isSetRelateByProcessId() ? 0 : 1);

        $result['BPM']['schema'] = \Process\models\SchemaModel::getInstance()->setOperationsExecutionStatus()->reloadOtherParamsForSchema()->getSchema();

        $result['BPM']['elements']['operations'] = \Process\models\OperationsModel::getElements('chevron', 'all');
        $result['BPM']['elements']['responsible'] = \Process\models\ResponsibleModel::getElements();

        return $result;
    }

    /**
     * getProcessListForOperation - список процессов (и/или шаблонов процессов)
     * mixed $this_template
     *            true - только шаблоны
     *            false - только карточки
     *            null - шаблоны и карточки
     */
    public static function getProcessListForOperation($this_template = false, $not_finished_object = true)
    {
        $result = [
            null => \Yii::t('ProcessModule.base', 'Process name'),
        ];

        $extension_copy = \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS);
        list($filter_controller) = \Yii::app()->createController($extension_copy->extension->name . '/ListViewFilter');

        // DataModel
        $data_model = new \DataModel();
        $data_model
            ->setExtensionCopy($extension_copy)
            ->setFromModuleTables();

        //responsible
        if ($extension_copy->isResponsible()) {
            $data_model->setFromResponsible();
        }

        //participant
        if ($extension_copy->isParticipant()) {
            $data_model->setFromParticipant();
        }

        //finished_object
        if ($not_finished_object) {
            $filter_data = $filter_controller->getParamsToQuery($extension_copy, [\FilterVirtualModel::VF_FINISHED_OBJECT], [\FilterVirtualModel::VF_FINISHED_OBJECT => ['corresponds' => 'corresponds_not']]);
            $data_model->andWhere($filter_data['conditions'], $filter_data['params']);
        }

        //this_template
        if ($this_template === true) {
            $data_model->andWhere(['AND', $extension_copy->getTableName() . '.this_template = "' . \EditViewModel::THIS_TEMPLATE_TEMPLATE . '" ']);
        } elseif ($this_template === false) {
            $data_model->andWhere(['AND', $extension_copy->getTableName() . '.this_template = "' . \EditViewModel::THIS_TEMPLATE_MODULE . '" OR ' . $extension_copy->getTableName() . '.this_template is null']);
        } elseif ($this_template === null) {
            $data_model->andWhere([
                'AND',
                $extension_copy->getTableName() . '.this_template = "' . \EditViewModel::THIS_TEMPLATE_TEMPLATE . '" OR ' . $extension_copy->getTableName() . '.this_template = "' . \EditViewModel::THIS_TEMPLATE_MODULE . '" OR ' . $extension_copy->getTableName() . '.this_template is null'
            ]);
        }

        $data_model->andWhere(['AND', $extension_copy->getTableName() . '.process_id != ' . ProcessModel::getInstance()->process_id]);

        $data_model
            ->setFromFieldTypes()
            ->setCollectingSelect()
            ->setGroup();

        //order
        $_GET['sort'] = '{"module_title":"a"}';
        \Sorting::getInstance()->setParamsFromUrl();
        $data_model->setOrder($data_model->getOrderFromSortingParams());

        //participant only
        if (\Yii::app()->controller->module->dataIfParticipant() && ($extension_copy->isParticipant() || $extension_copy->isResponsible())) {
            $data_model->setOtherPartisipantAllowed($extension_copy->copy_id);
        }

        if (!\Yii::app()->controller->module->dataIfParticipant() && ($extension_copy->isParticipant() || $extension_copy->isResponsible())) {
            $data_model->setDataBasedParentModule($extension_copy->copy_id);
        }

        $data_model = $data_model->findAll();

        foreach ($data_model as $model) {
            $result[$model['process_id']] = $model['module_title'];
        }

        return $result;
    }

    private function getResponsibleFromRelatedModule($data_id)
    {
        $process_model = $this;

        if ($data_id != $this->process_id) {
            $process_model = ProcessModel::model()->findByPk($data_id);
        }

        if ($process_model->related_module == false) {
            return false;
        }

        $module_tables_model = \ModuleTablesModel::getRelateModel($process_model->related_module, \ExtensionCopyModel::MODULE_PROCESS);
        if ($module_tables_model == false) {
            return false;
        }

        $data_model = (new \DataModel())
            ->setFrom('{{' . $module_tables_model->table_name . '}}')
            ->setWhere($module_tables_model->relate_field_name . '=:relate_field_name', [':relate_field_name' => $process_model->process_id])
            ->findRow();

        if ($data_model == false || empty($data_model[$module_tables_model->parent_field_name])) {
            return false;
        }

        $participant_model = \ParticipantModel::model()->find([
                'condition' => 'copy_id=:copy_id AND data_id=:data_id AND responsible="1"',
                'params'    => [
                    ':copy_id' => $process_model->related_module,
                    ':data_id' => $data_model[$module_tables_model->parent_field_name],
                ]
            ]
        );

        return ($participant_model ? $participant_model : false);
    }

    private function getResponsileFromProcess($process_id)
    {
        $participant_model = \ParticipantModel::model()->find([
                'condition' => 'copy_id=:copy_id AND data_id=:data_id AND responsible="1"',
                'params'    => [
                    ':copy_id' => \ExtensionCopyModel::MODULE_PROCESS,
                    ':data_id' => $process_id,
                ]
            ]
        );

        return ($participant_model ? $participant_model : false);
    }

    /**
     * getParticipantModelByParticipantTypeConst
     *
     * @param $process_id
     * @return mixed - null|participantModel|false
     */
    public function getResponsileByParticipantTypeConst($data_id, $ug_id)
    {
        switch ($ug_id) {
            case \ParticipantConstModel::TC_RELATE_RESPONSIBLE:
                return $this->getResponsibleFromRelatedModule($data_id);
            case \ParticipantConstModel::TC_RESPONSIBLE_FOR_PROCESS:
                return $this->getResponsileFromProcess($data_id);
        }
    }

    /**
     * validateBeforeCreateFromTemplate - валидация данных перед созданием процесса из шаблона
     *
     * @param array|string $method_name_list
     * @return $this
     */
    public function validateBeforeCreateFromTemplate($method_name_list = ['checkCreateFromTemplateProcess', 'checkCreateFromTemplateBpmParams', 'checkCreateFromTemplateParticipantTypeConst'])
    {
        $method_name_list = (array)$method_name_list;

        $process_validate_model = (new ProcessValidateModel())->setProcessModel($this);

        foreach ($method_name_list as $method_name) {
            $process_validate_model->{$method_name}();
        }

        $result = $process_validate_model->getResult();

        if ($result['status'] == false) {
            foreach ($result['messages'] as $attribute => $message) {
                if ($this->hasErrors($attribute)) {
                    continue;
                }
                $this->addError($attribute, $message);
            }
        }

        return $this;
    }

    /**
     * replaceResponsibleForProcessFromBindingObject - замена константы-участника в Процессе или операторе реальным ответственным
     *
     * @param string $from_data_type : process|operations
     */
    public function replaceResponsibleByParticipantTypeConst($from_data_type)
    {
        $method_name = 'replaceResponsibleFor' . ucfirst($from_data_type);

        $this->{$method_name}();

        return $this;
    }

    /**
     * replaceResponsibleForProcess - замена константы-участника в процессе
     *
     * @return $this
     */
    private function replaceResponsibleForProcess()
    {
        $process_id = ProcessModel::getInstance()->process_id;

        $participant_model_const = ParticipantModel::findTypeConstByEntity(\ExtensionCopyModel::MODULE_PROCESS, $process_id);

        if ($participant_model_const == false) {
            return;
        }

        $participant_model_related = $this->getResponsileByParticipantTypeConst($process_id, $participant_model_const->ug_id);

        //подменяет Константу участником из связанного обьекта
        ParticipantModel::replaceParticipantConstToParticipantUser($participant_model_const, $participant_model_related);

        return $this;
    }

    /**
     * replaceResponsibleForOperations - замена константы-участника в операторах
     *
     * @return mixed
     */
    private function replaceResponsibleForOperations()
    {
        $participants = [];

        foreach (\ParticipantConstModel::getTypeConstListFull() as $type_const) {
            $participant_model = $this->getResponsileByParticipantTypeConst(ProcessModel::getInstance()->process_id, $type_const);
            if ($participant_model == false) {
                continue;
            }

            $participants[] = [
                'ug_id'      => $type_const,
                'ug_type'    => ParticipantModel::PARTICIPANT_UG_TYPE_CONST,
                'attributes' => [
                    'ug_id'   => $participant_model->ug_id,
                    'ug_type' => $participant_model->ug_type,
                ],
            ];

        }

        if ($participants == false) {
            return $this;
        }

        $vars = [
            'action'     => BpmParamsModel::ACTION_UPDATE,
            'process_id' => ProcessModel::getInstance()->process_id,
            'objects'    => [
                'participants' => $participants,
            ],
        ];

        (new \Process\models\BpmParamsModel())
            ->setVars($vars)
            ->validate()
            ->run()
            ->getResultMessages();

        return $this;
    }

    /**
     * createFromTemplate - Создание процесса из шаблона, всех его операторов, установка статусов...
     *
     * @returm integer $process_id
     */
    public function createFromTemplate()
    {
        if ($this->hasErrors()) {
            return;
        }

        $process_id_old = ProcessModel::getInstance()->process_id;

        $new_module_title = (!empty($this->_vars['process']['module_title']) ? $this->_vars['process']['module_title'] : null);
        $new_b_status = (!empty($this->_vars['process']['b_status']) ? $this->_vars['process']['b_status'] : null);
        $parent_copy_id = (!empty($this->_vars['process']['parent_copy_id']) ? $this->_vars['process']['parent_copy_id'] : null);
        $parent_data_id = (!empty($this->_vars['process']['parent_data_id']) ? $this->_vars['process']['parent_data_id'] : null);
        $rau = (!empty($this->_vars['process']['rau']) ? true : false);

        $extension_copy = \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS);

        // копируем процесс
        $result = \EditViewCopyModel::getInstance($extension_copy)
            ->setThisTemplate(0)
            ->setResponsibleIsActiveUser($rau)
            ->copy($this->process_id, $extension_copy, false, null)
            ->getResult();

        if ($result['status'] == false) {
            $this->addError('relate_object_block', \Yii::t('ProcessModule.messages', 'Not defined Object instance'));

            return;
        }

        $this->_result = $result;
        $process_id = $result['id'][0];

        $process_model = ProcessModel::getInstance($process_id, true);

        if ($new_module_title !== null && $extension_copy->isAutoEntityTitle() == false) {
            $process_model->setAttribute('module_title', $new_module_title);
        }
        $process_model->setAttribute('parent_process_id', $this->process_id);

        $process_model->copyToProcessOperations($process_id_old);

        if (!empty($parent_copy_id) && !empty($parent_data_id)) {
            \Process\models\BindingObjectModel::getInstance()
                ->setVars([
                    'process_id' => $process_id,
                    'action'     => \Process\models\BindingObjectModel::ACTION_UPDATE,
                    'attributes' => [
                        'copy_id' => $parent_copy_id,
                        'data_id' => $parent_data_id,
                    ]
                ])
                ->setRelateCopyId($parent_copy_id)
                ->run();
        }

        if ($new_b_status == self::B_STATUS_IN_WORK) {
            $bo_model = BindingObjectModel::getInstance()
                ->setVars(['action' => BindingObjectModel::ACTION_CHECK, 'process_id' => ProcessModel::getInstance()->process_id])
                ->validate()
                ->run()
                ->getResult();

            if ($bo_model['status'] == false) {
                $new_b_status = self::B_STATUS_ZERO;
            }
        }

        $process_model->setAttribute('b_status', $new_b_status);
        $process_model->save();

        if ($process_id_old) {
            ProcessModel::getInstance($process_id_old);
        }

        return $process_id;
    }

    /**
     * copyToProcessOperations - копирование операторов процесса из одного процесса в другой
     *
     * @param $process_id_old
     * @param $process_id
     */
    public function copyToProcessOperations($process_id_from)
    {
        // copy operations
        $process_model = ProcessModel::getInstance();

        \Process\models\OperationsModel::model()->saveNewOperations($process_id_from, $process_model->process_id, $process_model->getSchema(), true);

        return $this;
    }

    /**
     * insertBindingObject - сохранение сущности связанного обьекта (после создания нового процесса)
     *
     * @return $this
     */
    public function insertBindingObject()
    {
        if ($this->hasErrors()) {
            return false;
        }

        $process_id = ProcessModel::getInstance()->process_id;

        if (empty($this->_vars['bpm_params'])) {
            return false;
        }

        $bpm_params = $this->_vars['bpm_params'];
        $bpm_params['process_id'] = $process_id;

        $result = (new \Process\models\BpmParamsModel())
            ->setVars($bpm_params)
            ->validate()
            ->run()
            ->getResultMessages();

        if ($result['status'] == false) {
            static::addActivityMessageIfEmptyRelateOblect();

            return false;
        }

        return true;
    }

    /**
     * запускает текущий процесс
     */
    public function runProcess()
    {
        if ($this->getBStatus() != ProcessModel::B_STATUS_IN_WORK) {
            $this->updateBStatus(ProcessModel::B_STATUS_IN_WORK);
        }

        SchemaModel::getInstance()->setOperationsExecutionStatus();

        return $this;
    }

    /**
     * runAlertProcess - запускает "чужой процесс"
     *
     * @param $process_id
     * @return bool
     */
    public static function runAlertProcess($process_id)
    {
        $process_id_old = ProcessModel::getInstance();

        // set new ProcessModel
        if ($process_id_old != $process_id) {
            ProcessModel::getInstance($process_id, true);
        }

        if (ProcessModel::getInstance()->getBStatus() != ProcessModel::B_STATUS_IN_WORK) {
            ProcessModel::getInstance()->updateBStatus(ProcessModel::B_STATUS_IN_WORK);
        }

        SchemaModel::getInstance()->setOperationsExecutionStatus(false);

        // set old ProcessModel
        if ($process_id_old && $process_id_old != $process_id) {
            ProcessModel::getInstance($process_id_old, true);
        }

        return true;

    }

    /**
     * findProcessIdTemplate - возвращает
     *
     * @param $process_id
     */
    public static function findProcessIdTemplate($process_id)
    {
        if (empty($process_id)) {
            return;
        }

        $result = \DataModel::getInstance()->setText('
						SELECT process_id FROM {{process}}
						WHERE process_id = (
								SELECT parent_process_id
								FROM {{process}}
								WHERE process_id = ' . $process_id . '
						)'
        )->findScalar();

        if (empty($result)) {
            return;
        } else {
            return $result;
        }
    }

    /**
     * isTemplateProcess - проверка на шаблон процесса
     *
     * @param $process_id
     */
    public static function isTemplateProcess($process_id)
    {
        if ($process_id) {
            return;
        }

        $result = \DataModel::getInstance()->setText('SELECT this_template FROM {{process}} WHERE process_id = ' . $process_id)->findScalar();

        if (empty($result) || $result === "0" || $result === null) {
            return false;
        } else {
            return true;
        }
    }

    public static function getModuleNameList($return_first = false)
    {
        $extension_models = \ExtensionCopyModel::model()
            ->modulesActive()
            ->setAccess()
            ->setScopesWithOutId([\ExtensionCopyModel::MODULE_PROCESS, \ExtensionCopyModel::MODULE_REPORTS])
            ->findAll([
                'order' => 'title',
            ]);

        if (empty($extension_models)) {
            return [];
        }

        if ($return_first) {
            return $extension_models[0];
        }

        $result = [];

        foreach ($extension_models as $module) {
            $result[$module['copy_id']] = $module['title'];
        }

        return $result;
    }

    /**
     * getOperations
     *
     * @param bool $return_chidren_models
     */
    public function getOperations($return_chidren_models = true)
    {
        $operation_list = [];
        $operations = OperationsModel::model()->findAll('process_id=:process_id', [':process_id' => $this->process_id]);

        if ($return_chidren_models) {
            if (!empty($operations)) {
                foreach ($operations as $operation_model) {
                    $model = OperationsModel::getChildrenModel($operation_model->element_name);
                    $operation_list[] = $model->setOperationsModel($operation_model);
                }
            }
        } else {
            $operation_list = $operations;
        }

        return $operation_list;
    }

    /**
     * getOperationByUniqueIndex
     */
    public function getOperationByUniqueIndex($uniqueIndex)
    {
        return OperationsModel::model()->find(
            'process_id=:process_id AND unique_index=:unique_index', [
                ':process_id' => $this->process_id,
                ':unique_index' => $uniqueIndex,
            ]);
    }


    /**
     * isSetRelateObjectInstance - проверяет наличие связаного єкзепляра объекта в процессе
     *
     * @param $copy_id
     * @return bool
     */
    public static function isSetRelateObjectInstance($copy_id)
    {
        $count = \DataModel::getInstance()
            ->setFrom('{{process}}')
            ->setWhere(BindingObjectModel::RM_FIELD_NAME . '=:copy_id AND this_template = "1"', [':copy_id' => (integer)$copy_id])
            ->findCount();

        return (boolean)$count;
    }

    /**
     * addActivityMessageIfEmptyRelateOblect - Добавляет уведомление о необходимости ответственному указать связанный объект
     *
     * @param bool $check_zero
     */
    public static function addActivityMessageIfEmptyRelateOblect($check_zero = true)
    {
        // проверка
        if ($check_zero) {
            $bo_model = BindingObjectModel::getInstance()
                ->setVars(['process_id' => ProcessModel::getInstance()->process_id]);
            $is = $bo_model->isSetRelateByProcessId();
            if ($is === null || $is === true) {
                return;
            }
        }
        $participant_model = \ParticipantModel::getParticipants(
            \ExtensionCopyModel::MODULE_PROCESS,
            ProcessModel::getInstance()->process_id,
            null, true, true);

        if (empty($participant_model)) {
            return;
        }

        if (empty($participant_model)) {
            return;
        }
        $user_id = $participant_model->ug_id;

        $params = [
            '{user_id}'           => $user_id,
            '{module_data_title}' => ProcessModel::getInstance()->module_title,
        ];

        $history_model = new \History();
        $history_model
            ->setAddRealteHistoryData(false)
            ->addToHistory(
                \HistoryMessagesModel::MT_PROCESS_RELATE_OBJECT_EMPTY,
                \ExtensionCopyModel::MODULE_PROCESS,
                ProcessModel::getInstance()->process_id,
                $params,
                false, false, true
            );

        if (!empty($user_id)) {
            $history_mark = new \HistoryMarkViewModel();
            $history_mark->user_id = $user_id;
            $history_mark->history_id = $history_model->getLastHistoryId();
            $history_mark->save();
        }
    }

    /**
     * Возвращает список операторов схемы
     *
     * @return array
     */
    public function getSchemaOperations()
    {
        $schema = $this->getSchema();

        if (!$schema) {
            return [];
        }

        $result = [];
        foreach ($schema as $responsible) {
            if (!$responsible['elements']) {
                continue;
            }

            foreach ($responsible['elements'] as $operation) {
                $result[] = $operation;
            }

        }

        return $result;
    }

}
