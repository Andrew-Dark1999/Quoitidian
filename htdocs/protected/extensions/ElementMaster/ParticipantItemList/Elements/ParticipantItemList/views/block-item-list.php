<?php
    if($this->model->isShowBlockItemList() == false){
        return;
    }

    $item_params = $this->model->getItemParams();
?>

<ul class="dropdown-menu element" data-type="drop_down_list" role="menu">
        <?php if($this->model->getBilHeaderTitle() !== null){ ?>
            <div class="participants-title"><b><?php echo $this->model->getBilHeaderTitle(); ?></b></div>
        <?php } ?>


        <?php if($this->model->getBilItemListSwitchShow()){ ?>
            <div class="btn-group btn-participant-email" data-toggle="buttons">
                <label class="btn btn-default element <?php echo $this->model->getItemSwitchKeyActiveLabel(ParticipantItemListBulder::TYPE_ITEM_LIST_PARTICIPANT) ?>" data-type="item_list_switch" data-type_item_list="<?php echo ParticipantItemListBulder::TYPE_ITEM_LIST_PARTICIPANT; ?>">
                    <?php echo Yii::t('base', 'Participant') ?>
                </label>
                <label class="btn btn-default element <?php echo $this->model->getItemSwitchKeyActiveLabel(ParticipantItemListBulder::TYPE_ITEM_LIST_EMAIL) ?>" data-type="item_list_switch" data-type_item_list="<?php echo ParticipantItemListBulder::TYPE_ITEM_LIST_EMAIL; ?>">
                    <?php echo 'Email' ?>
                </label>
            </div>
        <?php } ?>

        <div class="search-section">
        <?php if(in_array($this->model->getBilTypeItemList(), [ParticipantItemListBulder::TYPE_ITEM_LIST_SELECTED_ITEM_LIST, ParticipantItemListBulder::TYPE_ITEM_LIST_PARTICIPANT])){ ?>
            <input type="text" class="submodule-search form-control" placeholder="<?php echo Yii::t('base', 'Search'); ?>">
        <?php } ?>
        <?php if($this->model->getBilTypeItemList() == ParticipantItemListBulder::TYPE_ITEM_LIST_EMAIL){ ?>
            <input type="text" class="submodule-search form-control" placeholder="<?php echo Yii::t('base', 'Email'); ?>">
            <span class="add"><i class="fa fa-plus-circle" aria-hidden="true"></i></span>
        <?php } ?>
        </div>


        <div class="submodule-table">
            <table <?php echo $item_params['table_attr']; ?>
            <tbody>
                <?php
                    if($item_params['item_data_list']){
                        foreach($item_params['item_data_list'] as $item_data){
                            $model_item = clone($this->model);
                            $model_item
                                ->setIData($item_data)
                                ->setBilTypeItemListByIDataModel();

                            echo $model_item->getWingetHtml($model_item->getItemParamsView());
                        }
                    }
                ?>
            </tbody>
            </table>

        </div>

    <?php if($this->model->getBilLinkItemListAddParticipant()  && $this->model->getBilTypeItemList() == ParticipantItemListBulder::TYPE_ITEM_LIST_SELECTED_ITEM_LIST){ ?>
        <div class="participant-choice"><a href="javascript:void(0)" class="link-item-list-add-participant"><?php echo Yii::t('base', 'Add the participant'); ?></a></div>
    <?php } ?>
    <?php if($this->model->getBilLinkItemListAddEmail() && $this->model->getBilTypeItemList() == ParticipantItemListBulder::TYPE_ITEM_LIST_SELECTED_ITEM_LIST){ ?>
        <div class="participant-choice"><a href="javascript:void(0)" class="link-item-list-add-email"><?php echo Yii::t('base', 'Add the participant'); ?></a></div>
    <?php } ?>
    <?php if($this->model->getBilLinkSelectedItemList()){ ?>
        <div class="participant-choice"><a href="javascript:void(0)" class="link-selected-item-list"><?php echo Yii::t('base', 'Participants'); ?></a></div>
    <?php } ?>
</ul>


