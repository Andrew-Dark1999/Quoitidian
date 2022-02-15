<?php
$this->_template_letter_data['subject'] = Yii::t('email', '{company_name} - password restore', array('{company_name}' => '{company_name}'));

$this->_template_letter_data['header'] = '
    <div style="width:100%; height:25px;"></div>
    <span style="color:#ffffff;font-size:30px;font-family:arial;">'.Yii::t('email','Password recovery').'</span>
    <br /><br />
    <span style="color:#ffffff;font-size:15px;font-family:arial;">'.Yii::t('email', '{user_name}, we created link for recovery your password.', array('{user_name}' => '{user_name}')).'</span>';

$this->_template_letter_data['body'] = '
    '.Yii::t('email','For recovery your password, you must').'
    <a href="{site_url}"><span style="color: #009edb;">'.Yii::t('email','link this').'</span></a>
    <br/>
    <a href="mailto:{support_email}" target="_blank" style="color: #009edb !important; text-decoration: none;font-size:15px;font-family:arial;"><span style="color: #009edb;">{support_email}</span></a>
    <br />
    <span style="color:#646464;font-size:15px;font-family:arial;">'.Yii::t('email', 'We are always glad to help you.').' </span>
    <br />
    <br />
    <br />
    <span style="color:#646464;font-size:15px;font-family:arial;">'.Yii::t('email', 'Wish you success, the team Quotidian.').'</span>
';

