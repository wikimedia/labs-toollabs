<?php
require_once 'htmlpurifier/HTMLPurifier.standalone.php';
$config = HTMLPurifier_Config::createDefault();
$config->set( 'HTML.Doctype', 'HTML 4.01 Transitional' );
$config->set( 'URI.Base', 'https://tools.wmflabs.org' );
$config->set( 'URI.MakeAbsolute', true );
$config->set( 'URI.DisableExternalResources', true );
$config->set( 'CSS.ForbiddenProperties',
	array(
		'margin' => true,
		'margin-top' => true,
		'margin-right' => true,
		'margin-bottom' => true,
		'margin-left' => true,
		'padding' => true,
		'padding-top' => true,
		'padding-right' => true,
		'padding-bottom' => true,
		'padding-left' => true
	)
);
$purifier = new HTMLPurifier( $config );
