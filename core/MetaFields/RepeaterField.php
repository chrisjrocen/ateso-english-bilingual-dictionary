<?php
/**
 * @package AtesoEngDictionary
 */

namespace ATESO_ENG\MetaFields;

/**
 * Repeater Field Handler Class
 *
 * Handles rendering and processing of repeater fields,
 * specifically for paired Ateso/English example sentences.
 */
class RepeaterField {

	/**
	 * Render paired repeater field (Ateso/English examples)
	 *
	 * @param int    $post_id Post ID
	 * @param string $field_name Field name for the meta key
	 * @param string $ateso_label Label for Ateso field
	 * @param string $english_label Label for English field
	 */
	public static function render_paired_repeater( $post_id, $field_name, $ateso_label = 'Ateso', $english_label = 'English' ) {
		$examples = get_post_meta( $post_id, $field_name, true );

		// Unserialize if needed
		if ( is_string( $examples ) && ! empty( $examples ) ) {
			$examples = maybe_unserialize( $examples );
		}

		// Ensure we have an array
		if ( ! is_array( $examples ) || empty( $examples ) ) {
			$examples = array( array( 'ateso' => '', 'english' => '' ) );
		}

		?>
		<div class="ateso-repeater-field" data-field-name="<?php echo esc_attr( $field_name ); ?>">
			<div class="ateso-repeater-rows">
				<?php foreach ( $examples as $index => $example ) : ?>
					<?php
					self::render_repeater_row(
						$field_name,
						$index,
						isset( $example['ateso'] ) ? $example['ateso'] : '',
						isset( $example['english'] ) ? $example['english'] : '',
						$ateso_label,
						$english_label
					);
					?>
				<?php endforeach; ?>
			</div>

			<button type="button" class="button ateso-add-repeater-row">
				<?php esc_html_e( 'Add Example', 'ateso-eng-dictionary' ); ?>
			</button>

			<!-- Hidden template for new rows -->
			<script type="text/html" class="ateso-repeater-template">
				<?php
				self::render_repeater_row(
					$field_name,
					'{{INDEX}}',
					'',
					'',
					$ateso_label,
					$english_label,
					true
				);
				?>
			</script>
		</div>
		<?php
	}

	/**
	 * Render a single repeater row
	 *
	 * @param string $field_name Field name
	 * @param mixed  $index Row index
	 * @param string $ateso_value Ateso value
	 * @param string $english_value English value
	 * @param string $ateso_label Ateso label
	 * @param string $english_label English label
	 * @param bool   $is_template Whether this is a template row
	 */
	private static function render_repeater_row( $field_name, $index, $ateso_value = '', $english_value = '', $ateso_label = 'Ateso', $english_label = 'English', $is_template = false ) {
		?>
		<div class="ateso-repeater-row" data-index="<?php echo esc_attr( $index ); ?>">
			<div class="ateso-repeater-row-content">
				<div class="ateso-repeater-field-group">
					<label>
						<strong><?php echo esc_html( $ateso_label ); ?>:</strong>
						<textarea
							name="<?php echo esc_attr( $field_name ); ?>[<?php echo esc_attr( $index ); ?>][ateso]"
							rows="2"
							class="large-text"
							placeholder="<?php printf( esc_attr__( 'Enter %s text...', 'ateso-eng-dictionary' ), esc_attr( $ateso_label ) ); ?>"
						><?php echo esc_textarea( $ateso_value ); ?></textarea>
					</label>
				</div>

				<div class="ateso-repeater-field-group">
					<label>
						<strong><?php echo esc_html( $english_label ); ?>:</strong>
						<textarea
							name="<?php echo esc_attr( $field_name ); ?>[<?php echo esc_attr( $index ); ?>][english]"
							rows="2"
							class="large-text"
							placeholder="<?php printf( esc_attr__( 'Enter %s translation...', 'ateso-eng-dictionary' ), esc_attr( $english_label ) ); ?>"
						><?php echo esc_textarea( $english_value ); ?></textarea>
					</label>
				</div>
			</div>

			<div class="ateso-repeater-row-actions">
				<button type="button" class="button ateso-remove-repeater-row" title="<?php esc_attr_e( 'Remove this example', 'ateso-eng-dictionary' ); ?>">
					<span class="dashicons dashicons-no-alt"></span>
					<?php esc_html_e( 'Remove', 'ateso-eng-dictionary' ); ?>
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Process and save repeater field data from POST
	 *
	 * @param array  $post_data POST data array
	 * @param string $field_name Field name
	 * @return array Processed repeater data
	 */
	public static function process_repeater_data( $post_data, $field_name ) {
		if ( ! isset( $post_data[ $field_name ] ) || ! is_array( $post_data[ $field_name ] ) ) {
			return array();
		}

		$processed_data = array();

		foreach ( $post_data[ $field_name ] as $example ) {
			if ( ! is_array( $example ) ) {
				continue;
			}

			$ateso   = isset( $example['ateso'] ) ? sanitize_textarea_field( $example['ateso'] ) : '';
			$english = isset( $example['english'] ) ? sanitize_textarea_field( $example['english'] ) : '';

			// Only add if at least one field has content
			if ( ! empty( $ateso ) || ! empty( $english ) ) {
				$processed_data[] = array(
					'ateso'   => $ateso,
					'english' => $english,
				);
			}
		}

		return $processed_data;
	}

	/**
	 * Get repeater field value for display
	 *
	 * @param int    $post_id Post ID
	 * @param string $field_name Field name
	 * @return array Array of repeater items
	 */
	public static function get_repeater_value( $post_id, $field_name ) {
		$value = get_post_meta( $post_id, $field_name, true );

		// Unserialize if needed
		if ( is_string( $value ) && ! empty( $value ) ) {
			$value = maybe_unserialize( $value );
		}

		// Ensure we have an array
		if ( ! is_array( $value ) ) {
			return array();
		}

		return $value;
	}

	/**
	 * Display repeater field value on frontend
	 *
	 * @param int    $post_id Post ID
	 * @param string $field_name Field name
	 * @param string $wrapper_class Optional wrapper class
	 */
	public static function display_repeater_value( $post_id, $field_name, $wrapper_class = 'ateso-examples' ) {
		$examples = self::get_repeater_value( $post_id, $field_name );

		if ( empty( $examples ) ) {
			return;
		}

		echo '<div class="' . esc_attr( $wrapper_class ) . '">';

		foreach ( $examples as $index => $example ) {
			$ateso   = isset( $example['ateso'] ) ? $example['ateso'] : '';
			$english = isset( $example['english'] ) ? $example['english'] : '';

			if ( empty( $ateso ) && empty( $english ) ) {
				continue;
			}

			echo '<div class="ateso-example-item">';

			if ( ! empty( $ateso ) ) {
				echo '<div class="ateso-text">';
				echo '<strong>' . esc_html__( 'Ateso:', 'ateso-eng-dictionary' ) . '</strong> ';
				echo esc_html( $ateso );
				echo '</div>';
			}

			if ( ! empty( $english ) ) {
				echo '<div class="english-text">';
				echo '<strong>' . esc_html__( 'English:', 'ateso-eng-dictionary' ) . '</strong> ';
				echo esc_html( $english );
				echo '</div>';
			}

			echo '</div>';
		}

		echo '</div>';
	}
}
