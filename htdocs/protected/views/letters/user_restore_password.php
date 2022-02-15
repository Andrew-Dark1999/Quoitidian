<?php
$this->_template_letter_data['subject'] = "{company_name} - password restore";

$this->_template_letter_data['header'] = '
    <div style="width:100%; height:25px;"></div>
    <span style="color:#ffffff;font-size:30px;font-family:arial;">Password recovery</span>
    <br /><br />
    <span style="color:#ffffff;font-size:15px;font-family:arial;">{user_name}, we created for you a new password.</span>
';

$this->_template_letter_data['body'] = '
    For login you need:<br />
    LOGIN: <a href="#" target="_blank" style="color: #646464 !important; text-decoration: none;font-size:15px;font-family:arial;">
            <span style="color: #646464;">{login}</span>
           </a><br />PASSWORD: {password}<br /><br /><br /> 
    If you have any questions, please contact us at
    <a href="mailto:{support_email}" target="_blank" style="color: #009edb !important; text-decoration: none;font-size:15px;font-family:arial;"><span style="color: #009edb;">{support_email}</span></a>
    <br />
    <span style="color:#646464;font-size:15px;font-family:arial;">We are always glad to help you.</span>
    <br />
    <br />
    <br />
    <span style="color:#646464;font-size:15px;font-family:arial;">Wish you success, the team Quotidian.</span>
';

