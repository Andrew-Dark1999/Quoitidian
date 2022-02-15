<?php
    $relate_value = array();
    $vars = get_defined_vars();
    $relate_model = EditViewRelateModel::getInstance()
        ->setVars($vars)
        ->prepareVars();

    if(!isset($schema['params']['relate_get_value']) || (boolean)$schema['params']['relate_get_value'] == true){
        $relate_value = $relate_model->getValue();
    }
    $option_list = $relate_model->getOptionsDataList();
    $value = DataValueModel::getInstance()->getRelateValuesToHtml($relate_value, $schema['params']);
?>

<div class="column">
    <div class="dropdown submodule-link crm-dropdown element" data-type="drop_down">
        <button <?php echo $relate_model->getRelateDisabledAttr(); ?>
            name="<?php echo $this->getName(); ?>"
            class="btn btn-white dropdown-toggle <?php echo $this->getClass()?>element_relate"
            data-reloader="<?php echo $relate_model->getReloaderStatus(); ?>"
            data-module_parent="<?php echo ($relate_model->isModuleParent() ? 1 : 0); ?>"
            data-toggle="dropdown"
            data-id="<?php echo (!isset($schema['params']['relate_get_value']) || (boolean)$schema['params']['relate_get_value'] == true ? $relate_model->getId() : '') ?>"
            data-relate_copy_id="<?php echo $schema['params']['relate_module_copy_id']; ?>"
            data-type="drop_down_button"
        >
            <?php echo $value; ?>
        </button>

        <ul
            class="dropdown-menu element"
            data-type="drop_down_list"
            data-there_is_data="0"
            data-relate_copy_id="<?php echo $schema['params']['relate_module_copy_id']; ?>"
            role="menu"
            aria-labelledby="dropdownMenu1"
        >
            <div class="search-section">
                <input type="text" class="submodule-search form-control" placeholder="<?php echo Yii::t('base', 'Search'); ?>">
            </div>

            <div class="submodule-table">
                <table class="table list-table">
                    <tbody>
                    <?php
                    foreach($option_list as $option){
                        ?>
                        <tr class="sm_extension_data" data-id="<?php echo $option[$relate_model->relate_extension_copy->prefix_name . '_id']; ?>">
                            <td>
                                <span href="javasctript:void(0)" class="name"><?php echo DataValueModel::getInstance()->setFileLink(false)->getRelateValuesToHtml($option, $schema['params']); ?></span>
                            </td>
                        </tr>

                        <?php
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </ul>
    </div>
    <?php echo CHtml::error($extension_data, $schema['params']['name']); ?>
</div>
