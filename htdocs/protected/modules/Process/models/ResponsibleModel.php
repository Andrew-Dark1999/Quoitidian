<?php
/**
 * ResponsibleModel widget
 * @author Alex R.
 */

namespace Process\models;

class ResponsibleModel{


    public static function getElements($delete_line_feeds = true){
        $html = self::getElementHtml();
        if($delete_line_feeds){
            $html = \Helper::deleteLinefeeds($html);
        }

        return $html;
    }



    private static function getElementHtml(){
        return \Yii::app()->controller->widget('\Process\extensions\ElementMaster\BPM\Responsible\Responsible',
            array(),
            true);
    }







}
