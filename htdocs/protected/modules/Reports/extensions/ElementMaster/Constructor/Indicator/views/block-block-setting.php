<ul class="dropdown-menu dropdown-shadow reports-menu element" data-type="settings">
    <?php 
        if(!empty($schema['elements'])){
            foreach($schema['elements'] as $element){
                \Yii::app()->controller->widget('\Reports\extensions\ElementMaster\Constructor\Indicator\Indicator',
                                   array(
                                    'views' => array('block-block-setting-indicator'),
                                    'schema' => $schema,
                                    'element' => $element,
                                   ));
            }
        }
     ?>
    <li>
        <?php 
            $style = '';
            if(count($schema['elements']) >= \Reports\models\ConstructorModel::SETTING_INDICATOR_MAX_COUNT)
                $style = 'style="display : none"';
        ?>
        <a href="javascript:void(0)" class="sub-menu-link add-field element" data-type="add_indicator" <?php echo $style; ?>><?php echo \Yii::t('ReportsModule.base', 'Add indicator'); ?></a>
    </li>
</ul>
