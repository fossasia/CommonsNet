<?php
/**
 * @package GoogleAnalytics\Frontend
 */

?>
<!-- This site uses the Google Analytics by MonsterInsights plugin v<?php echo GAWP_VERSION; ?> - Universal enabled - https://www.monsterinsights.com/ -->
<script type="text/javascript">
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','__gaTracker');

<?php
// List the GA elements from the class-ga-js.php
if ( count( $gaq_push ) >= 1 ) {
	foreach ( $gaq_push as $item ) {
		if ( ! is_array( $item ) ) {
			echo '	__gaTracker('.$item.");\n";
		}
		elseif ( isset( $item['value'] ) ) {
			echo '	'.$item['value'] . "\n";
		}
	}
}
?>

</script>
<!-- / Google Analytics by MonsterInsights -->
