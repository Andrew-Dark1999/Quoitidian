<?php

/**
$2a$13$tDjEFbW9YxcYJuPUjixHSuJWJq5eGbvz9WX7nAt6HH/K2S.9/ViO.

$2y$13$jk41FyHcggd2.uv7ldJc7umDle0/AHg1F6PJ2hajgHJtV10bjCleG
$2y$13$Bco1qyMINlhM70oJwJhZGuQg05ZgRFBiLTrYgNYQhooyPZKZPLkFG

  *
 *
 * HistoryMessagesModel
* 
* @author Alex R.
*/






class HistoryMessagesModel{
    
    // индексы сообщений (messages_index)
    const MT_COMMENT_CREATED           = 1;
    const MT_COMMENT_CHANGED           = 2;
    const MT_COMMENT_DELETED           = 3;
    const MT_CREATED                   = 4;
    const MT_STATUS_CHANGED            = 5;
    const MT_DELETED                   = 6;
    const MT_RESPONSIBLE_APPOINTED     = 7;
    const MT_DATE_ENDING_CHANGED       = 8;
    const MT_FILE_UPLOADED             = 9;
    const MT_FILE_DELETED              = 10;
    //const MT_CHANGED                   = 11;

    const MT_ENABLE_MODULE_ACCESS      = 12;
    const MT_DISABLE_MODULE_ACCESS     = 13;
    const MT_CHANGED_MODULE_ACCESS     = 14;

    // For Process
    const MT_OPERATION_REJECTED        = 15;
    const MT_OPERATION_CREATED_TASK    = 16;
    const MT_OPERATION_MUST_CREATED_RECORD  = 17;
    const MT_OPERATION_MUST_CHANGED_RECORD  = 18;
    //const MT_OPERATION_CREATED_NOTIFICATION = 19;
    const MT_PROCESS_RELATE_OBJECT_EMPTY    = 20;
    const MT_PROCESS_MUST_APPOINT_RESPONSIBLE = 21;

    const MT_DATE_ENDING_BECOME             = 22;
    const MT_DATE_ENDING_BECOME_TO          = 23;

    // события для ссылок
    const LINK_ACTION_CARD                      =   'card';
    const LINK_ACTION_CARD_DELETE               =   'card_delete';
    const LINK_ACTION_MODULE                    =   'module';
    const LINK_ACTION_PROCESS_RUN               =   'process_run';
    const LINK_ACTION_PROCESS_OPERATION_RUN     =   'process_operation_run';
    const LINK_ACTION_USER_PROFILE              =   'user_profile';

    // тим модуля при отображении сообщений
    const MODULE_TYPE_TASK = 'task';
    const MODULE_TYPE_BASE = 'base';
    
    // названия обьектов сообщений
    const OBJECT_ACTIVITY   = 'activity';
    const OBJECT_NOTICE     = 'notice';
    const OBJECT_DN         = 'nd';

    // названия типов сообщений: тема/сообщение
    const TYPE_SUBJECT      = 'subject';
    const TYPE_MESSAGE      = 'message';


    // шаблоны элементов уведомлений (ссылки, блоки)
    const ME_URL_CARD           = 1;
    const ME_URL_MODULE         = 2;
    const ME_URL_FILE           = 3;
    const ME_URL_USER_PROFILE   = 4;
    const ME_URL_CARD_DN        = 5;
    const ME_URL_MODULE_DN      = 6;
    const ME_URL_FILE_DN        = 7;
    const ME_URL_USER_PROFILE_DN= 8;
    const ME_CARD               = 9;
    const ME_FROM               = 10;
    const ME_COMMENT            = 11;
    const ME_USER               = 12;


    private $_object_name;
    private $_module_type;

    private $_history_model;
    private $_message_params;
    private $_vars;

    private $_link_actions = array();   // массив линков
    private $_block_action_key = null;  // ключ линка, что указывается для переходи из всего блока сообщения.


    private $_key_prefix = '';
    private static $key_index = 1;

    private $_message_data = array(
        'subject' => null,
        'message' => null,
        'ico' => null,
        'class_color' => null,
    );


    public function __construct(){
        \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->getModule(false);
    }


    public static function getInstance(){
        return new self();
    }


    private function getActionKey(){
        $prefix = 'key' . $this->_key_prefix . self::$key_index;
        self::$key_index++;

        return $prefix . '_' . $this->_history_model->history_id;
    }


    /**
     * addLinkAction
     */
    private function addLinkAction($action_key, $params){
        if($action_key == false){
            return;
        }
        $this->_link_actions[$action_key] = $params;
    }

    public function setHistoryModel($history_model){
        $this->_history_model = $history_model;
        return $this;
    }


    public function setObjectName($object_name){
        $this->_object_name = $object_name;
        return $this;
    }


    public function setMessageParams($message_params){
        $this->_message_params = $message_params;
        return $this;
    }


    /**
     * getMTListForProcess - возвращает список констант, что относятся к уведомлениям из Процессов
     * @return array
     */
    public static function getMTListForProcess(){
        return array(
            self::MT_OPERATION_REJECTED,
            self::MT_OPERATION_CREATED_TASK,
            self::MT_OPERATION_MUST_CREATED_RECORD,
            self::MT_OPERATION_MUST_CHANGED_RECORD,
            //self::MT_OPERATION_CREATED_NOTIFICATION,
            self::MT_PROCESS_RELATE_OBJECT_EMPTY,
            self::MT_PROCESS_MUST_APPOINT_RESPONSIBLE,
        );
    }





    public function prepare(){
        $this->prepareVars();

        $this->_message_data = array(
            'subject' => $this->getFormatedMessage(self::TYPE_SUBJECT),
            'message' => $this->getFormatedMessage(self::TYPE_MESSAGE),
            'ico' => $this->getTagsFormat('color'),
            'class_color' => $this->getTagsFormat('image'),
        );

        return $this;
    }


    /**
     * getResult
     */
    public function getResult(){
        return array(
            'block_action_key' => $this->_block_action_key,
            'link_actions' => $this->_link_actions,
            'message_data' =>$this->_message_data,
        );
    }



    /**
     * подготавливает определенные параметры сообщения
     */
    public function prepareVars(){
        $this->_module_type = self::MODULE_TYPE_BASE;
        if($this->_history_model->copy_id == ExtensionCopyModel::MODULE_TASKS){
            $this->_module_type = self::MODULE_TYPE_TASK;
        }

        $this->_vars = array(
            'is_parent_entity' => false,
            'parent_entity' => array(
                'this_process' => false,
                'copy_id' => null,
                'data_id' => null,
                'operations_unique_index' => null, // for Process
            ),
        );



        // подключем данные из связаного модуля по СДМ по полю Название
        if(!empty($this->_message_params['{parent_module_data}']['is_parent_entity'])){
            $this->_vars = $this->_message_params['{parent_module_data}'];
            $this->_vars['parent_entity']['action'] = self::LINK_ACTION_CARD;
            $this->_vars['parent_entity']['this_process'] = false;
            return;
        }


        // подключем данные из связаного процесса
        if(!empty($this->_history_model->processOperations)){
            $this->_vars['parent_entity']['copy_id'] = \ExtensionCopyModel::MODULE_PROCESS;
            $this->_vars['parent_entity']['data_id'] = $this->_history_model->processOperations->process_id;
            $this->_vars['parent_entity']['operations_unique_index'] = $this->_history_model->processOperations->unique_index;
        } elseif(!empty($this->_message_params['{process_id}']) && !empty($this->_message_params['{unique_index}'])){
            $this->_vars['parent_entity']['copy_id'] = \ExtensionCopyModel::MODULE_PROCESS;
            $this->_vars['parent_entity']['data_id'] = $this->_message_params['{process_id}'];
            $this->_vars['parent_entity']['operations_unique_index'] = $this->_message_params['{unique_index}'];
        }

        if( $this->_vars['parent_entity']['data_id'] !== null && $this->_vars['parent_entity']['operations_unique_index'] !== null){
            $this->_vars['parent_entity']['this_process'] = true;
            $this->_vars['parent_entity']['module_title'] = \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->title;
            $this->_vars['parent_entity']['module_data_title'] = \DataModel::getInstance()->setSelect('module_title')->setFrom('{{process}}')->setWhere('process_id='.$this->_vars['parent_entity']['data_id'])->findScalar();
            $this->_vars['parent_entity']['action'] = self::LINK_ACTION_PROCESS_OPERATION_RUN;
            $this->_vars['is_parent_entity'] = true;
        } elseif(in_array($this->_history_model->history_messages_index, array(self::MT_PROCESS_RELATE_OBJECT_EMPTY, self::MT_PROCESS_MUST_APPOINT_RESPONSIBLE))){
            $this->_vars['parent_entity']['this_process'] = true;
            $this->_vars['parent_entity']['copy_id'] = $this->_history_model->copy_id;
            $this->_vars['parent_entity']['data_id'] = $this->_history_model->data_id;
            $this->_vars['parent_entity']['module_title'] = \ExtensionCopyModel::model()->findByPk(\ExtensionCopyModel::MODULE_PROCESS)->title;
            $this->_vars['parent_entity']['module_data_title'] = \DataModel::getInstance()->setSelect('module_title')->setFrom('{{process}}')->setWhere('process_id='.$this->_vars['parent_entity']['data_id'])->findScalar();
            $this->_vars['parent_entity']['action'] = self::LINK_ACTION_PROCESS_RUN;
            $this->_vars['is_parent_entity'] = true;
        }
    }




   /**
     *  Возвращает cообщение, заполненое значениями
     */
    private function getFormatedMessage($message_type){
        $message = $this->{'get' . $message_type . $this->_object_name}();

        return $message;
    }



    private function addParamsToMessage($message, $params){
        $this->prepareMessageParams($params);

        if(array_key_exists('message', $params)){
            foreach($params['message'] as $search => $replace){
                if(is_array($replace)) continue;
                $message = str_replace($search, $replace, $message);
            }
        }

        if(array_key_exists('js', $params)){
            $this->addLinkAction($params['js']['action_key'], $params['js']['params']);
        }

        return $message;
    }



    private function prepareMessageParams(&$params){
        switch($this->_history_model->history_messages_index){
            case self::MT_DATE_ENDING_BECOME_TO:
                if(is_array($params) && !empty($params['message']['{datetime}'])){
                    $params['message']['{datetime}'] = Helper::formatTimeShort($params['message']['{datetime}']);
                }

                break;
        }
    }






    /**
     * getSubjectNotice - собирает и возвращает заголовок уведомления Notice
     */
    private function getSubjectNotice(){
        $messages = array();

        switch($this->_history_model->history_messages_index){
            case self::MT_COMMENT_CREATED://1
            case self::MT_COMMENT_CHANGED://2
            case self::MT_COMMENT_DELETED:// 3
            case self::MT_CREATED://4
            case self::MT_STATUS_CHANGED://5
            case self::MT_RESPONSIBLE_APPOINTED://7
            case self::MT_DATE_ENDING_CHANGED://8
            case self::MT_FILE_UPLOADED://9
            case self::MT_FILE_DELETED://10
            case self::MT_OPERATION_REJECTED://15
            case self::MT_OPERATION_CREATED_TASK://16
            case self::MT_DATE_ENDING_BECOME://22
            case self::MT_DATE_ENDING_BECOME_TO://23

                // 1 если нет родительской сущности
                if($this->_vars['is_parent_entity'] == false){
                    $tmp_message = $this->getMessageElement(self::ME_URL_CARD);
                    $action_key = $this->getActionKey();
                    $params = array(
                        'message' => array(
                            '{action_key}' => $action_key,
                            '{module_data_title}' => $this->_message_params['{module_data_title}'],
                        ),
                        'js' => array(
                            'action_key' => $action_key,
                            'params' => array(
                                'action' => self::LINK_ACTION_CARD,
                                'copy_id' => $this->_message_params['{copy_id}'],
                                'data_id' => $this->_message_params['{data_id}'],
                            )
                        )
                    );

                    $this->_block_action_key = $action_key;

                    $messages[] = $this->addParamsToMessage($tmp_message, $params);


                    //
                    if($this->_module_type == self::MODULE_TYPE_BASE){
                        $messages[] = $this->getMessageElement(self::ME_FROM, true);

                        $tmp_message = $this->getMessageElement(self::ME_URL_MODULE);
                        $action_key = $this->getActionKey();
                        $params = array(
                            'message' => array(
                                '{action_key}' => $action_key,
                                '{module_title}' => $this->_message_params['{module_title}'],
                            ),
                            'js' => array(
                                'action_key' => $action_key,
                                'params' => array(
                                    'action' => self::LINK_ACTION_MODULE,
                                    'copy_id' => $this->_message_params['{copy_id}'],
                                )
                            )
                        );
                        $messages[] = $this->addParamsToMessage($tmp_message, $params);
                    }


                // 2 родительская судность - связаный модуль по СДМ по полю Название
                } else
                    if($this->_vars['is_parent_entity'] && $this->_vars['parent_entity']['this_process'] == false){
                    $tmp_message = $this->getMessageElement(self::ME_URL_CARD);
                    $action_key = $this->getActionKey();
                    $params = array(
                        'message' => array(
                            '{action_key}' => $action_key,
                            '{module_data_title}' => $this->_message_params['{module_data_title}'],
                        ),
                        'js' => array(
                            'action_key' => $action_key,
                            'params' => array(
                                'action' => self::LINK_ACTION_CARD,
                                'copy_id' => $this->_message_params['{copy_id}'],
                                'data_id' => $this->_message_params['{data_id}'],
                                'pci' => $this->_vars['parent_entity']['copy_id'],
                                'pdi' => $this->_vars['parent_entity']['data_id'],
                            )
                        )
                    );

                    $this->_block_action_key = $action_key;

                    $messages[] = $this->addParamsToMessage($tmp_message, $params);


                    $messages[] = $this->getMessageElement(self::ME_FROM, true);

                    $tmp_message = $this->getMessageElement(self::ME_URL_CARD);
                    $action_key = $this->getActionKey();
                    $params = array(
                        'message' => array(
                            '{action_key}' => $action_key,
                            '{module_data_title}' => $this->_vars['parent_entity']['module_data_title'],
                        ),
                        'js' => array(
                            'action_key' => $action_key,
                            'params' => $this->_vars['parent_entity'],
                        )
                    );
                    $messages[] = $this->addParamsToMessage($tmp_message, $params);

                // 3 родительская сущность - оператор процесса
                } else if($this->_vars['is_parent_entity'] && $this->_vars['parent_entity']['this_process']){
                    $tmp_message = $this->getMessageElement(self::ME_URL_CARD);
                    $action_key = $this->getActionKey();
                    $params = array(
                        'message' => array(
                            '{action_key}' => $action_key,
                            '{module_data_title}' => $this->_message_params['{module_data_title}'],
                        ),
                        'js' => array(
                            'action_key' => $action_key,
                            'params' => $this->_vars['parent_entity'],
                        )
                    );

                    $this->_block_action_key = $action_key;

                    $messages[] = $this->addParamsToMessage($tmp_message, $params);

                    $messages[] = $this->getMessageElement(self::ME_FROM, true);

                    $tmp_message = $this->getMessageElement(self::ME_URL_CARD);
                    $action_key = $this->getActionKey();
                    $params = array(
                        'message' => array(
                            '{action_key}' => $action_key,
                            '{module_data_title}' => $this->_vars['parent_entity']['module_data_title'],
                        ),
                        'js' => array(
                            'action_key' => $action_key,
                            'params' => array(
                                'action' => self::LINK_ACTION_PROCESS_RUN,
                                'copy_id' => $this->_vars['parent_entity']['copy_id'],
                                'data_id' => $this->_vars['parent_entity']['data_id'],
                            )
                        )
                    );
                    $messages[] = $this->addParamsToMessage($tmp_message, $params);
                }
                break;

            case self::MT_DELETED://6
                // 1
                $tmp_message = $this->getMessageElement(self::ME_CARD);
                $params = array(
                    'message' => array(
                        '{module_data_title}' => $this->_message_params['{module_data_title}'],
                    ),
                );
                $messages[] = $this->addParamsToMessage($tmp_message, $params);
                // 2
                if($this->_module_type == self::MODULE_TYPE_BASE || $this->_module_type == self::MODULE_TYPE_TASK){
                    $messages[] = $this->getMessageElement(self::ME_FROM, true);

                    $tmp_message = $this->getMessageElement(self::ME_URL_MODULE);

                    $action_key = '';
                    if(!empty($this->_history_model->historyMarkView[0]) && $this->_history_model->historyMarkView[0]->is_view == false){
                        $action_key = $this->getActionKey();
                    }

                    $params = array(
                        'message' => array(
                            '{action_key}' => $action_key,
                            '{module_title}' => $this->_message_params['{module_title}'],
                        ),
                        'js' => array(
                            'action_key' => $action_key,
                            'params' => array(
                                'action' => self::LINK_ACTION_CARD_DELETE,
                                'history_id' => $this->_history_model->history_id,
                                'copy_id' => $this->_message_params['{copy_id}'],
                            )
                        )
                    );

                    $this->_block_action_key = $action_key;
                    $messages[] = $this->addParamsToMessage($tmp_message, $params);
                }
                break;

            case self::MT_ENABLE_MODULE_ACCESS://12
            case self::MT_DISABLE_MODULE_ACCESS://13
            case self::MT_CHANGED_MODULE_ACCESS://14
                $tmp_message = $this->getMessageElement(self::ME_URL_MODULE);
                $action_key = $this->getActionKey();
                $params = array(
                    'message' => array(
                        '{action_key}' => $action_key,
                        '{module_title}' => $this->_message_params['{module_title}'],
                    ),
                    'js' => array(
                        'action_key' => $action_key,
                        'params' => array(
                            'action' => self::LINK_ACTION_MODULE,
                            'copy_id' => $this->_message_params['{copy_id}'],
                        )
                    )
                );
                $this->_block_action_key = $action_key;
                $messages[] = $this->addParamsToMessage($tmp_message, $params);
                break;

            case self::MT_OPERATION_MUST_CREATED_RECORD://17
            case self::MT_OPERATION_MUST_CHANGED_RECORD://18
            //case self::MT_OPERATION_CREATED_NOTIFICATION://19
            case self::MT_PROCESS_RELATE_OBJECT_EMPTY://20
            case self::MT_PROCESS_MUST_APPOINT_RESPONSIBLE://21
                // 1 клик для блока
                $action_key = $this->getActionKey();
                $params = array(
                    'js' => array(
                        'action_key' => $action_key,
                        'params' => $this->_vars['parent_entity'],
                    )
                );
                $this->_block_action_key = $action_key;
                $messages[] = $this->addParamsToMessage(null, $params);


                // 2
                $tmp_message = $this->getMessageElement(self::ME_URL_CARD);
                $action_key = $this->getActionKey();
                $params = array(
                    'message' => array(
                        '{action_key}' => $action_key,
                        '{module_data_title}' => $this->_vars['parent_entity']['module_data_title'] ,
                    ),
                    'js' => array(
                        'action_key' => $action_key,
                        'params' => array(
                            'action' => self::LINK_ACTION_PROCESS_RUN,
                            'copy_id' => $this->_vars['parent_entity']['copy_id'],
                            'data_id' => $this->_vars['parent_entity']['data_id'],
                        )
                    )
                );
                $messages[] = $this->addParamsToMessage($tmp_message, $params);

                $messages[] = $this->getMessageElement(self::ME_FROM, true);

                $tmp_message = $this->getMessageElement(self::ME_URL_MODULE);
                $action_key = $this->getActionKey();
                $params = array(
                    'message' => array(
                        '{action_key}' => $action_key,
                        '{module_title}' => $this->_vars['parent_entity']['module_title'],
                    ),
                    'js' => array(
                        'action_key' => $action_key,
                        'params' => array(
                            'action' => self::LINK_ACTION_MODULE,
                            'copy_id' => $this->_vars['parent_entity']['copy_id'],
                        )
                    )
                );
                $messages[] = $this->addParamsToMessage($tmp_message, $params);

                break;
         }

        return implode(' ', $messages);
    }


    /**
     * getUrl
     * @return string
     */
    private function getUrl($action, $params){
        $site_url = ParamsModel::getValueFromModel('site_url', ParamsModel::model()->findAll());

        switch($action){
            case self::LINK_ACTION_CARD:
                $url_params = array();
                $url_params[] = 'modal_ev=' . $params['data_id'];
                if(!empty($params['pci']) && !empty($params['pdi'])){
                    $url_params[] = 'pci=' . $params['pci'];
                    $url_params[] = 'pdi=' . $params['pdi'];
                }
                $history_params = [
                    'copy_id' => $params['copy_id'],
                    'data_id' => $params['data_id'],
                ];
                $module_action = (new \History())->getModuleAction($history_params, true);
                return $site_url . '/module/listView/' . $module_action . '/' . $params['copy_id'] . (!empty($url_params) ? '?' . implode('&', $url_params) : '');
            case self::LINK_ACTION_MODULE:
                return $site_url . '/module/listView/show/' . $params['copy_id'];
            case self::LINK_ACTION_PROCESS_RUN:
                return $site_url . '/module/BPM/run/' . $params['copy_id'] . '?process_id=' . $params['data_id'];
            case self::LINK_ACTION_PROCESS_OPERATION_RUN:
                return $site_url . '/module/BPM/run/' . $params['copy_id'] . '?process_id=' . $params['data_id'] . '&unique_index=' . $params['operations_unique_index'];
            case self::LINK_ACTION_USER_PROFILE:
                return $site_url . '/profile?users_id=' . $params['user_id'];
            default:
                return $site_url;
        }
    }





    /**
     * getSubjectNd - собирает и возвращает заголовок уведомления Nd (рассылка по email)
     */
    private function getSubjectNd(){
        $messages = array();

        switch($this->_history_model->history_messages_index){
            case self::MT_COMMENT_CREATED://1
            case self::MT_COMMENT_CHANGED://2
            case self::MT_COMMENT_DELETED:// 3
            case self::MT_CREATED://4
            case self::MT_STATUS_CHANGED://5
            case self::MT_RESPONSIBLE_APPOINTED://7
            case self::MT_DATE_ENDING_CHANGED://8
            case self::MT_FILE_UPLOADED://9
            case self::MT_FILE_DELETED://10
            case self::MT_OPERATION_REJECTED://15
            case self::MT_OPERATION_CREATED_TASK://16
            case self::MT_DATE_ENDING_BECOME://22
            case self::MT_DATE_ENDING_BECOME_TO://23

                // 1 если нет родительской сущности
                if($this->_vars['is_parent_entity'] == false){
                    $tmp_message = $this->getMessageElement(self::ME_URL_CARD_DN);
                    $url_params = array(
                        'copy_id' => $this->_message_params['{copy_id}'],
                        'data_id' => $this->_message_params['{data_id}'],
                    );
                    $params = array(
                        'message' => array(
                            '{url_card}' => $this->getUrl(self::LINK_ACTION_CARD, $url_params),
                            '{module_data_title}' => $this->_message_params['{module_data_title}'],
                        )
                    );
                    $messages[] = $this->addParamsToMessage($tmp_message, $params);


                    //2
                    $messages[] = $this->getMessageElement(self::ME_FROM, true);
                    $tmp_message = $this->getMessageElement(self::ME_URL_MODULE_DN);
                    $url_params = array(
                        'copy_id' => $this->_message_params['{copy_id}'],
                    );
                    $params = array(
                        'message' => array(
                            '{url_module}' => $this->getUrl(self::LINK_ACTION_MODULE, $url_params),
                            '{module_title}' => $this->_message_params['{module_title}'],
                        )
                    );
                    $messages[] = $this->addParamsToMessage($tmp_message, $params);

                    // 2 родительская судность - связаный модуль по СДМ по полю Название
                } else
                    if($this->_vars['is_parent_entity'] && $this->_vars['parent_entity']['this_process'] == false){
                        $tmp_message = $this->getMessageElement(self::ME_URL_CARD_DN);
                        $url_params = array(
                            'copy_id' => $this->_message_params['{copy_id}'],
                            'data_id' => $this->_message_params['{data_id}'],
                            'pci' => $this->_vars['parent_entity']['copy_id'],
                            'pdi' => $this->_vars['parent_entity']['data_id'],
                        );
                        $params = array(
                            'message' => array(
                                '{url_card}' => $this->getUrl(self::LINK_ACTION_CARD, $url_params),
                                '{module_data_title}' => $this->_message_params['{module_data_title}'],
                            )
                        );
                        $messages[] = $this->addParamsToMessage($tmp_message, $params);

                        $messages[] = $this->getMessageElement(self::ME_FROM, true);

                        $tmp_message = $this->getMessageElement(self::ME_URL_CARD_DN);
                        $url_params = array(
                            'copy_id' => $this->_vars['parent_entity']['copy_id'],
                            'data_id' => $this->_vars['parent_entity']['data_id'],
                        );
                        $params = array(
                            'message' => array(
                                '{url_card}' => $this->getUrl(self::LINK_ACTION_CARD, $url_params),
                                '{module_data_title}' => $this->_vars['parent_entity']['module_data_title'],
                            )
                        );
                        $messages[] = $this->addParamsToMessage($tmp_message, $params);

                        // 3 родительская сущность - оператор процесса
                    } else if($this->_vars['is_parent_entity'] && $this->_vars['parent_entity']['this_process']){
                        $tmp_message = $this->getMessageElement(self::ME_URL_CARD_DN);
                        $url_params = array(
                            'copy_id' => $this->_vars['parent_entity']['copy_id'],
                            'data_id' => $this->_vars['parent_entity']['data_id'],
                            'operations_unique_index' => $this->_vars['parent_entity']['operations_unique_index'],
                       );
                        $params = array(
                            'message' => array(
                                '{url_card}' => $this->getUrl(self::LINK_ACTION_PROCESS_OPERATION_RUN, $url_params),
                                '{module_data_title}' => $this->_message_params['{module_data_title}'],
                            )
                        );
                        $messages[] = $this->addParamsToMessage($tmp_message, $params);

                        $messages[] = $this->getMessageElement(self::ME_FROM, true);

                        $tmp_message = $this->getMessageElement(self::ME_URL_CARD_DN);
                        $url_params = array(
                            'copy_id' => $this->_vars['parent_entity']['copy_id'],
                            'data_id' => $this->_vars['parent_entity']['data_id'],
                        );
                        $params = array(
                            'message' => array(
                                '{url_card}' => $this->getUrl(self::LINK_ACTION_PROCESS_RUN, $url_params),
                                '{module_data_title}' => $this->_vars['parent_entity']['module_data_title'],
                            )
                        );
                        $messages[] = $this->addParamsToMessage($tmp_message, $params);
                    }

                break;



            case self::MT_DELETED://6
                // 1
                $tmp_message = $this->getMessageElement(self::ME_CARD);
                $params = array(
                    'message' => array(
                        '{module_data_title}' => $this->_message_params['{module_data_title}'],
                    ),
                );
                $messages[] = $this->addParamsToMessage($tmp_message, $params);

                //2
                $messages[] = $this->getMessageElement(self::ME_FROM, true);
                $tmp_message = $this->getMessageElement(self::ME_URL_MODULE_DN);
                $url_params = array(
                    'copy_id' => $this->_message_params['{copy_id}'],
                );
                $params = array(
                    'message' => array(
                        '{url_module}' => $this->getUrl(self::LINK_ACTION_MODULE, $url_params),
                        '{module_title}' => $this->_message_params['{module_title}'],
                    )
                );
                $messages[] = $this->addParamsToMessage($tmp_message, $params);

                break;

            case self::MT_ENABLE_MODULE_ACCESS://12
            case self::MT_DISABLE_MODULE_ACCESS://13
            case self::MT_CHANGED_MODULE_ACCESS://14
                $tmp_message = $this->getMessageElement(self::ME_URL_MODULE_DN);
                $url_params = array(
                    'copy_id' => $this->_message_params['{copy_id}'],
                );
                $params = array(
                    'message' => array(
                        '{url_module}' => $this->getUrl(self::LINK_ACTION_MODULE, $url_params),
                        '{module_title}' => $this->_message_params['{module_title}'],
                    )
                );
                $messages[] = $this->addParamsToMessage($tmp_message, $params);
                break;

            case self::MT_OPERATION_MUST_CREATED_RECORD://17
            case self::MT_OPERATION_MUST_CHANGED_RECORD://18
            //case self::MT_OPERATION_CREATED_NOTIFICATION://19
            case self::MT_PROCESS_RELATE_OBJECT_EMPTY://20
            case self::MT_PROCESS_MUST_APPOINT_RESPONSIBLE://21
                // 2
                $tmp_message = $this->getMessageElement(self::ME_URL_CARD_DN);
                $url_params = array(
                    'copy_id' => $this->_vars['parent_entity']['copy_id'],
                    'data_id' => $this->_vars['parent_entity']['data_id'],
                );
                $params = array(
                    'message' => array(
                        '{url_card}' => $this->getUrl(self::LINK_ACTION_PROCESS_RUN, $url_params),
                        '{module_data_title}' => $this->_vars['parent_entity']['module_data_title'] ,
                    )
                );
                $messages[] = $this->addParamsToMessage($tmp_message, $params);

                $messages[] = $this->getMessageElement(self::ME_FROM, true);

                $tmp_message = $this->getMessageElement(self::ME_URL_MODULE_DN);
                $url_params = array(
                    'copy_id' => $this->_vars['parent_entity']['copy_id'],
                );
                $params = array(
                    'message' => array(
                        '{url_module}' => $this->getUrl(self::ME_URL_MODULE_DN, $url_params),
                        '{module_title}' => $this->_vars['parent_entity']['module_title'],
                    )
                );
                $messages[] = $this->addParamsToMessage($tmp_message, $params);

                break;
        }

        return implode(' ', $messages);
    }



    /**
     * getSubjectActivity - собирает и возвращает заголовок уведомления Activity
     */
    private function getSubjectActivity(){
        $this->_key_prefix = '_a_';
        return $this->getSubjectNotice();
    }














    /**
     * getMessageNotice - собирает и возвращает тело уведомления для Notice
     */
    private function getMessageNotice(){
        $messages = array();
        $allow_tags = '<span></span><a></a><br><br/><img><div>';

        switch($this->_history_model->history_messages_index){
            case self::MT_COMMENT_CREATED://1
            case self::MT_COMMENT_CHANGED://2
            case self::MT_COMMENT_DELETED:// 3
            case self::MT_CREATED://4
            case self::MT_STATUS_CHANGED://5
            case self::MT_DELETED://6
            case self::MT_RESPONSIBLE_APPOINTED://7
            case self::MT_DATE_ENDING_CHANGED://8
            case self::MT_FILE_UPLOADED://9
            case self::MT_FILE_DELETED://10
            case self::MT_OPERATION_REJECTED://15
                // 1
                $messages[] = $this->getMessageElement(self::ME_USER, true);

                $tmp_message = $this->getMessageElement(self::ME_URL_USER_PROFILE);
                $action_key = $this->getActionKey();
                $params = array(
                    'message' => array(
                        '{action_key}' => $action_key,
                        '{user_full_name}' => $this->_message_params['{user_full_name}'],
                    ),
                    'js' => array(
                        'action_key' => $action_key,
                        'params' => array(
                            'action' => self::LINK_ACTION_USER_PROFILE,
                            'user_id' => $this->_message_params['{user_id}'],
                        )
                    )
                );
                $messages[] = $this->addParamsToMessage($tmp_message, $params);

                // 2
                // message text
                $tmp_message = $this->getMessageText();

                $params = array('message' => $this->_message_params);
                $messages[] = $this->addParamsToMessage($tmp_message, $params);



                // add file link
                if(in_array($this->_history_model->history_messages_index, array(self::MT_FILE_UPLOADED))){
                    $tmp_message = $this->getMessageElement(self::ME_URL_FILE);
                    $params = array('message' => $this->_message_params);
                    $messages[] = $this->addParamsToMessage($tmp_message, $params);
                }

                // add {comment}
                $tmp_message = $this->getMessageElement(self::ME_COMMENT);
                $params = array('message' => $this->_message_params);
                $messages[] = $this->addParamsToMessage($tmp_message, $params);


                switch($this->_history_model->history_messages_index){
                    case self::MT_COMMENT_CREATED:
                    case self::MT_COMMENT_CHANGED:
                        if (!empty($this->_message_params['{file_title}'])) {
                            foreach ($this->_message_params['{file_title}'] as $key => $name) {
                                if (!empty($this->_message_params['{file_url}'][$key]) && UploadsModel::checkFileExist($this->_message_params['{file_url}'][$key]) ||
                                    (!$name && preg_match('~|\/~', $this->_message_params['{file_url}'][$key]) && !empty($this->_message_params['{uploads_id}'][$key]))
                                ) {

                                    if(!$name){
                                        $upload_model = UploadsModel::model()->findByPk($this->_message_params['{uploads_id}'][$key]);
                                        if($upload_model) {
                                            $name = $upload_model->getFileType();
                                            $this->_message_params['{file_url}'][$key] = $upload_model->getAttribute('file_path');
                                        } else {
                                            continue;
                                        }
                                    }
                                    //$message = rtrim($message);
                                    $messages[] = ' ' . Yii::t('history', 'Download file link', array('{file_title}' => $name, '{file_url}' => $this->_message_params['{file_url}'][$key]));
                                } else
                                    if($name){
                                        //$message = rtrim($message);
                                        $messages[] = ' "'.$name.'"';
                                    }
                            }
                        }
                }

                break;

            case self::MT_ENABLE_MODULE_ACCESS://12
            case self::MT_DISABLE_MODULE_ACCESS://13
            case self::MT_CHANGED_MODULE_ACCESS://14
            case self::MT_OPERATION_CREATED_TASK://16
            case self::MT_OPERATION_MUST_CREATED_RECORD://17
            case self::MT_OPERATION_MUST_CHANGED_RECORD://18
            //case self::MT_OPERATION_CREATED_NOTIFICATION://19
            case self::MT_PROCESS_RELATE_OBJECT_EMPTY://20
            case self::MT_PROCESS_MUST_APPOINT_RESPONSIBLE://21
            case self::MT_DATE_ENDING_BECOME://22
            case self::MT_DATE_ENDING_BECOME_TO://23
                // 2
                // message text
                $tmp_message = $this->getMessageText();
                $params = array('message' => $this->_message_params);
                $messages[] = $this->addParamsToMessage($tmp_message, $params);

                // add {comment}
                $tmp_message = $this->getMessageElement(self::ME_COMMENT);
                $params = array('message' => $this->_message_params);
                $messages[] = $this->addParamsToMessage($tmp_message, $params);

                break;
        }
        $messages = implode(' ', $messages);
        $messages = strip_tags($messages, $allow_tags);

        $messages = $this->translateText($messages);

        return $messages;
    }




    /**
     * getMessageNotice - собирает и возвращает тело уведомления для Nd (рассылка по email)
     */
    private function getMessageNd(){
        $messages = array();
        $allow_tags = '<span></span><a></a><br><br/><img><div>';

        switch($this->_history_model->history_messages_index){
            case self::MT_COMMENT_CREATED://1
            case self::MT_COMMENT_CHANGED://2
            case self::MT_COMMENT_DELETED:// 3
            case self::MT_CREATED://4
            case self::MT_STATUS_CHANGED://5
            case self::MT_DELETED://6
            case self::MT_RESPONSIBLE_APPOINTED://7
            case self::MT_DATE_ENDING_CHANGED://8
            case self::MT_FILE_UPLOADED://9
            case self::MT_FILE_DELETED://10
            case self::MT_OPERATION_REJECTED://15
                // 1
                $tmp_message = $this->getMessageElement(self::ME_URL_USER_PROFILE_DN);
                $params = array(
                    'message' => array(
                        '{user_full_name}' => $this->_message_params['{user_full_name}'],
                    )
                );
                $messages[] = $this->addParamsToMessage($tmp_message, $params);

                // 2
                // message text
                $tmp_message = $this->getMessageText();

                $params = array('message' => $this->_message_params);
                $messages[] = $this->addParamsToMessage($tmp_message, $params);

                // add file link
                if(in_array($this->_history_model->history_messages_index, array(self::MT_FILE_UPLOADED))){
                    $tmp_message = $this->getMessageElement(self::ME_URL_FILE_DN);
                    $params = array('message' => $this->_message_params);
                    $messages[] = $this->addParamsToMessage($tmp_message, $params);
                }

                // add {comment}
                $tmp_message = $this->getMessageElement(self::ME_COMMENT);
                $params = array('message' => $this->_message_params);
                $messages[] = $this->addParamsToMessage($tmp_message, $params);


                switch($this->_history_model->history_messages_index){
                    case self::MT_COMMENT_CREATED:
                    case self::MT_COMMENT_CHANGED:
                        if (!empty($this->_message_params['{file_title}'])) {
                            foreach ($this->_message_params['{file_title}'] as $key => $name) {
                                if (!empty($this->_message_params['{file_url}'][$key]) && UploadsModel::checkFileExist($this->_message_params['{file_url}'][$key]) ||
                                    (!$name && preg_match('~|\/~', $this->_message_params['{file_url}'][$key]) && !empty($this->_message_params['{uploads_id}'][$key]))
                                ) {

                                    if(!$name){
                                        $upload_model = UploadsModel::model()->findByPk($this->_message_params['{uploads_id}'][$key]);
                                        if($upload_model) {
                                            $name = $upload_model->getFileType();
                                            $this->_message_params['{file_url}'][$key] = $upload_model->getAttribute('file_path');
                                        } else {
                                            continue;
                                        }
                                    }
                                    $messages[] = ' ' . Yii::t('history', 'Download file link', array('{file_title}' => $name, '{file_url}' => $this->_message_params['{file_url}'][$key]));
                                } else
                                    if($name){
                                        $messages[] = ' "'.$name.'"';
                                    }
                            }
                        }
                }

                break;

            case self::MT_ENABLE_MODULE_ACCESS://12
            case self::MT_DISABLE_MODULE_ACCESS://13
            case self::MT_CHANGED_MODULE_ACCESS://14
            case self::MT_OPERATION_CREATED_TASK://16
            case self::MT_OPERATION_MUST_CREATED_RECORD://17
            case self::MT_OPERATION_MUST_CHANGED_RECORD://18
            //case self::MT_OPERATION_CREATED_NOTIFICATION://19
            case self::MT_PROCESS_RELATE_OBJECT_EMPTY://20
            case self::MT_PROCESS_MUST_APPOINT_RESPONSIBLE://21
            case self::MT_DATE_ENDING_BECOME://22
            case self::MT_DATE_ENDING_BECOME_TO://23
                // 2
                // message text
                $tmp_message = $this->getMessageText();
                $params = array('message' => $this->_message_params);
                $messages[] = $this->addParamsToMessage($tmp_message, $params);

                // add {comment}
                $tmp_message = $this->getMessageElement(self::ME_COMMENT);
                $params = array('message' => $this->_message_params);
                $messages[] = $this->addParamsToMessage($tmp_message, $params);

                break;
        }
        $messages = implode(' ', $messages);
        $messages = strip_tags($messages, $allow_tags);

        $messages = $this->translateText($messages);

        return $messages;
    }



    /**
     * getMessageNotice - собирает и возвращает тело уведомления для Activity
     */
    private function getMessageActivity(){
        $messages = array();
        $allow_tags = '<span></span><a></a><br><br/><img>';

        switch($this->_history_model->history_messages_index){
            case self::MT_COMMENT_CREATED://1
            case self::MT_COMMENT_CHANGED://2
            case self::MT_COMMENT_DELETED:// 3
            case self::MT_CREATED://4
            case self::MT_STATUS_CHANGED://5
            case self::MT_DELETED://6
            case self::MT_RESPONSIBLE_APPOINTED://7
            case self::MT_DATE_ENDING_CHANGED://8
            case self::MT_FILE_UPLOADED://9
            case self::MT_FILE_DELETED://10
            case self::MT_OPERATION_REJECTED://15
            case self::MT_ENABLE_MODULE_ACCESS://12
            case self::MT_DISABLE_MODULE_ACCESS://13
            case self::MT_CHANGED_MODULE_ACCESS://14
            case self::MT_OPERATION_CREATED_TASK://16
            case self::MT_OPERATION_MUST_CREATED_RECORD://17
            case self::MT_OPERATION_MUST_CHANGED_RECORD://18
            //case self::MT_OPERATION_CREATED_NOTIFICATION://19
            case self::MT_PROCESS_RELATE_OBJECT_EMPTY://20
            case self::MT_PROCESS_MUST_APPOINT_RESPONSIBLE://21
            case self::MT_DATE_ENDING_BECOME://22
            case self::MT_DATE_ENDING_BECOME_TO://23

                // 2
                // message text
                $message_index = 0;
                if($this->_history_model->history_messages_index == self::MT_RESPONSIBLE_APPOINTED && isset($this->_message_params['{user_id}'])){
                    if($this->_message_params['{user_id}'] == WebUser::getUserId()){
                        $message_index = 1;
                    }
                }

                $tmp_message = $this->getMessageText($message_index);
                $params = array('message' => $this->_message_params);
                $messages[] = $this->addParamsToMessage($tmp_message, $params);

                // add {comment}
                $tmp_message = $this->getMessageElement(self::ME_COMMENT);
                $params = array('message' => $this->_message_params);
                $messages[] = $this->addParamsToMessage($tmp_message, $params);

                if($this->_history_model->history_messages_index == self::MT_RESPONSIBLE_APPOINTED && isset($this->_message_params['{user_id}'])) {
                    if($this->_message_params['{user_id}'] != WebUser::getUserId()){
                        $tmp_message = $this->getMessageElement(self::ME_URL_USER_PROFILE);
                        $action_key = $this->getActionKey();
                        $params = array(
                            'message' => array(
                                '{action_key}' => $action_key,
                                '{user_full_name}' => $this->_message_params['{user_full_name}'],
                            ),
                            'js' => array(
                                'action_key' => $action_key,
                                'params' => array(
                                    'action' => self::LINK_ACTION_USER_PROFILE,
                                    'user_id' => $this->_message_params['{user_id}'],
                                )
                            )
                        );
                        $messages[] = $this->addParamsToMessage($tmp_message, $params);
                    }
                }
                break;
        }
        $messages = implode(' ', $messages);
        $messages = strip_tags($messages, $allow_tags);

        $messages = $this->translateText($messages);

        return $messages;
    }





    private function getMessageElement($me_key, $translate = false){
        $result = $this->_message_elements[$me_key];
        if($translate){
            $result = \Yii::t('history', $result);
        }

        return $result;
    }





    private function getTagsFormat($name){
        if($this->_object_name == self::OBJECT_DN) return;
        return $this->_tags_formats[$this->_module_type][$name . '_' . $this->_object_name];
    }



    private function getMessageText($message_index = 0){
        $message = $this->_message_list[$this->_history_model->history_messages_index]['message_' . $this->_object_name];
        if(empty($message)) return;

        if(!empty($message[$this->_module_type])) $result = $message[$this->_module_type];
        if(!empty($message['general'])) $result = $message['general'];

        if(!empty($result)){
            $result = \Yii::t('history', $result, array($message_index));
        }

        return $result;
    }


    /**
     * Ищет в строке текст и его переводит с помощью фукнции eval. В строке должна быть конструкция PHP по маске: Yii::t(*)
     * @param $message
     * @return null|string|string[]
     */
    private function translateText($message){
        if($message == false){
            return $message;
        }

        $translate_function = function($matches){
            $code = $matches[0];
            return eval('return ' . $code . ';');
        };

        return preg_replace_callback('/(Yii::t\()(.*?)("\))/i', $translate_function, $message);
    }




    private $_message_elements = array(
        self::ME_URL_CARD => '<a href="javascript:void(0)" class="notice_navigation_link" data-action_key="{action_key}">{module_data_title}</a>',
        self::ME_URL_MODULE => '<a href="javascript:void(0)" class="notice_navigation_link" data-action_key="{action_key}">{module_title}</a>',
        self::ME_URL_FILE => '<a href="{file_url}" class="file-download" >"{file_title}"</a>',
        self::ME_URL_USER_PROFILE => '{user_full_name}',

        self::ME_URL_CARD_DN => '<a style="color:#009edb!important;text-decoration:underline;font-size:15px;font-family:arial;" href="{url_card}">{module_data_title}</a>',
        self::ME_URL_MODULE_DN => '<a style="color:#009edb!important;text-decoration:underline;font-size:15px;font-family:arial;" href="{url_module}">{module_title}</a>',
        self::ME_URL_FILE_DN => '<a style="color:#009edb!important;text-decoration:none;font-size:15px;font-family:arial;" href="{file_url}" class="file-download" >"{file_title}"</a>',
        self::ME_URL_USER_PROFILE_DN => '<span style="font-weight:bold;">{user_full_name}</span>',
        self::ME_CARD => '{module_data_title}',
        self::ME_FROM => 'from',
        self::ME_COMMENT => '{comment}',
        self::ME_USER => 'User',
    );




    private $_tags_formats = array(
        self::MODULE_TYPE_BASE => array(
            'color_notice' => 'alert-info blue',
            'image_notice' => 'fa-tags',
            'color_activity' => 'blue',
            'image_activity' => 'fa-tags',
        ),
        self::MODULE_TYPE_TASK => array(
            'color_notice' => 'alert-info violet',
            'image_notice' => 'fa-tasks',
            'color_activity' => 'violet',
            'image_activity' => 'fa-tasks',
        ),
    );



    private $_message_list = array(
        // 1
        self::MT_COMMENT_CREATED => array(
            'message_notice' => array(
                'general' => 'created comment: {message}',
            ),
            'message_nd' => array(
                'general' => 'created comment: {message}',
            ),
            'message_activity' => array(
                'general' => 'You created comment: {message}',
            ),
        ),
        // 2
        self::MT_COMMENT_CHANGED => array(
            'message_notice' => array(
                'general' => 'changed comment: {message}',
            ),
            'message_nd' => array(
                'general' => 'changed comment: {message}',
            ),
            'message_activity' => array(
                'general' => 'You changed comment: {message}',
            ),
        ),
        // 3
        self::MT_COMMENT_DELETED => array(
            'message_notice' => array(
                'general' => 'deleted comment: {message}',
            ),
            'message_nd' => array(
                'general' => 'deleted comment: {message}',
            ),
            'message_activity' => array(
                'general' => 'You deleted comment: {message}',
            ),
        ),
        // 4
        self::MT_CREATED => array(
            'message_notice' => array(
                self::MODULE_TYPE_BASE => 'created record',
                self::MODULE_TYPE_TASK => 'created task',
            ),
            'message_nd' => array(
                self::MODULE_TYPE_BASE => 'created record',
                self::MODULE_TYPE_TASK => 'created task',
            ),
            'message_activity' => array(
                self::MODULE_TYPE_BASE => 'You created record',
                self::MODULE_TYPE_TASK => 'You created task',
            ),
        ),
        // 5
        self::MT_STATUS_CHANGED => array(
            'message_notice' => array(
                'general' => 'changed status on "{status}"',
            ),
            'message_nd' => array(
                'general' => 'changed status on "{status}"',
            ),
            'message_activity' => array(
                'general' => 'You changed status to "{status}"',
            ),
        ),
        // 6
        self::MT_DELETED => array(
            'message_notice' => array(
                self::MODULE_TYPE_BASE => 'deleted record',
                self::MODULE_TYPE_TASK => 'deleted task',
            ),
            'message_nd' => array(
                self::MODULE_TYPE_BASE => 'deleted record',
                self::MODULE_TYPE_TASK => 'deleted task',
            ),
            'message_activity' => array(
                self::MODULE_TYPE_BASE => 'You deleted record',
                self::MODULE_TYPE_TASK => 'You deleted task',
            ),
        ),
        // 7
        self::MT_RESPONSIBLE_APPOINTED => array(
            'message_notice' => array(
                'general' => 'appointed as responsible',
            ),
            'message_nd' => array(
                'general' => 'appointed as responsible',
            ),
            'message_activity' => array(
                'general' => 'You appointed as responsible',
            ),
        ),
        // 8
        self::MT_DATE_ENDING_CHANGED => array(
            'message_notice' => array(
                'general' => 'changed due date on "{date_ending}"',
            ),
            'message_nd' => array(
                'general' => 'changed due date on "{date_ending}"',
            ),
            'message_activity' => array(
                'general' => 'You changed due date on "{date_ending}"',
            ),
        ),
        // 9
        self::MT_FILE_UPLOADED => array(
            'message_notice' => array(
                'general' => 'uploaded file',
            ),
            'message_nd' => array(
                'general' => 'uploaded file',
            ),
            'message_activity' => array(
                'general' => 'You uploaded file',
            ),
        ),
        // 10
        self::MT_FILE_DELETED => array(
            'message_notice' => array(
                'general' => 'deleted file "{file_title}"',
            ),
            'message_nd' => array(
                'general' => 'deleted file "{file_title}"',
            ),
            'message_activity' => array(
                'general' => 'You deleted file',
            ),
        ),
        // 11
        /*
        self::MT_CHANGED => array(
            'message_notice' => array(
                self::MODULE_TYPE_BASE => 'change record',
                self::MODULE_TYPE_TASK => 'changed task',
            ),
            'message_nd' => array(
                self::MODULE_TYPE_BASE => 'change record',
                self::MODULE_TYPE_TASK => 'changed task',
            ),
            'message_activity' => array(
                self::MODULE_TYPE_BASE => 'You changed record',
                self::MODULE_TYPE_TASK => 'You changed task',
            ),
        ),
        */
        // 12
        self::MT_ENABLE_MODULE_ACCESS => array(
            'message_notice' => array(
                self::MODULE_TYPE_BASE => 'Enabled access to the module "{module_title}"',
            ),
            'message_nd' => array(
                self::MODULE_TYPE_BASE => 'Enabled access to the module "{module_title}"',
            ),
            'message_activity' => array(
                self::MODULE_TYPE_BASE => 'Enabled access to the module',
            ),
        ),
        // 13
        self::MT_DISABLE_MODULE_ACCESS => array(
            'message_notice' => array(
                self::MODULE_TYPE_BASE => 'Disabled access to the module "{module_title}"',
            ),
            'message_nd' => array(
                self::MODULE_TYPE_BASE => 'Disabled access to the module "{module_title}"',
            ),
            'message_activity' => array(
                self::MODULE_TYPE_BASE => 'Disabled access to the module',
            ),
        ),
        // 14
        self::MT_CHANGED_MODULE_ACCESS => array(
            'message_notice' => array(
                self::MODULE_TYPE_BASE => 'Changed access to the module "{module_title}"',
            ),
            'message_nd' => array(
                self::MODULE_TYPE_BASE => 'Changed access to the module "{module_title}"',
            ),
            'message_activity' => array(
                self::MODULE_TYPE_BASE => 'Changed access to the module',
            ),
        ),
        // 15
        self::MT_OPERATION_REJECTED => array(
            'message_notice' => array(
                self::MODULE_TYPE_BASE => 'rejected record',
                self::MODULE_TYPE_TASK => 'rejected task',
            ),
            'message_nd' => array(
                self::MODULE_TYPE_BASE => 'rejected record',
                self::MODULE_TYPE_TASK => 'rejected task',
            ),
            'message_activity' => array(
                self::MODULE_TYPE_BASE => 'You rejected the record',
                self::MODULE_TYPE_TASK => 'You rejected task',
            ),
        ),
        // 16
        self::MT_OPERATION_CREATED_TASK => array(
            'message_notice' => array(
                self::MODULE_TYPE_TASK => 'Task created',
            ),
            'message_nd' => array(
                self::MODULE_TYPE_TASK => 'Task created',
            ),
        ),
        // 17
        self::MT_OPERATION_MUST_CREATED_RECORD => array(
            'message_notice' => array(
                'general' => 'You must create a record',
            ),
            'message_nd' => array(
                'general' => 'You must create a record',
            ),
        ),
        // 18
        self::MT_OPERATION_MUST_CHANGED_RECORD => array(
            'message_notice' => array(
                'general' => 'You must change the record',
            ),
            'message_nd' => array(
                'general' => 'You must change the record',
            ),
        ),
        // 19
        /*
        self::MT_OPERATION_CREATED_NOTIFICATION => array(
            'message_notice' => array(
                'general' => 'Created the notification',
            ),
            'message_nd' => array(
                'general' => 'Created the notification',
            ),
        ),
        */
        // 20
        self::MT_PROCESS_RELATE_OBJECT_EMPTY => array(
            'message_notice' => array(
                'general' => 'You must specify related object',
            ),
            'message_nd' => array(
                'general' => 'You must specify related object',
            ),
        ),
        // 21
        self::MT_PROCESS_MUST_APPOINT_RESPONSIBLE => array(
            'message_notice' => array(
                self::MODULE_TYPE_BASE => 'You must appoint a responsible',
            ),
            'message_nd' => array(
                self::MODULE_TYPE_BASE => 'You must appoint a responsible',
            ),
        ),
        // 22
        self::MT_DATE_ENDING_BECOME => array(
            'message_notice' => array(
                'general' => 'Today is date ending',
            ),
            'message_nd' => array(
                'general' => 'Today is date ending',
            ),
            'message_activity' => array(
                'general' => 'Today is date ending',
            ),
        ),
        // 22
        self::MT_DATE_ENDING_BECOME_TO => array(
            'message_notice' => array(
                'general' => 'The completion date expires today in {datetime}',
            ),
            'message_nd' => array(
                'general' => 'The completion date expires today in {datetime}',
            ),
            'message_activity' => array(
                'general' => 'The completion date expires today in {datetime}',
            ),
        ),
    );


}



















