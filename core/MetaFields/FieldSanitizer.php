<?php
/**
 * @package AtesoEngDictionary
 */

namespace ATESO_ENG\MetaFields;

/**
 * Field Sanitizer Class
 *
 * Centralized sanitization for all custom field types.
 * Provides validation and sanitization methods for different field types.
 */
class FieldSanitizer {

	/**
	 * Sanitize part of speech field
	 *
	 * @param string $value The input value
	 * @return string Sanitized value
	 */
	public static function sanitize_part_of_speech( $value ) {
		$allowed_values = array(
			'noun',
			'verb',
			'adjective',
			'adverb',
			'preposition',
			'conjunction',
			'interjection',
			'other',
		);

		$value = sanitize_text_field( $value );
		return in_array( $value, $allowed_values, true ) ? $value : '';
	}

	/**
	 * Sanitize gender field
	 *
	 * @param string $value The input value
	 * @return string Sanitized value
	 */
	public static function sanitize_gender( $value ) {
		$allowed_values = array( 'F', 'M', 'N/A' );

		$value = sanitize_text_field( $value );
		return in_array( $value, $allowed_values, true ) ? $value : 'N/A';
	}

	/**
	 * Sanitize usage context field
	 *
	 * @param string $value The input value
	 * @return string Sanitized value
	 */
	public static function sanitize_usage_context( $value ) {
		$allowed_values = array(
			'transitive verb',
			'intransitive verb',
			'reflexive verb',
			'other',
		);

		$value = sanitize_text_field( $value );
		return in_array( $value, $allowed_values, true ) ? $value : '';
	}

	/**
	 * Sanitize frequency field
	 *
	 * @param string $value The input value
	 * @return string Sanitized value
	 */
	public static function sanitize_frequency( $value ) {
		$allowed_values = array( 'common', 'rare', 'archaic' );

		$value = sanitize_text_field( $value );
		return in_array( $value, $allowed_values, true ) ? $value : '';
	}

	/**
	 * Sanitize category/domain field
	 *
	 * @param mixed $value The input value (array or comma-separated string)
	 * @return string Sanitized comma-separated values
	 */
	public static function sanitize_category_domain( $value ) {
		$allowed_categories = array(
			'technology',
			'agriculture',
			'anatomy',
			'nature',
			'daily life',
			'religion',
			'culture',
			'family',
			'food',
			'animals',
			'plants',
			'weather',
			'other',
		);

		// Handle array input (from multi-select)
		if ( is_array( $value ) ) {
			$values = $value;
		} else {
			// Handle comma-separated string
			$values = array_map( 'trim', explode( ',', $value ) );
		}

		// Sanitize and validate each value
		$sanitized_values = array();
		foreach ( $values as $val ) {
			$val = sanitize_text_field( $val );
			if ( in_array( $val, $allowed_categories, true ) ) {
				$sanitized_values[] = $val;
			}
		}

		return implode( ', ', array_unique( $sanitized_values ) );
	}

	/**
	 * Sanitize example sentences (repeater field)
	 *
	 * @param mixed $value The input value (should be array)
	 * @return string Serialized array of sanitized example sentences
	 */
	public static function sanitize_example_sentences( $value ) {
		// If already serialized, unserialize first
		if ( is_string( $value ) && self::is_serialized( $value ) ) {
			$value = maybe_unserialize( $value );
		}

		// Ensure we have an array
		if ( ! is_array( $value ) ) {
			return serialize( array() );
		}

		$sanitized_examples = array();

		foreach ( $value as $example ) {
			if ( is_array( $example ) && isset( $example['ateso'] ) && isset( $example['english'] ) ) {
				// Only add if at least one field has content
				$ateso   = sanitize_textarea_field( $example['ateso'] );
				$english = sanitize_textarea_field( $example['english'] );

				if ( ! empty( $ateso ) || ! empty( $english ) ) {
					$sanitized_examples[] = array(
						'ateso'   => $ateso,
						'english' => $english,
					);
				}
			}
		}

		return serialize( $sanitized_examples );
	}

	/**
	 * Check if a value is serialized
	 *
	 * @param string $data Value to check
	 * @return bool True if serialized, false otherwise
	 */
	private static function is_serialized( $data ) {
		// If it isn't a string, it isn't serialized
		if ( ! is_string( $data ) ) {
			return false;
		}

		$data = trim( $data );
		if ( 'N;' === $data ) {
			return true;
		}

		if ( strlen( $data ) < 4 ) {
			return false;
		}

		if ( ':' !== $data[1] ) {
			return false;
		}

		$lastc = substr( $data, -1 );
		if ( ';' !== $lastc && '}' !== $lastc ) {
			return false;
		}

		$token = $data[0];
		switch ( $token ) {
			case 's':
				if ( '"' !== substr( $data, -2, 1 ) ) {
					return false;
				}
				// Intentional fall-through
			case 'a':
			case 'O':
				return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
			case 'b':
			case 'i':
			case 'd':
				$end = '';
				return (bool) preg_match( "/^{$token}:[0-9.E+-]+;{$end}$/", $data );
		}

		return false;
	}

	/**
	 * Sanitize a generic text field
	 *
	 * @param string $value The input value
	 * @return string Sanitized value
	 */
	public static function sanitize_text( $value ) {
		return sanitize_text_field( $value );
	}

	/**
	 * Sanitize a generic textarea field
	 *
	 * @param string $value The input value
	 * @return string Sanitized value
	 */
	public static function sanitize_textarea( $value ) {
		return sanitize_textarea_field( $value );
	}

	/**
	 * Sanitize rich text with allowed HTML
	 *
	 * @param string $value The input value
	 * @return string Sanitized value
	 */
	public static function sanitize_rich_text( $value ) {
		return wp_kses_post( $value );
	}
}
