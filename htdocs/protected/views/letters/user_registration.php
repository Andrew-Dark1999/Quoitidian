<?php
$this->_template_letter_data['subject'] = Yii::t('email', "{company_name} - registration",  array('{company_name}' => '{company_name}'));

$this->_template_letter_data['header'] = '
    <div style="width:100%;height:20px;"></div>    
    <span style="color:#ffffff;font-size:30px;font-family:arial; line-height: 20px; text-transform: uppercase;"> '.Yii::t('email','WELCOME!').'</span>
    <br>
    <br>
    <span style="color:#ffffff;font-size:16px;font-family:arial; line-height: 40px">
    '.Yii::t('email', "{user_name}, thank you for registering in QUOTIDIAN",  array('{user_name}' => '{user_name}')).'
    </span>
';

$this->_template_letter_data['body'] = '
    '.Yii::t('email', 'To enter you need:').'<br><br>
    '.Yii::t('email','Your account:').' <a href="{site_url}" target="_blank" style="color: #009edb !important; text-decoration: none;font-size:15px;font-family:arial;">{site_title}</a><br>'.Yii::t('email','User:').' {login}<br>'.Yii::t('email','Password:').' {password}<br><br><br>
    <span style="color:#646464;font-size:15px;font-family:arial;">
    <br>
    '.Yii::t('email','To facilitate your understanding of the system, we have created 3').' <a href="#" style="color: #009edb !important; text-decoration: none;font-size:15px;font-family:arial;">'.Yii::t('email','tutorials').'</a>.
    <br />
    <br>
     '.Yii::t('email','A more detailed description of the system can be found in').' <a href="#" style="color: #009edb !important; text-decoration: none;font-size:15px;font-family:arial;">'.strtolower(Yii::t('email','Support')).'</a>.
    <br />
     <br>
     '.Yii::t('email','If you have questions, write us at').' <a href="mailto:{service_email}" target="_blank" style="color: #646464; text-decoration: none;font-size:14px;font-family:arial;"><span style="text-decoration: none;">{service_email}</span></a>, '.Yii::t('email','we will be happy to assist you').'.
     <br />
    <br>
    <br>
    <span style="color:#646464;font-size:15px;font-family:arial;">'.Yii::t('email','Good luck!').'</span>
    <br />
    <br />
    <br />
    <br />    
    <span style="color:#646464;font-size:15px;font-family:arial;">'.Yii::t('email','Quotidian Team').'</span>
    <br />
';


?>
