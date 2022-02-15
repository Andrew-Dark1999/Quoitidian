<ul class="breadcrumb">
	<?php foreach ($path as $pathspec): ?>
        <?php if(!empty($pathspec['url'])): ?>
            <li><a href="<?php echo $pathspec['url']; ?>"><?php echo $pathspec['title']; ?></a> <span class="divider">/</span></li>
        <?php else: ?>
            <li class="active"><?php echo $pathspec['title'] ?></li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>
