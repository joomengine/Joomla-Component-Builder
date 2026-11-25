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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\Model;


use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Model\CheckInNowInterface;


/**
 * Check In Now Method for Joomla 6
 * 
 * @since 5.1.2
 */
final class CheckInNow implements CheckInNowInterface
{
	/**
	 * Get the generated call snippet that invokes the check-in method.
	 *
	 * @return string  The code that calls the generated method.
	 * @since  5.1.2
	 */
	public function getCall(): string
	{
		$call = PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__) . " Check in items";
		$call .= PHP_EOL . Indent::_(2) . "\$this->checkInNow();" . PHP_EOL;

		return $call;
	}

	/**
	 * Build the full `checkInNow()` method code for the given view/table.
	 *
	 * @param  string  $view       The view/table suffix (e.g. 'items').
	 * @param  string  $component  The component name (without 'com_').
	 *
	 * @return string  The full method code as a string.
	 * @since  5.1.2
	 */
	public function getMethod($view, $component): string
	{
		$checkin = PHP_EOL . PHP_EOL . Indent::_(1) . "/**";
		$checkin .= PHP_EOL . Indent::_(1) . " * Build an SQL query to check in all items left checked out longer then a set time.";
		$checkin .= PHP_EOL . Indent::_(1) . " *";
		$checkin .= PHP_EOL . Indent::_(1) . " * @return void";
		$checkin .= PHP_EOL . Indent::_(1) . " * @throws \DateMalformedStringException";
		$checkin .= PHP_EOL . Indent::_(1) . " * @since 3.2.0";
		$checkin .= PHP_EOL . Indent::_(1) . " */";
		$checkin .= PHP_EOL . Indent::_(1) . "protected function checkInNow(): void";
		$checkin .= PHP_EOL . Indent::_(1) . "{";
		$checkin .= PHP_EOL . Indent::_(2) . "//" . Line::_(__Line__, __Class__) . " Get set check in time";
		$checkin .= PHP_EOL . Indent::_(2) . "\$time = Joomla__"."_aeb8e463_291f_4445_9ac4_34b637c12dbd___Power::getParams('com_" . $component . "')->get('check_in');";
		$checkin .= PHP_EOL . PHP_EOL . Indent::_(2) . "if (\$time)";
		$checkin .= PHP_EOL . Indent::_(2) . "{";
		$checkin .= PHP_EOL . Indent::_(3) . "//" . Line::_(__LINE__,__CLASS__) . " Get a db connection.";
		$checkin .= PHP_EOL . Indent::_(3) . "\$db = \$this->getDatabase();";
		$checkin .= PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__) . " Reset query.";
		$checkin .= PHP_EOL . Indent::_(3) . "\$query = \$db->createQuery();";
		$checkin .= PHP_EOL . Indent::_(3) . "\$query->select('*');";
		$checkin .= PHP_EOL . Indent::_(3) . "\$query->from(\$db->quoteName('#__" . $component . "_" . $view . "'));";
		$checkin .= PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__) . " Only select items that are checked out.";

		$checkin .= PHP_EOL . Indent::_(3) . "\$query->where(\$db->quoteName('checked_out') . ' >= 0');";

		$checkin .= PHP_EOL . Indent::_(3) . "//" . Line::_(__Line__, __Class__) . " Query only to see if we have a rows";
		$checkin .= PHP_EOL . Indent::_(3) . "\$query->setLimit(1);";
		$checkin .= PHP_EOL . Indent::_(3) . "\$db->setQuery(\$query);";
		$checkin .= PHP_EOL . Indent::_(3) . "\$db->execute();";
		$checkin .= PHP_EOL . Indent::_(3) . "if (\$db->getNumRows())";
		$checkin .= PHP_EOL . Indent::_(3) . "{";
		$checkin .= PHP_EOL . Indent::_(4) . "//" . Line::_(__Line__, __Class__) . " Get target date in the past.";
		$checkin .= PHP_EOL . Indent::_(4) . "\$date = Joomla__"."_39403062_84fb_46e0_bac4_0023f766e827___Power::getDate()->modify(\$time)->toSql();";
		$checkin .= PHP_EOL . Indent::_(4) . "//" . Line::_(__Line__, __Class__) . " Reset query.";
		$checkin .= PHP_EOL . Indent::_(4) . "\$query = \$db->createQuery();";
		$checkin .= PHP_EOL . PHP_EOL . Indent::_(4) . "//" . Line::_(__LINE__,__CLASS__) . " Fields to update.";
		$checkin .= PHP_EOL . Indent::_(4) . "\$fields = [";

		$checkin .= PHP_EOL . Indent::_(5) . "\$db->quoteName('checked_out_time') . ' = NULL',";
		$checkin .= PHP_EOL . Indent::_(5) . "\$db->quoteName('checked_out') . ' = NULL'";

		$checkin .= PHP_EOL . Indent::_(4) . "];";
		$checkin .= PHP_EOL . PHP_EOL . Indent::_(4) . "//" . Line::_(__LINE__,__CLASS__) . " Conditions for which records should be updated.";
		$checkin .= PHP_EOL . Indent::_(4) . "\$conditions = [";

		$checkin .= PHP_EOL . Indent::_(5) . "\$db->quoteName('checked_out') . ' = 0 OR ' . \$db->quoteName('checked_out') . ' > 0',";

		$checkin .= PHP_EOL . Indent::_(5) . "\$db->quoteName('checked_out_time') . ' < ' . \$db->quote(\$date)";
		$checkin .= PHP_EOL . Indent::_(4) . "];";
		$checkin .= PHP_EOL . PHP_EOL . Indent::_(4) . "//" . Line::_(__LINE__,__CLASS__) . " Check table.";
		$checkin .= PHP_EOL . Indent::_(4) . "\$query->update(\$db->quoteName('#__" . $component . "_" . $view . "'))->set(\$fields)->where(\$conditions); ";
		$checkin .= PHP_EOL . PHP_EOL . Indent::_(4) . "\$db->setQuery(\$query);";
		$checkin .= PHP_EOL . PHP_EOL . Indent::_(4) . "\$db->execute();";
		$checkin .= PHP_EOL . Indent::_(3) . "}";
		$checkin .= PHP_EOL . Indent::_(2) . "}";
		$checkin .= PHP_EOL . Indent::_(1) . "}";

		return $checkin;
	}
}

