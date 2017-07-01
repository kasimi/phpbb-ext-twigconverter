<?php

/**
 *
 * Twig Converter. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, kasimi, https://kasimi.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters for use
// ’ » “ ” …

$lang = array_merge($lang, array(
	'TWIGCONVERTER_SELECT'						=> 'Convert template files',
	'TWIGCONVERTER_SELECT_EXPLAIN'				=> 'Select an extension to convert its template files to Twig syntax. A zip file will be downloaded and also be saved to the board’s store directory.',
	'TWIGCONVERTER_SELECT_EXT'					=> 'Select extension',

	'TWIGCONVERTER_ERROR_NO_EXT'				=> 'Please select an extension.',
	'TWIGCONVERTER_ERROR_NO_TEMPLATE_FILES'		=> 'The selected extension doesn’t have any template files.',
	'TWIGCONVERTER_ERROR_WRITEABLE'				=> 'The destination directory isn’t writeable.',
	'TWIGCONVERTER_ERROR_ZIP'					=> 'Failed to create zip file.',
));
