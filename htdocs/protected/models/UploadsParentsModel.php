<?php


class UploadsParentsModel extends ActiveRecord
{

      
	public static function model($className=__CLASS__){
		return parent::model($className);
	}

	public function tableName(){
		return '{{uploads_parents}}';
	}

	public function rules(){
        return array(
            array('upload_id, parent_upload_id', 'required'),
			array('upload_id, parent_upload_id', 'numerical', 'integerOnly'=>true),
            array('id, upload_id, parent_upload_id', 'safe', 'on'=>'search'),
        );
    }


	public function relations(){
		return array(
		);
	}


    


}

