<div class="list_view_block paramrtrs_module">
    <section class="panel">
        <header class="panel-heading"><?php echo Yii::t('base', 'MAILING SERVISES'); ?></header>
        <div class="panel-body content_form">
            <div class="adv-table">
                <?php $form=$this->beginWidget('CActiveForm', array(
                        'id'=>'mailing_services_form',
                        'action'=>'/mailing_services',
                        'enableAjaxValidation'=>false,
                        'htmlOptions' => array(
                            'class' => 'settings_form',
                            'data-selector_content_box' => '#content_container',
                        )));
                ?>
                <div class="col-lg-4 settings_title text-right">
                    <?php echo 'Email'; ?>
                </div>
                <div class="settings_form_group b_visible m_email-box">
                    <?php echo $form->labelEx($model,'email_box',array('class'=>'col-lg-4 control-label text-right')); ?>
                    <div class="col-lg-6">
                        <?php echo $form->dropDownList(
                                            $model,
                                            'email_box',
                                            $model->getEmailMailboxList(),
                                            array('class'=>'form-control element select', 'data-field_type' => 'text'));
                        ?>
                    </div>
                    <?php echo $form->error($model,'email_box'); ?>
                </div>
                <?php if($model->email_box == \MailingServicesModel::EMAIL_BOX_EXTERNAL){ ?>
                <div class="settings_form_group">
                    <?php echo $form->labelEx($model,'email_host',array('class'=>'col-lg-4 control-label text-right')); ?>
                    <div class="col-lg-6">
                        <?php echo $form->textField($model,'email_host',array('class'=>'form-control element', 'data-field_type' => 'text')); ?>
                    </div>
                    <?php echo $form->error($model,'email_host'); ?>
                </div>
                <div class="settings_form_group">
                    <?php echo $form->labelEx($model,'email_port',array('class'=>'col-lg-4 control-label text-right')); ?>
                    <div class="col-lg-6">
                        <?php echo $form->textField($model,'email_port',array('class'=>'form-control element', 'data-field_type' => 'text')); ?>
                    </div>
                    <?php echo $form->error($model,'email_port'); ?>
                </div>
                <div class="settings_form_group">
                    <?php echo $form->labelEx($model,'email_username',array('class'=>'col-lg-4 control-label text-right')); ?>
                    <div class="col-lg-6">
                        <?php echo $form->textField($model,'email_username',array('class'=>'form-control element', 'data-field_type' => 'text')); ?>
                    </div>
                    <?php echo $form->error($model,'email_username'); ?>
                </div>
                <div class="settings_form_group">
                    <?php echo $form->labelEx($model,'email_password',array('class'=>'col-lg-4 control-label text-right')); ?>
                    <div class="col-lg-6">
                        <?php echo $form->passwordField($model,'email_password',array('class'=>'form-control element', 'data-field_type' => 'text', 'placeholder'=>'********')); ?>
                    </div>
                    <?php echo $form->error($model,'email_password'); ?>
                </div>
                <?php } ?>
                <?php $this->endWidget(); ?>

                <div class="settings_form">
                <?php if(Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)){ ?>
                    <div class="settings_form_group">
                        <label class="col-lg-4 control-label text-right"> </label>
                        <div class="col-lg-6">
                            <div class="btn-group">
                                <?php echo CHtml::submitButton(Yii::t('base', 'Save'), array('class'=>'btn btn-primary element', 'data-type' => 'save_form')); ?>
                            </div>
                            <div class="btn-group">
                                <?php echo CHtml::submitButton(Yii::t('base', 'Cancel'), array('class'=>'btn btn-default element', 'data-type' => 'cancel_form')); ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                </div>
            </div>
        </div>
    </section>
    <!--Forms END-->
</div>

<script type="text/javascript">

    $(window).load(function() {
        Global.initSelects();
    });

    $(document).on('change', '#MailingServicesModel_email_box', function(){
        var _data = {};

        $('#mailing_services_form').find(
            '.element[data-field_type="text"],'+
            '.element[data-field_type="check"],'+
            '.element[data-field_type="radio"],'+
            '.element[data-field_type="check_array"]').each(function(i, ul)
        {

            switch($(ul).data('field_type')){
                case 'text' :
                    _data[$(ul).attr('name')] = $(ul).val();
                    break;
                case 'radio' :
                case 'check' :
                    if($(ul).prop("checked") == false) return true;

                    _data[$(ul).attr('name')] = ($(ul).val());
                    break;
                case 'check_array' :
                    if($(ul).prop("checked") == false) return true;

                    if(element_data[$(ul).data('type')])
                        _data[$(ul).attr('name')].push($(ul).val())
                    else
                        _data[$(ul).attr('name')] = [$(ul).val()];

                    break;
            }

        })
        $.ajax({
            'url': Global.urls.url_mailing_services_refresh,
            'data': _data,
            'type' : "POST", 'dataType': 'json', async : false,
            success: function(data){
                if(data.status == true){
                    console.log(data.html);
                    $('.list_view_block').children().html($(data.html).children().html());
                    BackForwardHistory.getInstance().snapshot();
                    Global.initSelects();
                }
            },
            error: function(){
                Message.show([{'type':'error', 'message':Global.urls.url_ajax_error}], true);
            }
        });
    })

</script>

<style>
    .m_email-box{
        height: 34px;
    }
</style>
