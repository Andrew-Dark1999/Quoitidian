<div
    <?php
    //prepare attr for button
    foreach($view['attr'] as $key => $value){
        echo $key . '="' . $value . '" ';
    }
    ?>
>
    <button
        <?php
        //button
        foreach($view['button'] as $name => $value){
            echo $name . '="' . $value . '" ';
        }
        ?>
    >
        <?php echo $view['html_view']; ?>
    </button>


    <?php
        //button_actions
        if(!empty($view['button_actions'])){
        ?>
        <span class="icon-operation element" data-type="actions">
            <?php
                $params = [
                        'view' => 'view-button-actions',
                        'vars' => [
                                'button_actions' => $view['button_actions']
                            ]
                        ];
                Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.DropDownList.DropDownList'), $params);
            ?>
        </span>
    <?php }

        //html_options
        echo $view['html_options'];
    ?>
</div>
