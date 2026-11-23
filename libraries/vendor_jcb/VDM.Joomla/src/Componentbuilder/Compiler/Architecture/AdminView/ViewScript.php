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

namespace VDM\Joomla\Componentbuilder\Compiler\Architecture\AdminView;


use VDM\Joomla\Componentbuilder\Compiler\Architecture\Field\IfValueScript;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Field\OptionsScript;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Field\TargetControlsScript;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Field\TargetRelationScript;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Field\ValueScript;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentMulti;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ScriptMediaSwitch;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ScriptUserSwitch;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ValidationFix;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ViewScript as ViewScriptBuilder;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Customcode\Dispenser;
use VDM\Joomla\Componentbuilder\Compiler\Library\IncludeHelper;
use VDM\Joomla\Componentbuilder\Compiler\Model\Createdate;
use VDM\Joomla\Componentbuilder\Compiler\Model\Modifieddate;
use VDM\Joomla\Componentbuilder\Compiler\Placeholder;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Minify;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Placefix;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Structure;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Unique;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Utilities\StringHelper;


/**
 * Admin View Script Class.
 * 
 * Builds the javascript an admin view carries. The conditions the view
 * declares become one function per condition, the listeners that call it, the
 * tests it runs and the statements that reveal, hide and re-require the fields
 * it steers; the view's own custom code is appended; and the result is
 * minified when the build asks for it and stored for the view's edit file, its
 * footer and its list file.
 * 
 * Nothing here is decided by the Joomla version being compiled for, so there
 * is one class for every target.
 * 
 * @since  6.1.7
 */
final class ViewScript
{
	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 6.1.7
	 */
	protected Config $config;

	/**
	 * The Placeholder Class.
	 *
	 * @var   Placeholder
	 * @since 6.1.7
	 */
	protected Placeholder $placeholder;

	/**
	 * The Customcode Dispenser Class.
	 *
	 * @var   Dispenser
	 * @since 6.1.7
	 */
	protected Dispenser $dispenser;

	/**
	 * The Structure Class.
	 *
	 * @var   Structure
	 * @since 6.1.7
	 */
	protected Structure $structure;

	/**
	 * The Library Include Helper Class.
	 *
	 * @var   IncludeHelper
	 * @since 6.1.7
	 */
	protected IncludeHelper $includehelper;

	/**
	 * The Create Date Class.
	 *
	 * @var   Createdate
	 * @since 6.1.7
	 */
	protected Createdate $createdate;

	/**
	 * The Modified Date Class.
	 *
	 * @var   Modifieddate
	 * @since 6.1.7
	 */
	protected Modifieddate $modifieddate;

	/**
	 * The Content Multi Builder Class.
	 *
	 * @var   ContentMulti
	 * @since 6.1.7
	 */
	protected ContentMulti $contentmulti;

	/**
	 * The Script Media Switch Class.
	 *
	 * @var   ScriptMediaSwitch
	 * @since 6.1.7
	 */
	protected ScriptMediaSwitch $scriptmediaswitch;

	/**
	 * The Script User Switch Class.
	 *
	 * @var   ScriptUserSwitch
	 * @since 6.1.7
	 */
	protected ScriptUserSwitch $scriptuserswitch;

	/**
	 * The Validation Fix Class.
	 *
	 * @var   ValidationFix
	 * @since 6.1.7
	 */
	protected ValidationFix $validationfix;

	/**
	 * The View Script Builder Class.
	 *
	 * @var   ViewScriptBuilder
	 * @since 6.1.7
	 */
	protected ViewScriptBuilder $viewscript;

	/**
	 * The Field Value Script Class.
	 *
	 * @var   ValueScript
	 * @since 6.1.7
	 */
	protected ValueScript $valuescript;

	/**
	 * The Field Options Script Class.
	 *
	 * @var   OptionsScript
	 * @since 6.1.7
	 */
	protected OptionsScript $optionsscript;

	/**
	 * The Field If Value Script Class.
	 *
	 * @var   IfValueScript
	 * @since 6.1.7
	 */
	protected IfValueScript $ifvaluescript;

	/**
	 * The Field Target Controls Script Class.
	 *
	 * @var   TargetControlsScript
	 * @since 6.1.7
	 */
	protected TargetControlsScript $targetcontrolsscript;

	/**
	 * The Field Target Relation Script Class.
	 *
	 * @var   TargetRelationScript
	 * @since 6.1.7
	 */
	protected TargetRelationScript $targetrelationscript;

	/**
	 * Constructor.
	 *
	 * @param Config                $config                The Config Class.
	 * @param Placeholder           $placeholder           The Placeholder Class.
	 * @param Dispenser             $dispenser             The Customcode Dispenser Class.
	 * @param Structure             $structure             The Structure Class.
	 * @param IncludeHelper         $includehelper         The Library Include Helper Class.
	 * @param Createdate            $createdate            The Create Date Class.
	 * @param Modifieddate          $modifieddate          The Modified Date Class.
	 * @param ContentMulti          $contentmulti          The Content Multi Builder Class.
	 * @param ScriptMediaSwitch     $scriptmediaswitch     The Script Media Switch Class.
	 * @param ScriptUserSwitch      $scriptuserswitch      The Script User Switch Class.
	 * @param ValidationFix         $validationfix         The Validation Fix Class.
	 * @param ViewScriptBuilder     $viewscript            The View Script Builder Class.
	 * @param ValueScript           $valuescript           The Field Value Script Class.
	 * @param OptionsScript         $optionsscript         The Field Options Script Class.
	 * @param IfValueScript         $ifvaluescript         The Field If Value Script Class.
	 * @param TargetControlsScript  $targetcontrolsscript  The Field Target Controls Script Class.
	 * @param TargetRelationScript  $targetrelationscript  The Field Target Relation Script Class.
	 *
	 * @since 6.1.7
	 */
	public function __construct(Config $config,
		Placeholder $placeholder,
		Dispenser $dispenser,
		Structure $structure,
		IncludeHelper $includehelper,
		Createdate $createdate,
		Modifieddate $modifieddate,
		ContentMulti $contentmulti,
		ScriptMediaSwitch $scriptmediaswitch,
		ScriptUserSwitch $scriptuserswitch,
		ValidationFix $validationfix,
		ViewScriptBuilder $viewscript,
		ValueScript $valuescript,
		OptionsScript $optionsscript,
		IfValueScript $ifvaluescript,
		TargetControlsScript $targetcontrolsscript,
		TargetRelationScript $targetrelationscript)
	{
		$this->config = $config;
		$this->placeholder = $placeholder;
		$this->dispenser = $dispenser;
		$this->structure = $structure;
		$this->includehelper = $includehelper;
		$this->createdate = $createdate;
		$this->modifieddate = $modifieddate;
		$this->contentmulti = $contentmulti;
		$this->scriptmediaswitch = $scriptmediaswitch;
		$this->scriptuserswitch = $scriptuserswitch;
		$this->validationfix = $validationfix;
		$this->viewscript = $viewscript;
		$this->valuescript = $valuescript;
		$this->optionsscript = $optionsscript;
		$this->ifvaluescript = $ifvaluescript;
		$this->targetcontrolsscript = $targetcontrolsscript;
		$this->targetrelationscript = $targetrelationscript;
	}
	/**
	 * Build the javascript this admin view carries.
	 *
	 * Nothing is returned: the three scripts the view ends up with are stored
	 * on the view script builder, and the list view's javascript file, if it
	 * gets one, is registered on the content builder here.
	 *
	 * @param   array  $viewArray  The admin view, as the component data carries it.
	 *
	 * @return  void
	 *
	 * @since   6.1.7
	 */
	public function get(array $viewArray): void
	{
		// set the view name
		$nameSingleCode = $viewArray['settings']->name_single_code;
		$this->validationfix->setConditions($nameSingleCode, []);
		// add conditions to this view
		if (isset($viewArray['settings']->conditions)
			&& ArrayHelper::check(
				$viewArray['settings']->conditions
			))
		{
			// reset defaults
			$getValue       = [];
			$ifValue        = [];
			$targetControls = [];
			$functions      = [];
			$serverConditions = [];
			$serverGroups = [];
			$conditionTargets = [];

			foreach ($viewArray['settings']->conditions as $condition)
			{
				if (isset($condition['match_name'])
					&& StringHelper::check(
						$condition['match_name']
					))
				{
					$uniqueVar      = Unique::get(7);
					$matchName      = $condition['match_name'] . '_'
						. $uniqueVar;
					$targetBehavior = ($condition['target_behavior'] == 1
						|| $condition['target_behavior'] == 3) ? 'show'
						: 'hide';
					$targetDefault  = ($condition['target_behavior'] == 1
						|| $condition['target_behavior'] == 3) ? 'hide'
						: 'show';

					// set the realtation if any
					if ($condition['target_relation'])
					{
						// chain to other items of the same target
						$relations = $this->targetrelationscript->get(
							(array) $viewArray['settings']->conditions,
							(array) $condition, (string) $nameSingleCode
						);
						if (ArrayHelper::check($relations))
						{
							// set behavior and default array
							$behaviors[$matchName] = $targetBehavior;
							$defaults[$matchName]  = $targetDefault;
							$toggleSwitch[$matchName]
								= ($condition['target_behavior']
								== 1
								|| $condition['target_behavior'] == 2) ? true
								: false;
							// set the type buket
							$typeBuket[$matchName] = $condition['match_type'];
							// set function array
							$functions[$uniqueVar][0] = $matchName;
							$matchNames[$matchName]
								= $condition['match_name'];
							// get the select value
							$getValue[$matchName] = $this->valuescript->get(
								$condition['match_type'],
								(string) $condition['match_name'],
								$condition['match_extends'],
								(string) $uniqueVar
							);
							// get the options
							$options = $this->optionsscript->get(
								$condition['match_type'],
								$condition['match_options']
							);
							$serverConditions[$matchName] = $this->validationCondition($condition, $options);
							// set the if values
							$ifValue[$matchName] = $this->ifvaluescript->get(
								(string) $matchName,
								$condition['match_behavior'],
								$condition['match_type'], $options
							);
							// set the target controls
							$targetControls[$matchName]
								= $this->targetcontrolsscript->get(
								(bool) $toggleSwitch[$matchName],
								$condition['target_field'],
								(string) $targetBehavior,
								(string) $targetDefault, (string) $uniqueVar,
								(string) $nameSingleCode
							);

							foreach ($relations as $relation)
							{
								if (StringHelper::check(
									$relation['match_name']
								))
								{
									$relationName = $relation['match_name']
										. '_' . $uniqueVar;
									// set the type buket
									$typeBuket[$relationName]
										= $relation['match_type'];
									// set function array
									$functions[$uniqueVar][] = $relationName;
									$matchNames[$relationName]
										= $relation['match_name'];
									// get the relation option
									$relationOptions = $this->optionsscript->get(
										$relation['match_type'],
										$relation['match_options']
									);
									$serverConditions[$relationName] = $this->validationCondition($relation, $relationOptions);
									$getValue[$relationName]
										= $this->valuescript->get(
										$relation['match_type'],
										(string) $relation['match_name'],
										$relation['match_extends'],
										(string) $uniqueVar
									);
									$ifValue[$relationName]
										= $this->ifvaluescript->get(
										(string) $relationName,
										$relation['match_behavior'],
										$relation['match_type'],
										$relationOptions
									);
								}
							}
						}
					}
					else
					{
						// set behavior and default array
						$behaviors[$matchName] = $targetBehavior;
						$defaults[$matchName]  = $targetDefault;
						$toggleSwitch[$matchName]
							= ($condition['target_behavior']
							== 1
							|| $condition['target_behavior'] == 2) ? true
							: false;
						// set the type buket
						$typeBuket[$matchName] = $condition['match_type'];
						// set function array
						$functions[$uniqueVar][0] = $matchName;
						$matchNames[$matchName]   = $condition['match_name'];
						// get the select value
						$getValue[$matchName] = $this->valuescript->get(
							$condition['match_type'],
							(string) $condition['match_name'],
							$condition['match_extends'], (string) $uniqueVar
						);
						// get the options
						$options = $this->optionsscript->get(
							$condition['match_type'],
							$condition['match_options']
						);
						$serverConditions[$matchName] = $this->validationCondition($condition, $options);
						// set the if values
						$ifValue[$matchName] = $this->ifvaluescript->get(
							(string) $matchName, $condition['match_behavior'],
							$condition['match_type'], $options
						);
						// set the target controls
						$targetControls[$matchName]
							= $this->targetcontrolsscript->get(
							(bool) $toggleSwitch[$matchName],
							$condition['target_field'],
							(string) $targetBehavior, (string) $targetDefault,
							(string) $uniqueVar, (string) $nameSingleCode
						);
					}
				}
			}
			// reset buckets
			$initial    = '';
			$func       = '';
			$isSet      = '';
			$listener   = '';
			if (ArrayHelper::check($functions))
			{
				$refreshName = 'update' . str_replace(
					' ', '', ucwords(str_replace('_', ' ', $nameSingleCode))
				) . 'Conditions';
				$calls = '';
				foreach ($functions as $function => $matchKeys)
				{
					foreach ($matchKeys as $matchKey)
					{
						if ($ifValue[$matchKey] === 0 || $getValue[$matchKey]['get'] === '')
						{
							throw new \UnexpectedValueException(sprintf(
								'Unsupported condition in view "%s": field "%s", operator %d, type "%s". '
								. 'Select an operator supported by the watched field type.',
								$nameSingleCode,
								$matchNames[$matchKey],
								$serverConditions[$matchKey]['behavior'],
								$typeBuket[$matchKey]
							));
						}
					}
					$calls .= $this->functionCall($function, $matchKeys, $getValue)['code'];
				}

				// Every selector change recomputes the complete ordered decision.
				$watched = [];
				$modal = false;
				foreach ($matchNames as $matchKey => $name)
				{
					$matchType = $typeBuket[$matchKey];
					if ($this->scriptmediaswitch->inArray($matchType))
					{
						$modal = true;
						continue;
					}
					if ($this->scriptuserswitch->inArray($matchType))
					{
						$name .= '_id';
					}
					$watched[$name] = true;
				}
				foreach (array_keys($watched) as $name)
				{
					$listener .= PHP_EOL . "jQuery('#jform_" . $name . "').on('keyup', " . $refreshName . ");"
						. PHP_EOL . "jQuery('#adminForm').on('change', '#jform_" . $name . "', " . $refreshName . ");" . PHP_EOL;
				}
				if ($modal)
				{
					$listener .= PHP_EOL . "window.SqueezeBox.initialize({onClose: " . $refreshName . "});" . PHP_EOL;
				}

				// now build the function
				$func = '';
				foreach ($functions as $f_function => $f_matchKeys)
				{
					$map = '';
					// does this function require an array
					$addArray = false;
					$func_    = $this->functionCall(
						$f_function, $f_matchKeys, $getValue
					);
					// set array switch
					if ($func_['array'])
					{
						$addArray = true;
					}
					$func      .= PHP_EOL . "//" . Line::_(__Line__, __Class__)
						. " the " . $f_function . " function";
					$func      .= PHP_EOL . "function " . $f_function . "(";
					$fucounter = 0;
					foreach ($f_matchKeys as $fu_matchKey)
					{
						if (StringHelper::check($fu_matchKey))
						{
							if ($fucounter == 0)
							{
								$func .= $fu_matchKey;
							}
							else
							{
								$func .= ',' . $fu_matchKey;
							}
							$fucounter++;
						}
					}
					$func .= ")";
					$func .= PHP_EOL . "{";
					if ($addArray)
					{
						foreach ($f_matchKeys as $a_matchKey)
						{
							$name = $matchNames[$a_matchKey];
							$func .= PHP_EOL . Indent::_(1) . "if (isSet("
								. $a_matchKey . ") && " . $a_matchKey
								. ".constructor !== Array)" . PHP_EOL
								. Indent::_(1) . "{" . PHP_EOL . Indent::_(2)
								. "var temp_" . $f_function . " = "
								. $a_matchKey . ";" . PHP_EOL . Indent::_(2)
								. "var " . $a_matchKey . " = [];" . PHP_EOL
								. Indent::_(2) . $a_matchKey . ".push(temp_"
								. $f_function . ");" . PHP_EOL . Indent::_(1)
								. "}";
							$func .= PHP_EOL . Indent::_(1) . "else if (!isSet("
								. $a_matchKey . "))" . PHP_EOL . Indent::_(1)
								. "{";
							$func .= PHP_EOL . Indent::_(2) . "var "
								. $a_matchKey . " = [];";
							$func .= PHP_EOL . Indent::_(1) . "}";
							$func .= PHP_EOL . Indent::_(1) . "var " . $name
								. " = " . $a_matchKey . ".some(" . $a_matchKey
								. "_SomeFunc);" . PHP_EOL;

							// setup the map function
							$map .= PHP_EOL . "//" . Line::_(__Line__, __Class__)
								. " the " . $f_function . " Some function";
							$map .= PHP_EOL . "function " . $a_matchKey
								. "_SomeFunc(" . $a_matchKey . ")";
							$map .= PHP_EOL . "{";
							$map .= PHP_EOL . Indent::_(1) . "//"
								. Line::_(__Line__, __Class__)
								. " set the function logic";
							$map .= PHP_EOL . Indent::_(1) . "if (";
							$if  = $ifValue[$a_matchKey];
							if (StringHelper::check($if))
							{
								$map .= $if;
							}
							$map .= ")";
							$map .= PHP_EOL . Indent::_(1) . "{";
							$map .= PHP_EOL . Indent::_(2) . "return true;";
							$map .= PHP_EOL . Indent::_(1) . "}" . PHP_EOL
								. Indent::_(1) . "return false;";
							$map .= PHP_EOL . "}" . PHP_EOL;
						}
						$func .= PHP_EOL . PHP_EOL . Indent::_(1) . "//"
							. Line::_(__Line__, __Class__)
							. " set this function logic";
						$func .= PHP_EOL . Indent::_(1) . "if (";
						// set if counter
						$aifcounter = 0;
						foreach ($f_matchKeys as $af_matchKey)
						{
							$name = $matchNames[$af_matchKey];
							if ($aifcounter == 0)
							{
								$func .= $name;
							}
							else
							{
								$func .= ' && ' . $name;
							}
							$aifcounter++;
						}
						$func .= ")" . PHP_EOL . Indent::_(1) . "{";
					}
					else
					{
						$func .= PHP_EOL . Indent::_(1) . "//" . Line::_(
								__LINE__,__CLASS__
							) . " set the function logic";
						$func .= PHP_EOL . Indent::_(1) . "if (";
						// set if counter
						$ifcounter = 0;
						foreach ($f_matchKeys as $f_matchKey)
						{
							$if = $ifValue[$f_matchKey];
							if (StringHelper::check($if))
							{
								$if = count($f_matchKeys) > 1 ? '(' . $if . ')' : $if;
								if ($ifcounter == 0)
								{
									$func .= $if;
								}
								else
								{
									$func .= ' && ' . $if;
								}
								$ifcounter++;
							}
						}
						$func .= ")" . PHP_EOL . Indent::_(1) . "{";
					}
					// get the controles
					$controls = $targetControls[$f_matchKeys[0]];
					// get target behavior and default
					$targetBehavior = $behaviors[$f_matchKeys[0]];
					$targetDefault  = $defaults[$f_matchKeys[0]];
					$requiredTargets = [];
					foreach ($controls as $target => $action)
					{
						$conditionTargets[$target] = $action;
						if ($action['required'])
						{
							$requiredTargets[] = $target;
						}
					}
					if ($requiredTargets !== [])
					{
						$matches = [];
						foreach ($f_matchKeys as $key)
						{
							$matches[] = $serverConditions[$key] + [
								'array' => $addArray,
								'supported' => $ifValue[$key] !== 0,
							];
						}
						$serverGroups[] = [
							'matches' => $matches,
							'targets' => $requiredTargets,
							'show' => $targetBehavior === 'show',
							'toggle' => $toggleSwitch[$f_matchKeys[0]],
						];
					}
					// load the target behavior
					foreach ($controls as $target => $action)
					{
						$func .= $action['behavior'];
						if (StringHelper::check(
							$action[$targetBehavior]
						))
						{
							$func .= $action[$targetBehavior];
						}
					}
					// check if this is a toggle switch
					if ($toggleSwitch[$f_matchKeys[0]])
					{
						$func .= PHP_EOL . Indent::_(1) . "}" . PHP_EOL
							. Indent::_(1) . "else" . PHP_EOL . Indent::_(1)
							. "{";
						// load the default behavior
						foreach ($controls as $target => $action)
						{
							$func .= $action['default'];
							if (StringHelper::check(
								$action[$targetDefault]
							))
							{
								$func .= $action[$targetDefault];
							}
						}
					}
					$func .= PHP_EOL . Indent::_(1) . "}" . PHP_EOL . "}"
						. PHP_EOL . $map;
				}
				$initial = $this->refreshScript($refreshName, $conditionTargets, $calls);

				// set the isSet function
				$this->validationfix->setConditions($nameSingleCode, $serverGroups);
				$isSet = $this->isSetFunction();
			}
			// load to this buket
			$fileScript   = $initial . $func . $isSet;
			$footerScript = $listener;
		}
		// add custom script to edit form JS file
		if (!isset($fileScript))
		{
			$fileScript = '';
		}
		$fileScript .= $this->dispenser->get(
			'view_file', $nameSingleCode, PHP_EOL . PHP_EOL, null, true, ''
		);
		// add custom script to footer
		if (isset($this->dispenser->hub['view_footer'][$nameSingleCode])
			&& StringHelper::check(
				$this->dispenser->hub['view_footer'][$nameSingleCode]
			))
		{
			$customFooterScript = PHP_EOL . PHP_EOL . $this->placeholder->update_(
					$this->dispenser->hub['view_footer'][$nameSingleCode]
				);
			if (strpos($customFooterScript, '<?php') === false)
			{
				// only add now if no php is added to the footer script
				if (!isset($footerScript))
				{
					$footerScript = '';
				}
				$footerScript .= $customFooterScript;
				unset($customFooterScript);
			}
		}
		// Legacy custom scripts may still call the retired browser transport helper.
		$fileScript = $this->validationScript(
			$fileScript . ($footerScript ?? '') . ($customFooterScript ?? '')
		) . $fileScript;
		// set view listname
		$nameListCode = $viewArray['settings']->name_list_code;
		// add custom script to list view JS file
		if (($list_fileScript = $this->dispenser->get(
				'views_file', $nameSingleCode, PHP_EOL . PHP_EOL, null, true,
				false
			)) !== false
			&& StringHelper::check($list_fileScript))
		{
			// get dates
			$_created  = $this->createdate->get($viewArray);
			$_modified = $this->modifieddate->get($viewArray);
			// add file to view
			$_target = array($this->config->build_target => $nameListCode);
			$_config = array(Placefix::_h('CREATIONDATE') => $_created,
				Placefix::_h('BUILDDATE') => $_modified,
				Placefix::_h('VERSION') => $viewArray['settings']->version);
			$this->structure->build($_target, 'javascript_file', false, $_config);
			// set path
			$_path = '/administrator/components/com_' . $this->config->component_code_name
				. '/assets/js/' . $nameListCode . '.js';
			// load the file to the list view
			$this->contentmulti->set($nameListCode . '|ADMIN_ADD_JAVASCRIPT_FILE', PHP_EOL . PHP_EOL . Indent::_(2) . "//" . Line::_(
					__LINE__,__CLASS__
				) . " Add List View JavaScript File" . PHP_EOL . Indent::_(2)
				. $this->includehelper->get($_path)
			);
		}
		else
		{
			$list_fileScript = '';
			$this->contentmulti->set($nameListCode . '|ADMIN_ADD_JAVASCRIPT_FILE', '');
		}
		// minify the script
		if ($this->config->get('minify', 0) && isset($list_fileScript)
			&& StringHelper::check($list_fileScript))
		{
			// minify the fileScript javascript
			$list_fileScript = Minify::js($list_fileScript);
		}
		// minify the script
		if ($this->config->get('minify', 0) && isset($fileScript)
			&& StringHelper::check($fileScript))
		{
			// minify the fileScript javascript
			$fileScript = Minify::js($fileScript);
		}
		// minify the script
		if ($this->config->get('minify', 0) && isset($footerScript)
			&& StringHelper::check($footerScript))
		{
			// minify the footerScript javascript
			$footerScript = Minify::js($footerScript);
		}
		// make sure there is script to add
		if (isset($list_fileScript)
			&& StringHelper::check(
				$list_fileScript
			))
		{
			// load the script
			$this->viewscript->set(
				$nameListCode . '.list_fileScript', $list_fileScript
			);
		}
		// make sure there is script to add
		if (isset($fileScript)
			&& StringHelper::check(
				$fileScript
			))
		{
			// load the script
			$this->viewscript->set(
				$nameSingleCode . '.fileScript', $fileScript
			);
		}
		// make sure to add custom footer script if php was found in it, since we canot minfy it with php
		if (isset($customFooterScript)
			&& StringHelper::check(
				$customFooterScript
			))
		{
			if (!isset($footerScript))
			{
				$footerScript = '';
			}
			$footerScript .= $customFooterScript;
		}
		// make sure there is script to add
		if (isset($footerScript)
			&& StringHelper::check(
				$footerScript
			))
		{
			// add the needed script tags
			$footerScript = PHP_EOL
				. PHP_EOL . '<script type="text/javascript">' . PHP_EOL
				. $footerScript . PHP_EOL . "</script>";
			$this->viewscript->set(
				$nameSingleCode . '.footerScript', $footerScript
			);
		}
	}

	/**
	 * Reconcile every condition without exceeding the rendered form's required state.
	 *
	 * The public browser function is named update<ViewCode>Conditions. Authored
	 * AJAX handlers can call it after assigning watched values. Original optional
	 * or disabled controls remain optional. One-way actions leave the current pass
	 * unchanged when unmatched; no event history is used.
	 *
	 * @param   string  $function  The view-specific refresh function name.
	 * @param   array   $targets   Target selectors and authored required flags.
	 * @param   string  $calls     All condition calls in their authored order.
	 *
	 * @return  string  The baseline state, refresh function and initial listener.
	 *
	 * @since   6.2.1
	 */
	private function refreshScript(string $function, array $targets, string $calls): string
	{
		$selectors = [];
		$required = [];
		foreach ($targets as $name => $target)
		{
			$selectors[$target['selector']] = $target['selector'];
			if ($target['required'])
			{
				$required[] = $name;
			}
		}

		return '//' . Line::_(__LINE__, __CLASS__) . ' Original display and required state are captured before conditions run.'
			. PHP_EOL . 'var ' . $function . 'Baseline = null;'
			. PHP_EOL
			. PHP_EOL . '/**'
			. PHP_EOL . ' * Recompute this view\'s conditions in definition order from the original baseline.'
			. PHP_EOL . ' * Call after authored code assigns watched values, including AJAX defaults.'
			. PHP_EOL . ' * @return {void}'
			. PHP_EOL . ' * @since 6.2.1'
			. PHP_EOL . ' */'
			. PHP_EOL . 'function ' . $function . '()'
			. PHP_EOL . '{'
			. PHP_EOL . Indent::_(1) . 'if (' . $function . 'Baseline === null)'
			. PHP_EOL . Indent::_(1) . '{'
			. PHP_EOL . Indent::_(2) . $function . 'Baseline = {containers: [], required: []};'
			. PHP_EOL . Indent::_(2) . 'var selectors = ' . json_encode(array_values($selectors)) . ';'
			. PHP_EOL . Indent::_(2) . 'var elements = [];'
			. PHP_EOL . Indent::_(2) . 'for (var selectorIndex = 0; selectorIndex < selectors.length; selectorIndex++)'
			. PHP_EOL . Indent::_(2) . '{'
			. PHP_EOL . Indent::_(3) . 'var containers = jQuery(selectors[selectorIndex]).closest(\'.control-group\').get();'
			. PHP_EOL . Indent::_(3) . 'for (var containerIndex = 0; containerIndex < containers.length; containerIndex++)'
			. PHP_EOL . Indent::_(3) . '{'
			. PHP_EOL . Indent::_(4) . 'var element = containers[containerIndex];'
			. PHP_EOL . Indent::_(4) . 'if (element && element.style && elements.indexOf(element) === -1)'
			. PHP_EOL . Indent::_(4) . '{'
			. PHP_EOL . Indent::_(5) . 'elements.push(element);'
			. PHP_EOL . Indent::_(5) . $function . 'Baseline.containers.push({element: element, display: element.style.display});'
			. PHP_EOL . Indent::_(4) . '}'
			. PHP_EOL . Indent::_(3) . '}'
			. PHP_EOL . Indent::_(2) . '}'
			. PHP_EOL . Indent::_(2) . 'var requiredNames = ' . json_encode($required) . ';'
			. PHP_EOL . Indent::_(2) . 'for (var requiredIndex = 0; requiredIndex < requiredNames.length; requiredIndex++)'
			. PHP_EOL . Indent::_(2) . '{'
			. PHP_EOL . Indent::_(3) . 'var controls = jQuery(\'#jform_\' + requiredNames[requiredIndex]).get();'
			. PHP_EOL . Indent::_(3) . 'for (var controlIndex = 0; controlIndex < controls.length; controlIndex++)'
			. PHP_EOL . Indent::_(3) . '{'
			. PHP_EOL . Indent::_(4) . 'var element = controls[controlIndex];'
			. PHP_EOL . Indent::_(4) . 'var control = jQuery(element);'
			. PHP_EOL . Indent::_(4) . $function . 'Baseline.required.push({'
			. PHP_EOL . Indent::_(5) . 'element: element,'
			. PHP_EOL . Indent::_(5) . 'required: !control.is(\':disabled\')'
			. PHP_EOL . Indent::_(6) . '&& (Boolean(element.required) || element.hasAttribute(\'required\') || control.hasClass(\'required\'))'
			. PHP_EOL . Indent::_(4) . '});'
			. PHP_EOL . Indent::_(3) . '}'
			. PHP_EOL . Indent::_(2) . '}'
			. PHP_EOL . Indent::_(1) . '}'
			. PHP_EOL
			. PHP_EOL . Indent::_(1) . 'for (var index = 0; index < ' . $function . 'Baseline.containers.length; index++)'
			. PHP_EOL . Indent::_(1) . '{'
			. PHP_EOL . Indent::_(2) . 'var baseline = ' . $function . 'Baseline.containers[index];'
			. PHP_EOL . Indent::_(2) . 'baseline.element.style.display = baseline.display;'
			. PHP_EOL . Indent::_(1) . '}'
			. PHP_EOL . Indent::_(1) . 'var required = ' . $function . 'Baseline.required;'
			. PHP_EOL . Indent::_(1) . 'for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)'
			. PHP_EOL . Indent::_(1) . '{'
			. PHP_EOL . Indent::_(2) . 'if (required[requiredIndex].required)'
			. PHP_EOL . Indent::_(2) . '{'
			. PHP_EOL . Indent::_(3) . 'jQuery(required[requiredIndex].element).prop(\'required\', true)'
			. PHP_EOL . Indent::_(4) . '.attr(\'aria-required\', true).addClass(\'required\');'
			. PHP_EOL . Indent::_(2) . '}'
			. PHP_EOL . Indent::_(1) . '}'
			. PHP_EOL . $calls
			. PHP_EOL . Indent::_(1) . 'for (var requiredIndex = 0; requiredIndex < required.length; requiredIndex++)'
			. PHP_EOL . Indent::_(1) . '{'
			. PHP_EOL . Indent::_(2) . 'if (!required[requiredIndex].required)'
			. PHP_EOL . Indent::_(2) . '{'
			. PHP_EOL . Indent::_(3) . 'jQuery(required[requiredIndex].element).removeAttr(\'required\')'
			. PHP_EOL . Indent::_(4) . '.removeAttr(\'aria-required\').removeClass(\'required\');'
			. PHP_EOL . Indent::_(2) . '}'
			. PHP_EOL . Indent::_(1) . '}'
			. PHP_EOL . '}'
			. PHP_EOL
			. PHP_EOL . 'document.addEventListener(\'DOMContentLoaded\', ' . $function . ');' . PHP_EOL;
	}

	/**
	 * Preserve the browser's normalized match definition for model validation.
	 *
	 * @param   array  $condition  The normalized view condition.
	 * @param   array  $options    Options read by the shared options renderer.
	 *
	 * @return  array
	 * @since   6.2.0
	 */
	protected function validationCondition(array $condition, array $options): array
	{
		if (in_array((int) $condition['match_behavior'], [8, 9], true))
		{
			$options['keywords'] = array_map(
				static fn ($keyword) => StringHelper::safe($keyword, 'w'),
				$options['keywords'] ?? []
			);
		}

		return [
			'name' => $condition['match_name'],
			'behavior' => (int) $condition['match_behavior'],
			'options' => $options,
			'user' => $this->scriptuserswitch->inArray($condition['match_type']),
			'checkbox' => $condition['match_type'] === 'checkbox',
		];
	}

	/**
	 * Read back one of the scripts an admin view was given.
	 *
	 * @param   string  $view  The view code name.
	 * @param   string  $type  Which script: fileScript, footerScript or list_fileScript.
	 *
	 * @return  string  The script, or an empty string when the view has none of that kind.
	 *
	 * @since   6.1.7
	 */
	public function script(string $view, string $type): string
	{
		// the builder returns null for a view that was never given this script
		return (string) $this->viewscript->get($view . '.' . $type, '');
	}

	/**
	 * Build the statements that read every watched value and call one function.
	 *
	 * Public because the legacy helper's method of the same shape is public
	 * surface, and its shim delegates here.
	 *
	 * @param   string  $function   The name of the function to call.
	 * @param   array   $matchKeys  The keys of the values the function takes.
	 * @param   array   $getValue   The read statement of every key.
	 *
	 * @return  array{code: string, array: bool}  The call, and whether any value is an array.
	 *
	 * @since   6.1.7
	 */
	public function functionCall(string $function, array $matchKeys,
		array $getValue): array
	{
		$initial  = '';
		$funcsets = [];
		$array    = false;
		foreach ($matchKeys as $matchKey)
		{
			$value = $getValue[$matchKey];
			if ($value['isArray'])
			{
				$initial    .= PHP_EOL . Indent::_(1) . $value['get'];
				$funcsets[] = $matchKey;
				$array      = true;
			}
			else
			{
				$initial    .= PHP_EOL . Indent::_(1) . $value['get'];
				$funcsets[] = $matchKey;
			}
		}

		// make sure that the function is loaded only once
		if (ArrayHelper::check($funcsets))
		{
			$initial .= PHP_EOL . Indent::_(1) . $function . "(";
			$initial .= implode(',', $funcsets);
			$initial .= ");" . PHP_EOL;
		}

		return array('code' => $initial, 'array' => $array);
	}

	/**
	 * Keep legacy custom calls callable without accepting browser validation policy.
	 *
	 * Required fields are decided from compiler conditions on the server. The
	 * compatibility function deliberately performs no form or data mutation.
	 * It is included only when an authored script references the retired helper.
	 *
	 * @param   string  $script  The assembled edit-form scripts.
	 *
	 * @return  string  The compatibility function, or an empty string.
	 *
	 * @since   6.1.7
	 * @since   6.2.1  Removed the browser-owned required-field transport.
	 */
	protected function validationScript(string $script): string
	{
		if (strpos($script, 'updateFieldRequired') === false)
		{
			return '';
		}

		return PHP_EOL . "/**"
			. PHP_EOL . " * Compatibility for custom scripts using the retired required-field helper."
			. PHP_EOL . " *"
			. PHP_EOL . " * @param  {string}  name    The legacy field name, ignored."
			. PHP_EOL . " * @param  {number}  status  The legacy required state, ignored."
			. PHP_EOL . " *"
			. PHP_EOL . " * @return {void}"
			. PHP_EOL . " * @deprecated 6.2.1 Required fields are decided by server-side conditions."
			. PHP_EOL . " */"
			. PHP_EOL . "function updateFieldRequired(name, status)"
			. PHP_EOL . "{"
			. PHP_EOL . "}" . PHP_EOL;
	}

	/**
	 * Build the helper every generated condition test calls.
	 *
	 * @return  string  The isSet function.
	 *
	 * @since   6.1.7
	 */
	protected function isSetFunction(): string
	{
		$isSet = '';

	// set the isSet function
	$isSet = PHP_EOL . "// the isSet function";
	$isSet .= PHP_EOL . "function isSet(val)";
	$isSet .= PHP_EOL . "{";
	$isSet .= PHP_EOL . Indent::_(1)
		. "if ((val != undefined) && (val != null) && 0 !== val.length){";
	$isSet .= PHP_EOL . Indent::_(2) . "return true;";
	$isSet .= PHP_EOL . Indent::_(1) . "}";
	$isSet .= PHP_EOL . Indent::_(1) . "return false;";
	$isSet .= PHP_EOL . "}";

		return $isSet;
	}
}

