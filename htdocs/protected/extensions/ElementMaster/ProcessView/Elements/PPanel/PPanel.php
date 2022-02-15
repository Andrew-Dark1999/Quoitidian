<?php
/**
* PPanel widget  
* @author Alex R.
* @version 1.0
*/ 

class PPanel extends CWidget{

    public $extension_copy;
    public $this_template = EditViewModel::THIS_TEMPLATE_MODULE;
    public $this_module_template = false;
    public $block_field_name_replace = false;
    public $fields_group;
    public $panel_data = array();
    public $append_cards_html = true;
    public $card_list_html;
    public $process_view_builder_model;




    public function init(){
        $dnt_card_add_class = 'edit_view_dnt-add';
        if($this->this_module_template && $this->this_template == EditViewModel::THIS_TEMPLATE_MODULE) {
            $dnt_card_add_class = 'edit_view_select_dnt-add';
        }

        return $this->render('element', array(
                                    'dnt_card_add_class' => $dnt_card_add_class,
                                    'card_list_html' => $this->card_list_html,
                                 )
        );
        
    }




    public function getPanelDataList($field_name){
        $result = [
            'field_name' => $field_name,
            'field_value' => '',
            'html_value' => '',
        ];

        if(!empty($this->panel_data['fields_data'])){
            $fields_data = json_decode($this->panel_data['fields_data'], true);
        }

        if(empty($fields_data)){
            return $result;
        }


        $field_name_as = $this->process_view_builder_model->getFieldNameAs($field_name, false);
        $result['field_value'] = $fields_data[$field_name_as];

        $params = $this->extension_copy->getFieldSchemaParams($field_name);

        //html_value
        $result['html_value'] = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ProcessView.Elements.PPanelTitle.PPanelTitle'),
            array(
                'extension_copy' => $this->extension_copy,
                'params' => $params['params'],
                'value_data' => $fields_data,
                'file_link' => false,
                'relate_add_avatar' => false,
                'element_dye' => false,
                'field_name_as' => $field_name_as,
            )
            , true);


        /*
        $field_name_as = $this->fields_group_as[$field_name];
        if($this->block_field_name_replace){
            if($field_name_as == $this->block_field_name_replace){
                if(isset($fields_data[$this->extension_copy->getTableName() . '.' . $this->block_field_name_replace])){
                    $result['html_value'] = $fields_data[$this->extension_copy->getTableName() . '.' . $this->block_field_name_replace];
                }
            }
        }
        */

        return $result;
    }



}



