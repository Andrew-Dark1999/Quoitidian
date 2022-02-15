<?php
return array(

    // Названия источников
    'Post'     => 'Почта',
    'Skype'    => 'Skype',
    'WhatsApp' => 'WhatsApp',
    'Telegram' => 'Telegram',
    'Facebook' => 'Facebook',


    // Названия сервисов
    'Gmail'       => 'Gmail',
    'Mail.ru'     => 'Mail.ru',
    'Yandex'      => 'Yandex',
    'iCloud'      => 'iCloud',
    'Outlook.com' => 'Outlook.com',
    'IMAP'        => 'IMAP',

    'vk'          => 'В вонтакте',
    'facebook'    => 'Фейсбук',
    'SMS service' => 'SMS сервис',
    'Turbo SMS'   => 'Турбо SMS',

    'Post settings' => 'Настройки почты',
    'Email' => 'Email',
    'Password' => 'Пароль',
    'Login' => 'Логин',
    'WEB interface' => 'WEB интерфейс',
    'Server' => 'Сервер',
    'Service' => 'Сервис',
    'Using SSL' => 'Использование SSL',
    'Port number' => 'Номер порта',

    'Protocol SSL or TLS for IMAP' => 'Протокол SSL или TLS для IMAP',
    'Protocol SSL or TLS for SMTP' => 'Протокол SSL или TLS для SMTP',
    'Port SMTP' => 'Порт SMTP',
    'Port IMAP' => 'Порт IMAP',
    'Host SMTP' => 'Хост SMTP',
    'Host IMAP' => 'Хост IMAP',

    // ERRORS
    'Email address \'{address}\' is not correctly' => 'Электронный адрес \'{address}\' введен некорректно',
    'Email addresses \'{address}\' is not correctly' => 'Электронные адреса \'{address}\' введены некорректно',
    'Undefined destination email address' => 'Не определен электронный адрес получателя',
    'Login and/or password is not correctly' => 'Логин и/или пароль введены неверно',
    'Can\'t connecting with service "{service}"' => 'Невозможно подключиться к сервису "{service}"',
    'Can\'t connecting with host "{host}"' => 'Не удается подключиться к хосту "{host}"',
    'Error SMTP connection' => 'Ошибка подключения SMTP',
    'Error greeting EHLO' => 'Ошибка приветствия EHLO',
    'Server did not allow to start authorization' => 'Сервер не разрешил начать авторизацию',
    'IMAP host not exist' => 'Хост IMAP не существует',
    'Need select channel' => 'Необходимо выбрать чат',
    'Error reading message text' => 'Ошибка чтения текста сообщения',
    'No message text entered' => 'Не введен текст сообщения',

    // Init services
    'IMAP server params are not exists'     => 'Параметры сервера IMAP отсутствуют',

    // Send letters
    'The source "{s}" disable or not exist'                             => 'Ресурс "{s}" отключен или отсутствует',
    'The service "{s1}" of source "{s2}" is disable or not exist'       => 'Сервис "{s1}" ресурса "{s2}" отключен или отсутствует',
    'Params is not exists'                                              => 'Параметры отсутствуют',
    'User communication params are not found'                           => 'Пользовательские параметры подключения отсутствуют',
    'Letter params is not validate. It was not saved and sent.'         => 'Параметры письма не корректны. Письмо не сохранено и не отправлено.',
    'The letter was not sent, it will be sending later'                 => 'Письмо не было отправлено, оно будет отправлено позже',
    'The letter is not found'                                           => 'Письмо не найдено',
    'Data for sending is empty'                                         => 'Данные для пересылки отсутствуют',

    // Upload letters
    'Error opening IMAP connection'                                     => 'Ошибка открытия IMAP соединения',
    'Error opening IMAP connection for "Sent" box'                      => 'Ошибка открытия IMAP соединения с ящиком "Отправленные"',
    'Error opening IMAP connection, the Host must have value'           => 'Ошибка открытия IMAP соединения, параметр Host не должен быть пустым',
    'Undefined params "upload_uid"'                                     => 'Отсутствуют параметры "upload_uid"',
    'IMAP alert: "{s}"'                                                 => 'Сообщение IMAP: "{s}"',
    'IMAP error: "{s}"'                                                 => 'Ошибка IMAP: "{s}"',

    // Validate Errors
    'Field must have email address and can`t be empty'              => 'Поле должно содержать email и не может быть пустым',
    'Password field can`t be empty'                                 => 'Поле пароля не может быть пустым',
    'Field must have value and can`t be empty'                      => 'Поле должно иметь значение и не может быть пустым',
    'Field must have value 993 and can`t be empty'                  => 'Поле должно иметь значение 993 и не может быть пустым',
    'Field must have value 465, 587, 25 or 2525 and can`t be empty' => 'Поле должно иметь значение 465, 587, 25 или 2525 и не может быть пустым',
    'Field must have value "SSL" or "TLS" and can`t be empty'       => 'Поле должно иметь значение "SSL" или "TLS" и не может быть пустым',
    '--- without title ---'                                         => '--- без названия ---',

    // MailerModels
    'Params must have value' => 'Параметры должны иметь значение',
    // Save attachments
    'File "{file}" not exist' => 'Файл "{file}" не существует',
    'Error saving in table' => 'Ошибка записи в таблицу',
    'Error saving in table. Letter not saved and not sent' => 'Ошибка записи в таблицу. Письмо не сохранено и не отправлено',
    'Error on creating directory "{dir}"' => 'Ошибка создания дирректории "{dir}"',
    'Undefined user' => 'Пользователь не определен',

    'Subject' => 'Тема',
    'Recipient' => 'Получатель',
    'Message' => 'Сообщение',
    'signature' => 'Подпись',

    'Chat' => 'Чат',
    'Chats' => 'Чаты',
    'All chats' => 'Все чаты',
    'Create of chat' => 'Создать чат',
    'Add chat' => 'Добавить чат',
    'Participants by email' => 'Участники по email',
    'New chat' => 'Новый чат',
    '1#participant|in_array(n,[2,3,4])#participants|in_array(n,[0,5,6,7,8,9])#participants' => '1#участник|in_array(n,[2,3,4])#участника|in_array(n,[0,5,6,7,8,9])#участников',

);
