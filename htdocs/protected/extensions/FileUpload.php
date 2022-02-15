<?php

class FileUpload
{
    const THUMB_SCENARIO_ACTIVITY = 'activity';
    const THUMB_SCENARIO_PROFILE = 'profile';
    const THUMB_SCENARIO_UPLOAD = 'upload';
    const THUMB_SCENARIO_ATTACHMENTS = 'attachments';
    const THUMB_SCENARIO_COPY = 'copy';
    const THUMB_SCENARIO_AVATAR = 'avatar';

    private $data;

    /**
     * @var Validate
     */
    private $validator;

    /**
     * @var UploadsModel
     */
    private $uploadsModel;

    /**
     * FileUpload constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->validator = new Validate();
    }

    private function initUploadsModel()
    {
        if (array_key_exists('id', $this->data)) {
            $this->uploadsModel = UploadsModel::model()->findByPk($this->data['id']);

            if (!$this->uploadsModel) {
                $this->addValidateResult('e', Yii::t('messages', 'Data not available'));

                return;
            }

        } else {
            $this->uploadsModel = new UploadsModel();
        }
    }

    /**
     * @param $key
     * @return mixed
     */
    private function getDataValue($key)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }
    }

    /**
     * @return UploadsModel
     */
    public function getUploadsModel()
    {
        return $this->uploadsModel;
    }

    /**
     * @return Validate
     */
    public function getValidator(): Validate
    {
        return $this->validator;
    }

    /**
     * Загрузка файла на сервер
     */
    public function upload()
    {
        $this->initUploadsModel();

        if ($this->validator->beMessages(Validate::TM_ERROR)) {
            return false;
        }

        $this->uploadsModel->setScenario('upload');
        $this->uploadsModel->setThumbScenario($this->getDataValue('thumb_scenario'));
        $this->uploadsModel->setFileType($this->getDataValue('file_type'));
        $this->uploadsModel->setFormat($this->getDataValue('format'));
        $this->uploadsModel->setImageSizePixels($this->getDataValue('image_size_pixels'));

        $this->uploadsModel->file_source = UploadsModel::SOURCE_MODULE;
        $this->uploadsModel->status = 'temp';
        $this->uploadsModel->copy_id = $this->getDataValue('copy_id');

        if (($this->getDataValue('thumb_scenario') == self::THUMB_SCENARIO_PROFILE) || ($this->getDataValue('thumb_scenario') == self::THUMB_SCENARIO_AVATAR)) {
            $this->uploadsModel->setAddUpdateImage('square');
        }

        if (!$this->uploadsModel->save()) {
            return false;
        }

        return true;
    }

    /**
     * @return string|string[]|null
     */
    public function getValidateResultHtml()
    {
        return $this
            ->validator
            ->addValidateResult('e', CHtml::errorSummary($this->uploadsModel))
            ->getValidateResultHtml();
    }

    /**
     * Определение пармера миниатюры относительно сценария
     *
     * @return int|null
     */
    private function getThumbScenarioSize()
    {
        switch ($this->getDataValue('thumb_scenario')) {
            case self::THUMB_SCENARIO_ACTIVITY:
                $thumb_scenario_size = 42;
                break;
            case self::THUMB_SCENARIO_PROFILE:
                $thumb_scenario_size = 140;
                break;
            case self::THUMB_SCENARIO_UPLOAD:
            case self::THUMB_SCENARIO_ATTACHMENTS:
            case self::THUMB_SCENARIO_COPY:
                $thumb_scenario_size = 60;
                break;
            case self::THUMB_SCENARIO_AVATAR:
                $thumb_scenario_size = 85;
                break;
            default:
                $thumb_scenario_size = null;
        }

        return $thumb_scenario_size;
    }

    /**
     * @return array|mixed|string
     * @throws Exception
     */
    public function getView()
    {
        $this->uploadsModel->refresh();
        $view = '';

        if (isset($this->data['get_view']) && (boolean)$this->data['get_view']) {
            if (isset($this->data['copy_id'])) {
                $extension_copy = ExtensionCopyModel::model()->findByPk($this->data['copy_id']);
            }

            if ($this->data['thumb_scenario'] == self::THUMB_SCENARIO_ATTACHMENTS) {
                $view = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.Attachments.Attachments'),
                    [
                        'view'           => 'element',
                        'schema'         => $extension_copy->getAttachmentsField(),
                        'extension_copy' => $extension_copy,
                        'extension_data' => null,
                        'upload_value'   => $this->uploadsModel,
                    ],
                    true);
            } elseif ($this->data['thumb_scenario'] == self::THUMB_SCENARIO_ACTIVITY) {
                $view = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.Activity.Activity'),
                    [
                        'view'                         => 'attachments_one',
                        'schema'                       => $extension_copy->getFieldSchemaParamsByType('activity'),
                        'extension_copy'               => $extension_copy,
                        'upload_data'                  => $this->uploadsModel,
                        'attachents_buttons'           => ['download_file', 'delete_file'],
                        'attachments_image_thumb_size' => 60,
                    ],
                    true);
            } elseif ($this->data['thumb_scenario'] == self::THUMB_SCENARIO_PROFILE) {
                $extension_copy = ExtensionCopyModel::model()->findByPk(ExtensionCopyModel::MODULE_STAFF);
                $params = ['elements' => ['field' => $extension_copy->getFieldSchemaParams('ehc_image1')]];

                $alias = 'evm_' . $extension_copy->copy_id;
                $dinamic_params = [
                    'tableName' => $extension_copy->getTableName(null, false, true),
                    'params'    => Fields::getInstance()->getActiveRecordsParams($params),
                ];

                $extension_data = EditViewModel::modelR($alias, $dinamic_params)->findByPk(WebUser::getUserId());
                $extension_data->scenario = 'update';
                $extension_data->setElementSchema($extension_copy->getSchemaParse());
                $extension_data->extension_copy = $extension_copy;
                $extension_data->setMyAttributes(['ehc_image1' => $this->uploadsModel->id]);

                if ($extension_data->save()) {
                    ExtensionModel::model()->findByPk(ExtensionModel::MODULE_USERS)->getModule();
                    $view = [
                        'avatar_140' => ProfileModel::getFileBlockAvatar(),
                        'avatar_32'  => (new AvatarModel())->setDataArrayFromUserId()->getAvatar(),
                    ];
                }
            }
        }

        return $view;
    }

    /**
     * @return mixed
     */
    public function getFileInfo()
    {
        return $this
            ->uploadsModel
            ->setFileType($this->data['file_type'])
            ->getFileInfo($this->getThumbScenarioSize());
    }
}
