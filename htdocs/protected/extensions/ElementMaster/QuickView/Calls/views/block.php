<div class="right-stat-bar element hide" data-type="content_block" data-name="<?php echo $this->quick_view_model->getName(); ?>">
    <ul class="right-side-accordion">
        <li class="widget-collapsible section">
            <a href="javascript:void(0)" class="section-title widget-head purple-bg active clearfix display-flex flex-content-middle">
                <span class="pull-left"><?php echo $this->quick_view_model->getTitle(); ?></span>
                <?php echo $this->getButtonSwitch(); ?>
            </a>
            <div class="element" data-type="container">
                <ul class="widget-container channels-list sm_extension content-box"
                    data-copy_id="<?php echo ExtensionCopyModel::MODULE_CALLS; ?>"
                    data-page_name="listView"
                    data-parent_copy_id=""
                    data-parent_data_id=""
                    data-this_template="0"
                >
                    <?php echo $this->content; ?>
                </ul>

                <ul class="widget-container element" data-type="footer_buttons">
                    <?php echo $this->getFooterButtons(); ?>
                </ul>
            </div>
        </li>

        <script type="text/javascript" >
            $(document).ready(function () {
                instanceGlobal.contentReload.setVarsFromPage('<?php echo \ContentReloadModel::getContentVars(); ?>', null, null);
            })
        </script>
    </ul>
</div>
