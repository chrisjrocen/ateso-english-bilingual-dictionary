<?php
/**
 * Import ACF Fields.
 *
 * @package atesoenglish.
 */



// Imports custom fields
if( function_exists('acf_add_local_field_group') ):

	acf_add_local_field_group(array(
		'key' => 'group_62ec2f23d1a6b',
		'title' => 'Ateso Custom Fields',
		'fields' => array(
			array(
				'key' => 'field_62ec2f613b47f',
				'label' => 'Part of Speech',
				'name' => 'part_of_speech',
				'type' => 'text',
				'instructions' => 'nouns, verb, adjective etc',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
			),
			array(
				'key' => 'field_62ec2f8d3b480',
				'label' => 'Meaning',
				'name' => 'meaning',
				'type' => 'textarea',
				'instructions' => 'Meaning of Word',
				'required' => 1,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'maxlength' => '',
				'rows' => '',
				'new_lines' => 'wpautop',
			),
			array(
				'key' => 'field_62ec2fb33b481',
				'label' => 'Example',
				'name' => 'example',
				'type' => 'textarea',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'maxlength' => '',
				'rows' => 4,
				'new_lines' => 'wpautop',
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'ateso-words',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => '',
		'show_in_rest' => 0,
	));
	
	endif;		