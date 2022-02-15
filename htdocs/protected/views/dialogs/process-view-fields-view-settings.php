<div class="modal-dialog" style="width: 620px;" data-is-center>
    <section class="panel" >
        <div class="edit-view in sm_extension sm_extension_export no_middle">
            <header class="panel-heading editable-block hidden-edit">
            <span class="client-name">
               <span><?php echo Yii::t('base', 'Display Settings') ?></span>
            </span>
                <span class="tools options">
                <a href="javascript:void(0)" data-dismiss="modal" class="fa close-button"></a>
            </span>
            </header>
            <div class="panel-body fields-view-settings">
                <ul>
                    <li class="clearfix form-group inputs-group element" data-type="panel">
                        <span class="inputs-label"><?php echo Yii::t('base', 'Display field') ?></span>
                        <div class="columns-section col-1">
                            <div class="column">
                                <select class="select element" data-type="fields_view">
                                    <?php
                                        if(!empty($fields_view_list)){
                                            foreach($fields_view_list as $row){
                                                ?>
                                                <option value="<?php echo $row['value'] ?>" <?php echo($row['active'] ? 'selected' : '') ?>><?php echo $row['title'] ?></option>
                                                <?php
                                            }
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="buttons-section">
                    <button type="submit" class="btn btn-primary element" data-type="save"><?php echo Yii::t('base', 'Save')?></button>
                    <button type="button" class="btn btn-default close-button" data-dismiss="modal"><?php echo Yii::t('base', 'Cancel')?></button>
                </div>
            </div>
        </div>
    </section>
</div>
