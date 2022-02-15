<?php

class RegulationModel extends ActiveRecord
{
    const REGULATION_SYSTEM_SETTINGS = 1; 
   
    
    public $tableName = 'regulation';

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}


	public function rules()
	{
		return array(
			array('title', 'length', 'max'=>255),
			array('regulation_id, title', 'safe'),
		);
	}

	public function relations()
	{
		return array(
		);
	}



}
