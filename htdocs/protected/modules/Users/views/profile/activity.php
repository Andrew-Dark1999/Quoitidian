<div id="overview" class="tab-pane <?php if(isset($tab_active) && $tab_active == 'activity') echo 'active'; ?>">
    <!-- activity -->                
    <div class="row">
    	<div class="prof_activity">
            <?php
                $result = Yii::app()->controller->widget('Users.extensions.ProfileActivity.ProfileActivity',
                                                    array(
                                                        'history_data' => $history_data,
                                                    ))
                                                    ->getResult();

                echo $result['html'];
            ?>
            <?php
                if(Pagination::$active_page < Pagination::getInstance()->getCountPages()){
            ?>            
    		<div class="load_more">
                <span class="element" style="display: none;"
                      data-type="profile-activity-data"
                      data-date="<?php echo !empty($history_data) ? date('Y-m-d', strtotime($history_data[count($history_data)-1]->date_create)):""; ?>"
                      data-page="<?php echo Pagination::$active_page+1; ?>"
                      data-notification_position="<?php echo Yii::app()->user->getFlash('notification_position'); ?>"
                      
                ></span>
    			<a href="javascript:void(0)" class="btn btn-create element" data-type="profile-activity-more"><?php echo Yii::t('UsersModule.base', 'Load more') ?></a>
    		</div>
            <?php } ?>

            <script>
                HeaderNotice.clearLinkAction(HeaderNotice.NOTICE_OBJECT_ACTIVITY);
                HeaderNotice.addLinkActions(<?php echo (!empty($result['link_actions']) ? json_encode($result['link_actions']) : '""'); ?>)
            </script>

    	</div>
    </div>                                
</div>
