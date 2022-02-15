<div class="bpm_block sm_extension"
	 data-copy_id="<?php echo \ExtensionCopyModel::MODULE_PROCESS ?>"
	 data-id="<?php echo $process_model->process_id; ?>"
	 data-page_name="BPMView"
	 data-parent_copy_id="<?php //echo \ExtensionCopyModel::MODULE_PROCESS; ?>"
	 data-parent_data_id="<?php //echo \Yii::app()->request->getParam('process_id'); ?>"
	 data-this_template="0<?php //echo (integer)$this->this_template; ?>"

>
	<svg class="arrows" width="100%" height="100%"><circle cx="-10" cy="-100" r="10" fill="#a48ad4"></circle></svg>
	<div class="b_bpm b_bpm_top filter-block">
	<div class="bpm_top">
		<?php if(!empty($actions)){ ?>
	    <div class="btn-group crm-dropdown dropdown-right edit-dropdown element" data-type="actions">
	        <button class="btn dropdown-toggle btn-default"><?php echo \Yii::t('ProcessModule.base', 'Actions'); ?></button>
	        <ul class="dropdown-menu dropdown-shadow element" data-type="bpm_menu">
				<?php
					foreach($actions as $action){
						echo '<li'. ($action['active'] ? ' class="active"' : '').'><a href="javascript:void(0)" class="element" data-type="'.$action['type'].'">'.$action['title'].'</a></li>';
					}
				?>
	        </ul>
	    </div>
		<?php } ?>
        <!--switch beetween process-->
        <?php
            if(!empty($pm_extension_copy)){
        ?>
        <div class="btn-group element" data-type="drop_down">
          <div class="submodule-link btn-group crm-dropdown dropdown-right edit-dropdown element" data-type="process_menu">
              <button class="btn btn-default dropdown-toggle element max-width-btn" data-toggle="dropdown" data-id="" data-type="drop_down_button"><?php echo $process_menu_active_value ?></button>
              <ul class="dropdown-menu element" role="menu" data-type="drop_down_list" aria-labelledby="dropdownMenu1"
                  data-type="drop_down_list"
                  data-there_is_data="0"
                  data-relate_copy_id="">
                  <div class="search-section">
                      <input type="text" class="submodule-search form-control" placeholder="<?php echo Yii::t('base', 'Search'); ?>">
                  </div>
                  <div class="submodule-table">
                      <table class="table list-table">
                          <tbody>
                          <?php
                          if(!empty($process_menu_module_data)){
                              foreach($process_menu_module_data as $process_menu){
                                  $active = '';
                                  if($process_menu[$pm_extension_copy->prefix_name . '_id'] == $process_model->process_id) $active = 'active';
                                  ?>
                                  <tr class="sm_extension_data <?php echo $active; ?>"
                                      data-id="<?php echo $process_menu[$pm_extension_copy->prefix_name . '_id']; ?>">
                                  <td>
                                      <span href="javasctript:void(0)" class="name"><?php echo $process_menu['module_title']; ?></span>
                                  </td>
                                  </tr>
                                  <?php
                              }
                          } else {
                              ?>
                              <tr class="sm_extension_data" data-id=""><td><span href="javasctript:void(0)" class="name"><?php echo Yii::t($this->module->getModuleName() . 'Module.base', 'there are no processes') ?></span></td></tr>
                              <?php
                          }
                          ?>
                          </tbody>
                      </table>
                  </div>
              </ul>
          </div>
      </div>
        <?php } ?>
		<div class="btn-group">
			<button class="btn btn-default btn-round edit_view_show name" data-controller="module_param_bpm"><i class="fa fa-pencil"></i></button>
			<div class="hover_notif"><?= Yii::t('constructor', 'Updated'); ?></div>
		</div>
	    <div class="bpm_def element <?php echo (\Process\models\ProcessModel::getInstance()->getModeChange() == \Process\models\ProcessModel::MODE_CHANGE_VIEW ? 'hidden' : ''); ?>" data-type="operation_menu">
	    	<div class="bpm_operator element" data-type="operation" data-name="data_record">
	            <div class="bpm_body">
	                <i class="fa fa-flash"></i>
	            </div>
	            <div class="bpm_title"></div>
    			<div class="bpm_operator_remove"><i class="fa fa-times"></i></div>
    			<div class="hover_notif"><?php echo \Yii::t('ProcessModule.base', 'Data record'); ?></div>
	         </div>
	         <div class="bpm_operator element" data-type="operation" data-name="notification">
	            <div class="bpm_body">
	                <i class="fa fa-envelope-o"></i>
	            </div>
	            <div class="bpm_title"></div>
    			<div class="bpm_operator_remove"><i class="fa fa-times"></i></div>
    			<div class="hover_notif"><?php echo \Yii::t('ProcessModule.base', 'Notification'); ?></div>
	         </div>
	         <!--div class="bpm_operator element" data-type="operation" data-name="">
	            <div class="bpm_body">
	                <i class="fa fa-phone"></i>
	            </div>
	            <div class="bpm_title"></div>
    			<div class="bpm_operator_remove"><i class="fa fa-times"></i></div>
    			<div class="hover_notif">operaror name</div>
	         </div-->
	         <div class="bpm_operator element" data-type="operation" data-name="timer">
	            <div class="bpm_body">
	                <i class="fa fa-clock-o"></i>
	            </div>
	            <div class="bpm_title"></div>
    			<div class="bpm_operator_remove"><i class="fa fa-times"></i></div>
    			<div class="hover_notif"><?php echo \Yii::t('ProcessModule.base', 'Timer'); ?></div>
	         </div>
	         <div class="bpm_operator element" data-type="operation" data-name="condition">
	            <div class="bpm_body">
	                <i class="diamond"></i>
	            </div>
	            <div class="bpm_title"></div>
    			<div class="bpm_operator_remove"><i class="fa fa-times"></i></div>
    			<div class="hover_notif"><?php echo \Yii::t('ProcessModule.base', 'Condition'); ?></div>
	         </div>
	         <div class="bpm_operator element" data-type="operation" data-name="and">
	            <div class="bpm_body">
	                <i class="fa fa-plus"></i>
	            </div>
	            <div class="bpm_title"></div>
    			<div class="bpm_operator_remove"><i class="fa fa-times"></i></div>
    			<div class="hover_notif"><?php echo \Yii::t('ProcessModule.base', 'And'); ?></div>
	         </div>
            <div class="bpm_operator element" data-type="operation" data-name="scenario">
                <div class="bpm_body">
                    <i class="fa fa-code"></i>
                </div>
                <div class="bpm_title"></div>
                <div class="bpm_operator_remove"><i class="fa fa-times"></i></div>
                <div class="hover_notif"><?php echo \Yii::t('ProcessModule.base', 'Scenario'); ?></div>
            </div>
	         <div class="bpm_operator element" data-type="operation" data-name="task">
	            <div class="bpm_body">
	                <i class="fa fa-tasks"></i>
	            </div>
	            <div class="bpm_title"></div>
    			<div class="bpm_operator_remove"><i class="fa fa-times"></i></div>
    			<div class="hover_notif"><?php echo \Yii::t('ProcessModule.base', 'Task'); ?></div>
	         </div>
	         <div class="bpm_operator element" data-type="operation" data-name="agreetment">
	            <div class="bpm_body">
	                <i class="fa fa-check-circle-o"></i>
	            </div>
	            <div class="bpm_title"></div>
    			<div class="bpm_operator_remove"><i class="fa fa-times"></i></div>
    			<div class="hover_notif"><?php echo \Yii::t('ProcessModule.base', 'Agreetment'); ?></div>
	         </div>
	         <svg class="hidden">
	         	<path class="arrow" d="M0 11 L68 11 " stroke="#000000" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
	         	<text class="b_title" fill="#767676" font-size="14" font-family="CustomOpenSans"></text>
	         </svg>
	    </div>
	</div>
	</div>

    <div class="timing-block element" data-type="timing-block">
        <div class="timing element default" data-key>
            <span class="element" data-type="date">18.04.2018</span>
        </div>
        <div class="timing-container element" data-type="timing-container">
        </div>
    </div>

	<div class="bmp-container">
        <div class="bpm_unit hide outer_unit">
            <div class="bpm_uname">Клиент <i class="fa fa-cog"></i></div>
        </div>

        <?php
            $r = \Yii::app()->controller->getPackage('environments');
            if ($r['bpm-version'] == 1) {
                ?>
                <div class="b_bpm b_bpm_bottom">
                    <button class="b_bpm_fix btn btn-create bpm_responsible_add"><?php echo \Yii::t('base', 'Responsible'); ?> +</button>
                </div>
                <?php
            }
        ?>
    </div>

</div>


<script type="text/javascript">
	ProcessObj.setServerParams(<?php echo json_encode($server_params) ?>);
	ProcessObj.BPM.refreshResponsible();

	process = new Process();
	process.BPM.buildBPM();
</script>
