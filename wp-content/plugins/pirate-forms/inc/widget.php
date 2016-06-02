<?php
/**
 * Template for new WordPress widget
 *
 * @see WP_Widget::widget()
 */
class pirate_forms_contact_widget extends WP_Widget {

	/**
	 *  Widget constructor
	 */
	public function __construct() {
		parent::__construct(
			'pirate_forms_contact_widget',
			__( 'Pirate Forms', 'pirate-forms' ),
			array( 'classname'   => __FUNCTION__, 'description' => __( 'Pirate Forms','pirate-forms') )
		);
	}

	/**
	 * Widget logic and display
	 *
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {

		// Pulling out all settings
		extract( $args );
		extract( $instance );

		// Output all wrappers
		echo $before_widget . '<div class="pirate-forms-contact-widget">';

			if ( ! empty( $pirate_forms_widget_title ) )
				echo $before_title . $pirate_forms_widget_title . $after_title;

			if ( ! empty( $pirate_forms_widget_subtext ) )
				echo wpautop( stripslashes( $pirate_forms_widget_subtext ) );

			echo do_shortcode('[pirate_forms]');

		echo '</div>' . $after_widget;

	}

	/**
	 * Used to update widget settings
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		// Storing widget title as inputted option or category name
		$instance['pirate_forms_widget_title'] = apply_filters( 'widget_title', sanitize_text_field( $new_instance['pirate_forms_widget_title'] ) );

		$instance['pirate_forms_widget_subtext'] = $new_instance['pirate_forms_widget_subtext'];

		return $instance;
	}

	/**
	 * Used to generate the widget admin view
	 *
	 * @param array $instance
	 *
	 * @return string|void
	 */
	function form( $instance ) {

		$pirate_forms_widget_title = ! empty( $instance['pirate_forms_widget_title'] ) ? $instance['pirate_forms_widget_title'] : __( 'Title','pirate-forms' );
		$pirate_forms_widget_subtext = ! empty( $instance['pirate_forms_widget_subtext'] ) ? $instance['pirate_forms_widget_subtext'] : __( 'Text above form','pirate-forms' );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'pirate_forms_widget_title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'pirate_forms_widget_title' ); ?>" name="<?php echo $this->get_field_name( 'pirate_forms_widget_title' ); ?>" type="text" value="<?php echo esc_attr( $pirate_forms_widget_title ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'pirate_forms_widget_subtext' ); ?>"><?php _e( 'Subtext:' ); ?></label>
			<textarea class="widefat" id="<?php echo $this->get_field_id( 'pirate_forms_widget_subtext' ); ?>" name="<?php echo $this->get_field_name( 'pirate_forms_widget_subtext' ); ?>"><?php echo esc_attr( $pirate_forms_widget_subtext ); ?></textarea>
		</p>
		<?php

	}
}

add_action( 'widgets_init', create_function( '', 'return register_widget("pirate_forms_contact_widget");' ) );