<?php
/**
 * Class MangoOffice - SIP оператор "MangoOffice"
 */


class MangoOffice extends PluginServicePhoneAbstractFactory {


    public function getName(){
        return 'mango_office';
    }


    public  function getTitle(){
        return 'Mango Office';
    }


    public function getParamsModelClassName(){
        return 'MangoOfficeParamsModel';
    }


    public function getUserParamsModelClassName(){
        return 'MangoOfficeUserParamsModel';
    }


    public function getInternalActionsModelClassName(){
        return 'MangoOfficeInternalActions';
    }

    public function getExternalEventsModelClassName(){
        return 'MangoOfficeExternalEvents';
    }


    public function getApiModelClassName(){
        return 'MangoOfficeApi';
    }



}
