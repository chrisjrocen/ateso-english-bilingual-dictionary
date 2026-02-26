<?php

namespace ATESO_ENG\Frontend;

use ATESO_ENG\Base\BaseController;
use ATESO_ENG\Database\TermRepository;

class SingleWord extends BaseController {

	public function register() {
		add_action( 'init', array( $this, 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_action( 'template_redirect', array( $this, 'render' ) );
	}

	public function add_rewrite_rules() {
		add_rewrite_rule(
			'^dictionary/([a-zA-Z0-9-]+)/?$',
			'index.php?dict_word=$matches[1]',
			'top'
		);
	}

	public function add_query_vars( $vars ) {
		$vars[] = 'dict_word';
		return $vars;
	}

	public function render() {
		$slug = get_query_var( 'dict_word' );
		if ( ! $slug ) {
			return;
		}

		$term_repo = new TermRepository();
		$entry     = $term_repo->get_full_entry_by_slug( sanitize_title( $slug ) );

		if ( ! $entry ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			return;
		}

		// Enqueue styles.
		wp_enqueue_style(
			'ateso-dict-frontend',
			$this->plugin_url . 'assets/css/dictionary-frontend.css',
			array(),
			filemtime( $this->plugin_path . 'assets/css/dictionary-frontend.css' )
		);

		// Get random related words.
		$related = $term_repo->get_random_terms( 6, $entry->id );

		// Render the page.
		get_header();
		$this->render_entry( $entry, $related );
		get_footer();
		exit;
	}

	private function render_entry( $entry, $related ) {
		$word_display = esc_html( $entry->word );
		if ( $entry->homonym_number ) {
			$word_display .= '<sup>' . esc_html( $entry->homonym_number ) . '</sup>';
		}

		$page_title = esc_html( $entry->word ) . ' - Ateso-English Dictionary';
		// Set the document title.
		add_filter( 'document_title_parts', function( $parts ) use ( $page_title ) {
			$parts['title'] = $page_title;
			return $parts;
		});
		?>
		<div class="ateso-dict-single">
			<div class="ateso-dict-single-header">
				<p class="ateso-dict-breadcrumb">
					Translation of <strong><?php echo esc_html( $entry->word ); ?></strong> &mdash; Ateso&ndash;English dictionary
				</p>
				<div class="ateso-dict-share-top">
					<?php $this->render_share_links( $entry ); ?>
				</div>
			</div>

			<div class="ateso-dict-single-content">
				<h1 class="ateso-dict-word-title"><?php echo $word_display; ?></h1>

				<?php if ( $entry->pos ) : ?>
					<p class="ateso-dict-pos-line">
						<em><?php echo esc_html( $entry->pos ); ?></em>
						<?php if ( $entry->pos_detail && $entry->pos_detail !== $entry->pos ) : ?>
							<span class="ateso-dict-pos-detail">[ <?php echo esc_html( $entry->pos_detail ); ?> ]</span>
						<?php endif; ?>
						<?php if ( $entry->gender && 'N/A' !== $entry->gender ) : ?>
							<span class="ateso-dict-gender-tag"><?php echo esc_html( $entry->gender ); ?></span>
						<?php endif; ?>
					</p>
				<?php endif; ?>

				<?php if ( $entry->verb_stem ) : ?>
					<p class="ateso-dict-verb-stem"><?php echo esc_html( $entry->verb_stem ); ?></p>
				<?php endif; ?>

				<?php if ( $entry->plural ) : ?>
					<p class="ateso-dict-plural">plural: <strong><?php echo esc_html( $entry->plural ); ?></strong></p>
				<?php endif; ?>

				<hr class="ateso-dict-divider">

				<?php
				// Definitions.
				if ( ! empty( $entry->definitions ) ) :
					foreach ( $entry->definitions as $i => $def ) :
						?>
						<div class="ateso-dict-definition-block">
							<?php if ( count( $entry->definitions ) > 1 ) : ?>
								<span class="ateso-dict-def-number"><?php echo esc_html( $i + 1 ); ?></span>
							<?php endif; ?>
							<p class="ateso-dict-def-text"><?php echo esc_html( $def->definition_text ); ?></p>
						</div>
						<?php
					endforeach;
				endif;
				?>

				<?php
				// Examples.
				if ( ! empty( $entry->examples ) ) :
					?>
					<div class="ateso-dict-examples">
						<?php foreach ( $entry->examples as $ex ) : ?>
							<div class="ateso-dict-example-item">
								<p class="ateso-dict-example-ateso">&bull; <?php echo esc_html( $ex->ateso_text ); ?></p>
								<p class="ateso-dict-example-english"><?php echo esc_html( $ex->english_text ); ?></p>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php
				// Cross-references.
				$cp_relations = array_filter( $entry->relations ?? array(), function( $r ) {
					return 'cp' === $r->relation_type;
				});
				if ( ! empty( $cp_relations ) ) :
					?>
					<div class="ateso-dict-cross-refs">
						<strong>See also:</strong>
						<?php
						$links = array();
						foreach ( $cp_relations as $rel ) {
							if ( $rel->resolved_slug ) {
								$links[] = '<a href="' . esc_url( home_url( '/dictionary/' . $rel->resolved_slug . '/' ) ) . '">'
									. esc_html( $rel->resolved_word ?: $rel->related_word ) . '</a>';
							} else {
								$links[] = esc_html( $rel->related_word );
							}
						}
						echo implode( ', ', $links );
						?>
					</div>
				<?php endif; ?>

				<?php
				// Quick notes.
				$has_notes = $entry->dialect || $entry->usage_labels;
				if ( $has_notes ) :
					?>
					<div class="ateso-dict-quick-notes">
						<h3>Quick Notes</h3>
						<ul>
							<?php if ( $entry->dialect ) : ?>
								<li><strong>Dialect:</strong> <?php echo esc_html( $entry->dialect ); ?></li>
							<?php endif; ?>
							<?php if ( $entry->usage_labels ) : ?>
								<li><strong>Usage:</strong> <?php echo esc_html( $entry->usage_labels ); ?></li>
							<?php endif; ?>
						</ul>
					</div>
				<?php endif; ?>

				<hr class="ateso-dict-divider">

				<div class="ateso-dict-share-bottom">
					<strong>Share this word:</strong>
					<?php $this->render_share_links( $entry ); ?>
				</div>

				<div class="ateso-dict-citation">
					<p><strong>Cite this entry:</strong></p>
					<code>"<?php echo esc_html( $entry->word ); ?>." Ateso-English Dictionary. Retrieved <?php echo esc_html( gmdate( 'F j, Y' ) ); ?>, from <?php echo esc_url( home_url( '/dictionary/' . $entry->slug . '/' ) ); ?></code>
				</div>
			</div>

			<?php if ( ! empty( $related ) ) : ?>
				<div class="ateso-dict-related">
					<h2>More Ateso Words</h2>
					<div class="ateso-dict-related-grid">
						<?php foreach ( $related as $rel ) : ?>
							<a href="<?php echo esc_url( home_url( '/dictionary/' . $rel->slug . '/' ) ); ?>" class="ateso-dict-related-card">
								<h3><?php echo esc_html( $rel->word ); ?></h3>
								<p><?php echo esc_html( wp_trim_words( $rel->definition_preview ?? '', 12 ) ); ?></p>
								<span class="ateso-dict-related-pos"><?php echo esc_html( $rel->pos ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	private function render_share_links( $entry ) {
		$url   = urlencode( home_url( '/dictionary/' . $entry->slug . '/' ) );
		$title = urlencode( $entry->word . ' - Ateso-English Dictionary' );
		?>
		<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $url; ?>" target="_blank" rel="noopener" class="ateso-dict-share-btn" aria-label="Share on Facebook">f</a>
		<a href="https://x.com/intent/tweet?url=<?php echo $url; ?>&text=<?php echo $title; ?>" target="_blank" rel="noopener" class="ateso-dict-share-btn" aria-label="Share on X">X</a>
		<a href="https://wa.me/?text=<?php echo $title . '%20' . $url; ?>" target="_blank" rel="noopener" class="ateso-dict-share-btn" aria-label="Share on WhatsApp">W</a>
		<a href="mailto:?subject=<?php echo $title; ?>&body=<?php echo $url; ?>" class="ateso-dict-share-btn" aria-label="Share by email">@</a>
		<?php
	}
}
