<div
    class="panel-body element"
    <?php
        foreach($vars['attr'] as $key => $value){
            echo $key . '="' . $value . '" ';
        }
    ?>
>
    <div class="search-section">
        <input type="text" class="submodule-search form-control" placeholder="<?php echo Yii::t('base', 'Search'); ?>">
    </div>
    <div class="submodule-table">
        <table class="table list-table">
            <tbody>
            <?php
                echo $html_option;
            ?>
            </tbody>
        </table>
    </div>
</div>
