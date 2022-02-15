<span
    class="participant submodule-link dropdown crm-dropdown dropdown-right element <?php if($this->model->getBDisplay() == false) echo 'hide'; ?>"
    data-type="select"
    data-type_item_list="<?php echo $this->model->getBilTypeItemList(); ?>"
>
    <?php
        $this->model->getPrepareBlockAvatarParams();
        $this->render('block-avatar');
        $this->render('block-item-list');
    ?>
</span>
