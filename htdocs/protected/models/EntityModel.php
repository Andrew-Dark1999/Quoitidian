<?php
/**
 * EntityModel -        обработчик сущности (элемента).
 *                      Собирает информацию о сущности в статическое свойство.
 *                      В последствии все параметры перегружаются в JS объект Entity и инициализируються
 * @author Alex R
 */

class EntityModel{


    private static $_last_key = 0;
    private static $_last_parent_key;


    private static $_entity_properties = array();


    private $_key;
    private $_parent_key;
    private $_parent_event_id;
    private $_element_type;

    // при true сущность может использоваться с отлеженой инициализацией (пример - InlineEdit)
    private $_entity_for_vars = false;


    // entity properties
    private $_vars = array();
    private $_events = array();
    private $_callbacks = array();




    public function __construct($prepare_key = false, $set_parent_key_from_last = false){
        if($prepare_key){
            $this->prepareKey();
        }

        if($set_parent_key_from_last){
            $this->_parent_key = self::$_last_parent_key;
        }
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



    /*
    public function setKey($key){
        self::$_last_key = $key;
        $this->_key = $key;
        return $this;
    }
    */



    public function getKey(){
        return $this->_key;
    }




    public function setParentKey($key){
        $this->_parent_key = $key;

        return $this;
    }


    public function setParentEventId($event_id){
        $this->_parent_event_id = $event_id;
        return $this;
    }


    public function getParentEventId(){
        return $this->_parent_event_id;
    }


    public function setLastParentKey($key){
        self::$_last_parent_key = $key;

        return $this;
    }


    public function setElementType($element_type){
        $this->_element_type = $element_type;

        return $this;
    }



    private function getPreparedSelector($selector = null, $add_key = true){
        if($selector === null){
            return '.element[data-entity_key="' . $this->getKey() . '"]';
        }

        if($add_key){
            return '.element[data-entity_key="' . $this->getKey() . '"] ' . $selector;
        } else {
            return $selector;
        }
    }


    /**
     * setEntityForVars
     */
    public function setEntityForVars($entity_for_vars){
        $this->_entity_for_vars = $entity_for_vars;
    }

    /**
     * setVars
     * @param array $vars
     * @return $this
     */
    public function setVars(array $vars){
        $this->_vars = $vars;

        return $this;
    }


    /**
     * addVars
     */
    public function addVars(array $vars){
        $this->setVars(\Helper::arrayMerge($this->_vars, $vars));

        return $this;
    }




    /**
     * setEvents
     */
    public function setEvents(array $events){
        if($events == false){
            return $this;
        }

        foreach($events as $event){
            $this->addEvent(...$event);
        }

        return $this;
    }


    /**
     * addEvent
     */
    public function addEvent($selector, $event, $function_name, $vars = null, $selector_add_key = true){
        $event = [
            'selector' => $this->getPreparedSelector($selector, $selector_add_key),
            'event' => $event,
            'func' => $function_name,
        ];

        if($vars){
            $event['vars'] = $vars;
        }

        $this->_events[] = $event;

        return $this;
    }




    /**
     * setCallbacks
     */
    public function setCallbacks(array $callbacks){
        if($callbacks == false){
            return $this;
        }

        foreach($callbacks as $callback){
            $this->addCallback(...$callback);
        }

        return $this;
    }



    /**
     * addCallback
     */
    public function addCallback($name, $function_name, $vars = null){
        $callback = [
            'name' => $name,
            'func' => $function_name,
        ];

        if($vars){
            $callback['vars'] = $vars;
        }

        $this->_callbacks[] = $callback;

        return $this;
    }



    /**
     * resetToEntityProperties
     * @param string $position - before|after or null
     * @return $this
     */
    public function resetToEntityProperties(){
        $entity = [
            'key' => $this->_key,
            'parent_key' => $this->_parent_key,
        ];

        if($this->_entity_for_vars){
            $entity['entity_for_vars'] = $this->_entity_for_vars;
        }


        $entity_properties = [];

        if($this->_vars){
            $entity_properties['vars'] = $this->_vars;
            $entity_properties['vars']['element_type'] = $this->_element_type;
            $this->_vars = [];
        }
        if($this->_events){
            $entity_properties['events'] = $this->_events;
            $this->_events = [];
        }
        if($this->_callbacks){
            $entity_properties['callbacks'] = $this->_callbacks;
            $this->_callbacks = [];
        }

        if($entity_properties == false){
            return $this;
        }

        $entity['properties'] = $entity_properties;

        self::$_entity_properties[] = $entity;

        return $this;
    }


    /**
     * getEntityPropertiesSort - сортировка списка по ключах key && parent_key
     */
    public static function getEntityPropertiesSort(){
        $parent_key_list = [];

        // сортировка parent_key = null
        $sorting_function1 = function($a, $b) use (&$parent_key_list){
            if($a['parent_key'] == false){
                $parent_key_list[] = $a['key'];
                return -1;
            }

            if(in_array($a['parent_key'], $parent_key_list)){
                $parent_key_list[] = $a['key'];
                return 1;
            } else {
                $parent_key_list[] = $a['key'];
                return 1;
            }
        };
        // сортировка parent_key != null
        $sorting_function2 = function($a, $b) use (&$parent_key_list){
            if($a['parent_key'] == false){
                $parent_key_list[] = $a['key'];
                return 0;
            }

            if(in_array($a['parent_key'], $parent_key_list)){
                $parent_key_list[] = $a['key'];
                return 0;
            } else {
                $parent_key_list[] = $a['key'];
                return 1;
            }
        };

        usort(self::$_entity_properties, $sorting_function1);
        $parent_key_list = [];
        usort(self::$_entity_properties, $sorting_function2);
    }



    /**
     * getEntityProperties
     */
    public static function getEntityProperties($clear = true, $sort = true){
        if($sort){
            self::getEntityPropertiesSort();
        }

        $p = self::$_entity_properties;

        if($clear){
            self::$_entity_properties = [];
        }

        return $p;
    }



    /**
     * getEntityChildrenProperties
     */
    public function getEntityChildrenProperties($clear = true, $only_entity_for_vars = true){
        $p = array();

        if(self::isSetProperties() == false){
            return $p;
        }

        foreach(self::$_entity_properties as $index => $entity_property){
            if(array_key_exists('parent_key', $entity_property) && $entity_property['parent_key'] == $this->_key){
                if($only_entity_for_vars && array_key_exists('entity_for_vars', $entity_property) && $entity_property['entity_for_vars']){
                    $p[] = $entity_property;
                    unset($entity_property['entity_for_vars']);
                    if($clear){
                        unset(self::$_entity_properties[$index]);
                    }
                } else
                if($only_entity_for_vars == false){
                    $p[] = $entity_property;
                }
            }
        }

        return $p;
    }


    /**
     * isSetProperties
     * @return bool
     */
    public static function isSetProperties(){
        $b = empty(self::$_entity_properties);

        return !$b;;
    }






}
