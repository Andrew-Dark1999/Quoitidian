<li class="element prog-row clearfix sm_extension_data"
    data-type="calls_menu_channel"
    data-controller="edit_view"
    data-render_type="html"
    data-id="<?php echo $calls_model->calls_id; ?>">
    <div class="offset-right">
        <div>
            <span class="title">
                <a href="javascript:void(0)">
                    <?php echo $calls_model->getClientTitle(); ?>
                </a>
            </span>
            <p>
                <?php echo $calls_model->getContactTitle(); ?>
            </p>
        </div>
    </div>
</li>
