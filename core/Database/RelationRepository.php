<?php

namespace ATESO_ENG\Database;

class RelationRepository {

	/**
	 * Insert a relation.
	 */
	public function insert( $data ) {
		global $wpdb;

		$wpdb->insert(
			Schema::relations_table(),
			array(
				'term_id'         => $data['term_id'],
				'related_term_id' => $data['related_term_id'] ?? null,
				'related_word'    => $data['related_word'],
				'relation_type'   => $data['relation_type'] ?? 'cp',
			),
			array( '%d', '%d', '%s', '%s' )
		);

		return $wpdb->insert_id;
	}

	/**
	 * Get relations for a term.
	 */
	public function get_by_term_id( $term_id ) {
		global $wpdb;
		$table      = Schema::relations_table();
		$term_table = Schema::terms_table();

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT r.*, t.slug AS resolved_slug, t.word AS resolved_word
				FROM {$table} r
				LEFT JOIN {$term_table} t ON r.related_term_id = t.id
				WHERE r.term_id = %d",
				$term_id
			)
		);
	}

	/**
	 * Delete all relations for a term.
	 */
	public function delete_by_term_id( $term_id ) {
		global $wpdb;

		return false !== $wpdb->delete(
			Schema::relations_table(),
			array( 'term_id' => $term_id ),
			array( '%d' )
		);
	}

	/**
	 * Bulk insert relations.
	 */
	public function bulk_insert( $rows ) {
		global $wpdb;
		$table = Schema::relations_table();

		if ( empty( $rows ) ) {
			return 0;
		}

		$values       = array();
		$placeholders = array();

		foreach ( $rows as $row ) {
			$placeholders[] = '(%d, %s, %s)';
			$values[]       = $row['term_id'];
			$values[]       = $row['related_word'];
			$values[]       = $row['relation_type'] ?? 'cp';
		}

		$sql = "INSERT INTO {$table} (term_id, related_word, relation_type) VALUES "
			. implode( ', ', $placeholders );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->query( $wpdb->prepare( $sql, $values ) );
	}

	/**
	 * Resolve relations by matching related_word to existing terms.
	 * Call this after a full import to populate related_term_id.
	 */
	public function resolve_relations() {
		global $wpdb;
		$rel_table  = Schema::relations_table();
		$term_table = Schema::terms_table();

		// Match related_word to term word (take first match if multiple homonyms).
		$sql = "UPDATE {$rel_table} r
			INNER JOIN (
				SELECT word, MIN(id) AS term_id
				FROM {$term_table}
				GROUP BY word
			) t ON r.related_word = t.word
			SET r.related_term_id = t.term_id
			WHERE r.related_term_id IS NULL";

		return $wpdb->query( $sql );
	}
}
