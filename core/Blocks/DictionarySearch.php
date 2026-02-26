<?php

namespace ATESO_ENG\Blocks;

use ATESO_ENG\Base\BaseController;
use ATESO_ENG\Database\TermRepository;

class DictionarySearch extends BaseController {

	public function register() {
		add_action( 'init', array( $this, 'register_block' ) );
	}

	public function register_block() {
		register_block_type(
			$this->plugin_path . 'blocks/dictionary-search',
			array(
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Server-side render for the dictionary search block.
	 */
	public function render_block( $attributes ) {
		// Enqueue frontend assets.
		wp_enqueue_style(
			'ateso-dict-frontend',
			$this->plugin_url . 'assets/css/dictionary-frontend.css',
			array(),
			filemtime( $this->plugin_path . 'assets/css/dictionary-frontend.css' )
		);

		wp_enqueue_script(
			'ateso-dict-frontend',
			$this->plugin_url . 'assets/js/dictionary-frontend.js',
			array(),
			filemtime( $this->plugin_path . 'assets/js/dictionary-frontend.js' ),
			true
		);

		wp_localize_script( 'ateso-dict-frontend', 'atesoDictConfig', array(
			'restUrl'     => rest_url( 'dictionary/v1' ),
			'nonce'       => wp_create_nonce( 'wp_rest' ),
			'dictionaryUrl' => home_url( '/dictionary/' ),
		) );

		// Get letter counts for the alphabet bar.
		$term_repo     = new TermRepository();
		$letter_counts = $term_repo->get_letter_counts();

		ob_start();
		?>
		<div class="ateso-dictionary-app" data-rest-url="<?php echo esc_url( rest_url( 'dictionary/v1' ) ); ?>">
			<div class="ateso-dict-search-bar">
				<input
					type="text"
					class="ateso-dict-search-input"
					placeholder="Search Ateso or English..."
					autocomplete="off"
				/>
				<div class="ateso-dict-search-spinner" style="display: none;"></div>
			</div>

			<div class="ateso-dict-alphabet-bar">
				<?php
				$all_letters = range( 'A', 'Z' );
				foreach ( $all_letters as $letter ) :
					$count    = $letter_counts[ $letter ] ?? 0;
					$disabled = 0 === $count ? ' disabled' : '';
					?>
					<a href="#"
						class="ateso-dict-letter<?php echo $disabled; ?>"
						data-letter="<?php echo esc_attr( $letter ); ?>"
						<?php if ( 0 === $count ) echo 'aria-disabled="true"'; ?>
					>
						<?php echo esc_html( $letter ); ?>
						<?php if ( $count > 0 ) : ?>
							<span class="ateso-dict-letter-count"><?php echo esc_html( number_format( $count ) ); ?></span>
						<?php endif; ?>
					</a>
				<?php endforeach; ?>
			</div>

			<div class="ateso-dict-active-filter" style="display: none;">
				<span class="ateso-dict-filter-text"></span>
				<button type="button" class="ateso-dict-clear-filter">&times; Clear</button>
			</div>

			<div class="ateso-dict-results"></div>

			<div class="ateso-dict-pagination"></div>

			<div class="ateso-dict-loading" style="display: none;">
				<div class="ateso-dict-loading-spinner"></div>
				<p>Loading...</p>
			</div>

			<div class="ateso-dict-no-results" style="display: none;">
				<p>No results found. Try a different search term.</p>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
