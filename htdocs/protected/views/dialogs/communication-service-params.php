<?php $user_params = $communication_params_model->user_params;?>
<div class="modal-dialog" data-type="modal-dialog" style="width: 620px;" data-is-center>
    <section class="panel" >
        <div class="edit-view in sm_extension sm_extension_export no_middle">
            <header class="panel-heading editable-block hidden-edit">
            <span class="client-name">
               <span><?php echo Yii::t('communications', 'Post settings') ?></span>
            </span>
                <span class="tools options">
                <a href="javascript:void(0)" data-dismiss="modal" class="fa close-button"></a>
            </span>
            </header>
            <div class="panel-body element communication-services">

<!-- Выбор сервисов в кружочках -->
                <?php if(empty($user_params)){ ?>
                    <div class="element active" data-type="sub-window">
                        <div class="clearfix list-services">
                            <?php $i = 0; ?>
                            <?php foreach ($communication_params_model->user_form_params as $service){ ?>
                                <div class="element" data-id="<?php echo $i++; ?>" id="<?php echo $service['service_name'] ?>" data-service-name="<?php echo $service['service_name'] ?>">
                                    <div><img src="<?php echo $service['image'] ?>" alt=""></div>
                                    <span class="text"><?php echo $service['service_title'] ?></span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>

                <div class="element service-param<?php echo (!empty($user_params)) ? ' active' : '' ?>"<?php echo (!empty($user_params)) ? ' data-update="false"' : ' data-update="true"' ?> data-type="sub-window">
                    <div class="panel inputs-panel element" data-type="block">
                        <header class="panel-heading">
                            <span class="title"></span>
                        </header>
                    </div>
                    <div class="panel-body">

<!-- Выбор сервисов в dropbox -->
                        <?php if(!empty($user_params)){ ?>
                            <div class="element" data-type="list-services">
                                <ul>
                                    <li class="clearfix form-group inputs-group element" data-type="panel">
                                        <span class="inputs-label"><?php echo Yii::t('communications', 'Service') ?></span>
                                        <div class="columns-section col-1">
                                            <div class="column">
                                                <select class="select element" data-type="fields_view">
                                                    <?php
                                                    $i = 0;
                                                    foreach ($communication_params_model->user_form_params as $service) {
                                                        $s = '<option value="' . $i++ . '" ';
                                                        if($service['service_name'] == $user_params['service_name']){
                                                            $s .= ' selected';
                                                        }
                                                        $s .= '>' . $service['service_title'] . '</option>';
                                                        echo $s;
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        <?php } ?>

<!-- Параметры сервиса -->
                        <?php $i = 0; ?>
                        <?php foreach ($communication_params_model->user_form_params as $service){ ?>
                            <div class="element <?php echo (((!empty($user_params)) && ($service['service_name'] != $user_params['service_name'])) || (empty($user_params))) ? ' hide' : '' ?>" data-service-name="<?php echo $service['service_name'] ?>" data-id="<?php echo $i++ ?>">
                                <ul>
                                    <?php $j = 0; ?>
                                    <?php foreach ($service['user_params'] as $param){ ?>
                                        <li class="clearfix form-group inputs-group element" data-type="panel">
                                            <span class="inputs-label"><?php echo Yii::t('communications', $param['title']); ?></span>
                                            <div class="columns-section col-1 element" data-type="objects">
                                                <div class="column element" data-type="<?php echo $param['name']; ?>">
                                                    <?php if($param['element_type'] == 'input'){ ?>
                                                        <input
                                                                data-name="<?php echo $param['name'] ?>"
                                                                class="form-control element"
                                                                type="<?php echo $param['data_type']; ?>"
                                                                value="<?php echo (!empty($user_params[$param['name']]) ? $user_params[$param['name']] : '') ?>"
                                                                data-type="<?php echo $param['name']; ?>">
                                                    <?php } else { ?>
                                                    <?php if($param['element_type'] == 'select'){ ?>
                                                        <select
                                                                data-name="<?php echo $param['name'] ?>"
                                                                class="form-control element"
                                                                type="<?php echo $param['data_type']; ?>"
                                                                data-type="<?php echo $param['name']; ?>">
                                                        <?php foreach($param['list'] as $value => $title){ ?>
                                                            <option <?php echo (!empty($user_params[$param['name']]) && $value == $user_params[$param['name']] ? 'selected' : ''); ?> value="<?php echo $value; ?>"><?php echo $title; ?></option>
                                                        <?php } ?>
                                                        </select>
                                                    <?php } ?>
                                                    <?php } ?>
                                                    <div class="errorMessage"></div>
                                                    <?php $j++; ?>
                                                </div>
                                            </div>
                                        </li>
                                    <?php } ?>
                                    <li class="clearfix form-group inputs-group element" data-type="panel">
                                        <span class="inputs-label"><?php echo Yii::t('communications', 'signature'); ?></span>
                                        <div class="columns-section col-1 element" data-type="objects">
                                            <div class="column element" data-type="signature">

                                                        <textarea
                                                            data-name="signature"
                                                            class="form-control element"
                                                            data-type="signature"><?php echo (!empty($communication_params_model->params_model->signature) ? $communication_params_model->params_model->signature : '') ?></textarea>

                                                <div class="errorMessage"></div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="buttons-section">
                        <button type="submit" class="btn btn-primary element" data-type="save"><?php echo Yii::t('base', 'Save')?></button>
                        <button type="button" <?php echo (!empty($user_params)) ? 'data-dismiss="modal"' : ''; ?> class="btn btn-default <?php if(!(CommunicationsServiceParamsModel::issetUserParams())){ echo 'close-button-back';}else{ echo 'close-button'; } ?>"><?php echo Yii::t('base', 'Cancel')?></button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
