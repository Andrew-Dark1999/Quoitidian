<?php

/**
 * StaffModel
 * 
 * @author Alex R.
 * @copyright 2014
 */
class WebhookModel extends ActiveRecord
{
    public $tableName = 'webhooks';
    
	public static function model($className=__CLASS__){
		return parent::model($className);
	}
    
	public function rules()
	{
		return array(
			array('', 'safe'),
		);
	}


	public function relations(){
    	return array();
	}


    public function scopes(){
        return array();
    }
}
