<div class="horizontal-menu navbar-collapse element" data-type="module_menu">
    <ul class="nav navbar-nav">
        <?php
            $menu_count = $this->getTopModuleMenuHistoryCount();
            $extension_copy_data = $this->getExtensionCopyForModuleMenu();
            
            if(!empty($extension_copy_data)){
                if($menu_count === null || (integer)$menu_count > count($extension_copy_data)){
                    $menu_count = count($extension_copy_data);
                    History::getInstance()->setUserStorage(UsersStorageModel::TYPE_MENU_COUNT, 1, array('count' => (string)$menu_count));
                }
                $menu_count = (integer)$menu_count;
                $lich = 0;
        		foreach($extension_copy_data as $value){
                    $lich++;
        ?>
                    <li <?php if(isset($this->data['menu_main']['index']) && $this->data['menu_main']['index'] == $value['copy_id']) echo 'class="active"'  ?> data-sort_index="<?php echo $value->sort; ?>">
                        <a class="ajax_content_reload" data-action_key="<?php echo (new \ContentReloadModel(6, ['_use_auto_pci_pdi'=> false]))->addVars(['module'=>['copy_id' => $value['copy_id'], 'params'=>['this_template'=>0]]])->prepare()->getKey(); ?>" href="javascript:void(0)"><?php echo $value['title']; ?></a>
                    </li>
        <?php
                    if($menu_count == $lich) break;
                }
            }

            // скрытое меню
        ?>
    	<li id="more-links">
    		<a class="dropdown" href="javascript:" data-toggle="dropdown"><?php echo Yii::t('base', 'More'); ?> <b class="fa fa-angle-down"></b></a>
    		<ul class="dropdown-menu" role="menu">
                <?php
                    if(!empty($extension_copy_data)){
                        $lich = 0;
                		foreach($extension_copy_data as $value){
                		    $lich++;
                            if(($lich) <= (integer)$menu_count) continue;
                ?>
                            <li <?php if(isset($this->data['menu_main']['index']) && $this->data['menu_main']['index'] == $value['copy_id']) echo 'class="active"'  ?> data-sort_index="<?php echo $value->sort; ?>">
                                <a class="ajax_content_reload" data-action_key="<?php echo (new \ContentReloadModel(6, ['_use_auto_pci_pdi'=> false]))->addVars(['module'=>['copy_id' => $value['copy_id'], 'params'=>['this_template'=>0]]])->prepare()->getKey(); ?>" href="javascript:void(0)"><?php echo $value['title']; ?></a>
                            </li>
                <?php
                        }
                    }
                ?>
    		</ul>
    	</li>
    </ul>
</div>

<script type="text/javascript" >
    $(document).ready(function () {
        instanceGlobal.contentReload.setVarsFromPage('<?php echo \ContentReloadModel::getContentVars(); ?>', null, null);
    })
</script>
