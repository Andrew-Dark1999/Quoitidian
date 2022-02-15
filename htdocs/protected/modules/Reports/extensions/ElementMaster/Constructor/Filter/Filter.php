<?php

/**
 * Filter widget - Фильтр
 *
 * @author Alex R.
 * @version 1.0
 */
namespace Reports\extensions\ElementMaster\Constructor\Filter;

class Filter extends \CWidget
{

    public $views = ['block', 'block.block'];

    public $schema = null;  //array();

    public $element = null;

    public function init()
    {
        if (empty($this->views)) {
            return;
        }
        $result = '';
        foreach ($this->views as $view) {
            switch ($view) {
                case 'block.block' :
                    $data = [
                        'schema'  => $this->schema,
                        'content' => $result,
                    ];
                    $result = \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Constructor\Block\Block',
                        [
                            'data' => $data,
                        ],
                        true);
                    break;

                case 'block' :
                    $params_hidden = json_encode([
                        'type'         => $this->schema['type'],
                        'remove'       => $this->schema['remove'],
                        'unique_index' => $this->schema['unique_index'],
                    ]);

                    $result = $this->render('block', ['schema' => $this->schema, 'params_hidden' => $params_hidden], true);
                    break;

                case 'panel' :
                    $params_hidden = json_encode([
                        'type'                => $this->element['type'],
                        'remove'              => $this->element['remove'],
                        'drag_marker'         => $this->element['drag_marker'],
                        'show_module_copy_id' => $this->element['show_module_copy_id'],
                    ]);

                    $result = $this->render('panel', ['schema' => $this->schema, 'element' => $this->element, 'params_hidden' => $params_hidden], true);
                    break;

                case 'module' :
                    $result = $this->render('module', ['schema' => $this->schema, 'element' => $this->element], true);
                    break;

                case 'field-params' :
                    $fields_schema = [];
                    $extension_copy = null;

                    if (!empty($this->element['module_copy_id'])) {
                        $extension_copy = \ExtensionCopyModel::model()->findByPk($this->element['module_copy_id']);
                        $sub_module_schema_parse = $extension_copy->getSchemaParse();

                        $fields_schema = \SchemaConcatFields::getInstance()
                            ->setSchema($sub_module_schema_parse['elements'])
                            ->setWithoutFieldsForFilterGroup($extension_copy->getModule(false)->getModuleName())
                            ->parsing()
                            ->prepareWithOutDeniedRelateCopyId()
                            ->primaryOnFirstPlace()
                            ->prepareWithConcatName()
                            ->getResult();
                    }

                    $result = $this->render('field-params', [
                        'schema'         => $this->schema,
                        'element'        => $this->element,
                        'fields_schema'  => $fields_schema,
                        'destination'    => 'listView',
                        'extension_copy' => $extension_copy,

                    ], true);
                    break;
            }
        }

        echo $result;
    }

}
