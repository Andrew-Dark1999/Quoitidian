<?php
/**
 * ParticipantConstModel - предопеределенные константы
 */

class ParticipantConstModel{

    // type constans
    const TC_RELATE_RESPONSIBLE         = 1;
    const TC_RESPONSIBLE_FOR_PROCESS    = 2;


    private $_type_const_list = [];




    public function setTypeConstList($list){
        $this->_type_const_list = $list;
        return $this;
    }


    public static function getTypeConstListFull(){
        return [
            self::TC_RELATE_RESPONSIBLE,
            //self::TC_RESPONSIBLE_FOR_PROCESS,
        ];
    }


    private function hasTypeConst($type_const){
        $type_const_list = $this->_type_const_list;
        if($type_const_list == false){
            return false;
        }

        return in_array($type_const, $type_const_list);
    }


    public function isConstValue($type_const){
        if($type_const == false){
            return false;
        }

        $type_const_list = $this->getTypeConstListFull();

        return (in_array($type_const, $type_const_list));
    }


    public function getTypeConstTitle($type_const){
        switch($type_const){
            case self::TC_RELATE_RESPONSIBLE:
                return \Yii::t('base', 'Related responsible');
            case self::TC_RESPONSIBLE_FOR_PROCESS:
                return \Yii::t('base', 'Responsible for the process');
        }
    }



    public function getTypeConstTitleListFull(){
        $type_const_list = $this->_type_const_list;
        if($type_const_list == false){
            return;
        }

        $result = array();
        foreach($type_const_list as $type_const){
            $type_const_title = $this->getConstTypeTitleByTC($type_const);
            if($type_const_title == false){
                continue;
            }
            $result[] = $type_const_title;
        }

        return $result;
    }


    public function getConstTypeTitleByTC($type_const){
        return [
            'ug_id' => $type_const,
            'ug_type' => \ParticipantModel::PARTICIPANT_UG_TYPE_CONST,
            'ehc_image1' => $this->getImageSrc(),
            'title' => $this->getTypeConstTitle($type_const),
            'order_index' => 0,
        ];
    }



    public function getImageSrc(){
        return 'static/images/user_const.png';
    }


    /**
     * getProcessFlagByConstType
     */
    public function getProcessFlagByConstType($const_type){
        switch($const_type){
            case self::TC_RELATE_RESPONSIBLE:
                return ParticipantFlagsModel::FLAG_CONST_RELATE_RESPONSIBLE;
            case self::TC_RESPONSIBLE_FOR_PROCESS:
                return ParticipantFlagsModel::FLAG_CONST_RESPONSIBLE_FOR_PROCESS;
        }
    }



}
