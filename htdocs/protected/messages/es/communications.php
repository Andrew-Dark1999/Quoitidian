<?php
return array(

    // Названия источников
    'Post'     => 'Correo',
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

    'vk'          => 'VK',
    'facebook'    => 'facebook',
    'SMS service' => 'SMS service',
    'Turbo SMS'   => 'Turbo SMS',

    'Post settings' => 'Ajustes de correo',
    'Email' => 'Email',
    'Password' => 'Contraseña',
    'Login' => 'Ingresar',
    'WEB interface' => 'Interfaz WEB',
    'Server' => 'Servidor',
    'Service' => 'Servicio',
    'Using SSL' => 'Usando SSL',
    'Port number' => 'Número de puerto',

    'Protocol SSL or TLS for IMAP' => 'Protocolo SSL o TLS para IMAP',
    'Protocol SSL or TLS for SMTP' => 'Protocolo SSL o TLS para SMTO',
    'Port SMTP' => 'Puerto SMTP',
    'Port IMAP' => 'Puerto IMAP',
    'Host SMTP' => 'Host SMTP',
    'Host IMAP' => 'Host IMAP',

    // ERRORS
    'Email address \'{address}\' is not correctly' => 'La dirección de email \'{address}\'no es correcta',
    'Email addresses \'{address}\' is not correctly' => 'Las direcciones de email \'{address}\' no son correctas',
    'Undefined destination email address' => 'Dirección de email de destino no definida',
    'Login and/or password is not correctly' => 'Usuario y/o contraseña incorrectos',
    'Can\'t connecting with service "{service}"' => 'No se puede conectar con el servicio "{service}"',
    'Can\'t connecting with host "{host}"' => 'No se puede conectar con el host "{host}"',
    'Error SMTP connection' => 'Error en la conexión SMTP',
    'Error greeting EHLO' => 'Error al saludar a EHLO',
    'Server did not allow to start authorization' => 'El servidor no permitió iniciar la autorización',
    'IMAP host not exist' => 'El host IMAP no existe',
    'Need select channel' => 'Se debe seleccionar un canal',
    'Error reading message text' => 'Error al leer el mensaje de texto',
    'No message text entered' => 'Ningún mensaje de texto ingresado',

    // Init services
    'IMAP server params are not exists'     => 'Los parámetros del servidor IMAP no existen',

    // Send letters
    'The source "{s}" disable or not exist'                             => 'La Fuente  "{s}"  está deshabilitada o no existe.',
    'The service "{s1}" of source "{s2}" is disable or not exist'       => 'El servicio "{s1}"  de la Fuente "{s2}" está deshabilitado o no existe',
    'Params is not exists'                                              => 'No existe el parámetro',
    'User communication params are not found'                           => 'No se encuentran los parámetros de comunicación del usuario',
    'Letter params is not validate. It was not saved and sent.'         => 'Los parámetros del correo no son válidos. No fueron guardados y enviados.',
    'The letter was not sent, it will be sending later'                 => 'El correo no fue enviado, se enviará más tarde.',
    'The letter is not found'                                           => 'No se encuentra el correo',
    'Data for sending is empty'                                         => 'No hay datos para enviar
//',

    // Upload letters
    'Error opening IMAP connection'                                     => 'Error al abrir la conexión IMAP',
    'Error opening IMAP connection for "Sent" box'                      => 'Error al abrir la conexión IMAP con el cuadro “elementos enviados”',
    'Error opening IMAP connection, the Host must have value'           => 'Error al abrir la conexión IMAP, el Host debe tener un valor.',
    'Undefined params "upload_uid"'                                     => 'Parámetros "upload_uid" indefinidos',
    'IMAP alert: "{s}"'                                                 => 'Alerta IMAP: "{s}"',
    'IMAP error: "{s}"'                                                 => 'Error IMAP: "{s}"',

    // Validate Errors
    'Field must have email address and can`t be empty'              => 'El campo debe contener el correo electrónico, no puede estar vacío.',
    'Password field can`t be empty'                                 => 'El campo de la contraseña no puede estar vacío',
    'Field must have value and can`t be empty'                      => 'El campo debe tener un valor, no puede estar vacío',
    'Field must have value 993 and can`t be empty'                  => 'El campo debe contener el valor 993, no puede estar vacío',
    'Field must have value 465, 587, 25 or 2525 and can`t be empty' => 'El campo debe contener el valor xxx, no puede estar vacío',
    'Field must have value "SSL" or "TLS" and can`t be empty'       => 'El campo debe contener el valor "SSL" o "TLS", no puede estar vacío',
    '--- without title ---'                                         => '--- sin titulo  ---',

    // MailerModels
    'Params must have value' => 'El parámetro debe tener un valor',
    // Save attachments
    'File "{file}" not exist' => 'El archive "{file}" no existe',
    'Error saving in table' => 'Error al guardar en la tabla',
    'Error saving in table. Letter not saved and not sent' => 'Error al guardar en la table. El correo no ha sido guardado ni enviado.',
    'Error on creating directory "{dir}"' => 'Error al crear el directorio "{dir}"',
    'Undefined user' => 'Usuario indefinido',

    'Subject' => 'Tema',
    'Recipient' => 'Receptor',
    'Message' => 'Mensaje',
    'signature' => 'Firma',

    'Chat' => 'Chat',
    'Chats' => 'Chats',
    'All chats' => 'Todos los chats',
    'Create of chat' => 'Crear chat',
    'Add chat' => 'Agregar chat',
    'Participants by email' => 'Participantes por email',
    'New chat' => 'Nuevo chat',
    '1#participant|in_array(n,[2,3,4])#participants|in_array(n,[0,5,6,7,8,9])#participants' => '1#participante|in_array(n,[2,3,4])#participante|in_array(n,[0,5,6,7,8,9])#participantes',

);
