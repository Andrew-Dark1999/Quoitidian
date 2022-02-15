<?php

/**
 * ControllerModel -  Класс для управление блоками.
 *                          Используются в отображениях для групировки контента

 * @autor Alex R.
 */


class ControllerModel{

    // content blocks
    const CONTENT_BLOCK_ALL = 'all';
    const CONTENT_BLOCK_0   = 0;
    const CONTENT_BLOCK_1   = 1;
    const CONTENT_BLOCK_2   = 2;
    const CONTENT_BLOCK_3   = 3;
    const CONTENT_BLOCK_4   = 4;
    const CONTENT_BLOCK_5   = 5;


    const CONTENT_BLOCK_DIFFERENT_MAIN_TOP_USER_MENU = 'main_top_user_menu';
    const CONTENT_BLOCK_DIFFERENT_MAIN_TOP_MODULE_MENU = 'main_top_module_menu';
    const CONTENT_BLOCK_DIFFERENT_MAIN_LEFT_MODULE_MENU = 'main_left_module_menu';


    private static $_active_blocks = [self::CONTENT_BLOCK_ALL];




    /**
     * setContentBlocks
     */
    public static function setContentBlocks($blocks, $replace = true){
        if($blocks == false) return;

        if(!is_array($blocks)){
            $blocks = (array)$blocks;
        }

        if($replace){
            self::$_active_blocks = $blocks;
        } else {
            self::$_active_blocks = array_merge(self::$_active_blocks, $blocks);
        }

        self::$_active_blocks = array_unique(self::$_active_blocks);
    }




    /**
     * inContentBlock
     */
     public static function inContentBlock($block_names){
        if(count(self::$_active_blocks) === 1 && in_array(self::CONTENT_BLOCK_ALL, self::$_active_blocks)) return true;
        if(self::$_active_blocks == false) return false;
        
        if(!is_array($block_names)){
            $block_names = (array)$block_names;
        }

        $b = false;
        foreach($block_names as $block_name){
            if(in_array($block_name, self::$_active_blocks)){
                $b = true;
                break;
            }
        }
        
        return $b;
     }




}
