<?php


class ValidateModulesModel extends ActiveRecord{


	public static function model($className=__CLASS__){
		return parent::model($className);
	}

	public function tableName(){
		return '{{validate_modules}}';
	}


	public function rules(){
		return array(
			array('params', 'length', 'max'=>1000),
			array('id, params', 'safe'),
		);
	}


	public static function getParams(){
		$data = static::model()->findAll();

		$params = array();
		if(!empty($data)){
			foreach($data as $row){
				$params[] = json_decode($row['params'], true);
			}
		}

		return $params;
	}



}
