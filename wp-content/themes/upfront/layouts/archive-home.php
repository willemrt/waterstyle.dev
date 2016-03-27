<?php
/*
Layout File, at least one main region must be added;
 */

$type = !empty($type) ? $type : 'wide';
$left_sidebar = !empty($left_sidebar) ? $left_sidebar : false;
$right_sidebar = !empty($right_sidebar) ? $right_sidebar : false;

$main = upfront_create_region(array(
	'name' => "main",
	'title' => __("Main Area"),
	'scope' => "local",
	'type' => $type,
	'default' => true,
	'allow_sidebar' => true
), array(
	'row' => 200,
	'background_type' => 'color',
	'background_color' => '#c5d0db'
));

$main->add_element('Uimage', array(
	'id' => 'default-image',
	'columns' => 8,
	'rows' => 20,
	'margin_top' => 6,
	'margin_left' => 1,
	'new_line' => true,
	'options' => array(
	)
));

$main->add_element('PlainTxt', array(
	'id' => 'default-text',
	'columns' => 6,
	'rows' => 10,
	'margin_top' => 6,
	'margin_left' => 1,
	'new_line' => false,
	'options' => array(
		'content' => "<p>Text element with no background color applied<p>",
		"class" => "c24 upfront-plain_txt",
	)
));

$main->add_element('PlainTxt', array(
	'id' => 'default-text-bg',
	'columns' => 6,
	'rows' => 10,
	'margin_top' => 6,
	'margin_left' => 1,
	'new_line' => false,
	'options' => array(
		'content' => "<p>Text element with background color applied gets additional padding to prevent text running into sides</p>",
		'background_color' => '#ffffff',
		"class" => "c24 upfront-plain_txt",
	)
));

if ( $left_sidebar ){
	$left = upfront_create_region(array(
		'name' => "left-sidebar",
		'title' => __("Left Sidebar"),
		'scope' => "local",
	), array(
		'col' => 6,
		'background_type' => 'color',
		'background_color' => '#fff'
	));

	$left->add_element('PlainTxt', array(
		'id' => 'default-left-text',
		'columns' => 4,
		'rows' => 50,
		'margin_top' => 10,
		'margin_left' => 1,
		'new_line' => false,
		'options' => array(
			'content' => "Text element on left sidebar",
			"class" => "c24 upfront-plain_txt",
		)
	));

	$main->add_side_region($left, 'left');
}

if ( $right_sidebar ){
	$right = upfront_create_region(array(
		'name' => "right-sidebar",
		'title' => __("Right Sidebar"),
		'scope' => "local",
	), array(
		'col' => 6,
		'background_type' => 'color',
		'background_color' => '#fff'
	));

	$right->add_element('PlainTxt', array(
		'id' => 'default-right-text',
		'columns' => 4,
		'rows' => 50,
		'margin_top' => 10,
		'margin_left' => 1,
		'new_line' => false,
		'options' => array(
			'content' => "Text element on right sidebar",
			"class" => "c24 upfront-plain_txt",
		)
	));

	$main->add_side_region($right, 'right');
}


$fixed = upfront_create_region(array(
	'name' => "floating",
	'title' => __("Floating Area"),
	'scope' => "local",
	'type' => 'fixed'
), array(
	'width' => 250,
	'height' => 250,
	'top' => 30,
	'left' => 30,
	'background_type' => 'color',
	'background_color' => '#aeb8c2'
));
$main->add_side_region($fixed, 'fixed');

$regions->add($main);

