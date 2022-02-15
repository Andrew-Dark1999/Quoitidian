<?php
/**
 * ProcessViewTodoListModel - модель управления TODO списками
 * @author Alex R.
 */

class ProcessViewTodoListModel{


    protected $_table_name = 'process_view_todo_list';




    public function getTableName($add_prefix = true){
        if($add_prefix){
            return '{{' . $this->_table_name . '}}';
        } else {
            return $this->_table_name;
        }
    }



    public function findAll($condition, $params = array()){
        $data_list = (new \DataModel())
                        ->setFrom($this->getTableName())
                        ->setWhere($condition, $params)
                        ->findAll();

        return $data_list;
    }


    /**
     * insert - Вставка записи в связующую таблицу о новом ТОДО списке
     * @param array $attributes
     */
    public function insert(array $attributes){
        (new \DataModel())->Insert($this->getTableName(), $attributes);
    }



    /**
     * insertMulti - Вставка записей в связующую таблицу о новом ТОДО списке
     * @param array $attributes
     */
    public function insertMulti(array $attributes){
        (new \DataModel())->InsertMulti($this->getTableName(), $attributes);
    }



    /**
     * clearModuleTodoList - очистка ТОДО списка в модуле от "пустых" значений
     */
    public function clearModuleTodoList($copy_id){
        if(is_array($copy_id) && in_array(\ExtensionCopyModel::MODULE_TASKS, $copy_id) == false){
            return;
        } else
        if($copy_id != \ExtensionCopyModel::MODULE_TASKS){
            return;
        }

        $copy_id = \ExtensionCopyModel::MODULE_TASKS;
        $extension_copy = \ExtensionCopyModel::model()->findByPk($copy_id);

        $query = '
                    DELETE
                    FROM {{tasks_todo_list}}
                    WHERE
                    (
                      SELECT count(*)
                      FROM {{process_view_todo_list}} t0
                      LEFT JOIN 
                        {{process_view_sorting_list}} t1 ON t1.sorting_list_id = t0.sorting_list_id
                      WHERE
                        t0.todo_list_id = {{tasks_todo_list}}.todo_list_id AND
                        t1.copy_id = '.$copy_id.'
                    ) = 0
                      AND
                    NOT exists(
                      SELECT '.$extension_copy->getPKFieldName().'
                      FROM '.$extension_copy->getTableName().'
                      WHERE todo_list = {{tasks_todo_list}}.todo_list_id
                    )
        ';

        (new DataModel())->setText($query)->execute();
    }




}
