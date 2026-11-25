<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    4th September 2022
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this JCB template file (EVER)
defined('_JCB_TEMPLATE') or die;
?>
###BOM###
namespace ###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Administrator\Model;

###DASH_MODEL_HEADER###

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * ###Component### List Model
 *
 * @since  1.6
 */
class ###Component###Model extends ListModel
{
	/**
	 * Represents the current user object.
	 *
	 * @var   User  The user object representing the current user.
	 * @since 3.2.0
	 */
	protected User $user;

	/**
	 * View groups of this component
	 *
	 * @var   array<string, string>
	 * @since 5.1.1
	 */
	protected array $viewGroups = [
		'main' => [###DASHBOARDICONS###],
	];
###DASHBOARDICONACCESS###
	/**
	 * The styles array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $styles = [
		'media/com_###component###/css/admin/admin.css',
		'media/com_###component###/css/admin/dashboard.css'
	];

	/**
	 * The scripts array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $scripts = [
		'media/com_###component###/js/admin/admin.js'
	];

	/**
	 * Constructor
	 *
	 * @param   array                 $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   ?MVCFactoryInterface  $factory  The factory.
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	public function __construct($config = [], ?MVCFactoryInterface $factory = null)
	{
		parent::__construct($config, $factory);
	}

	/**
	 * Use the identity supplied after model construction.
	 *
	 * @param   User  $currentUser  The user supplied by the controller or helper.
	 *
	 * @return  void
	 * @since   6.2.0
	 */
	public function setCurrentUser(User $currentUser): void
	{
		parent::setCurrentUser($currentUser);
		$this->user = $currentUser;
	}

	/**
	 * Get dashboard icons, grouped by view sections.
	 *
	 * @return array<string, array<int, \stdClass|false>>
	 * @since  5.1.1
	 */
	public function getIcons(): array
	{
		$icons = [];

		foreach ($this->viewGroups as $group => $views)
		{
			if (!Super___0a59c65c_9daf_4bc9_baf4_e063ff9e6a8a___Power::check($views))
			{
				$icons[$group][] = false;
				continue;
			}

			foreach ($views as $view)
			{
				$icon = $this->buildIconObject($view);
				if ($icon !== null)
				{
					$icons[$group][] = $icon;
				}
			}
		}

		return $icons;
	}

	/**
	 * Method to get the styles that have to be included on the view
	 *
	 * @return  array    styles files
	 * @since   4.3
	 */
	public function getStyles(): array
	{
		return $this->styles;
	}

	/**
	 * Method to set the styles that have to be included on the view
	 *
	 * @return  void
	 * @since   4.3
	 */
	public function setStyles(string $path): void
	{
		$this->styles[] = $path;
	}

	/**
	 * Method to get the script that have to be included on the view
	 *
	 * @return  array    script files
	 * @since   4.3
	 */
	public function getScripts(): array
	{
		return $this->scripts;
	}

	/**
	 * Method to set the script that have to be included on the view
	 *
	 * @return  void
	 * @since   4.3
	 */
	public function setScript(string $path): void
	{
		$this->scripts[] = $path;
	}

	/**
	 * Build a single dashboard icon if access is granted.
	 *
	 * @param string $view The view string to parse.
	 *
	 * @return \stdClass|null  The icon object or null if access denied.
	 * @since  5.1.1
	 */
	protected function buildIconObject(string $view): ?\stdClass
	{
		$parsed = $this->parseViewDefinition($view);
		if (!$parsed)
		{
			return null;
		}

		[
			'type' => $type,
			'name' => $name,
			'url' => $url,
			'image' => $image,
			'alt' => $alt,
			'viewName' => $viewName,
			'add' => $add,
		] = $parsed;

		if (!$this->hasAccessToView($viewName, $add))
		{
			return null;
		}

		return $this->createIconObject($url, $name, $image, $alt);
	}

	/**
	 * Parse a view string into structured components.
	 *
	 * @param string $view  The view definition string.
	 *
	 * @return array<string, mixed>|null  Parsed values or null on failure.
	 * @since  5.1.1
	 */
	protected function parseViewDefinition(string $view): ?array
	{
		$add = false;

		if (strpos($view, '||') !== false)
		{
			$parts = explode('||', $view);
			if (count($parts) === 3)
			{
				[$type, $name, $url] = $parts;
				return [
					'type' => $type,
					'name' => 'COM_###COMPONENT###_DASHBOARD_' . Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::safe($name, 'U'),
					'url' => $url,
					'image' => "{$name}.{$type}",
					'alt' => $name,
					'viewName' => $name,
					'add' => false,
				];
			}
		}

		if (strpos($view, '.') !== false)
		{
			$parts = explode('.', $view);
			$type = $parts[0] ?? '';
			$name = $parts[1] ?? '';
			$action = $parts[2] ?? null;
			$viewName = $name;

			if ($action)
			{
				if ($action === 'add')
				{
					$url = "index.php?option=com_###component###&view={$name}&layout=edit";
					$image = "{$name}_{$action}.{$type}";
					$alt = "{$name}&nbsp;{$action}";
					$name = 'COM_###COMPONENT###_DASHBOARD_' .
							Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::safe($name, 'U') . '_ADD';
					$add = true;
				}
				else
				{
					if (strpos($action, '_qpo0O0oqp_') !== false)
					{
						[$action, $ext] = explode('_qpo0O0oqp_', $action);
						$extension = str_replace('_po0O0oq_', '.', $ext);
					}
					else
					{
						$extension = "com_###component###.{$name}";
					}
					$url = "index.php?option=com_categories&view=categories&extension={$extension}";
					$image = "{$name}_{$action}.{$type}";
					$alt = "{$name}&nbsp;{$action}";
					$name = 'COM_###COMPONENT###_DASHBOARD_' .
							Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::safe($name, 'U') . '_' .
							Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::safe($action, 'U');
				}
			}
			else
			{
				$url = "index.php?option=com_###component###&view={$name}";
				$image = "{$name}.{$type}";
				$alt = $name;
				$name = 'COM_###COMPONENT###_DASHBOARD_' .
						Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::safe($name, 'U');
			}

			return compact('type', 'name', 'url', 'image', 'alt', 'viewName', 'add');
		}

		return [
			'type' => 'png',
			'name' => ucwords($view) . '<br /><br />',
			'url' => "index.php?option=com_###component###&view={$view}",
			'image' => "{$view}.png",
			'alt' => $view,
			'viewName' => $view,
			'add' => false,
		];
	}

	/**
	 * Determine if the user has access to view or create the item.
	 *
	 * @param string $viewName The base name of the view.
	 * @param bool $add If this is an add-action.
	 *
	 * @return bool
	 * @since  5.1.1
	 */
	protected function hasAccessToView(string $viewName, bool $add): bool
	{
		$viewAccess = $this->viewAccess;
		$accessAdd = $add && isset($viewAccess["{$viewName}.create"])
			? $viewAccess["{$viewName}.create"]
			: ($add ? 'core.create' : '');

		$accessTo = $viewAccess["{$viewName}.access"] ?? '';

		$dashboardAdd = isset($viewAccess["{$viewName}.dashboard_add"]) &&
					$this->user->authorise($viewAccess["{$viewName}.dashboard_add"], 'com_###component###');

		$dashboardList = isset($viewAccess["{$viewName}.dashboard_list"]) &&
					$this->user->authorise($viewAccess["{$viewName}.dashboard_list"], 'com_###component###');

		if ($add && Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::check($accessAdd))
		{
			return $this->user->authorise($accessAdd, 'com_###component###') && $dashboardAdd;
		}

		if (Super___1f28cb53_60d9_4db1_b517_3c7dc6b429ef___Power::check($accessTo))
		{
			return $this->user->authorise($accessTo, 'com_###component###') && $dashboardList;
		}

		return !$accessTo && !$accessAdd;
	}

	/**
	 * Create a \stdClass icon object.
	 *
	 * @param string $url Icon URL.
	 * @param string $name Language string or label.
	 * @param string $image Image filename.
	 * @param string $alt Alt text.
	 *
	 * @return \stdClass
	 * @since  5.1.1
	 */
	protected function createIconObject(string $url, string $name, string $image, string $alt): \stdClass
	{
		$icon = new \stdClass;
		$icon->url = $url;
		$icon->name = $name;
		$icon->image = $image;
		$icon->alt = $alt;
		return $icon;
	}###DASH_MODEL_METHODS###
}
