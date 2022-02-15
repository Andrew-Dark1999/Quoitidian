<?php
$this->_template_letter_data['subject'] = "{company_name} - востановление пароля";

$this->_template_letter_data['header'] = '
    <div style="width:100%; height:25px;"></div>
    <span style="color:#ffffff;font-size:30px;font-family:arial;">Восстановление пароля</span>
    <br /><br />
    <span style="color:#ffffff;font-size:15px;font-family:arial;">{user_name}, мы создали для вас новый пароль.</span>
';

$this->_template_letter_data['body'] = '
    Для входа в систему вам понадобятся:<br />
    ЛОГИН: <a href="#" target="_blank" style="color: #646464 !important; text-decoration: none;font-size:15px;font-family:arial;">
            <span style="color: #646464;">{login}</span>
           </a><br />ПАРОЛЬ: {password}<br /><br /><br />
    Если у вас появятся вопросы, пишите нам на
    <a href="mailto:{support_email}" target="_blank" style="color: #009edb !important; text-decoration: none;font-size:15px;font-family:arial;"><span style="color: #009edb;">{support_email}</span></a>
    <br />
    <span style="color:#646464;font-size:15px;font-family:arial;">Мы всегда будем рады вам помочь.</span>
    <br />
    <br />
    <br />
    <span style="color:#646464;font-size:15px;font-family:arial;">Желаем вам успехов, команда Quotidian.</span>
';
