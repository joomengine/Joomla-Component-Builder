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

namespace VDM\Joomla\Componentbuilder\Utilities;


use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use VDM\Joomla\Utilities\JsonHelper;
use VDM\Joomla\Utilities\ArrayHelper;


/**
 * Repositories Helper
 * 
 * @since 3.2.2
 */
abstract class RepoHelper
{
	/**
	 * get available repositories of target area
	 *
	 * @param int   $target    The target area
	 *
	 * @return array|null   The result set
	 * @since  3.2.0
	 **/
	public static function get(int $target): ?array
	{
		$db = self::getDbo();
		$query = self::getQuery($db);
		$query->where($db->quoteName('published') . ' >= 1')
			->where($db->quoteName('target') . ' = ' . $target)
			->order($db->quoteName('ordering') . ' desc');
		$db->setQuery($query);
		$db->execute();

		if ($db->getNumRows())
		{
			$items = $db->loadObjectList();
			$options = [];
			foreach($items as $item)
			{
				self::modelRepoDetails($item);

				$path = $item->organisation . '/' . $item->repository;
				$options[$path] =  $item;
			}

			return $options;
		}

		return null;
	}

	/**
	 * get a repository
	 *
	 * @param string   $guid    The repository guid
	 *
	 * @return array|null   The repository
	 * @since  5.1.4
	 **/
	public static function getRepo(string $guid): ?object
	{
		$db = self::getDbo();
		$query = self::getQuery($db);
		$query->where($db->quoteName('guid') . ' = ' . $db->quote($guid));
		$db->setQuery($query);
		$db->execute();

		if ($db->getNumRows())
		{
			$item = $db->loadObject();
			self::modelRepoDetails($item);

			return $item;
		}
		return null;
	}

	/**
	 * get query object
	 *
	 * @return object   The result set
	 * @since  5.1.4
	 **/
	protected static function getQuery(?DatabaseInterface $db = null): object
	{
		$db ??= self::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array(
				'type',
				'base',
				'organisation',
				'repository',
				'read_branch',
				'write_branch',
				'token',
				'username',
				'author_name',
				'author_email',
				'target',
				'access_repo',
				'addplaceholders',
				'guid'
			)))
			->from($db->quoteName('#__componentbuilder_repository'));

		return $query;
	}

	/**
	 * get the database
	 *
	 * @return DatabaseInterface
	 * @since  5.1.4
	 */
	protected static function getDbo(): DatabaseInterface
	{
		if (Factory::getContainer()->has(DatabaseInterface::class))
		{
			return Factory::getContainer()->get(DatabaseInterface::class);
		}

		return Factory::getDbo();
	}

	/**
	 * Model the repo details
	 *
	 * @param object &$item An object with values to model.
	 *
	 * @return void
	 * @since  5.1.1
	 */
	protected static function modelRepoDetails(object &$item): void
	{
		// Helper: Check if string is empty, whitespace, or null
		$isEmpty = fn(?string $v): bool =>
			$v === null || trim($v) === '';

		// Helper: Check if string is only digits
		$isOnlyDigits = fn(string $v): bool =>
			preg_match('/^\d+$/', $v);

		// Helper: Check if email has an @
		$isValidEmail = fn(string $v): bool =>
			strpos($v, '@') !== false;

		if ($item->access_repo != 1)
		{
			unset($item->username);
			unset($item->token);
		}
		unset($item->access_repo);

		$item->placeholders = self::setPlaceholders($item->addplaceholders ?? '');
		unset($item->addplaceholders);

		$item->target = self::setTarget((int) ($item->type ?? 1));

		// Sanitize base url
		if ($item->target === 'github')
		{
			$item->base = 'https://api.github.com';
		}
		elseif (!property_exists($item, 'base') || !is_string($item->base)
			|| $isEmpty($item->base))
		{
			$item->base = null;
		}

		// Sanitize author_name
		if (!property_exists($item, 'author_name') || !is_string($item->author_name)
			|| $isEmpty($item->author_name) || $isOnlyDigits($item->author_name))
		{
			$item->author_name = null;
		}

		// Sanitize author_email
		if (!property_exists($item, 'author_email') || !is_string($item->author_email)
			|| $isEmpty($item->author_email) || !$isValidEmail($item->author_email))
		{
			$item->author_email = null;
		}

		// Sanitize author_name
		if (!property_exists($item, 'author_name') || !is_string($item->author_name)
			|| $isEmpty($item->author_name) || $isOnlyDigits($item->author_name))
		{
			$item->author_name = null;
		}
	}

	/**
	 * set the placeholders for this repo
	 *
	 * @param string   $placeholders    The repo placeholders
	 *
	 * @return array  The result set
	 * @since  5.0.3
	 **/
	protected static function setPlaceholders(string $placeholders): array
	{
		$bucket = [];
		if (JsonHelper::check($placeholders))
		{
			$placeholders = json_decode((string) $placeholders, true);
			if (ArrayHelper::check($placeholders))
			{
				foreach ($placeholders as $row)
				{
					$bucket[$row['target']] = $row['value'];
				}
			}
		}
		return $bucket;
	}

	/**
	 * Determine the repository system target name from its type identifier.
	 *
	 * @param int   $type   The repository system type identifier.
	 *
	 * @return string  The resolved target name ('gitea' or 'github').
	 * @since  5.1.1
	 **/
	protected static function setTarget(int $type): string
	{
		return $type === 1 ? 'gitea' : 'github';
	}
}

