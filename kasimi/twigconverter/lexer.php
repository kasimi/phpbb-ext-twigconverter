<?php

/**
 *
 * Twig Converter. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, kasimi, https://kasimi.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace kasimi\twigconverter;

class lexer extends \phpbb\template\twig\lexer
{
	public function get_code()
	{
		return $this->code;
	}
}
