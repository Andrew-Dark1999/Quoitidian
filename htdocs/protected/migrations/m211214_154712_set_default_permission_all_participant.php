<?php

class m211214_154712_set_default_permission_all_participant extends CDbMigration
{
    public function up()
    {
        $this->update(
            '{{extension_copy}}',
            ['schema' => '[{"type":"block","attr":[],"params":{"title":"\u041d\u043e\u0432\u044b\u0439 \u0431\u043b\u043e\u043a","title_edit":true,"destroy":"1","header_hidden":"1","unique_index":"547284bb7654ac0980ca1a372f28e0e0","border_top":"0","chevron_down":"1","edit_view_show":"1","edit_view_display":"1","block_panel_contact_exists":"1"},"elements":[{"type":"block_panel","params":{"count_panels":"9","make":true},"elements":[{"type":"panel","attr":[],"params":{"destroy":"1","active_count_select_fields":"1","count_select_fields":"4","c_count_select_fields_display":"1","c_list_view_display":"1","c_process_view_group_display":"1","list_view_visible":"1","process_view_group":"0","edit_view_edit":"0","inline_edit":"0","edit_view_show":"1","list_view_display":"0","edit_view_display":"0","process_view_display":"0"},"elements":[{"type":"label","attr":[],"params":{"title":"access_id"}},{"type":"block_field_type","params":{"count_edit":"1"},"elements":[{"type":"edit","attr":[],"params":{"display":"1","is_primary":"0","edit_view_show":"1","c_load_params_btn_display":"1","c_load_params_view":true,"c_db_create":true,"c_types_list_index":"1","\u0441_remove":true,"title":null,"name":"access_id","relate_module_copy_id":null,"relate_module_template":false,"relate_index":null,"relate_field":null,"relate_type":null,"relate_many_select":"0","relate_links":[{"value":"create","checked":true},{"value":"select","checked":true},{"value":"copy","checked":true},{"value":"delete","checked":true}],"values":[],"pk":false,"type":"numeric","type_db":"decimal","type_view":"edit","maxLength":null,"minLength":null,"file_types":null,"file_types_mimo":null,"file_thumbs_size":null,"file_max_size":null,"file_min_size":null,"file_generate":"0","name_generate":"","read_only":"0","size":16,"decimal":5,"required":"0","formula":false,"default_value":"","group_index":"1","filter_enabled":true,"filter_exception_position":[],"input_attr":"","add_zero_value":"1","avatar":true,"rules":"","unique":"0","money_type":"0","add_hundredths":"0","filter_group":"group2","name_generate_params":""}}]}]},{"type":"panel","attr":[],"params":{"destroy":"1","active_count_select_fields":"1","count_select_fields":"4","c_count_select_fields_display":"1","c_list_view_display":"1","c_process_view_group_display":"1","list_view_visible":"1","process_view_group":"0","edit_view_edit":"0","inline_edit":"0","edit_view_show":"1","list_view_display":"0","edit_view_display":"0","process_view_display":"0"},"elements":[{"type":"label","attr":[],"params":{"title":"access_id_type"}},{"type":"block_field_type","params":{"count_edit":"1"},"elements":[{"type":"edit","attr":[],"params":{"display":"1","is_primary":"0","edit_view_show":"1","c_load_params_btn_display":"1","c_load_params_view":true,"c_db_create":true,"c_types_list_index":"1","\u0441_remove":true,"title":null,"name":"access_id_type","relate_module_copy_id":null,"relate_module_template":false,"relate_index":null,"relate_field":null,"relate_type":null,"relate_many_select":"0","relate_links":[{"value":"create","checked":true},{"value":"select","checked":true},{"value":"copy","checked":true},{"value":"delete","checked":true}],"values":[],"pk":false,"type":"numeric","type_db":"decimal","type_view":"edit","maxLength":null,"minLength":null,"file_types":null,"file_types_mimo":null,"file_thumbs_size":null,"file_max_size":null,"file_min_size":null,"file_generate":"0","name_generate":"","read_only":"0","size":16,"decimal":5,"required":"0","formula":false,"default_value":"","group_index":"2","filter_enabled":true,"filter_exception_position":[],"input_attr":"","add_zero_value":"1","avatar":true,"rules":"","unique":"0","money_type":"0","add_hundredths":"0","filter_group":"group2","name_generate_params":""}}]}]},{"type":"panel","attr":[],"params":{"destroy":"1","active_count_select_fields":"1","count_select_fields":"4","c_count_select_fields_display":"1","c_list_view_display":"1","c_process_view_group_display":"1","list_view_visible":"1","process_view_group":"0","edit_view_edit":"1","inline_edit":"1","edit_view_show":"1","list_view_display":"1","edit_view_display":"1","process_view_display":"1"},"elements":[{"type":"label","attr":[],"params":{"title":"View"}},{"type":"block_field_type","params":{"count_edit":"1"},"elements":[{"type":"edit","attr":[],"params":{"display":"1","is_primary":"0","edit_view_show":"1","c_load_params_btn_display":"1","c_load_params_view":true,"c_db_create":true,"c_types_list_index":"1","\u0441_remove":true,"title":null,"name":"rule_view","relate_module_copy_id":null,"relate_module_template":false,"relate_index":null,"relate_field":null,"relate_type":null,"relate_many_select":"0","relate_links":[{"value":"create","checked":true},{"value":"select","checked":true},{"value":"copy","checked":true},{"value":"delete","checked":true}],"values":{"1":"Allowed","2":"Prohibited"},"pk":false,"type":"select","type_db":"integer","type_view":"edit","maxLength":null,"minLength":null,"file_types":null,"file_types_mimo":null,"file_thumbs_size":null,"file_max_size":null,"file_min_size":null,"file_generate":"0","name_generate":"","read_only":"0","size":11,"decimal":null,"required":"1","formula":false,"default_value":"1","group_index":"3","filter_enabled":true,"filter_exception_position":[],"input_attr":"","add_zero_value":"0","avatar":true,"rules":"","unique":"0","money_type":false,"add_hundredths":false,"filter_group":"group1","name_generate_params":"","select_option":"Prohibited","select_sort":{"1":"0","2":"1"},"select_remove_forbid":["","0","0"],"select_finished_object":["","0","0"],"select_slug":["","",""]}}]}]},{"type":"panel","attr":[],"params":{"destroy":"1","active_count_select_fields":"1","count_select_fields":"4","c_count_select_fields_display":"1","c_list_view_display":"1","c_process_view_group_display":"1","list_view_visible":"1","process_view_group":"0","edit_view_edit":"1","inline_edit":"1","edit_view_show":"1","list_view_display":"1","edit_view_display":"1","process_view_display":"1"},"elements":[{"type":"label","attr":[],"params":{"title":"Create"}},{"type":"block_field_type","params":{"count_edit":"1"},"elements":[{"type":"edit","attr":[],"params":{"display":"1","is_primary":"0","edit_view_show":"1","c_load_params_btn_display":"1","c_load_params_view":true,"c_db_create":true,"c_types_list_index":"1","\u0441_remove":true,"title":null,"name":"rule_create","relate_module_copy_id":null,"relate_module_template":false,"relate_index":null,"relate_field":null,"relate_type":null,"relate_many_select":"0","relate_links":[{"value":"create","checked":true},{"value":"select","checked":true},{"value":"copy","checked":true},{"value":"delete","checked":true}],"values":{"1":"Allowed","2":"Prohibited"},"pk":false,"type":"select","type_db":"integer","type_view":"edit","maxLength":null,"minLength":null,"file_types":null,"file_types_mimo":null,"file_thumbs_size":null,"file_max_size":null,"file_min_size":null,"file_generate":"0","name_generate":"","read_only":"0","size":11,"decimal":null,"required":"1","formula":false,"default_value":"1","group_index":"4","filter_enabled":true,"filter_exception_position":[],"input_attr":"","add_zero_value":"1","avatar":true,"rules":"","unique":"0","money_type":false,"add_hundredths":false,"filter_group":"group1","name_generate_params":"","select_option":"Prohibited","select_sort":{"1":"0","2":"1"},"select_remove_forbid":["","0","0"],"select_finished_object":["","0","0"],"select_slug":["","",""]}}]}]},{"type":"panel","attr":[],"params":{"destroy":"1","active_count_select_fields":"1","count_select_fields":"4","c_count_select_fields_display":"1","c_list_view_display":"1","c_process_view_group_display":"1","list_view_visible":"1","process_view_group":"0","edit_view_edit":"1","inline_edit":"1","edit_view_show":"1","list_view_display":"1","edit_view_display":"1","process_view_display":"1"},"elements":[{"type":"label","attr":[],"params":{"title":"Edit"}},{"type":"block_field_type","params":{"count_edit":"1"},"elements":[{"type":"edit","attr":[],"params":{"display":"1","is_primary":"0","edit_view_show":"1","c_load_params_btn_display":"1","c_load_params_view":true,"c_db_create":true,"c_types_list_index":"1","\u0441_remove":true,"title":null,"name":"rule_edit","relate_module_copy_id":null,"relate_module_template":false,"relate_index":null,"relate_field":null,"relate_type":null,"relate_many_select":"0","relate_links":[{"value":"create","checked":true},{"value":"select","checked":true},{"value":"copy","checked":true},{"value":"delete","checked":true}],"values":{"1":"Allowed","2":"Prohibited"},"pk":false,"type":"select","type_db":"integer","type_view":"edit","maxLength":null,"minLength":null,"file_types":null,"file_types_mimo":null,"file_thumbs_size":null,"file_max_size":null,"file_min_size":null,"file_generate":"0","name_generate":"","read_only":"0","size":11,"decimal":null,"required":"1","formula":false,"default_value":"1","group_index":"5","filter_enabled":true,"filter_exception_position":[],"input_attr":"","add_zero_value":"0","avatar":true,"rules":"","unique":"0","money_type":false,"add_hundredths":false,"filter_group":"group1","name_generate_params":"","select_option":"Prohibited","select_sort":{"1":"0","2":"1"},"select_remove_forbid":["","0","0"],"select_finished_object":["","0","0"],"select_slug":["","",""]}}]}]},{"type":"panel","attr":[],"params":{"destroy":"1","active_count_select_fields":"1","count_select_fields":"4","c_count_select_fields_display":"1","c_list_view_display":"1","c_process_view_group_display":"1","list_view_visible":"1","process_view_group":"0","edit_view_edit":"1","inline_edit":"1","edit_view_show":"1","list_view_display":"1","edit_view_display":"1","process_view_display":"1"},"elements":[{"type":"label","attr":[],"params":{"title":"Delete"}},{"type":"block_field_type","params":{"count_edit":"1"},"elements":[{"type":"edit","attr":[],"params":{"display":"1","is_primary":"0","edit_view_show":"1","c_load_params_btn_display":"1","c_load_params_view":true,"c_db_create":true,"c_types_list_index":"1","\u0441_remove":true,"title":null,"name":"rule_delete","relate_module_copy_id":null,"relate_module_template":false,"relate_index":null,"relate_field":null,"relate_type":null,"relate_many_select":"0","relate_links":[{"value":"create","checked":true},{"value":"select","checked":true},{"value":"copy","checked":true},{"value":"delete","checked":true}],"values":{"1":"Allowed","2":"Prohibited"},"pk":false,"type":"select","type_db":"integer","type_view":"edit","maxLength":null,"minLength":null,"file_types":null,"file_types_mimo":null,"file_thumbs_size":null,"file_max_size":null,"file_min_size":null,"file_generate":"0","name_generate":"","read_only":"0","size":11,"decimal":null,"required":"1","formula":false,"default_value":"1","group_index":"6","filter_enabled":true,"filter_exception_position":[],"input_attr":"","add_zero_value":"0","avatar":true,"rules":"","unique":"0","money_type":false,"add_hundredths":false,"filter_group":"group1","name_generate_params":"","select_option":"Prohibited","select_sort":{"1":"0","2":"1"},"select_remove_forbid":["","0","0"],"select_finished_object":["","0","0"],"select_slug":["","",""]}}]}]},{"type":"panel","attr":[],"params":{"destroy":"1","active_count_select_fields":"1","count_select_fields":"4","c_count_select_fields_display":"1","c_list_view_display":"1","c_process_view_group_display":"1","list_view_visible":"1","process_view_group":"0","edit_view_edit":"1","inline_edit":"1","edit_view_show":"1","list_view_display":"1","edit_view_display":"1","process_view_display":"1"},"elements":[{"type":"label","attr":[],"params":{"title":"Import"}},{"type":"block_field_type","params":{"count_edit":"1"},"elements":[{"type":"edit","attr":[],"params":{"display":"1","is_primary":"0","edit_view_show":"1","c_load_params_btn_display":"1","c_load_params_view":true,"c_db_create":true,"c_types_list_index":"1","\u0441_remove":true,"title":null,"name":"rule_import","relate_module_copy_id":null,"relate_module_template":false,"relate_index":null,"relate_field":null,"relate_type":null,"relate_many_select":"0","relate_links":[{"value":"create","checked":true},{"value":"select","checked":true},{"value":"copy","checked":true},{"value":"delete","checked":true}],"values":{"1":"Allowed","2":"Prohibited"},"pk":false,"type":"select","type_db":"integer","type_view":"edit","maxLength":null,"minLength":null,"file_types":null,"file_types_mimo":null,"file_thumbs_size":null,"file_max_size":null,"file_min_size":null,"file_generate":"0","name_generate":"","read_only":"0","size":11,"decimal":null,"required":"1","formula":false,"default_value":"1","group_index":"7","filter_enabled":true,"filter_exception_position":[],"input_attr":"","add_zero_value":"0","avatar":true,"rules":"","unique":"0","money_type":false,"add_hundredths":false,"filter_group":"group1","name_generate_params":"","select_option":"Prohibited","select_sort":{"1":"0","2":"1"},"select_remove_forbid":["","0","0"],"select_finished_object":["","0","0"],"select_slug":["","",""]}}]}]},{"type":"panel","attr":[],"params":{"destroy":"1","active_count_select_fields":"1","count_select_fields":"4","c_count_select_fields_display":"1","c_list_view_display":"1","c_process_view_group_display":"1","list_view_visible":"1","process_view_group":"0","edit_view_edit":"1","inline_edit":"1","edit_view_show":"1","list_view_display":"1","edit_view_display":"1","process_view_display":"1"},"elements":[{"type":"label","attr":[],"params":{"title":"Export"}},{"type":"block_field_type","params":{"count_edit":"1"},"elements":[{"type":"edit","attr":[],"params":{"display":"1","is_primary":"0","edit_view_show":"1","c_load_params_btn_display":"1","c_load_params_view":true,"c_db_create":true,"c_types_list_index":"1","\u0441_remove":true,"title":null,"name":"rule_export","relate_module_copy_id":null,"relate_module_template":false,"relate_index":null,"relate_field":null,"relate_type":null,"relate_many_select":"0","relate_links":[{"value":"create","checked":true},{"value":"select","checked":true},{"value":"copy","checked":true},{"value":"delete","checked":true}],"values":{"1":"Allowed","2":"Prohibited"},"pk":false,"type":"select","type_db":"integer","type_view":"edit","maxLength":null,"minLength":null,"file_types":null,"file_types_mimo":null,"file_thumbs_size":null,"file_max_size":null,"file_min_size":null,"file_generate":"0","name_generate":"","read_only":"0","size":11,"decimal":null,"required":"1","formula":false,"default_value":"1","group_index":"8","filter_enabled":true,"filter_exception_position":[],"input_attr":"","add_zero_value":"0","avatar":true,"rules":"","unique":"0","money_type":false,"add_hundredths":false,"filter_group":"group1","name_generate_params":"","select_option":"Prohibited","select_sort":{"1":"0","2":"1"},"select_remove_forbid":["","0","0"],"select_finished_object":["","0","0"],"select_slug":["","",""]}}]}]},{"type":"panel","attr":[],"params":{"destroy":"1","active_count_select_fields":"1","count_select_fields":"4","c_count_select_fields_display":"1","c_list_view_display":"1","c_process_view_group_display":"1","list_view_visible":"1","process_view_group":"0","edit_view_edit":"1","inline_edit":"1","edit_view_show":"1","list_view_display":"1","edit_view_display":"1"},"elements":[{"type":"label","attr":[],"params":{"title":"All participants"}},{"type":"block_field_type","params":{"count_edit":"1"},"elements":[{"type":"edit","attr":[],"params":{"display":"1","is_primary":"0","edit_view_show":"1","c_load_params_btn_display":"1","c_load_params_view":true,"c_db_create":true,"c_types_list_index":"1","\u0441_remove":true,"title":null,"name":"rule_all_participants","relate_module_copy_id":null,"relate_module_template":false,"relate_index":null,"relate_field":null,"relate_type":null,"relate_many_select":"0","relate_links":[{"value":"create","checked":true},{"value":"select","checked":true},{"value":"copy","checked":true},{"value":"delete","checked":true}],"values":{"1":"Allowed","2":"Prohibited"},"pk":false,"type":"select","type_db":"integer","type_view":"edit","maxLength":null,"minLength":null,"file_types":null,"file_types_mimo":null,"file_thumbs_size":null,"file_max_size":null,"file_min_size":null,"file_generate":"0","name_generate":"","read_only":"0","size":11,"decimal":null,"required":"0","formula":false,"default_value":"2","group_index":"9","filter_enabled":true,"filter_exception_position":[],"input_attr":"","add_zero_value":"1","avatar":true,"rules":"","unique":"0","money_type":false,"add_hundredths":false,"filter_group":"group1","name_generate_params":"","select_option":"No","select_sort":{"1":"0","2":"1"},"select_remove_forbid":["","1","1"],"select_finished_object":["","0","0"],"select_slug":["","",""]}}]}]}]}]}]'],
            'copy_id = 3' //Разрешения
        );

        (new DataModel())
            ->setText("
                create table {{permission_rule_all_participants}}
                (
                    rule_all_participants_id              int auto_increment primary key,
                    rule_all_participants_title           varchar(255)                null,
                    rule_all_participants_color           varchar(20)                 null,
                    rule_all_participants_sort            int                         null,
                    rule_all_participants_remove          enum ('1', '0') default '1' null,
                    rule_all_participants_finished_object enum ('1', '0') default '0' null,
                    rule_all_participants_slug            varchar(255)                null
                )
                    charset = utf8;
            ")->execute();

        $this->insert('{{module_tables}}', [
            'copy_id'           => 3,
            'table_name'        => 'permission_rule_all_participants',
            'type'              => 'relate_select',
            'relate_type'       => 'belongs_to',
            'parent_field_name' => 'rule_all_participants',
            'relate_field_name' => 'rule_all_participants_id',
        ]);

        $this->addColumn('{{permission}}', 'rule_all_participants', 'int(11)');

        $this->insert('{{permission_rule_all_participants}}', [
            'rule_all_participants_title'           => 'Allowed',
            'rule_all_participants_color'           => null,
            'rule_all_participants_sort'            => '0',
            'rule_all_participants_remove'          => '1',
            'rule_all_participants_finished_object' => '0',
            'rule_all_participants_slug'            => null,
        ]);

        $this->insert('{{permission_rule_all_participants}}', [
            'rule_all_participants_title'           => 'Prohibited',
            'rule_all_participants_color'           => null,
            'rule_all_participants_sort'            => '1',
            'rule_all_participants_remove'          => '1',
            'rule_all_participants_finished_object' => '0',
            'rule_all_participants_slug'            => null,
        ]);

        $this->update('{{permission}}', ['rule_all_participants' => 2]);
    }

    public function down()
    {
        echo "m211214_154712_set_default_permission_all_participant does not support migration down.\n";

        return false;
    }
}