<?php
/**
* InlineEdit widget  
* @author Alex R.
* @version 1.0
*/

class InLineEdit extends CWidget{

    // Отображдение
    public $view = 'element';
    // Схема
    public $params;
    //extensionCopyModel
    public $extension_copy;
    // pci
    public $parent_copy_id;
    // this_template
    public $this_template = EditViewModel::THIS_TEMPLATE_MODULE;
     
     
     
    public function init(){
        $this->render($this->view, array(
                                    'params' => $this->params,
                                    'extension_copy' => $this->extension_copy,
                                    'parent_copy_id' => $this->parent_copy_id,
                                    'this_template' => $this->this_template,
                                 )
        );
    }



    public function getSelectList(){
        $select_list = (new \DataListModel())
            ->setGlobalParams(['schema_field' => $this->params])
            ->setExtensionCopy($this->extension_copy)
            ->prepare(\DataListModel::TYPE_FOR_SELECT_TYPE_LIST, null)
            ->getData();

        return $select_list;
    }



    public function getSelectHtmlOptions(){
        $options = array(
            'id' => $this->params['name'],
            'class'=>'select'
        );

        if(!empty($this->params['input_attr'])){
            $options += (array)$this->params['input_attr'];
        }

        return $options;
    }

}
