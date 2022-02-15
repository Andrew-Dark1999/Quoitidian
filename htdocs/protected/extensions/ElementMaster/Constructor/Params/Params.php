<?php

class Params extends CWidget{
    
    public $view = 'element';
    // Модель формы 
    public $model = null;
    // Параметры Params по схеме данных
    public $params = array();
    //  
    public $field_attr = array();
    // extension_copy
    public $extension_copy;
    //список модулей для осключения из списка (для типа "Связь с другим модулем") 
    public $exception_copy_id = array();
    // показвывает блок выбора сцета для елемента select 
    public $select_color_block = false;
    // параметры для елемента из блока select
    public $select_params = array();
    //в случае загрузки через аякс
    public $extension_copy_id = false;
    
    public function init(){

        $select_list = array();
        if(!empty($this->params)){
            if($this->params['type'] == 'select' && $this->params['type_view'] == Fields::TYPE_VIEW_DEFAULT){
                $select_list = array(
                    array(
                        $this->params['name'] . '_id' => 1,
                        $this->params['name'] . '_title' => Yii::t('base', 'Value') . ' 1',
                        $this->params['name'] . '_color' => 'gray',
                        $this->params['name'] . '_sort' => 0,
                        $this->params['name'] . '_remove' => true,
                        $this->params['name'] . '_finished_object' => false,
                        $this->params['name'] . '_slug' => '',
                        )
                );
            }

            if($this->params['type'] == 'select' && $this->params['type_view'] == Fields::TYPE_VIEW_BUTTON_STATUS){
                $select_list = array(
                    array(
                        $this->params['name'] . '_id' => 1,
                        $this->params['name'] . '_title' => Yii::t('base', 'Finished'),
                        $this->params['name'] . '_color' => 'orange',
                        $this->params['name'] . '_sort' => 0,
                        $this->params['name'] . '_remove' => false,
                        $this->params['name'] . '_finished_object' => '1',
                        $this->params['name'] . '_slug' => '',
                        )
                );
            }




            if($this->params['type'] == 'select' && !empty($this->extension_copy)){
                $table_name = $this->extension_copy->getTableName($this->params['name']);

                $data_model = new DataModel();

                $table = $data_model->setText('SHOW TABLES like "' . $table_name . '"')->findAll();
                if(!empty($table)){
                    $data_model->reset();
                    $select_list = $data_model
                        ->setFrom($table_name)
                        ->findAll();

                    if(!empty($select_list) && isset($select_list[0][$this->params['name'] . '_sort']))
                        $select_list = Helper::arraySort($select_list, $this->params['name'] . '_sort');
                }
            }
        }

        $this->render($this->view, array(
                                        'params' => $this->params,
                                        'model' => $this->model,
                                        'exception_copy_id' => $this->exception_copy_id,
                                        'extension_copy' => $this->extension_copy,
                                        'field_attr' => $this->field_attr,
                                        'select_color_block' => $this->select_color_block,
                                        'select_params' => $this->select_params,
                                        'select_list' => $select_list,
                                        'extension_copy_id' => $this->extension_copy_id,
                                     ));
    }
 
 

}
