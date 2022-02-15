<?php
/**
* PCard widget  
* @author Alex R.
* @version 1.0
*/ 

class PCard extends CWidget{

    public $extension_copy;
    public $fields_group = array();
    public $fields_view;

    public $panel_data;
    public $card_data;


    public $block_field_name_replace = false;
    public $js_content_reload_add_vars = false;


    public function init(){

        // list image file
        $last_img_file = null;
        if(\Yii::app()->controller->module->process_view_last_bl_active_image) {
            $schema = $this->extension_copy->getAttachmentsField();
            if (isset($schema['name']) && !empty($this->card_data[$schema['name']])) {
                $attachments_keys[] = $this->card_data[$schema['name']];
            }

            $attachments_keys = ActivityMessagesModel::getLastAttachment(
                $this->extension_copy->getPrimaryKey(),
                $this->card_data[$this->extension_copy->prefix_name.'_id']
            );

            $last_img_file = UploadsModel::model()->getLastUploadImgFile($attachments_keys);
        }

        return $this->render('element', array(
                                    'last_img_file' => $last_img_file,
                                 ));
    }
 
 
}
