<?php
/**
* @version $Id: mod_jdownloads_latest.php v3.8
* @package mod_jdownloads_latest
* @copyright (C) 2018 Arno Betz
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author Arno Betz http://www.jDownloads.com
*
*
* This modul shows you the most recent downloads from the jDownloads component. 
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once JPATH_SITE . '/components/com_jdownloads/helpers/route.php';

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jdownloads/models', 'jdownloadsModel');

class modJdownloadsTopHelper
{
	static function getList($params)
	{
        $db = JFactory::getDbo();

        // Get an instance of the generic downloads model
        $model = JModelLegacy::getInstance ('downloads', 'jdownloadsModel', array('ignore_request' => true));

        // Set application parameters in model
        $app = JFactory::getApplication();
        $appParams = $app->getParams('com_jdownloads');
        $model->setState('params', $appParams);
       
        // Set the filters based on the module params
        $model->setState('list.start', 0);
        $model->setState('list.limit', (int) $params->get('sum_view', 5));
        $model->setState('filter.published', 1);
        $model->setState('filter.access', true);
        $model->setState('filter.user_access', true);
        
        $access = true;
        $authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));

        // Category filter
        $catid = $params->get('catid', array()); 
        if (empty($catid)){
            $model->setState('filter.category_id', '');
        } else {
            $model->setState('filter.category_id', $catid);
        }    

        // User filter
        $userId = JFactory::getUser()->get('id');

        // Filter by language
        $model->setState('filter.language', $app->getLanguageFilter());
		
		//optional apply minium rating if the selection is based on rated
		if ($params->get('Selection') =='Rated' and !empty($params->get('Rating_min')) and is_numeric($params->get('Rating_min')) ) {
			//same rating formule as in models a filter.rating would be nice to keep calculation in one place.
			$model->setState('filter.additional', 'ROUND(r.rating_sum / r.rating_count, 0) >='. $params->get('Rating_min') );
		}

        // Set sort ordering default
        $ordering = 'a.downloads';
        $dir = 'DESC'; //Default is desc for everything
		
		
		//Retrieve ordering column from parameters
		if ($params->get('download_ordering') =='Default') {
			
			//Find which column sorting match the selection
			switch ($params->get('Selection')) {
				case 'Hits':
				 $ordering = 'a.downloads';
				 break;
				case 'Rated':
				 $ordering = 'rating';
				 break;
				case 'Updated':
				 $ordering = 'a.modified' ;
				 break;
				case 'Latest':
				// or should it be ordered wtih ID?
				 $ordering = 'a.created';
				 break;
				default: 
				 $ordering = 'a.downloads';
			}	
		}
		else {
			//IF not default get download order from config 
			$ordering = $params->get('download_ordering','a.downloads');
		}
		
		$model->setState('list.ordering', $ordering);


		// Sort order direction
		// if it is not random or default get the value from the config		
		if ($params->get('download_ordering_direction') !='Random' AND $params->get('download_ordering_direction') =='Default'){
			// if it is not random or default get the value from the config
			$dir= $params->get('download_ordering_direction','DESC');		
		}
		
        $model->setState('list.direction', $dir);
		
		
		//Retrieve records from database
        $items = $model->getItems();
		
		
		//If sort order was random we need to shuffle the recieved rows
		//And set the number of items to show .
		$Number_of_rows = $params->get('sum_view');
		if ($params->get('download_ordering_direction') =='Random') { 
			shuffle($items);
			$Number_of_rows = $params->get('random_selection');
		}

		//delete rows from array to get to correct number of rows
		$i= 0;
		while (count($items) >$Number_of_rows) {
			unset($items[$i]);
			$i++;
		}
		$items = array_merge($items); //to reset row numbers
		
		
        foreach ($items  as &$item)
        {
            $item->slug = $item->id . ':' . $item->alias;
            $item->catslug = $item->catid . ':' . $item->category_alias;

            if ($access || in_array($item->access, $authorised))
            {
                // We know that user has the privilege to view the download
                $item->link = '-';
            } else {
                $item->link = JRoute::_('index.php?option=com_users&view=login');
            }
			$i++;
			if ($i == 0) {break; }
        }
        return $items;        
	}
    
    /**
    * remove the language tag from a given text and return only the text
    *    
    * @param string     $msg
    */
    public static function getOnlyLanguageSubstring($msg)
    {
        // Get the current locale language tag
        $lang       = JFactory::getLanguage();
        $lang_key   = $lang->getTag();        
        
        // remove the language tag from the text
        $startpos = strpos($msg, '{'.$lang_key.'}') +  strlen( $lang_key) + 2 ;
        $endpos   = strpos($msg, '{/'.$lang_key.'}') ;
        
        if ($startpos !== false && $endpos !== false){
            return substr($msg, $startpos, ($endpos - $startpos ));
        } else {    
            return $msg;
        }    
    }      
    
    /**
    * Converts a string into Float while taking the given or locale number format into account
    * Used as default the defined separator characters from the Joomla main language ini file (as example: en-GB.ini)  
    * 
    * @param mixed $str
    * @param mixed $dec_point
    * @param mixed $thousands_sep
    * @param mixed $decimals
    * @return mixed
    */
    public static function strToNumber( $str, $dec_point=null, $thousands_sep=null, $decimals = 0 )
    {
        if( is_null($dec_point) || is_null($thousands_sep) ) {
            if( is_null($dec_point) ) {
                $dec_point = JText::_('DECIMALS_SEPARATOR');
            }
            if( is_null($thousands_sep) ) {
                $thousands_sep = JText::_('THOUSANDS_SEPARATOR');
            }
        }
        // in this case use we as default the en-GB format
        if (!$dec_point) $dec_point = '.'; 
        if (!$thousands_sep) $thousands_sep = ','; 

        $number = number_format($str, $decimals, $dec_point, $thousands_sep);
        return $number;
    }    
}	
?>