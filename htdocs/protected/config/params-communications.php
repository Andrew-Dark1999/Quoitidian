<?php

return array(
    'params' => array(
        'communications' => array(
            'sources' => array(
                'email' => array(
                    'class' => 'EmailSource',
                    'enable' => true,
                    'attachment_type_files' => array(
                        'jpeg',
                        'gif',
                    ),
                    'mailbox_international' => array(
                        'inbox' => array('INBOX', 'Inbox', 'Входящие', 'Вхідні', 'Уваходныя'),
                        'sent' => array('Sent', 'Sent Mail', 'Sent Items', 'Исходящие', 'Отправленные', 'Надіслані', 'Адасланыя', 'Дасланыя'),
                        'trash' => array('Trash', 'Deleted Items', 'Удалённые', 'Удаленные', 'Корзина', 'Кошик', 'Видалені', 'Выдаленыя', 'Кошык'),
                    ),
                    'services' => array(
                        'gmail' => array(
                            'class' => 'ServiceGmail',
                            'enable' => true,
                            'server_params' => array(
                                'imap_server_host'           => 'imap.gmail.com',
                                'imap_server_connect_secure' => 'ssl',
                                'imap_server_port'           => 993,
                                'smtp_server_host'           => 'smtp.gmail.com',
                                'smtp_server_connect_secure' => 'ssl',
                                'smtp_server_port'           => 465,
                            ),
                        ),
                        'mail_ru' => array(
                            'class' => 'ServiceMailRu',
                            'enable' => false,
                            'server_params' => array(
                                'imap_server_host'           => 'imap.mail.ru',
                                'imap_server_connect_secure' => 'ssl',
                                'imap_server_port'           => 993,
                                'smtp_server_host'           => 'smtp.mail.ru',
                                'smtp_server_connect_secure' => 'tls',
                                'smtp_server_port'           => 2525,
                            ),
                        ),
                        'yandex' => array(
                            'class' => 'ServiceYandex',
                            'enable' => false,
                            'server_params' => array(
                                'imap_server_host'           => 'imap.yandex.ru',
                                'imap_server_connect_secure' => 'ssl',
                                'imap_server_port'           => 993,
                                'smtp_server_host'           => 'smtp.mailgun.org',//'smtp.yandex.ru',
                                'smtp_server_connect_secure' => 'tls',
                                'smtp_server_port'           => 2525,
                                'alternate_username'         => 'postmaster@mg.quotidian.cl',
                                'alternate_password'         => 'cbea155082209daf090eef4b77deb94f',
                            ),
                        ),
                        'i_cloud' => array(
                            'class' => 'ServiceICloud',
                            'enable' => true,
                            'server_params' => array(
                                'imap_server_host'           => 'imap.mail.me.com',
                                'imap_server_connect_secure' => 'ssl',
                                'imap_server_port'           => 993,
                                'smtp_server_host'           => 'smtp.mail.me.com',
                                'smtp_server_connect_secure' => 'tls',
                                'smtp_server_port'           => 587,
                            ),
                        ),
                        'outlook_com' => array(
                            'class' => 'ServiceOutlookCom',
                            'enable' => true,
                            'server_params' => array(
                                'imap_server_host'           => 'imap-mail.outlook.com',
                                'imap_server_connect_secure' => 'tls',
                                'imap_server_port'           => 993,
                                'smtp_server_host'           => 'smtp-mail.outlook.com',
                                'smtp_server_connect_secure' => 'tls',
                                'smtp_server_port'           => 587,
                            ),
                        ),
                        'imap' => array(
                            'class' => 'ServiceIMAP',
                            'enable' => true,
                            'server_params' => array(
                                'imap_server_host'           => 'imap-mail',
                                'imap_server_connect_secure' => 'tls',
                                'imap_server_port'           => 993,
                                'smtp_server_host'           => 'smtp-mail',
                                'smtp_server_connect_secure' => 'tls',
                                'smtp_server_port'           => 587,
                            ),
                        ),
                    ),
                ),
                'skype' => array(
                    'class' => 'SkypeSource',
                    'enable' => false,
                    'services' => array(),
                ),
                'whats_app' => array(
                    'class' => 'WhatsAppSource',
                    'enable' => false,
                    'services' => array(),
                ),
                'telegram' => array(
                    'class' => 'TelegramSource',
                    'enable' => false,
                    'services' => array(),
                ),
                'facebook' => array(
                    'class' => 'FacebookSource',
                    'enable' => false,
                    'services' => array(),
                ),
            )
        )
    )
);
