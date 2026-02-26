<?php

namespace ATESO_ENG\Admin;

use ATESO_ENG\Database\Schema;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class ListTable extends \WP_List_Table {

	public function __construct() {
		parent::__construct( array(
			'singular' => 'dictionary_entry',
			'plural'   => 'dictionary_entries',
			'ajax'     => false,
		) );
	}

	public function get_columns() {
		return array(
			'cb'          => '<input type="checkbox" />',
			'word'        => 'Word',
			'pos'         => 'Part of Speech',
			'gender'      => 'Gender',
			'plural'      => 'Plural',
			'dialect'     => 'Dialect',
			'definitions' => 'Definitions',
		);
	}

	public function get_sortable_columns() {
		return array(
			'word' => array( 'word', true ),
			'pos'  => array( 'pos', false ),
		);
	}

	public function prepare_items() {
		global $wpdb;
		$table     = Schema::terms_table();
		$def_table = Schema::definitions_table();
		$per_page  = 50;
		$page      = $this->get_pagenum();
		$offset    = ( $page - 1 ) * $per_page;
		$search    = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
		$orderby   = isset( $_REQUEST['orderby'] ) ? sanitize_sql_orderby( $_REQUEST['orderby'] . ' ASC' ) : 'word ASC';

		// Sanitize orderby.
		$allowed_orderby = array( 'word', 'pos' );
		$orderby_col     = isset( $_REQUEST['orderby'] ) && in_array( $_REQUEST['orderby'], $allowed_orderby, true )
			? $_REQUEST['orderby'] : 'word';
		$order           = isset( $_REQUEST['order'] ) && 'desc' === strtolower( $_REQUEST['order'] ) ? 'DESC' : 'ASC';

		$where  = 'WHERE t.parent_id IS NULL';
		$params = array();

		if ( $search ) {
			$where   .= ' AND t.word LIKE %s';
			$params[] = '%' . $wpdb->esc_like( $search ) . '%';
		}

		if ( ! empty( $params ) ) {
			$sql = $wpdb->prepare(
				"SELECT t.*, (SELECT COUNT(*) FROM {$def_table} d WHERE d.term_id = t.id) AS def_count
				FROM {$table} t {$where}
				ORDER BY {$orderby_col} {$order}
				LIMIT %d OFFSET %d",
				array_merge( $params, array( $per_page, $offset ) )
			);
			$total = $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM {$table} t {$where}", $params )
			);
		} else {
			$sql = $wpdb->prepare(
				"SELECT t.*, (SELECT COUNT(*) FROM {$def_table} d WHERE d.term_id = t.id) AS def_count
				FROM {$table} t {$where}
				ORDER BY {$orderby_col} {$order}
				LIMIT %d OFFSET %d",
				$per_page,
				$offset
			);
			$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$table} t {$where}" );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$this->items = $wpdb->get_results( $sql );

		$this->set_pagination_args( array(
			'total_items' => (int) $total,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total / $per_page ),
		) );

		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);
	}

	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="entry_ids[]" value="%d" />', $item->id );
	}

	public function column_word( $item ) {
		$edit_url   = admin_url( 'admin.php?page=ateso-dict-add&id=' . $item->id );
		$delete_url = wp_nonce_url(
			admin_url( 'admin.php?page=ateso-dictionary&action=delete&id=' . $item->id ),
			'delete_entry_' . $item->id
		);

		$word = esc_html( $item->word );
		if ( $item->homonym_number ) {
			$word .= '<sup>' . esc_html( $item->homonym_number ) . '</sup>';
		}

		$actions = array(
			'edit'   => sprintf( '<a href="%s">Edit</a>', esc_url( $edit_url ) ),
			'delete' => sprintf( '<a href="%s" onclick="return confirm(\'Delete this entry?\')">Delete</a>', esc_url( $delete_url ) ),
		);

		return sprintf( '<strong><a href="%s">%s</a></strong>%s', esc_url( $edit_url ), $word, $this->row_actions( $actions ) );
	}

	public function column_pos( $item ) {
		$pos = esc_html( $item->pos );
		if ( $item->pos_detail && $item->pos_detail !== $item->pos ) {
			$pos .= ' <small>(' . esc_html( $item->pos_detail ) . ')</small>';
		}
		return $pos;
	}

	public function column_gender( $item ) {
		return esc_html( $item->gender ?? '-' );
	}

	public function column_plural( $item ) {
		return esc_html( $item->plural ?? '-' );
	}

	public function column_dialect( $item ) {
		return esc_html( $item->dialect ?? '-' );
	}

	public function column_definitions( $item ) {
		return (int) $item->def_count;
	}

	public function get_bulk_actions() {
		return array(
			'delete' => 'Delete',
		);
	}

	public function no_items() {
		echo 'No dictionary entries found. <a href="' . esc_url( admin_url( 'admin.php?page=ateso-dict-import' ) ) . '">Import data</a>.';
	}
}
