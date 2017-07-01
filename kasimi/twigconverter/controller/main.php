<?php

/**
 *
 * Twig Converter. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, kasimi, https://kasimi.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace kasimi\twigconverter\controller;

use kasimi\twigconverter\lexer;
use phpbb\exception\http_exception;
use phpbb\extension\manager;
use phpbb\request\request_interface;
use phpbb\symfony_request;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class main
{
	/** @var request_interface */
	protected $request;

	/** @var symfony_request */
	protected $symfony_request;

	/* @var template */
	protected $template;

	/* @var user */
	protected $user;

	/** @var manager */
	protected $ext_manager;

	/** @var string */
	protected $root_path;

	/**
	 * Constructor
	 *
	 * @param request_interface	$request
	 * @param symfony_request	$symfony_request,
	 * @param template			$template
	 * @param user				$user
	 * @param manager			$ext_manager
	 * @param string			$root_path
	 */
	public function __construct(
		request_interface $request,
		symfony_request $symfony_request,
		template $template,
		user $user,
		manager $ext_manager,
		$root_path
	)
	{
		$this->request			= $request;
		$this->symfony_request	= $symfony_request;
		$this->template			= $template;
		$this->user				= $user;
		$this->ext_manager		= $ext_manager;
		$this->root_path		= $root_path;
	}

	/**
	 * @param string $u_action
	 */
	public function convert($u_action)
	{
		$this->user->add_lang_ext('kasimi/twigconverter', 'common');

		add_form_key('kasimi/twigconverter');

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('kasimi/twigconverter'))
			{
				trigger_error('FORM_INVALID');
			}

			$ext_name = $this->request->variable('ext_name', '');
			$ext_name = str_replace('.', '', $ext_name);

			try
			{
				$template_contents = $this->convert_extension($ext_name);

				$zip_directory = $this->root_path . 'store/';
				$zip_filename = str_replace('/', '_', $ext_name) .  '_twig_templates.zip';

				$this->make_zip($zip_directory, $zip_filename, $template_contents);
				$this->download($zip_directory . $zip_filename);

				exit_handler();
			}
			catch (\Exception $e)
			{
				$this->template->assign_var('ERROR', $this->user->lang($e->getMessage()));
			}
		}

		$ext_names = array_keys($this->ext_manager->all_available());

		foreach($ext_names as $ext_name)
		{
			$this->template->assign_block_vars('ext', array(
				'NAME' => $ext_name,
			));
		}

		$this->template->assign_var('U_ACTION', $u_action);
	}

	/**
	 * @param string $ext_name The extension to convert, vendor/extname.
	 * @return array An array mapping each template filename in the given extension to the contents of the converted template syntax.
	 */
	protected function convert_extension($ext_name)
	{
		$ext_path = $this->ext_manager->get_extension_path($ext_name, true);

		if (!$ext_name || !@is_dir($ext_path))
		{
			throw new http_exception(400, 'TWIGCONVERTER_ERROR_NO_EXT');
		}

		$template_files = $this->ext_manager->get_finder()
			->extension_suffix('.html')
			->find_from_extension($ext_name, $ext_path);

		if (!$template_files)
		{
			throw new http_exception(400, 'TWIGCONVERTER_ERROR_NO_TEMPLATE_FILES');
		}

		$converted_syntax = array();
		$lexer = new lexer();

		foreach (array_keys($template_files) as $template_file)
		{
			$contents = @file_get_contents($this->root_path . $template_file);
			$converted_syntax[$template_file] = $lexer->convert($contents);
		}

		return $converted_syntax;
	}

	/**
	 * @param string $zip_directory Directory to store the zip file in.
	 * @param string $zip_filename The name of the zip file.
	 * @param array $file_contents An array mapping a filename to its contents.
	 */
	protected function make_zip($zip_directory, $zip_filename, array $file_contents)
	{
		if (!phpbb_is_writable($zip_directory))
		{
			throw new http_exception(400, 'TWIGCONVERTER_ERROR_WRITEABLE');
		}

		$zip = new \ZipArchive();

		if (true !== $zip->open($zip_directory . $zip_filename, \ZipArchive::CREATE | \ZipArchive::OVERWRITE))
		{
			throw new http_exception(400, 'TWIGCONVERTER_ERROR_ZIP');
		}

		try
		{
			foreach ($file_contents as $filename => $contents)
			{
				$zip->addFromString($filename, $contents);
			}
		}
		finally
		{
			$zip->close();
		}
	}

	/**
	 * @param string $filename
	 * @return Response
	 */
	protected function download($filename)
	{
		return (new BinaryFileResponse($filename))
			->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, basename($filename))
			->prepare($this->symfony_request)
			->send();
	}
}
