<?php

namespace ATESO_ENG\Api;

use ATESO_ENG\Base\BaseController;
use ATESO_ENG\Database\TermRepository;
use ATESO_ENG\Database\SearchQuery;

class RestController extends BaseController {

	const NAMESPACE = 'dictionary/v1';

	public function register() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route(
			self::NAMESPACE,
			'/search',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'search' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'q'        => array(
						'type'              => 'string',
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'letter'   => array(
						'type'              => 'string',
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'pos'      => array(
						'type'              => 'string',
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'page'     => array(
						'type'    => 'integer',
						'default' => 1,
						'minimum' => 1,
					),
					'per_page' => array(
						'type'    => 'integer',
						'default' => 20,
						'minimum' => 1,
						'maximum' => 100,
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/word/(?P<slug>[a-zA-Z0-9-]+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_word' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/letters',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_letter_counts' ),
				'permission_callback' => '__return_true',
			)
		);

		// Word of the Day endpoints.
		register_rest_route(
			self::NAMESPACE,
			'/word-of-the-day',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_word_of_the_day' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/word-of-the-day/refresh',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'refresh_word_of_the_day' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}

	/**
	 * Search dictionary entries.
	 */
	public function search( $request ) {
		$search_query = new SearchQuery();

		$result = $search_query->execute(
			$request->get_param( 'q' ),
			$request->get_param( 'letter' ),
			$request->get_param( 'pos' ),
			$request->get_param( 'page' ),
			$request->get_param( 'per_page' )
		);

		$items = array();
		foreach ( $result['results'] as $term ) {
			$items[] = array(
				'id'                  => (int) $term->id,
				'word'                => $term->word,
				'slug'                => $term->slug,
				'homonym_number'      => $term->homonym_number ? (int) $term->homonym_number : null,
				'pos'                 => $term->pos,
				'pos_detail'          => $term->pos_detail,
				'gender'              => $term->gender,
				'plural'              => $term->plural,
				'dialect'             => $term->dialect,
				'verb_stem'           => $term->verb_stem,
				'definition_preview'  => $term->definition_preview ?? '',
				'url'                 => home_url( '/dictionary/' . $term->slug . '/' ),
			);
		}

		return new \WP_REST_Response(
			array(
				'results'      => $items,
				'total'        => $result['total'],
				'pages'        => $result['pages'],
				'current_page' => $request->get_param( 'page' ),
			),
			200
		);
	}

	/**
	 * Get a single word with full details.
	 */
	public function get_word( $request ) {
		$slug = sanitize_title( $request->get_param( 'slug' ) );

		$term_repo = new TermRepository();
		$entry     = $term_repo->get_full_entry_by_slug( $slug );

		if ( ! $entry ) {
			return new \WP_Error( 'not_found', 'Word not found.', array( 'status' => 404 ) );
		}

		$definitions = array();
		foreach ( $entry->definitions as $def ) {
			$definitions[] = array(
				'id'   => (int) $def->id,
				'text' => $def->definition_text,
			);
		}

		$examples = array();
		foreach ( $entry->examples as $ex ) {
			$examples[] = array(
				'ateso'   => $ex->ateso_text,
				'english' => $ex->english_text,
			);
		}

		$relations = array();
		foreach ( $entry->relations as $rel ) {
			$relations[] = array(
				'word'          => $rel->related_word,
				'type'          => $rel->relation_type,
				'slug'          => $rel->resolved_slug ?? null,
				'resolved_word' => $rel->resolved_word ?? null,
			);
		}

		return new \WP_REST_Response(
			array(
				'id'              => (int) $entry->id,
				'word'            => $entry->word,
				'slug'            => $entry->slug,
				'homonym_number'  => $entry->homonym_number ? (int) $entry->homonym_number : null,
				'plural'          => $entry->plural,
				'pos'             => $entry->pos,
				'pos_detail'      => $entry->pos_detail,
				'gender'          => $entry->gender,
				'dialect'         => $entry->dialect,
				'verb_stem'       => $entry->verb_stem,
				'usage_labels'    => $entry->usage_labels,
				'letter'          => $entry->letter,
				'definitions'     => $definitions,
				'examples'        => $examples,
				'relations'       => $relations,
				'url'             => home_url( '/dictionary/' . $entry->slug . '/' ),
			),
			200
		);
	}

	/**
	 * Get letter counts for the alphabet bar.
	 */
	public function get_letter_counts() {
		$term_repo = new TermRepository();
		$counts    = $term_repo->get_letter_counts();

		return new \WP_REST_Response( $counts, 200 );
	}

	/**
	 * Get the current Word of the Day.
	 */
	public function get_word_of_the_day() {
		$data = $this->resolve_wotd();

		if ( ! $data ) {
			return new \WP_Error( 'no_words', 'No dictionary entries with definitions found.', array( 'status' => 404 ) );
		}

		return new \WP_REST_Response( $data, 200 );
	}

	/**
	 * Refresh the Word of the Day (admin only).
	 */
	public function refresh_word_of_the_day() {
		delete_transient( 'ateso_dict_wotd' );

		$data = $this->resolve_wotd();

		if ( ! $data ) {
			return new \WP_Error( 'no_words', 'No dictionary entries with definitions found.', array( 'status' => 404 ) );
		}

		return new \WP_REST_Response( $data, 200 );
	}

	/**
	 * Resolve the Word of the Day: read from transient or pick a new one.
	 *
	 * @return array|null WOTD data array or null if no terms exist.
	 */
	private function resolve_wotd() {
		$term_repo = new TermRepository();
		$term_id   = get_transient( 'ateso_dict_wotd' );

		if ( $term_id ) {
			$term = $term_repo->find_by_id( $term_id );
			// Term may have been deleted — fall through if so.
			if ( $term ) {
				return $this->format_wotd( $term, $term_repo );
			}
			delete_transient( 'ateso_dict_wotd' );
		}

		// Pick a new random term with a definition.
		$term = $term_repo->get_random_term_with_definition();

		if ( ! $term ) {
			return null;
		}

		set_transient( 'ateso_dict_wotd', $term->id, DAY_IN_SECONDS );

		return $this->format_wotd( $term, $term_repo );
	}

	/**
	 * Format a term object into the WOTD response shape.
	 *
	 * @param object         $term      Term row object.
	 * @param TermRepository $term_repo Repository instance.
	 * @return array Formatted WOTD data.
	 */
	private function format_wotd( $term, $term_repo ) {
		global $wpdb;
		$def_table = \ATESO_ENG\Database\Schema::definitions_table();

		// Fetch first definition if not already available.
		$definition_preview = $term->definition_preview ?? null;
		if ( ! $definition_preview ) {
			$definition_preview = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT definition_text FROM {$def_table} WHERE term_id = %d ORDER BY sort_order ASC LIMIT 1",
					$term->id
				)
			);
		}

		return array(
			'id'                 => (int) $term->id,
			'word'               => $term->word,
			'slug'               => $term->slug,
			'homonym_number'     => $term->homonym_number ? (int) $term->homonym_number : null,
			'pos'                => $term->pos,
			'pos_detail'         => $term->pos_detail,
			'plural'             => $term->plural,
			'gender'             => $term->gender,
			'definition_preview' => $definition_preview ?? '',
			'url'                => home_url( '/dictionary/' . $term->slug . '/' ),
		);
	}
}
