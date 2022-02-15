<?php
/**
 * ArrowModel widget
 * @author Alex R.
 */

namespace Process\models;

class ArrowModel{

    const TYPE_INNER = 'inner';
    const TYPE_OUTER = 'outer';

    const STATUS_ACTIVE     = 'active';
    const STATUS_UNACTIVE   = 'unactive';

    private static $_instance;

    private static $_refresh_instance = false;

    private $_parent_unique_index_list = [];

    private $_process_id = null;

    private $_process_id_check = true;


    public static function getInstance($new_instance = false){
        if(static::$_instance === null){
            static::$_instance = new static;
            return static::$_instance;
        } else {
            if($new_instance){
                static::$_instance = new static;
            }
            return static::$_instance;
        }
    }



    public static function setRefreshInstace($status = true){
        self::$_refresh_instance = true;
    }


    public function setProcessIdCheck($process_id_check){
        $this->_process_id_check = $process_id_check;
        return $this;
    }


    /**
     * Возвращает unique_index подчиненных операторов
     */
    public function getUniqueIndexParent($unique_index){
        if(self::$_refresh_instance){
           $this->_parent_unique_index_list = [];
        }

        if(!$this->_parent_unique_index_list || $this->isChangedProcessId()){
             $this->setArrowsUniqueIndexList();
        }

        if(!empty($this->_parent_unique_index_list[$unique_index])){
            return $this->_parent_unique_index_list[$unique_index];
        }
    }



    private function isChangedProcessId(){
        if($this->_process_id_check == false) return false;

        if($this->_process_id === null || $this->_process_id != ProcessModel::getInstance()->process_id){
            $this->_process_id = ProcessModel::getInstance()->process_id;
            return true;
        }

        return false;
    }


    /**
     * Установка значений unique_index из arrows
     */
    private function setArrowsUniqueIndexList(){
        $schema = ProcessModel::getInstance()->getSchema();
        $this->findUniqueIndexInArrows($schema);

        return $this;
    }




    /**
     * Поиск unique_index в arrows
     */
    private function findUniqueIndexInArrows($schema){

        if(!is_array($schema)) return $this;

        if(array_key_exists('elements', $schema) && !empty($schema['elements'])){
            $this->findUniqueIndexInArrows($schema['elements']);
        } elseif(array_key_exists('type', $schema) && $schema['type'] == \Process\models\SchemaModel::ELEMENT_TYPE_OPERATION){
            if(empty($schema['arrows'])) return $this;

            foreach($schema['arrows'] as $arrow){
                if(!empty($arrow['unique_index'])){
                    if(array_key_exists($arrow['unique_index'], $this->_parent_unique_index_list) && in_array($schema['unique_index'], $this->_parent_unique_index_list[$arrow['unique_index']])){
                        continue;
                    }
                    $this->_parent_unique_index_list[$arrow['unique_index']][] = $schema['unique_index'];
                }
            }
        } elseif(is_array($schema)){
            foreach($schema as $item){
                $this->findUniqueIndexInArrows($item);
            }
        }

        return $this;

    }



}


