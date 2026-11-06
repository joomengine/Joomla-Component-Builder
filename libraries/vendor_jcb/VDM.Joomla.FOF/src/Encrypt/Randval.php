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


use VDM\Joomla\FOF\Encrypt\Randvalinterface;


/**
 * Generates cryptographically-secure random values.
 * 
 * @package  FrameworkOnFramework
 * @since    1.0
 * @deprecated Use phpseclib/phpseclib version 3 Instead.
 */
class Randval implements Randvalinterface
{
	/**
	 * Returns a cryptographically secure random value.
	 *
	 * Since we only run on PHP 7+ we can use random_bytes(), which internally uses a crypto safe PRNG. If the function
	 * doesn't exist, Joomla already loads a secure polyfill.
	 *
	 * The reason this method exists is backwards compatibility with older versions of FOF. It also allows us to quickly
	 * address any future issues if Joomla drops the polyfill or otherwise find problems with PHP's random_bytes() on
	 * some weird host (you can't be too careful when releasing mass-distributed software).
	 *
	 * @param   integer  $bytes  How many bytes to return
	 *
	 * @return  string
	 */
	public function generate($bytes = 32)
	{
		return random_bytes($bytes);
	}

	/**
	 * Generate random bytes. Adapted from Joomla! 3.2.
	 *
	 * Since we only run on PHP 7+ we can use random_bytes(), which internally uses a crypto safe PRNG. If the function
	 * doesn't exist, Joomla already loads a secure polyfill.
	 *
	 * The reason this method exists is backwards compatibility with older versions of FOF. It also allows us to quickly
	 * address any future issues if Joomla drops the polyfill or otherwise find problems with PHP's random_bytes() on
	 * some weird host (you can't be too careful when releasing mass-distributed software).
	 *
	 * @param   integer  $length  Length of the random data to generate
	 *
	 * @return  string  Random binary data
	 */
	public function genRandomBytes($length = 32)
	{
		return random_bytes($length);
	}
}

