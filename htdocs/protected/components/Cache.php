<?php


class Cache{
    
    const CACHE_TYPE_DB     = 'db';
    const CACHE_TYPE_PAGE   = 'page';
    

    public static function enabled($cache_type){
        if(isset(Yii::app()->params['cache'][$cache_type]) && Yii::app()->params['cache'][$cache_type]['enabled'] == true){
            return true;
        }
        return false;
    }
    
    
    
    public static function getParam($cache_type, $param_name, $return_if_not = null){
        if(isset(Yii::app()->params['cache'][$cache_type][$param_name])) 
            return Yii::app()->params['cache'][$cache_type][$param_name];
        else 
            return $return_if_not;
    }
    
    
    
    public static function flush($cache_type){
        if(!is_array($cache_type)) $cache_type = array($cache_type);
        
        foreach($cache_type as $ct){
            if(self::enabled($ct)){
                Yii::app()->cache->flush();
                break;
            }            
        }
    }
    
    
} 