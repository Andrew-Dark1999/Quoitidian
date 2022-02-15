<div class="filter-box" data-filter_id="<?php if(!empty($filter_id)) echo $filter_id; ?>">
    <div class="filter-box-panels">
        <?php echo $content; ?>
    </div>
    <div class="filter-box-operations filter-box-table">
    	<div class="filter-box-col">
			<div>
	    		<input class="element_filter form-control" data-name="filter_title" type="text" <?php echo (FilterModel::$_access_to_change ? '' : 'disabled="disabled"') ?> placeholder="<?php echo Yii::t('base', 'Filter name'); ?>"/>
			</div>
	    </div>
		<div class="filter-box-col">
			<?php
			echo \CHtml::activeDropDownList(
				$filter_model,
				'view',
				FilterModel::getViewList(),
				array(
					'class' => 'element_filter edit-dropdown select',
					'data-name' => 'filter_view',
					'disabled' => (FilterModel::$_access_to_change ? '' : 'disabled'),
					'placeholder' => '',
				)
			);
			?>
		</div>

		<?php if(FilterModel::$_access_to_change){ ?>
	    <div class="filter-box-col">
	    	<div class="filter-apply">
			    <input type="button" name="name" class="filter-btn-save btn btn-primary" value="<?php echo Yii::t('base', 'Save'); ?>"/>
			    <input type="button" name="save" class="filter-btn-cancel btn btn-default" value="<?php echo Yii::t('base', 'Cancel'); ?>"/>
                <?php if(isset($btn_filter_delete) && $btn_filter_delete){ ?><input type="button" name="save" class="filter-btn-delete btn btn-danger" value="<?php echo Yii::t('base', 'Delete'); ?>"/> <?php } ?>
		    </div>
		</div>
		<?php } ?>
		<!-- Table last cell imitation -->
		<div class="filter-box-col full-width-col"></div>
    </div>
</div>
