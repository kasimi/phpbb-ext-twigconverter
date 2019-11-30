<?php

/**
 *
 * Twig Converter. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, kasimi, https://kasimi.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace kasimi\twigconverter\acp;

class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\kasimi\twigconverter\acp\main_module',
			'title'		=> 'ACP_TWIGCONVERTER_TITLE',
			'modes'		=> [
				'convert'	=> [
					'title'		=> 'ACP_TWIGCONVERTER_CONVERT',
					'auth'		=> 'ext_kasimi/twigconverter && acl_a_board',
					'cat'		=> ['ACP_TWIGCONVERTER_TITLE'],
				],
			],
		];
	}
}
