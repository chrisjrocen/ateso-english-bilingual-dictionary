<?php

/**
 * Register Registration Form Block
 *
 * @package ATESO_ENG
 */

namespace ATESO_ENG\Blocks;

use ATESO_ENG\Base\BaseController;

/**
 * Handle all the blocks required for Registration Form.
 */
class AtesoWords extends BaseController {

	/**
	 * Register function is called by default to get the class running
	 *
	 * @return void
	 */
	public function register() {
			add_action( 'init', array( $this, 'register_block' ) );
			add_action( 'rest_api_init', array( $this, 'register_rest_route' ) );
	}

	/**
	 * Register rest route.
	 */
	public function register_rest_route() {
		register_rest_route(
			'ateso/v1',
			'/words',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_words' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Extract words from the database.
	 *
	 * @param Array $request Request body.
	 */
	public function get_words( $request ) {
		$offset = absint( $request->get_param( 'offset' ) );
		$limit  = 20;

		$args = array(
			'post_type'      => 'ateso-words',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'offset'         => $offset,
		);

		$query   = new \WP_Query( $args );
		$results = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$results[] = array(
					'title'   => get_the_title(),
					'link'    => get_permalink(),
					'meaning' => get_field( 'meaning' ),
				);
			}
			wp_reset_postdata();
		}

		return rest_ensure_response( $results );
	}

	/**
	 * Render callback for the Ateso Words block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public function render_archive( $attributes ) {
		ob_start();
		?>
		<div class="ateso-words-search-container">
			<input type="text" class="ateso-words-search" placeholder="Search Ateso word..." />
		</div>
		<div class="ateso-words-archive" data-offset="20">
			<?php
			$args  = array(
				'post_type'      => 'ateso-words',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'rand',
			);
			$query = new \WP_Query( $args );
			if ( $query->have_posts() ) :
				while ( $query->have_posts() ) :
					$query->the_post();
					echo sprintf(
						'<div class="ateso-word-card" data-title="%s">
                            <a href="%s">
                                <h3>%s</h3>
                                <p>%s</p>
                            </a>
                        </div>
                        ',
						esc_attr( get_the_title() ),
						esc_attr( get_permalink() ),
						esc_html( get_the_title() ),
						wp_kses_post( get_field( 'meaning' ) )
					);
				endwhile;
				wp_reset_postdata();
			endif;
			?>
		</div>
		<div class="ateso-words-loading">Loading...</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Register block function called by init hook
	 *
	 * @return void
	 */
	public function register_block() {
		register_block_type_from_metadata(
			$this->plugin_path . 'build/ateso-words/',
			array(
				'render_callback' => array( $this, 'render_archive' ),
			)
		);

		wp_register_script(
			'ateso-words-frontend',
			$this->plugin_url . 'assets/js/script.js',
			array(),
			'1.0',
			true
		);

		wp_enqueue_script( 'ateso-words-frontend' );

		wp_register_style(
			'ateso-words-base-css',
			$this->plugin_url . 'assets/build/css/base.css',
			array(),
			'1.0'
		);

		wp_enqueue_style( 'ateso-words-base-css' );
	}
}
