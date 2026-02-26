<?php

namespace ATESO_ENG\Database;

class DefinitionRepository {

	/**
	 * Insert a definition.
	 */
	public function insert( $data ) {
		global $wpdb;

		$wpdb->insert(
			Schema::definitions_table(),
			array(
				'term_id'         => $data['term_id'],
				'definition_text' => $data['definition_text'],
				'sort_order'      => $data['sort_order'] ?? 0,
			),
			array( '%d', '%s', '%d' )
		);

		return $wpdb->insert_id;
	}

	/**
	 * Get definitions for a term.
	 */
	public function get_by_term_id( $term_id ) {
		global $wpdb;
		$table = Schema::definitions_table();

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE term_id = %d ORDER BY sort_order ASC",
				$term_id
			)
		);
	}

	/**
	 * Update a definition.
	 */
	public function update( $id, $data ) {
		global $wpdb;

		return false !== $wpdb->update(
			Schema::definitions_table(),
			$data,
			array( 'id' => $id ),
			null,
			array( '%d' )
		);
	}

	/**
	 * Delete a definition.
	 */
	public function delete( $id ) {
		global $wpdb;

		return false !== $wpdb->delete(
			Schema::definitions_table(),
			array( 'id' => $id ),
			array( '%d' )
		);
	}

	/**
	 * Delete all definitions for a term.
	 */
	public function delete_by_term_id( $term_id ) {
		global $wpdb;

		return false !== $wpdb->delete(
			Schema::definitions_table(),
			array( 'term_id' => $term_id ),
			array( '%d' )
		);
	}

	/**
	 * Bulk insert definitions.
	 */
	public function bulk_insert( $rows ) {
		global $wpdb;
		$table = Schema::definitions_table();

		if ( empty( $rows ) ) {
			return 0;
		}

		$values      = array();
		$placeholders = array();

		foreach ( $rows as $row ) {
			$placeholders[] = '(%d, %s, %d)';
			$values[]       = $row['term_id'];
			$values[]       = $row['definition_text'];
			$values[]       = $row['sort_order'] ?? 0;
		}

		$sql = "INSERT INTO {$table} (term_id, definition_text, sort_order) VALUES "
			. implode( ', ', $placeholders );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->query( $wpdb->prepare( $sql, $values ) );
	}
}
