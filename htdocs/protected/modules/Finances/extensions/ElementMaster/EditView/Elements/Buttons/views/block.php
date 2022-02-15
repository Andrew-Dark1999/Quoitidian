<div class="buttons-block element" data-type="block_button">
    <?php echo $content; ?>
    <?php
        if(Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $this->extension_copy->copy_id, Access::ACCESS_TYPE_MODULE)){
    
            $params_np = array(
                'create_new_card' => true,
                'parent_copy_id' => \DocumentsGenerateModelExt::MODULE_DEALS,
                'this_copy_id' => \DocumentsGenerateModelExt::MODULE_FINANCES,
                'default_data' => array(
                    'block_unique_index' => \DocumentsGenerateModelExt::BLOCK_ID_FINANCE_PAYMENT,
                    'finances_typenew' => \DocumentsGenerateModelExt::BLOCK_ID_FINANCE_PAYMENT,
                    'finances_type' => 1,
                    'finances_format' => 1,
                    'finances_status' => \DocumentsGenerateModelExt::getFinanceStatusId(\DocumentsGenerateModelExt::$STATUSES_FINANCE['paid']),
                    'finances_date' => date('Y-m-d H:i:s'),
            ));
            
            $params_print = array();
            
            if(!empty($this->extension_data->finansy_id)) {
                $linked = \DataModel::getInstance()->setFrom('{{linked_cards}}')->setWhere('extension_copy_id = ' . \DocumentsGenerateModelExt::MODULE_DEALS . ' AND l_extension_copy_id = ' . \DocumentsGenerateModelExt::MODULE_FINANCES . ' AND l_card_id = ' . (int)$this->extension_data->finansy_id .  ' AND l_extension_copy_id_target = ' . \ExtensionCopyModel::MODULE_DOCUMENTS)->findRow();
                if(!empty($linked['l_card_id_target'])) {
                    $params_print = array(
                        'edit_next_card' => true,
                        'copy_id' => \ExtensionCopyModel::MODULE_DOCUMENTS,
                        'id' => $linked['l_card_id_target'],
                    );
                }
            }
            
            if(count($params_print)==0) {
                $params_print = array(
                    'access_auto_next_card' => true,
                );
            }
    ?>
            <span class="element" data-type="button">
              <div class="btn btn-default actions crm-dropdown dropdown">
                  <span class="dropdown-toggle" data-toggle="dropdown"><?php echo Yii::t('base', 'Actions')?></span>
                  <ul class="dropdown-menu" role="menu">
                      <li><a href="javascript:void(0)" data-type="create_np" data-params='<?php echo json_encode($params_np); ?>' class="element edit_view_btn-save"><?php echo Yii::t('FinancesModule.base', 'Create Payment');?></a></li>
                      <li><a href="javascript:void(0)" data-type="create_print" data-params='<?php echo json_encode($params_print); ?>' class="element edit_view_btn-save"><?php echo Yii::t('FinancesModule.base', 'Print');?></a></li>
                  </ul>
              </div> 
            </span>

          <span class="element" data-type="button">
              <button type="submit" class="btn btn-primary <?php echo $this->button_attr['save']['class']?>"><?php echo \Yii::t('base', 'Save'); ?></button>
          </span>
          

          
      <?php
          }
      ?>
</div>