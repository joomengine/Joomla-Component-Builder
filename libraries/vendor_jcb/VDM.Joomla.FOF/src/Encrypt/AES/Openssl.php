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

namespace VDM\Joomla\FOF\Encrypt\AES;


use VDM\Joomla\FOF\Encrypt\Randval;
use VDM\Joomla\FOF\Utils\Phpfunc;
use VDM\Joomla\FOF\Encrypt\AES\AesInterface;
use VDM\Joomla\FOF\Encrypt\AES\Abstraction;


/**
 * Openssl AES encryption class
 * 
 * @package  FrameworkOnFramework
 * @since    1.0
 * @deprecated Use phpseclib/phpseclib version 3 Instead.
 */
class Openssl extends Abstraction implements AesInterface
{
	/**
	 * The OpenSSL options for encryption / decryption
	 *
	 * @var  int
	 */
	protected $openSSLOptions = 0;

	/**
	 * The encryption method to use
	 *
	 * @var  string
	 */
	protected $method = 'aes-128-cbc';

	public function __construct()
	{
		$this->openSSLOptions = OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING;
	}

	public function setEncryptionMode($mode = 'cbc', $strength = 128)
	{
		static $availableAlgorithms = null;
		static $defaultAlgo = 'aes-128-cbc';

		if (!is_array($availableAlgorithms))
		{
			$availableAlgorithms = openssl_get_cipher_methods();

			foreach (array('aes-256-cbc', 'aes-256-ecb', 'aes-192-cbc',
				         'aes-192-ecb', 'aes-128-cbc', 'aes-128-ecb') as $algo)
			{
				if (in_array($algo, $availableAlgorithms))
				{
					$defaultAlgo = $algo;
					break;
				}
			}
		}

		$strength = (int) $strength;
		$mode     = strtolower($mode);

		if (!in_array($strength, array(128, 192, 256)))
		{
			$strength = 256;
		}

		if (!in_array($mode, array('cbc', 'ebc')))
		{
			$mode = 'cbc';
		}

		$algo = 'aes-' . $strength . '-' . $mode;

		if (!in_array($algo, $availableAlgorithms))
		{
			$algo = $defaultAlgo;
		}

		$this->method = $algo;
	}

	public function encrypt($plainText, $key, $iv = null)
	{
		$iv_size = $this->getBlockSize();
		$key     = $this->resizeKey($key, $iv_size);
		$iv      = $this->resizeKey($iv, $iv_size);

		if (empty($iv))
		{
			$randVal   = new Randval();
			$iv        = $randVal->generate($iv_size);
		}

		$plainText .= $this->getZeroPadding($plainText, $iv_size);
		$cipherText = openssl_encrypt($plainText, $this->method, $key, $this->openSSLOptions, $iv);
		$cipherText = $iv . $cipherText;

		return $cipherText;
	}

	public function decrypt($cipherText, $key)
	{
		$iv_size    = $this->getBlockSize();
		$key        = $this->resizeKey($key, $iv_size);
		$iv         = substr($cipherText, 0, $iv_size);
		$cipherText = substr($cipherText, $iv_size);
		$plainText  = openssl_decrypt($cipherText, $this->method, $key, $this->openSSLOptions, $iv);

		return $plainText;
	}

	public function isSupported(?Phpfunc $phpfunc = null)
	{
		if (!is_object($phpfunc) || !($phpfunc instanceof $phpfunc))
		{
			$phpfunc = new Phpfunc();
		}

		if (!$phpfunc->function_exists('openssl_get_cipher_methods'))
		{
			return false;
		}

		if (!$phpfunc->function_exists('openssl_random_pseudo_bytes'))
		{
			return false;
		}

		if (!$phpfunc->function_exists('openssl_cipher_iv_length'))
		{
			return false;
		}

		if (!$phpfunc->function_exists('openssl_encrypt'))
		{
			return false;
		}

		if (!$phpfunc->function_exists('openssl_decrypt'))
		{
			return false;
		}

		if (!$phpfunc->function_exists('hash'))
		{
			return false;
		}

		if (!$phpfunc->function_exists('hash_algos'))
		{
			return false;
		}

		$algorightms = $phpfunc->openssl_get_cipher_methods();

		if (!in_array('aes-128-cbc', $algorightms))
		{
			return false;
		}

		$algorightms = $phpfunc->hash_algos();

		if (!in_array('sha256', $algorightms))
		{
			return false;
		}

		return true;
	}

	/**
	 * @return int
	 */
	public function getBlockSize()
	{
		return openssl_cipher_iv_length($this->method);
	}
}

