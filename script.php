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

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerScript;

class PlgFinderSearchJshoppingInstallerScript extends InstallerScript
{
 public function install($parent)
 {
  
   
  $db  = Factory::getDbo();
  $query = $db->getQuery(true);
  $query->update('#__extensions');
  $query->set($db->quoteName('enabled') . ' = 1');
  $query->where($db->quoteName('element') . ' = ' . $db->quote('searchjshopping'));
  $query->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
  $db->setQuery($query);
  $db->execute(); 
   
  
 }
   public function uninstall($parent) 
  {
	       
       
  }
}
