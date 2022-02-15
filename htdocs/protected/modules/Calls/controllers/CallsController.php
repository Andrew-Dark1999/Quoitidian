<?php


class CallsController extends Controller{

    /*














Left Menu

    Неопределено
    +38 067 955 0336
    <название Контакта>

    Неопределено
    +38 067 955 0336

    По номеру Клиента:
    ТОВ Бобкин
    <название первого Контакта, если есть>


    По номеру Контакта:
    ТОВ Бобкин
    <название Контакта>


    <calls_service_events>
    events_id
    calls_id
    service_name
    service_event_id
    params = []

    <calls_service_events_phones>
    phones_id
    calls_id
    events_id
    phone

     */

    public function filters(){
        return array(
            'checkAccess',
        );
    }


    public function filterCheckAccess($filterChain){
        $filterChain->run();
    }






}
