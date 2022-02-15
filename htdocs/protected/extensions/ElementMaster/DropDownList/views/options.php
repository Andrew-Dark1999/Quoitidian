<ul
    class="dropdown-menu element"
    role="menu"
    aria-labelledby="dropdownMenu1"
    <?php
    foreach($vars['attr'] as $key => $value){
        echo $key . '="' . $value . '" ';
    }
    ?>
>
    <?php if($vars['search_display']== true){ ?>
    <div class="search-section">
        <input type="text" class="submodule-search form-control" placeholder="<?php echo Yii::t('base', 'Search'); ?>">
    </div>
    <?php } ?>
    <div class="submodule-table">
        <table class="table list-table">
            <tbody>
            <?php
                echo $html_option;
            ?>
            </tbody>
        </table>
    </div>



    <?php
    //button_actions
    if(!empty($vars['button_actions'])){
        $params = [
                'view' => 'view-options-button',
                'vars' => [
                        'button_actions' => $vars['button_actions']
                    ]
                ];
        Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.DropDownList.DropDownList'), $params);
    }  ?>
</ul>
