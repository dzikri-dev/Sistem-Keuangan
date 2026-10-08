<?php
/**
 * Blezt.id theme functions.
 *
 * @package bleztd
 */

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'bleztd-style',
		get_parent_theme_file_uri( 'style.css' ),
		array(),
		wp_get_theme()->get( 'Version' )
	);
} );

add_action( 'after_setup_theme', function () {
	add_editor_style( 'style.css' );
} );

add_action( 'init', function () {
	register_block_style( 'core/group', array(
		'name'  => 'card',
		'label' => 'Card',
	) );
	register_block_style( 'core/group', array(
		'name'  => 'glass',
		'label' => 'Glass',
	) );
} );
