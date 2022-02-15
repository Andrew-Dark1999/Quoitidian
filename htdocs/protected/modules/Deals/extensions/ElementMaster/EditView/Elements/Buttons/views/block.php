<div class="buttons-block element" data-type="block_button">
    <?php echo $content; ?>
    <?php
        if(Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $this->extension_copy->copy_id, Access::ACCESS_TYPE_MODULE)){
            $empty_parameters = \Deals\models\ContractModel::getEmptyParametersMessages();
    ?>
            <script src="/static/js/modules/deals/agemeent.js"></script>
            <input class="form-control sdelkin_id" type="hidden" value="<?php echo (!empty($this->extension_data->sdelkin_id)) ? $this->extension_data->sdelkin_id : 0;?>">
            <input class="form-control extension_copy_id" type="hidden" value="<?php echo $this->extension_copy->copy_id;?>">
            <input class="form-control empty_parameter_1" type="hidden" value="<?php echo $empty_parameters[1];?>">  
            <input class="form-control empty_parameter_2" type="hidden" value="<?php echo $empty_parameters[2];?>">  
            <input class="form-control empty_parameter_3" type="hidden" value="<?php echo $empty_parameters[3];?>">  
            <input class="form-control empty_parameter_4" type="hidden" value="<?php echo $empty_parameters[4];?>">  
            <input class="form-control empty_parameter_5" type="hidden" value="<?php echo $empty_parameters[5];?>">  
            <input class="form-control empty_parameter_6" type="hidden" value="<?php echo $empty_parameters[6];?>">
                  
            <span class="element" data-type="button">
              <div class="btn btn-default actions crm-dropdown dropdown">
                  <span class="dropdown-toggle" data-toggle="dropdown"><?php echo Yii::t('base', 'Actions')?></span>
                  <ul class="dropdown-menu" role="menu">
                      <li><a href="javascript:void(0)" data-type="make_contract" class="element"><?php echo Yii::t('DealsModule.base', 'Make a contract');?></a></li>
                  </ul>
              </div> 
     
      <?php    
                $j_property = \Deals\models\ContractModel::getJointProperty();
                $templates = \Deals\models\ContractModel::getTemplates();
                $signed = \Deals\models\ContractModel::getSigned();
                
      ?>

                  <div class="modal">
                    <div class="modal-dialog hidden make_contract_wrapper" style="width: 650px; margin-top: 356px;">
                      <section class="panel make_contract">
                        <header class="panel-heading">
                            <span><?php echo Yii::t('DealsModule.base', 'Make the agreement');?></span>
                            <span class="tools pull-right">
                                <a href="javascript:void(0)" data-dismiss="modal" class="fa close-button"></a>
                            </span>
                        </header>
                        <div class="panel-body element" data-type="panel_block" style="overflow: hidden;">
                          <ul class="inputs-block">
                            <li class="clearfix form-group inputs-group element" data-type="panel">
                              <span class="inputs-label"><?php echo Yii::t('DealsModule.base', 'Area sq.m');?></span>
                              <div class="columns-section col-1">
                                <div class="column">
                                  <input readonly class="form-control deal_square" type="text" value="">
                                </div>
                              </div>
                            </li>
                            <li class="clearfix form-group inputs-group element" data-type="panel">
                              <span class="inputs-label"><?php echo Yii::t('DealsModule.base', 'Discount (%) per sq.m.');?></span>
                              <div class="columns-section col-1">
                                  <div class="column">
                                    <input class="form-control deal_discount" type="text" value="">
                                  </div>
                              </div>
                            </li>
                            <li class="clearfix form-group inputs-group element" data-type="panel">
                              <span class="inputs-label"><?php echo Yii::t('DealsModule.base', 'Cost per square meter');?></span>
                              <div class="columns-section col-1">
                                  <div class="column">
                                    <input class="form-control deal_object_parammetrprice" type="text" value="">
                                  </div>
                              </div>
                            </li>
                            <li class="clearfix form-group inputs-group element" data-type="panel">
                              <span class="inputs-label"><?php echo Yii::t('DealsModule.base', 'Сost of additional of payments');?></span>
                              <div class="columns-section col-1">
                                  <div class="column">
                                    <input readonly class="form-control deal_add_payments" type="text" value="">
                                  </div>
                              </div>
                            </li>
                            <li class="clearfix form-group inputs-group element" data-type="panel">
                              <span class="inputs-label"><?php echo Yii::t('DealsModule.base', 'Deduction from the contract price');?></span>
                              <div class="columns-section col-1">
                                  <div class="column">
                                    <input class="form-control deal_minus" type="text" value="">
                                  </div>
                              </div>
                            </li>
                            <li class="clearfix form-group inputs-group element" data-type="panel">
                              <span class="inputs-label"><?php echo Yii::t('DealsModule.base', 'Amount of the deal');?></span>
                              <div class="columns-section col-1">
                                  <div class="column">
                                    <input readonly class="form-control deal_agreement_sum" type="text" value="">
                                  </div>
                              </div>
                            </li>
                            <li class="clearfix form-group inputs-group element" data-type="panel">
                              <span class="inputs-label"><?php echo Yii::t('DealsModule.base', 'Number of steps');?></span>
                              <div class="columns-section col-1">
                                <div class="column">
                                  <select class="select steps_counter deal_level_count">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                  </select>
                                </div>
                              </div>
                            </li>
                            <li class="clearfix form-group inputs-group element" data-type="panel" step-order="1">
                              <span class="inputs-label"><?php echo Yii::t('DealsModule.base', 'Amount of the first stage');?></span>
                              <div class="columns-section col-1">
                                  <div class="column">
                                    <input class="form-control deal_1_sum" type="text" value="">
                                  </div>
                              </div>
                            </li>
                            <li class="clearfix form-group inputs-group element" data-type="panel" step-order="1">
                              <span class="inputs-label"><?php echo Yii::t('DealsModule.base', 'Area sq.m');?></span>
                              <div class="columns-section col-1">
                                  <div class="column">
                                    <input class="form-control deal_1_square" type="text" value="">
                                  </div>
                              </div>
                            </li>
                            <li class="clearfix form-group inputs-group element" data-type="panel" step-order="1">
                              <span class="inputs-label"><?php echo Yii::t('DealsModule.base', 'Pay before');?></span>
                              <div class="columns-section col-1">
                                  <div class="column">
                                    <input class="form-control date deal_1_date" type="text" value="" data-date-format="dd.mm.yyyy">
                                  </div>
                              </div>
                            </li>
                            
                            <li class="clearfix form-group inputs-group element" data-type="panel" step-order="2">
                              <span class="inputs-label"><?php echo Yii::t('DealsModule.base', 'Amount of the second stage');?></span>
                              <div class="columns-section col-1">
                                  <div class="column">
                                    <input class="form-control deal_2_sum" type="text" value="0">
                                  </div>
                              </div>
                            </li>
                            <li class="clearfix form-group inputs-group element" data-type="panel" step-order="2">
                              <span class="inputs-label"><?php echo Yii::t('DealsModule.base', 'Area sq.m');?></span>
                              <div class="columns-section col-1">
                                  <div class="column">
                                    <input  class="form-control deal_2_square" type="text" value="">
                                  </div>
                              </div>
                            </li>
                            <li class="clearfix form-group inputs-group element" data-type="panel" step-order="2">
                              <span class="inputs-label"><?php echo Yii::t('DealsModule.base', 'Pay before');?></span>
                              <div class="columns-section col-1">
                                  <div class="column">
                                    <input class="form-control date deal_2_date" type="text" value="" data-date-format="dd.mm.yyyy">
                                  </div>
                              </div>
                            </li>
                            
                            <li class="clearfix form-group inputs-group element" data-type="panel" step-order="3">
                              <span class="inputs-label"><?php echo Yii::t('DealsModule.base', 'Amount of the third stage');?></span>
                              <div class="columns-section col-1">
                                  <div class="column">
                                    <input class="form-control deal_3_sum"  type="text" value="0">
                                  </div>
                              </div>
                            </li>
                            <li class="clearfix form-group inputs-group element" data-type="panel" step-order="3">
                              <span class="inputs-label"><?php echo Yii::t('DealsModule.base', 'Area sq.m');?></span>
                              <div class="columns-section col-1">
                                  <div class="column">
                                    <input class="form-control deal_3_square" type="text" value="">
                                  </div>
                              </div>
                            </li>
                            <li class="clearfix form-group inputs-group element" data-type="panel" step-order="3">
                              <span class="inputs-label"><?php echo Yii::t('DealsModule.base', 'Pay before');?></span>
                              <div class="columns-section col-1">
                                  <div class="column">
                                    <input class="form-control date deal_3_date" type="text" value="" data-date-format="dd.mm.yyyy">
                                  </div>
                              </div>
                            </li>
                            <li class="clearfix form-group inputs-group element" data-type="panel">
                              <span class="inputs-label"><?php echo Yii::t('DealsModule.base', 'Template');?>:</span>
                              <div class="columns-section col-1">
                                <div class="column">
                                  <select class="select deal_template element-is-scroll">
                                    <?php foreach($templates as $v) {?>
                                    <option value="<?php echo $v['documents_id'];?>"><?php echo $v['module_title'];?></option>
                                    <?php }?>
                                  </select>
                                </div>
                              </div>
                            </li>
                            <li class="clearfix form-group inputs-group element" data-type="panel">
                              <span class="inputs-label"><?php echo Yii::t('DealsModule.base', 'Joint property');?>:</span>
                              <div class="columns-section col-1">
                                <div class="column">
                                  <select class="select deal_j_property">
                                    <?php foreach($j_property as $k => $v) {?>
                                    <option value="<?php echo $k;?>"><?php echo $v;?></option>
                                    <?php }?>
                                  </select>
                                </div>
                              </div>
                            </li>       
                            <li class="clearfix form-group inputs-group element" data-type="panel">
                              <span class="inputs-label"><?php echo Yii::t('DealsModule.base', 'Amount of jur. services');?></span>
                              <div class="columns-section col-1">
                                  <div class="column">
                                    <input class="form-control deal_jur_service" type="text" value="4000">
                                  </div>
                              </div>
                            </li>                            
                            <li class="clearfix form-group inputs-group element" data-type="panel">
                              <span class="inputs-label"><?php echo Yii::t('DealsModule.base', 'Signed');?></span>
                              <div class="columns-section col-1">
                                <div class="column">
                                  <select class="select deal_signed">
                                    <?php foreach($signed as $k => $v) {?>
                                    <option value="<?php echo $k;?>"><?php echo $v;?></option>
                                    <?php }?>
                                  </select>
                                </div>
                              </div>
                            </li>
                            <li class="clearfix form-group inputs-group element" data-type="panel">
                              <span class="inputs-label"><?php echo Yii::t('DealsModule.base', 'Date of signing the agreement');?></span>
                              <div class="columns-section col-1">
                                  <div class="column">
                                    <input class="form-control date deal_date" type="text" value="<?php echo date('d.m.Y'); ?>" data-date-format="dd.mm.yyyy">
                                  </div>
                              </div>
                            </li>
                          </ul>
                          <div class="buttons-section">
                            <button type="submit" class="btn btn-primary deal_go"><?= Yii::t('DealsModule.base', 'Create');?></button>
                            <button type="button" class="btn btn-default close-button" data-dismiss="modal"><?= Yii::t('base', 'Cancel');?></button>
                          </div>
                        </div>
                      </section>
                    </div>
                  </div>
                  
                </span>

          <span class="element" data-type="button">
              <button type="submit" class="btn btn-primary <?php echo $this->button_attr['save']['class']?>"><?php echo \Yii::t('base', 'Save'); ?></button>
          </span>
      <?php
          }
      ?>
</div>