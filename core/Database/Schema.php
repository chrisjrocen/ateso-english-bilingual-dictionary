<?php

namespace ATESO_ENG\Database;

use ATESO_ENG\Base\BaseController;

class Schema extends BaseController {

	const DB_VERSION = '1.0.0';
	const DB_VERSION_OPTION = 'ateso_dict_db_version';

	public function register() {
		add_action( 'admin_init', array( $this, 'check_version' ) );
	}

	/**
	 * Check if the database needs upgrading.
	 */
	public function check_version() {
		$installed_version = get_option( self::DB_VERSION_OPTION );
		if ( $installed_version !== self::DB_VERSION ) {
			self::create_tables();
		}
	}

	/**
	 * Get the terms table name.
	 */
	public static function terms_table() {
		global $wpdb;
		return $wpdb->prefix . 'dict_terms';
	}

	/**
	 * Get the definitions table name.
	 */
	public static function definitions_table() {
		global $wpdb;
		return $wpdb->prefix . 'dict_definitions';
	}

	/**
	 * Get the examples table name.
	 */
	public static function examples_table() {
		global $wpdb;
		return $wpdb->prefix . 'dict_examples';
	}

	/**
	 * Get the relations table name.
	 */
	public static function relations_table() {
		global $wpdb;
		return $wpdb->prefix . 'dict_relations';
	}

	/**
	 * Create all dictionary tables using dbDelta.
	 */
	public static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$terms_table       = self::terms_table();
		$definitions_table = self::definitions_table();
		$examples_table    = self::examples_table();
		$relations_table   = self::relations_table();

		// Terms table.
		$sql_terms = "CREATE TABLE {$terms_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			word varchar(191) NOT NULL,
			slug varchar(191) NOT NULL,
			homonym_number tinyint unsigned DEFAULT NULL,
			plural varchar(191) DEFAULT NULL,
			pos varchar(50) NOT NULL DEFAULT '',
			pos_detail varchar(100) DEFAULT NULL,
			gender char(3) DEFAULT NULL,
			dialect varchar(50) DEFAULT NULL,
			verb_stem varchar(20) DEFAULT NULL,
			letter char(1) NOT NULL DEFAULT '',
			usage_labels varchar(255) DEFAULT NULL,
			parent_id bigint(20) unsigned DEFAULT NULL,
			sort_order int unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY idx_word (word),
			KEY idx_slug (slug),
			KEY idx_letter (letter),
			KEY idx_pos (pos),
			KEY idx_parent_id (parent_id)
		) {$charset_collate};";

		// Definitions table.
		$sql_definitions = "CREATE TABLE {$definitions_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			term_id bigint(20) unsigned NOT NULL,
			definition_text text NOT NULL,
			sort_order tinyint unsigned NOT NULL DEFAULT 0,
			PRIMARY KEY  (id),
			KEY idx_term_id (term_id),
			FULLTEXT KEY idx_definition_text (definition_text)
		) {$charset_collate};";

		// Examples table.
		$sql_examples = "CREATE TABLE {$examples_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			term_id bigint(20) unsigned NOT NULL,
			definition_id bigint(20) unsigned DEFAULT NULL,
			ateso_text text NOT NULL,
			english_text text NOT NULL,
			sort_order tinyint unsigned NOT NULL DEFAULT 0,
			PRIMARY KEY  (id),
			KEY idx_term_id (term_id),
			KEY idx_definition_id (definition_id)
		) {$charset_collate};";

		// Relations table.
		$sql_relations = "CREATE TABLE {$relations_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			term_id bigint(20) unsigned NOT NULL,
			related_term_id bigint(20) unsigned DEFAULT NULL,
			related_word varchar(191) NOT NULL,
			relation_type varchar(20) NOT NULL DEFAULT 'cp',
			PRIMARY KEY  (id),
			KEY idx_term_id (term_id),
			KEY idx_related_term_id (related_term_id),
			KEY idx_relation_type (relation_type)
		) {$charset_collate};";

		dbDelta( $sql_terms );
		dbDelta( $sql_definitions );
		dbDelta( $sql_examples );
		dbDelta( $sql_relations );

		update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
	}

	/**
	 * Drop all dictionary tables. Only called on explicit uninstall.
	 */
	public static function drop_tables() {
		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DROP TABLE IF EXISTS " . self::relations_table() );
		$wpdb->query( "DROP TABLE IF EXISTS " . self::examples_table() );
		$wpdb->query( "DROP TABLE IF EXISTS " . self::definitions_table() );
		$wpdb->query( "DROP TABLE IF EXISTS " . self::terms_table() );
		// phpcs:enable

		delete_option( self::DB_VERSION_OPTION );
	}

	/**
	 * Truncate all dictionary tables (for re-import).
	 */
	public static function truncate_tables() {
		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "TRUNCATE TABLE " . self::relations_table() );
		$wpdb->query( "TRUNCATE TABLE " . self::examples_table() );
		$wpdb->query( "TRUNCATE TABLE " . self::definitions_table() );
		$wpdb->query( "TRUNCATE TABLE " . self::terms_table() );
		// phpcs:enable
	}
}
