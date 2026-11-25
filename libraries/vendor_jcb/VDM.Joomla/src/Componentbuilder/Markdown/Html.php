<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    4th September, 2022
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Markdown;


/**
 * A fully self-contained PHP class that converts simplified Markdown to HTML.
 * Supports headings, emphasis, links, images, task lists, code blocks, tables,
 * blockquotes, and more. Designed for robust and production-ready usage.
 * 
 * @since  5.1.1
 */
final class Html
{
	/**
	 * Convert Markdown/README string to sanitized and formatted HTML output.
	 *
	 * This method serves as the primary entry point to transform Markdown
	 * into semantic HTML using internal PHP logic without external libraries.
	 *
	 * @param  string  $markdown  Raw Markdown content to be converted.
	 *
	 * @return string  The resulting sanitized HTML output.
	 *
	 * @throws \RuntimeException If the input is empty or conversion fails.
	 * @since  5.1.1
	 */
	public function convert(string $markdown): string
	{
		$markdown = trim($markdown);

		if ($markdown === '') {
			throw new \RuntimeException('Markdown input is empty.');
		}

		$html = htmlspecialchars($markdown, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

		$html = $this->convertCodeBlocks($html);
		$html = $this->convertInlineCode($html);
		$html = $this->convertHeadings($html);
		$html = $this->convertBoldItalic($html);
		// An image contains link syntax, so consume the whole image first.
		$html = $this->convertImages($html);
		$html = $this->convertLinks($html);
		$html = $this->convertTaskLists($html);
		$html = $this->convertOrderedLists($html);
		$html = $this->convertUnorderedLists($html);
		$html = $this->convertTables($html);
		$html = $this->convertBlockquotes($html);

		return $this->convertParagraphsAndLineBreaks($html);
	}

	/**
	 * Transform fenced code blocks (``` content ```) into HTML <pre><code>.
	 *
	 * @param  string  $text  Raw Markdown content to be converted.
	 *
	 * @return string  HTML output with code blocks converted.
	 * @since  5.1.1
	 */
	protected function convertCodeBlocks(string $text): string
	{
		return preg_replace_callback('/```(.*?)```/s', fn($m) => '<pre><code>' . trim($m[1]) . '</code></pre>', $text);
	}

	/**
	 * Convert inline code markers (`code`) into <code> elements.
	 *
	 * @param  string  $text  Raw Markdown content to be converted.
	 *
	 * @return string  HTML output with inline code elements.
	 * @since  5.1.1
	 */
	protected function convertInlineCode(string $text): string
	{
		return preg_replace('/`([^`\n]+)`/', '<code>$1</code>', $text);
	}

	/**
	 * Convert markdown heading levels (# to ######) into HTML <h1>-<h6>.
	 *
	 * @param  string  $text  Raw Markdown content to be converted.
	 *
	 * @return string  HTML output with headings.
	 * @since  5.1.1
	 */
	protected function convertHeadings(string $text): string
	{
		for ($i = 6; $i >= 1; $i--) {
			$pattern = '/^' . str_repeat('#', $i) . '\s*(.*?)\s*$/m';
			$text = preg_replace($pattern, "<h{$i}>$1</h{$i}>", $text);
		}
		return $text;
	}

	/**
	 * Replace emphasis markers (**bold**, *italic*) with HTML <strong> and <em>.
	 *
	 * @param  string  $text  Raw Markdown content to be converted.
	 *
	 * @return string  HTML output with emphasis tags.
	 * @since  5.1.1
	 */
	protected function convertBoldItalic(string $text): string
	{
		$text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);
		$text = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $text);
		return $text;
	}

	/**
	 * Transform markdown link format [label](url) into HTML <a href>.
	 *
	 * @param  string  $text  Raw Markdown content to be converted.
	 *
	 * @return string  HTML output with anchor tags.
	 * @since  5.1.1
	 */
	protected function convertLinks(string $text): string
	{
		// Keep brackets in generated image attributes out of ordinary link parsing.
		return preg_replace_callback(
			'/<img\b(?:[^">]|"[^"]*")*>(*SKIP)(*F)|\[(.*?)\]\((.*?)\)/',
			fn($m) => '<a href="' . $this->safeUrl($m[2]) . '">' . $m[1] . '</a>',
			$text
		);
	}

	/**
	 * Convert image format ![alt](src) into HTML <img> tags.
	 *
	 * @param  string  $text  Raw Markdown content to be converted.
	 *
	 * @return string  The convert image format ![alt](src) into HTML <img> tags.
	 * @since  5.1.1
	 */
	protected function convertImages(string $text): string
	{
		return preg_replace_callback(
			'/!\[(.*?)\]\((.*?)\)/',
			fn($m) => '<img src="' . $this->safeUrl($m[2]) . '" alt="' . $m[1] . '">',
			$text
		);
	}

	/**
	 * Keep a markdown URL to a scheme that cannot execute script.
	 *
	 * convert() escapes the whole document before these rules run, so a URL
	 * cannot break out of the attribute, but it can still carry a scripting
	 * scheme such as javascript:. Browsers entity decode an attribute and
	 * drop control characters before they read the scheme, so normalise the
	 * same way before deciding, and drop anything that is not allowed.
	 *
	 * @param  string  $url  The URL captured from the markdown source.
	 *
	 * @return string  The URL, or an empty string when its scheme is refused.
	 * @since  6.1.7
	 */
	protected function safeUrl(string $url): string
	{
		$probe = (string) preg_replace(
			'/[\x00-\x20\x7F]+/',
			'',
			html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8')
		);

		// relative, root relative, query and anchor links carry no scheme
		if (!preg_match('#^([a-z][a-z0-9+.\-]*):#i', $probe, $match))
		{
			return $url;
		}

		$allowed = ['http', 'https', 'mailto', 'ftp', 'ftps', 'tel'];

		return in_array(strtolower($match[1]), $allowed, true) ? $url : '';
	}

	/**
	 * Convert task list items into HTML checkboxes (☑ / ☐).
	 *
	 * @param  string  $text  Raw Markdown content to be converted.
	 *
	 * @return string  HTML output with task list elements.
	 * @since  5.1.1
	 */
	protected function convertTaskLists(string $text): string
	{
		return preg_replace_callback('/(^[-*]\s+\[(x| )\]\s+.*(?:\n[-*]\s+\[(x| )\]\s+.*)*)/mi', function ($m) {
			$lines = preg_split('/\n/', trim($m[0]));
			$items = array_map(function ($line) {
				$checked = str_contains($line, '[x]') || str_contains($line, '[X]');
				$label = trim(preg_replace('/^[-*]\s+\[[xX ]\]\s+/', '', $line));
				$box = $checked ? '☑' : '☐';
				return "<li>{$box} {$label}</li>";
			}, $lines);
			return '<ul class="task-list">' . implode('', $items) . '</ul>';
		}, $text);
	}

	/**
	 * Convert ordered lists (1., 2., etc.) into <ol> structures.
	 *
	 * @param  string  $text  Raw Markdown content to be converted.
	 *
	 * @return string  HTML output with ordered list.
	 * @since  5.1.1
	 */
	protected function convertOrderedLists(string $text): string
	{
		return preg_replace_callback('/(^\d+\..+(?:\n\d+\..+)*)/m', function ($m) {
			$lines = preg_split('/\n/', trim($m[0]));
			$items = array_map(fn($l) => '<li>' . trim(preg_replace('/^\d+\.\s*/', '', $l)) . '</li>', $lines);
			return '<ol>' . implode('', $items) . '</ol>';
		}, $text);
	}

	/**
	 * Convert unordered list markers (-, *) into <ul> structures.
	 *
	 * @param  string  $text  Raw Markdown content to be converted.
	 *
	 * @return string  HTML output with unordered list.
	 * @since  5.1.1
	 */
	protected function convertUnorderedLists(string $text): string
	{
		return preg_replace_callback('/(^[-*]\s+.+(?:\n[-*]\s+.+)*)/m', function ($m) {
			$lines = preg_split('/\n/', trim($m[0]));
			$items = array_map(fn($l) => '<li>' . trim(preg_replace('/^[-*]\s*/', '', $l)) . '</li>', $lines);
			return '<ul>' . implode('', $items) . '</ul>';
		}, $text);
	}

	/**
	 * Convert markdown table syntax into HTML table layout.
	 *
	 * @param  string  $text  Raw Markdown content containing table syntax.
	 *
	 * @return string  HTML output with <table>, <thead>, and <tbody>.
	 * @since  5.1.1
	 */
	protected function convertTables(string $text): string
	{
		return preg_replace_callback('/(^\|.*?\|\s*\n\|[-:\s|]+\|\s*\n(?:\|.*?\|\s*\n?)*)/m', function ($m) {
			$lines = array_filter(array_map('trim', explode("\n", trim($m[0]))));
			if (count($lines) < 2) return '';

			$headers = array_map('trim', explode('|', trim($lines[0], '|')));
			$rows = array_slice($lines, 2);

			$thead = '<thead><tr>' . implode('', array_map(fn($h) => '<th>' . $h . '</th>', $headers)) . '</tr></thead>';
			$tbody = '';

			foreach ($rows as $line) {
				$cells = array_map('trim', explode('|', trim($line, '|')));
				$tbody .= '<tr>' . implode('', array_map(fn($c) => '<td>' . $c . '</td>', $cells)) . '</tr>';
			}

			return "<table>{$thead}<tbody>{$tbody}</tbody></table>";
		}, $text);
	}

	/**
	 * Convert blockquote markers > into <blockquote> blocks.
	 *
	 * @param  string  $text  Raw Markdown content with > quotes.
	 *
	 * @return string  HTML output with blockquote structure.
	 * @since  5.1.1
	 */
	protected function convertBlockquotes(string $text): string
	{
		return preg_replace_callback('/(^>.*(?:\n>.*)*)/m', function ($m) {
			$lines = array_map(fn($l) => trim(preg_replace('/^>\s?/', '', $l)), explode("\n", $m[0]));
			return '<blockquote>' . implode('<br>', $lines) . '</blockquote>';
		}, $text);
	}

	/**
	 * Converts newlines and wraps non-block content in <p> and <br>.
	 *
	 * @param  string  $text  HTML content after all block replacements.
	 *
	 * @return string  Final cleaned and structured HTML.
	 * @since  5.1.1
	 */
	protected function convertParagraphsAndLineBreaks(string $text): string
	{
		// Ensure input is a string to avoid deprecated null passing
		$text = (string) $text;

		// Convert multiple newlines into paragraph breaks
		$text = preg_replace("/\n{2,}/", '</p><p>', $text);

		// Wrap overall content in paragraph tags
		$text = '<p>' . $text . '</p>';

		// Replace single newlines with <br> tags,
		// except when following closing block elements.
		// Avoids variable-length lookbehind by using a negative lookahead instead.
		$text = preg_replace(
			'/(?:^|[^>])\K\n(?!<\/?(ul|ol|pre|table|thead|tbody|tr|blockquote))/',
			"<br>\n",
			$text
		);

		// Remove paragraph wrappers around block-level tags
		return preg_replace(
			'/<p>\s*(<(ul|ol|pre|h[1-6]|table|blockquote)>.*?<\/\2>)\s*<\/p>/si',
			'$1',
			$text
		);
	}
}

