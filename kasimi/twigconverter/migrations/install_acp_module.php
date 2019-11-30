<?php

/**
 *
 * Twig Converter. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, kasimi, https://kasimi.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace kasimi\twigconverter\migrations;

use phpbb\db\migration\migration;

class install_acp_module extends migration
{
	public function update_data()
	{
		return [
			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_TWIGCONVERTER_TITLE'
			]],
			['module.add', [
				'acp',
				'ACP_TWIGCONVERTER_TITLE',
				[
					'module_basename'	=> '\kasimi\twigconverter\acp\main_module',
					'modes'				=> ['convert'],
				],
			]],
		];
	}
}
