<?php

namespace ATESO_ENG\Database;

class TermRepository {

	/**
	 * Find a term by ID.
	 */
	public function find_by_id( $id ) {
		global $wpdb;
		$table = Schema::terms_table();

		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id )
		);
	}

	/**
	 * Find a term by slug.
	 */
	public function find_by_slug( $slug ) {
		global $wpdb;
		$table = Schema::terms_table();

		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE slug = %s", $slug )
		);
	}

	/**
	 * Get a full entry with definitions, examples, and relations.
	 */
	public function get_full_entry( $id ) {
		$term = $this->find_by_id( $id );
		if ( ! $term ) {
			return null;
		}

		global $wpdb;
		$def_table  = Schema::definitions_table();
		$ex_table   = Schema::examples_table();
		$rel_table  = Schema::relations_table();
		$term_table = Schema::terms_table();

		// Definitions.
		$term->definitions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$def_table} WHERE term_id = %d ORDER BY sort_order ASC",
				$id
			)
		);

		// Examples.
		$term->examples = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$ex_table} WHERE term_id = %d ORDER BY sort_order ASC",
				$id
			)
		);

		// Relations with resolved term data.
		$term->relations = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT r.*, t.word AS resolved_word, t.slug AS resolved_slug, t.pos AS resolved_pos
				FROM {$rel_table} r
				LEFT JOIN {$term_table} t ON r.related_term_id = t.id
				WHERE r.term_id = %d",
				$id
			)
		);

		// Sub-entries.
		$term->sub_entries = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$term_table} WHERE parent_id = %d ORDER BY sort_order ASC",
				$id
			)
		);

		return $term;
	}

	/**
	 * Get full entry by slug.
	 */
	public function get_full_entry_by_slug( $slug ) {
		$term = $this->find_by_slug( $slug );
		if ( ! $term ) {
			return null;
		}
		return $this->get_full_entry( $term->id );
	}

	/**
	 * Browse terms by letter.
	 */
	public function browse_by_letter( $letter, $page = 1, $per_page = 20 ) {
		global $wpdb;
		$table  = Schema::terms_table();
		$offset = ( $page - 1 ) * $per_page;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE letter = %s AND parent_id IS NULL
				ORDER BY word ASC, homonym_number ASC
				LIMIT %d OFFSET %d",
				strtoupper( $letter ),
				$per_page,
				$offset
			)
		);

		$total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE letter = %s AND parent_id IS NULL",
				strtoupper( $letter )
			)
		);

		return array(
			'results' => $results,
			'total'   => (int) $total,
			'pages'   => ceil( $total / $per_page ),
		);
	}

	/**
	 * Get word counts grouped by letter.
	 */
	public function get_letter_counts() {
		global $wpdb;
		$table = Schema::terms_table();

		$cached = wp_cache_get( 'dict_letter_counts', 'ateso_dict' );
		if ( false !== $cached ) {
			return $cached;
		}

		$rows = $wpdb->get_results(
			"SELECT letter, COUNT(*) AS count FROM {$table}
			WHERE parent_id IS NULL AND letter != ''
			GROUP BY letter ORDER BY letter ASC"
		);

		$counts = array();
		foreach ( $rows as $row ) {
			$counts[ $row->letter ] = (int) $row->count;
		}

		wp_cache_set( 'dict_letter_counts', $counts, 'ateso_dict', DAY_IN_SECONDS );

		return $counts;
	}

	/**
	 * Get total term count.
	 */
	public function get_total_count() {
		global $wpdb;
		$table = Schema::terms_table();

		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	}

	/**
	 * Insert a new term.
	 */
	public function insert( $data ) {
		global $wpdb;
		$table = Schema::terms_table();

		$wpdb->insert(
			$table,
			array(
				'word'           => $data['word'],
				'slug'           => $data['slug'],
				'homonym_number' => $data['homonym_number'] ?? null,
				'plural'         => $data['plural'] ?? null,
				'pos'            => $data['pos'] ?? '',
				'pos_detail'     => $data['pos_detail'] ?? null,
				'gender'         => $data['gender'] ?? null,
				'dialect'        => $data['dialect'] ?? null,
				'verb_stem'      => $data['verb_stem'] ?? null,
				'letter'         => $data['letter'] ?? '',
				'usage_labels'   => $data['usage_labels'] ?? null,
				'parent_id'      => $data['parent_id'] ?? null,
				'sort_order'     => $data['sort_order'] ?? 0,
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d' )
		);

		$this->flush_cache();

		return $wpdb->insert_id;
	}

	/**
	 * Update a term.
	 */
	public function update( $id, $data ) {
		global $wpdb;
		$table = Schema::terms_table();

		$result = $wpdb->update(
			$table,
			$data,
			array( 'id' => $id ),
			null,
			array( '%d' )
		);

		$this->flush_cache();

		return false !== $result;
	}

	/**
	 * Delete a term and all related data.
	 */
	public function delete( $id ) {
		global $wpdb;

		// Delete related data first.
		$wpdb->delete( Schema::relations_table(), array( 'term_id' => $id ), array( '%d' ) );
		$wpdb->delete( Schema::examples_table(), array( 'term_id' => $id ), array( '%d' ) );
		$wpdb->delete( Schema::definitions_table(), array( 'term_id' => $id ), array( '%d' ) );

		// Delete sub-entries.
		$sub_entries = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT id FROM " . Schema::terms_table() . " WHERE parent_id = %d",
				$id
			)
		);
		foreach ( $sub_entries as $sub_id ) {
			$this->delete( $sub_id );
		}

		// Delete the term itself.
		$result = $wpdb->delete( Schema::terms_table(), array( 'id' => $id ), array( '%d' ) );

		$this->flush_cache();

		return false !== $result;
	}

	/**
	 * Get random terms for "related words" section.
	 */
	public function get_random_terms( $count = 6, $exclude_id = 0 ) {
		global $wpdb;
		$table = Schema::terms_table();
		$def_table = Schema::definitions_table();

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT t.*, (SELECT d.definition_text FROM {$def_table} d WHERE d.term_id = t.id ORDER BY d.sort_order ASC LIMIT 1) AS definition_preview
				FROM {$table} t
				WHERE t.id != %d AND t.parent_id IS NULL
				ORDER BY RAND()
				LIMIT %d",
				$exclude_id,
				$count
			)
		);
	}

	/**
	 * Flush dictionary cache.
	 */
	private function flush_cache() {
		wp_cache_delete( 'dict_letter_counts', 'ateso_dict' );
	}
}
