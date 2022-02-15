<?php

/**
 * StaffModel
 * 
 * @author Alex R.
 * @copyright 2014
 */
class StaffModel extends ActiveRecord
{
    public $tableName = 'users';
    
	public static function model($className=__CLASS__){
		return parent::model($className);
	}
    
	public function rules()
	{
		return array(
			array('users_id,date_create,date_edit,user_create,user_edit,import_status,ehc_image1,phone,mobile,skype,sur_name,first_name,father_name,email,active,leader', 'safe'),
		);
	}


	public function relations(){
    	return array();
	}


    public function scopes(){
        return array();
    }


    /**
     * исключает ИД из запроса
     */
    public function scopeExtensionID($exception_id){
        if(empty($exception_id)) return $this;
        $criteria = new CDBCriteria();
        $criteria->addNotInCondition('users_id', $exception_id);
        $this->getDbCriteria()->mergeWith($criteria);

        return $this;
    }
    
    /**
     * условие активности записи
     */
    public function scopeActive($active = "1"){
        $this->getDbCriteria()->mergeWith(array(
            'condition' => 'active =:active',
            'params' => array(':active' => $active),
        ));
        
        return $this;
    }



    public function getFullName($add_father_name = false){
        if($add_father_name)
            $full_name = implode(' ', array($this->sur_name, $this->first_name, $this->father_name));
        else
            $full_name = implode(' ', array($this->sur_name, $this->first_name));

        return $full_name;
    }



    public function getAvatar($thumb_size = 32, $attr = array('class' => 'list-view-avatar')){
        return Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.ListView.Elements.Avatar.Avatar'),
                   array(
                    'data_array' => $this->getAttributes(),
                    'thumb_size' => $thumb_size,
                    'attr' => $attr,
                   ),
                   true);
    }


    /**
     * Возвращает инициалы пользщователя
     */
    public function getInitials(){
        return ($this->sur_name ? mb_substr($this->sur_name, 0, 1) : '') . ($this->first_name ? mb_substr($this->first_name, 0, 1) : '');
    }



}
