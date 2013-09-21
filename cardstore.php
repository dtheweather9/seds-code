<?php


function create_post_type_sedscard() {
	register_post_type( 'seds_cardrecord',
		array(
			'labels' => array(
				'name' => __( 'Card Job Prints' ),
				'singular_name' => __( 'Card Print Job' )
			),
			'description' => "Post type for controlling print jobs",
			'public' => false,
			'exclude_from_search' => false,
			'has_archive' => true,
			'rewrite' => array('slug' => 'products'),
			'show_ui' => true,
			'supports' => array( 'title', 'editor', 'comments', 'custom-fields' ),
		)
	);
}
add_action( 'init', 'create_post_type_sedscard' );
