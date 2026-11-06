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

namespace VDM\Joomla\Componentbuilder\Power;


use Joomla\Registry\Registry as JoomlaRegistry;
use Joomla\CMS\Factory as JoomlaFactory;
use Joomla\Input\Input;
use VDM\Joomla\Utilities\GetHelper;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Componentbuilder\Utilities\RepoHelper;
use VDM\Joomla\Componentbuilder\Abstraction\ComponentConfig;


/**
 * Compiler Configurations
 * 
 * 	All these functions are accessed via the direct name without the get:
 * 	example: $this->component_code_name calls: $this->getComponentcodename()
 * 
 * 	All values once called are cached, yet can be updated directly:
 * 	example: $this->component_code_name = 'new_code_name'; // be warned!
 * 
 * @since 3.2.0
 */
class Config extends ComponentConfig
{
	/**
	 * The Global Joomla Configuration
	 *
	 * @var     JoomlaRegistry
	 * @since 3.2.0
	 */
	protected JoomlaRegistry $config;

	/**
	 * Constructor
	 *
	 * @param Input|null            $input      Input
	 * @param JoomlaRegistry|null   $params     The component parameters
	 * @param JoomlaRegistry|null   $config     The Joomla configuration
	 *
	 * @throws \Exception
	 * @since 3.2.0
	 */
	public function __construct(?Input $input = null, ?JoomlaRegistry $params = null, ?JoomlaRegistry $config = null)
	{
		parent::__construct($input, $params);

		$this->config = $config ?: JoomlaFactory::getApplication()->getConfig();
	}

	/**
	 * get Gitea Username
	 *
	 * @return  string  the access token
	 * @since 3.2.0
	 */
	protected function getGiteausername(): ?string
	{
		return $this->params->get('gitea_username');
	}

	/**
	 * get Gitea Access Token
	 *
	 * @return  string  the access token
	 * @since 3.2.0
	 */
	protected function getGiteatoken(): ?string
	{
		return $this->params->get('gitea_token');
	}

	/**
	 * Get super power core organisation
	 *
	 * @return  string   The super power core organisation
	 * @since 3.2.0
	 */
	protected function getSuperpowerscoreorganisation(): string
	{
		// the VDM default organisation is [joomla]
		$organisation = 'joomla';

		return $organisation;
	}

	/**
	 * Get super power init repos
	 *
	 * @return  array The init repositories on Gitea
	 * @since 3.2.0
	 */
	protected function getSuperpowersinitrepos(): array
	{
		// some defaults repos we need by JCB
		$repos = [];

		// get the users own power repo (can overwrite all)
		if ($this->gitea_username !== null)
		{
			$repos[$this->gitea_username . '.super-powers'] = (object) [
				'organisation' => $this->gitea_username,
				'repository' => 'super-powers',
				'read_branch' => 'master'
			];
		}

		$repos[$this->super_powers_core_organisation . '.super-powers'] = (object) [
			'organisation' => $this->super_powers_core_organisation,
			'repository' => 'super-powers',
			'read_branch' => 'master'
		];
		$repos[$this->super_powers_core_organisation . '.gitea'] = (object) [
			'organisation' => $this->super_powers_core_organisation,
			'repository' => 'gitea',
			'read_branch' => 'master'
		];
		$repos[$this->super_powers_core_organisation . '.openai'] = (object) [
			'organisation' => $this->super_powers_core_organisation,
			'repository' => 'openai',
			'read_branch' => 'master'
		];

		return $repos;
	}

	/**
	 * get temporary path
	 *
	 * @return  string  The temporary path
	 * @since 3.2.0
	 */
	protected function getTmppath(): string
	{
		// get the temporary path
		return $this->config->get('tmp_path');
	}

	/**
	 * Get switch to add super powers
	 *
	 * @return  bool  Switch to add super powers
	 * @since 3.2.0
	 */
	protected function getAddsuperpowers(): bool
	{
		return (bool) $this->params->get('powers_repository', 0);
	}

	/**
	 * Get switch to add own super powers
	 *
	 * @return  bool  Switch to add own super powers
	 * @since 3.2.0
	 */
	protected function getAddownpowers(): bool
	{
		if ($this->add_super_powers)
		{
			return (bool) $this->params->get('super_powers_repositories', 0);
		}

		return false;
	}

	/**
	 * Get local super powers repository path
	 *
	 * @return  string The path to the local repository
	 * @since 3.2.0
	 */
	protected function getLocalpowersrepositorypath(): string
	{
		$default = $this->tmp_path . '/super_powers';

		if (!$this->add_super_powers)
		{
			return $default;
		}

		return $this->params->get('local_powers_repository_path', $default);
	}

	/**
	 * Get super power approved paths
	 *
	 * @return  array The approved paths to the repositories on Gitea
	 * @since 3.2.0
	 */
	protected function getApprovedpaths(): array
	{
		// some defaults repos we need by JCB
		$approved = $this->super_powers_init_repos;

		$paths = RepoHelper::get(1); // super powers = 1

		if ($paths !== null)
		{
			foreach ($paths as $path)
			{
				$owner = $path->organisation ?? null;
				$repo = $path->repository ?? null;
				if ($owner !== null && $repo !== null)
				{
					// we make sure to get only the objects
					$approved = ["{$owner}.{$repo}" => $path] + $approved;
				}
			}
		}

		return array_values($approved);
	}

	/**
	 * get indentation value
	 *
	 * @return  string  Indentation value
	 * @since 3.2.2
	 */
	protected function getIndentationvalue(): string
	{
		return "\t";
	}
}

