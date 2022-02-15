<?php
/**
 * TData widget
 *
 * @author Alex R.
 * @version 1.0
 */

class TData extends CWidget
{

    // результат отображения, что возвращается: element или primary_link  
    public $result_render = 'element';

    //экземпляр ExtensionCopyModel
    public $extension_copy;

    // Параметры елемента схемы
    public $params = [];

    // Данные поля
    public $value_data;

    // Указывает на активацию провью для изображений и ссылок для файлов
    public $file_link = true;

    // указыват на существование первичного поля
    public $be_primary_field = false;

    // Показывает аватар связаного модуля;
    public $title_add_avatar = false;

    // Показывает аватар связаного модуля;
    public $relate_add_avatar = true;

    // Для файлов возвращаются только ссылки
    public $files_only_url = false;

    // добавляет ссылку для редактирования
    public $primary_link_add = true;

    // указывает, какой линк (с каким набором атрибутов ) будет сформирован для поля с атрибутом is_primary=true     
    public $primary_link;

    // показать Ответственного вместо аватара
    public $avatar_view_responsible = false;

    // разрешает красить елемент (значение) в соответствии его свойств
    public $element_dye = true;

    // список блоков
    public $blocks = [];

    // показывает ссылку для полей  тива СДМ
    public $show_sdm_link = true;

    // this_template
    public $this_template = false;

    // finished_object
    public $finished_object = false;

    // span|img - тег, что будет возвращен для вывода картинки
    public $img_tag = 'span';

    public function init()
    {
        $result = '';
        switch ($this->result_render) {
            case 'element' :
                $result = $this->getTableRow();
                break;
            case 'primary_link' :
                $result = $this->getPrimaryLink();
                break;
        }

        echo $result;
    }

    /**
     * возвращает весь елемент отображения
     */
    private function getTableRow()
    {

        if ($this->file_link) {
            if (!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, Yii::app()->controller->module->getAccessCheckParams('access_id'), Yii::app()->controller->module->getAccessCheckParams('access_id_type'))) {
                $this->file_link = false;
            }
        }

        $element = $this->render('element', [
            'extension_copy'    => $this->extension_copy,
            'params'            => $this->params,
            'value_data'        => $this->value_data,
            'file_link'         => $this->file_link,
            'relate_add_avatar' => $this->relate_add_avatar,
            'element_dye'       => $this->element_dye,
            'blocks'            => $this->blocks,
            'this_template'     => $this->this_template,
            'show_sdm_link'     => $this->show_sdm_link,
            'finished_object'   => $this->finished_object,
        ], true
        );

        //link
        if ($this->primary_link_add && $this->primary_link !== null &&
            (array_key_exists('is_primary', $this->params) && (boolean)$this->params['is_primary'] == true) || // первичное поле
            (ListViewBulder::$primary_link_aded == false && $this->be_primary_field == false && ($this->params['type'] == 'string' || $this->params['type'] == 'numeric') &&
                ($this->params['type_view'] == Fields::TYPE_VIEW_DEFAULT || $this->params['type_view'] == Fields::TYPE_VIEW_EDIT_HIDDEN))) // если нет первичного - строковое, числовое
        {
            if ($this->primary_link != ListViewBulder::PRIMARY_LINK_EDIT_VIEW_SUBMODULE) {
                if ($this->params['type'] == 'relate_string') {
                    $this->primary_link = ListViewBulder::PRIMARY_LINK_LIST_VIEW;
                }
            }

            $element = $this->render('primary_link', [
                'params'         => $this->params,
                'value'          => $element,
                'primary_link'   => $this->primary_link,
                'extension_copy' => $this->extension_copy,
            ], true
            );
            ListViewBulder::$primary_link_aded = true;
        }

        // avatar || participant
        $avatar = '';
        if ($this->params['type'] == 'string' || $this->params['type'] == 'display' || $this->params['type'] == 'relate_string') {
            if (array_key_exists('is_primary', $this->params) && $this->params['is_primary'] == true && $this->relate_add_avatar && $this->extension_copy->isAvatar()) {

                $avatar = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ListView.Elements.Avatar.Avatar'),
                    [
                        'extension_copy'          => $this->extension_copy,
                        'data_array'              => $this->value_data,
                        'thumb_size'              => 32,
                        'avatar_view_responsible' => $this->avatar_view_responsible,
                    ],
                    true);
            }
        }

        return $avatar . $element;
    }

    /**
     * возвращает  елемент отображения в ссылке
     */
    private function getPrimaryLink()
    {
        $element = $this->render('primary_link', [
            'params'         => $this->params,
            'value'          => $this->value_data,
            'primary_link'   => $this->primary_link,
            'extension_copy' => $this->extension_copy,
        ], true
        );

        echo $element;
    }

    public function editViewIsEnable()
    {
        $module_model = $this->extension_copy->getModule();

        return $module_model->edit_view_enable;
    }

    public function isDateTimeAllDay()
    {
        $field_name = $this->params['name'] . '_ad';
        $value = $this->value_data[$field_name];

        return (bool)$value;
    }

    public function getDateTimeFormat()
    {
        $date_time = $this->value_data[$this->params['name']];
        $result = Helper::formatDateTimeShort($date_time);

        switch ($this->params['type_view']) {
            case Fields::TYPE_VIEW_BUTTON_DATE_ENDING:
                if ($this->isDateTimeAllDay()) {
                    $result = Helper::formatDate($date_time);
                }
                break;
            case Fields::TYPE_VIEW_DT_DATE:
                $result = Helper::formatDate($date_time);
                break;
        }

        return $result;
    }

    public function getDateTimeColor()
    {
        if ($this->params['type_view'] != Fields::TYPE_VIEW_BUTTON_DATE_ENDING) {
            return;
        }

        $date_time = $this->value_data[$this->params['name']];

        if (empty($date_time) || !strtotime($date_time)) {
            return;
        }

        $color = null;

        $date_diff = DateTimeOperations::dateDiff($date_time, date('Y-m-d H:i:s'));

        if ($date_diff !== null && $date_diff === -1) {
            $color = 'red';
        }

        return $color;
    }

    public function getDateTimeAttributes()
    {
        $attributes = [
            'data-name="' . $this->params['name'] . '"',
            'data-value_date="' . (strtotime($this->value_data[$this->params['name']]) ? date(LocaleCRM::getInstance2()->_data_p['dateFormats']['medium'], strtotime($this->value_data[$this->params['name']])) : "") . '"',
            'data-value_time="' . (strtotime($this->value_data[$this->params['name']]) ? date(LocaleCRM::getInstance2()->_data_p['timeFormats']['medium_short'], strtotime($this->value_data[$this->params['name']])) : "") . '"',
        ];

        if ($this->params['type_view'] == Fields::TYPE_VIEW_BUTTON_DATE_ENDING) {
            $attributes[] = 'data-all_day="' . (int)$this->isDateTimeAllDay() . '"';
        }

        return $attributes;
    }

}
