<ul class="dropdown-menu dropdown-shadow local-storage reports-menu element" data-type="settings" role="menu">
    <?php if($element['graph_type'] == \Reports\models\ConstructorModel::GRAPH_LINE){ ?>
    <li>
        <select class="selectpicker element" data-type="period">
            <?php
                if(!empty($schema['data']['periods']))
                foreach($schema['data']['periods'] as $period => $title){ 
            ?>
            <option
                value="<?php echo $period; ?>"
                <?php if($element['period'] == $period)  echo 'selected="selected"' ?>
             ><?php echo $title; ?></option>
            <?php } ?>                 
        </select>
    </li>
    <?php } ?>
    <?php
    /* if($element['graph_type'] == \Reports\models\ConstructorModel::GRAPH_HISTOGRAM){ ?>
    <li>
        <select class="selectpicker element" data-type="display_option">
            <?php
                foreach(\Reports\models\ConstructorModel::getGraphDisplayOptions() as $index => $title){
            ?>
            <option
                value="<?php echo $index; ?>"
                <?php if($element['display_option'] == $index)  echo 'selected="selected"' ?>
            ><?php echo $title; ?></option>
            <?php } ?>                 
        </select>
    </li>    
    <?php } */ ?>

    <?php 
        \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Constructor\Graph\Graph',
                           array(
                            'views' => array('block_block_setting_indicator'),
                            'schema' => $schema,
                            'element' => $element,
                           ));
     ?>

    <li><span style="display: none;" class="params_hidden"><?php echo $params_hidden; ?></span></li>
</ul>
