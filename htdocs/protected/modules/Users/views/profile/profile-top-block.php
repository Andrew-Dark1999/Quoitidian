<section class="panel">
    <div class="panel-body profile-information">
       <div class="col-md-3">
           <div class="profile-pic text-center">
               <div class="element" data-type="profile">
                   <?php
                       echo ProfileModel::getFileBlockAvatar($user_info['user_model']->users_id, 140, $user_info['read_only']);
                       ?>
               </div>
           </div>
       </div>
       <div class="col-md-5">
           <div class="profile-desk">
               <h1><?php echo $user_info['user_model']->getFullName(); ?></h1>
               <span class="text-muted"></span>
               <p><?php echo $user_info['user_description']; ?></p>
           </div>
       </div>
       <div class="col-md-4">
           <div class="profile-contacts">
                <div class="contacts element" data-type="block_field_type_contact">
                   <?php
                        $schema = $extension_copy_staff->getSchemaParse();
                        foreach($schema['elements'] as $schema_value){
                            if(!isset($schema_value['field'])) continue;
                            if($schema_value['field']['params']['type'] == 'string' && $schema_value['field']['params']['type_view'] == Fields::TYPE_VIEW_EDIT_HIDDEN){
                                echo Yii::app()->controller->widget(ViewList::getView('ext.ElementMaster.EditView.Elements.Edit.Edit'),
                                                   array(
                                                    'view' => 'element_hidden',
                                                    'schema' => $schema_value['field'],
                                                    'parent_copy_id' => null,
                                                    'extension_copy' => $extension_copy_staff,
                                                    'extension_data' => $personal_info_model,
                                                    'read_only' => $user_info['read_only'],
                                                   ),
                                                   true);
                            }
                        }
                   ?>
                </div>
           </div>
       </div>
    </div>
</section>

