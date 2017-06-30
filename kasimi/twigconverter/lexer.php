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
	/**
	 * Code taken from https://github.com/phpbb/phpbb/blob/release-3.2.0/phpBB/phpbb/template/twig/lexer.php#L23
	 *
	 * @param string $code
	 * @return string
	 */
	public function convert($code)
	{
		// Our phpBB tags
		// Commented out tokens are handled separately from the main replace
		$phpbb_tags = array(
			/*'BEGIN',
			'BEGINELSE',
			'END',
			'IF',
			'ELSE',
			'ELSEIF',
			'ENDIF',
			'DEFINE',
			'UNDEFINE',*/
			'ENDDEFINE',
			'INCLUDE',
			'INCLUDEPHP',
			'INCLUDEJS',
			'INCLUDECSS',
			'PHP',
			'ENDPHP',
			'EVENT',
		);
		// Twig tag masks
		$twig_tags = array(
			'autoescape',
			'endautoescape',
			'if',
			'elseif',
			'else',
			'endif',
			'block',
			'endblock',
			'use',
			'extends',
			'embed',
			'filter',
			'endfilter',
			'flush',
			'for',
			'endfor',
			'macro',
			'endmacro',
			'import',
			'from',
			'sandbox',
			'endsandbox',
			'set',
			'endset',
			'spaceless',
			'endspaceless',
			'verbatim',
			'endverbatim',
		);
		// Fix tokens that may have inline variables (e.g. <!-- DEFINE $TEST = '{FOO}')
		$code = $this->strip_surrounding_quotes(array(
			'INCLUDE',
			'INCLUDEPHP',
			'INCLUDEJS',
			'INCLUDECSS',
		), $code);
		$code = $this->fix_inline_variable_tokens(array(
			'DEFINE \$[a-zA-Z0-9_]+ =',
			'INCLUDE',
			'INCLUDEPHP',
			'INCLUDEJS',
			'INCLUDECSS',
		), $code);
		$code = $this->add_surrounding_quotes(array(
			'INCLUDE',
			'INCLUDEPHP',
			'INCLUDEJS',
			'INCLUDECSS',
		), $code);
		// Fix our BEGIN statements
		$code = $this->fix_begin_tokens($code);
		// Fix our IF tokens
		$code = $this->fix_if_tokens($code);
		// Fix our DEFINE tokens
		$code = $this->fix_define_tokens($code);
		// Replace all of our starting tokens, <!-- TOKEN --> with Twig style, {% TOKEN %}
		// This also strips outer parenthesis, <!-- IF (blah) --> becomes <!-- IF blah -->
		$code = preg_replace('#<!-- (' . implode('|', $phpbb_tags) . ')(?: (.*?) ?)?-->#', '{% $1 $2 %}', $code);
		// Replace all of our twig masks with Twig code (e.g. <!-- BLOCK .+ --> with {% block $1 %})
		$code = $this->replace_twig_tag_masks($code, $twig_tags);
		// Replace all of our language variables, {L_VARNAME}, with Twig style, {{ lang('NAME') }}
		// Appends any filters after lang()
		$code = preg_replace('#{L_([a-zA-Z0-9_\.]+)(\|[^}]+?)?}#', '{{ lang(\'$1\')$2 }}', $code);
		// Replace all of our escaped language variables, {LA_VARNAME}, with Twig style, {{ lang('NAME')|escape('js') }}
		// Appends any filters after lang(), but before escape('js')
		$code = preg_replace('#{LA_([a-zA-Z0-9_\.]+)(\|[^}]+?)?}#', '{{ lang(\'$1\')$2|escape(\'js\') }}', $code);
		// Replace all of our variables, {VARNAME}, with Twig style, {{ VARNAME }}
		// Appends any filters
		$code = preg_replace('#{([a-zA-Z0-9_\.]+)(\|[^}]+?)?}#', '{{ $1$2 }}', $code);

		return $code;
	}
}
