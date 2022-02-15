<?php
/* @var application\components\filter\FilterPanel $filterPanel */
?>
<div class="filter-box-panel filter-box-table">
    <div class="filter-box-col">
        <?php
            Yii::app()->controller->widget('Reports\extensions\Filters\ListView\Elements\FilterModule\FilterModule',
                array(
                    'modules' => $this->modules,
                    'selected_copy_id' => $this->selected_copy_id,
                ));
        ?>
    </div>
    <div class="filter-box-col">
      <div class="col-block">
          <select class="element_filter edit-dropdown select list-modules" data-name="field">
              <?php
              foreach ($filterPanel->getModuleFields() as $field) {
                  ?>
                  <option value="<?php echo $field['value']; ?>" <?php echo $field['selected'] ? 'selected="selected"' : ''; ?>><?php echo $field['title']; ?></option>
                  <?php
              }
              ?>

          </select>
      </div>
    </div>
    <div class="filter-box-col full-width-col">
        <div class="filter-box-condition"><?php echo $filterPanel->getConditionView(); ?></div>
        <div class="filter-box-condition-value"><?php echo $filterPanel->getConditionValueView(); ?></div>

    </div>
    <div class="filter-box-col">
        <div class="apply-box">
            <input type="button" class="filter-panel-add btn btn-primary" value="+" title="<?php echo Yii::t('base', 'Add'); ?>"/>
            <input type="button" class="filter-panel-delete btn btn-default" value="-" title="<?php echo Yii::t('base', 'Delete'); ?>"/>
        </div>
    </div>
</div>
