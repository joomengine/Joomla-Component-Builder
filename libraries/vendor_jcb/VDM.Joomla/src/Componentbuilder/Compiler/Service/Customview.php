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
use VDM\Joomla\Componentbuilder\Compiler\Customview\Data as CustomviewData;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\Data as DynamicgetData;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\Selection as DynamicgetSelection;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\Methods;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\GetItems;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\GetItem;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\JoomlaSix\GetItem as J6GetItem;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\ListQuery;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\JoomlaSix\ListQuery as J6ListQuery;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\CustomGetMethods;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\JoomlaSix\CustomGetMethods as J6CustomGetMethods;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\Queries;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\QueryFilter;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\QueryWhere;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\QueryOrder;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\QueryGroup;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\UikitLoader;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\Globals;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\CustomJoin;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\JoinStructure;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\DecodeColumn;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\FilterColumn;
use VDM\Joomla\Componentbuilder\Compiler\Dynamicget\FieldonContentPrepare;


/**
 * Compiler Customview
 * 
 * @since 3.2.0
 */
class Customview implements ServiceProviderInterface
{
	/**
	 * Compile target selected for this build scope.
	 *
	 * @var   int
	 * @since 6.2.0
	 */
	protected int $targetVersion;

	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 * @since   3.2.0
	 */
	public function register(Container $container)
	{
		$container->alias(CustomviewData::class, 'Customview.Data')
			->share('Customview.Data', [$this, 'getCustomviewData'], true);

		$container->alias(DynamicgetData::class, 'Dynamicget.Data')
			->share('Dynamicget.Data', [$this, 'getDynamicgetData'], true);

		$container->alias(DynamicgetSelection::class, 'Dynamicget.Selection')
			->share('Dynamicget.Selection', [$this, 'getDynamicgetSelection'], true);

		$container->alias(Methods::class, 'Dynamicget.Methods')
			->share('Dynamicget.Methods', [$this, 'getMethods'], true);

		$container->alias(GetItems::class, 'Dynamicget.GetItems')
			->share('Dynamicget.GetItems', [$this, 'getGetItems'], true);

		$container->alias(GetItem::class, 'Dynamicget.GetItem')
			->share('Dynamicget.GetItem', [$this, 'getGetItem'], true);

		$container->share('Dynamicget.Shared.GetItem', [$this, 'getSharedGetItem'], true);

		$container->alias(J6GetItem::class, 'Dynamicget.J6.GetItem')
			->share('Dynamicget.J6.GetItem', [$this, 'getJ6GetItem'], true);

		$container->alias(ListQuery::class, 'Dynamicget.ListQuery')
			->share('Dynamicget.ListQuery', [$this, 'getListQuery'], true);

		$container->share('Dynamicget.Shared.ListQuery', [$this, 'getSharedListQuery'], true);

		$container->alias(J6ListQuery::class, 'Dynamicget.J6.ListQuery')
			->share('Dynamicget.J6.ListQuery', [$this, 'getJ6ListQuery'], true);

		$container->alias(CustomGetMethods::class, 'Dynamicget.CustomGetMethods')
			->share('Dynamicget.CustomGetMethods', [$this, 'getCustomGetMethods'], true);

		$container->share('Dynamicget.Shared.CustomGetMethods', [$this, 'getSharedCustomGetMethods'], true);

		$container->alias(J6CustomGetMethods::class, 'Dynamicget.J6.CustomGetMethods')
			->share('Dynamicget.J6.CustomGetMethods', [$this, 'getJ6CustomGetMethods'], true);

		$container->alias(Queries::class, 'Dynamicget.Queries')
			->share('Dynamicget.Queries', [$this, 'getQueries'], true);

		$container->alias(QueryFilter::class, 'Dynamicget.QueryFilter')
			->share('Dynamicget.QueryFilter', [$this, 'getQueryFilter'], true);

		$container->alias(QueryWhere::class, 'Dynamicget.QueryWhere')
			->share('Dynamicget.QueryWhere', [$this, 'getQueryWhere'], true);

		$container->alias(QueryOrder::class, 'Dynamicget.QueryOrder')
			->share('Dynamicget.QueryOrder', [$this, 'getQueryOrder'], true);

		$container->alias(QueryGroup::class, 'Dynamicget.QueryGroup')
			->share('Dynamicget.QueryGroup', [$this, 'getQueryGroup'], true);

		$container->alias(UikitLoader::class, 'Dynamicget.UikitLoader')
			->share('Dynamicget.UikitLoader', [$this, 'getUikitLoader'], true);

		$container->alias(Globals::class, 'Dynamicget.Globals')
			->share('Dynamicget.Globals', [$this, 'getGlobals'], true);

		$container->alias(CustomJoin::class, 'Dynamicget.CustomJoin')
			->share('Dynamicget.CustomJoin', [$this, 'getCustomJoin'], true);

		$container->alias(JoinStructure::class, 'Dynamicget.JoinStructure')
			->share('Dynamicget.JoinStructure', [$this, 'getJoinStructure'], true);

		$container->alias(DecodeColumn::class, 'Dynamicget.DecodeColumn')
			->share('Dynamicget.DecodeColumn', [$this, 'getDecodeColumn'], true);

		$container->alias(FilterColumn::class, 'Dynamicget.FilterColumn')
			->share('Dynamicget.FilterColumn', [$this, 'getFilterColumn'], true);

		$container->alias(FieldonContentPrepare::class, 'Dynamicget.FieldonContentPrepare')
			->share('Dynamicget.FieldonContentPrepare', [$this, 'getFieldonContentPrepare'], true);
	}

	/**
	 * Get the Compiler Customview Data
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CustomviewData
	 * @since   3.2.0
	 */
	public function getCustomviewData(Container $container): CustomviewData
	{
		return new CustomviewData(
			$container->get('Config'),
			$container->get('Event'),
			$container->get('Customcode'),
			$container->get('Customcode.Gui'),
			$container->get('Model.Libraries'),
			$container->get('Templatelayout.Data'),
			$container->get('Dynamicget.Data'),
			$container->get('Model.Loader'),
			$container->get('Model.Javascriptcustomview'),
			$container->get('Model.Csscustomview'),
			$container->get('Model.Phpcustomview'),
			$container->get('Model.Ajaxcustomview'),
			$container->get('Model.Custombuttons'),
			$container->get('Utilities.Counter'),
			$container->get('Joomla.Database'),
			$container->get('Package.Get')
		);
	}

	/**
	 * Get the Compiler Dynamicget Data
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DynamicgetData
	 * @since 3.2.0
	 */
	public function getDynamicgetData(Container $container): DynamicgetData
	{
		return new DynamicgetData(
			$container->get('Config'),
			$container->get('Registry'),
			$container->get('Event'),
			$container->get('Customcode'),
			$container->get('Customcode.Dispenser'),
			$container->get('Customcode.Gui'),
			$container->get('Model.Dynamicget'),
			$container->get('Utilities.Counter'),
			$container->get('Joomla.Database'),
			$container->get('Package.Get')
		);
	}

	/**
	 * Get the Compiler Dynamicget Selection
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DynamicgetSelection
	 * @since 3.2.0
	 */
	public function getDynamicgetSelection(Container $container): DynamicgetSelection
	{
		return new DynamicgetSelection(
			$container->get('Config'),
			$container->get('Compiler.Builder.Get.As.Lookup'),
			$container->get('Compiler.Builder.Site.Fields'),
			$container->get('Joomla.Database')
		);
	}

	/**
	 * Get The Methods Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Methods
	 * @since   5.1.2
	 */
	public function getMethods(Container $container): Methods
	{
		return new Methods(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Dynamicget.GetItem'),
			$container->get('Dynamicget.GetItems'),
			$container->get('Dynamicget.ListQuery'),
			$container->get('Dynamicget.CustomGetMethods'),
			$container->get('Dynamicget.UikitLoader')
		);
	}

	/**
	 * Get The GetItems Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  GetItems
	 * @since   5.1.2
	 */
	public function getGetItems(Container $container): GetItems
	{
		return new GetItems(
			$container->get('Config'),
			$container->get('Compiler.Builder.Site.Decrypt'),
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Site.Field.Data'),
			$container->get('Compiler.Builder.Site.Field.Decode.Filter'),
			$container->get('Compiler.Builder.Model.Expert.Field.Initiator'),
			$container->get('Compiler.Builder.Event.Dispatcher'),
			$container->get('Dynamicget.DecodeColumn'),
			$container->get('Dynamicget.FilterColumn'),
			$container->get('Dynamicget.FieldonContentPrepare'),
			$container->get('Dynamicget.UikitLoader'),
			$container->get('Dynamicget.Globals'),
			$container->get('Dynamicget.CustomJoin')
		);
	}

	/**
	 * Get The GetItem Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  GetItem
	 * @since   5.1.2
	 */
	public function getGetItem(Container $container): GetItem
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		if ((int) $this->targetVersion === 6)
		{
			return $container->get('Dynamicget.J6.GetItem');
		}

		return $container->get('Dynamicget.Shared.GetItem');
	}

	/**
	 * Build the GetItem renderer for shared targets.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  GetItem
	 * @since   6.2.0
	 */
	public function getSharedGetItem(Container $container): GetItem
	{
		return new GetItem(
			$container->get('Config'),
			$container->get('Compiler.Builder.Site.Decrypt'),
			$container->get('Placeholder'),
			$container->get('Language'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Site.Field.Data'),
			$container->get('Compiler.Builder.Site.Field.Decode.Filter'),
			$container->get('Compiler.Builder.Model.Expert.Field.Initiator'),
			$container->get('Compiler.Builder.Event.Dispatcher'),
			$container->get('Dynamicget.DecodeColumn'),
			$container->get('Dynamicget.FilterColumn'),
			$container->get('Dynamicget.FieldonContentPrepare'),
			$container->get('Dynamicget.UikitLoader'),
			$container->get('Dynamicget.Globals'),
			$container->get('Dynamicget.CustomJoin'),
			$container->get('Dynamicget.Queries'),
			$container->get('Dynamicget.QueryFilter'),
			$container->get('Dynamicget.QueryWhere'),
			$container->get('Dynamicget.QueryOrder'),
			$container->get('Dynamicget.QueryGroup')
		);
	}

	/**
	 * Build the GetItem renderer for Joomla 6 targets.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6GetItem
	 * @since   6.2.0
	 */
	public function getJ6GetItem(Container $container): J6GetItem
	{
		return new J6GetItem(
			$container->get('Config'),
			$container->get('Compiler.Builder.Site.Decrypt'),
			$container->get('Placeholder'),
			$container->get('Language'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Site.Field.Data'),
			$container->get('Compiler.Builder.Site.Field.Decode.Filter'),
			$container->get('Compiler.Builder.Model.Expert.Field.Initiator'),
			$container->get('Compiler.Builder.Event.Dispatcher'),
			$container->get('Dynamicget.DecodeColumn'),
			$container->get('Dynamicget.FilterColumn'),
			$container->get('Dynamicget.FieldonContentPrepare'),
			$container->get('Dynamicget.UikitLoader'),
			$container->get('Dynamicget.Globals'),
			$container->get('Dynamicget.CustomJoin'),
			$container->get('Dynamicget.Queries'),
			$container->get('Dynamicget.QueryFilter'),
			$container->get('Dynamicget.QueryWhere'),
			$container->get('Dynamicget.QueryOrder'),
			$container->get('Dynamicget.QueryGroup')
		);
	}

	/**
	 * Get The ListQuery Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ListQuery
	 * @since   5.1.2
	 */
	public function getListQuery(Container $container): ListQuery
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		if ((int) $this->targetVersion === 6)
		{
			return $container->get('Dynamicget.J6.ListQuery');
		}

		return $container->get('Dynamicget.Shared.ListQuery');
	}

	/**
	 * Build the ListQuery renderer for shared targets.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ListQuery
	 * @since   6.2.0
	 */
	public function getSharedListQuery(Container $container): ListQuery
	{
		return new ListQuery(
			$container->get('Config'),
			$container->get('Customcode.Dispenser'),
			$container->get('Dynamicget.Queries'),
			$container->get('Dynamicget.QueryFilter'),
			$container->get('Dynamicget.QueryWhere'),
			$container->get('Dynamicget.QueryOrder'),
			$container->get('Dynamicget.QueryGroup')
		);
	}

	/**
	 * Build the ListQuery renderer for Joomla 6 targets.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6ListQuery
	 * @since   6.2.0
	 */
	public function getJ6ListQuery(Container $container): J6ListQuery
	{
		return new J6ListQuery(
			$container->get('Config'),
			$container->get('Customcode.Dispenser'),
			$container->get('Dynamicget.Queries'),
			$container->get('Dynamicget.QueryFilter'),
			$container->get('Dynamicget.QueryWhere'),
			$container->get('Dynamicget.QueryOrder'),
			$container->get('Dynamicget.QueryGroup')
		);
	}

	/**
	 * Get The CustomGetMethods Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CustomGetMethods
	 * @since   5.1.2
	 */
	public function getCustomGetMethods(Container $container): CustomGetMethods
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		if ((int) $this->targetVersion === 6)
		{
			return $container->get('Dynamicget.J6.CustomGetMethods');
		}

		return $container->get('Dynamicget.Shared.CustomGetMethods');
	}

	/**
	 * Build the CustomGetMethods renderer for shared targets.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CustomGetMethods
	 * @since   6.2.0
	 */
	public function getSharedCustomGetMethods(Container $container): CustomGetMethods
	{
		return new CustomGetMethods(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Dynamicget.FieldonContentPrepare'),
			$container->get('Dynamicget.JoinStructure'),
			$container->get('Dynamicget.DecodeColumn'),
			$container->get('Dynamicget.FilterColumn'),
			$container->get('Dynamicget.UikitLoader'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Site.Decrypt'),
			$container->get('Compiler.Builder.Model.Expert.Field.Initiator'),
			$container->get('Compiler.Builder.Site.Field.Data'),
			$container->get('Compiler.Builder.Site.Field.Decode.Filter'),
			$container->get('Compiler.Builder.Other.Join'),
			$container->get('Compiler.Builder.Other.Query'),
			$container->get('Compiler.Builder.Other.Filter'),
			$container->get('Compiler.Builder.Other.Where'),
			$container->get('Compiler.Builder.Other.Order'),
			$container->get('Compiler.Builder.Other.Group'),
			$container->get('Compiler.Builder.Event.Dispatcher')
		);
	}

	/**
	 * Build the CustomGetMethods renderer for Joomla 6 targets.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J6CustomGetMethods
	 * @since   6.2.0
	 */
	public function getJ6CustomGetMethods(Container $container): J6CustomGetMethods
	{
		return new J6CustomGetMethods(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Dynamicget.FieldonContentPrepare'),
			$container->get('Dynamicget.JoinStructure'),
			$container->get('Dynamicget.DecodeColumn'),
			$container->get('Dynamicget.FilterColumn'),
			$container->get('Dynamicget.UikitLoader'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Site.Decrypt'),
			$container->get('Compiler.Builder.Model.Expert.Field.Initiator'),
			$container->get('Compiler.Builder.Site.Field.Data'),
			$container->get('Compiler.Builder.Site.Field.Decode.Filter'),
			$container->get('Compiler.Builder.Other.Join'),
			$container->get('Compiler.Builder.Other.Query'),
			$container->get('Compiler.Builder.Other.Filter'),
			$container->get('Compiler.Builder.Other.Where'),
			$container->get('Compiler.Builder.Other.Order'),
			$container->get('Compiler.Builder.Other.Group'),
			$container->get('Compiler.Builder.Event.Dispatcher')
		);
	}

	/**
	 * Get The Queries Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Queries
	 * @since   5.1.2
	 */
	public function getQueries(Container $container): Queries
	{
		return new Queries(
			$container->get('Config'),
			$container->get('Dynamicget.JoinStructure'),
			$container->get('Compiler.Builder.Site.Dynamic.Get'),
			$container->get('Compiler.Builder.Other.Query'),
			$container->get('Placeholder')
		);
	}

	/**
	 * Get The QueryFilter Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  QueryFilter
	 * @since   5.1.2
	 */
	public function getQueryFilter(Container $container): QueryFilter
	{
		return new QueryFilter(
			$container->get('Config'),
			$container->get('Compiler.Builder.Site.Field.Data'),
			$container->get('Compiler.Builder.Site.Field.Decode.Filter'),
			$container->get('Compiler.Builder.Site.Main.Get'),
			$container->get('Compiler.Builder.Other.Filter')
		);
	}

	/**
	 * Get The QueryWhere Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  QueryWhere
	 * @since   5.1.2
	 */
	public function getQueryWhere(Container $container): QueryWhere
	{
		return new QueryWhere(
			$container->get('Config'),
			$container->get('Compiler.Builder.Other.Where'),
			$container->get('Compiler.Builder.Site.Main.Get')
		);
	}

	/**
	 * Get The QueryOrder Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  QueryOrder
	 * @since   5.1.2
	 */
	public function getQueryOrder(Container $container): QueryOrder
	{
		return new QueryOrder(
			$container->get('Config'),
			$container->get('Compiler.Builder.Other.Order'),
			$container->get('Compiler.Builder.Site.Main.Get')
		);
	}

	/**
	 * Get The QueryGroup Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  QueryGroup
	 * @since   5.1.2
	 */
	public function getQueryGroup(Container $container): QueryGroup
	{
		return new QueryGroup(
			$container->get('Config'),
			$container->get('Compiler.Builder.Other.Group'),
			$container->get('Compiler.Builder.Site.Main.Get')
		);
	}

	/**
	 * Get The UikitLoader Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  UikitLoader
	 * @since   5.1.2
	 */
	public function getUikitLoader(Container $container): UikitLoader
	{
		return new UikitLoader(
			$container->get('Config'),
			$container->get('Compiler.Builder.Content.One')
		);
	}

	/**
	 * Get The Globals Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Globals
	 * @since   5.1.2
	 */
	public function getGlobals(Container $container): Globals
	{
		return new Globals();
	}

	/**
	 * Get The CustomJoin Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  CustomJoin
	 * @since   5.1.2
	 */
	public function getCustomJoin(Container $container): CustomJoin
	{
		return new CustomJoin(
			$container->get('Config'),
			$container->get('Compiler.Builder.Site.Dynamic.Get'),
			$container->get('Compiler.Builder.Other.Join'),
			$container->get('Compiler.Builder.Get.As.Lookup'),
			$container->get('Dynamicget.JoinStructure')
		);
	}

	/**
	 * Get The JoinStructure Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  JoinStructure
	 * @since   5.1.2
	 */
	public function getJoinStructure(Container $container): JoinStructure
	{
		return new JoinStructure();
	}

	/**
	 * Get The DecodeColumn Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  DecodeColumn
	 * @since   5.1.2
	 */
	public function getDecodeColumn(Container $container): DecodeColumn
	{
		return new DecodeColumn(
			$container->get('Config'),
			$container->get('Placeholder'),
			$container->get('Compiler.Builder.Model.Expert.Field'),
			$container->get('Compiler.Builder.Site.Decrypt')
		);
	}

	/**
	 * Get The FilterColumn Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FilterColumn
	 * @since   5.1.2
	 */
	public function getFilterColumn(Container $container): FilterColumn
	{
		return new FilterColumn();
	}

	/**
	 * Get The FieldonContentPrepare Class.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  FieldonContentPrepare
	 * @since   5.1.2
	 */
	public function getFieldonContentPrepare(Container $container): FieldonContentPrepare
	{
		return new FieldonContentPrepare(
			$container->get('Config'),
			$container->get('Compiler.Builder.Content.One'),
			$container->get('Compiler.Builder.Event.Dispatcher')
		);
	}
}

