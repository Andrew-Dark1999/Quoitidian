<div class="element" data-type="block_participant" data-block_type="list_view">
<?php
    $select_model = (new ParticipantItemListBulder())
                            ->setBData($responsible_data)
                            ->setBDisplay((empty($participant_data_entities) && ListViewBulder::$participant_list_hidden == false ? false : true))
                            ->setBilHeaderTitle(Yii::t('messages', 'Appoint a responsible'))
                            ->setBaResponsibleAvatar(true)
                            ->setBilData($participant_data_entities)
                            ->setBilTypeItemList(ParticipantItemListBulder::TYPE_ITEM_LIST_PARTICIPANT)
                            ->setILinkRemove(false)
                            ->prepareHtml(ParticipantItemListBulder::VIEW_BLOCK);

    echo $select_model->getHtml();
?>
</div>
