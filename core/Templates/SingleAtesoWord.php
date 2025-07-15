<?php
/**
 * Template for displaying a single Ateso word.
 *
 * @package ATESO_ENG
 */

namespace ATESO_ENG\Templates;

/**
 * Main class to handle the single template for ateso-words.
 */
class SingleAtesoWord {

	/**
	 * Register all hooks.
	 */
	public function register() {
		add_action( 'template_redirect', array( $this, 'render' ) );
	}

	/**
	 * Render single word.
	 */
	public function render() {
		if ( ! is_singular( 'ateso-words' ) ) {
			return;
		}

		get_header();

		echo '<div class="ateso-words-single-wrapper">';
		echo '<div class="ateso-words-archive">'; // Reuse archive class for layout.

		while ( have_posts() ) {
			the_post();
			$meaning       = get_field( 'meaning' );
			$link          = get_permalink();
			$title         = get_the_title();
			$url           = urlencode( $link );
			$encoded_title = urlencode( $title );

			echo sprintf(
				'<div class="ateso-word-card" data-title="%s" data-meaning="%s">
					<a href="%s">
						<h3>%s</h3>
						<p>%s</p>
					</a>
				</div>',
				esc_attr( $title ),
				esc_attr( $meaning ),
				esc_url( $link ),
				esc_html( $title ),
				wp_kses_post( $meaning )
			);

			$this->render_share_buttons( $url, $encoded_title );
		}

		echo '</div>';
		$this->render_related_words( get_the_ID() );
		echo '</div>';

		get_footer();
	}

	/**
	 * Render social sharing buttons
	 *
	 * @param string $post_url   The URL of the post to share.
	 * @param string $post_title The title of the post to share.
	 */
	private function render_share_buttons( $post_url, $post_title ) {
		$facebook_url = "https://www.facebook.com/sharer/sharer.php?u={$post_url}";
		$twitter_url  = "https://twitter.com/intent/tweet?url={$post_url}&text={$post_title}";
		$whatsapp_url = "https://api.whatsapp.com/send?text={$post_title}%20{$post_url}";
		$email_url    = "mailto:?subject={$post_title}&body={$post_url}";

		echo '<div class="job-share-buttons">';
		echo '<span>' . esc_html__( 'Share this word:', 'ateso-eng' ) . '</span>';
		echo '<button class="copy-link-button" data-url="' . esc_url( get_permalink() ) . '">' . esc_html__( 'Copy Link', 'ateso-eng' ) . '</button>';
		echo '<a href="' . esc_url( $facebook_url ) . '" target="_blank" rel="noopener noreferrer">Facebook</a>';
		echo '<a href="' . esc_url( $twitter_url ) . '" target="_blank" rel="noopener noreferrer">X</a>';
		echo '<a href="' . esc_url( $whatsapp_url ) . '" target="_blank" rel="noopener noreferrer">WhatsApp</a>';
		echo '<a href="' . esc_url( $email_url ) . '" target="_blank" rel="noopener noreferrer">Email</a>';
		echo '</div>';
	}

	/**
	 * Display related Ateso words
	 *
	 * @param int $current_post_id The ID of the current post to exclude from related words.
	 */
	private function render_related_words( $current_post_id ) {
		echo '<div class="related-ateso-words"><h3>' . esc_html__( 'More Words', 'ateso-eng' ) . '</h3>';

		$query = new \WP_Query(
			array(
				'post_type'      => 'ateso-words',
				'post__not_in'   => array( $current_post_id ),
				'posts_per_page' => 6,
				'orderby'        => 'rand',
			)
		);

		if ( $query->have_posts() ) {
			echo '<div class="ateso-words-archive">';
			while ( $query->have_posts() ) {
				$query->the_post();
				$related_title   = get_the_title();
				$related_link    = get_permalink();
				$related_meaning = get_field( 'meaning' );

				echo sprintf(
					'<div class="ateso-word-card" data-title="%s" data-meaning="%s">
						<a href="%s">
							<h3>%s</h3>
							<p>%s</p>
						</a>
					</div>',
					esc_attr( $related_title ),
					esc_attr( $related_meaning ),
					esc_url( $related_link ),
					esc_html( $related_title ),
					wp_kses_post( $related_meaning )
				);
			}
			echo '</div>';
			wp_reset_postdata();
		} else {
			echo '<p>' . esc_html__( 'No related words found.', 'ateso-eng' ) . '</p>';
		}

		echo '</div>';
	}
}
