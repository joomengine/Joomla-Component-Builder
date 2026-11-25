<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    19th August, 2026
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\Component;


use Joomla\CMS\Factory;
use Joomla\Filter\OutputFilter;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne;
use VDM\Joomla\Componentbuilder\Compiler\Component;
use VDM\Joomla\Componentbuilder\Compiler\Component\Placeholder as ComponentPlaceholder;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Creator\AccessSections;
use VDM\Joomla\Componentbuilder\Compiler\Creator\ConfigFieldsets;
use VDM\Joomla\Componentbuilder\Compiler\Creator\EmailHelper;
use VDM\Joomla\Componentbuilder\Compiler\Creator\Helper;
use VDM\Joomla\Componentbuilder\Compiler\Customcode\Dispenser;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\ComHelperClass\CreateUserInterface;
use VDM\Joomla\Componentbuilder\Compiler\Placeholder;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Counter;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Utilities\ArrayHelper;


/**
 * Component Details Class.
 * 
 * Fills in everything the component says about itself before any of its views
 * are built: its names, who wrote it and when, the version the target expects,
 * the scripts and styles it carries, and the placeholders the component was
 * given of its own.
 * 
 * Every view built after this reads what is set here, so it runs once, first.
 * 
 * @since 6.1.7
 */
final class Details
{
	/**
	 * The Content One Builder Class.
	 *
	 * @var   ContentOne
	 * @since 6.1.7
	 */
	protected ContentOne $contentone;

	/**
	 * The Placeholder Class.
	 *
	 * @var   Placeholder
	 * @since 6.1.7
	 */
	protected Placeholder $placeholder;

	/**
	 * The Component Placeholder Class.
	 *
	 * @var   ComponentPlaceholder
	 * @since 6.1.7
	 */
	protected ComponentPlaceholder $componentplaceholder;

	/**
	 * The Component Class.
	 *
	 * @var   Component
	 * @since 6.1.7
	 */
	protected Component $component;

	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 6.1.7
	 */
	protected Config $config;

	/**
	 * The Counter Class.
	 *
	 * @var   Counter
	 * @since 6.1.7
	 */
	protected Counter $counter;

	/**
	 * The Customcode Dispenser Class.
	 *
	 * @var   Dispenser
	 * @since 6.1.7
	 */
	protected Dispenser $dispenser;

	/**
	 * The Component Image Type Class.
	 *
	 * @var   ImageType
	 * @since 6.1.7
	 */
	protected ImageType $imagetype;

	/**
	 * The Access Sections Creator Class.
	 *
	 * @var   AccessSections
	 * @since 6.1.7
	 */
	protected AccessSections $accesssections;

	/**
	 * The Config Fieldsets Creator Class.
	 *
	 * @var   ConfigFieldsets
	 * @since 6.1.7
	 */
	protected ConfigFieldsets $configfieldsets;

	/**
	 * The Create User Helper Class.
	 *
	 * @var   CreateUserInterface
	 * @since 6.1.7
	 */
	protected CreateUserInterface $createuser;

	/**
	 * The Helper Creator Class.
	 *
	 * @var   Helper
	 * @since 6.1.7
	 */
	protected Helper $helper;

	/**
	 * The Email Helper Creator Class.
	 *
	 * @var   EmailHelper
	 * @since 6.1.7
	 */
	protected EmailHelper $emailhelper;

	/**
	 * Constructor.
	 *
	 * @param ContentOne           $contentone               The Content One Builder Class.
	 * @param Placeholder          $placeholder              The Placeholder Class.
	 * @param ComponentPlaceholder $componentplaceholder     The Component Placeholder Class.
	 * @param Component            $component                The Component Class.
	 * @param Config               $config                   The Config Class.
	 * @param Counter              $counter                  The Counter Class.
	 * @param Dispenser            $dispenser                The Customcode Dispenser Class.
	 * @param ImageType            $imagetype                The Component Image Type Class.
	 * @param AccessSections       $accesssections           The Access Sections Creator Class.
	 * @param ConfigFieldsets      $configfieldsets          The Config Fieldsets Creator Class.
	 * @param CreateUserInterface  $createuser               The Create User Helper Class.
	 * @param Helper               $helper                   The Helper Creator Class.
	 * @param EmailHelper          $emailhelper              The Email Helper Creator Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(ContentOne $contentone,
		Placeholder $placeholder,
		ComponentPlaceholder $componentplaceholder,
		Component $component,
		Config $config,
		Counter $counter,
		Dispenser $dispenser,
		ImageType $imagetype,
		AccessSections $accesssections,
		ConfigFieldsets $configfieldsets,
		CreateUserInterface $createuser,
		Helper $helper,
		EmailHelper $emailhelper)
	{
		$this->contentone = $contentone;
		$this->placeholder = $placeholder;
		$this->componentplaceholder = $componentplaceholder;
		$this->component = $component;
		$this->config = $config;
		$this->counter = $counter;
		$this->dispenser = $dispenser;
		$this->imagetype = $imagetype;
		$this->accesssections = $accesssections;
		$this->configfieldsets = $configfieldsets;
		$this->createuser = $createuser;
		$this->helper = $helper;
		$this->emailhelper = $emailhelper;
	}

	/**
	 * Fill in what the component says about itself.
	 *
	 * @return  void
	 *
	 * @since   6.1.7
	 */
	public function set(): void
	{
		// COMPONENT
		$this->contentone->set('COMPONENT',
			$this->placeholder->get('COMPONENT')
		);

		// Component
		$this->contentone->set('Component',
			$this->placeholder->get('Component')
		);

		// component
		$this->contentone->set('component',
			$this->placeholder->get('component')
		);

		// ComponentNamespace
		$this->contentone->set('ComponentNamespace',
			$this->placeholder->get('ComponentNamespace')
		);

		// COMPANYNAME
		$companyname = $this->component->get('companyname');
		$this->contentone->set('COMPANYNAME', trim(
			(string) OutputFilter::cleanText($companyname)
		));

		// POWER_LIBRARY_FOLDER
		$this->contentone->set('POWER_LIBRARY_FOLDER',
			$this->config->power_library_folder
		);

		// CREATIONDATE
		$creationDate = $this->component->get('created');
		$creationDateFormat = Factory::getDate($creationDate)->format('jS F, Y');

		$this->contentone->set('CREATIONDATE', $creationDateFormat);
		$this->contentone->set('GLOBALCREATIONDATE', $creationDateFormat);

		$this->counter->projectStart = strtotime($creationDate);

		// BUILDDATE
		$this->contentone->set('BUILDDATE', Factory::getDate(
			$this->config->get('build_date', 'now'))->format('jS F, Y'));
		$this->contentone->set('GLOBALBUILDDATE',
			$this->contentone->get('BUILDDATE'));

		// AUTHOR
		$author = $this->component->get('author');
		$this->contentone->set('AUTHOR', trim(
			(string) OutputFilter::cleanText($author)
		));

		// AUTHOREMAIL
		$this->contentone->set('AUTHOREMAIL',
			trim((string) $this->component->get('email', ''))
		);

		// AUTHORWEBSITE
		$this->contentone->set('AUTHORWEBSITE',
			trim((string) $this->component->get('website', ''))
		);

		// COPYRIGHT
		$this->contentone->set('COPYRIGHT',
			trim((string) $this->component->get('copyright', ''))
		);

		// LICENSE
		$this->contentone->set('LICENSE',
			trim((string) $this->component->get('license', ''))
		);

		// VERSION
		$this->contentone->set('VERSION',
			trim((string) $this->component->get('component_version', ''))
		);
		// set the actual global version
		$this->contentone->set('ACTUALVERSION',
			$this->contentone->get('VERSION')
		);

		// do some Tweaks to the version based on selected options
		if (strpos((string) $this->contentone->get('VERSION'), '.') !== false)
		{
			$versionArray = explode(
				'.', (string) $this->contentone->get('VERSION')
			);
		}
		// load only first two values
		if (isset($versionArray)
			&& ArrayHelper::check(
				$versionArray
			) && $this->component->get('mvc_versiondate', 0) == 2)
		{
			$this->contentone->set('VERSION',
				$versionArray[0] . '.' . $versionArray[1] . '.x'
			);
		}
		// load only the first value
		elseif (isset($versionArray)
			&& ArrayHelper::check(
				$versionArray
			) && $this->component->get('mvc_versiondate', 0) == 3)
		{
			$this->contentone->set('VERSION',
				$versionArray[0] . '.x.x'
			);
		}
		unset($versionArray);

		// set the namespace prefix
		$this->contentone->set('NAMESPACEPREFIX',
			$this->placeholder->get('NAMESPACEPREFIX')
		);

		// set the global version in case
		$this->contentone->set('GLOBALVERSION',
			$this->contentone->get('VERSION')
		);

		// set the joomla target xml version
		$this->contentone->set('XMLVERSION',
			$this->config->joomla_versions[$this->config->joomla_version]['xml_version']
		);

		// set the api files block of the manifest
		$this->contentone->set('API_FILES', $this->apiFiles());

		// Component_name
		$name = $this->component->get('name');
		$this->contentone->set('Component_name',
			OutputFilter::cleanText($name)
		);

		// SHORT_DISCRIPTION
		$short_description = $this->component->get('short_description');
		$this->contentone->set('SHORT_DESCRIPTION', trim(
			(string) OutputFilter::cleanText(
				$short_description
			)
		));

		// DESCRIPTION
		$this->contentone->set('DESCRIPTION',
			trim((string) $this->component->get('description'))
		);

		// COMP_IMAGE_TYPE
		$this->contentone->set('COMP_IMAGE_TYPE',
			$this->imagetype->set($this->component->get('image') ?? '')
		);

		// ACCESS_SECTIONS
		$this->contentone->set('ACCESS_SECTIONS',
			$this->accesssections->get()
		);

		// CONFIG_FIELDSETS
		$keepLang   = $this->config->lang_target;
		$this->config->lang_target = 'admin';

		// start loading the category tree scripts
		$this->contentone->set('CATEGORY_CLASS_TREES', '');
		// run the field sets for first time
		$this->configfieldsets->set(1);
		$this->config->lang_target = $keepLang;

		// ADMINJS
		$this->contentone->set('ADMINJS',
			$this->placeholder->update_(
			$this->dispenser->hub['component_js']
		));
		// SITEJS
		$this->contentone->set('SITEJS',
			$this->placeholder->update_(
			$this->dispenser->hub['component_js']
		));

		// ADMINCSS
		$this->contentone->set('ADMINCSS',
			$this->placeholder->update_(
			$this->dispenser->hub['component_css_admin']
		));
		// SITECSS
		$this->contentone->set('SITECSS',
			$this->placeholder->update_(
			$this->dispenser->hub['component_css_site']
		));

		// CUSTOM_HELPER_SCRIPT
		$this->contentone->set('CUSTOM_HELPER_SCRIPT',
			$this->placeholder->update_(
			$this->dispenser->hub['component_php_helper_admin']
		));

		// BOTH_CUSTOM_HELPER_SCRIPT
		$this->contentone->set('BOTH_CUSTOM_HELPER_SCRIPT',
			$this->placeholder->update_(
			$this->dispenser->hub['component_php_helper_both']
		));

		// ADMIN_GLOBAL_EVENT_HELPER
		if (!$this->contentone->exists('ADMIN_GLOBAL_EVENT'))
		{
			$this->contentone->set('ADMIN_GLOBAL_EVENT', '');
		}
		if (!$this->contentone->exists('ADMIN_GLOBAL_EVENT_HELPER'))
		{
			$this->contentone->set('ADMIN_GLOBAL_EVENT_HELPER', '');
		}
		// now load the data for the global event if needed
		if ($this->component->get('add_admin_event', 0) == 1)
		{
			// ADMIN_GLOBAL_EVENT
			$this->contentone->add('ADMIN_GLOBAL_EVENT',
				PHP_EOL . PHP_EOL . '//' . Line::_(__LINE__, __CLASS__) . ' Trigger the Global Admin Event'
			);
			$this->contentone->add('ADMIN_GLOBAL_EVENT',
				PHP_EOL . $this->contentone->get('Component')
				. 'Helper::globalEvent(Factory::getDocument());');
			// ADMIN_GLOBAL_EVENT_HELPER
			$this->contentone->add('ADMIN_GLOBAL_EVENT_HELPER',
				PHP_EOL . PHP_EOL . Indent::_(1) . '/**'
			);
			$this->contentone->add('ADMIN_GLOBAL_EVENT_HELPER',
				PHP_EOL . Indent::_(1)
				. '*' . Indent::_(1) . 'The Global Admin Event Method.');
			$this->contentone->add('ADMIN_GLOBAL_EVENT_HELPER',
				PHP_EOL . Indent::_(1) . '**/'
			);
			$this->contentone->add('ADMIN_GLOBAL_EVENT_HELPER',
				PHP_EOL . Indent::_(1)
				. 'public static function globalEvent($document)');
			$this->contentone->add('ADMIN_GLOBAL_EVENT_HELPER',
				PHP_EOL . Indent::_(1) . '{'
			);
			$this->contentone->add('ADMIN_GLOBAL_EVENT_HELPER',
				PHP_EOL . $this->placeholder->update_(
					$this->dispenser->hub['component_php_admin_event']
				));
			$this->contentone->add('ADMIN_GLOBAL_EVENT_HELPER',
				PHP_EOL . Indent::_(1) . '}'
			);
		}

		// now load the readme file if needed
		if ($this->component->get('addreadme', 0) == 1)
		{
			$this->contentone->add('EXSTRA_ADMIN_FILES',
				PHP_EOL . Indent::_(3)
				. "<filename>README.txt</filename>");
		}

		// HELPER_CREATEUSER
		$this->contentone->add('HELPER_CREATEUSER',
			$this->createuser->get(
				$this->component->get('creatuserhelper', 0)
			)
		);

		// HELP
		$this->contentone->set('HELP', $this->helper->none());
		// HELP_SITE
		$this->contentone->set('HELP_SITE', $this->helper->none());

		// build route parse switch
		$this->contentone->set('ROUTER_PARSE_SWITCH', '');
		// build route views
		$this->contentone->set('ROUTER_BUILD_VIEWS', '');

		// add the helper emailer if set
		$this->contentone->set('HELPER_EMAIL', $this->emailhelper->get());

		// load the global placeholders
		foreach ($this->componentplaceholder->get() as $globalPlaceholder =>
			$gloabalValue
		)
		{
			$this->contentone->set($globalPlaceholder, $gloabalValue);
		}
	}

	/**
	 * The manifest block that installs the api folder.
	 *
	 * The block is only written when an admin view asked for an API, since
	 * the api folder is removed from the build otherwise.
	 *
	 * @return  string  The api files block, or nothing.
	 * @since   6.1.7
	 */
	private function apiFiles(): string
	{
		if ($this->config->get('add_api') === null)
		{
			return '';
		}

		$files = [];
		$files[] = PHP_EOL . PHP_EOL . Indent::_(1) . '<api>';
		$files[] = Indent::_(2) . '<files folder="api">';
		$files[] = Indent::_(3) . '<filename>index.html</filename>';
		$files[] = Indent::_(3) . '<folder>src</folder>';
		$files[] = Indent::_(2) . '</files>';
		$files[] = Indent::_(1) . '</api>';

		return implode(PHP_EOL, $files);
	}
}

