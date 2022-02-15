<?php
/**
 * Edit widget
 *
 * @author Alex R.
 * @version 1.0
 */

class Edit extends CWidget
{

    // Схема
    public $schema;

    // Контент
    public $content;

    // Отображдение
    public $view = 'element';

    //extensionCopyModel
    public $extension_copy;

    // orm актуальной части данных сабмодуля для EditView
    public $extension_data;

    // сущности первичного родительского модуля
    public $primary_entities;

    // формирование данных для шаблона
    public $this_template = EditViewModel::THIS_TEMPLATE_MODULE;

    // copy_id родительского модуля
    public $parent_copy_id;

    // id данных родительского модуля
    public $parent_data_id;

    // массив данных полей relate модуля. Передается при добавлении данных в EditView для Сабмодуля
    public $parent_relate_data_list = null;

    // данные елемента по умолчанию
    public $default_data = null;

    public $relate = [];

    public $read_only = false;

    public $only_one_block_select;

    public $builder_model = null;

    //показываем ссылку для генерации документа по шаблону
    public $show_generate_url = false;

    public function init()
    {
        $this->render($this->view, [
                'relate'                  => $this->relate,
                'schema'                  => $this->schema,
                'content'                 => $this->content,
                'primary_entities'        => $this->primary_entities,
                'parent_copy_id'          => $this->parent_copy_id,
                'parent_data_id'          => $this->parent_data_id,
                'parent_relate_data_list' => $this->parent_relate_data_list,
                'extension_copy'          => $this->extension_copy,
                'extension_data'          => $this->extension_data,
                'default_data'            => $this->default_data,
                'read_only'               => $this->read_only,
                'this_template'           => $this->this_template,
                'show_generate_url'       => $this->show_generate_url,
                'only_one_block_select'   => $this->only_one_block_select,
            ]
        );
    }

    public function getSelectList()
    {
        $select_list = (new \DataListModel())
            ->setGlobalParams(['schema_field' => $this->schema['params']])
            ->setExtensionCopy($this->extension_copy)
            ->prepare(\DataListModel::TYPE_FOR_SELECT_TYPE_LIST, null)
            ->getData();

        return $select_list;
    }

    public function getSelectHtmlOptions()
    {
        $options = [
            'id'    => $this->schema['params']['name'],
            'class' => 'select'
        ];

        if (!empty($this->schema['params']['input_attr'])) {
            $options += (array)$this->schema['params']['input_attr'];
        }

        return $options;
    }

    public function formatNumeric($value)
    {
        if (!$this->extension_data->hasErrors($this->schema['params']['name'])) {
            $value = Helper::TruncateEndZero($value);
        }
        if ($value == '' || !is_numeric($value)) {
            return $value;
        }
        if (!empty($this->schema['params']['add_hundredths'])) {
            $value = number_format($value, 2, '.', '');
        } else {
            $count = strpos($value, '.');

            if ($count === false) {
                $count = 0;
            } else {
                $count = strlen($value) - $count - 1;
            }

            $value = number_format($value, $count, '.', '');
        }

        return $value;
    }

    public function show()
    {

    }

    public function hasTime()
    {
        $result = true;

        switch ($this->schema['params']['type_view']) {
            case Fields::TYPE_VIEW_DT_DATE:
                $result = false;
                break;
        }

        return $result;
    }
}
