<?php
/**
 * Register New AtesoWords Post Type.
 * Uses native WordPress custom post meta (no ACF dependency).
 */

namespace ATESO_ENG\PostType;

use ATESO_ENG\Traits\PostType;
use ATESO_ENG\MetaFields\MetaBoxManager;

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
	protected $taxonomy_names_plural = array( 'Parts of Speech', 'Dialects' );

	/**
	 * Taxonomy Names Singular.
	 *
	 * @var array
	 */
	protected $taxonomy_names_singular = array( 'Part of Speech', 'Dialect' );

	/**
	 * Taxonomy Slugs.
	 *
	 * @var array
	 */
	protected $taxonomy_slugs = array( 'part_of_speech', 'dialect' );

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
	protected $enable_gutenberg_editor = true;

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
	protected $enable_hierarchical = true;

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
	 * Register the post type and initialize meta boxes.
	 */
	public function register() {
		$this->register_post_type();

		// Initialize meta box manager
		$meta_box_manager = new MetaBoxManager();
		$meta_box_manager->register();
	}
}
