<?php
/*
 * @package plugin searchjshopping for Joomla! 4.x
 * @version $Id: searchjshopping 1.0.0 2022-05-01 10:10:10Z $
 * @author KWProductions Co.
 * @copyright (C) 2022- KwProductions Co.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 
 This file is part of searchjshopping.
    searchjshopping is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.
    searchjshopping is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with searchjshopping.  If not, see <http://www.gnu.org/licenses/>.
 
*/
 

defined('_JEXEC') or die;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Database\DatabaseQuery;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;


class PlgFinderSearchJshopping extends Adapter
{
	

	protected $context = 'Product';

	
	protected $extension = 'com_jshopping';

	
	protected $layout = 'Product';

	
	protected $type_title = 'Product';

	
	protected $table = '#__jshopping_products';

	
	protected $state_field = 'published';


	protected $autoloadLanguage = true;


	public function onFinderCategoryChangeState($extension, $pks, $value)
	{
		if ($extension === 'com_jshopping')
		{
			$this->categoryStateChange($pks, $value);
		}
	}
	
		public function onAfterInitialise()
	{
		
		$this->loadLanguage();
	}


	public function onFinderAfterDelete($context, $table): void
	{
		if ($context === 'com_jshopping.product')
		{
			$id = $table->id;
		}
		elseif ($context === 'com_finder.index')
		{
			$id = $table->link_id;
		}
		else
		{
			return;
		}

		$this->remove($id);
	}

	
	public function onFinderAfterSave($context, $row, $isNew): void
	{
		if ($context === 'com_jshopping.product')
		{
			if (!$isNew && $this->old_access != $row->access)
			{
				$this->itemAccessChange($row);
			}

			$this->reindex($row->id);
		}

		if ($context === 'com_jshopping.category')
		{
			if (!$isNew && $this->old_cataccess != $row->access)
			{
				$this->categoryAccessChange($row);
			}
		}
	}

	
	public function onFinderBeforeSave($context, $row, $isNew)
	{
	
		if ($context === 'com_jshopping.product')
		{
			if (!$isNew)
			{
				$this->checkItemAccess($row);
			}
		}

		if ($context === 'com_jshopping.category')
		{
			if (!$isNew)
			{
				$this->checkCategoryAccess($row);
			}
		}

		return true;
	}

	
	public function onFinderChangeState($context, $pks, $value)
	{
		if ($context === 'com_jshopping.product')
		{
			$this->itemStateChange($pks, $value);
		}

		if ($context === 'com_plugins.plugin' && $value === 0)
		{
			$this->pluginDisable($pks);
		}
	}

	protected function index(Result $item)
	{
		
		
		  $lang = Factory::getLanguage('site');
		  $tag = $lang->getTag();
		
		if (ComponentHelper::isEnabled($this->extension) === false)
		{
			return;
		}

		$item->setLanguage();

	
		
			$registry = new Registry('{}');
		$item->params = clone ComponentHelper::getParams('com_jshopping', true);

		$item->params->merge($registry);
		
				
		$item->product_url = "index.php?option=com_jshopping&view=product&task=view&category_id=" . $item->catslug . '&product_id=' . $item->slug;
        $item->url = $item->product_url;
		$item->route = $item->product_url;
		$item->access = 1;
		

			if(empty($item->summary))
				$item->summary = Helper::prepareContent($item->body, $item->params, $item);
			else
				$item->summary = Helper::prepareContent($item->summary, $item->params, $item);

			    $item->publish_start_date =  Factory::getDate()->toSql();
		$item->start_date= Factory::getDate()->toSql();
		$item->state = 1;
		
		$item->title = $item->t;
			


		Helper::getContentExtras($item);

		$this->indexer->index($item);
	}

	protected function setup()
	{
		
		return true;
	}

	protected function getListQuery($query = null)
	{
		$db = $this->db;
	
        $user =  Factory::getUser();
		        $lang = Factory::getLanguage('site');
								$tag = $lang->getTag();

				

        
      
        

			  
			  	$query = $query instanceof DatabaseQuery ? $query : $db->getQuery(true)          
			     ->select($db->quoteName(['prod.product_id', 'pr_cat.category_id'], ['slug', 'catslug'])) 
				 ->select($db->quoteName('prod.name_'.$tag, 't'))
                 ->select($db->quoteName('prod.description_'.$tag, 'body'))	
                 ->select($db->quoteName('prod.short_description_'.$tag, 'summary'))					 
                 ->select($db->quoteName('prod.product_date_added', 'created'))
				 ->select($db->quoteName('cat.name_'.$tag ,'section'))				 
               ->from($db->quoteName('#__jshopping_products', 'prod'))	
               ->join('LEFT', $db->quoteName('#__jshopping_products_to_categories','pr_cat') .' ON '. $db->quoteName('pr_cat.product_id').' = '. $db->quoteName('prod.product_id') )	
               ->join('LEFT', $db->quoteName('#__jshopping_categories','cat') .' ON '. $db->quoteName('pr_cat.category_id').' = '. $db->quoteName('cat.category_id') )	
	            ->where($db->quoteName('prod.product_publish').' = 1')
		        ->where($db->quoteName('cat.category_publish').' = 1');	
              		  
             

		return $query;
	}
}
