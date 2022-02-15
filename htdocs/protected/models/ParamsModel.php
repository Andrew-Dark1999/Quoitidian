<?php

class ParamsModel extends ActiveRecord
{
    //public $params_id;
    public $title;

    public $value;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{params}}';
    }

    public function rules()
    {
        return [
            ['title, value', 'required'],
            ['title', 'length', 'max' => 255],
            ['params_id, title, value', 'safe', 'on' => 'search'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'params_id' => 'Params',
            'title'     => Yii::t('base', 'Title'),
            'value'     => Yii::t('base', 'Value'),
        ];
    }

    public function titleName($title)
    {
        $this->getDbCriteria()->mergeWith([
            'condition' => 'title=:title',
            'params'    => [':title' => $title],
        ]);

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public static function getValueFromModel($title, $params_model = null)
    {
        if ($params_model === null) {
            $params_model = self::model()->titleName($title)->findAll();
        }
        if (empty($params_model)) {
            return;
        }
        foreach ($params_model as $params) {
            if ($params['title'] == $title) {
                return $params['value'];
            }
        }
    }

    public static function getValueArrayFromModel($title, $params_model = null)
    {
        if ($params_model === null) {
            $params_model = self::model()->titleName($title)->find();
        }
        if (empty($params_model)) {
            return;
        }

        return json_decode($params_model['value'], true);
    }

    public function getValueJson()
    {
        return json_decode($this->value, true);
    }

    public static function InsertOrUpdateData($title, $value)
    {
        $params_model = self::model()->titleName($title)->find();
        if (empty($params_model)) {
            $params_model = new ParamsModel();
            $params_model->title = $title;
        }

        if (is_array($value)) {
            $value = json_encode($value);
        }

        $params_model->value = $value;
        $params_model->save();
    }

    public static function loadJsParams()
    {
        $mc = new MessageSource;
        $list = $mc->getMessagesJs();

        $locale = new LocaleCRM();
        $locale_data = $locale->getAllData();

        $confirm_buttons = [
            'OK'     => ['type' => 'button', 'class' => 'btn btn-default yes-button'],
            'Cancel' => ['type' => 'button', 'class' => 'btn btn-default close-button', 'data-dismiss' => 'modal'],
        ];

        $result = [
            'message_dialog_default'            => \Yii::app()->controller->renderPartial(ViewList::getView('dialogs/message'), ['buttons' => []], true),
            'message_dialog_info'               => \Yii::app()->controller->renderPartial(ViewList::getView('dialogs/message'), ['buttons' => Validate::getInstance()->getButtons()], true),
            'message_dialog_confirm'            => \Yii::app()->controller->renderPartial(ViewList::getView('dialogs/message'), ['buttons' => $confirm_buttons], true),
            'message_dialog_upload_select_file' => \Yii::app()->controller->renderPartial(ViewList::getView('dialogs/uploadSelectFile'), [], true),
            'startup_guide'                     => StartupGuideModel::getSteps(),
            'list'                              => $list,
            'locale'                            => $locale_data,
            'global'                            => Yii::app()->params->global,
            'templates'                         => (new \TemplateModel())->prepateDefaultTemplates()->getJsTemplateList(),
            'template_design'                   => self::getTemplateDesignParams(),
            'edit_view'                         => self::getEditViewParams(),
            'quick_view_blocks'                 => QuickViewModel::getInstance()
                ->prepareDataItemsModelList()
                ->getBlockModelListJs(),
        ];

        return $result;
    }

    /**
     * getDefaultMin - возвращает статус для загрузки обновного шаблона
     *
     * @return bool
     */
    public static function getDefaultMin()
    {
        $default_min = \Yii::app()->request->getParam('default_min');

        if ($default_min !== null) {
            return (boolean)$default_min;
        }

        $default_min = \ParamsModel::getValueFromModel('default_min');

        return (boolean)$default_min;
    }

    private static function getTemplateDesignParams()
    {
        $result = [
            'background' => null,
        ];

        $userParams = (new UsersParamsModel())->scopeActiveUser()->find();
        if ($userParams) {
            $result['background'] = $userParams->getBackgroundUrl();
        }

        return $result;
    }

    private static function getEditViewParams()
    {
        $result = [
            'activity_editor' => BlockActivityEditorDefinition::EMOJI,
        ];

        $userParams = (new UsersParamsModel())->scopeActiveUser()->find();
        if ($userParams && $userParams->activity_editor) {
            $result['activity_editor'] = $userParams->activity_editor;
        }

        return $result;
    }
}
