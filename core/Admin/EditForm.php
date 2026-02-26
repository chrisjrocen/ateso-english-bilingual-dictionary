<?php

namespace ATESO_ENG\Admin;

use ATESO_ENG\Base\BaseController;
use ATESO_ENG\Database\TermRepository;
use ATESO_ENG\Database\DefinitionRepository;
use ATESO_ENG\Database\ExampleRepository;
use ATESO_ENG\Database\RelationRepository;

class EditForm extends BaseController {

	public function register() {
		add_action( 'admin_post_ateso_dict_save_entry', array( $this, 'handle_save' ) );
	}

	public function render() {
		$id    = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$entry = null;

		if ( $id ) {
			$term_repo = new TermRepository();
			$entry     = $term_repo->get_full_entry( $id );
		}

		$is_edit = ! empty( $entry );
		$title   = $is_edit ? 'Edit Entry: ' . esc_html( $entry->word ) : 'Add New Entry';
		?>
		<div class="wrap">
			<h1><?php echo esc_html( $title ); ?></h1>

			<?php if ( ! empty( $_GET['updated'] ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p>Entry saved successfully.</p>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $_GET['error'] ) && 'missing_word' === $_GET['error'] ) : ?>
				<div class="notice notice-error is-dismissible">
					<p>The word field is required.</p>
				</div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="ateso_dict_save_entry">
				<input type="hidden" name="entry_id" value="<?php echo esc_attr( $id ); ?>">
				<?php wp_nonce_field( 'ateso_dict_save_entry', '_dict_nonce' ); ?>

				<table class="form-table">
					<tr>
						<th><label for="word">Word</label></th>
						<td><input type="text" name="word" id="word" class="regular-text" value="<?php echo esc_attr( $entry->word ?? '' ); ?>" required></td>
					</tr>
					<tr>
						<th><label for="homonym_number">Homonym Number</label></th>
						<td><input type="number" name="homonym_number" id="homonym_number" min="1" max="9" class="small-text" value="<?php echo esc_attr( $entry->homonym_number ?? '' ); ?>">
						<p class="description">For words with same spelling (e.g., ba1, ba2)</p></td>
					</tr>
					<tr>
						<th><label for="plural">Plural</label></th>
						<td><input type="text" name="plural" id="plural" class="regular-text" value="<?php echo esc_attr( $entry->plural ?? '' ); ?>"></td>
					</tr>
					<tr>
						<th><label for="pos">Part of Speech</label></th>
						<td>
							<select name="pos" id="pos">
								<option value="">-- Select --</option>
								<?php
								$pos_options = array( 'noun', 'verb', 'adjective', 'adverb', 'preposition', 'conjunction', 'interjection', 'pronoun', 'cardinal number', 'affix', 'other' );
								foreach ( $pos_options as $opt ) {
									$selected = ( $entry->pos ?? '' ) === $opt ? 'selected' : '';
									echo '<option value="' . esc_attr( $opt ) . '" ' . $selected . '>' . esc_html( ucfirst( $opt ) ) . '</option>';
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="pos_detail">POS Detail</label></th>
						<td><input type="text" name="pos_detail" id="pos_detail" class="regular-text" value="<?php echo esc_attr( $entry->pos_detail ?? '' ); ?>">
						<p class="description">E.g., transitive verb, causative verb, singular noun</p></td>
					</tr>
					<tr>
						<th>Gender</th>
						<td>
							<?php
							$gender = $entry->gender ?? '';
							foreach ( array( '' => 'None', 'F' => 'Feminine (F)', 'M' => 'Masculine (M)', 'N/A' => 'Neuter (N/A)' ) as $val => $label ) {
								$checked = $gender === $val ? 'checked' : '';
								echo '<label style="margin-right: 16px;"><input type="radio" name="gender" value="' . esc_attr( $val ) . '" ' . $checked . '> ' . esc_html( $label ) . '</label>';
							}
							?>
						</td>
					</tr>
					<tr>
						<th><label for="dialect">Dialect</label></th>
						<td>
							<select name="dialect" id="dialect">
								<option value="">-- None --</option>
								<?php
								foreach ( array( 'Usuk', 'outside Usuk', 'Ateso Atororo', 'Serere' ) as $d ) {
									$selected = ( $entry->dialect ?? '' ) === $d ? 'selected' : '';
									echo '<option value="' . esc_attr( $d ) . '" ' . $selected . '>' . esc_html( $d ) . '</option>';
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="verb_stem">Verb Stem</label></th>
						<td>
							<select name="verb_stem" id="verb_stem">
								<option value="">-- None --</option>
								<?php
								foreach ( array( 'ko-a', 'ko-o', 'ki-a', 'ki-o' ) as $s ) {
									$selected = ( $entry->verb_stem ?? '' ) === $s ? 'selected' : '';
									echo '<option value="' . esc_attr( $s ) . '" ' . $selected . '>' . esc_html( $s ) . '</option>';
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="usage_labels">Usage Labels</label></th>
						<td><input type="text" name="usage_labels" id="usage_labels" class="regular-text" value="<?php echo esc_attr( $entry->usage_labels ?? '' ); ?>">
						<p class="description">Comma-separated: jocular, archaic, figurative, etc.</p></td>
					</tr>
				</table>

				<h2>Definitions</h2>
				<div id="definitions-repeater">
					<?php
					$definitions = $is_edit ? $entry->definitions : array();
					if ( empty( $definitions ) ) {
						$definitions = array( (object) array( 'definition_text' => '' ) );
					}
					foreach ( $definitions as $i => $def ) :
						?>
						<div class="def-row" style="margin-bottom: 8px; display: flex; gap: 8px; align-items: start;">
							<span style="min-width: 24px; padding-top: 6px;"><?php echo esc_html( $i + 1 ); ?>.</span>
							<textarea name="definitions[]" rows="2" style="flex: 1;"><?php echo esc_textarea( $def->definition_text ?? '' ); ?></textarea>
							<button type="button" class="button remove-def" style="color: #b32d2e;">&times;</button>
						</div>
					<?php endforeach; ?>
				</div>
				<button type="button" id="add-definition" class="button">+ Add Definition</button>

				<h2>Examples</h2>
				<div id="examples-repeater">
					<?php
					$examples = $is_edit ? $entry->examples : array();
					if ( empty( $examples ) ) {
						$examples = array( (object) array( 'ateso_text' => '', 'english_text' => '' ) );
					}
					foreach ( $examples as $i => $ex ) :
						?>
						<div class="ex-row" style="margin-bottom: 12px; padding: 8px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
							<div style="display: flex; gap: 8px; margin-bottom: 4px;">
								<label style="min-width: 60px;">Ateso:</label>
								<input type="text" name="examples_ateso[]" class="regular-text" style="flex: 1;" value="<?php echo esc_attr( $ex->ateso_text ?? '' ); ?>">
							</div>
							<div style="display: flex; gap: 8px;">
								<label style="min-width: 60px;">English:</label>
								<input type="text" name="examples_english[]" class="regular-text" style="flex: 1;" value="<?php echo esc_attr( $ex->english_text ?? '' ); ?>">
							</div>
							<button type="button" class="button remove-ex" style="color: #b32d2e; margin-top: 4px;">Remove</button>
						</div>
					<?php endforeach; ?>
				</div>
				<button type="button" id="add-example" class="button">+ Add Example</button>

				<h2>Cross References</h2>
				<div id="refs-repeater">
					<?php
					$relations = $is_edit ? $entry->relations : array();
					if ( empty( $relations ) ) {
						$relations = array( (object) array( 'related_word' => '' ) );
					}
					foreach ( $relations as $rel ) :
						if ( empty( $rel->related_word ) ) continue;
						?>
						<div class="ref-row" style="margin-bottom: 4px; display: flex; gap: 8px;">
							<input type="text" name="cross_refs[]" class="regular-text" value="<?php echo esc_attr( $rel->related_word ?? '' ); ?>">
							<button type="button" class="button remove-ref" style="color: #b32d2e;">&times;</button>
						</div>
					<?php endforeach; ?>
				</div>
				<button type="button" id="add-ref" class="button">+ Add Cross Reference</button>

				<?php submit_button( $is_edit ? 'Update Entry' : 'Add Entry' ); ?>
			</form>
		</div>

		<script>
		(function() {
			// Add definition row.
			document.getElementById('add-definition').addEventListener('click', function() {
				const container = document.getElementById('definitions-repeater');
				const count = container.querySelectorAll('.def-row').length + 1;
				const div = document.createElement('div');
				div.className = 'def-row';
				div.style.cssText = 'margin-bottom: 8px; display: flex; gap: 8px; align-items: start;';
				div.innerHTML = '<span style="min-width: 24px; padding-top: 6px;">' + count + '.</span>'
					+ '<textarea name="definitions[]" rows="2" style="flex: 1;"></textarea>'
					+ '<button type="button" class="button remove-def" style="color: #b32d2e;">&times;</button>';
				container.appendChild(div);
			});

			// Add example row.
			document.getElementById('add-example').addEventListener('click', function() {
				const container = document.getElementById('examples-repeater');
				const div = document.createElement('div');
				div.className = 'ex-row';
				div.style.cssText = 'margin-bottom: 12px; padding: 8px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;';
				div.innerHTML = '<div style="display: flex; gap: 8px; margin-bottom: 4px;">'
					+ '<label style="min-width: 60px;">Ateso:</label>'
					+ '<input type="text" name="examples_ateso[]" class="regular-text" style="flex: 1;"></div>'
					+ '<div style="display: flex; gap: 8px;">'
					+ '<label style="min-width: 60px;">English:</label>'
					+ '<input type="text" name="examples_english[]" class="regular-text" style="flex: 1;"></div>'
					+ '<button type="button" class="button remove-ex" style="color: #b32d2e; margin-top: 4px;">Remove</button>';
				container.appendChild(div);
			});

			// Add cross ref row.
			document.getElementById('add-ref').addEventListener('click', function() {
				const container = document.getElementById('refs-repeater');
				const div = document.createElement('div');
				div.className = 'ref-row';
				div.style.cssText = 'margin-bottom: 4px; display: flex; gap: 8px;';
				div.innerHTML = '<input type="text" name="cross_refs[]" class="regular-text">'
					+ '<button type="button" class="button remove-ref" style="color: #b32d2e;">&times;</button>';
				container.appendChild(div);
			});

			// Delegate remove clicks.
			document.addEventListener('click', function(e) {
				if (e.target.classList.contains('remove-def') || e.target.classList.contains('remove-ex') || e.target.classList.contains('remove-ref')) {
					e.target.closest('.def-row, .ex-row, .ref-row').remove();
				}
			});
		})();
		</script>
		<?php
	}

	/**
	 * Handle form save.
	 */
	public function handle_save() {
		if ( ! check_admin_referer( 'ateso_dict_save_entry', '_dict_nonce' ) ) {
			wp_die( 'Security check failed.' );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized.' );
		}

		$id   = absint( $_POST['entry_id'] ?? 0 );
		$word = sanitize_text_field( $_POST['word'] ?? '' );

		if ( ! $word ) {
			wp_redirect( admin_url( 'admin.php?page=ateso-dict-add&error=missing_word' ) );
			exit;
		}

		$term_repo = new TermRepository();
		$def_repo  = new DefinitionRepository();
		$ex_repo   = new ExampleRepository();
		$rel_repo  = new RelationRepository();

		$homonym = ! empty( $_POST['homonym_number'] ) ? absint( $_POST['homonym_number'] ) : null;
		$slug    = sanitize_title( $word );
		if ( $homonym ) {
			$slug .= '-' . $homonym;
		}

		$first_alpha = '';
		for ( $i = 0; $i < strlen( $word ); $i++ ) {
			if ( ctype_alpha( $word[ $i ] ) ) {
				$first_alpha = strtoupper( $word[ $i ] );
				break;
			}
		}

		$term_data = array(
			'word'           => $word,
			'slug'           => $slug,
			'homonym_number' => $homonym,
			'plural'         => sanitize_text_field( $_POST['plural'] ?? '' ) ?: null,
			'pos'            => sanitize_text_field( $_POST['pos'] ?? '' ),
			'pos_detail'     => sanitize_text_field( $_POST['pos_detail'] ?? '' ) ?: null,
			'gender'         => sanitize_text_field( $_POST['gender'] ?? '' ) ?: null,
			'dialect'        => sanitize_text_field( $_POST['dialect'] ?? '' ) ?: null,
			'verb_stem'      => sanitize_text_field( $_POST['verb_stem'] ?? '' ) ?: null,
			'letter'         => $first_alpha,
			'usage_labels'   => sanitize_text_field( $_POST['usage_labels'] ?? '' ) ?: null,
		);

		if ( $id ) {
			$term_repo->update( $id, $term_data );
		} else {
			$id = $term_repo->insert( $term_data );
		}

		// Replace definitions.
		$def_repo->delete_by_term_id( $id );
		$definitions = $_POST['definitions'] ?? array();
		foreach ( $definitions as $i => $def_text ) {
			$def_text = sanitize_text_field( $def_text );
			if ( $def_text ) {
				$def_repo->insert( array(
					'term_id'         => $id,
					'definition_text' => $def_text,
					'sort_order'      => $i,
				) );
			}
		}

		// Replace examples.
		$ex_repo->delete_by_term_id( $id );
		$ateso_texts   = $_POST['examples_ateso'] ?? array();
		$english_texts = $_POST['examples_english'] ?? array();
		foreach ( $ateso_texts as $i => $ateso ) {
			$ateso   = sanitize_text_field( $ateso );
			$english = sanitize_text_field( $english_texts[ $i ] ?? '' );
			if ( $ateso && $english ) {
				$ex_repo->insert( array(
					'term_id'      => $id,
					'ateso_text'   => $ateso,
					'english_text' => $english,
					'sort_order'   => $i,
				) );
			}
		}

		// Replace cross references.
		$rel_repo->delete_by_term_id( $id );
		$cross_refs = $_POST['cross_refs'] ?? array();
		foreach ( $cross_refs as $ref ) {
			$ref = sanitize_text_field( $ref );
			if ( $ref ) {
				$rel_repo->insert( array(
					'term_id'       => $id,
					'related_word'  => $ref,
					'relation_type' => 'cp',
				) );
			}
		}

		wp_redirect( admin_url( 'admin.php?page=ateso-dict-add&id=' . $id . '&updated=1' ) );
		exit;
	}
}
