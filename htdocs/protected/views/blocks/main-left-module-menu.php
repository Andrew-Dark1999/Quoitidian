<div class="col-xs-3 widget-left element <?php echo ($this->left_menu == false ? 'hide' : '')?>" data-type="left_menu">
	<div class="panel">
		<div class="panel-body">
			<ul class="nav nav-pills nav-stacked mail-nav crm-pills">
            <?php if(Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)){ ?>
                    <li <?php if(!empty($this->data['menu_main']['index']) && $this->data['menu_main']['index'] == 'constructor') echo 'class="active"' ?> >
                        <a class="ajax_content_reload" data-action_key="<?php echo (new \ContentReloadModel(1))->addVars(array('index' => 'constructor'))->prepare()->getKey(); ?>" href="javascript:void(0)"><i class="fa fa-sitemap"></i><?php echo Yii::t('base', 'Módulos'); ?></a>
                    </li>
                    <li <?php if(!empty($this->data['menu_main']['index']) && $this->data['menu_main']['index'] == 'parameters') echo 'class="active"' ?> >
                        <a class="ajax_content_reload" data-action_key="<?php echo (new \ContentReloadModel(2))->addVars(array('url' => \Yii::app()->createUrl('/parameters')))->prepare()->getKey(); ?>" href="javascript:void(0)"><i class="fa fa-cogs"></i><?php echo Yii::t('base', 'Ajustes de sistema'); ?></a>
                    </li>
                    <li <?php if(!empty($this->data['menu_main']['index']) && $this->data['menu_main']['index'] == 'plugins') echo 'class="active"' ?> >
                        <a class="ajax_content_reload" data-action_key="<?php echo (new \ContentReloadModel(2))->addVars(array('url' => \Yii::app()->createUrl('/plugins')))->prepare()->getKey(); ?>" href="javascript:void(0)"><i class="fa fa-puzzle-piece"></i><?php echo Yii::t('base', 'Integración'); ?></a>
                    </li>

                <?php
                    $extension_copy_data = ExtensionCopyModel::model()
                                                                ->modulesActive()
                                                                ->setScopeMenu('main_left')
                                                                ->findAll('`schema` != "" OR `schema` is not NULL');
                    if(!empty($extension_copy_data)){
						$extension_copy_data[0]['title'] = "Usuarios";
						
                        foreach($extension_copy_data as $value){
													
                		  $module = $value->getModule();
                          $menu_icon_class = '';
                          if(isset($module->menu_icon_class)){
                              $menu_icon_class = $module->menu_icon_class;
                          }
                ?>
                            <li <?php if(!empty($this->data['menu_main']['index']) && $this->data['menu_main']['index'] == $value['copy_id']) echo 'class="active"'  ?>>
                                <a class="ajax_content_reload" data-action_key="<?php echo (new \ContentReloadModel(3, ['_use_auto_pci_pdi'=> false]))->addVars(array('module' => array('copy_id' => $value['copy_id'])))->prepare()->getKey(); ?>" href="javascript:void(0)">
                                    <i class="fa <?php echo $menu_icon_class; ?>"></i><?php echo Yii::t('modules', $value['title']); ?>
                                </a>
                            </li>
                <?php   }
                    }
                 }
                ?>
                <li <?php if(!empty($this->data['menu_main']['index']) && $this->data['menu_main']['index'] == 'mailing_services') echo 'class="active"' ?> >
                    <a class="ajax_content_reload" data-action_key="<?php echo (new \ContentReloadModel(2))->addVars(array('url' => \Yii::app()->createUrl('/mailing_services')))->prepare()->getKey(); ?>" href="javascript:void(0)">
                        <i class="fa fa-cog"></i><?php echo Yii::t('base', 'Servicios de mailing'); ?>
                    </a>
                </li>
            </ul>
		</div>
	</div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        var content_vars = '<?php echo \ContentReloadModel::getContentVars(); ?>';
        instanceGlobal.contentReload.setVarsFromPage(content_vars, null, null);
    });
</script>
