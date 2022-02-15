<?php
/**
* FieldTypes widget  
* @author Alex R.
* @version 1.0
*/ 

class FieldTypes{

    const TYPE_SIZE_VARCHAR     = 255;
    const TYPE_SIZE_TEXT        = 65535;
    const TYPE_SIZE_MEDIUMTEXT  = 16777215;



    private $_types = array(
        'integer' => array(
                'type_db' => 'integer',
                'size' => 11,
        ),
        'float' => array(
                'type_db' => 'float',
                'size' => 11,
                'decimal' => 2,
        ),
        'decimal' => array(
                'type_db' => 'decimal',
                'size' => 16,
                'decimal' => 5,
        ),
        'string' => array(
                'type_db' => 'string',
                'maxLength' => self::TYPE_SIZE_VARCHAR,
                'size' => self::TYPE_SIZE_VARCHAR,
                'default_value' => '',
        ),
        'text' => array(
                'type_db' => 'text',
        ),
        'datetime' => array(
                'type_db' => 'datestamp',
        ),
        'enum' => array(
                'type_db' => 'enum',
                'values' => array('1', '0'),
        ),
    );
    


    public static function getInstance(){
        return new self;
    }

    /**
    * Возвращает параметры типа поля
    * @param $type string
    * @return array
    */
    public function getType($type = null){
        if($type === null) return $this->_types;
        return $this->_types[$type]; 
    }




    /**
    * Возвращает SQL строку для создания поля типа TinyInt
    * @return string 
    */
    public function getSqlCreateColumnTinyInt($params = array()){
        return "int(3) DEFAULT " . (isset($params['default']) ? $params['default'] : 'NULL');
    }

    /**
     * Возвращает SQL строку для создания поля типа BigInt
     * @return string
     */
    public function getSqlCreateColumnBigInt($params = array()){
        return "bigint(20) DEFAULT " . (isset($params['default']) ? $params['default'] : 'NULL');
    }

    /**
    * Возвращает SQL строку для создания поля типа integer
    * @param $params array
    * @return string 
    */
    public function getSqlCreateColumnInteger($params = array()){
        if(isset($params['pk']) && $params['pk'] == true)
            return "int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY";
        else
        return "int(" . (isset($params['size']) && $params['size'] ? $params['size'] : 11)  . ") DEFAULT " . (isset($params['default']) ? $params['default'] : 'NULL');
    }

    /**
    * Возвращает SQL строку для создания поля типа float
    * @param $params array
    * @return string 
    */
    public function getSqlCreateColumnFloat($params = array()){
        return "float(" . (isset($params['size']) && $params['size'] ? $params['size'] : 11)  . ", ". (isset($params['decimal']) && $params['decimal'] ? $params['decimal'] : 2) .") DEFAULT " . (isset($params['default']) ? $params['default'] : 'NULL');
    }

    /**
    * Возвращает SQL строку для создания поля типа decimal
    * @param $params array
    * @return string 
    */
    public function getSqlCreateColumnDecimal($params = array()){
        return "decimal(" . (isset($params['size']) && $params['size'] ? $params['size'] : 11) . ", ". (isset($params['decimal']) && $params['decimal'] ? $params['decimal'] : 2) .") DEFAULT " . (isset($params['default']) ? $params['default'] : 'NULL');
    }

    /**
    * Возвращает SQL строку для создания поля типа string
    * @param $params array
    * @return string 
    */
    public function getSqlCreateColumnString($params = array()){
        if(isset($params['size'])){
            switch($params['size']){
                case self::TYPE_SIZE_TEXT :
                    return $this->getSqlCreateColumnText($params);
                case self::TYPE_SIZE_MEDIUMTEXT :
                    return $this->getSqlCreateColumnMediumText($params);
            }
        }

        return "varchar(" . (isset($params['size']) && $params['size'] ? $params['size'] : self::TYPE_SIZE_VARCHAR) . ") DEFAULT " . (isset($params['default']) ? $params['default'] : 'NULL');
    }
    
    /**
    * Возвращает SQL строку для создания поля типа text
    * @param $params array
    * @return string 
    */
    public function getSqlCreateColumnText($params = array()){
        return "text DEFAULT " . (isset($params['default']) ? $params['default'] : 'NULL');
    }

    /**
     * Возвращает SQL строку для создания поля типа mudiumtext
     * @param $params array
     * @return string
     */
    public function getSqlCreateColumnMediumText($params = array()){
        return "mudiumtext DEFAULT " . (isset($params['default']) ? $params['default'] : 'NULL');
    }

    /**
    * Возвращает SQL строку для создания поля типа datetime
    * @param $params array
    * @return string 
    */
    public function getSqlCreateColumnDateStamp($params = array()){
        return "datetime DEFAULT " . (isset($params['default']) ? $params['default'] : 'NULL');
    }

    /**
    * Возвращает SQL строку для создания поля типа enum
    * @param $params array  - список параметров enum массива
    * @return string 
    */
    public function getSqlCreateColumnEnum($params = array()){
        $list = '';
        if(!isset($params['values'])) return;
        $params['values'] = Fields::getInstance()->UnGroupFieldIfTitleSimilar($params['values']);
        foreach($params['values'] as $value) 
            $list.= "'" . $value . "',";
        $list = substr($list, 0, -1);
        return "enum (" . $list . ") DEFAULT " . (isset($params['default']) ? $params['default'] : 'NULL');
               
    }


  

}    
