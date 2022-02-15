<?php

class LocaleCRM extends CLocale{
    
    public $_data_p;

    public static function getInstance2(){
        return new static();
    }

    
    public function __construct($id = null){
        if($id === null) $id = Yii::app()->getLocale()->getId();
        $_id = self::getCanonicalID($id);
		$dataPath = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'i18n';
		$dataFile = $dataPath.DIRECTORY_SEPARATOR.$_id.'.php';
		if(is_file($dataFile))
			$this->_data_p = require($dataFile);
		else
			throw new CException(Yii::t('yii','Unrecognized locale "{locale}".',array('{locale}'=>$id)));

        parent::__construct($id);
    }
    
    
    
    
    
    /**
    * @return array (All $_data)
    */
    public function getAllData(){
        return array(
                'language' => Yii::app()->getLanguage(),
                'numberSymbols' => $this->_data_p['numberSymbols'],
                'currencySymbols' => $this->_data_p['currencySymbols'],
                'monthNames' => $this->_data_p['monthNames'],
                'monthNamesSA' => $this->_data_p['monthNamesSA'],
                'weekDayNames' => $this->_data_p['weekDayNames'],
                'weekDayNamesSA' => $this->_data_p['weekDayNamesSA'],
                'dateTimeFormats' => $this->_data_p['dateTimeFormats'],
                'dateFormats' => $this->_data_p['dateFormats'],
                'timeFormats' => $this->_data_p['timeFormats'],
        );
    }
    
} 
