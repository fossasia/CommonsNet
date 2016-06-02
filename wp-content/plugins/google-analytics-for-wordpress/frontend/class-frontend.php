<?php
/**
 * @package GoogleAnalytics\Frontend
 */

/**
 * The basic frontend class for the GA plugin, extendable for the children
 */
class Yoast_GA_Frontend {

	/** @var array $options */
	protected $options;

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->options = Yoast_GA_Options::instance()->options;

		if ( isset( $this->options['tag_links_in_rss'] ) && $this->options['tag_links_in_rss'] == 1 ) {
			add_filter( 'the_permalink_rss', array( $this, 'rsslinktagger' ), 99 );
		}

		// Check if the customer is running Universal or not (Enable in GA Settings -> Universal)
		if ( isset( $this->options['enable_universal'] ) && $this->options['enable_universal'] == 1 ) {
			new Yoast_GA_Universal;
		}
		else {
			new Yoast_GA_JS;
		}

	}

	/**
	 * Add the UTM source parameters in the RSS feeds to track traffic
	 *
	 * @param string $guid
	 *
	 * @return string
	 */
	public function rsslinktagger( $guid ) {
		global $post;
		if ( is_feed() ) {
			if ( $this->options['allow_anchor'] ) {
				$delimiter = '#';
			}
			else {
				$delimiter = '?';
				if ( strpos( $guid, $delimiter ) > 0 ) {
					$delimiter = '&amp;';
				}
			}

			return $guid . $delimiter . 'utm_source=rss&amp;utm_medium=rss&amp;utm_campaign=' . urlencode( $post->post_name );
		}

		return $guid;
	}

}
