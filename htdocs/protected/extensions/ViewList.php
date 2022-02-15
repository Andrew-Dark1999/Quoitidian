<?php

/**
 * Список отображений и путей к виджетам, что используются в классах системы
 * Перед выводом отображенний в браузер можно переопределить путь к отображению
 */

class ViewList
{

    private static $_views = [
        // general views
        'site/index'                => '//site/index',
        'site/parameters'           => '//site/parameters',
        'site/plugins'              => '//site/plugins',
        'site/mailingServices'      => '//site/mailing-services',
        'site/listView'             => '//site/list-view',
        'site/processView'          => '//site/process-view',
        'site/calendarView'         => '//site/calendar-view',
        'site/calendarViewTemplate' => '//site/calendar-view-template',

        'site/editView'                                                                          => '//site/edit-view',

        //other
        'participant/listView'                                                                   => 'participant/list-view',
        'print/listView'                                                                         => '//print/list-view',

        //dialogs
        'dialogs/editViewAdd'                                                                    => '//dialogs/edit-view-add',
        'dialogs/subModuleAddCards'                                                              => '//dialogs/sub-module-add-cards',
        'dialogs/uploadSelectFile'                                                               => '//dialogs/upload-select-file',
        'dialogs/subModuleSelectGenerate'                                                        => '//dialogs/sub-module-select-generate',
        'dialogs/message'                                                                        => '//dialogs/message',
        'dialogs/export'                                                                         => '//dialogs/export',
        'dialogs/import'                                                                         => '//dialogs/import',
        'dialogs/bulkEdit'                                                                       => '//dialogs/bulk-edit',
        'dialogs/processViewFieldsViewSettings'                                                  => '//dialogs/process-view-fields-view-settings',
        'dialogs/communicationServiceParams'                                                     => '//dialogs/communication-service-params',

        //filter
        'filter/list-menu'                                                                       => '//filter/list-menu',
        'filter/list-menu-sm'                                                                    => '//filter/list-menu-sm',
        'filter/installed'                                                                       => '//filter/installed',
        'filter/block'                                                                           => '//filter/block',
        'filter/panel'                                                                           => '//filter/panel',

        //DropDown
        'ext.ElementMaster.DropDownList.DropDownList'                                            => 'ext.ElementMaster.DropDownList.DropDownList',

        // EditView
        'ext.ElementMaster.EditView.Elements.Block.Block'                                        => 'ext.ElementMaster.EditView.Elements.Block.Block',
        'ext.ElementMaster.EditView.Elements.Panel.Panel'                                        => 'ext.ElementMaster.EditView.Elements.Panel.Panel',
        'ext.ElementMaster.EditView.Elements.Label.Label'                                        => 'ext.ElementMaster.EditView.Elements.Label.Label',
        'ext.ElementMaster.EditView.Elements.Edit.Edit'                                          => 'ext.ElementMaster.EditView.Elements.Edit.Edit',
        'ext.ElementMaster.EditView.Elements.Buttons.Buttons'                                    => 'ext.ElementMaster.EditView.Elements.Buttons.Buttons',
        'ext.ElementMaster.EditView.Elements.Activity.Activity'                                  => 'ext.ElementMaster.EditView.Elements.Activity.Activity',
        'ext.ElementMaster.EditView.Elements.ParticipantBlock.ParticipantBlock'                  => 'ext.ElementMaster.EditView.Elements.ParticipantBlock.ParticipantBlock',
        'ext.ElementMaster.EditView.Elements.Attachments.Attachments'                            => 'ext.ElementMaster.EditView.Elements.Attachments.Attachments',
        'ext.ElementMaster.EditView.Elements.TableColumn.TableColumn'                            => 'ext.ElementMaster.EditView.Elements.TableColumn.TableColumn',
        'ext.ElementMaster.EditView.Elements.SubModule.BlockTable'                               => 'ext.ElementMaster.EditView.Elements.SubModule.BlockTable',
        'ext.ElementMaster.EditView.Elements.SubModule.TBody'                                    => 'ext.ElementMaster.EditView.Elements.SubModule.TBody',
        'ext.ElementMaster.EditView.Elements.FileBlock.FileBlock'                                => 'ext.ElementMaster.EditView.Elements.FileBlock.FileBlock',
        'ext.ElementMaster.Constructor.Elements.FieldType.FieldType'                             => 'ext.ElementMaster.Constructor.Elements.FieldType.FieldType',

        // ListView
        'ext.ElementMaster.ListView.Elements.TData.TData'                                        => 'ext.ElementMaster.ListView.Elements.TData.TData',
        'ext.ElementMaster.ListView.Elements.Avatar.Avatar'                                      => 'ext.ElementMaster.ListView.Elements.Avatar.Avatar',

        // ProcessView
        'ext.ElementMaster.ProcessView.Elements.PPanel.PPanel'                                   => 'ext.ElementMaster.ProcessView.Elements.PPanel.PPanel',
        'ext.ElementMaster.ProcessView.Elements.PCard.PCard'                                     => 'ext.ElementMaster.ProcessView.Elements.PCard.PCard',
        'ext.ElementMaster.ProcessView.Elements.PPanelEdit.PPanelEdit'                           => 'ext.ElementMaster.ProcessView.Elements.PPanelEdit.PPanelEdit',
        'ext.ElementMaster.ProcessView.Elements.PPanelTitle.PPanelTitle'                         => 'ext.ElementMaster.ProcessView.Elements.PPanelTitle.PPanelTitle',

        // InLine
        'ext.ElementMaster.InLineEdit.Elements.InLineEdit.InLineEdit'                            => 'ext.ElementMaster.InLineEdit.Elements.InLineEdit.InLineEdit',

        //Filters
        'ext.Filters.ListView.Elements.FilterCondition.FilterCondition'                          => 'ext.Filters.ListView.Elements.FilterCondition.FilterCondition',
        'ext.Filters.ListView.Elements.FilterConditionValue.FilterConditionValue'                => 'ext.Filters.ListView.Elements.FilterConditionValue.FilterConditionValue',
        'ext.Filters.ListView.Elements.FilterCondition.FilterCondition'                          => 'ext.Filters.ListView.Elements.FilterCondition.FilterCondition',

        //Select
        'ext.ElementMaster.ParticipantItemList.Elements.ParticipantItemList.ParticipantItemList' => 'ext.ElementMaster.ParticipantItemList.Elements.ParticipantItemList.ParticipantItemList',
    ];

    /**
     * возвращает путь в отобрадению (виджету)
     */
    public static function getView($view_key)
    {
        return self::$_views[$view_key];
    }

    /**
     * обновляет пути отображений (виджетов), или дополняет новыми
     *
     * @param array $view_data - список отобрадений "ключ_путь" => "значий_путь"
     */
    public static function setView($key, $value)
    {
        self::$_views[$key] = $value;
    }

    /**
     * обновляет пути отображений (виджетов), или дополняет новыми
     *
     * @param array $view_data - список отобрадений "ключ_путь" => "значий_путь"
     */
    public static function setViews(array $view_data)
    {
        self::$_views = Helper::arrayMerge(self::$_views, $view_data);
    }

}
