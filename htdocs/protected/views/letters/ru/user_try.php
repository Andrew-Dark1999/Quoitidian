<?php
$this->_template_letter_data['subject'] = "{company_name} - регистрация";

$this->_template_letter_data['header'] = '
    <div style="width:100%;height:20px;"></div>
    <span style="color:#ffffff;font-size:26px;font-family:arial; line-height: 40px">{user_name},
    <br>
    мы рады, что вы решили попробовать Quotidian.</span>
    <br>
';

$this->_template_letter_data['body'] = '
    Для входа в систему вам понадобятся:<br><br>
    ЛОГИН: {login}<br>ПАРОЛЬ: {password}<br><br><br>
    <span style="color:#646464;font-size:15px;font-family:arial;">
    <br>
    <br>
    Если у вас появятся вопросы, пишите нам на <a href="mailto:{sales_email}" style="color: #009edb !important; text-decoration: none;font-size:15px;font-family:arial;">{sales_email}</a>.
    <br />
    <span style="color:#646464;font-size:15px;font-family:arial;">Мы всегда будем рады вам помочь.</span>
    <br />
    <br />
    <br />
    <span style="color:#646464;font-size:15px;font-family:arial;">Желаем вам успехов, команда Quotidian.</span>
';


?>
