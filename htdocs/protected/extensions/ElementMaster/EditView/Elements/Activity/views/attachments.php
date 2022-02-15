<!-- attachments -->
<?php
echo Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.FileBlock.FileBlock'),
               array(
                'schema' => $schema,
                'upload_model' => $upload_value,
                'extension_copy' => $extension_copy,
                'extension_data' => $extension_data,
                'upload_link_show' => false,
                'buttons' => $this->attachents_buttons,
                'thumb_size' => $attachments_image_thumb_size,
                'block_class' => $attachments_image_block_class,
               ),
               true);
?>    
