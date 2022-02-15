<div class="element btn-list-icons" data-type="navigation_block">
    <?php foreach($block_model_list as $quick_view_block_model){ ?>

        <?php if($quick_view_block_model->getName() == 'communications'){ ?>
            <span
                class="element comments <?php echo ($quick_view_block_model->getName() == $this->quick_view_model->getName() ? 'active' : ''); ?>"
                data-type="link"
                data-block_name="communications"
            >
                <i class="fa fa-comments-o" aria-hidden="true"></i>
            </span>
        <?php } ?>
        <?php if($quick_view_block_model->getName() == 'calls'){ ?>
            <span
                class="element handset <?php echo ($quick_view_block_model->getName() == $this->quick_view_model->getName() ? 'active' : ''); ?>"
                data-type="link"
                data-block_name="calls"
            >
                <i class="fa fa-phone" aria-hidden="true"></i>
            </span>
        <?php } ?>
    <?php } ?>
</div>
