<?php

namespace ATESO_ENG\Database;

class SearchQuery {

	/**
	 * Execute a dictionary search.
	 *
	 * @param string $q       Search query.
	 * @param string $letter  Filter by letter.
	 * @param string $pos     Filter by POS.
	 * @param int    $page    Page number.
	 * @param int    $per_page Results per page.
	 * @return array { results: array, total: int, pages: int }
	 */
	public function execute( $q = '', $letter = '', $pos = '', $page = 1, $per_page = 20 ) {
		global $wpdb;

		$term_table = Schema::terms_table();
		$def_table  = Schema::definitions_table();
		$offset     = ( $page - 1 ) * $per_page;

		// Build cache key.
		$cache_key = 'dict_search_' . md5( serialize( compact( 'q', 'letter', 'pos', 'page', 'per_page' ) ) );
		$cached    = wp_cache_get( $cache_key, 'ateso_dict' );
		if ( false !== $cached ) {
			return $cached;
		}

		$where_clauses = array( 't.parent_id IS NULL' );
		$params        = array();

		// Letter filter.
		if ( $letter ) {
			$where_clauses[] = 't.letter = %s';
			$params[]        = strtoupper( $letter );
		}

		// POS filter.
		if ( $pos ) {
			$where_clauses[] = 't.pos = %s';
			$params[]        = $pos;
		}

		if ( $q ) {
			$q_sanitized = sanitize_text_field( $q );

			if ( strlen( $q_sanitized ) < 4 ) {
				// Short search: use LIKE only (FULLTEXT min word length is usually 4).
				$like_word = $wpdb->esc_like( $q_sanitized ) . '%';
				$like_def  = '%' . $wpdb->esc_like( $q_sanitized ) . '%';

				$search_sql = "(t.word LIKE %s OR d.definition_text LIKE %s)";
				$params[]   = $like_word;
				$params[]   = $like_def;
				$where_clauses[] = $search_sql;

				$order_sql = $wpdb->prepare(
					"CASE
						WHEN t.word = %s THEN 0
						WHEN t.word LIKE %s THEN 1
						ELSE 2
					END, t.word ASC",
					$q_sanitized,
					$wpdb->esc_like( $q_sanitized ) . '%'
				);

				$where  = implode( ' AND ', $where_clauses );
				$sql    = "SELECT DISTINCT t.*, SUBSTRING(d.definition_text, 1, 150) AS definition_preview
					FROM {$term_table} t
					LEFT JOIN {$def_table} d ON d.term_id = t.id
					WHERE {$where}
					ORDER BY {$order_sql}
					LIMIT %d OFFSET %d";
				$params[] = $per_page;
				$params[] = $offset;

				$count_sql = "SELECT COUNT(DISTINCT t.id)
					FROM {$term_table} t
					LEFT JOIN {$def_table} d ON d.term_id = t.id
					WHERE {$where}";

			} else {
				// Longer search: use FULLTEXT + LIKE.
				$like_word = $wpdb->esc_like( $q_sanitized ) . '%';

				$search_sql = "(t.word LIKE %s OR MATCH(d.definition_text) AGAINST(%s IN NATURAL LANGUAGE MODE))";
				$params[]   = $like_word;
				$params[]   = $q_sanitized;
				$where_clauses[] = $search_sql;

				$order_sql = $wpdb->prepare(
					"CASE
						WHEN t.word = %s THEN 0
						WHEN t.word LIKE %s THEN 1
						ELSE 2
					END,
					MATCH(d.definition_text) AGAINST(%s IN NATURAL LANGUAGE MODE) DESC,
					t.word ASC",
					$q_sanitized,
					$wpdb->esc_like( $q_sanitized ) . '%',
					$q_sanitized
				);

				$where  = implode( ' AND ', $where_clauses );
				$sql    = "SELECT DISTINCT t.*, SUBSTRING(d.definition_text, 1, 150) AS definition_preview
					FROM {$term_table} t
					LEFT JOIN {$def_table} d ON d.term_id = t.id
					WHERE {$where}
					ORDER BY {$order_sql}
					LIMIT %d OFFSET %d";
				$params[] = $per_page;
				$params[] = $offset;

				$count_sql = "SELECT COUNT(DISTINCT t.id)
					FROM {$term_table} t
					LEFT JOIN {$def_table} d ON d.term_id = t.id
					WHERE {$where}";
			}

		} else {
			// No search query: browse mode.
			$where = implode( ' AND ', $where_clauses );

			$sql = "SELECT t.*, (SELECT SUBSTRING(d.definition_text, 1, 150) FROM {$def_table} d WHERE d.term_id = t.id ORDER BY d.sort_order ASC LIMIT 1) AS definition_preview
				FROM {$term_table} t
				WHERE {$where}
				ORDER BY t.word ASC, t.homonym_number ASC
				LIMIT %d OFFSET %d";
			$params[] = $per_page;
			$params[] = $offset;

			$count_sql = "SELECT COUNT(*) FROM {$term_table} t WHERE {$where}";
		}

		// Build the count params (same as main params minus LIMIT/OFFSET).
		$count_params = array_slice( $params, 0, -2 );

		// Execute.
		if ( ! empty( $params ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$results = $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$results = $wpdb->get_results( $sql );
		}

		if ( ! empty( $count_params ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$total = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $count_params ) );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$total = (int) $wpdb->get_var( $count_sql );
		}

		// For results without a definition_preview (browse mode already handled above),
		// fetch the first definition for each.
		foreach ( $results as $result ) {
			if ( empty( $result->definition_preview ) ) {
				$result->definition_preview = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT SUBSTRING(definition_text, 1, 150) FROM {$def_table} WHERE term_id = %d ORDER BY sort_order ASC LIMIT 1",
						$result->id
					)
				);
			}
		}

		$response = array(
			'results' => $results,
			'total'   => $total,
			'pages'   => (int) ceil( $total / $per_page ),
		);

		wp_cache_set( $cache_key, $response, 'ateso_dict', HOUR_IN_SECONDS );

		return $response;
	}
}
