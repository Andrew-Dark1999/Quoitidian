<?php
    if(in_array($key, ['step1_panel','step2_panel','step3_panel','step4_panel','step5_panel'])){
?>
<div class="guide-panel">
    <div class="guide-sidebar"
         data-key="">

        <div data-position="vertical">
            <div class="steps">
                <?php
                if($key == 'step1_panel'){ ?>
                    <div class="item">
                        <h1><?php echo Yii::t('startup-guide', 'Users'); ?></h1>
                        <p><?php echo Yii::t('startup-guide', 'The Quotidian platform will allow you to solve the tasks more effectively if you invite your entire department.'); ?></p>
                        <p><?php echo Yii::t('startup-guide', 'Specify the e-mail addresses of the colleges so that they can connect to work. If necessary, you can configure the access rights and roles.'); ?></p>
                    </div>
                <?php }
                if($key == 'step2_panel'){ ?>
                    <div class="item">
                        <h1><?php echo Yii::t('startup-guide', 'Modules'); ?></h1>
                        <p><?php echo Yii::t('startup-guide', 'In order to configure the system to solve your problems, you can turn off or turn off the modules that you need or otherwise are not required.'); ?></p>
                    </div>
                <?php }
                if($key == 'step3_panel'){ ?>
                    <div class="item">
                        <h1><?php echo Yii::t('startup-guide', 'Stages of sales'); ?></h1>
                        <p><?php echo Yii::t('startup-guide', 'Before buying any potential customer goes through a set of stages in the transaction. The consistency of these stages is your sales funnel.'); ?></p>
                        <p><?php echo Yii::t('startup-guide', 'Customize the stages of sales that correspond to your business.'); ?></p>
                    </div>
                <?php }
                if($key == 'step4_panel'){ ?>
                    <div class="item">
                        <h1><?php echo Yii::t('startup-guide', 'Notice'); ?></h1>
                        <p><?php echo Yii::t('startup-guide', 'To stay up to date with all the changes to tasks and objects within the system that you are subscribed to, you can set up notifications by e-mail.'); ?></p>
                    </div>
                <?php }
                if($key == 'step5_panel'){ ?>
                    <div class="item">
                        <h1><?php echo Yii::t('startup-guide', 'Your photo'); ?></h1>
                        <p><?php echo Yii::t('startup-guide', 'Upload your photo to the system\'s password to ensure that your colleagues can easily navigate the list of tasks and transactions, where you will be responsible.'); ?></p>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="btn-bottom">
            <div class="dotstyle dotstyle-puff element" data-type="dot">
                <ul>
                    <?php
                        $_step = intval(substr($key, 4, 1));
                        for ($i=1; $i <= $_step; $i++){
                            echo '<li class="active"></li>';
                        }
                        for ($i=(5 - $_step); $i > 0; $i--){
                            echo '<li></li>';
                        }
                    ?>
                </ul>
            </div>
            <div class="buttons-section">
                <button type="button" class="btn btn-white"><?php echo Yii::t('base', 'Back'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo Yii::t('base', 'Continue'); ?></button>
            </div>
        </div>
    </div>
</div>
<?php } ?>





<?php
    if($key == 'step1_dialog'){
?>
<div class="modal-dialog step1" data-is-center>
    <section class="panel">
    <header class="panel-heading full-width">
        <span class="editable-field"><?php echo Yii::t('startup-guide', 'Adding Users'); ?></span>
    </header>
    <div class="panel-body element">
        <ul class="inputs-block">
            <li class="clearfix form-group inputs-group element">
                <div class="column">
                    <input class="form-control element" data-type="user" data-name="user1" placeholder="<?php echo \Yii::t('startup-guide', 'Email'); ?>" type="text">
                    <div class="errorMessage"></div>
                </div>
            </li>
            <li class="clearfix form-group inputs-group element">
                <div class="column">
                    <input class="form-control element" data-type="user" data-name="user2" placeholder="<?php echo \Yii::t('startup-guide', 'Email'); ?>" type="text">
                    <div class="errorMessage"></div>
                </div>
            </li>
            <li class="clearfix form-group inputs-group element">
                <div class="column">
                    <input class="form-control element" data-type="user" data-name="user3" placeholder="<?php echo \Yii::t('startup-guide', 'Email'); ?>" type="text">
                    <div class="errorMessage"></div>
                </div>
            </li>
            <li class="clearfix form-group inputs-group element">
                <div class="column">
                    <input class="form-control element" data-type="user" data-name="user4" placeholder="<?php echo \Yii::t('startup-guide', 'Email'); ?>" type="text">
                    <div class="errorMessage"></div>
                </div>
            </li>
        </ul>
        <div class="buttons-section">
            <button type="submit" class="btn btn-primary btn-add"><?php echo \Yii::t('startup-guide', 'Add'); ?></button>
            <button type="submit" class="btn btn-default btn-skip"><?php echo \Yii::t('startup-guide', 'Skip'); ?></button>
        </div>
    </div>
    </section>
</div>
<?php } ?>


<?php
if($key == 'step2_content'){
?>
  <div class="step2_arrow">
      <?php echo Yii::t('startup-guide', 'Configure the display of modules'); ?>
  </div>
<?php } ?>


<?php
if($key == 'step3_select_item'){
?>
    <li>
        <span class="drag-marker"><i></i></span>
        <input
                type="text"
                class="form-control element_params"
                data-type="select_option"
                data-id="<?php  echo (!empty($select_params['id']) ? $select_params['id'] : ''); ?>"
                data-remove="<?php echo  (!empty($select_params['remove']) ? $select_params['remove'] : '1'); ?>"
                data-finished_object="<?php echo  (!empty($select_params['finished_object']) ? $select_params['finished_object'] : '0'); ?>"
                data-sort="<?php echo  (!empty($select_params['sort']) ? (integer)$select_params['sort'] : '0'); ?>"
                data-slug="<?php echo  (!empty($select_params['slug']) ? $select_params['slug'] : ''); ?>"
                data-color="<?php  echo  (!empty($select_params['color']) ? $select_params['color'] : 'gray'); ?>"
                value="<?php  echo  (!empty($select_params['value']) ? $select_params['value'] : ''); ?>"
                placeholder="<?php echo Yii::t('base', 'Value'); ?>"
        />
        <?php if(!empty($select_params['remove'])){ ?>
            <a href="javascript:void(0)" class="todo-remove" data-element="select" ><i class="fa fa-times"></i></a>
        <?php } ?>
        <select class="selectpicker select-color">
            <option data-content="<span class='label label-gray' data-color='gray'></span>"></option>
            <option data-content="<span class='label label-orange' data-color='orange'></span>"></option>
            <option data-content="<span class='label label-yellow' data-color='yellow'></span>"></option>
            <option data-content="<span class='label label-green' data-color='green'></span>"></option>
            <option data-content="<span class='label label-sea' data-color='sea'></span>"></option>
            <option data-content="<span class='label label-blue' data-color='blue'></span>"></option>
            <option data-content="<span class='label label-violet' data-color='violet'></span>"></option>
        </select>
    </li>
<?php } ?>


<?php
    if($key == 'step3_dialog'){
?>
<div class="modal-dialog step3" data-is-center>
    <section class="panel">
        <header class="panel-heading full-width">
            <span class="editable-field"><?php echo Yii::t('startup-guide', 'Stages of sales'); ?></span>
        </header>
        <div class="panel-body element">
            <ul class="sub-menu element ui-sortable color-status-labels element_field_type_params_select">
                <?php echo $content ?>
                <div class="btn-element">
                    <a href="javascript:void(0)"
                       class="sub-menu-link add-field"><?php echo \Yii::t('startup-guide', 'Add'); ?></a>
                </div>
            </ul>
            <div class="buttons-section">
                <button type="submit" class="btn btn-primary element" data-type="save"><?php echo \Yii::t('startup-guide', 'Save'); ?></button>
            </div>
        </div>
    </section>
</div>
<?php } ?>

<?php
if($key == 'step4_content'){
    ?>
    <div class="step4_arrow_email">
        <?php echo Yii::t('startup-guide', 'Enter the email you want to receive notifications to'); ?>
    </div>

    <div class="step4_arrow_period">
        <?php echo Yii::t('startup-guide', 'Specify the frequency of sending notifications'); ?>
    </div>

    <div class="step4_arrow_save_change">
        <?php echo Yii::t('startup-guide', 'Save changes'); ?>
    </div>
<?php } ?>

<?php
if($key == 'step5_content'){
    ?>
    <div class="step5_arrow">
        <?php echo Yii::t('startup-guide', 'Upload photo'); ?>
    </div>
<?php } ?>

