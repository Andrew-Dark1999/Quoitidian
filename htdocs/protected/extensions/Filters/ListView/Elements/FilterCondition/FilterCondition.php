<?php
/**
 * FilterCondition widget
 *
 * @author Alex R.
 * @version 1.0
 */

class FilterCondition extends CWidget
{

    // схема поля 
    public $field_schema = null;

    // данные елемента
    public $condition_value = null;

    // mixed. группа применения (ListView, Constructor...), в которой состоит фильтр
    public $destination;

    public function init()
    {
        $filter_list = null;

        if (!empty($this->field_schema)) {
            $filter_list = FilterMap::getFilterList(
                (new \Fields)->getFilterGroup($this->field_schema['params']['type']),
                $this->destination,
                $this->field_schema['params']['filter_exception_position']
            );
        }

        $this->render('element', [
                'filter_list'     => $filter_list,
                'condition_value' => $this->condition_value,
            ]
        );
    }

}
