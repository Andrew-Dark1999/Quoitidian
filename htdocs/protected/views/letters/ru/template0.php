<?php
$this->_template_letter_data['template'] = '
<table style="background:#d5d6d9;" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tbody><tr>
        <td align="center" style="padding: 0 40px 0 40px;">
            <p style="margin-top:40px;"></p>
            <table style="border-collapse: collapse;" border="0" cellpadding="0" cellspacing="0" width="700">
                <tbody>
                <!--header line statrs-->
                <tr>
                    <td align="center">
                        <div style="width:100%; background:#383B4E; border-top-left-radius:5px; border-top-right-radius: 5px; padding: 44px 0 42px 0;">
                            <img src="{site_url}'.\Mailer::$image_logo_mail_top.'" height="47" width="317" alt="" />
                        </div>
                    </td>
                </tr>
                <!--header line end-->
                <!--content statrs-->
                <tr>
                    <td align="left">
                        <div style="background:white; padding:20px 42px 0 42px; font-family: arial; ">
                            <table style="font-size:14px; color:#646464;">
                                {body}
                            </table>
                        </div>
                    </td>
                </tr>
                <!--content end -->
                <tr>
                    <td align="center">
                        <div style="width:100%; background:#383B4E; border-bottom-left-radius:5px; border-bottom-right-radius: 5px;">
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
