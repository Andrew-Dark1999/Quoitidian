<?php


interface QuickViewBlockModelInterface {


    public function getName();
    public function getJsClassName();
    public function getTitle();
    public function getItemsModelName();
    public function getBlockGroupName();
    public function getWidgetAlias();
    public function getCopyId();


}
