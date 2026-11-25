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


use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\EventInterface as Event;
use VDM\Joomla\Componentbuilder\Compiler\Language;
use VDM\Joomla\Componentbuilder\Compiler\Component;
use VDM\Joomla\Componentbuilder\Compiler\Field\Name as FieldName;
use VDM\Joomla\Componentbuilder\Compiler\Field\TypeName;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Counter;
use VDM\Joomla\Componentbuilder\Compiler\Creator\Permission;
use VDM\Joomla\Componentbuilder\Compiler\Builder\AssetsRules;
use VDM\Joomla\Componentbuilder\Compiler\Builder\CustomTabs;
use VDM\Joomla\Componentbuilder\Compiler\Builder\PermissionViews;
use VDM\Joomla\Componentbuilder\Compiler\Builder\PermissionFields;
use VDM\Joomla\Componentbuilder\Compiler\Builder\PermissionComponent;
use VDM\Joomla\Componentbuilder\Compiler\Creator\CustomButtonPermissions;
use VDM\Joomla\Utilities\MathHelper;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Utilities\StringHelper;


/**
 * Access Sections Creator Class
 * 
 * @since 3.2.0
 */
final class AccessSections
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 3.2.0
	 */
	protected Config $config;

	/**
	 * The EventInterface Class.
	 *
	 * @var   Event
	 * @since 3.2.0
	 */
	protected Event $event;

	/**
	 * The Language Class.
	 *
	 * @var   Language
	 * @since 3.2.0
	 */
	protected Language $language;

	/**
	 * The Component Class.
	 *
	 * @var   Component
	 * @since 3.2.0
	 */
	protected Component $component;

	/**
	 * The Name Class.
	 *
	 * @var   FieldName
	 * @since 3.2.0
	 */
	protected FieldName $fieldname;

	/**
	 * The TypeName Class.
	 *
	 * @var   TypeName
	 * @since 3.2.0
	 */
	protected TypeName $typename;

	/**
	 * The Counter Class.
	 *
	 * @var   Counter
	 * @since 3.2.0
	 */
	protected Counter $counter;

	/**
	 * The Permission Class.
	 *
	 * @var   Permission
	 * @since 3.2.0
	 */
	protected Permission $permission;

	/**
	 * The AssetsRules Class.
	 *
	 * @var   AssetsRules
	 * @since 3.2.0
	 */
	protected AssetsRules $assetsrules;

	/**
	 * The CustomTabs Class.
	 *
	 * @var   CustomTabs
	 * @since 3.2.0
	 */
	protected CustomTabs $customtabs;

	/**
	 * The PermissionViews Class.
	 *
	 * @var   PermissionViews
	 * @since 3.2.0
	 */
	protected PermissionViews $permissionviews;

	/**
	 * The PermissionFields Class.
	 *
	 * @var   PermissionFields
	 * @since 3.2.0
	 */
	protected PermissionFields $permissionfields;

	/**
	 * The PermissionComponent Class.
	 *
	 * @var   PermissionComponent
	 * @since 3.2.0
	 */
	protected PermissionComponent $permissioncomponent;

	/**
	 * The CustomButtonPermissions Class.
	 *
	 * @var   CustomButtonPermissions
	 * @since 3.2.0
	 */
	protected CustomButtonPermissions $custombuttonpermissions;

	/**
	 * Constructor.
	 *
	 * @param Config                    $config                    The Config Class.
	 * @param Event                     $event                     The EventInterface Class.
	 * @param Language                  $language                  The Language Class.
	 * @param Component                 $component                 The Component Class.
	 * @param FieldName                 $fieldname                 The Name Class.
	 * @param TypeName                  $typename                  The TypeName Class.
	 * @param Counter                   $counter                   The Counter Class.
	 * @param Permission                $permission                The Permission Class.
	 * @param AssetsRules               $assetsrules               The AssetsRules Class.
	 * @param CustomTabs                $customtabs                The CustomTabs Class.
	 * @param PermissionViews           $permissionviews           The PermissionViews Class.
	 * @param PermissionFields          $permissionfields          The PermissionFields Class.
	 * @param PermissionComponent       $permissioncomponent       The PermissionComponent Class.
	 * @param CustomButtonPermissions   $custombuttonpermissions   The CustomButtonPermissions Class.
	 *
	 * @since 3.2.0
	 */
	public function __construct(Config $config, Event $event, Language $language,
		Component $component, FieldName $fieldname,
		TypeName $typename, Counter $counter,
		Permission $permission, AssetsRules $assetsrules,
		CustomTabs $customtabs, PermissionViews $permissionviews,
		PermissionFields $permissionfields,
		PermissionComponent $permissioncomponent,
		CustomButtonPermissions $custombuttonpermissions)
	{
		$this->config = $config;
		$this->event = $event;
		$this->language = $language;
		$this->component = $component;
		$this->fieldname = $fieldname;
		$this->typename = $typename;
		$this->counter = $counter;
		$this->permission = $permission;
		$this->assetsrules = $assetsrules;
		$this->customtabs = $customtabs;
		$this->permissionviews = $permissionviews;
		$this->permissionfields = $permissionfields;
		$this->permissioncomponent = $permissioncomponent;
		$this->custombuttonpermissions = $custombuttonpermissions;
	}

	/**
	 * Get Access Sections
	 *
	 * @return  string
	 * @since 3.2.0
	 */
	public function get(): string
	{
		// access size counter
		$this->counter->accessSize = 12; // ;)

		// Trigger Event: jcb_ce_onBeforeBuildAccessSections
		$this->event->trigger(
			'jcb_ce_onBeforeBuildAccessSections'
		);

		// Get the default fields
		$default_fields = $this->config->default_fields;
		$this->permissioncomponent->add('->HEAD<-', [
			'name' => 'core.admin',
			'title' => 'JACTION_ADMIN',
			'description' => 'JACTION_ADMIN_COMPONENT_DESC'
		], true);
		$this->permissioncomponent->add('->HEAD<-', [
			'name' => 'core.options',
			'title' => 'JACTION_OPTIONS',
			'description' => 'JACTION_OPTIONS_COMPONENT_DESC'
		], true);
		$this->permissioncomponent->add('->HEAD<-', [
			'name' => 'core.manage',
			'title' => 'JACTION_MANAGE',
			'description' => 'JACTION_MANAGE_COMPONENT_DESC'
		], true);

		if ($this->config->get('add_eximport', false))
		{
			$exportTitle = $this->config->lang_prefix . '_'
				. StringHelper::safe('Export Data', 'U');
			$exportDesc  = $this->config->lang_prefix . '_'
				. StringHelper::safe('Export Data', 'U')
				. '_DESC';
			$this->language->set('bothadmin', $exportTitle, 'Export Data');
			$this->language->set(
				'bothadmin', $exportDesc,
				' Allows users in this group to export data.'
			);
			$this->permissioncomponent->add('->HEAD<-', [
				'name' => 'core.export',
				'title' => $exportTitle,
				'description' => $exportDesc
			], true);

			// the size needs increase
			$this->counter->accessSize++;
			$importTitle = $this->config->lang_prefix . '_'
				. StringHelper::safe('Import Data', 'U');
			$importDesc  = $this->config->lang_prefix . '_'
				. StringHelper::safe('Import Data', 'U')
				. '_DESC';
			$this->language->set('bothadmin', $importTitle, 'Import Data');
			$this->language->set(
				'bothadmin', $importDesc,
				' Allows users in this group to import data.'
			);
			$this->permissioncomponent->add('->HEAD<-', [
				'name' => 'core.import',
				'title' => $importTitle,
				'description' => $importDesc
			], true);

			// the size needs increase
			$this->counter->accessSize++;
		}

		// version permission
		$batchTitle = $this->config->lang_prefix . '_'
			. StringHelper::safe('Use Batch', 'U');
		$batchDesc  = $this->config->lang_prefix . '_'
			. StringHelper::safe('Use Batch', 'U') . '_DESC';
		$this->language->set('bothadmin', $batchTitle, 'Use Batch');
		$this->language->set(
			'bothadmin', $batchDesc,
			' Allows users in this group to use batch copy/update method.'
		);
		$this->permissioncomponent->add('->HEAD<-', [
			'name' => 'core.batch',
			'title' => $batchTitle,
			'description' => $batchDesc
		], true);

		// version permission
		$importTitle = $this->config->lang_prefix . '_'
			. StringHelper::safe('Edit Versions', 'U');
		$importDesc  = $this->config->lang_prefix . '_'
			. StringHelper::safe('Edit Versions', 'U')
			. '_DESC';
		$this->language->set('bothadmin', $importTitle, 'Edit Version');
		$this->language->set(
			'bothadmin', $importDesc,
			' Allows users in this group to edit versions.'
		);
		$this->permissioncomponent->add('->HEAD<-', [
			'name' => 'core.version',
			'title' => $importTitle,
			'description' => $importDesc
		], true);

		// set the defaults
		$this->permissioncomponent->add('->HEAD<-', [
			'name' => 'core.create',
			'title' => 'JACTION_CREATE',
			'description' => 'JACTION_CREATE_COMPONENT_DESC'
		], true);
		$this->permissioncomponent->add('->HEAD<-', [
			'name' => 'core.delete',
			'title' => 'JACTION_DELETE',
			'description' => 'JACTION_DELETE_COMPONENT_DESC'
		], true);
		$this->permissioncomponent->add('->HEAD<-', [
			'name' => 'core.edit',
			'title' => 'JACTION_EDIT',
			'description' => 'JACTION_EDIT_COMPONENT_DESC'
		], true);
		$this->permissioncomponent->add('->HEAD<-', [
			'name' => 'core.edit.state',
			'title' => 'JACTION_EDITSTATE',
			'description' => 'JACTION_ACCESS_EDITSTATE_DESC'
		], true);
		$this->permissioncomponent->add('->HEAD<-', [
			'name' => 'core.edit.own',
			'title' => 'JACTION_EDITOWN',
			'description' => 'JACTION_EDITOWN_COMPONENT_DESC'
		], true);

		// set the Joomla fields
		if ($this->config->get('set_joomla_fields', false))
		{
			$this->permissioncomponent->add('->HEAD<-', [
				'name' => 'core.edit.value',
				'title' => 'JACTION_EDITVALUE',
				'description' => 'JACTION_EDITVALUE_COMPONENT_DESC'
			], true);

			// the size needs increase
			$this->counter->accessSize++;
		}

		// new custom created by permissions
		$created_byTitle = $this->config->lang_prefix . '_'
			. StringHelper::safe('Edit Created By', 'U');
		$created_byDesc  = $this->config->lang_prefix . '_'
			. StringHelper::safe('Edit Created By', 'U')
			. '_DESC';
		$this->language->set('bothadmin', $created_byTitle, 'Edit Created By');
		$this->language->set(
			'bothadmin', $created_byDesc,
			' Allows users in this group to edit created by.'
		);
		$this->permissioncomponent->add('->HEAD<-', [
			'name' => 'core.edit.created_by',
			'title' => $created_byTitle,
			'description' => $created_byDesc
		], true);

		// new custom created date permissions
		$createdTitle = $this->config->lang_prefix . '_'
			. StringHelper::safe('Edit Created Date', 'U');
		$createdDesc  = $this->config->lang_prefix . '_'
			. StringHelper::safe('Edit Created Date', 'U')
			. '_DESC';
		$this->language->set('bothadmin', $createdTitle, 'Edit Created Date');
		$this->language->set(
			'bothadmin', $createdDesc,
			' Allows users in this group to edit created date.'
		);
		$this->permissioncomponent->add('->HEAD<-', [
			'name' => 'core.edit.created',
			'title' => $createdTitle,
			'description' => $createdDesc
		], true);

		// set the menu controller lookup
		$menuControllers = ['access', 'submenu', 'dashboard_list', 'dashboard_add'];

		// set the custom admin views permissions
		if ($this->component->isArray('custom_admin_views'))
		{
			foreach ($this->component->get('custom_admin_views') as $custom_admin_view)
			{
				// new custom permissions to access this view
				$customAdminName  = $custom_admin_view['settings']->name;
				$customAdminCode  = $custom_admin_view['settings']->code;
				$customAdminTitle = $this->config->lang_prefix . '_'
					. StringHelper::safe(
						$customAdminName . ' Access', 'U'
					);
				$customAdminDesc  = $this->config->lang_prefix . '_'
					. StringHelper::safe(
						$customAdminName . ' Access', 'U'
					) . '_DESC';
				$sortKey          = StringHelper::safe(
					$customAdminName . ' Access'
				);
				$this->language->set(
					'bothadmin', $customAdminTitle, $customAdminName . ' Access'
				);
				$this->language->set(
					'bothadmin', $customAdminDesc,
					' Allows the users in this group to access '
					. StringHelper::safe($customAdminName, 'w')
					. '.'
				);
				$this->permissioncomponent->set($sortKey, [
					'name' => "$customAdminCode.access",
					'title' => $customAdminTitle,
					'description' => $customAdminDesc
				]);

				// the size needs increase
				$this->counter->accessSize++;

				// add the custom permissions to use the buttons of this view
				$this->custombuttonpermissions->add(
					$custom_admin_view['settings'], $customAdminName,
					$customAdminCode
				);

				// add menu controll view that has menus options
				foreach ($menuControllers as $menuController)
				{
					// add menu controll view that has menus options
					if (isset($custom_admin_view[$menuController])
						&& $custom_admin_view[$menuController])
					{
						$targetView_ = 'views.';
						if ($menuController === 'dashboard_add')
						{
							$targetView_ = 'view.';
						}

						// menucontroller
						$menucontrollerView['action']         = $targetView_
							. $menuController;
						$menucontrollerView['implementation'] = '2';
						if (isset($custom_admin_view['settings']->permissions)
							&& ArrayHelper::check(
								$custom_admin_view['settings']->permissions
							))
						{
							array_push(
								$custom_admin_view['settings']->permissions,
								$menucontrollerView
							);
						}
						else
						{
							$custom_admin_view['settings']->permissions
								= [];
							$custom_admin_view['settings']->permissions[]
								= $menucontrollerView;
						}
						unset($menucontrollerView);
					}
				}

				$this->permission->set(
					$custom_admin_view, $customAdminCode, $customAdminCode,
					$menuControllers, 'customAdmin'
				);
			}
		}

		// set the site views permissions
		if ($this->component->isArray('site_views'))
		{
			foreach ($this->component->get('site_views') as $site_view)
			{
				// new custom permissions to access this view
				$siteName  = $site_view['settings']->name;
				$siteCode  = $site_view['settings']->code;
				$siteTitle = $this->config->lang_prefix . '_'
					. StringHelper::safe(
						$siteName . ' Access Site', 'U'
					);
				$siteDesc  = $this->config->lang_prefix . '_'
					. StringHelper::safe(
						$siteName . ' Access Site', 'U'
					) . '_DESC';
				$sortKey   = StringHelper::safe(
					$siteName . ' Access Site'
				);

				if (isset($site_view['access']) && $site_view['access'] == 1)
				{
					$this->language->set(
						'bothadmin', $siteTitle, $siteName . ' (Site) Access'
					);
					$this->language->set(
						'bothadmin', $siteDesc,
						' Allows the users in this group to access site '
						. StringHelper::safe($siteName, 'w')
						. '.'
					);
					$this->permissioncomponent->set($sortKey, [
						'name' => "site.$siteCode.access",
						'title' => $siteTitle,
						'description' => $siteDesc
					]);

					// the size needs increase
					$this->counter->accessSize++;

					// check if this site view requires access rule to default to public
					if (isset($site_view['public_access'])
						&& $site_view['public_access'] == 1)
					{
						// we use one as public group (TODO we see if we run into any issues)
						$this->assetsrules->add('site', '"site.' . $siteCode
							. '.access":{"1":1}');
					}
				}

				// add the custom permissions to use the buttons of this view
				$this->custombuttonpermissions->add(
					$site_view['settings'], $siteName, $siteCode
				);
			}
		}

		if ($this->component->isArray('admin_views'))
		{
			foreach ($this->component->get('admin_views') as $view)
			{
				// set view name
				$nameView  = StringHelper::safe(
					$view['settings']->name_single
				);
				$nameViews = StringHelper::safe(
					$view['settings']->name_list
				);

				// add custom tab permissions if found
				if (($tabs_ = $this->customtabs->get($nameView)) !== null
					&& ArrayHelper::check($tabs_))
				{
					foreach ($tabs_ as $_customTab)
					{
						if (isset($_customTab['permission'])
							&& $_customTab['permission'] == 1)
						{
							$this->permissioncomponent->set($_customTab['sortKey'], [
								'name' => $_customTab['view'] . '.' . $_customTab['code'] . '.viewtab',
								'title' => $_customTab['lang_permission'],
								'description' => $_customTab['lang_permission_desc']
							]);

							// the size needs increase
							$this->counter->accessSize++;
						}
					}
				}

				// add the custom permissions to use the buttons of this view
				$this->custombuttonpermissions->add(
					$view['settings'], $view['settings']->name_single, $nameView
				);

				if ($nameView != 'component')
				{
					// add menu controll view that has menus options
					foreach ($menuControllers as $menuController)
					{
						// add menu controll view that has menus options
						if (isset($view[$menuController])
							&& $view[$menuController])
						{
							$targetView_ = 'views.';
							if ($menuController === 'dashboard_add')
							{
								$targetView_ = 'view.';
							}
							// menucontroller
							$menucontrollerView['action'] = $targetView_ . $menuController;
							$menucontrollerView['implementation'] = '2';
							if (isset($view['settings']->permissions)
								&& ArrayHelper::check(
									$view['settings']->permissions
								))
							{
								array_push(
									$view['settings']->permissions,
									$menucontrollerView
								);
							}
							else
							{
								$view['settings']->permissions = [];
								$view['settings']->permissions[] = $menucontrollerView;
							}
							unset($menucontrollerView);
						}
					}

					// check if there are fields
					if (ArrayHelper::check($view['settings']->fields))
					{
						// field permission options
						$permission_options = [1 => 'edit', 2 => 'access', 3 => 'view'];

						// check the fields for their permission settings
						foreach ($view['settings']->fields as $field)
						{
							// see if field require permissions to be set
							if (isset($field['permission'])
								&& ArrayHelper::check(
									$field['permission']
								))
							{
								if (ArrayHelper::check(
									$field['settings']->properties
								))
								{
									$fieldType = $this->typename->get($field);
									$fieldName = $this->fieldname->get(
										$field, $nameViews
									);

									// loop the permission options
									foreach ($field['permission'] as $permission_id)
									{
										// set the permission key word
										$permission_option = $permission_options[(int) $permission_id];

										// reset the bucket
										$fieldView = [];

										// set the permission for this field
										$fieldView['action'] = 'view.' . $permission_option . '.' . $fieldName;
										$fieldView['implementation'] = '3';

										// check if persmissions was already set
										if (isset($view['settings']->permissions)
											&& ArrayHelper::check(
												$view['settings']->permissions
											))
										{
											array_push($view['settings']->permissions, $fieldView);
										}
										else
										{
											$view['settings']->permissions = [];
											$view['settings']->permissions[] = $fieldView;
										}

										// ensure that no default field get loaded
										if (!in_array($fieldName, $default_fields))
										{
											// load to global field permission set
											$this->permissionfields->
												set("$nameView.$fieldName.$permission_option", $fieldType);
										}
									}
								}
							}
						}
					}

					$this->permission->set(
						$view, $nameView, $nameViews, $menuControllers
					);
				}
			}

			// Trigger Event: jcb_ce_onAfterBuildAccessSections
			$this->event->trigger(
				'jcb_ce_onAfterBuildAccessSections'
			);

			if ($this->config->get('add_api') && $this->config->get('joomla_version', 3) >= 5)
			{
				$this->addApiPermission();
			}

			/// now build the section
			$component = $this->permissioncomponent->build();

			// add views to the component section
			$component .= $this->permissionviews->build();

			// remove the fix, is not needed
			if ($this->counter->accessSize < 30)
			{
				// since we have less than 30 actions
				// we do not need the fix for this component
				$this->config->set('add_assets_table_fix', 0);
			}
			else
			{
				// get the worst case column size required (can be worse I know)
				// access/action size x 20 characters x 8 groups
				$character_length = (int) MathHelper::bc(
					'mul', $this->counter->accessSize, 20, 0
				);

				// set worse case
				$this->config->set('access_worse_case', (int) MathHelper::bc(
					'mul', $character_length, 8, 0
				));
			}

			// return the build
			return $component;
		}

		return false;
	}

	/**
	 * Add the component API action unless a compiler extension already supplied it.
	 *
	 * @return  void
	 * @since   6.3.0
	 */
	private function addApiPermission(): void
	{
		$actions = $this->permissioncomponent->toArray();

		if (in_array('core.api', array_column($actions['->HEAD<-'] ?? [], 'name'), true)
			|| in_array('core.api', array_column($actions, 'name'), true))
		{
			return;
		}

		$description = $this->config->lang_prefix . '_ACCESS_WEB_SERVICES_DESC';
		$this->language->set('bothadmin', $description, 'Allows users in this group to access this component through web services.');
		$this->permissioncomponent->add('->HEAD<-', [
			'name' => 'core.api',
			'title' => 'JACTION_API',
			'description' => $description
		], true);
		$this->counter->accessSize++;
	}
}

