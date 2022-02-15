<?php
$this->_template_letter_data['subject'] = Yii::t('email', '{company_name} - registration');

$this->_template_letter_data['header'] = '
    <div style="width:100%;height:20px;"></div>
    <span style="color:#ffffff;font-size:26px;font-family:arial; line-height: 40px">{user_name},
    <br>
    '.Yii::t('email','we are glad that you decided to try Quotidian.').'</span>
    <br>
';

$this->_template_letter_data['body'] = '
    '.Yii::t('email','For login you need:').'<br><br>
    '.Yii::t('email','Login:').' {login}<br>'.Yii::t('email','Password:').' {password}<br><br><br>
    <span style="color:#646464;font-size:15px;font-family:arial;">
    <br>
    <br>
    <br>
    '.Yii::t('email','If you have questions, write us at').' <a href="mailto:{sales_email}" style="color: #009edb !important; text-decoration: none;font-size:15px;font-family:arial;">{sales_email}</a>.
    <br />
    <span style="color:#646464;font-size:15px;font-family:arial;">'.ucfirst(Yii::t('email','we will be happy to assist you')).'</span>
    <br />
    <br />
    <br />
    <span style="color:#646464;font-size:15px;font-family:arial;">'.Yii::t('email','We wish you success, Quotidian Team.').'</span>
';


?>
