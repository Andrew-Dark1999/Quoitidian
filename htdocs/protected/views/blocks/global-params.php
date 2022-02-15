<span id="global_params"
        data-url_html_message="<?php echo Yii::app()->createUrl('/site/htmlMessage'); ?>"
        data-url_edit_module="<?php echo Yii::app()->createUrl('/constructor/editModule'); ?>"
        data-url_create_module="<?php echo Yii::app()->createUrl('/constructor/createModule'); ?>"
        data-url_edit_view_update_relate_for_template="<?php echo Yii::app()->createUrl('/module/editView/updateRelateForTemplate'); ?>"
        data-url_list_view_additional_update="<?php echo Yii::app()->createUrl('/module/listView/additionalUpdate'); ?>"
        data-url_process_view_get_panel="<?php echo Yii::app()->createUrl('/module/processView/getPanel'); ?>"

        data-delete_user_storage="<?php echo Yii::app()->createUrl('/history/deleteUserStorage'); ?>"
></span>


<script>
    var urls = {
        url_edit_view_activity_save_message : "<?php echo Yii::app()->createUrl('/module/activityMessages/saveMessage'); ?>",
        url_edit_view_activity_delete_message : "<?php echo Yii::app()->createUrl('/module/activityMessages/deleteMessage'); ?>",
        url_edit_view_activity_delete_file : "<?php echo Yii::app()->createUrl('/module/activityMessages/deleteFile'); ?>",
        url_edit_view_activity_get_message_by_id : "<?php echo Yii::app()->createUrl('/module/activityMessages/getMessageById'); ?>",
        url_edit_view_activity_get_message_list : "<?php echo Yii::app()->createUrl('/module/activityMessages/getMessageList'); ?>",
        edit_view_bulk_edit : "<?php echo Yii::app()->createUrl('/module/editView/bulkEdit'); ?>",
        list_view_formula_check : "<?php echo Yii::app()->createUrl('/module/listView/formulaCheck'); ?>",
        constructor_validate_calculated : "<?php echo Yii::app()->createUrl('/constructor/validationCalculated'); ?>",
        get_user_storage_url : "<?php echo Yii::app()->createUrl('/history/getUserStorageUrl'); ?>",
        set_user_storage_back_url : "<?php echo Yii::app()->createUrl('/history/setUserStorageBackUrl'); ?>",
        url_filter_add_condition_value : "<?php echo Yii::app()->createUrl('/module/listViewFilter/addConditionValue'); ?>",
        url_filter_add_condition : "<?php echo Yii::app()->createUrl('/module/listViewFilter/addCondition'); ?>",
        url_filter_add_panel : "<?php echo Yii::app()->createUrl('/module/listViewFilter/addPanel'); ?>",
        url_filter_save : "<?php echo Yii::app()->createUrl('/module/listViewFilter/save'); ?>",
        url_data_clear_rubbish : "<?php echo Yii::app()->createUrl('/site/ClearRubbish'); ?>",
        url_upload_show_google_doc_view : "<?php echo Yii::app()->createUrl('/file/showGoogleDocView'); ?>",
        url_upload_url_link : "<?php echo Yii::app()->createUrl('/file/uploadUrlLink'); ?>",
        url_ajax_error : "<?php echo 'An error has occurred. Please check your Internet connection and try again'; ?>",
        url_upload_delete_file : "<?php echo Yii::app()->createUrl('/file/deleteFile'); ?>",
        url_upload_delete_file_avatar : "<?php echo Yii::app()->createUrl('/file/deleteFileAvatar'); ?>",
        get_user_storage_url_from_index : "<?php echo Yii::app()->createUrl('/history/getUserStorageUrlFromIndex'); ?>",
        url_edit_view_report_constructor : "<?php echo Yii::app()->createUrl('/module/constructor/view'); ?>",
        url_edit_view_edit : "<?php echo Yii::app()->createUrl('/module/editView/edit'); ?>",
        url_edit_view_add_by_title : "<?php echo Yii::app()->createUrl('/module/editView/addByTitle'); ?>",
        url_todo_list : "<?php echo Yii::app()->createUrl('/module/processView/getTodoList'); ?>",
        get_user_storage_url_via_parent : "<?php echo Yii::app()->createUrl('/history/getUserStorageUrlViaParent'); ?>",
        url_edit_view_edit_select : "<?php echo Yii::app()->createUrl('/module/editView/editSelect'); ?>",
        url_edit_view_relate_reload_sdm : "<?php echo Yii::app()->createUrl('/module/editView/relateReloadSDM'); ?>",
        url_edit_view_relate_reload_sdm_channel : "<?php echo Yii::app()->createUrl('/module/editView/relateReloadSDMChannel'); ?>",
        url_edit_view_relate_reload : "<?php echo Yii::app()->createUrl('/module/editView/relateReload'); ?>",
        url_copy_module : "<?php echo Yii::app()->createUrl('/constructor/copyModule'); ?>",
        url_edit_block_module_list : "<?php echo Yii::app()->createUrl('/constructor/addElementBlockList'); ?>",
        url_settings : "<?php echo Yii::app()->createUrl('constructor/settings'); ?>",
        url_edit_sub_module_list : "<?php echo Yii::app()->createUrl('/constructor/addElementSubModuleList'); ?>",
        url_is_used_select : "<?php echo Yii::app()->createUrl('/constructor/isUsedSelect'); ?>",
        url_validate_module : "<?php echo Yii::app()->createUrl('/constructor/validateModule'); ?>",
        url_validate_before_belete_module : "<?php echo Yii::app()->createUrl('/constructor/validateBeforeDeleteModule'); ?>",
        url_module_set_status : "<?php echo Yii::app()->createUrl('/constructor/setModuleStatus'); ?>",
        url_set_list_order : "<?php echo Yii::app()->createUrl('/constructor/setListOrder'); ?>",
        url_module_fields : "<?php echo Yii::app()->createUrl('/constructor/getModuleFields'); ?>",
        url_save_module : "<?php echo Yii::app()->createUrl('/constructor/saveModule'); ?>",
        url_delete_module : "<?php echo Yii::app()->createUrl('constructor/deleteModule'); ?>",
        url_edit_module_add_element : "<?php echo Yii::app()->createUrl('/constructor/addElement'); ?>",
        url_add_element_data_for_select : "<?php echo Yii::app()->createUrl('/constructor/addElementDataForSelect'); ?>",
        url_module_names : "<?php echo Yii::app()->createUrl('/constructor/getModuleNames'); ?>",
        url_field_params : "<?php echo Yii::app()->createUrl('/constructor/fieldParams'); ?>",
        url_delete_module_data : "<?php echo Yii::app()->createUrl('constructor/deleteModuleData'); ?>",

        url_process_view_get_html_edit_panel_title : "<?php echo Yii::app()->createUrl('/module/processView/getHtmlEditPanelTitle'); ?>",

        url_list_view_add_cards_sub_module : "<?php echo Yii::app()->createUrl('/module/listView/addCardsSubModule'); ?>",
        url_list_view_copy_for_sub_module : "<?php echo Yii::app()->createUrl('/module/listView/copyForSubModule'); ?>",
        url_list_view_card_list_for_sub_module : "<?php echo Yii::app()->createUrl('/module/listView/cardListForSubModule'); ?>",
        url_list_view_delete_from_sub_module : "<?php echo Yii::app()->createUrl('/module/listView/deleteFromSubModule'); ?>",
        url_list_view_insert_card_in_sub_module : "<?php echo Yii::app()->createUrl('/module/listView/insertCardInSubModule'); ?>",
        url_list_view_update_card_list_sub_modules : "<?php echo Yii::app()->createUrl('/module/listView/updateCardListSubModules'); ?>",
        url_list_view_copy : "<?php echo Yii::app()->createUrl('/module/listView/copy'); ?>",
        url_list_view_delete : "<?php echo Yii::app()->createUrl('/module/listView/delete'); ?>",
        url_upload_file_progress : "<?php echo Yii::app()->createUrl('/file/uploadFileProgress'); ?>",
        session_upload_progress_name : "<?php echo ini_get("session.upload_progress.name"); ?>",
        post_max_size : "<?php echo HelperIniParams::getPostMaxSize(HelperIniParams::UNIT_BYTE); ?>",
        upload_max_filesize : "<?php echo HelperIniParams::getPostUploadMaxFileSize(HelperIniParams::UNIT_BYTE); ?>",
        url_upload_file : "<?php echo Yii::app()->createUrl('/file/uploadFile'); ?>",
        url_list_view_select_export : "<?php echo Yii::app()->createUrl('/module/listView/selectExportFields'); ?>",
        url_list_view_export : "<?php echo Yii::app()->createUrl('/module/listView/export'); ?>",
        url_list_view_import_postprocessing : "<?php echo Yii::app()->createUrl('/module/listView/importPostProccesing'); ?>",
        url_list_view_import : "<?php echo Yii::app()->createUrl('/module/listView/import'); ?>",
        url_list_view_load_templates_from_block : "<?php echo Yii::app()->createUrl('/module/listView/loadTemplatesFromBlock'); ?>",
        url_edit_view_toggle_blocks : "<?php echo Yii::app()->createUrl('/module/editView/toggleBlocks'); ?>",
        url_list_view_save_imported : "<?php echo Yii::app()->createUrl('/module/listView/saveImported'); ?>",
        url_list_view_cancel_imported : "<?php echo Yii::app()->createUrl('/module/listView/cancelImported'); ?>",
        url_list_view_additional_update : "<?php echo Yii::app()->createUrl('/module/listView/additionalUpdate'); ?>",
        url_list_view_print : "<?php echo Yii::app()->createUrl('/module/listView/print'); ?>",
        url_list_view_generate : "<?php echo Yii::app()->createUrl('/module/listView/generate'); ?>",
        url_process_view_run : "<?php echo Yii::app()->createUrl('/module/BPM/run') . '/' . \ExtensionCopyModel::MODULE_PROCESS; ?>",
        url_process_view_constructor : "<?php echo Yii::app()->createUrl('/module/BPM/constructor') . '/' . \ExtensionCopyModel::MODULE_PROCESS; ?>",
        url_process_view_update : "<?php echo Yii::app()->createUrl('/module/processView/update'); ?>",
        url_process_view_card_sort : "<?php echo Yii::app()->createUrl('/module/processView/cardSort'); ?>",
        url_process_view_panel_sort : "<?php echo Yii::app()->createUrl('/module/processView/panelSort'); ?>",
        url_process_view_panel_sort_delete : "<?php echo Yii::app()->createUrl('/module/processView/panelSortDelete'); ?>",
        url_process_save_second_fields_view : "<?php echo Yii::app()->createUrl('/module/processView/saveSecondFieldView'); ?>",
        url_set_language : "<?php echo Yii::app()->createUrl('/site/setLanguage'); ?>",
        url_filter_load : "<?php echo Yii::app()->createUrl('/module/listViewFilter/load'); ?>",
        url_filter_add_block : "<?php echo Yii::app()->createUrl('/module/listViewFilter/addBlock'); ?>",
        url_filter_delete : "<?php echo Yii::app()->createUrl('/module/listViewFilter/delete'); ?>",
        url_list_view_show : "<?php echo Yii::app()->createUrl('/module/listView/show'); ?>",

        url_participant_get_item_list : "<?php echo Yii::app()->createUrl('/module/participant/getItemList'); ?>",
        url_participant_get_selected_icon_item : "<?php echo Yii::app()->createUrl('/module/participant/getSelectedIconItem'); ?>",
        url_participant_get_selected_icon_item_for_user : "<?php echo Yii::app()->createUrl('/module/participant/getSelectedIconItemForUser'); ?>",
        url_participant_get_selected_list_item : "<?php echo Yii::app()->createUrl('/module/participant/getSelectedListItem'); ?>",
        url_participant_get_list_item_as_responsible : "<?php echo Yii::app()->createUrl('/module/participant/getListItemAsResponsible'); ?>",
        url_participant_save_item_email : "<?php echo Yii::app()->createUrl('/module/participant/saveItemEmail'); ?>",
        url_participant_has_participant_user_by_email_id : "<?php echo Yii::app()->createUrl('/module/participant/hasParticipantUserByEmailId'); ?>",
        url_participant_has_participant : "<?php echo Yii::app()->createUrl('/module/participant/hasParticipant'); ?>",
        url_participant_find_exists_email_participant_in_communications : "<?php echo Yii::app()->createUrl('/module/participant/findExistsEmailParticipantInCommunications'); ?>",



        //url_participant_delete_card : "<?php echo Yii::app()->createUrl('/module/participant/deleteCard'); ?>",
        url_participant_add_users : "<?php echo Yii::app()->createUrl('/module/participantusers'); ?>",
        url_in_line_save : "<?php echo Yii::app()->createUrl('/module/editView/inLineSave'); ?>",
        url_set_user_storage : "<?php echo Yii::app()->createUrl('/history/setUserStorage'); ?>",
        url_process_view_save_panel_title : "<?php echo Yii::app()->createUrl('/module/processView/savePanelTitle'); ?>",
        url_process_view_panel_menu_action_run : "<?php echo Yii::app()->createUrl('/module/processView/panelMenuActionRun'); ?>",
        url_process_view_show : "<?php echo Yii::app()->createUrl('/module/processView/show'); ?>",
        url_mailing_services_refresh : "<?php echo Yii::app()->createUrl('/site/mailingServicesRefresh'); ?>",
        url_get_user_storage : "<?php echo Yii::app()->createUrl('/history/getUserStorage'); ?>",
        url_in_line_cancel : "<?php echo Yii::app()->createUrl('/module/editView/inLineCancel'); ?>",
        url_load_params : "<?php echo Yii::app()->createUrl('/site/loadParams'); ?>",
        url_profile_html_refresh : "<?php echo Yii::app()->createUrl('/profile/profileHtmlRefresh'); ?>",
        url_profile_save : "<?php echo Yii::app()->createUrl('/profile/profileSave'); ?>",
        url_restore : "<?php echo Yii::app()->createUrl('/restore'); ?>",
        url_profile_api_regenerate_token : "<?php echo Yii::app()->createUrl('/profile/apiRegenerateToken'); ?>",
        url_profile_personal_contact_save : "<?php echo Yii::app()->createUrl('/profile/personalContactSave'); ?>"
    }

    Global.urls = urls;
    ModelGlobal.saveUrls(urls);
</script>
