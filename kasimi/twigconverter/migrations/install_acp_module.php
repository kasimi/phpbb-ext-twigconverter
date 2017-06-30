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

class install_acp_module extends \phpbb\db\migration\migration
{
	public function update_data()
	{
		return array(
			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_TWIGCONVERTER_TITLE'
			)),
			array('module.add', array(
				'acp',
				'ACP_TWIGCONVERTER_TITLE',
				array(
					'module_basename'	=> '\kasimi\twigconverter\acp\main_module',
					'modes'				=> array('convert'),
				),
			)),
		);
	}
}
