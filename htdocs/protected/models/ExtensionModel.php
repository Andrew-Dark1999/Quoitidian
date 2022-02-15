<?php


 
class ExtensionModel extends ActiveRecord
{
    const MODULE_BASE        = 1;
    const MODULE_USERS       = 2;
    const MODULE_USERS_GROUP = 3;
    const MODULE_PERMISSION  = 4;
    const MODULE_ROLES       = 5;
    const MODULE_STAFF       = 6;
    const MODULE_PARTICIPANT = 7;
    const MODULE_TASKS       = 8;
    const MODULE_REPORTS     = 9;
	const MODULE_PROCESS     = 10;
	const MODULE_PROJECTS	 = 11;
	const MODULE_DOCUMENTS   = 12;
	const MODULE_NOTIFICATION = 13;
	const MODULE_COMMUNICATIONS = 14;
    const MODULE_CALLS          = 15;
    const MODULE_WEBHOOK        = 16;
    
    
    public $tableName = 'extension';
    
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    
    
	public function rules()
	{
		return array(
			array('type, name, version, source, active', 'required'),
			array('type', 'length', 'max'=>6),
			array('name', 'length', 'max'=>100),
			array('version', 'length', 'max'=>10),
			array('source', 'length', 'max'=>7),
			array('active', 'length', 'max'=>1),
			array('date_create', 'safe'),
			array('extension_id, type, date_create, name, version, source, active', 'safe', 'on'=>'search'),
		);
	}

	public function relations()
	{
    	return array(
            'extensionCopy' => array(self::HAS_MANY, 'ExtensionCopyModel', 'extension_id'),
		);
	}

    public function scopes(){
        return array(
            "modules" => array(
                "condition" => "type = 'module'",
            ),
            "modulesActive" => array(
                "condition" => "type = 'module' AND active =  '1'",
            ),
        );
    }


	public function attributeLabels()
	{
		return array(
			'extension_id' => 'Extension',
			'type' => Yii::t('base', 'Type'),
			'date_create' => 'Date Create',
			'name' => Yii::t('base', 'Name'),
			'version' => Yii::t('base', 'Version'),
			'description' => Yii::t('base', 'Description'),
			'source' => Yii::t('base', 'Source'),
			'active' => Yii::t('base', 'Active'),
		);
	}

	public function search()
	{
		$criteria=new CDbCriteria;
		$criteria->compare('extension_id',$this->extension_id);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('date_create',$this->date_create,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('version',$this->version,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('source',$this->source,true);
		$criteria->compare('active',$this->active,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
    
    
    /**
    * Регистрирует и возвращает класс модуля
    * @return class
    */
    public function getModule($extension_copy = null){
        Extension::getInstance()->registerModules($this);
        $module = Yii::app()->getModule($this->name);
        $module->extensionCopy = $extension_copy;
        return $module;
    }
    

    /**
     * Возвращает автивный класс модуля
     */
    public function getActiveModule(){
        $module = Yii::app()->getModule($this->name);
        if(empty($module)){
            Extension::getInstance()->registerModules($this);
            $module = Yii::app()->getModule($this->name);
        }
        return $module;
    }


    /**
     * устанавливает условие названия модуля для виборки из Бд
     */ 
    public function scopeModuleName($module_name){
        $this->getDbCriteria()->mergeWith(array(
            'condition' => 'name=:name',
            'params' => array(':name' => $module_name),
        ));
        return $this;
    }
        
    

}
