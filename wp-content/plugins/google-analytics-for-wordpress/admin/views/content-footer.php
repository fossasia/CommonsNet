<?php
/**
 * @package GoogleAnalytics\Admin
 */

?>
</div>
<div class="yoast-ga-banners">
	<?php foreach ( $banners as $item ) : ?>
	<p><a href="<?php echo $item['url']; ?>" target="_blank">
			<img src="<?php echo $item['banner']; ?>" alt="<?php echo $item['title']; ?>" class="yoast-banner" border="0" width="250" />
	</a></p>
	<?php endforeach; ?>
	<?php _e( 'Remove these ads?', 'google-analytics-for-wordpress' ); ?><br />
	<a href="https://www.monsterinsights.com/pricing/#utm_medium=text-link&utm_source=gawp-config&utm_campaign=wpgaplugin" target="_blank"><?php _e( 'Upgrade to Google Analytics By MonsterInsights Pro »', 'google-analytics-for-wordpress' ); ?></a>
</div>
</div>