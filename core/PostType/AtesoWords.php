<?php
/**
 * Register New AtesoWords Post Type.
 * Add Fields via ACF Pro Plugin.
 */

namespace ATESO_ENG\PostType;

use ATESO_ENG\Traits\PostType;

/**
 * Class AtesoWords
 *
 * This class registers the AtesoWords post type and its associated taxonomy.
 */
class AtesoWords {
	use PostType;

	/**
	 * Post Type Name.
	 *
	 * @var string
	 */
	protected $post_type_name = 'ateso-words';

	/**
	 * Post Type Name Singular.
	 *
	 * @var string
	 */
	protected $post_type_name_single = 'Ateso Word';

	/**
	 * Post Type Slug.
	 *
	 * @var string
	 */
	protected $post_type_slug = 'ateso-words';

	/**
	 * Taxonomy Names.
	 *
	 * @var array
	 */
	protected $taxonomy_names_plural = array( 'Parts of Speech' );

	/**
	 * Taxonomy Names Singular.
	 *
	 * @var array
	 */
	protected $taxonomy_names_singular = array( 'Part of Speech' );

	/**
	 * Taxonomy Slugs.
	 *
	 * @var array
	 */
	protected $taxonomy_slugs = array( 'part_of_speech' );

	/**
	 * Taxonomy Slug for AtesoWords.
	 *
	 * @var string
	 */
	protected $taxonomy_slug = 'part_of_speech';

	/**
	 * Enable Archives.
	 *
	 * @var bool
	 */
	protected $enable_archives = true;

	/**
	 * Enable Gutenberg Editor.
	 *
	 * @var bool
	 */
	protected $enable_gutenberg_editor = false;

	/**
	 * Menu Position.
	 *
	 * @var int
	 */
	protected $menu_position = 16;

	/**
	 * Menu Icon.
	 *
	 * @var string
	 */
	protected $menu_icon = 'dashicons-businessperson';

	/**
	 * Enable Hierarchical Structure.
	 *
	 * @var bool
	 */
	protected $enable_hierarchical = false;

	/**
	 * Enable Detail Pages.
	 *
	 * @var bool
	 */
	protected $enable_detail_pages = true;

	/**
	 * Enable Taxonomy Page.
	 *
	 * @var bool
	 */
	protected $enable_taxonomy_page = true;

	/**
	 * Enable Taxonomy.
	 *
	 * @var bool
	 */
	protected $enable_taxonomy = true;

	/**
	 * Constructor to register the AtesoWords post type and taxonomy.
	 */
	public function __construct() {
		$this->register_post_type();
	}
}
