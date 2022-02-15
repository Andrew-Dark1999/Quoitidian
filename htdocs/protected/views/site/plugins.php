<div class="list_view_block paramrtrs_module element" data-page="plugins">
    <section class="panel">
        <header class="panel-heading"><?php echo Yii::t('base', 'Integration'); ?></header>
        <div class="panel-body content_form">
            <?php
                $source_model_list = $plugins_model->getSourceModelList();
                foreach($source_model_list as $source_model){
                ?>
                <div class="adv-table settings_form element" data-type="block" data-source_name="<?php echo $source_model->getName(); ?>">
                    <div class="col-lg-4 settings_title text-right">
                        <?php echo $source_model->getTitle(); ?>
                    </div>
                    <div class="row b_visible settings_form_group">
                        <label class="col-lg-4 control-label text-right"><?php echo \Yii::t('base', 'Service name'); ?></label>
                        <div class="col-lg-6">
                            <?php
                                $service_model = $source_model->getActiveService();
                                echo CHtml::dropDownList(
                                        'service_name',
                                        ($service_model ? $service_model->getName() : null),
                                        [PluginsModel::SERVICE_NAME_DISABLED => \Yii::t('base', 'Service disabled')] + $source_model->getServicesTitle(),
                                    array('class' => 'element select form-control', 'data-type' => 'service_name')
                                );
                            ?>
                        </div>
                    </div>
                    <div class="element" data-type="service_params">
                        <?php echo ($service_model ? $service_model->getParamsModel()->getHtml() : ''); ?>
                    </div>
                </div>
                <?php
                }
            ?>

            <div class="settings_form">
            <?php if(Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)){ ?>
                <div class="settings_form_group">
                    <label class="col-lg-4 control-label text-right"> </label>
                    <div class="col-lg-6">
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary element" data-type="save"><?php echo Yii::t('base', 'Save')?></button>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default element" data-type="cancel"><?php echo Yii::t('base', 'Cancel')?></button>
                        </div>
                    </div>
                </div>
            <?php } ?>
            </div>
        </div>
    </section>
</div>
