<?php
/**
 * @package    Joomla.Component.Builder
 * @created    23rd October, 2026
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Compiler\Creator\JoomlaSix;


use VDM\Joomla\Componentbuilder\Compiler\Creator\ConfigFieldsetsEmailHelper as SharedEmailHelper;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Utilities\String\NamespaceHelper;


/**
 * Email configuration fieldsets for Joomla six.
 * 
 * @since  6.2.0
 */
final class ConfigFieldsetsEmailHelper extends SharedEmailHelper
{
	/**
	 * Keep translations free of executable markup.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function dkimDescription(): string
	{
		return "<p>Using the below details, you need to configure your DNS by adding a TXT record on your domain: <b><span id='a_dkim_domain'></span></b></p>";
	}

	/**
	 * Use the component note field that activates the DKIM media script.
	 *
	 * @return  string
	 * @since   6.2.0
	 */
	protected function dkimNoteAttributes(): string
	{
		return 'type="Dkim" addfieldprefix="' . $this->config->namespace_prefix
			. '\\Component\\' . NamespaceHelper::safeSegment(StringHelper::safe($this->config->component_code_name, 'F'))
			. '\Administrator\Field"';
	}
}

