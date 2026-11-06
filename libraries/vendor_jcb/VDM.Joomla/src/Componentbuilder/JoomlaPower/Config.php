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

namespace VDM\Joomla\Componentbuilder\JoomlaPower;


use Joomla\Registry\Registry as JoomlaRegistry;
use Joomla\CMS\Factory as JoomlaFactory;
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
	 * @param Input|null        $input      Input
	 * @param Registry|null     $params     The component parameters
	 * @param Registry|null     $config     The Joomla configuration
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
	protected function getJoomlapowerscoreorganisation(): string
	{
		// the VDM default organisation is [joomla]
		$organisation = 'joomla';

		return $this->params->get('joomla_powers_core_organisation', $organisation);
	}

	/**
	 * Get Joomla power init repos
	 *
	 * @return  array The init repositories on Gitea
	 * @since 3.2.0
	 */
	protected function getJoomlapowersinitrepos(): array
	{
		// some defaults repos we need by JCB
		$repos = [];
		// get the users own power repo (can overwrite all)
		if (!empty($this->gitea_username))
		{
			$repos[$this->gitea_username . '.joomla-powers'] = (object) ['organisation' => $this->gitea_username, 'repository' => 'joomla-powers', 'read_branch' => 'master'];
		}
		$repos[$this->joomla_powers_core_organisation . '.joomla-powers'] = (object) ['organisation' => $this->joomla_powers_core_organisation, 'repository' => 'joomla-powers', 'read_branch' => 'master'];

		return $repos;
	}

	/**
	 * Get joomla power approved paths
	 *
	 * @return  array The approved paths to the repositories on Gitea
	 * @since 3.2.0
	 */
	protected function getApprovedjoomlapaths(): array
	{
		// some defaults repos we need by JCB
		$approved = $this->joomla_powers_init_repos;

		$paths = RepoHelper::get(2); // Joomla Power = 2

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
}

