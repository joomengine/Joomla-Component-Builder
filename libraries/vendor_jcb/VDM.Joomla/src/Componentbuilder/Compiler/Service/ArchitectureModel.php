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

namespace VDM\Joomla\Componentbuilder\Compiler\Service;


use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\CustomQuery;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\FieldRelation;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\FilterQuery;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\AliasTitleFix;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\Model\AliasTitleFix as J6ModelAliasTitleFix;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Model\ItemSaveInterface as ModelItemSave;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\ItemSave as SharedModelItemSave;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\Model\ItemSave as J6ModelItemSave;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Model\ItemSave as J3ModelItemSave;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\RecordKeyFix as ModelRecordKeyFix;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Model\BatchCopyInterface as ModelBatchCopy;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\BatchCopy as SharedModelBatchCopy;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Model\BatchCopy as J3ModelBatchCopy;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Model\BatchMoveInterface as ModelBatchMove;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\BatchMove as SharedModelBatchMove;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Model\BatchMove as J3ModelBatchMove;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Model\GetFormInterface as ModelGetForm;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\GetForm as SharedModelGetForm;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Model\GetForm as J3ModelGetForm;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Model\ItemsMethodInterface as ModelItemsMethod;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\ItemsMethod as SharedModelItemsMethod;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Model\ItemsMethod as J3ModelItemsMethod;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\Model\ItemsMethod as J6ModelItemsMethod;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Model\ListQueryInterface as ModelListQuery;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\ListQuery as SharedModelListQuery;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Model\ListQuery as J3ModelListQuery;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\Model\ListQuery as J6ModelListQuery;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\SearchQuery;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Model\ItemsStringFixInterface as ModelItemsStringFix;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\ItemsStringFix as SharedModelItemsStringFix;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Model\ItemsStringFix as J3ModelItemsStringFix;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\SelectionTranslation;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\SelectionTranslationMethod;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Model\AllowEditInterface;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\AllowEdit as SharedModelAllowEdit;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Model\AllowEdit as J3ModelAllowEdit;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Model\CanDeleteInterface;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\Model\CanDelete as J6ModelCanDelete;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFive\Model\CanDelete as J5ModelCanDelete;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFour\Model\CanDelete as J4ModelCanDelete;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Model\CanDelete as J3ModelCanDelete;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Model\CanEditStateInterface;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\Model\CanEditState as J6ModelCanEditState;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFive\Model\CanEditState as J5ModelCanEditState;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFour\Model\CanEditState as J4ModelCanEditState;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Model\CanEditState as J3ModelCanEditState;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Architecture\Model\CheckInNowInterface;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaSix\Model\CheckInNow as J6CheckInNow;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFive\Model\CheckInNow as J5CheckInNow;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaFour\Model\CheckInNow as J4CheckInNow;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\JoomlaThree\Model\CheckInNow as J3CheckInNow;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\StoredId as ModelStoredId;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\PopulateState as ModelPopulateState;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\SortFields as ModelSortFields;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\FilterFields as ModelFilterFields;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\GetItemMethod as ModelGetItemMethod;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\GenerateNewTitle as ModelGenerateNewTitle;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\GenerateNewAlias as ModelGenerateNewAlias;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\ValidationFix as ModelValidationFix;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\ConditionalRule as ModelConditionalRule;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\UniqueFields as ModelUniqueFields;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\CheckboxSave as ModelCheckboxSave;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Table\Constructor as TableConstructor;
use VDM\Joomla\Componentbuilder\Compiler\Architecture\Model\AjaxMethods as ModelAjaxMethods;


/**
 * Architecture Model Service Provider
 * 
 * @since 3.2.0
 */
class ArchitectureModel implements ServiceProviderInterface
{
	/**
	 * Current Joomla Version Being Build
	 *
	 * @var     int
	 * @since 3.2.0
	 **/
	protected $targetVersion;

	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 * @since 3.2.0
	 */
	public function register(Container $container)
	{
		$container->alias(SelectionTranslationMethod::class, 'Architecture.Model.SelectionTranslationMethod')
			->share('Architecture.Model.SelectionTranslationMethod', [$this, 'getModelSelectionTranslationMethod'], true);

		$container->alias(SelectionTranslation::class, 'Architecture.Model.SelectionTranslation')
			->share('Architecture.Model.SelectionTranslation', [$this, 'getModelSelectionTranslation'], true);

		$container->alias(ModelItemsStringFix::class, 'Architecture.Model.ItemsStringFix')
			->share('Architecture.Model.ItemsStringFix', [$this, 'getModelItemsStringFix'], true);

		$container->alias(SharedModelItemsStringFix::class, 'Architecture.Model.Shared.ItemsStringFix')
			->share('Architecture.Model.Shared.ItemsStringFix', [$this, 'getSharedModelItemsStringFix'], true);

		$container->alias(J3ModelItemsStringFix::class, 'Architecture.Model.J3.ItemsStringFix')
			->share('Architecture.Model.J3.ItemsStringFix', [$this, 'getJ3ModelItemsStringFix'], true);

		$container->alias(FieldRelation::class, 'Architecture.Model.FieldRelation')
			->share('Architecture.Model.FieldRelation', [$this, 'getModelFieldRelation'], true);

		$container->alias(CustomQuery::class, 'Architecture.Model.CustomQuery')
			->share('Architecture.Model.CustomQuery', [$this, 'getModelCustomQuery'], true);

		$container->alias(SearchQuery::class, 'Architecture.Model.SearchQuery')
			->share('Architecture.Model.SearchQuery', [$this, 'getModelSearchQuery'], true);

		$container->alias(FilterQuery::class, 'Architecture.Model.FilterQuery')
			->share('Architecture.Model.FilterQuery', [$this, 'getModelFilterQuery'], true);

		$container->alias(ModelListQuery::class, 'Architecture.Model.ListQuery')
			->share('Architecture.Model.ListQuery', [$this, 'getModelListQuery'], true);

		$container->alias(SharedModelListQuery::class, 'Architecture.Model.Shared.ListQuery')
			->share('Architecture.Model.Shared.ListQuery', [$this, 'getSharedModelListQuery'], true);

		$container->alias(J3ModelListQuery::class, 'Architecture.Model.J3.ListQuery')
			->share('Architecture.Model.J3.ListQuery', [$this, 'getJ3ModelListQuery'], true);

		$container->alias(J6ModelListQuery::class, 'Architecture.Model.J6.ListQuery')
			->share('Architecture.Model.J6.ListQuery', [$this, 'getJ6ModelListQuery'], true);

		$container->alias(ModelItemSave::class, 'Architecture.Model.ItemSave')
			->share('Architecture.Model.ItemSave', [$this, 'getModelItemSave'], true);

		$container->alias(SharedModelItemSave::class, 'Architecture.Model.Shared.ItemSave')
			->share('Architecture.Model.Shared.ItemSave', [$this, 'getSharedModelItemSave'], true);

		$container->alias(J6ModelItemSave::class, 'Architecture.Model.J6.ItemSave')
			->share('Architecture.Model.J6.ItemSave', [$this, 'getJ6ModelItemSave'], true);

		$container->alias(J3ModelItemSave::class, 'Architecture.Model.J3.ItemSave')
			->share('Architecture.Model.J3.ItemSave', [$this, 'getJ3ModelItemSave'], true);

		$container->alias(ModelRecordKeyFix::class, 'Architecture.Model.RecordKeyFix')
			->share('Architecture.Model.RecordKeyFix', [$this, 'getModelRecordKeyFix'], true);

		$container->alias(AliasTitleFix::class, 'Architecture.Model.AliasTitleFix')
			->share('Architecture.Model.AliasTitleFix', [$this, 'getModelAliasTitleFix'], true);

		$container->share('Architecture.Model.Shared.AliasTitleFix', [$this, 'getSharedModelAliasTitleFix'], true);

		$container->alias(J6ModelAliasTitleFix::class, 'Architecture.Model.J6.AliasTitleFix')
			->share('Architecture.Model.J6.AliasTitleFix', [$this, 'getJ6ModelAliasTitleFix'], true);

		$container->alias(ModelBatchCopy::class, 'Architecture.Model.BatchCopy')
			->share('Architecture.Model.BatchCopy', [$this, 'getModelBatchCopy'], true);

		$container->alias(SharedModelBatchCopy::class, 'Architecture.Model.Shared.BatchCopy')
			->share('Architecture.Model.Shared.BatchCopy', [$this, 'getSharedModelBatchCopy'], true);

		$container->alias(J3ModelBatchCopy::class, 'Architecture.Model.J3.BatchCopy')
			->share('Architecture.Model.J3.BatchCopy', [$this, 'getJ3ModelBatchCopy'], true);

		$container->alias(ModelBatchMove::class, 'Architecture.Model.BatchMove')
			->share('Architecture.Model.BatchMove', [$this, 'getModelBatchMove'], true);

		$container->alias(SharedModelBatchMove::class, 'Architecture.Model.Shared.BatchMove')
			->share('Architecture.Model.Shared.BatchMove', [$this, 'getSharedModelBatchMove'], true);

		$container->alias(J3ModelBatchMove::class, 'Architecture.Model.J3.BatchMove')
			->share('Architecture.Model.J3.BatchMove', [$this, 'getJ3ModelBatchMove'], true);

		$container->alias(ModelGetForm::class, 'Architecture.Model.GetForm')
			->share('Architecture.Model.GetForm', [$this, 'getModelGetForm'], true);

		$container->alias(SharedModelGetForm::class, 'Architecture.Model.Shared.GetForm')
			->share('Architecture.Model.Shared.GetForm', [$this, 'getSharedModelGetForm'], true);

		$container->alias(J3ModelGetForm::class, 'Architecture.Model.J3.GetForm')
			->share('Architecture.Model.J3.GetForm', [$this, 'getJ3ModelGetForm'], true);

		$container->alias(ModelItemsMethod::class, 'Architecture.Model.ItemsMethod')
			->share('Architecture.Model.ItemsMethod', [$this, 'getModelItemsMethod'], true);

		$container->alias(SharedModelItemsMethod::class, 'Architecture.Model.Shared.ItemsMethod')
			->share('Architecture.Model.Shared.ItemsMethod', [$this, 'getSharedModelItemsMethod'], true);

		$container->alias(J3ModelItemsMethod::class, 'Architecture.Model.J3.ItemsMethod')
			->share('Architecture.Model.J3.ItemsMethod', [$this, 'getJ3ModelItemsMethod'], true);

		$container->alias(J6ModelItemsMethod::class, 'Architecture.Model.J6.ItemsMethod')
			->share('Architecture.Model.J6.ItemsMethod', [$this, 'getJ6ModelItemsMethod'], true);

		$container->alias(J3ModelAllowEdit::class, 'Architecture.Model.J3.AllowEdit')
			->share('Architecture.Model.J3.AllowEdit', [$this, 'getJ3ModelAllowEdit'], true);

		$container->alias(SharedModelAllowEdit::class, 'Architecture.Model.Shared.AllowEdit')
			->share('Architecture.Model.Shared.AllowEdit', [$this, 'getSharedModelAllowEdit'], true);

		$container->alias(AllowEditInterface::class, 'Architecture.Model.AllowEdit')
			->share('Architecture.Model.AllowEdit', [$this, 'getModelAllowEdit'], true);

		$container->alias(J3ModelCanDelete::class, 'Architecture.Model.J3.CanDelete')
			->share('Architecture.Model.J3.CanDelete', [$this, 'getJ3ModelCanDelete'], true);

		$container->alias(J4ModelCanDelete::class, 'Architecture.Model.J4.CanDelete')
			->share('Architecture.Model.J4.CanDelete', [$this, 'getJ4ModelCanDelete'], true);

		$container->alias(J5ModelCanDelete::class, 'Architecture.Model.J5.CanDelete')
			->share('Architecture.Model.J5.CanDelete', [$this, 'getJ5ModelCanDelete'], true);

		$container->alias(J6ModelCanDelete::class, 'Architecture.Model.J6.CanDelete')
			->share('Architecture.Model.J6.CanDelete', [$this, 'getJ6ModelCanDelete'], true);

		$container->alias(CanDeleteInterface::class, 'Architecture.Model.CanDelete')
			->share('Architecture.Model.CanDelete', [$this, 'getModelCanDelete'], true);

		$container->alias(J3ModelCanEditState::class, 'Architecture.Model.J3.CanEditState')
			->share('Architecture.Model.J3.CanEditState', [$this, 'getJ3ModelCanEditState'], true);

		$container->alias(J4ModelCanEditState::class, 'Architecture.Model.J4.CanEditState')
			->share('Architecture.Model.J4.CanEditState', [$this, 'getJ4ModelCanEditState'], true);

		$container->alias(J5ModelCanEditState::class, 'Architecture.Model.J5.CanEditState')
			->share('Architecture.Model.J5.CanEditState', [$this, 'getJ5ModelCanEditState'], true);

		$container->alias(J6ModelCanEditState::class, 'Architecture.Model.J6.CanEditState')
			->share('Architecture.Model.J6.CanEditState', [$this, 'getJ6ModelCanEditState'], true);

		$container->alias(CanEditStateInterface::class, 'Architecture.Model.CanEditState')
			->share('Architecture.Model.CanEditState', [$this, 'getModelCanEditState'], true);

		$container->alias(CheckInNowInterface::class, 'Architecture.Model.CheckInNow')
			->share('Architecture.Model.CheckInNow', [$this, 'getCheckInNow'], true);

		$container->alias(J6CheckInNow::class, 'Architecture.Model.J6.CheckInNow')
			->share('Architecture.Model.J6.CheckInNow', [$this, 'getJ6CheckInNow'], true);

		$container->alias(J5CheckInNow::class, 'Architecture.Model.J5.CheckInNow')
			->share('Architecture.Model.J5.CheckInNow', [$this, 'getJ5CheckInNow'], true);

		$container->alias(J4CheckInNow::class, 'Architecture.Model.J4.CheckInNow')
			->share('Architecture.Model.J4.CheckInNow', [$this, 'getJ4CheckInNow'], true);

		$container->alias(J3CheckInNow::class, 'Architecture.Model.J3.CheckInNow')
			->share('Architecture.Model.J3.CheckInNow', [$this, 'getJ3CheckInNow'], true);

		$container->alias(ModelCheckboxSave::class, 'Architecture.Model.CheckboxSave')
			->share('Architecture.Model.CheckboxSave', [$this, 'getModelCheckboxSave'], true);

		$container->alias(TableConstructor::class, 'Architecture.Table.Constructor')
			->share('Architecture.Table.Constructor', [$this, 'getTableConstructor'], true);

		$container->alias(ModelAjaxMethods::class, 'Architecture.Model.AjaxMethods')
			->share('Architecture.Model.AjaxMethods', [$this, 'getModelAjaxMethods'], true);

		$container->alias(ModelStoredId::class, 'Architecture.Model.StoredId')
			->share('Architecture.Model.StoredId', [$this, 'getModelStoredId'], true);

		$container->alias(ModelPopulateState::class, 'Architecture.Model.PopulateState')
			->share('Architecture.Model.PopulateState', [$this, 'getModelPopulateState'], true);

		$container->alias(ModelSortFields::class, 'Architecture.Model.SortFields')
			->share('Architecture.Model.SortFields', [$this, 'getModelSortFields'], true);

		$container->alias(ModelFilterFields::class, 'Architecture.Model.FilterFields')
			->share('Architecture.Model.FilterFields', [$this, 'getModelFilterFields'], true);

		$container->alias(ModelGetItemMethod::class, 'Architecture.Model.GetItemMethod')
			->share('Architecture.Model.GetItemMethod', [$this, 'getModelGetItemMethod'], true);

		$container->alias(ModelGenerateNewTitle::class, 'Architecture.Model.GenerateNewTitle')
			->share('Architecture.Model.GenerateNewTitle', [$this, 'getModelGenerateNewTitle'], true);

		$container->alias(ModelGenerateNewAlias::class, 'Architecture.Model.GenerateNewAlias')
			->share('Architecture.Model.GenerateNewAlias', [$this, 'getModelGenerateNewAlias'], true);

		$container->alias(ModelConditionalRule::class, 'Architecture.Model.ConditionalRule')
			->share('Architecture.Model.ConditionalRule', [$this, 'getModelConditionalRule'], true);

		$container->alias(ModelValidationFix::class, 'Architecture.Model.ValidationFix')
			->share('Architecture.Model.ValidationFix', [$this, 'getModelValidationFix'], true);

		$container->alias(ModelUniqueFields::class, 'Architecture.Model.UniqueFields')
			->share('Architecture.Model.UniqueFields', [$this, 'getModelUniqueFields'], true);
	}

	/**
	 * Get The Model SelectionTranslationMethod Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SelectionTranslationMethod
	 * @since   6.1.7
	 */
	public function getModelSelectionTranslationMethod(Container $container): SelectionTranslationMethod
	{
		return new SelectionTranslationMethod(
			$container->get('Compiler.Builder.Selection.Translation')
		);
	}

	/**
	 * Get The Model SelectionTranslation Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SelectionTranslation
	 * @since   6.1.7
	 */
	public function getModelSelectionTranslation(Container $container): SelectionTranslation
	{
		return new SelectionTranslation(
			$container->get('Compiler.Builder.Selection.Translation')
		);
	}

	/**
	 * Get The Model ItemsStringFix Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ModelItemsStringFix
	 * @since   6.1.7
	 */
	public function getModelItemsStringFix(Container $container): ModelItemsStringFix
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 models have no getCurrentUser()
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.Model.J3.ItemsStringFix');
		}

		return $container->get('Architecture.Model.Shared.ItemsStringFix');
	}

	/**
	 * Get The Model ItemsStringFix Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedModelItemsStringFix
	 * @since   6.1.7
	 */
	public function getSharedModelItemsStringFix(Container $container): SharedModelItemsStringFix
	{
		return new SharedModelItemsStringFix(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Architecture.Model.FieldRelation'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Field.Relations'),
			$container->get('Compiler.Builder.Model.Expert.Field'),
			$container->get('Compiler.Builder.Permission.Fields'),
			$container->get('Compiler.Builder.Selection.Translation'),
			$container->get('Compiler.Builder.Tags'),
			$container->get('Compiler.Builder.Items.Method.Eximport.String'),
			$container->get('Compiler.Builder.Items.Method.List.String'),
			$container->get('Compiler.Builder.Model.Expert.Field.Initiator')
		);
	}

	/**
	 * Get The Model ItemsStringFix Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ModelItemsStringFix
	 * @since   6.1.7
	 */
	public function getJ3ModelItemsStringFix(Container $container): J3ModelItemsStringFix
	{
		return new J3ModelItemsStringFix(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Architecture.Model.FieldRelation'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Field.Relations'),
			$container->get('Compiler.Builder.Model.Expert.Field'),
			$container->get('Compiler.Builder.Permission.Fields'),
			$container->get('Compiler.Builder.Selection.Translation'),
			$container->get('Compiler.Builder.Tags'),
			$container->get('Compiler.Builder.Items.Method.Eximport.String'),
			$container->get('Compiler.Builder.Items.Method.List.String'),
			$container->get('Compiler.Builder.Model.Expert.Field.Initiator')
		);
	}

	/**
	 * Get The Model FieldRelation Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FieldRelation
	 * @since   6.1.7
	 */
	public function getModelFieldRelation(Container $container): FieldRelation
	{
		return new FieldRelation(
			$container->get('Compiler.Builder.List.Join'),
			$container->get('Placeholder')
		);
	}

	/**
	 * Get The Model CustomQuery Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CustomQuery
	 * @since   6.1.7
	 */
	public function getModelCustomQuery(Container $container): CustomQuery
	{
		return new CustomQuery(
			$container->get('Compiler.Builder.Custom.Field'),
			$container->get('Compiler.Builder.Custom.List'),
			$container->get('Compiler.Creator.Custom.Field.Type.File')
		);
	}

	/**
	 * Get The SearchQuery Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SearchQuery
	 * @since   6.1.7
	 */
	public function getModelSearchQuery(Container $container): SearchQuery
	{
		return new SearchQuery(
			$container->get('Compiler.Builder.Search'),
			$container->get('Compiler.Builder.Custom.Field')
		);
	}

	/**
	 * Get The FilterQuery Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FilterQuery
	 * @since   6.1.7
	 */
	public function getModelFilterQuery(Container $container): FilterQuery
	{
		return new FilterQuery(
			$container->get('Compiler.Builder.Filter'),
			$container->get('Compiler.Builder.Admin.Filter.Type'),
			$container->get('Compiler.Builder.Content.One')
		);
	}

	/**
	 * Get The ListQuery Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ModelListQuery
	 * @since   6.1.7
	 */
	public function getModelListQuery(Container $container): ModelListQuery
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 takes its user and database from the global factory
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.Model.J3.ListQuery');
		}

		if ((int) $this->targetVersion === 6)
		{
			return $container->get('Architecture.Model.J6.ListQuery');
		}

		return $container->get('Architecture.Model.Shared.ListQuery');
	}

	/**
	 * Get The ListQuery Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedModelListQuery
	 * @since   6.1.7
	 */
	public function getSharedModelListQuery(Container $container): SharedModelListQuery
	{
		return new SharedModelListQuery(
			$container->get('Config'),
			$container->get('Customcode.Dispenser'),
			$container->get('Field.Database.Name'),
			$container->get('Architecture.Model.CustomQuery'),
			$container->get('Architecture.Model.SearchQuery'),
			$container->get('Architecture.Model.FilterQuery'),
			$container->get('Compiler.Builder.Access.Switch'),
			$container->get('Compiler.Builder.Category'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Field.Names'),
			$container->get('Compiler.Builder.Views.Default.Ordering')
		);
	}

	/**
	 * Get The ListQuery Class for Joomla 3.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ModelListQuery
	 * @since   6.1.7
	 */
	public function getJ3ModelListQuery(Container $container): J3ModelListQuery
	{
		return new J3ModelListQuery(
			$container->get('Config'),
			$container->get('Customcode.Dispenser'),
			$container->get('Field.Database.Name'),
			$container->get('Architecture.Model.CustomQuery'),
			$container->get('Architecture.Model.SearchQuery'),
			$container->get('Architecture.Model.FilterQuery'),
			$container->get('Compiler.Builder.Access.Switch'),
			$container->get('Compiler.Builder.Category'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Field.Names'),
			$container->get('Compiler.Builder.Views.Default.Ordering')
		);
	}


	/**
	 * Get The ListQuery Class for Joomla 6.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6ModelListQuery
	 * @since   6.2.0
	 */
	public function getJ6ModelListQuery(Container $container): J6ModelListQuery
	{
		return new J6ModelListQuery(
			$container->get('Config'),
			$container->get('Customcode.Dispenser'),
			$container->get('Field.Database.Name'),
			$container->get('Architecture.Model.CustomQuery'),
			$container->get('Architecture.Model.SearchQuery'),
			$container->get('Architecture.Model.FilterQuery'),
			$container->get('Compiler.Builder.Access.Switch'),
			$container->get('Compiler.Builder.Category'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Field.Names'),
			$container->get('Compiler.Builder.Views.Default.Ordering')
		);
	}

	/**
	 * Get The ItemsMethod Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ModelItemsMethod
	 * @since   6.1.7
	 */
	public function getModelItemsMethod(Container $container): ModelItemsMethod
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 takes its user and database from the global factory
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.Model.J3.ItemsMethod');
		}

		if ((int) $this->targetVersion === 6)
		{
			return $container->get('Architecture.Model.J6.ItemsMethod');
		}

		return $container->get('Architecture.Model.Shared.ItemsMethod');
	}

	/**
	 * Get The ItemsMethod Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedModelItemsMethod
	 * @since   6.1.7
	 */
	public function getSharedModelItemsMethod(Container $container): SharedModelItemsMethod
	{
		return new SharedModelItemsMethod(
			$container->get('Config'),
			$container->get('Customcode.Dispenser'),
			$container->get('Placeholder'),
			$container->get('Field.Database.Name'),
			$container->get('Architecture.Model.CustomQuery'),
			$container->get('Architecture.Model.ItemsStringFix'),
			$container->get('Architecture.Model.SelectionTranslation'),
			$container->get('Compiler.Builder.Access.Switch'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Views.Default.Ordering'),
			$container->get('Compiler.Builder.Eximport.View')
		);
	}

	/**
	 * Get The ItemsMethod Class for Joomla 3.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ModelItemsMethod
	 * @since   6.1.7
	 */
	public function getJ3ModelItemsMethod(Container $container): J3ModelItemsMethod
	{
		return new J3ModelItemsMethod(
			$container->get('Config'),
			$container->get('Customcode.Dispenser'),
			$container->get('Placeholder'),
			$container->get('Field.Database.Name'),
			$container->get('Architecture.Model.CustomQuery'),
			$container->get('Architecture.Model.ItemsStringFix'),
			$container->get('Architecture.Model.SelectionTranslation'),
			$container->get('Compiler.Builder.Access.Switch'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Views.Default.Ordering'),
			$container->get('Compiler.Builder.Eximport.View')
		);
	}


	/**
	 * Get The ItemsMethod Class for Joomla 6.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6ModelItemsMethod
	 * @since   6.2.0
	 */
	public function getJ6ModelItemsMethod(Container $container): J6ModelItemsMethod
	{
		return new J6ModelItemsMethod(
			$container->get('Config'),
			$container->get('Customcode.Dispenser'),
			$container->get('Placeholder'),
			$container->get('Field.Database.Name'),
			$container->get('Architecture.Model.CustomQuery'),
			$container->get('Architecture.Model.ItemsStringFix'),
			$container->get('Architecture.Model.SelectionTranslation'),
			$container->get('Compiler.Builder.Access.Switch'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Views.Default.Ordering'),
			$container->get('Compiler.Builder.Eximport.View')
		);
	}

	/**
	 * Get The ItemSave Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ModelItemSave
	 * @since   6.1.7
	 */
	public function getModelItemSave(Container $container): ModelItemSave
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 reaches the current user through the global factory
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.Model.J3.ItemSave');
		}

		if ((int) $this->targetVersion === 6)
		{
			return $container->get('Architecture.Model.J6.ItemSave');
		}

		return $container->get('Architecture.Model.Shared.ItemSave');
	}

	/**
	 * Get The ItemSave Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedModelItemSave
	 * @since   6.1.7
	 */
	public function getSharedModelItemSave(Container $container): SharedModelItemSave
	{
		return new SharedModelItemSave(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Base.Six.Four'),
			$container->get('Compiler.Builder.Json.Item'),
			$container->get('Compiler.Builder.Json.String'),
			$container->get('Compiler.Builder.Permission.Fields'),
			$container->get('Compiler.Builder.Model.Basic.Field'),
			$container->get('Compiler.Builder.Model.Medium.Field'),
			$container->get('Compiler.Builder.Model.Whmcs.Field'),
			$container->get('Compiler.Builder.Model.Expert.Field'),
			$container->get('Compiler.Builder.Model.Expert.Field.Initiator'),
			$container->get('Architecture.Model.RecordKeyFix')
		);
	}

	/**
	 * Build the Joomla 6 Model ItemSave renderer.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6ModelItemSave
	 * @since   6.2.0
	 */
	public function getJ6ModelItemSave(Container $container): J6ModelItemSave
	{
		return new J6ModelItemSave(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Base.Six.Four'),
			$container->get('Compiler.Builder.Json.Item'),
			$container->get('Compiler.Builder.Json.String'),
			$container->get('Compiler.Builder.Permission.Fields'),
			$container->get('Compiler.Builder.Model.Basic.Field'),
			$container->get('Compiler.Builder.Model.Medium.Field'),
			$container->get('Compiler.Builder.Model.Whmcs.Field'),
			$container->get('Compiler.Builder.Model.Expert.Field'),
			$container->get('Compiler.Builder.Model.Expert.Field.Initiator'),
			$container->get('Architecture.Model.RecordKeyFix')
		);
	}

	/**
	 * Get The ItemSave Class for Joomla 3.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ModelItemSave
	 * @since   6.1.7
	 */
	public function getJ3ModelItemSave(Container $container): J3ModelItemSave
	{
		return new J3ModelItemSave(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Base.Six.Four'),
			$container->get('Compiler.Builder.Json.Item'),
			$container->get('Compiler.Builder.Json.String'),
			$container->get('Compiler.Builder.Permission.Fields'),
			$container->get('Compiler.Builder.Model.Basic.Field'),
			$container->get('Compiler.Builder.Model.Medium.Field'),
			$container->get('Compiler.Builder.Model.Whmcs.Field'),
			$container->get('Compiler.Builder.Model.Expert.Field'),
			$container->get('Compiler.Builder.Model.Expert.Field.Initiator'),
			$container->get('Architecture.Model.RecordKeyFix')
		);
	}

	/**
	 * Get The RecordKeyFix Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ModelRecordKeyFix
	 * @since   6.1.7
	 */
	public function getModelRecordKeyFix(Container $container): ModelRecordKeyFix
	{
		return new ModelRecordKeyFix(
			$container->get('Config'),
			$container->get('Compiler.Builder.Database.Unique.Guid'),
			$container->get('Compiler.Builder.Database.Unique.Keys'),
			$container->get('Compiler.Builder.Alias')
		);
	}

	/**
	 * Get The AliasTitleFix Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AliasTitleFix
	 * @since   6.1.7
	 */
	public function getModelAliasTitleFix(Container $container): AliasTitleFix
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		if ((int) $this->targetVersion === 6)
		{
			return $container->get('Architecture.Model.J6.AliasTitleFix');
		}

		return $container->get('Architecture.Model.Shared.AliasTitleFix');
	}

	/**
	 * Get the title and alias fix shared by Joomla 3, 4, and 5.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AliasTitleFix
	 * @since   6.2.0
	 */
	public function getSharedModelAliasTitleFix(Container $container): AliasTitleFix
	{
		return new AliasTitleFix(
			$container->get('Compiler.Builder.Alias'),
			$container->get('Compiler.Builder.Title'),
			$container->get('Compiler.Builder.Custom.Alias'),
			$container->get('Compiler.Builder.Category.Code'),
			$container->get('Compiler.Builder.Content.One')
		);
	}

	/**
	 * Get the title and alias fix for Joomla 6.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6ModelAliasTitleFix
	 * @since   6.2.0
	 */
	public function getJ6ModelAliasTitleFix(Container $container): J6ModelAliasTitleFix
	{
		return new J6ModelAliasTitleFix(
			$container->get('Compiler.Builder.Alias'),
			$container->get('Compiler.Builder.Title'),
			$container->get('Compiler.Builder.Custom.Alias'),
			$container->get('Compiler.Builder.Category.Code'),
			$container->get('Compiler.Builder.Content.One')
		);
	}

	/**
	 * Get The BatchCopy Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ModelBatchCopy
	 * @since   6.1.7
	 */
	public function getModelBatchCopy(Container $container): ModelBatchCopy
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 takes the current user from the global factory
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.Model.J3.BatchCopy');
		}

		return $container->get('Architecture.Model.Shared.BatchCopy');
	}

	/**
	 * Get The BatchCopy Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedModelBatchCopy
	 * @since   6.1.7
	 */
	public function getSharedModelBatchCopy(Container $container): SharedModelBatchCopy
	{
		return new SharedModelBatchCopy(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Alias'),
			$container->get('Compiler.Builder.Category.Code'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Custom.Alias'),
			$container->get('Compiler.Builder.Title')
		);
	}

	/**
	 * Get The BatchCopy Class for Joomla 3.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ModelBatchCopy
	 * @since   6.1.7
	 */
	public function getJ3ModelBatchCopy(Container $container): J3ModelBatchCopy
	{
		return new J3ModelBatchCopy(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Alias'),
			$container->get('Compiler.Builder.Category.Code'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Custom.Alias'),
			$container->get('Compiler.Builder.Title')
		);
	}

	/**
	 * Get The BatchMove Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ModelBatchMove
	 * @since   6.1.7
	 */
	public function getModelBatchMove(Container $container): ModelBatchMove
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 takes the current user from the global factory
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.Model.J3.BatchMove');
		}

		return $container->get('Architecture.Model.Shared.BatchMove');
	}

	/**
	 * Get The BatchMove Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedModelBatchMove
	 * @since   6.1.7
	 */
	public function getSharedModelBatchMove(Container $container): SharedModelBatchMove
	{
		return new SharedModelBatchMove(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Category.Code'),
			$container->get('Compiler.Builder.Content.One')
		);
	}

	/**
	 * Get The BatchMove Class for Joomla 3.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ModelBatchMove
	 * @since   6.1.7
	 */
	public function getJ3ModelBatchMove(Container $container): J3ModelBatchMove
	{
		return new J3ModelBatchMove(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Category.Code'),
			$container->get('Compiler.Builder.Content.One')
		);
	}

	/**
	 * Get The GetForm Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ModelGetForm
	 * @since   6.1.7
	 */
	public function getModelGetForm(Container $container): ModelGetForm
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 takes the current user from the global factory
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.Model.J3.GetForm');
		}

		return $container->get('Architecture.Model.Shared.GetForm');
	}

	/**
	 * Get The GetForm Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedModelGetForm
	 * @since   6.1.7
	 */
	public function getSharedModelGetForm(Container $container): SharedModelGetForm
	{
		return new SharedModelGetForm(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Customcode.Dispenser'),
			$container->get('Field.Groups'),
			$container->get('Compiler.Builder.Permission.Fields')
		);
	}

	/**
	 * Get The GetForm Class for Joomla 3.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ModelGetForm
	 * @since   6.1.7
	 */
	public function getJ3ModelGetForm(Container $container): J3ModelGetForm
	{
		return new J3ModelGetForm(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Customcode.Dispenser'),
			$container->get('Field.Groups'),
			$container->get('Compiler.Builder.Permission.Fields')
		);
	}

	/**
	 * Get The AllowEdit Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  AllowEditInterface
	 * @since   5.1.4
	 */
	public function getModelAllowEdit(Container $container): AllowEditInterface
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		// only Joomla 3 checks the edit state the legacy way
		if ((int) $this->targetVersion === 3)
		{
			return $container->get('Architecture.Model.J3.AllowEdit');
		}

		return $container->get('Architecture.Model.Shared.AllowEdit');
	}

	/**
	 * Get The AllowEdit Class shared by every remaining target.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  SharedModelAllowEdit
	 * @since   6.1.7
	 */
	public function getSharedModelAllowEdit(Container $container): SharedModelAllowEdit
	{
		return new SharedModelAllowEdit(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Category'),
			$container->get('Compiler.Builder.Category.Other.Name')
		);
	}

	/**
	 * Get The AllowEdit Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ModelAllowEdit
	 * @since   5.1.4
	 */
	public function getJ3ModelAllowEdit(Container $container): J3ModelAllowEdit
	{
		return new J3ModelAllowEdit(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission'),
			$container->get('Customcode.Dispenser')
		);
	}

	/**
	 * Get The Model CanDelete Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CanDeleteInterface
	 * @since 3.2.0
	 */
	public function getModelCanDelete(Container $container): CanDeleteInterface
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		return $container->get('Architecture.Model.J' . $this->targetVersion . '.CanDelete');
	}

	/**
	 * Get The Model CanDelete Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6ModelCanDelete
	 * @since   5.1.2
	 */
	public function getJ6ModelCanDelete(Container $container): J6ModelCanDelete
	{
		return new J6ModelCanDelete(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission')
		);
	}

	/**
	 * Get The Model CanDelete Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J5ModelCanDelete
	 * @since 3.2.0
	 */
	public function getJ5ModelCanDelete(Container $container): J5ModelCanDelete
	{
		return new J5ModelCanDelete(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission')
		);
	}

	/**
	 * Get The Model CanDelete Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J4ModelCanDelete
	 * @since 3.2.0
	 */
	public function getJ4ModelCanDelete(Container $container): J4ModelCanDelete
	{
		return new J4ModelCanDelete(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission')
		);
	}

	/**
	 * Get The Model CanDelete Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ModelCanDelete
	 * @since 3.2.0
	 */
	public function getJ3ModelCanDelete(Container $container): J3ModelCanDelete
	{
		return new J3ModelCanDelete(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission')
		);
	}

	/**
	 * Get The Model Can Edit State Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CanEditStateInterface
	 * @since 3.2.0
	 */
	public function getModelCanEditState(Container $container): CanEditStateInterface
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		return $container->get('Architecture.Model.J' . $this->targetVersion . '.CanEditState');
	}

	/**
	 * Get The Model Can Edit State Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6ModelCanEditState
	 * @since  5.1.2
	 */
	public function getJ6ModelCanEditState(Container $container): J6ModelCanEditState
	{
		return new J6ModelCanEditState(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission')
		);
	}

	/**
	 * Get The Model Can Edit State Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J5ModelCanEditState
	 * @since 3.2.0
	 */
	public function getJ5ModelCanEditState(Container $container): J5ModelCanEditState
	{
		return new J5ModelCanEditState(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission')
		);
	}

	/**
	 * Get The Model Can Edit State Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J4ModelCanEditState
	 * @since 3.2.0
	 */
	public function getJ4ModelCanEditState(Container $container): J4ModelCanEditState
	{
		return new J4ModelCanEditState(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission')
		);
	}

	/**
	 * Get The Model Can Edit State Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3ModelCanEditState
	 * @since 3.2.0
	 */
	public function getJ3ModelCanEditState(Container $container): J3ModelCanEditState
	{
		return new J3ModelCanEditState(
			$container->get('Config'),
			$container->get('Compiler.Creator.Permission')
		);
	}

	/**
	 * Get The Model CanDelete Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CheckInNowInterface
	 * @since   5.1.2
	 */
	public function getCheckInNow(Container $container): CheckInNowInterface
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		return $container->get('Architecture.Model.J' . $this->targetVersion . '.CheckInNow');
	}

	/**
	 * Get The Model CheckInNow Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6CheckInNow
	 * @since   5.1.2
	 */
	public function getJ6CheckInNow(Container $container): J6CheckInNow
	{
		return new J6CheckInNow();
	}


	/**
	 * Get The Model CheckInNow Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J5CheckInNow
	 * @since   5.1.2
	 */
	public function getJ5CheckInNow(Container $container): J5CheckInNow
	{
		return new J5CheckInNow();
	}

	/**
	 * Get The Model CheckInNow Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J4CheckInNow
	 * @since   5.1.2
	 */
	public function getJ4CheckInNow(Container $container): J4CheckInNow
	{
		return new J4CheckInNow();
	}

	/**
	 * Get The Model CheckInNow Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3CheckInNow
	 * @since   5.1.2
	 */
	public function getJ3CheckInNow(Container $container): J3CheckInNow
	{
		return new J3CheckInNow();
	}
	/**
	 * Get The Model CheckboxSave Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ModelCheckboxSave
	 * @since   6.1.7
	 */
	public function getModelCheckboxSave(Container $container): ModelCheckboxSave
	{
		return new ModelCheckboxSave(
			$container->get('Compiler.Builder.Check.Box')
		);
	}

	/**
	 * Get The Table Constructor Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  TableConstructor
	 * @since   6.1.7
	 */
	public function getTableConstructor(Container $container): TableConstructor
	{
		return new TableConstructor(
			$container->get('Config'),
			$container->get('Compiler.Builder.Tags'),
			$container->get('Compiler.Builder.History'),
			$container->get('Compiler.Builder.Category.Code')
		);
	}

	/**
	 * Get The Model AjaxMethods Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ModelAjaxMethods
	 * @since   6.1.7
	 */
	public function getModelAjaxMethods(Container $container): ModelAjaxMethods
	{
		return new ModelAjaxMethods(
			$container->get('Customcode.Dispenser'),
			$container->get('Placeholder')
		);
	}

	/**
	 * Get The Model StoredId Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ModelStoredId
	 * @since   6.1.7
	 */
	public function getModelStoredId(Container $container): ModelStoredId
	{
		return new ModelStoredId(
			$container->get('Compiler.Builder.Admin.Filter.Type'),
			$container->get('Compiler.Builder.Filter'),
			$container->get('Compiler.Builder.Access.Switch'),
			$container->get('Compiler.Builder.Field.Names'),
			$container->get('Config'),
			$container->get('Compiler.Builder.Sort')
		);
	}

	/**
	 * Get The Model PopulateState Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ModelPopulateState
	 * @since   6.1.7
	 */
	public function getModelPopulateState(Container $container): ModelPopulateState
	{
		return new ModelPopulateState(
			$container->get('Compiler.Builder.Admin.Filter.Type'),
			$container->get('Compiler.Builder.Filter'),
			$container->get('Compiler.Builder.Field.Names'),
			$container->get('Compiler.Builder.Sort')
		);
	}

	/**
	 * Get The Model SortFields Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ModelSortFields
	 * @since   6.1.7
	 */
	public function getModelSortFields(Container $container): ModelSortFields
	{
		return new ModelSortFields(
			$container->get('Compiler.Builder.Sort')
		);
	}

	/**
	 * Get The Model FilterFields Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ModelFilterFields
	 * @since   6.1.7
	 */
	public function getModelFilterFields(Container $container): ModelFilterFields
	{
		return new ModelFilterFields(
			$container->get('Compiler.Builder.Filter'),
			$container->get('Compiler.Builder.Access.Switch'),
			$container->get('Compiler.Builder.Sort')
		);
	}

	/**
	 * Get The Model GetItemMethod Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ModelGetItemMethod
	 * @since   6.1.7
	 */
	public function getModelGetItemMethod(Container $container): ModelGetItemMethod
	{
		return new ModelGetItemMethod(
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Base.Six.Four'),
			$container->get('Compiler.Builder.Json.Item'),
			$container->get('Compiler.Builder.Json.Item.Array'),
			$container->get('Compiler.Builder.Json.String'),
			$container->get('Compiler.Builder.Tags'),
			$container->get('Customcode.Dispenser'),
			$container->get('Compiler.Builder.Model.Basic.Field'),
			$container->get('Compiler.Builder.Model.Medium.Field'),
			$container->get('Compiler.Builder.Model.Whmcs.Field'),
			$container->get('Compiler.Builder.Model.Expert.Field'),
			$container->get('Compiler.Builder.Model.Expert.Field.Initiator')
		);
	}

	/**
	 * Get The Model GenerateNewTitle Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ModelGenerateNewTitle
	 * @since   6.1.7
	 */
	public function getModelGenerateNewTitle(Container $container): ModelGenerateNewTitle
	{
		return new ModelGenerateNewTitle(
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Alias'),
			$container->get('Compiler.Builder.Custom.Alias'),
			$container->get('Compiler.Builder.Title')
		);
	}

	/**
	 * Get The Model GenerateNewAlias Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ModelGenerateNewAlias
	 * @since   6.1.7
	 */
	public function getModelGenerateNewAlias(Container $container): ModelGenerateNewAlias
	{
		return new ModelGenerateNewAlias(
			$container->get('Compiler.Builder.Alias'),
			$container->get('Compiler.Builder.Custom.Alias'),
			$container->get('Compiler.Builder.Title')
		);
	}

	/**
	 * Get the native conditional rule emitter.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ModelConditionalRule
	 * @since   6.2.0
	 */
	public function getModelConditionalRule(Container $container): ModelConditionalRule
	{
		return new ModelConditionalRule();
	}

	/**
	 * Get The Model ValidationFix Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ModelValidationFix
	 * @since   6.1.7
	 */
	public function getModelValidationFix(Container $container): ModelValidationFix
	{
		return new ModelValidationFix(
			$container->get('Compiler.Builder.Validation.Fix'),
			$container->get('Registry'),
			$container->get('Architecture.Model.ConditionalRule')
		);
	}

	/**
	 * Get The Model UniqueFields Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ModelUniqueFields
	 * @since   6.1.7
	 */
	public function getModelUniqueFields(Container $container): ModelUniqueFields
	{
		return new ModelUniqueFields(
			$container->get('Compiler.Builder.Database.Unique.Guid'),
			$container->get('Compiler.Builder.Database.Unique.Keys'),
			$container->get('Compiler.Builder.Database.Tables'),
			$container->get('Config'),
			$container->get('Language')
		);
	}
}

