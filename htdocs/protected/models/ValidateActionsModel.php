<?php


class ValidateActionsModel extends ActiveRecord{

	const KEY_CONSTRUCTOR_MODULE_SAVE	= 'c_module_save';
	const KEY_CONSTRUCTOR_MODULE_DELETE = 'c_module_delete';


	public static function model($className=__CLASS__){
		return parent::model($className);
	}

	public function tableName(){
		return '{{validate_actions}}';
	}


	public function rules(){
		return array(
			array('key_f', 'length', 'max'=>15),
			array('params', 'length', 'max'=>1000),
			array('id, key_f, params', 'safe'),
		);
	}



	public static function getParams($key){
		$criteria = new CDbCriteria();
		if(is_array($key)){
			$criteria->addInCondition('key_f', $key);
		} else {
			$criteria->addCondition('key_f = "' . $key . '"');
		}
		$data = static::model()->findAll($criteria);


		$params = array();
		if(!empty($data)){
			foreach($data as $row){
				$params[] = json_decode($row['params'], true);
			}
		}
		return $params;
	}

}
