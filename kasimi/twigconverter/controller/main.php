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
	 * @param symfony_request	$symfony_request
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
		$available_extensions = $this->available_extensions();
		$available_styles = $this->available_styles();
		add_form_key('kasimi/twigconverter');

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('kasimi/twigconverter'))
			{
				trigger_error('FORM_INVALID');
			}

			$name = $this->request->variable('name', '');

			try
			{
				if (in_array($name, $available_extensions))
				{
					$this->run($name, $this->get_extension_template_files($name));
				}
				else if (in_array($name, $available_styles))
				{
					$this->run($name, $this->get_style_template_files($name));
				}
			}
			catch (\Exception $e)
			{
				$this->template->assign_var('ERROR', $this->user->lang($e->getMessage()));
			}
		}

		foreach ($available_extensions as $ext_name)
		{
			$this->template->assign_block_vars('ext', array(
				'NAME' => $ext_name,
			));
		}

		foreach ($available_styles as $style_name)
		{
			$this->template->assign_block_vars('style', array(
				'NAME' => $style_name,
			));
		}

		$this->template->assign_var('U_ACTION', $u_action);
	}

	/**
	 * @return array An array containing all available extension names.
	 */
	protected function available_extensions()
	{
		return array_keys($this->ext_manager->all_available());
	}

	/**
	 * @return array An array containing all available style names.
	 */
	protected function available_styles()
	{
		$styles = array();

		$dp = @opendir($this->root_path . 'styles/');
		if ($dp)
		{
			while (($file = readdir($dp)) !== false)
			{
				$dir = $this->root_path . 'styles/' . $file;
				if ($file[0] == '.' || !is_dir($dir))
				{
					continue;
				}

				if (file_exists("{$dir}/style.cfg"))
				{
					$styles[] = $file;
				}
			}
			closedir($dp);
		}

		sort($styles);

		return $styles;
	}

	/**
	 * @param string $ext_name The name of the extension.
	 * @return array An array containing relative path names to all .html files for the given extension.
	 */
	protected function get_extension_template_files($ext_name)
	{
		$template_files = $this->ext_manager->get_finder(true)
			->extension_suffix('.html')
			->find_from_extension($ext_name, $this->ext_manager->get_extension_path($ext_name, true));

		return array_keys($template_files);
	}

	/**
	 * @param string $style_name The name of the style.
	 * @return array An array containing relative path names to all .html files for the given style.
	 */
	protected function get_style_template_files($style_name)
	{
		$template_files = $this->ext_manager->get_finder(true)
			->core_suffix('.html')
			->find_from_paths(array('/' => $this->root_path . 'styles/' . $style_name));

		$filenames = array();

		foreach ($template_files as $template_file)
		{
			$filenames[] = 'styles/' . $style_name . '/' . $template_file['named_path'];
		}

		return $filenames;
	}

	/**
	 * @param string $name The name of the extension or the style.
	 * @param array $template_files An array of file names found within the extension or style directory.
	 */
	protected function run($name, array $template_files)
	{
		if (!$template_files)
		{
			throw new http_exception(400, 'TWIGCONVERTER_ERROR_NO_TEMPLATE_FILES');
		}

		$template_contents = $this->convert_files($template_files);

		$zip_directory = $this->root_path . 'store/';
		$zip_filename = str_replace('/', '_', $name) . '_twig_templates.zip';

		$this->make_zip($zip_directory, $zip_filename, $template_contents);
		$this->download($zip_directory . $zip_filename, 'application/zip');

		exit_handler();
	}

	/**
	 * @param array $filenames An array containing file names to convert, without root path.
	 * @return array An array mapping each file name from the $filenames array to the contents of the converted template syntax.
	 */
	protected function convert_files(array $filenames)
	{
		$converted_syntax = array();
		$lexer = new lexer();

		foreach ($filenames as $filename)
		{
			$contents = @file_get_contents($this->root_path . $filename);
			$converted_syntax[$filename] = $lexer->convert($contents);
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
	 * @param string $content_type
	 * @return Response
	 */
	protected function download($filename, $content_type)
	{
		$response = new BinaryFileResponse($filename);
		$response->headers->set('Content-Type', $content_type);

		return $response
			->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, basename($filename))
			->prepare($this->symfony_request)
			->send();
	}
}
