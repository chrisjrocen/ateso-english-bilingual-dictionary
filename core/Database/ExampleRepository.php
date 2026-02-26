<?php

namespace ATESO_ENG\Database;

class ExampleRepository {

	/**
	 * Insert an example.
	 */
	public function insert( $data ) {
		global $wpdb;

		$wpdb->insert(
			Schema::examples_table(),
			array(
				'term_id'       => $data['term_id'],
				'definition_id' => $data['definition_id'] ?? null,
				'ateso_text'    => $data['ateso_text'],
				'english_text'  => $data['english_text'],
				'sort_order'    => $data['sort_order'] ?? 0,
			),
			array( '%d', '%d', '%s', '%s', '%d' )
		);

		return $wpdb->insert_id;
	}

	/**
	 * Get examples for a term.
	 */
	public function get_by_term_id( $term_id ) {
		global $wpdb;
		$table = Schema::examples_table();

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE term_id = %d ORDER BY sort_order ASC",
				$term_id
			)
		);
	}

	/**
	 * Delete all examples for a term.
	 */
	public function delete_by_term_id( $term_id ) {
		global $wpdb;

		return false !== $wpdb->delete(
			Schema::examples_table(),
			array( 'term_id' => $term_id ),
			array( '%d' )
		);
	}

	/**
	 * Bulk insert examples.
	 */
	public function bulk_insert( $rows ) {
		global $wpdb;
		$table = Schema::examples_table();

		if ( empty( $rows ) ) {
			return 0;
		}

		$values       = array();
		$placeholders = array();

		foreach ( $rows as $row ) {
			$def_id = ! empty( $row['definition_id'] ) ? absint( $row['definition_id'] ) : null;

			if ( $def_id ) {
				$placeholders[] = '(%d, %d, %s, %s, %d)';
				$values[]       = $row['term_id'];
				$values[]       = $def_id;
			} else {
				$placeholders[] = '(%d, NULL, %s, %s, %d)';
				$values[]       = $row['term_id'];
			}
			$values[] = $row['ateso_text'];
			$values[] = $row['english_text'];
			$values[] = $row['sort_order'] ?? 0;
		}

		$sql = "INSERT INTO {$table} (term_id, definition_id, ateso_text, english_text, sort_order) VALUES "
			. implode( ', ', $placeholders );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->query( $wpdb->prepare( $sql, $values ) );
	}
}
