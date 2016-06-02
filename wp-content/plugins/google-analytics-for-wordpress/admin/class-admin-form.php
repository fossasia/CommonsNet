<?php
/**
 * @package GoogleAnalytics\Admin
 */

/**
 * This class is for the backend
 */
class Yoast_GA_Admin_Form {

	/**
	 * @var string $form_namespace
	 */
	private static $form_namespace;

	/**
	 * Create a form element to init a form
	 *
	 * @param string $namespace
	 *
	 * @return string
	 */
	public static function create_form( $namespace ) {
		self::$form_namespace = $namespace;

		$action = admin_url( 'admin.php' );
		if ( isset( $_GET['page'] ) ) {
			$action .= '?page=' . $_GET['page'];
		}

		return '<form action="' . $action . '" method="post" id="yoast-ga-form-' . self::$form_namespace . '" class="yoast_ga_form">' . wp_nonce_field( 'save_settings', 'yoast_ga_nonce', null, false );
	}


	/**
	 * Return the form end tag and the submit button
	 *
	 * @param string $button_label
	 * @param string $name
	 * @param string $onclick
	 *
	 * @return null|string
	 */
	public static function end_form( $button_label = null, $name = 'submit', $onclick = null ) {
		if ( $button_label === null ) {
			$button_label = __( 'Save changes', 'google-analytics-for-wordpress' );
		}

		$output = null;
		$output .= '<div class="ga-form ga-form-input">';
		$output .= '<input type="submit" name="ga-form-' . $name . '" value="' . $button_label . '" class="button button-primary ga-form-submit" id="yoast-ga-form-submit-' . self::$form_namespace . '"';
		if ( ! is_null( $onclick ) ) {
			$output .= ' onclick="' . $onclick . '"';
		}
		$output .= ' />';
		$output .= '</div></form>';

		return $output;
	}


	/**
	 * Create a input form element with our labels and wrap them
	 *
	 * @param string      $type
	 * @param null|string $title
	 * @param null|string $name
	 * @param null|string $text_label
	 * @param null|string $description
	 *
	 * @return null|string
	 */
	public static function input( $type = 'text', $title = null, $name = null, $text_label = null, $description = null ) {
		$input = null;
		$id    = str_replace( '[', '-', $name );
		$id    = str_replace( ']', '', $id );

		$input_value = self::get_formfield_from_options( $name );

		$input .= '<div class="ga-form ga-form-input">';
		if ( ! is_null( $title ) ) {
			$input .= self::label( $id, $title, $type );
		}

		$attributes = array(
			'type'  => $type,
			'id'    => 'yoast-ga-form-' . $type . '-' . self::$form_namespace . '-' . $id . '',
			'name'  => $name,
			'class' => 'ga-form ga-form-' . $type . ' ',
		);

		if ( $type == 'checkbox' ) {
			$attributes['value'] = '1';

			if ( $input_value == 1 ) {
				$attributes['checked'] = 'checked';
			}
		}
		else {
			$attributes['value'] = esc_attr( stripslashes( $input_value ) );
		}

		$input .= '<input ' . self::parse_attributes( $attributes ) . ' />';

		// If we get a description, append it to this select field in a new row
		if ( ! is_null( $description ) ) {
			$input .= self::show_help( $id, $description );
		}

		if ( ! is_null( $text_label ) ) {
			$input .= '<label class="ga-form ga-form-' . $type . '-label" id="yoast-ga-form-label-' . $type . '-textlabel-' . self::$form_namespace . '-' . $id . '" for="yoast-ga-form-' . $type . '-' . self::$form_namespace . '-' . $id . '">' . $text_label . '</label>';
		}

		$input .= '</div>';

		return $input;
	}

	/**
	 * Generate a select box
	 *
	 * @param string      $title
	 * @param string      $name
	 * @param array       $values
	 * @param null|string $description
	 * @param bool        $multiple
	 * @param string      $empty_text
	 *
	 * @return null|string
	 */
	public static function select( $title, $name, $values, $description = null, $multiple = false, $empty_text = null ) {
		$select = null;
		$id     = str_replace( '[', '-', $name );
		$id     = str_replace( ']', '', $id );

		$select .= '<div class="ga-form ga-form-input">';
		if ( ! is_null( $title ) ) {
			$select .= self::label( $id, $title, 'select' ); // '<label class="ga-form ga-form-select-label ga-form-label-left" id="yoast-ga-form-label-select-' . self::$form_namespace . '-' . $id . '">' . $title . ':</label>';
		}

		if ( $multiple ) {
			$select .= '<select multiple name="' . $name . '[]" id="yoast-ga-form-select-' . self::$form_namespace . '-' . $id . '" class="ga-multiple">';
		}
		else {
			$select .= '<select data-placeholder="' . $empty_text . '" name="' . $name . '" id="yoast-ga-form-select-' . self::$form_namespace . '-' . $id . '">';
			if ( ! is_null( $empty_text ) ) {
				$select .= '<option></option>';
			}
		}
		if ( count( $values ) >= 1 ) {
			$select_value = self::get_formfield_from_options( $name );

			foreach ( $values as $optgroup => $value ) {
				if ( ! empty( $value['items'] ) ) {
					$select .= self::create_optgroup( $optgroup, $value, $select_value );
				}
				else {
					$select .= self::option( $select_value, $value );
				}
			}
		}
		$select .= '</select>';

		if ( ! is_null( $description ) ) {
			$select .= self::show_help( $id, $description );
		}

		$select .= '</div>';

		return $select;
	}


	/**
	 * Generate a textarea field
	 *
	 * @param string      $title
	 * @param string      $name
	 * @param null|string $description
	 *
	 * @return null|string
	 */
	public static function textarea( $title, $name, $description = null ) {
		$text = null;
		$id   = Yoast_GA_Options::instance()->option_prefix . '_' . $name;

		$textarea_value = self::get_formfield_from_options( $name );

		$text .= '<div class="ga-form ga-form-input">';

		if ( ! is_null( $title ) ) {
			$text .= '<label class="ga-form ga-form-select-label ga-form-label-left" id="yoast-ga-form-label-select-' . self::$form_namespace . '-' . $id . '">' . __( $title, 'google-analytics-for-wordpress' ) . ':</label>';
		}

		$text .= '<textarea rows="5" cols="60" name="' . $name . '" id="yoast-ga-form-textarea-' . self::$form_namespace . '-' . $id . '">' . stripslashes( $textarea_value ) . '</textarea>';

		if ( ! is_null( $description ) ) {
			$text .= self::show_help( $id, $description );
		}

		$text .= '</div>';

		return $text;
	}

	/**
	 * Parsing a option string for select
	 *
	 * @param string $select_value
	 * @param string $value
	 *
	 * @return string
	 */
	private static function option( $select_value, $value ) {

		if ( is_array( $select_value ) ) {
			if ( in_array( esc_attr( $value['id'] ), $select_value ) ) {
				return '<option value="' . esc_attr( $value['id'] ) . '" selected="selected">' . esc_attr( stripslashes( $value['name'] ) ) . '</option>';
			}
			else {
				return '<option value="' . esc_attr( $value['id'] ) . '">' . esc_attr( stripslashes( $value['name'] ) ) . '</option>';
			}
		}
		else {
			return '<option value="' . esc_attr( $value['id'] ) . '" ' . selected( $select_value, $value['id'], false ) . '>' . esc_attr( stripslashes( $value['name'] ) ) . '</option>';
		}
	}


	/**
	 * Show a question mark with help
	 *
	 * @param string $id
	 * @param string $description
	 *
	 * @return string
	 */
	public static function show_help( $id, $description ) {
		$help = '<img src="' . plugins_url( 'assets/img/question-mark.png', GAWP_FILE ) . '" class="alignleft yoast_help" id="' . esc_attr( $id . 'help' ) . '" alt="' . esc_attr( $description ) . '" />';

		return $help;
	}


	/**
	 * Will parse the optgroups.
	 *
	 * @param array $values
	 *
	 * @return array
	 */
	public static function parse_optgroups( $values ) {
		$optgroups = array();
		foreach ( $values as $key => $value ) {
			foreach ( $value['items'] as $subitem ) {
				$optgroups[ $subitem['name'] ]['items'] = $subitem['items'];
			}
		}

		return $optgroups;
	}

	/**
	 * Creates a label
	 *
	 * @param string $id
	 * @param string $title
	 * @param string $type
	 *
	 * @return string
	 */
	private static function label( $id, $title, $type ) {
		return '<label for="' . 'yoast-ga-form-' . $type . '-' . self::$form_namespace . '-' . $id . '" class="ga-form ga-form-' . $type . '-label ga-form-label-left" id="yoast-ga-form-label-' . $type . '-' . self::$form_namespace . '-' . $id . '">' . $title . ':</label>';
	}

	/**
	 * Creates a optgroup with the items. If items contain items it will create a nested optgroup
	 *
	 * @param string $optgroup
	 * @param array  $value
	 * @param array  $select_value
	 *
	 * @return string
	 */
	private static function create_optgroup( $optgroup, $value, $select_value ) {
		$optgroup = '<optgroup label="' . esc_attr( $optgroup ) . '">';

		foreach ( $value['items'] as $option ) {
			if ( ! empty( $option['items'] ) ) {

				$optgroup .= self::create_optgroup( esc_attr( $option['name'] ), $option, $select_value );
			}
			else {
				$optgroup .= self::option( $select_value, $option );
			}
		}

		$optgroup .= '</optgroup>';

		return $optgroup;
	}


	/**
	 * Getting the value from the option, if it doesn't exist return empty string
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	private static function get_formfield_from_options( $name ) {
		static $options;

		if ( $options === null ) {
			$options = Yoast_GA_Options::instance()->get_options();
		}

		// Catch a notice if the option doesn't exist, yet
		return ( isset( $options[ $name ] ) ) ? $options[ $name ] : '';
	}

	/**
	 * Parsing given array with attributes as an attribute string
	 *
	 * @param array $attributes_to_parse
	 *
	 * @return string
	 */
	private static function parse_attributes( $attributes_to_parse ) {
		$parsed_attributes = '';
		foreach ( $attributes_to_parse as $attribute_name => $attribute_value ) {
			$parsed_attributes .= $attribute_name . '="' . $attribute_value . '" ';
		}

		return trim( $parsed_attributes );
	}

}