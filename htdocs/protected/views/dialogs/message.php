<div class="modal-dialog" data-is-center style="width: 620px;">
    <section class="panel element" data-type="message">
    <div style="padding: 0 0 20px 0;">
    <table>
        <tbody>
        <?php
            $code_action = array();
            if(!empty($messages)){

            foreach($messages as $value){
                if(!empty($value['code_action'])) $code_action[] = $value['code_action'];
                $type = Yii::t('messages', $value['type']);
                $type = mb_substr(mb_strtoupper($type, 'utf-8'), 0, 1, 'utf-8') . mb_substr($type, 1, null, 'utf-8');

        ?>
            <tr>
                <td valign="top"><?php echo $type; ?>:</td>
                <td>&nbsp;</td>
                <td><?php
                        if($translate) echo Yii::t('messages', $value['message']);
                        else echo $value['message'];
                    ?>
                </td>
            </tr>
        <?php } ?>
        <?php } else { ?>
            <tr>
                <td valign="top"></td>
                <td>&nbsp;</td>
                <td></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    </div>
	<div class="buttons-section">
        <?php
            if(!empty($buttons)){
                foreach($buttons as $title => $attr){
                    if(!empty($attr['type'])){
                        switch($attr['type']){
                            case 'button':
                                echo CHtml::button(Yii::t("base", $title), $attr);
                                break;
                            case 'a':
                                echo CHtml::link(Yii::t("base", $title), '', $attr);
                        }
                    } else{
                        echo CHtml::button(Yii::t("base", $title), $attr);
                    }
                }
            }
        ?>
	</div>
    <?php if(!empty($code_action)){ ?>
        <input class="element" data-type="code_action" type="hidden" value="<?php echo implode(',', array_unique($code_action)) ?>" />
    <?php } ?>
    <?php if(!empty($params)){ ?>
        <span class="element" data-type="params" style="display: none;"><?php echo json_encode($params) ?></span>
    <?php } ?>
    </section>
</div>
