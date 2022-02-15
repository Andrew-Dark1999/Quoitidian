<?php
    $crm_properties = [
        '_active_object' => $this,
        '_extension_copy' => $extension_copy,
    ];
?>
<?php if(ControllerModel::inContentBlock(ControllerModel::CONTENT_BLOCK_1)){ ?>
<div class="calendar_view_block list_view_block copy_id<?php echo $extension_copy->copy_id; ?> sm_extension"
     data-copy_id="<?php echo $extension_copy->copy_id; ?>"
     data-page_name="calendarView"
     data-parent_copy_id="<?php echo (array_key_exists('pci', $_GET) ? $_GET['pci'] : ''); ?>"
     data-parent_data_id="<?php echo (array_key_exists('pdi', $_GET) ? $_GET['pdi'] : ''); ?>"
     data-this_template="<?php echo (integer)$this->this_template; ?>"
>


    <div class="filter-block clearfix">
        <div class="wievs_tuggle">
            <?php
            foreach($this->getSwitchIconList($extension_copy) as $switch_icon){
                echo '<a class="'.$switch_icon['class'].'" data-action_key="'.$switch_icon['data-action_key'].'" data-type="'.$switch_icon['data-type'].'" href="javascript:void(0)"><i class="'.$switch_icon['i_class'].'"></i></a>';
            }
            ?>
        </div>

        <form class="search-filter" method="get">
            <div class="search-field form-control">
                <div class="filters-installed">
                    <?php echo $filters_installed ?>
                </div>
                <input type="text" class="search-input" placeholder="<?php echo Yii::t('base', 'Search'); ?>" value="<?php if(Search::$text !== null) echo Search::$text; ?>">
            </div>
        </form><!-- /.search-block -->
        <?php if($this->module->list_view_btn_filter){ ?>
            <div class="btn-group crm-dropdown dropdown-right edit-dropdown">
                <button class="btn dropdown-toggle btn-create btn-round" data-toggle="dropdown"><i class="fa fa-filter"></i></button>
                <ul class="dropdown-menu dropdown-shadow filter-menu">
                    <?php if($filter_menu_list_virual) echo $filter_menu_list_virual; ?>
                    <li><a href="javascript:void(0);" class="filter-create" ><i class="fa fa-plus-circle"></i><?php echo Yii::t('base', 'Create filter'); ?></a></li>
                    <?php if($filter_menu_list) { ?>
                        <li><span class="filter-separator filters-created"><?php echo Yii::t('base', 'Created filters'); ?></span></li>
                        <?php echo $filter_menu_list; ?>
                    <?php } ?>
                </ul>
                <div class="hover_notif"><?= Yii::t('base', 'Filters'); ?></div>
            </div>
        <?php } ?>

        <?php if($this->module->finishedObject()){?>
            <div class="btn-group">
                <button class="btn btn-default btn-round element <?php if($finished_object) echo 'active'; ?>"
                        data-type="finished_object"
                        data-action_key="<?php echo (new ContentReloadModel(8, $crm_properties))->prepare()->getKey(); ?>">
                    <i class="fa fa-check-square"></i>
                </button>
                <div class="hover_notif"><?= Yii::t('constructor', 'Completed'); ?></div>
            </div>
        <?php } ?>

        <?php if($this->module->list_view_btn_filter){ ?>
            <div class="filter" style="display: none;">
                <hr>
                <div class="filter-box-container"></div>
            </div>
        <?php } ?>
    </div>

    <div class="hide element" data-type="edit-view">
        <div class="fc-event fc-event-skin fc-event-hori element" data-type="card">
            <div class="fc-event-inner fc-event-skin">
                    <span class="fc-event-title element sm_extension_data"
                          data-controller="ev"
                          data-name="module_title" data-id="{1}" data-type="title">{0}</span>
            </div>
        </div>
    </div>

    <div class="list-view-panel content-panel">
<!--        --><?php //$this->renderPartial('//site/calendar-view-template.php'); ?>
        <section class="panel">
            <header class="panel-heading element" data-type="title-module"></header>
            <div class="panel-body">
                <div class="adv-table editable-table">
                    <div id="list-table_wrapper_all" class="list-table_wrapper_all">
                        <div class="row">
                            <div class="col-sm-12">

                                <div id="calendar" class="has-toolbar fc element calendar-block" data-type="calendar">
                                    <table class="fc-header" style="width:100%">
                                        <tbody>
                                        <tr>
                                            <td class="fc-header-left">

                                                <div class="btn-group fc-button">
                                                    <button class="btn btn-default btn-round element btn-left"> <i class="fa fa-chevron-left"></i> </button>
                                                    <button class="btn btn-default btn-round element btn-right"><i class="fa fa-chevron-right "></i></button>
                                                    <button type="button" class="btn btn-default btn-today">today</button>
                                                </div>

                                                <span class="fc-header-title">
                                            <h2 class="element" data-type="period"></h2>
                                        </span>
                                            </td>
                                            <td class="fc-header-center">
                                            </td>
                                            <td class="fc-header-right element" data-type="sort-period">
                                                <div class="fc-button">
                                                    <div class="btn-group" data-toggle="buttons">
                                                        <label class="btn btn-default element"  data-type="month">
                                                            <input type="radio" name="options" id="month"> <span>month</span>
                                                        </label>
                                                        <label class="btn btn-default element"  data-type="week">
                                                            <input type="radio" name="options" id="week"> <span>week</span>
                                                        </label>
                                                        <label class="btn btn-default element"  data-type="days">
                                                            <input type="radio" name="options" id="day"> <span>day</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <div class="fc-content calendar-view">
                                        <div class="fc-view fc-view-month fc-grid">
                                            <table class="fc-border-separate" style="width:100%" cellspacing="0">
                                                <thead>
                                                <tr class="fc-first fc-last">
                                                    <th class="fc-widget-header fc-first"></th>
                                                    <th class="fc-widget-header" ></th>
                                                    <th class="fc-widget-header" ></th>
                                                    <th class="fc-widget-header" ></th>
                                                    <th class="fc-widget-header" ></th>
                                                    <th class="fc-widget-header" ></th>
                                                    <th class="fc-widget-header" ></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr class="fc-week">
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content fc-last">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr class="fc-week">
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content fc-last">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr class="fc-week">
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content fc-last">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr class="fc-week">
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content fc-last">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr class="fc-week">
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content fc-last">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr class="fc-week fc-last">
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content fc-last">
                                                        <div>
                                                            <div class="fc-day-number"></div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>

                                        </div>
                                        <div class="fc-view hide fc-view-basicWeek fc-grid">
                                            <table class="fc-border-separate" style="width:100%" cellspacing="0">
                                                <thead>
                                                <tr class="fc-first">
                                                    <th class=""></th>
                                                    <th class="fc-widget-header element" data-type="week-title"></th>
                                                    <th class="fc-widget-header element" data-type="week-title"></th>
                                                    <th class="fc-widget-header element" data-type="week-title"></th>
                                                    <th class="fc-widget-header element" data-type="week-title"></th>
                                                    <th class="fc-widget-header element" data-type="week-title"></th>
                                                    <th class="fc-widget-header element" data-type="week-title"></th>
                                                    <th class="fc-widget-header element" data-type="week-title"></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr class="hide default">
                                                    <td class="time-cell">
                                                        <div class="time element" data-type="time"></div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div>
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div><div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div><div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div><div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div><div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div><div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                    <td class="fc-widget-content">
                                                        <div><div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="fc-view hide fc-view-basicDay fc-grid">
                                            <table class="fc-border-separate" style="width:100%" cellspacing="0">
                                                <thead>
                                                <tr class="fc-first fc-last">
                                                    <th class="fc-wed element fc-widget-header fc-first"></th>
                                                    <th class="fc-wed element fc-widget-header fc-last"></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr class="hide default">
                                                    <td class="time-cell fc-widget-content">
                                                        <div class="time element" data-type="time"></div>
                                                    </td>
                                                    <td class="fc-wed fc-widget-content fc-first fc-last fc-state-highlight fc-today">
                                                        <div style="">
                                                            <div class="fc-day-content element" data-type="day-content"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

</div>
<?php } ?>

<?php
    $action_key_b = (new ContentReloadModel(null, $crm_properties))->prepareModuleList(true)->getKey();
?>

<script>
    $(function(){
        //if (Global.getInstance().isCash()) return;
        if (window.backForward) return;

        var content_vars = '<?php echo ContentReloadModel::getContentVars(true, false); ?>',
            param1 = [<?php echo $action_key_b; ?>, true],
            param2 = [<?php echo $action_key_b; ?>, false];

        var contentReload = Global.getInstance().getContentReloadInstance();
        var object = contentReload || {},
            reloadPage;

        //F5 перегрузка страницы
        if (!Object.keys(object).length) {
            reloadPage = true;
        }

        object.afterLoadView = function (json) {
            var instance,
                globalInstance = Global.getInstance();

            globalInstance.setPreloader(this.preloader); //подовжуємо роботу прелоадера через інтерфейс
            instance = CalendarView.getInstance(true);
            globalInstance.setSubInstance(instance);

            //по F5
            if (!reloadPage) {
                $('.content-panel').empty().append($(CalendarView.getTemplate()));
            }

            instance
                .setCopyId(<?php echo $extension_copy->copy_id; ?>)
                .run()

            // don't hide preloader
            this.hidePreloader = function () {
                return this;
            }

            return this;
        }


        if (contentReload) {
            contentReload.setVarsFromPage(content_vars, param1, param2);
        }  else {
            instanceGlobal.contentReload.setVarsFromPage(content_vars, param1, param2);
            if (!instanceGlobal.contentReload.isRunning()) {
                object.afterLoadView();
            }
        }
    })
</script>

