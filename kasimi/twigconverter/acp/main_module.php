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

class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main($id, $mode)
	{
		global $phpbb_container;

		$this->tpl_name = 'acp_twigconverter_body';
		$this->page_title = 'ACP_TWIGCONVERTER_TITLE';

		$controller = $phpbb_container->get('kasimi.twigconverter.controller');
		$controller->$mode($this->u_action);
	}
}
