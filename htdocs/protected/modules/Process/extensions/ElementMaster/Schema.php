<?php
/**
 * Schema
 *
 * @autor Alex R.
 */

namespace Process\extensions\ElementMaster;

use Process\models\ArrowModel;
use Process\models\OperationDataRecordModel;

class Schema
{

    private $_schema;

    public static function getInstance()
    {
        return new self();
    }

    private function setSchema()
    {
        $this->_schema = \Process\models\SchemaModel::getInstance()->getSchema();

        return $this;
    }

    private function getSchema()
    {
        if ($this->_schema === null) {
            $this->setSchema();
        }

        return $this->_schema;
    }

    /**
     * getOperations  - список операторов
     */
    public function getOperations($schema = null)
    {
        $result = [];

        if ($schema === null) {
            $schema = $this->getSchema();
        }

        if (empty($schema)) {
            return $result;
        }

        foreach ($schema as $responsible) {
            if ($responsible['type'] == \Process\models\SchemaModel::ELEMENT_TYPE_RESPONSIBLE && isset($responsible['elements']) && !empty($responsible['elements'])) {
                foreach ($responsible['elements'] as $element) {
                    if (isset($element['type']) && $element['type'] == \Process\models\SchemaModel::ELEMENT_TYPE_OPERATION) {
                        $result[] = $element;
                    }
                }
            }
        }

        $this->operationSortingByCol($result);

        return $result;
    }

    /**
     * operationSorting - сортировка операторов по колонках
     *
     * @param $operations
     */
    private function operationSortingByCol(&$operations)
    {
        if ($operations == false) {
            return;
        }

        // sorting function
        $sorting_function = function ($a, $b) {
            if ($a['coordinates']['col'] == $b['coordinates']['col']) {
                return 0;
            }

            return ($a['coordinates']['col'] < $b['coordinates']['col'] ? -1 : 1);
        };

        usort($operations, $sorting_function);
    }

    /**
     * getResponsibleList  - список ответвенных
     */
    public function getResponsibleList($schema = null)
    {
        $result = [];

        if ($schema === null) {
            $schema = $this->getSchema();
        }

        if (empty($schema)) {
            return $result;
        }

        foreach ($schema as $responsible) {
            $result[] = [
                'ug_id'   => $responsible['ug_id'],
                'ug_type' => $responsible['ug_type'],
            ];
        }

        return $result;
    }

    /**
     * getOperationResponsibleList  - список ответвенных каждого оператора
     */
    public function getOperationResponsibleList($schema = null)
    {
        $result = [];

        if ($schema === null) {
            $schema = $this->getSchema();
        }

        if (empty($schema)) {
            return $result;
        }

        foreach ($schema as $responsible) {
            if ($responsible['type'] == \Process\models\SchemaModel::ELEMENT_TYPE_RESPONSIBLE && isset($responsible['elements']) && !empty($responsible['elements'])) {
                foreach ($responsible['elements'] as $element) {
                    if (isset($element['type']) && $element['type'] == \Process\models\SchemaModel::ELEMENT_TYPE_OPERATION) {
                        $result[$element['unique_index']] = [
                            'ug_id'   => $responsible['ug_id'],
                            'ug_type' => $responsible['ug_type'],
                            'flag'    => (!empty($responsible['flag']) ? $responsible['flag'] : null)
                        ];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * getOperationResponsible
     */
    public function getOperationResponsible($schema = null, $unique_index)
    {
        $result = null;

        if ($schema === null) {
            $schema = $this->getSchema();
        }
        if (empty($schema)) {
            return $result;
        }

        $responsible_list = $this->getOperationResponsibleList($schema);

        if (empty($responsible_list)) {
            return $result;
        }

        foreach ($responsible_list as $ui => $value) {
            if ($ui == $unique_index) {
                $result = $value;
                break;
            }
        }

        return $result;
    }

    /**
     * getOperationsByResponsible - возвращает список операторов по ответственному
     */
    public function getOperationsByResponsible($ug_id, $ug_type, $schema = null)
    {
        $result = [];

        if ($schema === null) {
            $schema = $this->getSchema();
        }

        if (empty($schema)) {
            return $result;
        }

        foreach ($schema as $responsible) {
            if (
                $responsible['type'] == \Process\models\SchemaModel::ELEMENT_TYPE_RESPONSIBLE &&
                $responsible['ug_id'] == $ug_id &&
                $responsible['ug_type'] == $ug_type
            ) {
                if (isset($responsible['elements']) && !empty($responsible['elements'])) {
                    foreach ($responsible['elements'] as $element) {
                        if (isset($element['type']) && $element['type'] == \Process\models\SchemaModel::ELEMENT_TYPE_OPERATION) {
                            $result[] = $element['unique_index'];
                        }
                    }
                }
                break;
            }
        }

        return $result;
    }

    /**
     * getOperationsUniqueIndex  - список "unique_index" операторов
     */
    public function getOperationsUniqueIndex($schema = null)
    {
        $result = [];

        if ($schema === null) {
            $schema = $this->getSchema();
        }

        $operations = $this->getOperations($schema);

        foreach ($operations as $operation) {
            if (!empty($operation['unique_index'])) {
                $result[] = $operation['unique_index'];
            }
        }

        return $result;
    }

    public function unactiveSheduledInOperationBegin(&$operation_schema)
    {
        foreach ($operation_schema as &$element) {
            if ($element['type'] == \Process\models\OperationBeginModel::ELEMENT_START_ON_TIME) {
                $element['value'] = null;
                $element['elements'] = [];
            }
        }
    }

    /**
     * getDefaultSchema - схема операторов по умолчанию
     *
     * @return array
     */
    public function getDefaultSchema()
    {
        $responrible = \Process\models\ParticipantModel::getParticipant(\ExtensionCopyModel::MODULE_PROCESS, \Process\models\ProcessModel::getInstance()->process_id, '1');

        $schema = [
            [
                'type'         => \Process\models\SchemaModel::ELEMENT_TYPE_RESPONSIBLE,
                'ug_id'        => (!empty($responrible) ? $responrible['ug_id'] : null),
                'ug_type'      => (!empty($responrible) ? $responrible['ug_type'] : null),
                'flag'         => null, //participant flag
                'title'        => (new \Process\models\SchemaModel())->getParticipantTitle($responrible['ug_id'], $responrible['ug_type']),
                'unique_index' => md5(date_format(date_create(), 'YmdHisu')) . '99',
                'elements'     => [
                    [
                        'type'                => \Process\models\SchemaModel::ELEMENT_TYPE_OPERATION,
                        'name'                => \Process\models\OperationsModel::ELEMENT_BEGIN,
                        'title'               => null,
                        'unique_index'        => md5(date_format(date_create(), 'YmdHisu')) . '100',
                        'unique_index_parent' => [],
                        'coordinates'         => [
                            'row' => 1,
                            'col' => 1,
                        ],
                        'arrows'              => [
                            [
                                'unique_index' => ($uniqueIndexEnd = (md5(date_format(date_create(), 'YmdHisu')) . '101')), // индекс оператора, куда рисуется стрелка
                                'type'         => \Process\models\ArrowModel::TYPE_INNER,            // для "внешняя связь"
                                'title'        => '',           //--
                            ],
                        ],
                    ],
                    [
                        'type'                => \Process\models\SchemaModel::ELEMENT_TYPE_OPERATION,
                        'name'                => \Process\models\OperationsModel::ELEMENT_END,
                        'title'               => null,
                        'unique_index'        => $uniqueIndexEnd,
                        'unique_index_parent' => [],
                        'coordinates'         => [
                            'row' => 1,
                            'col' => 2,
                        ],
                        'arrows'              => [
                            [
                                'unique_index' => '', // індекс опретора, куда малюєм стрілку
                                'type'         => '',         // для "внешняя связь"
                                'title'        => '',        //--
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $schema;
    }

    /**
     * getDefaultSchema - схема по умолчанию блока Ответственный
     *
     * @return array
     */
    public function getDefaultSchemaResponsible($ug_id, $ug_type)
    {
        return [
            'type'         => \Process\models\SchemaModel::ELEMENT_TYPE_RESPONSIBLE,
            'ug_id'        => $ug_id,
            'ug_type'      => $ug_type,
            'flag'         => null, //participant flag
            'unique_index' => md5(date_format(date_create(), 'YmdHisu') . $ug_id . $ug_type) . '99',
            'elements'     => [],
        ];
    }











    /**************************************************
     * DEFAULT Operation Schema
     *************************************************/

    /**
     * getDefaultSchemaOperation
     */
    public function getDefaultSchemaOperation($element_name)
    {
        $element_name = str_replace('_', '', $element_name);
        $method = 'getDefaultSchemaOperation' . $element_name;
        if (!method_exists($this, $method)) {
            return [];
        }
        $schema = $this->{$method}();

        return $schema;
    }

    /**
     * getDefaultSchemaOperation - Begin
     */
    private function getDefaultSchemaOperationBegin()
    {
        $schema = [
            [
                'type'  => \Process\models\OperationBeginModel::ELEMENT_PREVIOUS_PROCESS,
                'value' => null,
            ],
            [
                'type'     => \Process\models\OperationBeginModel::ELEMENT_START_ON_TIME,
                'value'    => null,
                'elements' => [],
            ],
        ];

        return $schema;
    }

    /**
     * getDefaultSchemaOperation - End
     */
    private function getDefaultSchemaOperationEnd()
    {
        $schema = [
            [
                'type'  => \Process\models\OperationEndModel::ELEMENT_NEXT_PROCESS,
                'value' => null,
            ],
        ];

        return $schema;
    }

    /**
     * getDefaultSchemaOperation - And
     */
    private function getDefaultSchemaOperationAnd()
    {
        $schema = [
            [
                'type'        => \Process\models\OperationAndModel::ELEMENT_NUMBER_BRANCHES,
                'value'       => 1,
                'show_params' => 1,
            ],
        ];

        return $schema;
    }

    /**
     * getDefaultSchemaOperation - Task
     */
    private function getDefaultSchemaOperationTask()
    {
        $schema = [
            [
                'type'       => \Process\models\OperationTaskBaseModel::COPY_ID,
                'value'      => null,
                'is_element' => false,
            ],
            [
                'type'       => \Process\models\OperationTaskBaseModel::CARD_ID,
                'value'      => null,
                'is_element' => false,
            ],
            [
                'type'  => \Process\models\OperationTaskBaseModel::ELEMENT_SDM_OPERATION_TASK,
                'value' => null,
            ],
            [
                'type'  => \Process\models\OperationTaskBaseModel::ELEMENT_EXECUTION_TIME,
                'value' => [
                    \Process\models\OperationTaskBaseModel::ELEMENT_EXECUTION_TIME_DAY => 0,
                ]
            ]
        ];

        return $schema;
    }

    /**
     * getDefaultSchemaOperation - Agreetment
     */
    private function getDefaultSchemaOperationAgreetment()
    {
        $schema = [
            [
                'type'       => \Process\models\OperationTaskBaseModel::COPY_ID,
                'value'      => null,
                'is_element' => false,
            ],
            [
                'type'       => \Process\models\OperationTaskBaseModel::CARD_ID,
                'value'      => null,
                'is_element' => false,
            ],
            [
                'type'  => \Process\models\OperationTaskBaseModel::ELEMENT_SDM_OPERATION_TASK,
                'value' => null,
            ],
            [
                'type'  => \Process\models\OperationAgreetmentModel::ELEMENT_TYPE_AGREETMENT,
                'value' => \Process\models\OperationAgreetmentModel::TYPE_AGREETMENT_INTERNAL,
            ],
            [
                'type'  => \Process\models\OperationAgreetmentModel::ELEMENT_EMAIL,
                'value' => null,
            ],
            [
                'type'  => \Process\models\OperationTaskBaseModel::ELEMENT_EXECUTION_TIME,
                'value' => [
                    \Process\models\OperationTaskBaseModel::ELEMENT_EXECUTION_TIME_DAY => 0,
                ]
            ]
        ];

        return $schema;
    }

    /**
     * getDefaultSchemaOperation - Condition
     */
    private function getDefaultSchemaOperationCondition()
    {
        $schema = [
            [
                'type'  => \Process\models\OperationConditionModel::ELEMENT_OBJECT_NAME,
                'value' => null,
            ],
            [
                'type'  => \Process\models\OperationConditionModel::ELEMENT_RELATE_MODULE,
                'value' => null,
            ],
            [
                'type'  => \Process\models\OperationConditionModel::ELEMENT_FIELD_NAME,
                'value' => null,
            ],
            [
                'type'                                                                  => \Process\models\OperationConditionModel::ELEMENT_VALUE_SCALAR,
                'value'                                                                 => null,
                \Process\models\OperationConditionModel::ELEMENT_VALUE_SCALAR_CONDITION => null,
                \Process\models\OperationConditionModel::ELEMENT_VALUE_SCALAR_VALUE     => null,
                'arrow_status'                                                          => ArrowModel::STATUS_UNACTIVE,
            ],
        ];

        return $schema;
    }

    /**
     * getDefaultSchemaOperation - DataRecord
     */
    private function getDefaultSchemaOperationDataRecord()
    {
        $schema = [
            [
                'type'  => OperationDataRecordModel::ELEMENT_TYPE_OPERATION,
                'value' => OperationDataRecordModel::ELEMENT_TO_CREATING_RECORD,
            ],
            [
                'type'  => OperationDataRecordModel::ELEMENT_CALL_EDIT_VIEW,
                'value' => OperationDataRecordModel::ELEMENT_CEV_CALL,
            ],
            [
                'type'  => OperationDataRecordModel::ELEMENT_MODULE_NAME,
                'value' => null,
            ],
            /*
            array(
                'type' => OperationDataRecordModel::ELEMENT_RECORD_NAME_LIST,
                'value' => null,
                'param_id' => null
            ),
            */
            [
                'type'  => OperationDataRecordModel::ELEMENT_RECORD_NAME_TEXT,
                'value' => OperationDataRecordModel::getRecordNameIndexName(),
            ],
            [
                'type'  => OperationDataRecordModel::ELEMENT_REQUIRED_FIELDS,
                'value' => null,
            ],
            [
                'type'  => OperationDataRecordModel::ELEMENT_MESSAGE,
                'value' => null,
            ],
            /*
            array(
                'type' => OperationDataRecordModel::ELEMENT_VALUE_BLOCK,
                'value' => null,
                'field_name' => null,
                'counter' => null,
            ),
            array(
                'type' => OperationDataRecordModel::ELEMENT_LABEL_ADD_VALUE,
            ),
            */
        ];

        return $schema;
    }

    /**
     * getDefaultSchemaOperation - Timer
     */
    private function getDefaultSchemaOperationTimer()
    {
        $schema = [
            [
                'type'     => \Process\models\OperationBeginModel::ELEMENT_START_ON_TIME,
                'value'    => \Process\models\OperationBeginModel::START_ON_TIME_ONE,
                'elements' => [
                    [
                        'type'  => \Process\models\OperationBeginModel::ELEMENT_DATE,
                        'title' => \Yii::t('ProcessModule.base', 'Start date'),
                        'value' => null,
                    ]
                ],
            ],
        ];

        return $schema;
    }

    /**
     * getDefaultSchemaOperationNotification - Notification
     */
    private function getDefaultSchemaOperationNotification()
    {
        $nf_model = \Process\models\OperationNotificationFactoryModel::getInstance()->setDefaultVars();

        $schema = [
            [
                'type'  => \Process\models\OperationNotificationFactoryModel::ELEMENT_TYPE_MESSAGE,
                'value' => $nf_model->getActiveTypeMessage(),
            ],
            [
                'type'  => \Process\models\OperationNotificationFactoryModel::ELEMENT_SERVICE_NAME,
                'value' => $nf_model->getActiveServiceName(),
            ],
            [
                'type'  => \Process\models\OperationNotificationFactoryModel::ELEMENT_SERVICE_VARS,
                'value' => $nf_model->getActiveServiceModel()->getDefaultSchema(),
            ],

        ];

        return $schema;
    }

    /**
     * getDefaultSchemaOperation - Scenario
     */
    private function getDefaultSchemaOperationScenario()
    {
        $schema = [
            [
                'type'  => \Process\models\OperationScenarioModel::ELEMENT_SCRIPT_TEXT,
                'value' => '',
            ],
            [
                'type'  => \Process\models\OperationScenarioModel::ELEMENT_SCRIPT_TYPE,
                'value' => \Process\models\OperationScenarioModel::SCRIPT_TYPE_PHP,
            ],
        ];

        return $schema;
    }

}
