<?php
/**
 * @package GoogleAnalytics\Frontend
 */

?>
<!-- This site uses the Google Analytics by MonsterInsights plugin v<?php echo GAWP_VERSION; ?> - Universal disabled - https://www.monsterinsights.com/ -->
<script type="text/javascript">

	var _gaq = _gaq || [];
<?php
// List the GA elements from the class-ga-js.php
if ( count( $gaq_push ) >= 1 ) {
	foreach ( $gaq_push as $item ) {
		if ( ! is_array( $item ) ) {
			echo '	_gaq.push([' . $item . "]);\n";
		}
		elseif ( isset( $item['value'] ) ) {
			echo '	'.$item['value'] . "\n";
		}
	}
}
?>

	(function () {
		var ga = document.createElement('script');
		ga.type = 'text/javascript';
		ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0];
		s.parentNode.insertBefore(ga, s);
	})();

</script>
<!-- / Google Analytics by MonsterInsights -->
