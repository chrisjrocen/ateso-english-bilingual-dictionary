<?php
/**
 * Word of the Day Gutenberg block.
 *
 * Displays a daily random Ateso word with its definition.
 * Uses WordPress transients for 24-hour caching.
 *
 * @package ATESO_ENG
 */

namespace ATESO_ENG\Blocks;

use ATESO_ENG\Base\BaseController;
use ATESO_ENG\Database\TermRepository;
use ATESO_ENG\Database\Schema;

class WordOfTheDay extends BaseController {

	/**
	 * Register the block on the init hook.
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Register the Gutenberg block using block.json metadata.
	 */
	public function register_block() {
		register_block_type(
			$this->plugin_path . 'blocks/word-of-the-day',
			array(
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Server-side render callback for the Word of the Day block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	public function render_block( $attributes ) {
		$show_plural = $attributes['showPlural'] ?? true;

		// Enqueue frontend JS for the modal (only when rendered).
		wp_enqueue_script(
			'ateso-wotd-frontend',
			$this->plugin_url . 'assets/js/word-of-the-day.js',
			array(),
			filemtime( $this->plugin_path . 'assets/js/word-of-the-day.js' ),
			true
		);

		wp_localize_script( 'ateso-wotd-frontend', 'atesoDictWotdConfig', array(
			'restUrl'       => rest_url( 'dictionary/v1' ),
			'nonce'         => wp_create_nonce( 'wp_rest' ),
			'dictionaryUrl' => home_url( '/dictionary/' ),
		) );

		// Get the Word of the Day data.
		$data = $this->get_wotd_data();

		ob_start();

		if ( ! $data ) {
			?>
			<div class="ateso-wotd-empty">
				<p>No words available yet. Import dictionary data to get started.</p>
			</div>
			<?php
			return ob_get_clean();
		}

		$word_display = esc_html( $data->word );
		if ( ! empty( $data->homonym_number ) ) {
			$word_display .= '<sup>' . esc_html( $data->homonym_number ) . '</sup>';
		}

		$word_url = esc_url( home_url( '/dictionary/' . $data->slug . '/' ) );

		// Truncate definition to 100 characters.
		$definition = $data->definition_preview ?? '';
		if ( mb_strlen( $definition ) > 100 ) {
			$definition = mb_substr( $definition, 0, 100 ) . '&hellip;';
		}
		?>
		<div class="ateso-wotd-card" data-slug="<?php echo esc_attr( $data->slug ); ?>">
			<span class="ateso-wotd-label"><?php esc_html_e( 'Word of the Day', 'ateso-eng-dictionary' ); ?></span>

			<h3 class="ateso-wotd-word">
				<a href="<?php echo $word_url; ?>"
				   class="ateso-wotd-word-link"
				   aria-label="<?php echo esc_attr( $data->word ); ?> — <?php esc_attr_e( 'view full entry', 'ateso-eng-dictionary' ); ?>"
				><?php echo $word_display; ?></a>
			</h3>

			<?php if ( $data->pos ) : ?>
				<span class="ateso-wotd-pos"><?php echo esc_html( $data->pos ); ?></span>
			<?php endif; ?>

			<?php if ( $definition ) : ?>
				<p class="ateso-wotd-def"><?php echo esc_html( $definition ); ?></p>
			<?php endif; ?>

			<?php if ( $show_plural && ! empty( $data->plural ) ) : ?>
				<p class="ateso-wotd-plural">
					<?php
					/* translators: %s: plural form of the word */
					printf( esc_html__( 'plural: %s', 'ateso-eng-dictionary' ), '<strong>' . esc_html( $data->plural ) . '</strong>' );
					?>
				</p>
			<?php endif; ?>

			<button type="button"
			        class="ateso-wotd-more"
			        data-slug="<?php echo esc_attr( $data->slug ); ?>"
			        aria-label="<?php echo esc_attr( $data->word ); ?> — <?php esc_attr_e( 'see full entry', 'ateso-eng-dictionary' ); ?>"
			><?php esc_html_e( 'See full entry &rarr;', 'ateso-eng-dictionary' ); ?></button>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Retrieve the Word of the Day, using a 24-hour transient cache.
	 *
	 * Stores only the term ID in the transient so that if the definition
	 * is updated during the day, the fresh text is always displayed.
	 *
	 * @return object|null Term object with definition_preview, or null.
	 */
	private function get_wotd_data() {
		$term_repo = new TermRepository();
		$term_id   = get_transient( 'ateso_dict_wotd' );

		if ( $term_id ) {
			$term = $term_repo->find_by_id( $term_id );
			if ( $term ) {
				// Fetch the first definition.
				$term->definition_preview = $this->get_first_definition( $term->id );
				return $term;
			}
			// Term was deleted — clear stale transient and pick a new one.
			delete_transient( 'ateso_dict_wotd' );
		}

		// Pick a new random term that has at least one definition.
		$term = $term_repo->get_random_term_with_definition();

		if ( ! $term ) {
			return null;
		}

		set_transient( 'ateso_dict_wotd', $term->id, DAY_IN_SECONDS );

		return $term;
	}

	/**
	 * Get the first definition text for a given term ID.
	 *
	 * @param int $term_id Term ID.
	 * @return string Definition text or empty string.
	 */
	private function get_first_definition( $term_id ) {
		global $wpdb;
		$def_table = Schema::definitions_table();

		$text = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT definition_text FROM {$def_table} WHERE term_id = %d ORDER BY sort_order ASC LIMIT 1",
				$term_id
			)
		);

		return $text ?: '';
	}
}
