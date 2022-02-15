<div class="top-nav hr-top-nav element" data-type="user_menu">
	<ul class="nav pull-right top-menu">
			<!--li>
				<input type="text" class="form-control search" placeholder=" Search">
			</li-->
			<!-- user login dropdown start-->
			<li class="dropdown element" data-type="main_top_profile_menu_user">
					<a data-toggle="dropdown" class="dropdown-toggle" href="javascript:void(0)">
                        <?php
                            echo (new AvatarModel())
                                        ->loadUserModule()
                                        ->setDataArrayFromUserId()
                                        ->getAvatar();
                        ?>
						<span class="username"><?php echo UsersModel::model()->findByPk(WebUser::getUserId())->getFullName();  ?></span>
						<b class="caret"></b>
					</a>
					<ul class="dropdown-menu extended logout">
						<li>
							<a class="ajax_content_reload" data-action_key="<?php echo (new \ContentReloadModel(4))->addVars(array('url' => $this->createUrl('/profile')))->prepare()->getKey(); ?>" href="javascript:void(0)"><i class=" fa fa-suitcase"></i> <?php echo Yii::t('base', 'Perfil'); ?></a>
						</li>
						<?php if(Access::checkAccess(PermissionModel::PERMISSION_DATA_VIEW, RegulationModel::REGULATION_SYSTEM_SETTINGS, Access::ACCESS_TYPE_REGULATION)){  ?>
							<li>
								<a class="ajax_content_reload" data-action_key="<?php echo (new \ContentReloadModel(5))->addVars(array('index' => 'constructor'))->prepare()->getKey(); ?>" href="javascript:void(0)"><i class="fa fa-cog"></i> <?php echo Yii::t('base', 'Ajustes'); ?> </a>
							</li>
                        <?php } ?>
						<li><a href="<?php echo $this->createUrl('/logout') ?>"><i class="fa fa-key"></i> <?php echo Yii::t('base', 'Cerrar sesión'); ?></a></li>
					</ul>
			</li>
			<!-- user login dropdown end -->
			<!--toggle right menu-->
<!--        --><?php
//            if ($this->beginContent(Yii::app()->params['rightMenu'])) {
//            ?>
<!--			<li>-->
<!--                <div class="toggle-right-box">-->
<!--                    <div class="fa fa-bars"></div>-->
<!--                </div>-->
<!--			</li>-->
<!--        --><?// }?>
	</ul>
</div>

<script type="text/javascript">
	$(document).ready(function(){
        var content_vars = '<?php echo \ContentReloadModel::getContentVars(); ?>';
        instanceGlobal.contentReload.setVarsFromPage(content_vars, null, null);
	});
</script>
