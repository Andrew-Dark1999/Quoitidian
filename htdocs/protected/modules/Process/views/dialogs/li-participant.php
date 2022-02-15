<li class="clearfix form-group inputs-group element" data-type="dinamic">
    <span class="inputs-label"><?php echo (!empty($rbr_model) ? $rbr_model->getLableTitle() : Yii::t('ProcessModule.base', 'Participants')); ?></span>
    <div class="columns-section col-1">
        <div class="column">
            <div class="element"
                 data-type="participant_block<?php echo (!empty($rbr_model) ? $rbr_model->getBlockIndex() : ''); ?>"
                 data-ug_id="<?php echo $base_ug_id; ?>"
                 data-ug_type="<?php echo $base_ug_type; ?>"
                 >
                <div class="dropdown submodule-link crm-dropdown element" data-type="drop_down">
                    <button
                        name="participant"
                        class="btn btn-white dropdown-toggle element_relate_participant element"
                        type="button"
                        data-toggle="dropdown"
                        data-ug_id="<?php echo $ug_id; ?>"
                        data-ug_type="<?php echo $ug_type; ?>"
                        data-type="drop_down_button"
                    ><?php echo $html_active_responsible; ?></button>
                    <ul
                        class="dropdown-menu element"
                        data-type="drop_down_list"
                        data-there_is_data="0"
                        data-relate_copy_id="<?php echo \ExtensionCopyModel::MODULE_PARTICIPANT; ?>"
                        role="menu"
                        aria-labelledby="dropdownMenu1"
                    >
                        <div class="search-section">
                            <input type="text" class="submodule-search form-control" placeholder="<?php echo Yii::t('base', 'Search'); ?>">
                        </div>

                        <div class="submodule-table">
                            <table class="table list-table">
                                <tbody>
                                <?php
                                if(!empty($html_values)){
                                    foreach($html_values as $value){
                                        ?>

                                        <tr class="sm_extension_data" data-ug_id="<?php echo $value['ug_id']; ?>" data-ug_type="<?php echo $value['ug_type']; ?>">
                                            <td>
                                                <span href="javasctript:void(0)" class="name"><?php echo $value['html']; ?></span>
                                            </td>
                                        </tr>
                                    <?php }} ?>
                                </tbody>
                            </table>
                        </div>
                    </ul>
                </div>
                <div class="errorMessage"><?php if(!empty($rbr_model) && $rbr_model->getStatus() == false) echo $rbr_model->getMessage('participant_block' . $rbr_model->getBlockIndex()) ?></div>
            </div>
        </div>
    </div>
</li>
