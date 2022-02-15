<?php

/**
 * ContentReloadModel -  Класс для управление ссылками и из событиями.
 * @autor Alex R.
 */


class ContentReloadModel{

    // Content reload Actions run
    const CR_ACTION_RUN_LOAD_MODULE             = 'loadModule';
    const CR_ACTION_RUN_LOAD_BPM_PROCESS        = 'loadBpmProcess';
    const CR_ACTION_RUN_LOAD_PAGE               = 'loadPage';
    const CR_ACTION_RUN_LOAD_TO_LINK            = 'loadToLink';
    const CR_ACTION_RUN_LOAD_LOGO               = 'loadLogo';

    // Content reload Actions after
    const CR_ACTION_AFTER_SHOW_LEFT_MENU                  = 'actionShowLeftMenu';
    const CR_ACTION_AFTER_HIDE_LEFT_MENU                  = 'actionHideLeftMenu';
    const CR_ACTION_AFTER_SHOW_EDIT_VIEW                  = 'actionShowEditView';
    const CR_ACTION_AFTER_SHOW_PROCESS_BPM_OPERATION      = 'actionShowProcessBpmOperation';
    const CR_ACTION_AFTER_SWITCH_MENU                     = 'actionSwitchMenu';
    const CR_ACTION_AFTER_SWITCH_MENU_BY_COPY_ID          = 'actionSwitchMenuByCopyId';
    const CR_ACTION_AFTER_DEACTIVE_ELEMENTS_MENU          = 'actionDeactiveElementsMenu';
    const CR_ACTION_AFTER_SHOW_COMMUNICATION_CONFIG_POPUP = 'actionShowCommunicationConfigPopup';

    //const CR_ACTION_AFTER_SET_VARS_TGC                = 'actionSetVarsToGeneralContent';




    private static $_last_key = 0;
    private static $_content_vars = array();

    private $_active_object = null;
    private $_extension_copy = null;

    private $_use_auto_pci_pdi = true; // использовать паратры pci && pdi автоматически с get запроса


    private $_key;


    /**
     * Параметры по умолчанию.
     * В закоментированной строке неабязательные параметры, и могут дополнительно динамичски добавлятся
     */
    private $_vars = array(
        'action_run' => null,
        'selector_content_box' => '#content_container',
        /*
         Список динамических параметров:
        'action_after' => null,
        'content_blocks' => array(),
        'url' => null,
        'check_expediency_switch' => 'false',
        'index' => null,
        // если ссылка на модуль
        'module' => array(
            'copy_id' => null,
            'data_id' => null,
            'this_template' => null,
            'destination' => null,
            'parent_module' => array(
                'pci' => null,
                'pdi' => null,
            ),
        ),
        */
    );


    public function __construct($vars_group_code = null, array $properties = null){
        if($properties){
            foreach($properties as $p_name => $p_value){
                if(property_exists($this, $p_name)){
                    $this->{$p_name} = $p_value;
                }
            }
        }

        $this->prepareKey();
        $this->setDefaultContentVars($vars_group_code);
    }


    private function prepareKey(){
        self::$_last_key++;

        $mt = microtime(true);
        if($mt){
             $mt = explode('.', $mt);
            if(!empty($mt[1])){
                $mt = $mt[1];
            } else {
                $mt = '';
            }
        } else {
            $mt = '';
        }

        $this->_key = (integer)date('His') . $mt . self::$_last_key;

        return $this;
    }


    public function setKey($key){
        self::$_last_key = $key;
        $this->_key = $key;
        return $this;
    }


    public function getKey(){
        return $this->_key;
    }


    public function setActiveObject($active_object){
        $this->_active_object = $active_object;
        return $this;
    }


    public function setExtensionCopy($extension_copy){
        $this->_extension_copy = $extension_copy;
        return $this;
    }


    public function addVars(array $vars, $clear = false){
        if($clear){
            $this->_vars = $vars;
        } else {
            $this->_vars = \Helper::arrayMerge($this->_vars, $vars);
        }

        return $this;
    }

    public function addActionAfter(array $vars, $clear = false)
    {
        if($clear){
            $this->_vars['action_after'] = $vars;
        } else {
            foreach ($vars as $key=>$value)
            {
                $this->_vars['action_after'][]=$value;
            }
        }

        return $this;
    }


    public function setVarUrl($url){
        $this->_vars['url'] = $url;
        return $this;
    }


    public function setVarSelectorContentBox($selector_content_box){
        $this->_vars['selector_content_box'] = $selector_content_box;
        return $this;
    }


    public function setVarContentBlocks($blocks){
        $this->_vars['content_blocks'] = $blocks;
        return $this;
    }


    public function prepare(){
        self::$_content_vars[$this->_key] = $this->_vars;
        return $this;
    }




    /**
     * getObjectVars - возвращает параметры ссылки-модуля
     *
     * return array(
            'module => array(
                'copy_id' => '',
                'params' => array(
                    'this_template' => '',
                    'pci' => '',
                    'pdi' => '',
                )
            )
     * )
     */
    private function getObjectVars(){
        $class = null;

        if($this->_active_object) {
            $class = get_class($this->_active_object);
        }

        $result = array();

        switch($class){
            case 'ListViewController' :
                $result = array(
                    'destination' => 'listView',
                    'params' => array(
                        'this_template' => 0,
                    ),
                );
                break;
            case 'ProcessViewController' :
                $result = array(
                    'destination' => 'processView',
                    'params' => array(
                        'this_template' => 0,
                    ),
                );
                break;
            case 'ReportsController' :
                $result = array(
                    'destination' => '',
                    'params' => array(
                        'this_template' => 0,
                    ),
                );
                break;
        }

        // if Module (ListViewController, ProcessViewController)
        if($this->_extension_copy){
            //copy_id
            if($this->_extension_copy){
                $result['copy_id'] = $this->_extension_copy->copy_id;
            }
            //this_template
            if($this->_active_object && property_exists($this->_active_object, 'this_template')){
                $result['params']['this_template'] = (int)$this->_active_object->this_template;
            }


            if($this->_use_auto_pci_pdi){
                //pci
                if($pci = \Yii::app()->request->getParam('pci')){
                    $result['params']['pci'] = $pci;
                }
                //pdi
                if($pdi = \Yii::app()->request->getParam('pdi')){
                    $result['params']['pdi'] = $pdi;
                }
            }
        }

        return $result;
    }





    /**
     * prepareModuleList - подготовка данных о модуле. Используется при формировании общей ссылки при при перегрузке страницы listView || processView || calendarView
     * @param bool $all_page - true если весь блок, false- только данные грида
     * @return $this
     */
    public function prepareModuleList($all_page = true){
        $class_name = null;

        if($this->_active_object) {
            $class_name = get_class($this->_active_object);
        }

        if($all_page){
            if($this->_extension_copy){
                if(in_array($this->_extension_copy->copy_id, array(ExtensionCopyModel::MODULE_ROLES, ExtensionCopyModel::MODULE_USERS, ExtensionCopyModel::MODULE_PERMISSION))){
                    $this->setDefaultContentVars(3);
                } else {
                    $this->setDefaultContentVars(6);
                }
            } else {
                $this->setDefaultContentVars(9);
            }
        } else {
            if($this->_extension_copy){
                $this->setDefaultContentVars(8);
            } else {
                $this->setDefaultContentVars(9);
            }
        }

        $this->addVars(array('action_after' => null));

        switch($class_name){
            case 'ListViewController' :
            case 'ReportsController' :
                if($all_page == false){
                    $vars = array(
                        'selector_content_box' => '#list-table_wrapper_all',
                        'content_blocks' => array(\ControllerModel::CONTENT_BLOCK_2, \ControllerModel::CONTENT_BLOCK_3),
                    );
                    $this->addVars($vars);
                }
                break;
            case 'ProcessViewController' :
                if($all_page == false){
                    $vars = array(
                        'selector_content_box' => '#process_wrapper',
                        'content_blocks' => array(\ControllerModel::CONTENT_BLOCK_2, \ControllerModel::CONTENT_BLOCK_3),
                    );
                    $this->addVars($vars);
                }
                break;

            case 'ConstructorController' :
                if($all_page == false){
                    $vars = array(
                        'selector_content_box' => '#list-table_wrapper_all',
                        'content_blocks' => array(\ControllerModel::CONTENT_BLOCK_2, \ControllerModel::CONTENT_BLOCK_3),
                    );
                    $this->addVars($vars);
                }
                break;
        }

        $this->prepare();

        return $this;
    }




    public static function getContentVars($return_json = true, $clear = true, $key = null){
        $data = self::$_content_vars;

        if(empty($data)) return;

        if($key){
            $data[$key] = self::$_content_vars[$key];
            unset(self::$_content_vars[$key]);
        }

        if($clear){
            self::$_content_vars = array();
        }

        if($return_json){
            return json_encode($data);
        } else {
            return $data;
        }
    }


    /**
     * setDefaultContentVars - установка параметров по умолчанию
     * @param $vars_group_code
     */
    private function setDefaultContentVars($vars_group_code, $method_params = null){
        $method_name = 'setVars' . $vars_group_code;

        if(method_exists($this, $method_name) == false) return;

        $this->{$method_name}($method_params);
    }


    /**
     * 1 . простые ссылки в меню Параметры + история пользователя
     */
    private function setVars1(){
        $vars = array(
            'action_run' => self::CR_ACTION_RUN_LOAD_PAGE,
            'action_after' => [self::CR_ACTION_AFTER_SWITCH_MENU],
        );

        $this->addVars($vars);
    }


    /**
     * 2. просты ссылки и меню Параметры
     */
    private function setVars2(){
        $vars = array(
            'action_run' => self::CR_ACTION_RUN_LOAD_TO_LINK,
            'action_after' => [self::CR_ACTION_AFTER_SWITCH_MENU],
        );

        $this->addVars($vars);
    }


    /**
     * 3. Модули в меню параметры
     */
    private function setVars3(){
        $vars = array(
            'action_run' => self::CR_ACTION_RUN_LOAD_MODULE,
            'module' => $this->getObjectVars(),
        );

        if(empty($vars['module']['params']['pci']) && empty($vars['module']['params']['pdi'])){
            $vars['action_after'][] =  self::CR_ACTION_AFTER_SWITCH_MENU;
        }

        $this->addVars($vars);
    }


    /**
     * 4. простые ссилки в меню польователя
     */
    private function setVars4(){
        $vars = array(
            'action_run' => self::CR_ACTION_RUN_LOAD_TO_LINK,
            'action_after' => [self::CR_ACTION_AFTER_DEACTIVE_ELEMENTS_MENU, self::CR_ACTION_AFTER_HIDE_LEFT_MENU],
        );

        $this->addVars($vars);
    }


    /**
     * 5. простые ссылки в меню пользователя, что есть в мень Параметры
     */
    private function setVars5(){
        $vars = array(
            'action_run' => self::CR_ACTION_RUN_LOAD_PAGE,
            'action_after' => [self::CR_ACTION_AFTER_SWITCH_MENU, self::CR_ACTION_AFTER_SHOW_LEFT_MENU],
        );

        $this->addVars($vars);
    }


    /**
     * 6. Модули из верхнего меню модулей
     */
    private function setVars6(){
        $vars = array(
            'action_run' => self::CR_ACTION_RUN_LOAD_MODULE,
            'action_after' => [self::CR_ACTION_AFTER_HIDE_LEFT_MENU],
            'module' => $this->getObjectVars(),
        );

        if(empty($vars['module']['params']['pci']) && empty($vars['module']['params']['pdi'])){
            $vars['action_after'][] =  self::CR_ACTION_AFTER_SWITCH_MENU;
        }

        $this->addVars($vars);
    }




    /**
     * 7. Голотип
     */
    private function setVars7(){
        $vars = array(
            'action_run' => self::CR_ACTION_RUN_LOAD_LOGO,
        );

        $this->addVars($vars);
    }





    /**
     * 8. Модули. Сортировка, пагинация
     */
    private function setVars8(){
        $vars = array(
            'action_run' => self::CR_ACTION_RUN_LOAD_MODULE,
            'module' => $this->getObjectVars(),
        );

        $this->addVars($vars);
    }




    /**
     * 9. Конструктор модулей
     */
    private function setVars9(){
        $vars = array(
            'action_run' => self::CR_ACTION_RUN_LOAD_PAGE,
            'index' => 'constructor',
        );

        $this->addVars($vars);
    }






    /**
     * gettContentHtmlDifferenBlocks - возвращается дополнительный контент по доп. запросу из JS
     */
    public function gettContentHtmlDifferenBlocks($block_list){
        if($block_list == false) return;
        $block_list = (array)$block_list;

        $result = array();

        foreach($block_list as $block){
            switch($block['name']){
                case ControllerModel::CONTENT_BLOCK_DIFFERENT_MAIN_TOP_USER_MENU:
                    $result[$block['name']] = $this->getHtmlMainTopUserMenu();
                    break;
                case ControllerModel::CONTENT_BLOCK_DIFFERENT_MAIN_TOP_MODULE_MENU:
                    $result[$block['name']] = $this->getHtmlMainTopModuleMenu();
                    break;
                case ControllerModel::CONTENT_BLOCK_DIFFERENT_MAIN_LEFT_MODULE_MENU:
                    $result[$block['name']] = $this->getHtmlMainLeftModuleMenu();
                    break;
            }
        }

        return $result;
    }


    /**
     * getHtmlMainTopProfileMenuUser - возвращает блок активного пользователя в главном меню
     */
    private function getHtmlMainTopUserMenu(){
        return Yii::app()->controller->renderPartial('//blocks/main-top-profile-menu', array('this' => Yii::app()->controller), true);
    }




    /**
     * getHtmlMainTopModuleMenu - возвращает блок списка модулей из главном меню
     */
    private function getHtmlMainTopModuleMenu(){
        return Yii::app()->controller->renderPartial('//blocks/main-top-module-menu', array('this' => Yii::app()->controller), true);
    }





    /**
     * getHtmlMainLeftModuleMenu - возвращает блок административного левого
     */
    private function getHtmlMainLeftModuleMenu(){
        return Yii::app()->controller->renderPartial('//blocks/main-left-module-menu', array('this' => Yii::app()->controller), true);
    }





}
