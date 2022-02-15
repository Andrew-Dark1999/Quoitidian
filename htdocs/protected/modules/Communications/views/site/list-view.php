<?php
$this->renderPartial('//site/list-view', get_defined_vars());
?>

<script>
    <?php if(!(CommunicationsServiceParamsModel::issetUserParams())){ ?>
    if(instanceGlobal) {
        instanceGlobal.contentReload.actionShowCommunicationConfigPopup();
    }
    else{
        $(document).on('ready',function () {
            instanceGlobal.contentReload.actionShowCommunicationConfigPopup();
        });
    }
    <?php }else{ ?>

    <?php } ?>
</script>
