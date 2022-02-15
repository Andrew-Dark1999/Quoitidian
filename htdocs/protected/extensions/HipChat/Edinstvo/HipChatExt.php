<?php

/**
 * HipChatExt
 * Класс для дополнительной обработки API HipChat (Единство)
 * only for Edinstvo
 * @copyright 2016
 */
class HipChatExt {


    const DEFAULT_OPERATOR_ID = 1;
    
    const DEFAULT_ROOM_ID = 1333462;
    const DEFAULT_SENDER = 'Edinstvo62.ru';

    /**
     * Ключ авторизации на сервисе
     */
    private $auth_token = null;

    
    /**
     * Текст сообщения для отправки в HipChat
     */
    private $message = '';
    
    
    /**
     * Внутренние пользователи CRM
     */
    private $users = array(
        "130" => 56,    //monoskin
        "131" => 76,    //kaftaeva
        "141" => 93,    //sedneva
        "142" => 81,    //grigorieva
        "143" => 0,     //moskvitina
        "144" => 63,    //muratova
        "145" => 80,    //kuznetsova
        "146" => 94,    //stroykova
        "1" =>   1,     //admin
    );
    
    
    /**
     * Соответствие комнат операторам
     */
    private $rooms = array(
        "141" => 1572217,    
        "142" => 1572218,    
        "143" => 2624416,    
        "144" => 1572219,    
        "145" => 1895311,    
        "146" => 1895245,     
    );
            
    private $user_id = 0;
    private $operator_id = 0;
    private $room_id = 1572231;
    private $disposable_links = array();
    
    
    private $statuses_appeals = array(
        'not_processed' => 'Не обработано',
        'processed' => 'Обработано',
    );
    
    public function __construct(){
        Yii::import('application.extensions.HipChat.HipChat');
    }
    
    
    public static function getInstance(){
        return new self();
    }
  
  
    public function setAuthToken($auth_token){
        $this->auth_token = $auth_token;
        return $this;
    }
    
    
    /**
     *  Создаем новую карточку по ее коду во вспомогательной таблице
     */
    public function addCard($code){
        
        $link = \DataModel::getInstance()
                    ->setFrom('{{disposable_links}}')
                    ->setWhere("code = '$code'")
                    ->findRow();
        
        if($link) {
            //права доступа
            if(!$this->checkAccess($link['copy_id']))
                return false;
            
            return $this->prepareCard($link);
            
        }else
            return false;
        
    }
    
    
    /**
     *  Проверка прав
     */
    private function checkAccess($copy_id){
        
        if(!Access::checkAccess(PermissionModel::PERMISSION_DATA_RECORD_EDIT, $copy_id))
            return false;

        $res = false;
        foreach($this->users as $k => $v) {
            if($v == WebUser::getUserId()){
                $res = true;
                break;
            } 
        }
        // if(!$res)
            // return false;
        
        return true;
        
    }
    
    
    /**
     *  Подготавливаем карточку (либо создаем новую либо сразу возвращаем ссылку на уже ранее созданную)
     */
    private function prepareCard($card){
        
        $url = false;
        
        switch($card['type']) {
            
            case 'create':
            
                $data = unserialize($card['card_data']);
                if(!empty($data['EditViewModel'])) {
                    $extension_copy = \ExtensionCopyModel::model()->modulesActive()->findByPK($card['copy_id']);
                    $schema_parser = $extension_copy->getSchemaParse();
                    
                    $alias = 'evm_' . $extension_copy->copy_id;
                    $dinamic_params = array(
                        'tableName' => $extension_copy->getTableName(null, false),
                        'params' => Fields::getInstance()->getActiveRecordsParams($schema_parser),
                    );
                    
                    $extension_data = \EditViewModel::modelR($alias, $dinamic_params, true);
                    $extension_data->setElementSchema($schema_parser);
                    $extension_data->extension_copy = $extension_copy;
                    
                    $extension_data->setMyAttributes($data['EditViewModel']);

                    if($extension_data->save()) {
                        
                        //новая карточка создана
                        //изменяем тип
                        \DataModel::getInstance()->Update('{{disposable_links}}', array('type' => 'edit', 'related_card_id' => $extension_data->qwe_primary_key), 'id = ' . $card['id']);

                        //СМ привязка
                        if(!empty($data['EV_SM'])) {
                            foreach($data['EV_SM'] as $sm) {
                                
                                $sm_relate = \DataModel::getInstance()
                                    ->setFrom('{{module_tables}}')
                                    ->setWhere("copy_id = '{$extension_copy->copy_id}' AND relate_copy_id = '{$sm['related_copy_id']}' AND type='relate_module_many'")
                                    ->findRow();
                                if($sm_relate) {
                                    //добавляем привязку к только что созданной карточке
                                    \DataModel::getInstance()->Insert('{{' . $sm_relate['table_name'] . '}}', array(
                                            $sm_relate['parent_field_name'] => $extension_data->qwe_primary_key, 
                                            $sm_relate['relate_field_name'] => $sm['related_card_id'], 
                                        )
                                    );
                                }
                            }
                        }
                        
                        $url = "/module/listView/show/{$card['copy_id']}?modal_ev={$extension_data->qwe_primary_key}";
                    }
                }
                
 
            break;
            case 'edit':
                $url = "/module/listView/show/{$card['copy_id']}?modal_ev={$card['related_card_id']}";
            break;
            
            
        }
            
        return $url;
        
    }
    
    
    public function setUserId($operator_id){
        
        if(!$operator_id)
            $operator_id = self::DEFAULT_OPERATOR_ID;
        
        if(!empty($this->users[$operator_id])) {
            $this->user_id = $this->users[$operator_id];
            $this->operator_id = $operator_id;
        }
        
        return $this;
    }
  
  
     public function setRoomId(){
        
        if(!empty($this->rooms[$this->operator_id])) 
            $this->room_id = $this->rooms[$this->operator_id];
        
        return $this;
    }
  
    /**
     *  Подготовка сообщения для отправки
     */
    public function prepareMessage($phone){
        
        if(!$this->user_id)
            return $this;
        
        $extension_copy_clients = \ExtensionCopyModel::model()->modulesActive()->findByAttributes(array('title'=>'Клиенты'));
        $extension_copy_appeals = \ExtensionCopyModel::model()->modulesActive()->findByAttributes(array('title'=>'Обращения'));

        if($extension_copy_clients !== null && $extension_copy_appeals !== null) {
            
            //ищем телефон в карточке клиента
            $clients = \DataModel::getInstance()
                        ->setFrom($extension_copy_clients->getTableName())
                        ->setWhere("ehc_field1 = '$phone' OR ehc_field2 = '$phone' OR ehc_field3 = '$phone'")
                        ->findAll();
                        
          
            if($clients) {
                
                //карточка клиента(ов) найдена
                foreach($clients as $client) {
                    
                    $card_data = array(
                        'EditViewModel' => array(
                            'module_title'  => $client['module_title'],
                            'ehc_field2'    => $phone,
                            'traetment_status'      => $this->getAppealStatusId($this->statuses_appeals['not_processed']),
                        ),
                        'EV_SM' => array(array('related_copy_id' => $extension_copy_clients->copy_id, 'related_card_id' => $client[$extension_copy_clients->prefix_name . '_id'])),
                    );
                    
                    $this->generateLinks(
                        $extension_copy_appeals->copy_id, 
                        $card_data,
                        $card_data['EditViewModel']['module_title']
                    );
                }
                

            
            }else {
                
                //клиент не найден, ищем в обращениях (нас интересует последнее)
                $appeal = \DataModel::getInstance()
                        ->setFrom($extension_copy_appeals->getTableName())
                        ->setWhere("ehc_field2 = '$phone'")
                        ->setOrder($extension_copy_appeals->prefix_name . '_id DESC')
                        ->setLimit(1)
                        ->findRow();
                        
                if($appeal) {
                    
                    //обращение найдено
                    $this->disposable_links[] = array(
                        'anchor' => $appeal['module_title'],
                        'href' => "/module/listView/show/{$extension_copy_appeals->copy_id}?modal_ev={$appeal[$extension_copy_appeals->prefix_name . '_id']}", //ссылка на уже существующую запись
                    );
                    
                }else {
                    
                    //обращения и клиенты также не найдены

                }
            }
            
            //создаем запись для нового обращения без указания клиента
            $card_data = array(
                'EditViewModel' => array(
                    'module_title'  => $phone,
                    'ehc_field2'    => $phone,
                    'traetment_status'      => $this->getAppealStatusId($this->statuses_appeals['not_processed']),
                ),
            );
            
            $this->generateLinks(
                $extension_copy_appeals->copy_id, 
                $card_data,
                'Неизвестный клиент'
            );
        
            $this->message = $this->generateMessage($phone);   
            
        }
        
        
        return $this;
    }
    
    
    /**
     *  Генерация сообщения
     */
    private function generateMessage($phone){
    
        $time = date("H:i",time());
        $message = "Звонок на {$this->operator_id} ({$time}) [{$phone}]";

        if(!empty($this->disposable_links)) {
            $links = array();
            foreach($this->disposable_links as $link) 
                $links[] = "<a href=\"{$link['href']}\">{$link['anchor']}</a>";
            $message .= ": " . implode(', ', $links);
        }
        
        return $message;
    }
    
    
    /**
     *  Ссылки в сообщении
     */
    private function generateLinks($copy_id, $card_data, $anchor){
                
        $code = md5(time() . mt_rand(100, 9999) . serialize($card_data));
        \DataModel::getInstance()->Insert('{{disposable_links}}', array(
                'copy_id' => $copy_id, 
                'code' => $code, 
                'card_data' => serialize($card_data), 
                'user_create' => $this->user_id, 
                'timestamp_create' => time(), 
                'type' => 'create',
            )
        );
        
        $this->disposable_links []= array(
            'anchor' => $anchor,
            'href' => Yii::app()->getBaseUrl(true) . "/hipChat/addCard?code={$code}",
        );
        
    }
    
    
    /**
     *  Получаем статус обращения по его названию
     */
    private static function getAppealStatusId($title){
        
        $id = 0;
        $data = \DataModel::getInstance()->setFrom('{{ms_base_obrashhenija_traetment_status}}')->setWhere("traetment_status_title = '$title'")->findRow();
        
        if(!empty($data['traetment_status_id']))
            $id = $data['traetment_status_id'];
                
        return $id;        
        
    }
  
  
    /**
     *  Отправляем сообщение в HipChat
     */
    public function sendMessage(){
        
        if(!$this->user_id)
            return $this;
        
        //echo($this->message);
         
        try {
            
             $hip_chat = new \HipChat($this->auth_token);
             //var_dump($hip_chat->get_rooms());
             //$hip_chat->message_room($this->room_id, 'Edinstvo Calls', $this->message, true, \HipChat::COLOR_RED);
             $hip_chat->message_room(3532965, 'Edinstvo Calls', $this->message, true, \HipChat::COLOR_RED);
        
        } catch (Exception $e) {
            //не получилось с API
            //echo 'error';
        }
        
    }
        
  
}
