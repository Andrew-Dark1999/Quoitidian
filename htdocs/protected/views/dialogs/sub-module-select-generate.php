<div class="modal-dialog" style="width: 620px;">
    <section class="panel" >
    <div class="edit-view in sm_extension sm_extension_generate no_middle">
        <span style="display: none;" class="default_data"></span>
        <header class="panel-heading editable-block hidden-edit">
            <span class="client-name">
               <span><?php echo Yii::t('base', 'Generate') ?></span>
            </span>
        	<span class="tools options">
                <a href="javascript:void(0)" data-dismiss="modal" class="fa close-button"></a>
            </span>
        </header>

        <div class="panel-body">
            <div class="panel-body">
            
<?php
            foreach($sm as $title => $subModule) {
?>
                <ul class="inputs-block">
                    <li class="clearfix form-group inputs-group">
                        <span class="inputs-label"><?php echo $title; ?></span>
                        <div class="columns-section col-1">
                            <div class="column">
                                    <div class="column">
                                         <div class="dropdown submodule-link crm-dropdown">
                                            <button 
                                                   class="btn btn-white element_relate"
                                                   data-toggle="dropdown"
                                                   data-relate_copy_id="<?php echo $subModule[0]; ?>"
                                                   data-mod_id="<?php echo $subModule[1]; ?>"
                                                   >
                                                   <?php echo '';  /*дефолтное значение*/?>
                                            </button>
                                                                
                                            <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                                                <div class="search-section">
                                                    <input type="text" class="submodule-search form-control" placeholder="<?php echo Yii::t('base', 'Search'); ?>">
                                                </div>
                                                <div class="submodule-table">
                                                    <table class="table list-table">
                                                    <tbody>
                                                    
                                                    <?php
                                                        foreach($subModule[2] as $v) {
                                                            $first = current($v);
                                                            
                                                    ?>
                                                        <tr class="sm_extension_data" data-id="<?php echo $first; ?>">
                                                            <td>
                                                                <label class="multilabel">
                                                                    <span href="javasctript:void(0)" class="name"><?php echo $v['module_title']; ?></span>
                                                                    <input type="checkbox">
                                                                </label>
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
                                 </div>
                            </div>
                        </div>
                    </li>
                </ul>       
<?php
            }
?>
    
         
        </div>
        <div class="buttons-section">
        
            <input class="element" data-name="copy_id" value='<?php echo $copy_id; ?>' style="display: none;">
            <input class="element" data-name="form_data" value='<?php echo $form_data; ?>' style="display: none;">
            <input class="element" data-name="sdm_data" value='<?php echo $sdm_data; ?>' style="display: none;">
            <input class="element" data-name="sm_data" value='<?php echo $sm_data; ?>' style="display: none;">
            <input class="element" data-name="service_data" value='<?php echo $service_data; ?>' style="display: none;">
            <input class="element" data-name="vars" value='<?php echo $vars; ?>' style="display: none;">
        
            <button type="submit" class="btn btn-primary edit_view_select_btn-generate"><?php echo Yii::t('base', 'Generate')?></button>
            <button type="button" class="btn btn-default close-button" data-dismiss="modal"><?php echo Yii::t('base', 'Cancel')?></button>
        </div>
            
    
        </div>
    </div>

</section>
</div>
