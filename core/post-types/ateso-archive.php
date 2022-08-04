<?php
/**
 * Query and display Ateso Words
 *
 * @param array $atts Attributes passed from the Shortcode. Default empty.
 *
 * @return void
 */
function render_ateso_words_page($atts)
{
	$args = array(
		'post_type'   => 'ateso-words',
		'post_status' => 'publish',
		'orderby'     => 'title',
		'order'       => 'ASC',
	);

	// Query for Ateso Words.
	$the_query = new WP_Query($args);

	if ($the_query->have_posts()) {

		echo '<div class="main-container">';

		while ($the_query->have_posts()) {
			$the_query->the_post();

			loop_through_ateso_words();
	
		}
		
		echo '</div>';

	} else {
		printf('No books found');
	}
	/* Restore original Post Data */
	wp_reset_postdata();
}


/**
 * To display one Word
 *
 * @return void
 */
function loop_through_ateso_words() {

	echo '<article class="card-container"><h2>' . esc_attr(get_the_title()) . '</h2>';

	if (has_post_thumbnail()) {

		echo '<a href="">';
		
		the_post_thumbnail('medium', array(
			'class' => 'book-card',
			'alt'   => get_the_title()
		));

			//get_the_post_thumbnail(null, 'post-thumbnail', '');

			echo '</a>';
}

	the_excerpt();

	echo '<a href="' . get_permalink() .'">';
	
	echo '<button class="chrx-button">More</button></a>';
	
	echo '</article>';
}
