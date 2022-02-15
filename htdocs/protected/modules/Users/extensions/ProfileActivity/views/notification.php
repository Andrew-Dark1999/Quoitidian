<div class="notification <?php echo $interface_params['class_color']; ?>">
	<div class="notification_box <?php echo $position; ?>">
		<div class="title"><?php echo $subject; ?><span>, <?php echo $datetime_old; ?></span></div>
		<div class="text"><?php echo $message; ?></div>
	</div>
	<div class="notification_icon">
        <i class="fa <?php echo $interface_params['icon']; ?>"></i>
    </div>
    <?php echo $content; ?>
</div>
