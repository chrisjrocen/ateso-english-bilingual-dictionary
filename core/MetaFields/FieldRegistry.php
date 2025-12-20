<?php
/**
 * @package AtesoEngDictionary
 */

namespace ATESO_ENG\MetaFields;

use ATESO_ENG\Base\BaseController;

/**
 * Field Registry Class
 *
 * Centralized registration and management of all custom post meta fields
 * for the ateso-words custom post type.
 */
class FieldRegistry extends BaseController {

	/**
	 * Register the service
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_all_fields' ) );
	}

	/**
	 * Register all custom post meta fields
	 */
	public function register_all_fields() {
		$fields = $this->get_field_definitions();

		foreach ( $fields as $meta_key => $args ) {
			register_post_meta( 'ateso-words', $meta_key, $args );
		}
	}

	/**
	 * Get all field definitions
	 *
	 * @return array Field definitions with their registration arguments
	 */
	public function get_field_definitions() {
		return array(
			'homonym_number' => array(
				'type'              => 'string',
				'description'       => 'Homonym number for distinguishing words with identical spelling (e.g., 1, 2)',
				'show_in_rest'      => true,
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => '__return_true',
			),
			'plural_form' => array(
				'type'              => 'string',
				'description'       => 'Plural form of the word (e.g., aaweak, aicai)',
				'show_in_rest'      => true,
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => '__return_true',
			),
			'verb_stem' => array(
				'type'              => 'string',
				'description'       => 'Verb stem (e.g., ko-a, ko-o)',
				'show_in_rest'      => true,
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => '__return_true',
			),
			'part_of_speech_select' => array(
				'type'              => 'string',
				'description'       => 'Part of speech dropdown value',
				'show_in_rest'      => true,
				'single'            => true,
				'sanitize_callback' => array( FieldSanitizer::class, 'sanitize_part_of_speech' ),
				'auth_callback'     => '__return_true',
			),
			'gender' => array(
				'type'              => 'string',
				'description'       => 'Gender of noun (F for feminine, M for masculine, N/A)',
				'show_in_rest'      => true,
				'single'            => true,
				'sanitize_callback' => array( FieldSanitizer::class, 'sanitize_gender' ),
				'auth_callback'     => '__return_true',
			),
			'primary_definition' => array(
				'type'              => 'string',
				'description'       => 'Primary English translation/definition',
				'show_in_rest'      => true,
				'single'            => true,
				'sanitize_callback' => 'wp_kses_post',
				'auth_callback'     => '__return_true',
			),
			'secondary_definitions' => array(
				'type'              => 'string',
				'description'       => 'Additional meanings or translations (comma-separated or line-separated)',
				'show_in_rest'      => true,
				'single'            => true,
				'sanitize_callback' => 'sanitize_textarea_field',
				'auth_callback'     => '__return_true',
			),
			'dialect_marker' => array(
				'type'              => 'string',
				'description'       => 'Dialect notes (e.g., Usuk, outside Usuk)',
				'show_in_rest'      => true,
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => '__return_true',
			),
			'usage_context' => array(
				'type'              => 'string',
				'description'       => 'Usage context (e.g., transitive verb, intransitive verb, reflexive verb)',
				'show_in_rest'      => true,
				'single'            => true,
				'sanitize_callback' => array( FieldSanitizer::class, 'sanitize_usage_context' ),
				'auth_callback'     => '__return_true',
			),
			'example_sentences' => array(
				'type'              => 'string',
				'description'       => 'Example sentences in Ateso and English (stored as serialized array)',
				'show_in_rest'      => false, // Complex data structure
				'single'            => true,
				'sanitize_callback' => array( FieldSanitizer::class, 'sanitize_example_sentences' ),
				'auth_callback'     => '__return_true',
			),
			'cross_references' => array(
				'type'              => 'string',
				'description'       => 'Related words and cross-references (e.g., cp. ekacoan)',
				'show_in_rest'      => true,
				'single'            => true,
				'sanitize_callback' => 'sanitize_textarea_field',
				'auth_callback'     => '__return_true',
			),
			'idiomatic_phrases' => array(
				'type'              => 'string',
				'description'       => 'Idiomatic phrases and expressions',
				'show_in_rest'      => true,
				'single'            => true,
				'sanitize_callback' => 'sanitize_textarea_field',
				'auth_callback'     => '__return_true',
			),
			'notes' => array(
				'type'              => 'string',
				'description'       => 'Linguistic, cultural, or usage notes',
				'show_in_rest'      => false, // Admin-only field
				'single'            => true,
				'sanitize_callback' => 'wp_kses_post',
				'auth_callback'     => '__return_true',
			),
			'pronunciation' => array(
				'type'              => 'string',
				'description'       => 'Phonetic representation (e.g., IPA)',
				'show_in_rest'      => true,
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => '__return_true',
			),
			'etymology' => array(
				'type'              => 'string',
				'description'       => 'Word origin and history',
				'show_in_rest'      => true,
				'single'            => true,
				'sanitize_callback' => 'sanitize_textarea_field',
				'auth_callback'     => '__return_true',
			),
			'frequency' => array(
				'type'              => 'string',
				'description'       => 'Usage frequency (common, rare, archaic)',
				'show_in_rest'      => true,
				'single'            => true,
				'sanitize_callback' => array( FieldSanitizer::class, 'sanitize_frequency' ),
				'auth_callback'     => '__return_true',
			),
			'category_domain' => array(
				'type'              => 'string',
				'description'       => 'Category/domain tags (stored as comma-separated values)',
				'show_in_rest'      => true,
				'single'            => true,
				'sanitize_callback' => array( FieldSanitizer::class, 'sanitize_category_domain' ),
				'auth_callback'     => '__return_true',
			),
			'synonyms' => array(
				'type'              => 'string',
				'description'       => 'Ateso synonyms (comma-separated)',
				'show_in_rest'      => true,
				'single'            => true,
				'sanitize_callback' => 'sanitize_textarea_field',
				'auth_callback'     => '__return_true',
			),
			'antonyms' => array(
				'type'              => 'string',
				'description'       => 'Ateso antonyms (comma-separated)',
				'show_in_rest'      => true,
				'single'            => true,
				'sanitize_callback' => 'sanitize_textarea_field',
				'auth_callback'     => '__return_true',
			),
		);
	}

	/**
	 * Get array of all field keys
	 *
	 * @return array Field keys
	 */
	public function get_field_keys() {
		return array_keys( $this->get_field_definitions() );
	}

	/**
	 * Get field definition by key
	 *
	 * @param string $field_key The field key
	 * @return array|null Field definition or null if not found
	 */
	public function get_field_definition( $field_key ) {
		$definitions = $this->get_field_definitions();
		return isset( $definitions[ $field_key ] ) ? $definitions[ $field_key ] : null;
	}

	/**
	 * Check if a field key is valid
	 *
	 * @param string $field_key The field key to check
	 * @return bool True if valid, false otherwise
	 */
	public function is_valid_field( $field_key ) {
		return array_key_exists( $field_key, $this->get_field_definitions() );
	}
}
