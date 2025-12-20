<?php
/**
 * Template for displaying a single Ateso word.
 * Vocabulary.com-inspired design for dictionary entries.
 *
 * @package ATESO_ENG
 */

namespace ATESO_ENG\Templates;

use ATESO_ENG\MetaFields\RepeaterField;

/**
 * Main class to handle the single template for ateso-words.
 */
class SingleAtesoWord {

	/**
	 * Register all hooks.
	 */
	public function register() {
		add_action( 'template_redirect', array( $this, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Enqueue custom styles for single word template.
	 */
	public function enqueue_styles() {
		if ( is_singular( 'ateso-words' ) ) {
			wp_add_inline_style( 'wp-block-library', $this->get_custom_css() );
		}
	}

	/**
	 * Get custom CSS for the template.
	 *
	 * @return string CSS styles
	 */
	private function get_custom_css() {
		return '
		/* Ateso Dictionary Single Word Template Styles */
		.ateso-single-word-container {
			max-width: 800px;
			margin: 0 auto;
			padding: 40px 20px;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif;
			line-height: 1.6;
			color: #333;
		}

		/* Word Header */
		.ateso-word-header {
			margin-bottom: 30px;
			padding-bottom: 20px;
			border-bottom: 2px solid #e5e5e5;
		}

		.ateso-word-title {
			font-size: 48px;
			font-weight: 700;
			margin: 0 0 15px 0;
			color: #1a1a1a;
		}

		.ateso-word-actions {
			display: flex;
			gap: 20px;
			align-items: center;
		}

		.ateso-word-actions a {
			color: #0073aa;
			text-decoration: none;
			font-size: 14px;
			transition: color 0.2s;
		}

		.ateso-word-actions a:hover {
			color: #005177;
			text-decoration: underline;
		}

		/* Pronunciation */
		.ateso-pronunciation {
			font-size: 18px;
			color: #666;
			margin: 15px 0;
			font-style: italic;
		}

		.ateso-pronunciation .ipa-guide {
			font-size: 12px;
			margin-left: 10px;
			color: #0073aa;
		}

		/* Introductory Paragraph */
		.ateso-intro {
			font-size: 18px;
			line-height: 1.7;
			margin: 25px 0;
			padding: 20px;
			background-color: #f8f9fa;
			border-left: 4px solid #0073aa;
		}

		/* Definitions Section */
		.ateso-definitions {
			margin: 30px 0;
		}

		.ateso-definitions h2 {
			font-size: 28px;
			font-weight: 600;
			margin-bottom: 20px;
			color: #1a1a1a;
		}

		.ateso-definitions ol {
			list-style-type: decimal;
			padding-left: 30px;
			margin: 0;
		}

		.ateso-definitions ol li {
			margin-bottom: 15px;
			font-size: 16px;
		}

		.ateso-definitions .pos-tag {
			display: inline-block;
			background-color: #e8f4f8;
			color: #0073aa;
			padding: 2px 8px;
			border-radius: 3px;
			font-size: 13px;
			font-weight: 600;
			margin-right: 8px;
		}

		.ateso-definitions .gender-tag {
			display: inline-block;
			background-color: #f0f0f0;
			color: #666;
			padding: 2px 6px;
			border-radius: 3px;
			font-size: 12px;
			margin-right: 8px;
		}

		/* Examples Section */
		.ateso-examples {
			margin: 30px 0;
			padding: 25px;
			background-color: #fafbfc;
			border-radius: 5px;
		}

		.ateso-examples h2 {
			font-size: 24px;
			font-weight: 600;
			margin-bottom: 20px;
			color: #1a1a1a;
		}

		.ateso-examples ul {
			list-style-type: none;
			padding: 0;
			margin: 0;
		}

		.ateso-examples ul li {
			margin-bottom: 15px;
			padding: 10px;
			background-color: #fff;
			border-left: 3px solid #0073aa;
		}

		.ateso-examples .example-ateso {
			font-weight: 700;
			color: #1a1a1a;
			margin-bottom: 5px;
		}

		.ateso-examples .example-english {
			color: #555;
			font-style: italic;
		}

		/* Word Family / Related Words */
		.ateso-word-family {
			margin: 35px 0;
			padding: 25px;
			border: 2px solid #e5e5e5;
			border-radius: 5px;
			background-color: #fff;
		}

		.ateso-word-family h2 {
			font-size: 24px;
			font-weight: 600;
			margin-bottom: 20px;
			color: #1a1a1a;
		}

		.ateso-word-family h3 {
			font-size: 16px;
			font-weight: 600;
			margin: 15px 0 10px 0;
			color: #555;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}

		.ateso-word-family .family-content {
			margin-bottom: 15px;
			line-height: 1.8;
		}

		.ateso-word-family hr {
			border: none;
			border-top: 1px solid #e5e5e5;
			margin: 20px 0;
		}

		/* Quick Notes / Assessment Box */
		.ateso-quick-notes {
			margin: 30px 0;
			padding: 25px;
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			color: #fff;
			border-radius: 8px;
			box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
		}

		.ateso-quick-notes h2 {
			font-size: 22px;
			font-weight: 600;
			margin-bottom: 15px;
			color: #fff;
		}

		.ateso-quick-notes ul {
			list-style-type: none;
			padding: 0;
			margin: 0;
		}

		.ateso-quick-notes ul li {
			margin-bottom: 10px;
			padding-left: 20px;
			position: relative;
		}

		.ateso-quick-notes ul li:before {
			content: "▸";
			position: absolute;
			left: 0;
			color: #fff;
		}

		.ateso-quick-notes strong {
			font-weight: 600;
			color: #f0f0f0;
		}

		/* Citation Section */
		.ateso-citation {
			margin: 40px 0 20px 0;
			padding: 20px;
			background-color: #f5f5f5;
			border-radius: 5px;
			font-size: 14px;
			color: #666;
		}

		.ateso-citation h3 {
			font-size: 16px;
			font-weight: 600;
			margin-bottom: 10px;
			color: #333;
		}

		.ateso-citation pre {
			background-color: #fff;
			padding: 15px;
			border-left: 3px solid #0073aa;
			margin: 10px 0 0 0;
			white-space: pre-wrap;
			word-wrap: break-word;
			font-family: "Courier New", monospace;
			font-size: 13px;
			color: #333;
		}

		/* Share Buttons */
		.ateso-share-buttons {
			margin: 30px 0;
			padding: 20px;
			background-color: #f8f9fa;
			border-radius: 5px;
			text-align: center;
		}

		.ateso-share-buttons span {
			display: block;
			margin-bottom: 12px;
			font-weight: 600;
			color: #333;
		}

		.ateso-share-buttons a {
			display: inline-block;
			margin: 5px 8px;
			padding: 8px 16px;
			background-color: #0073aa;
			color: #fff;
			text-decoration: none;
			border-radius: 4px;
			font-size: 14px;
			transition: background-color 0.2s;
		}

		.ateso-share-buttons a:hover {
			background-color: #005177;
		}

		/* Related Words Grid */
		.ateso-related-words {
			margin: 40px 0;
		}

		.ateso-related-words h3 {
			font-size: 26px;
			font-weight: 600;
			margin-bottom: 20px;
			color: #1a1a1a;
		}

		.ateso-words-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
			gap: 20px;
		}

		.ateso-word-card {
			padding: 20px;
			background-color: #fff;
			border: 1px solid #e5e5e5;
			border-radius: 5px;
			transition: box-shadow 0.2s, transform 0.2s;
		}

		.ateso-word-card:hover {
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
			transform: translateY(-2px);
		}

		.ateso-word-card a {
			text-decoration: none;
			color: inherit;
		}

		.ateso-word-card h3 {
			font-size: 20px;
			font-weight: 600;
			margin: 0 0 10px 0;
			color: #0073aa;
		}

		.ateso-word-card p {
			margin: 0;
			font-size: 14px;
			color: #555;
			line-height: 1.5;
		}

		/* Responsive Design */
		@media (max-width: 768px) {
			.ateso-word-title {
				font-size: 36px;
			}

			.ateso-definitions h2,
			.ateso-examples h2,
			.ateso-word-family h2 {
				font-size: 22px;
			}

			.ateso-intro {
				font-size: 16px;
				padding: 15px;
			}

			.ateso-words-grid {
				grid-template-columns: 1fr;
			}

			.ateso-word-actions {
				flex-wrap: wrap;
			}
		}

		@media (max-width: 480px) {
			.ateso-single-word-container {
				padding: 20px 15px;
			}

			.ateso-word-title {
				font-size: 28px;
			}
		}
		';
	}

	/**
	 * Render single word.
	 */
	public function render() {
		if ( ! is_singular( 'ateso-words' ) ) {
			return;
		}

		get_header();

		while ( have_posts() ) {
			the_post();
			$post_id = get_the_ID();

			echo '<div class="ateso-single-word-container">';

			// Word Header Section
			$this->render_word_header( $post_id );

			// Pronunciation Section
			$this->render_pronunciation( $post_id );

			// Introductory Paragraph
			$this->render_intro_paragraph( $post_id );

			// Definitions Section
			$this->render_definitions( $post_id );

			// Examples Section
			$this->render_examples( $post_id );

			// Word Family / Related Words Section
			$this->render_word_family( $post_id );

			// Quick Notes / Assessment Box
			$this->render_quick_notes( $post_id );

			// Share Buttons
			$this->render_share_buttons( $post_id );

			// Citation Section
			$this->render_citation( $post_id );

			echo '</div>';

			// Related Words at the bottom
			$this->render_related_words( $post_id );
		}

		get_footer();
	}

	/**
	 * Render word header with title and actions.
	 *
	 * @param int $post_id Post ID.
	 */
	private function render_word_header( $post_id ) {
		$title = get_the_title();
		$homonym_number = get_post_meta( $post_id, 'homonym_number', true );

		echo '<div class="ateso-word-header">';
		echo '<h1 class="ateso-word-title">' . esc_html( $title );
		if ( ! empty( $homonym_number ) ) {
			echo '<sup>' . esc_html( $homonym_number ) . '</sup>';
		}
		echo '</h1>';

		echo '<div class="ateso-word-actions">';
		echo '<a href="#" class="add-to-list">' . esc_html__( 'Add to list', 'ateso-eng-dictionary' ) . '</a>';
		echo '<a href="#share" class="share-word">' . esc_html__( 'Share', 'ateso-eng-dictionary' ) . '</a>';
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Render pronunciation section.
	 *
	 * @param int $post_id Post ID.
	 */
	private function render_pronunciation( $post_id ) {
		$pronunciation = get_post_meta( $post_id, 'pronunciation', true );

		if ( ! empty( $pronunciation ) ) {
			echo '<div class="ateso-pronunciation">';
			echo esc_html( $pronunciation );
			echo '<a href="#" class="ipa-guide">[IPA guide]</a>';
			echo '</div>';
		}
	}

	/**
	 * Render introductory paragraph.
	 *
	 * @param int $post_id Post ID.
	 */
	private function render_intro_paragraph( $post_id ) {
		$title = get_the_title();
		$primary_definition = get_post_meta( $post_id, 'primary_definition', true );
		$part_of_speech = get_post_meta( $post_id, 'part_of_speech_select', true );
		$gender = get_post_meta( $post_id, 'gender', true );
		$plural_form = get_post_meta( $post_id, 'plural_form', true );
		$verb_stem = get_post_meta( $post_id, 'verb_stem', true );
		$usage_context = get_post_meta( $post_id, 'usage_context', true );

		if ( empty( $primary_definition ) ) {
			return;
		}

		echo '<div class="ateso-intro">';

		// Build introductory sentence
		$intro_parts = array();

		if ( ! empty( $part_of_speech ) ) {
			$article = in_array( strtolower( $part_of_speech ), array( 'adjective', 'adverb', 'interjection' ), true ) ? 'An' : 'A';
			$intro_parts[] = $article . ' <strong>' . esc_html( $part_of_speech ) . '</strong>';

			if ( ! empty( $gender ) && 'N/A' !== $gender ) {
				$intro_parts[] = '(' . esc_html( $gender === 'F' ? 'feminine' : 'masculine' ) . ')';
			}
		}

		$intro_parts[] = 'that means <em>' . wp_kses_post( $primary_definition ) . '</em>';

		if ( ! empty( $usage_context ) ) {
			$intro_parts[] = ', often used as <strong>' . esc_html( $usage_context ) . '</strong>';
		}

		if ( ! empty( $plural_form ) ) {
			$intro_parts[] = '. Plural: <strong>' . esc_html( $plural_form ) . '</strong>';
		}

		if ( ! empty( $verb_stem ) ) {
			$intro_parts[] = '. Verb stem: <strong>' . esc_html( $verb_stem ) . '</strong>';
		}

		echo implode( ' ', $intro_parts ) . '.';

		echo '</div>';
	}

	/**
	 * Render definitions section.
	 *
	 * @param int $post_id Post ID.
	 */
	private function render_definitions( $post_id ) {
		$title = get_the_title();
		$primary_definition = get_post_meta( $post_id, 'primary_definition', true );
		$secondary_definitions = get_post_meta( $post_id, 'secondary_definitions', true );
		$part_of_speech = get_post_meta( $post_id, 'part_of_speech_select', true );
		$gender = get_post_meta( $post_id, 'gender', true );

		if ( empty( $primary_definition ) ) {
			return;
		}

		echo '<div class="ateso-definitions">';
		echo '<h2>' . sprintf( esc_html__( 'Definitions of %s', 'ateso-eng-dictionary' ), esc_html( $title ) ) . '</h2>';
		echo '<ol>';

		// Primary definition
		echo '<li>';
		if ( ! empty( $part_of_speech ) ) {
			echo '<span class="pos-tag">' . esc_html( $part_of_speech ) . '</span>';
		}
		if ( ! empty( $gender ) && 'N/A' !== $gender ) {
			echo '<span class="gender-tag">' . esc_html( $gender ) . '</span>';
		}
		echo wp_kses_post( $primary_definition );
		echo '</li>';

		// Secondary definitions
		if ( ! empty( $secondary_definitions ) ) {
			// Split by semicolon or newline
			$secondary_list = preg_split( '/[;\n]+/', $secondary_definitions );
			foreach ( $secondary_list as $definition ) {
				$definition = trim( $definition );
				if ( ! empty( $definition ) ) {
					echo '<li>' . wp_kses_post( $definition ) . '</li>';
				}
			}
		}

		echo '</ol>';
		echo '</div>';
	}

	/**
	 * Render examples section.
	 *
	 * @param int $post_id Post ID.
	 */
	private function render_examples( $post_id ) {
		$title = get_the_title();
		$examples = RepeaterField::get_repeater_value( $post_id, 'example_sentences' );

		if ( empty( $examples ) ) {
			return;
		}

		echo '<div class="ateso-examples">';
		echo '<h2>' . esc_html__( 'Examples in Ateso and English', 'ateso-eng-dictionary' ) . '</h2>';
		echo '<ul>';

		foreach ( $examples as $example ) {
			$ateso = isset( $example['ateso'] ) ? trim( $example['ateso'] ) : '';
			$english = isset( $example['english'] ) ? trim( $example['english'] ) : '';

			if ( ! empty( $ateso ) || ! empty( $english ) ) {
				echo '<li>';
				if ( ! empty( $ateso ) ) {
					echo '<div class="example-ateso">' . esc_html( $ateso ) . '</div>';
				}
				if ( ! empty( $english ) ) {
					echo '<div class="example-english">' . esc_html( $english ) . '</div>';
				}
				echo '</li>';
			}
		}

		echo '</ul>';
		echo '</div>';
	}

	/**
	 * Render word family / related words section.
	 *
	 * @param int $post_id Post ID.
	 */
	private function render_word_family( $post_id ) {
		$synonyms = get_post_meta( $post_id, 'synonyms', true );
		$antonyms = get_post_meta( $post_id, 'antonyms', true );
		$cross_references = get_post_meta( $post_id, 'cross_references', true );
		$idiomatic_phrases = get_post_meta( $post_id, 'idiomatic_phrases', true );

		// Only show section if at least one field has content
		if ( empty( $synonyms ) && empty( $antonyms ) && empty( $cross_references ) && empty( $idiomatic_phrases ) ) {
			return;
		}

		echo '<div class="ateso-word-family">';
		echo '<h2>' . esc_html__( 'Word Family & Related Information', 'ateso-eng-dictionary' ) . '</h2>';

		if ( ! empty( $synonyms ) ) {
			echo '<h3>' . esc_html__( 'Synonyms', 'ateso-eng-dictionary' ) . '</h3>';
			echo '<div class="family-content">' . esc_html( $synonyms ) . '</div>';
		}

		if ( ! empty( $antonyms ) ) {
			echo '<h3>' . esc_html__( 'Antonyms', 'ateso-eng-dictionary' ) . '</h3>';
			echo '<div class="family-content">' . esc_html( $antonyms ) . '</div>';
		}

		if ( ! empty( $cross_references ) ) {
			echo '<h3>' . esc_html__( 'Cross References', 'ateso-eng-dictionary' ) . '</h3>';
			echo '<div class="family-content">' . esc_html( $cross_references ) . '</div>';
		}

		if ( ! empty( $idiomatic_phrases ) ) {
			echo '<hr>';
			echo '<h3>' . esc_html__( 'Idiomatic Phrases', 'ateso-eng-dictionary' ) . '</h3>';
			echo '<div class="family-content">' . wp_kses_post( nl2br( $idiomatic_phrases ) ) . '</div>';
		}

		echo '</div>';
	}

	/**
	 * Render quick notes / assessment box.
	 *
	 * @param int $post_id Post ID.
	 */
	private function render_quick_notes( $post_id ) {
		$frequency = get_post_meta( $post_id, 'frequency', true );
		$category_domain = get_post_meta( $post_id, 'category_domain', true );
		$dialect_marker = get_post_meta( $post_id, 'dialect_marker', true );
		$etymology = get_post_meta( $post_id, 'etymology', true );
		$notes = get_post_meta( $post_id, 'notes', true );

		// Only show section if at least one field has content
		if ( empty( $frequency ) && empty( $category_domain ) && empty( $dialect_marker ) && empty( $etymology ) && empty( $notes ) ) {
			return;
		}

		echo '<div class="ateso-quick-notes">';
		echo '<h2>' . esc_html__( 'Quick Notes', 'ateso-eng-dictionary' ) . '</h2>';
		echo '<ul>';

		if ( ! empty( $frequency ) ) {
			echo '<li><strong>' . esc_html__( 'Usage Frequency:', 'ateso-eng-dictionary' ) . '</strong> ' . esc_html( ucfirst( $frequency ) ) . '</li>';
		}

		if ( ! empty( $category_domain ) ) {
			echo '<li><strong>' . esc_html__( 'Category/Domain:', 'ateso-eng-dictionary' ) . '</strong> ' . esc_html( $category_domain ) . '</li>';
		}

		if ( ! empty( $dialect_marker ) ) {
			echo '<li><strong>' . esc_html__( 'Dialect:', 'ateso-eng-dictionary' ) . '</strong> ' . esc_html( $dialect_marker ) . '</li>';
		}

		if ( ! empty( $etymology ) ) {
			echo '<li><strong>' . esc_html__( 'Etymology:', 'ateso-eng-dictionary' ) . '</strong> ' . esc_html( $etymology ) . '</li>';
		}

		if ( ! empty( $notes ) ) {
			echo '<li><strong>' . esc_html__( 'Notes:', 'ateso-eng-dictionary' ) . '</strong> ' . wp_kses_post( $notes ) . '</li>';
		}

		echo '</ul>';
		echo '</div>';
	}

	/**
	 * Render share buttons.
	 *
	 * @param int $post_id Post ID.
	 */
	private function render_share_buttons( $post_id ) {
		$post_url = get_permalink( $post_id );
		$post_title = get_the_title( $post_id );
		$encoded_url = urlencode( $post_url );
		$encoded_title = urlencode( $post_title );

		$facebook_url = "https://www.facebook.com/sharer/sharer.php?u={$encoded_url}";
		$twitter_url = "https://twitter.com/intent/tweet?url={$encoded_url}&text={$encoded_title}";
		$whatsapp_url = "https://api.whatsapp.com/send?text={$encoded_title}%20{$encoded_url}";
		$email_url = "mailto:?subject={$encoded_title}&body={$encoded_url}";

		echo '<div id="share" class="ateso-share-buttons">';
		echo '<span>' . esc_html__( 'Share this word:', 'ateso-eng-dictionary' ) . '</span>';
		echo '<a href="' . esc_url( $facebook_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Facebook', 'ateso-eng-dictionary' ) . '</a>';
		echo '<a href="' . esc_url( $twitter_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'X (Twitter)', 'ateso-eng-dictionary' ) . '</a>';
		echo '<a href="' . esc_url( $whatsapp_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'WhatsApp', 'ateso-eng-dictionary' ) . '</a>';
		echo '<a href="' . esc_url( $email_url ) . '" rel="noopener noreferrer">' . esc_html__( 'Email', 'ateso-eng-dictionary' ) . '</a>';
		echo '</div>';
	}

	/**
	 * Render citation section.
	 *
	 * @param int $post_id Post ID.
	 */
	private function render_citation( $post_id ) {
		$title = get_the_title( $post_id );
		$post_url = get_permalink( $post_id );
		$current_date = date_i18n( 'F j, Y' );

		echo '<div class="ateso-citation">';
		echo '<h3>' . esc_html__( 'Cite this entry', 'ateso-eng-dictionary' ) . '</h3>';
		echo '<pre>';
		echo esc_html( $title ) . '. ';
		echo 'Ateso-English Bilingual Dictionary. ';
		echo esc_html( $post_url ) . '. ';
		echo 'Accessed ' . esc_html( $current_date ) . '.';
		echo '</pre>';
		echo '</div>';
	}

	/**
	 * Render related words section.
	 *
	 * @param int $current_post_id The ID of the current post to exclude from related words.
	 */
	private function render_related_words( $current_post_id ) {
		$query = new \WP_Query(
			array(
				'post_type'      => 'ateso-words',
				'post__not_in'   => array( $current_post_id ),
				'posts_per_page' => 6,
				'orderby'        => 'rand',
			)
		);

		if ( ! $query->have_posts() ) {
			return;
		}

		echo '<div class="ateso-single-word-container">';
		echo '<div class="ateso-related-words">';
		echo '<h3>' . esc_html__( 'More Ateso Words', 'ateso-eng-dictionary' ) . '</h3>';
		echo '<div class="ateso-words-grid">';

		while ( $query->have_posts() ) {
			$query->the_post();
			$related_title = get_the_title();
			$related_link = get_permalink();
			$related_meaning = get_post_meta( get_the_ID(), 'primary_definition', true );

			echo '<div class="ateso-word-card">';
			echo '<a href="' . esc_url( $related_link ) . '">';
			echo '<h3>' . esc_html( $related_title ) . '</h3>';
			echo '<p>' . wp_kses_post( wp_trim_words( $related_meaning, 15, '...' ) ) . '</p>';
			echo '</a>';
			echo '</div>';
		}

		echo '</div>'; // .ateso-words-grid
		echo '</div>'; // .ateso-related-words
		echo '</div>'; // .ateso-single-word-container

		wp_reset_postdata();
	}
}
