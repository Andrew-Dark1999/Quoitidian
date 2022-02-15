<?php
$this->_template_letter_data['template'] = '
<table style="background:#d5d6d9;" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tbody><tr>
        <td align="center" style="padding: 0 40px 0 40px;">
            <p style="margin-top:40px;"></p>
            <table style="border-collapse: collapse;" border="0" cellpadding="0" cellspacing="0" width="700">
                <tbody>
                <tr>
                    <td align="center">
                        <div style="width:100%; background:#009edb; border-top-left-radius:5px; border-top-right-radius: 5px;">
                            <div style="width:100%; height:55px;"></div>
                            <img src="{site_url}'.\Mailer::$image_logo_mail_top.'" height="47" width="317" alt="" />
                            <div style="width:100%; height:25px;"></div>
                            {header}
                            <div style="width:100%; height:33px;"></div>
                            <a href="{site_url}" target="_blank" style="color: #ffffff !important; background:#c552ae;text-decoration: none;font-size:18px;font-family:arial;display: inline-block;height: 49px;line-height: 49px;padding: 0 45px;border-radius: 3px;">Перейти в Quotidian</a>
                            <div style="width:100%; height:43px;"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td align="left">
                        <div style="background:#f9f9f9; padding:0 45px; color:#646464; font-size:15px; font-family: arial;">
                            <div style="width:100%; height:32px;"></div>

                            {body}


                            <div style="width:100%; height:32px;"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td align="center">
                        <div style="width:100%; background:#383838; border-bottom-left-radius:5px; border-bottom-right-radius: 5px;">
                            <div style="width:100%; height:58px;"></div>
                            <img src="{site_url}'.\Mailer::$image_logo_mail_bottom.'" height="34" width="227">
                            <div style="width:100%; height:50px;"></div>
                            <span style="color:#ffffff;font-size:14px;font-family:arial;">© Copyright, '.date("Y").', Quotidian</span><br>
                            <span style="color:#ffffff;font-size:14px;font-family:arial;">Отдел продаж: </span><a href="mailto:{sales_email}" target="_blank" style="color: #ffffff !important; text-decoration: none;font-size:14px;font-family:arial;"><span style="color: #ffffff!important; text-decoration: none;">{sales_email}</span></a><br>
                            <span style="color:#ffffff;font-size:14px;font-family:arial;">Техническая поддержка: </span><a href="mailto:{support_email}" target="_blank" style="color: #ffffff !important; text-decoration: none;font-size:14px;font-family:arial;"><span style="color: #ffffff!important; text-decoration: none;">{support_email}</span></a><br>
                            <div style="width:100%; height:45px;"></div>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td align="center" style="padding: 0 40px 0 40px;">
            <div style="width:100%; height:15px;"></div>
            <span color="#383838" style="font-family:Arial;font-size:14px;">Вы получили это письмо, так как являетесь клиентом Quotidian</span>
            <p style="margin-bottom:50px;"></p>
        </td>
    </tr>
    </tbody></table>';
