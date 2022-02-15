<?php

/**
 * ExtensionConstModel
 *
 * @Autor Alex R.
 */
class ExtensionConstModel{

    const E_TYPE_EXTENSION      = 'extension';
    const E_TYPE_EXTENSION_COPY = 'extension_copy';

    public $tableName = 'extension_const';


    private static function getInstance(){
        return new self();
    }


    private function findExtensionId($extension_type, $extension_name){
        $id = \DataModel::getInstance()
                    ->setSelect('extension_id')
                    ->setFrom($this->tableName)
                    ->setWhere('extension_name = "'.$extension_type.'" AND extension_name = "'.$extension_name.'"')
                    ->findScalar();

        if(!empty($id)) return $id;
    }


    /**
     * getExtensionId
     */
    public static function getExtensionId($extension_name){
        return self::getInstance()->findExtensionId(self::E_TYPE_EXTENSION, $extension_name);
    }


    /**
     * getExtensionCopyId
     */
    public static function getExtensionCopyId($extension_name){
        return self::getInstance()->findExtensionId(self::E_TYPE_EXTENSION_COPY, $extension_name);
    }

}
