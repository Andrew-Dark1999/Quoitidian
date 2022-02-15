<div class="file-box">
    
    <?php
        $params = array(
                    'schema' => $schema,
                    'upload_model' => $upload_value,
                    'extension_copy' => $extension_copy,
                    'extension_data' => $extension_data,
                    'upload_link_show' => false,
                    'block_class' => 'col-xs-6 file-item',
                    'thumb_size' => $thumb_size,
                   );
        if($buttons !== null) $params['buttons'] = $buttons;

        echo Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.FileBlock.FileBlock'),
                   $params,
                   true);
    ?>    
</div>                        

