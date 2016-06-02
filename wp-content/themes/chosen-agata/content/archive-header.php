<?php

if ( is_home() ) {
	echo '<h1 class="screen-reader-text">' . get_bloginfo("name") . ' ' . __('Posts', 'chosen') . '</h1>';
}
if ( ! is_archive() ) {
	return;
}

$icon_class = 'folder-open';

if ( is_tag() ) {
	$icon_class = 'tag';
} elseif ( is_author() ) {
	$icon_class = 'user';
} elseif ( is_date() ) {
	$icon_class = 'calendar';
}
?>

<div class='archive-header'>
	<h1>
		<i class="fa fa-<?php echo $icon_class; ?>"></i>
		<?php the_archive_title(); ?>
	</h1>
	<?php the_archive_description(); ?>
</div>