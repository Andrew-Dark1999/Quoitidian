<?php
/**
* BlockTable widget  
* @author Alex R.
* @version 1.0
*/ 

class BlockTable extends CWidget{

    // Схема родителя
    public $schema;
    // ExtensionCopyModel родителя
    public $extension_copy = array();
    // id записи родительского модуля
    public $data_id = null;
    //
    public $this_template;





    public function init(){
        $extension_copy_relate = ExtensionCopyModel::model()->findByPk($this->schema['params']['relate_module_copy_id']);

        $table_module_relate = ModuleTablesModel::model()->find(array(
                'condition' => 'copy_id=:copy_id AND relate_copy_id=:relate_copy_id AND `type`="relate_module_many"' ,
                'params' => array(
                    ':copy_id'=>$this->extension_copy->copy_id,
                    ':relate_copy_id'=>$extension_copy_relate->copy_id,
                )
            )
        );

        $vars = array(
            'parent_copy_id' => $this->extension_copy->copy_id,
            'parent_data_id' => $this->data_id,
        );

        $extension_copy_data = (new EditViewSubModuleModel())
            ->setVars($vars)
            ->setExtensionCopy($extension_copy_relate)
            ->prepareVars()
            ->setDataListLimit(null)
            ->setDataListOffset(null)
            ->getSelectedDataList();

        $this->render('block-table', array(
                'schema' => $this->schema,
                'extension_copy' => $this->extension_copy,
                'extension_copy_relate' => $extension_copy_relate,
                'extension_copy_data' => $extension_copy_data,
                'table_module_relate' => $table_module_relate,
                'relate_links' => $this->getLinks($extension_copy_relate),
            )
        );
    }





    private function addLink($link){
        if(!isset($this->schema['params']['relate_links'])) return $link;
        foreach($this->schema['params']['relate_links'] as $relate_link){
            if(($relate_link['value'] == $link['value']) || ($relate_link['value'] == 'create' && $link['value'] == 'create-select')){
                $link['checked'] = (boolean)$relate_link['checked'];
                break;
            }
        }
        return $link;
    }
    
    
    private function getLinks($extension_copy_relate){
        $links = array();
        //
        if(
            Access::checkAdvancedAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $this->extension_copy->copy_id) &&
            Access::checkAdvancedAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $this->schema['params']['relate_module_copy_id'])
        ){
            if($this->this_template == EditViewModel::THIS_TEMPLATE_MODULE && $extension_copy_relate->isSetIsTemplate() && $this->schema['params']['relate_module_template'] == false)
                $links[] = $this->addLink(array('value' => 'create-select', 'checked' => true, 'title' => Yii::t('base', 'Create')));
            else
                $links[] = $this->addLink(array('value' => 'create', 'checked' => true, 'title' => Yii::t('base', 'Create')));
        }
        //
        if(Access::checkAdvancedAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $this->extension_copy->copy_id)){
            $links[] = $this->addLink(array('value' => 'select', 'checked' => true, 'title' => Yii::t('base', ($this->schema['params']['relate_module_template'] == false ? 'Link' : 'Take the template'))));
        }
        //
        if(
           Access::checkAdvancedAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $this->extension_copy->copy_id) &&
           Access::checkAdvancedAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $this->schema['params']['relate_module_copy_id'])
        ){
            $links[] = $this->addLink(array('value' => 'copy',   'checked' => true, 'title' => Yii::t('base', 'Copy')));
        }
        //
        if(
            Access::checkAdvancedAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $this->extension_copy->copy_id)){
            $links[] = $this->addLink(array('value' => 'delete', 'checked' => true, 'title' => Yii::t('base', 'Delete')));
        }
      
        return $links;
    }





}
