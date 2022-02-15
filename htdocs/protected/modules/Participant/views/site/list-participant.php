<div class="modal-dialog" style="width: 620px;">
    <section class="panel"  >
        <div class="edit-view in submodule-view sm_extension_relate_participant"
             data-parent_copy_id="<?php echo $parent_copy_id; ?>"
             data-parent_data_id="<?php echo $parent_data_id; ?>"
            >
            <header class="panel-heading">
                <?php echo Yii::t('ParticipantModule.base', 'Participants project'); ?>
                <span class="tools pull-right">
                <a href="javascript:void(0)" data-dismiss="modal" class="fa close-button"></a>
            </span>
            </header>

            <div class="panel-body" style="padding: 15px 10px" >
                <div class="search-section">
                    <input type="text" class="submodule-search form-control" placeholder="<?php echo Yii::t('base', 'Search'); ?>">
                </div>
                <div class="submodule-table">
                    <table class="table list-table">
                        <tbody>
                        <?php
                        if(!empty($users)){
                            foreach($users as $value){
                                ?>
                                <tr class="sm_extension_data"
                                    data-id="<?php echo $value->getPrimaryKey(); ?>"
                                    >

                                    <td>
                                        <?php
                                            $value_field = Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ListView.Elements.Avatar.Avatar'),
                                                array(
                                                    'data_array' => $value->getAttributes(),
                                                    'thumb_size' => 32,
                                                ),
                                                true);

                                            $value_field.=$value->getFullName();
                                            ?>
                                            <span href="javasctript:void(0)" class="name"><?php echo $value_field; ?></span>
                                    </td>
                                    <td><input type="checkbox" class="checkbox"></td>
                                </tr>
                            <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="2">
                                    <?php echo Yii::t('ParticipantModule.base', 'Participants all users add'); ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
                <div class="buttons-section">
                    <?php if(!empty($users)) { ?>
                        <button type="submit" class="btn btn-primary participant_list_view_btn-tie"><?php echo Yii::t('base', 'Link')?></button>
                    <?php } ?>
                    <button type="button" class="btn btn-default close-button" data-dismiss="modal"><?php echo Yii::t('base', 'Cancel')?></button>
                </div>
            </div>
        </div>
    </section>
</div>