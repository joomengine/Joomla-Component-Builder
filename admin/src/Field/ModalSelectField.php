<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    30th April, 2015
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace VDM\Component\Componentbuilder\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ModalSelectField as ModalSelectFieldCore;
use Joomla\Database\ParameterType;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * A modal content selection field, now with the radical ability to handle string-based GUIDs.
 * Because, apparently, assuming everything is an integer was the hill to die on.
 *
 * This override exists solely to bypass a hardcoded constraint that shouldn't have been there in the first place.
 * But hey, at least we get another class extension to maintain!
 *
 * @since  5.0.0
 */
class ModalSelectField extends ModalSelectFieldCore
{
	/**
	 * Method to retrieve the title of selected item.
	 *
	 * @return string
	 *
	 * @since   5.0.0
	 */
	protected function getValueTitle()
	{
		// Selecting the title for the field value, when required info were given
		if ($this->value && $this->sql_title_table && $this->sql_title_column && $this->sql_title_key) {
			try {
				$db	= $this->getDatabase();
				$query = $db->getQuery(true)
					->select($db->quoteName($this->sql_title_column))
					->from($db->quoteName($this->sql_title_table))
					->where($db->quoteName($this->sql_title_key) . ' = :value')
					->bind(':value', $this->value, is_numeric($this->value) ? ParameterType::INTEGER : ParameterType::STRING);

				/**
				 *   All this—just because someone decided to hardcode [ParameterType::INTEGER] in the core.
				 *   We could have just handle it dynamically, but no...
				 *
				 *   Polymorphic behavior isn't the enemy. Hardcoded constraints that force class extensions?
				 *   Now *that's* the real problem. But sure, let's keep pretending that integers are the
				 *   only valid identifiers in a database.
				 */

				$db->setQuery($query);

				return $db->loadResult() ?: $this->value;
			} catch (\Throwable $e) {
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}
		}

		return $this->value;
	}
}