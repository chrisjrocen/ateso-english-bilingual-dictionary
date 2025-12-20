<?php
/**
 * @package AtesoEngDictionary
 */

namespace ATESO_ENG\MetaFields;

use ATESO_ENG\Base\BaseController;

/**
 * Meta Box Manager Class
 *
 * Registers and manages all custom meta boxes for the ateso-words post type.
 * Handles rendering of fields and saving meta data with proper security.
 */
class MetaBoxManager extends BaseController {

	/**
	 * Register the service
	 */
	public function register() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_ateso-words', array( $this, 'save_meta_boxes' ), 10, 2 );
	}

	/**
	 * Add all meta boxes
	 */
	public function add_meta_boxes() {
		// Primary Information (visible by default)
		add_meta_box(
			'ateso_primary_info',
			__( 'Primary Information', 'ateso-eng-dictionary' ),
			array( $this, 'render_primary_info_meta_box' ),
			'ateso-words',
			'normal',
			'high'
		);

		// Linguistic Details (collapsed)
		add_meta_box(
			'ateso_linguistic_details',
			__( 'Linguistic Details', 'ateso-eng-dictionary' ),
			array( $this, 'render_linguistic_details_meta_box' ),
			'ateso-words',
			'normal',
			'default'
		);

		// Additional Definitions (collapsed)
		add_meta_box(
			'ateso_additional_definitions',
			__( 'Additional Definitions', 'ateso-eng-dictionary' ),
			array( $this, 'render_additional_definitions_meta_box' ),
			'ateso-words',
			'normal',
			'default'
		);

		// Examples & Usage (collapsed)
		add_meta_box(
			'ateso_examples_usage',
			__( 'Examples & Usage', 'ateso-eng-dictionary' ),
			array( $this, 'render_examples_usage_meta_box' ),
			'ateso-words',
			'normal',
			'default'
		);

		// Related Information (collapsed)
		add_meta_box(
			'ateso_related_info',
			__( 'Related Information', 'ateso-eng-dictionary' ),
			array( $this, 'render_related_info_meta_box' ),
			'ateso-words',
			'normal',
			'default'
		);

		// Metadata (collapsed)
		add_meta_box(
			'ateso_metadata',
			__( 'Metadata', 'ateso-eng-dictionary' ),
			array( $this, 'render_metadata_meta_box' ),
			'ateso-words',
			'side',
			'default'
		);
	}

	/**
	 * Render Primary Information meta box
	 *
	 * @param \WP_Post $post Post object
	 */
	public function render_primary_info_meta_box( $post ) {
		wp_nonce_field( 'ateso_words_meta_box', 'ateso_words_nonce' );

		$homonym_number      = get_post_meta( $post->ID, 'homonym_number', true );
		$gender              = get_post_meta( $post->ID, 'gender', true );
		$primary_definition  = get_post_meta( $post->ID, 'primary_definition', true );
		$part_of_speech      = get_post_meta( $post->ID, 'part_of_speech_select', true );

		?>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="homonym_number"><?php esc_html_e( 'Homonym Number', 'ateso-eng-dictionary' ); ?></label>
				</th>
				<td>
					<input type="text" id="homonym_number" name="homonym_number" value="<?php echo esc_attr( $homonym_number ); ?>" class="regular-text" placeholder="1, 2, 3..." />
					<p class="description"><?php esc_html_e( 'For distinguishing words with identical spelling (e.g., 1, 2)', 'ateso-eng-dictionary' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="part_of_speech_select"><?php esc_html_e( 'Part of Speech', 'ateso-eng-dictionary' ); ?></label>
				</th>
				<td>
					<select id="part_of_speech_select" name="part_of_speech_select" class="regular-text">
						<option value=""><?php esc_html_e( '-- Select --', 'ateso-eng-dictionary' ); ?></option>
						<option value="noun" <?php selected( $part_of_speech, 'noun' ); ?>><?php esc_html_e( 'Noun', 'ateso-eng-dictionary' ); ?></option>
						<option value="verb" <?php selected( $part_of_speech, 'verb' ); ?>><?php esc_html_e( 'Verb', 'ateso-eng-dictionary' ); ?></option>
						<option value="adjective" <?php selected( $part_of_speech, 'adjective' ); ?>><?php esc_html_e( 'Adjective', 'ateso-eng-dictionary' ); ?></option>
						<option value="adverb" <?php selected( $part_of_speech, 'adverb' ); ?>><?php esc_html_e( 'Adverb', 'ateso-eng-dictionary' ); ?></option>
						<option value="preposition" <?php selected( $part_of_speech, 'preposition' ); ?>><?php esc_html_e( 'Preposition', 'ateso-eng-dictionary' ); ?></option>
						<option value="conjunction" <?php selected( $part_of_speech, 'conjunction' ); ?>><?php esc_html_e( 'Conjunction', 'ateso-eng-dictionary' ); ?></option>
						<option value="interjection" <?php selected( $part_of_speech, 'interjection' ); ?>><?php esc_html_e( 'Interjection', 'ateso-eng-dictionary' ); ?></option>
						<option value="other" <?php selected( $part_of_speech, 'other' ); ?>><?php esc_html_e( 'Other', 'ateso-eng-dictionary' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Grammatical category of the word', 'ateso-eng-dictionary' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label><?php esc_html_e( 'Gender', 'ateso-eng-dictionary' ); ?></label>
				</th>
				<td>
					<fieldset>
						<label>
							<input type="radio" name="gender" value="F" <?php checked( $gender, 'F' ); ?> />
							<?php esc_html_e( 'Feminine (F)', 'ateso-eng-dictionary' ); ?>
						</label><br/>
						<label>
							<input type="radio" name="gender" value="M" <?php checked( $gender, 'M' ); ?> />
							<?php esc_html_e( 'Masculine (M)', 'ateso-eng-dictionary' ); ?>
						</label><br/>
						<label>
							<input type="radio" name="gender" value="N/A" <?php checked( $gender, 'N/A' ); ?> <?php checked( $gender, '' ); ?> />
							<?php esc_html_e( 'Not Applicable (N/A)', 'ateso-eng-dictionary' ); ?>
						</label>
					</fieldset>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="primary_definition"><?php esc_html_e( 'Primary Definition', 'ateso-eng-dictionary' ); ?></label>
				</th>
				<td>
					<?php
					wp_editor(
						$primary_definition,
						'primary_definition',
						array(
							'textarea_name' => 'primary_definition',
							'textarea_rows' => 5,
							'media_buttons' => false,
							'teeny'         => true,
							'quicktags'     => false,
						)
					);
					?>
					<p class="description"><?php esc_html_e( 'Main English translation/definition', 'ateso-eng-dictionary' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render Linguistic Details meta box
	 *
	 * @param \WP_Post $post Post object
	 */
	public function render_linguistic_details_meta_box( $post ) {
		$plural_form    = get_post_meta( $post->ID, 'plural_form', true );
		$verb_stem      = get_post_meta( $post->ID, 'verb_stem', true );
		$usage_context  = get_post_meta( $post->ID, 'usage_context', true );
		$pronunciation  = get_post_meta( $post->ID, 'pronunciation', true );
		$etymology      = get_post_meta( $post->ID, 'etymology', true );

		?>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="plural_form"><?php esc_html_e( 'Plural Form', 'ateso-eng-dictionary' ); ?></label>
				</th>
				<td>
					<input type="text" id="plural_form" name="plural_form" value="<?php echo esc_attr( $plural_form ); ?>" class="large-text" placeholder="aaweak, aicai" />
					<p class="description"><?php esc_html_e( 'Plural form of the word', 'ateso-eng-dictionary' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="verb_stem"><?php esc_html_e( 'Verb Stem', 'ateso-eng-dictionary' ); ?></label>
				</th>
				<td>
					<input type="text" id="verb_stem" name="verb_stem" value="<?php echo esc_attr( $verb_stem ); ?>" class="large-text" placeholder="ko-a, ko-o" />
					<p class="description"><?php esc_html_e( 'Verb stem or root form', 'ateso-eng-dictionary' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="usage_context"><?php esc_html_e( 'Usage Context', 'ateso-eng-dictionary' ); ?></label>
				</th>
				<td>
					<select id="usage_context" name="usage_context" class="large-text">
						<option value=""><?php esc_html_e( '-- Select --', 'ateso-eng-dictionary' ); ?></option>
						<option value="transitive verb" <?php selected( $usage_context, 'transitive verb' ); ?>><?php esc_html_e( 'Transitive Verb', 'ateso-eng-dictionary' ); ?></option>
						<option value="intransitive verb" <?php selected( $usage_context, 'intransitive verb' ); ?>><?php esc_html_e( 'Intransitive Verb', 'ateso-eng-dictionary' ); ?></option>
						<option value="reflexive verb" <?php selected( $usage_context, 'reflexive verb' ); ?>><?php esc_html_e( 'Reflexive Verb', 'ateso-eng-dictionary' ); ?></option>
						<option value="other" <?php selected( $usage_context, 'other' ); ?>><?php esc_html_e( 'Other', 'ateso-eng-dictionary' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Context of word usage', 'ateso-eng-dictionary' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="pronunciation"><?php esc_html_e( 'Pronunciation', 'ateso-eng-dictionary' ); ?></label>
				</th>
				<td>
					<input type="text" id="pronunciation" name="pronunciation" value="<?php echo esc_attr( $pronunciation ); ?>" class="large-text" placeholder="IPA or phonetic representation" />
					<p class="description"><?php esc_html_e( 'Phonetic representation (e.g., IPA)', 'ateso-eng-dictionary' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="etymology"><?php esc_html_e( 'Etymology', 'ateso-eng-dictionary' ); ?></label>
				</th>
				<td>
					<textarea id="etymology" name="etymology" rows="3" class="large-text"><?php echo esc_textarea( $etymology ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Word origin and history', 'ateso-eng-dictionary' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render Additional Definitions meta box
	 *
	 * @param \WP_Post $post Post object
	 */
	public function render_additional_definitions_meta_box( $post ) {
		$secondary_definitions = get_post_meta( $post->ID, 'secondary_definitions', true );

		?>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="secondary_definitions"><?php esc_html_e( 'Secondary Definitions', 'ateso-eng-dictionary' ); ?></label>
				</th>
				<td>
					<textarea id="secondary_definitions" name="secondary_definitions" rows="5" class="large-text"><?php echo esc_textarea( $secondary_definitions ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Additional meanings or translations (one per line or comma-separated)', 'ateso-eng-dictionary' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render Examples & Usage meta box
	 *
	 * @param \WP_Post $post Post object
	 */
	public function render_examples_usage_meta_box( $post ) {
		$idiomatic_phrases = get_post_meta( $post->ID, 'idiomatic_phrases', true );

		?>
		<div class="ateso-examples-section">
			<h4><?php esc_html_e( 'Example Sentences', 'ateso-eng-dictionary' ); ?></h4>
			<?php RepeaterField::render_paired_repeater( $post->ID, 'example_sentences', 'Ateso', 'English' ); ?>
		</div>

		<hr style="margin: 20px 0;" />

		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="idiomatic_phrases"><?php esc_html_e( 'Idiomatic Phrases', 'ateso-eng-dictionary' ); ?></label>
				</th>
				<td>
					<textarea id="idiomatic_phrases" name="idiomatic_phrases" rows="4" class="large-text"><?php echo esc_textarea( $idiomatic_phrases ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Idiomatic phrases and expressions using this word', 'ateso-eng-dictionary' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render Related Information meta box
	 *
	 * @param \WP_Post $post Post object
	 */
	public function render_related_info_meta_box( $post ) {
		$cross_references = get_post_meta( $post->ID, 'cross_references', true );
		$synonyms         = get_post_meta( $post->ID, 'synonyms', true );
		$antonyms         = get_post_meta( $post->ID, 'antonyms', true );

		?>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="cross_references"><?php esc_html_e( 'Cross References', 'ateso-eng-dictionary' ); ?></label>
				</th>
				<td>
					<textarea id="cross_references" name="cross_references" rows="3" class="large-text"><?php echo esc_textarea( $cross_references ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Related words (e.g., cp. ekacoan)', 'ateso-eng-dictionary' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="synonyms"><?php esc_html_e( 'Synonyms', 'ateso-eng-dictionary' ); ?></label>
				</th>
				<td>
					<textarea id="synonyms" name="synonyms" rows="3" class="large-text"><?php echo esc_textarea( $synonyms ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Ateso synonyms (comma-separated)', 'ateso-eng-dictionary' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="antonyms"><?php esc_html_e( 'Antonyms', 'ateso-eng-dictionary' ); ?></label>
				</th>
				<td>
					<textarea id="antonyms" name="antonyms" rows="3" class="large-text"><?php echo esc_textarea( $antonyms ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Ateso antonyms (comma-separated)', 'ateso-eng-dictionary' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render Metadata meta box
	 *
	 * @param \WP_Post $post Post object
	 */
	public function render_metadata_meta_box( $post ) {
		$dialect_marker  = get_post_meta( $post->ID, 'dialect_marker', true );
		$frequency       = get_post_meta( $post->ID, 'frequency', true );
		$category_domain = get_post_meta( $post->ID, 'category_domain', true );
		$notes           = get_post_meta( $post->ID, 'notes', true );

		// Convert comma-separated string to array for multi-select
		$selected_categories = ! empty( $category_domain ) ? array_map( 'trim', explode( ',', $category_domain ) ) : array();

		?>
		<div class="ateso-metadata-fields">
			<p>
				<label for="dialect_marker"><strong><?php esc_html_e( 'Dialect Marker', 'ateso-eng-dictionary' ); ?></strong></label><br/>
				<input type="text" id="dialect_marker" name="dialect_marker" value="<?php echo esc_attr( $dialect_marker ); ?>" class="widefat" placeholder="Usuk, outside Usuk" />
				<span class="description"><?php esc_html_e( 'Dialect notes', 'ateso-eng-dictionary' ); ?></span>
			</p>

			<p>
				<label for="frequency"><strong><?php esc_html_e( 'Frequency', 'ateso-eng-dictionary' ); ?></strong></label><br/>
				<select id="frequency" name="frequency" class="widefat">
					<option value=""><?php esc_html_e( '-- Select --', 'ateso-eng-dictionary' ); ?></option>
					<option value="common" <?php selected( $frequency, 'common' ); ?>><?php esc_html_e( 'Common', 'ateso-eng-dictionary' ); ?></option>
					<option value="rare" <?php selected( $frequency, 'rare' ); ?>><?php esc_html_e( 'Rare', 'ateso-eng-dictionary' ); ?></option>
					<option value="archaic" <?php selected( $frequency, 'archaic' ); ?>><?php esc_html_e( 'Archaic', 'ateso-eng-dictionary' ); ?></option>
				</select>
			</p>

			<p>
				<label for="category_domain"><strong><?php esc_html_e( 'Category/Domain', 'ateso-eng-dictionary' ); ?></strong></label><br/>
				<select id="category_domain" name="category_domain[]" class="widefat" multiple size="6">
					<?php
					$categories = array( 'technology', 'agriculture', 'anatomy', 'nature', 'daily life', 'religion', 'culture', 'family', 'food', 'animals', 'plants', 'weather', 'other' );
					foreach ( $categories as $category ) {
						$selected = in_array( $category, $selected_categories, true ) ? 'selected' : '';
						echo '<option value="' . esc_attr( $category ) . '" ' . $selected . '>' . esc_html( ucfirst( $category ) ) . '</option>';
					}
					?>
				</select>
				<span class="description"><?php esc_html_e( 'Hold Ctrl/Cmd to select multiple', 'ateso-eng-dictionary' ); ?></span>
			</p>

			<p>
				<label for="notes"><strong><?php esc_html_e( 'Notes', 'ateso-eng-dictionary' ); ?></strong></label><br/>
				<textarea id="notes" name="notes" rows="5" class="widefat"><?php echo esc_textarea( $notes ); ?></textarea>
				<span class="description"><?php esc_html_e( 'Linguistic/cultural info', 'ateso-eng-dictionary' ); ?></span>
			</p>
		</div>
		<?php
	}

	/**
	 * Save all meta box data
	 *
	 * @param int      $post_id Post ID
	 * @param \WP_Post $post Post object
	 */
	public function save_meta_boxes( $post_id, $post ) {
		// Verify nonce
		if ( ! isset( $_POST['ateso_words_nonce'] ) || ! wp_verify_nonce( $_POST['ateso_words_nonce'], 'ateso_words_meta_box' ) ) {
			return;
		}

		// Check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check user permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save simple text fields
		$simple_fields = array(
			'homonym_number',
			'plural_form',
			'verb_stem',
			'part_of_speech_select',
			'gender',
			'dialect_marker',
			'usage_context',
			'pronunciation',
			'frequency',
		);

		foreach ( $simple_fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				$field_registry = new FieldRegistry();
				$field_def      = $field_registry->get_field_definition( $field );
				$callback       = isset( $field_def['sanitize_callback'] ) ? $field_def['sanitize_callback'] : 'sanitize_text_field';

				if ( is_array( $callback ) ) {
					$value = call_user_func( $callback, $_POST[ $field ] );
				} else {
					$value = call_user_func( $callback, $_POST[ $field ] );
				}

				update_post_meta( $post_id, $field, $value );
			}
		}

		// Save textarea fields
		$textarea_fields = array(
			'secondary_definitions',
			'etymology',
			'idiomatic_phrases',
			'cross_references',
			'synonyms',
			'antonyms',
			'notes',
		);

		foreach ( $textarea_fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				if ( 'notes' === $field ) {
					update_post_meta( $post_id, $field, wp_kses_post( $_POST[ $field ] ) );
				} else {
					update_post_meta( $post_id, $field, sanitize_textarea_field( $_POST[ $field ] ) );
				}
			}
		}

		// Save primary definition (rich text)
		if ( isset( $_POST['primary_definition'] ) ) {
			update_post_meta( $post_id, 'primary_definition', wp_kses_post( $_POST['primary_definition'] ) );
		}

		// Save category_domain (multi-select)
		if ( isset( $_POST['category_domain'] ) && is_array( $_POST['category_domain'] ) ) {
			$value = FieldSanitizer::sanitize_category_domain( $_POST['category_domain'] );
			update_post_meta( $post_id, 'category_domain', $value );
		} else {
			update_post_meta( $post_id, 'category_domain', '' );
		}

		// Save example sentences (repeater field)
		if ( isset( $_POST['example_sentences'] ) && is_array( $_POST['example_sentences'] ) ) {
			$processed_examples = RepeaterField::process_repeater_data( $_POST, 'example_sentences' );
			update_post_meta( $post_id, 'example_sentences', serialize( $processed_examples ) );
		} else {
			update_post_meta( $post_id, 'example_sentences', serialize( array() ) );
		}
	}
}
