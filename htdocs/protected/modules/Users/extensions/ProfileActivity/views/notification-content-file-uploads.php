<?php
    $params = array(
                'schema' => array(),
                'upload_model' => $upload_model,
                'upload_link_show' => false,
                'block_class' => 'col-xs-3 ' . $position,
                'thumb_size' => 60,
                'buttons' => array('download_file'),
                'set_params_from_schema' => false,
                'params' => array(
                            'file_type' => $file_type,
                            'field_name' => null,
                ),                    
               );
    echo Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.FileBlock.FileBlock'),
               $params,
               true);
?>    
                    

