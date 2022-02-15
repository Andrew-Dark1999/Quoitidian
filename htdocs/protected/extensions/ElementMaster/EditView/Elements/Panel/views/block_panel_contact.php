<div class="row element" data-type="block_panel_contact">
	<div class="col-md-10 contacts-block">
         <div class="file-box"
             data-name="EditViewModel[ehc_image1]"
         >
            <?php
                if(!empty($extension_data->ehc_image1)){
                    if(!is_array($extension_data->ehc_image1)) $upload_model = UploadsModel::model()->setRelateKey($extension_data->ehc_image1)->find();
                    else $upload_model = UploadsModel::model()->findByPk($extension_data->ehc_image1[0]);
                }
                else $upload_model = null;
                
                echo Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.FileBlock.FileBlock'),
                           array(
                            'view' => 'element_contact',
                            'schema' => $schema,
                            'upload_model' => $upload_model,
                            'extension_copy' => $extension_copy,
                            'extension_data' => $extension_data,
                            'thumb_size' => 85,
                           ),
                           true);
            ?>
        </div>
        
        <?php if(!empty($content)) echo $content; ?>
        
	</div><!-- /.contacts-block -->
</div><!-- /.row -->
