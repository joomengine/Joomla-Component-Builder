<?php
/**
 * @package       FrameworkOnFramework
 * @subpackage    Encryption
 * @copyright     Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @note	      This file has been modified by the Joomla! Project (and VDM) and no longer reflects the original work of its author.
 * @depreciation  This was ported for the sake of those who have stuff encrypted with the FOF encryption suite.
 *                  - Do not use this in new projects.
 *                  - Expect no updates.
 *                  - This is outdated.
 *                  - Not best choice for encryption.
 *                  - Use phpseclib/phpseclib version 3 Instead.
 *                  - Checkout the JCB Crypt Suite. <https://git.vdm.dev/joomla/phpseclib>
 */

namespace VDM\Joomla\FOF\Encrypt;


/**
 * Randvalinterface
 * 
 * @package  FrameworkOnFramework
 * @since    1.0
 * @deprecated Use phpseclib/phpseclib version 3 Instead.
 */
interface Randvalinterface
{
	/**
	 *
	 * Returns a cryptographically secure random value.
	 *
	 * @return string
	 *
	 */
	public function generate();
}

