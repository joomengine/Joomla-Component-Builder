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

namespace VDM\Joomla\Componentbuilder\Compiler\Creator;


use VDM\Joomla\Componentbuilder\Compiler\Builder\Router;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;


/**
 * Router Methods Manual Creator Class
 * 
 * @since 3.2.0
 */
class RouterMethodsManual
{
	/**
	 * The Router Class.
	 *
	 * @var   Router
	 * @since 3.2.0
	 */
	protected Router $router;

	/**
	 * Fresh-query method emitted by this target renderer.
	 *
	 * @var   string
	 * @since 6.2.0
	 */
	protected string $queryMethod = 'getQuery(true)';

	/**
	 * Constructor.
	 *
	 * @param Router    $router    The Router Class.
	 *
	 * @since 3.2.0
	 */
	public function __construct(Router $router)
	{
		$this->router = $router;
	}

	/**
	 * Get Methods Code
	 *
	 * @return  string
	 * @since   3.2.0
	 */
	public function get(): string
	{
		$views = $this->router->get('views');
		if ($views !== null)
		{
			$code = [];
			foreach ($views as $view)
			{
				// we only add these if we can get an ID (int) value
				// else you will need to use the manual or customcode options
				if (empty($view->key) || empty($view->alias))
				{
					continue;
				}

				$code[] = '';
				$code[] = Indent::_(1) . "/**";
				$code[] = Indent::_(1) . " * Method to get the segment(s) for {$view->view}";
				$code[] = Indent::_(1) . " *";
				$code[] = Indent::_(1) . " * @param   string  \$segment  Segment of the article to retrieve the ID for";
				$code[] = Indent::_(1) . " * @param   array   \$query    The request that is parsed right now";
				$code[] = Indent::_(1) . " *";
				$code[] = Indent::_(1) . " * @return  mixed   The {$view->key} of this item or false";
				$code[] = Indent::_(1) . " * @since   4.4.0";
				$code[] = Indent::_(1) . " */";
				$code[] = Indent::_(1) . "public function get{$view->View}Id(\$segment, \$query)";
				$code[] = Indent::_(1) . "{";
				$code[] = Indent::_(2) . "if (\$this->noIDs)";
				$code[] = Indent::_(2) . "{";
				$code[] = Indent::_(3) . "\$dbquery = \$this->db->{$this->queryMethod};";
				$code[] = Indent::_(3) . "\$dbquery->select(\$this->db->quoteName('{$view->key}'))";
				$code[] = Indent::_(4) . "->from(\$this->db->quoteName('{$view->table}'))";
				$code[] = Indent::_(4) . "->where(";
				$code[] = Indent::_(5) . "[";
				$code[] = Indent::_(6) . "\$this->db->quoteName('{$view->alias}') . ' = :alias'";
				$code[] = Indent::_(5) . "]";
				$code[] = Indent::_(4) . ")";
				$code[] = Indent::_(4) . "->bind(':alias', \$segment);";
				$code[] = Indent::_(3) . "\$this->db->setQuery(\$dbquery);";
				$code[] = '';
				$code[] = Indent::_(3) . "return (int) \$this->db->loadResult();";
				$code[] = Indent::_(2) . "}";
				$code[] = '';
				$code[] = Indent::_(2) . "return (int) \$segment;";
				$code[] = Indent::_(1) . "}";
				$code[] = '';
				$code[] = Indent::_(1) . "/**";
				$code[] = Indent::_(1) . " * Method to get the segment(s) for {$view->view}";
				$code[] = Indent::_(1) . " *";
				$code[] = Indent::_(1) . " * @param   string  \$id     ID of the contact to retrieve the segments for";
				$code[] = Indent::_(1) . " * @param   array   \$query  The request that is built right now";
				$code[] = Indent::_(1) . " *";
				$code[] = Indent::_(1) . " * @return  array|string  The segments of this item";
				$code[] = Indent::_(1) . " * @since   4.4.0";
				$code[] = Indent::_(1) . " */";
				$code[] = Indent::_(1) . "public function get{$view->View}Segment(\$id, \$query)";
				$code[] = Indent::_(1) . "{";
				$code[] = Indent::_(2) . "\$id = (string) (\$id ?? '');";
				$code[] = Indent::_(2) . "if (empty(\$id))";
				$code[] = Indent::_(2) . "{";
				$code[] = Indent::_(3) . "return '';";
				$code[] = Indent::_(2) . "}";
				$code[] = '';
				$code[] = Indent::_(2) . "if (strpos(\$id, ':') === false)";
				$code[] = Indent::_(2) . "{";
				$code[] = Indent::_(3) . "\$dbquery = \$this->db->{$this->queryMethod};";
				$code[] = Indent::_(3) . "\$dbquery->select(\$this->db->quoteName('{$view->alias}'))";
				$code[] = Indent::_(4) . "->from(\$this->db->quoteName('{$view->table}'))";
				$code[] = Indent::_(4) . "->where(\$this->db->quoteName('{$view->key}') . ' = :id')";
				$code[] = Indent::_(4) . "->bind(':id', (int) \$id, ParameterType::INTEGER);";
				$code[] = Indent::_(3) . "\$this->db->setQuery(\$dbquery);";
				$code[] = '';
				$code[] = Indent::_(3) . "\$id .= ':' . \$this->db->loadResult();";
				$code[] = Indent::_(2) . "}";
				$code[] = '';
				$code[] = Indent::_(2) . "if (\$this->noIDs && strpos(\$id, ':') !== false)";
				$code[] = Indent::_(2) . "{";
				$code[] = Indent::_(3) . "list(\$void, \$segment) = explode(':', \$id, 2);";
				$code[] = '';
				$code[] = Indent::_(3) . "return [(int) \$void => \$segment];";
				$code[] = Indent::_(2) . "}";
				$code[] = '';
				$code[] = Indent::_(2) . "return [(int) \$id => \$id];";
				$code[] = Indent::_(1) . "}";
			}

			return PHP_EOL . implode(PHP_EOL, $code);
		}

		return '';
	}
}

