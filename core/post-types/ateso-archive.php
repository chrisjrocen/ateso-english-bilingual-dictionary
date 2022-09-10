<?php
/**
 * Query and display Ateso Words
 *
 * @param array $atts Attributes passed from the Shortcode. Default empty.
 *
 * @return void
 */

function render_ateso_words_page( $atts ) {
	$args = array(
		'post_type' => 'ateso-words',
		'post_status' => 'publish',
		'orderby' => 'title',
		'order' => 'ASC',
	);

	// Query for Ateso Words.
	$the_query = new WP_Query( $args );

	if ( $the_query->have_posts() ) {

		echo '<div class="grid-container">';

		while ( $the_query->have_posts() ) {
			
			$the_query->the_post();

			// Loop_through_ateso_words
			echo '<div class="grid-item"><a href="' . get_permalink() . '"><h3 class="word-title">' . get_the_title() . '</h3></a>';

			if ( get_field( 'meaning' ) ) {

				echo '<p class="word-meaning">' . the_field( 'meaning' ) . '</p>';
			}

			// if ( get_field( 'example' ) ) {

			// 	echo '<p class="example-title">Example: ';
				
			// 	echo '<span class="word-example">' . the_field( 'example' ) . '</span></p>';
			// }

			echo '<div class="more-link"><a href="' . get_permalink() . '">More</a></div></div>'; 

			// End Loop

		}

		echo '</div>';

	}
	else {
		printf('No books found');
	}
	/* Restore original Post Data */
	wp_reset_postdata();
}
