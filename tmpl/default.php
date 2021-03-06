<?php
/**
* @version $Id: mod_jdownloads_top.php
* @package mod_jdownloads_top
* @copyright (C) 2018 Arno Betz
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author Arno Betz http://www.jDownloads.com
* modified ColinM November 2020
* This module shows you the most downloaded Downloads from the jDownloads component. 
* It is only for jDownloads 3.9 and later (Support: www.jDownloads.com)
*/

defined('_JEXEC') or die;

    JHTML::_('bootstrap.tooltip');
    
    // Path to the mime type image folder (for file symbols) 
    $file_pic_folder = JDHelper::getFileTypeIconPath($jdparams->get('selected_file_type_icon_set'));

    $html = '';
	
    if ($files){
		$html = '<div style="width:100%;" class="moduletable'.$moduleclass_sfx.'">';
        if ($text_before <> ''){
			$html .= '<div class="jd_module_before" style="text-align:'.$alignment.';">'.$text_before.'</div>';			
		}
		
        for ($i=0; $i<count($files); $i++) {
			$html .= '<div style="clear:both;"></div>';
            $has_no_file = false;
            if (!$files[$i]->url_download && !$files[$i]->other_file_id && !$files[$i]->extern_file){
               // only a document without file
               $has_no_file = true;           
            }
                         
            // get the first image as thumbnail when it exist           
            $thumbnail = ''; 
            $first_image = '';
            $images = explode("|",$files[$i]->images);
            if (isset($images[0])) $first_image = $images['0'];
			//get version label
            $version = $params->get('short_version', '');
            // shorten the file title?			
            if ($sum_char > 0){
				$gesamt = strlen($files[$i]->title) + strlen($files[$i]->release) + strlen($short_version) +1;
				if ($gesamt > $sum_char){
				   $files[$i]->title = JString::substr($files[$i]->title, 0, $sum_char).$short_char;
				   $files[$i]->release = '';
				}    
			} 
			 
            if ($cat_show && $files[$i]->catid > 1) {
                if ($cat_show_type == 'containing') {
					$cat_show_text2 = $files[$i]->category_title;
                } else {
                    if ($files[$i]->category_cat_dir_parent){
						$cat_show_text2 = $files[$i]->category_cat_dir_parent.'/'.$files[$i]->category_cat_dir;
                    } else {
						$cat_show_text2 = $files[$i]->category_cat_dir;
                    }
                }
            } else {
                $cat_show_text2 = '';
            }  

            // create the link
            if ($files[$i]->link == '-'){
                // the user has access to view this item
                if ($detail_view == '1'){
                    if ($detail_view_config == 0){                    
                        // the details view is deactivated in jD config so the
                        // link must start directly the download process
                        if ($direct_download_config == 1){
                            if (!$has_no_file){
                                $link = JRoute::_('index.php?option='.$option.'&amp;task=download.send&amp;id='.$files[$i]->slug.'&amp;catid='.$files[$i]->catid.'&amp;m=0');                    
                            } else {
                                // create a link to the Downloads category as this download does not have a file
                                if ($files[$i]->menuc_cat_itemid){
                                    $link = JRoute::_('index.php?option='.$option.'&amp;view=category&catid='.$files[$i]->catid.'&amp;Itemid='.$files[$i]->menuc_cat_itemid);
                                } else {
                                    $link = JRoute::_('index.php?option='.$option.'&amp;view=category&catid='.$files[$i]->catid.'&amp;Itemid='.$Itemid);
                                }                                
                            }   
                        } else {
                            // link to the summary page
                            if (!$has_no_file){
                                $link = JRoute::_('index.php?option='.$option.'&amp;view=summary&amp;id='.$files[$i]->slug.'&amp;catid='.$files[$i]->catid);
                            } else {
                                // create a link to the Downloads category as this download has not a file
                                if ($files[$i]->menuc_cat_itemid){
                                    $link = JRoute::_('index.php?option='.$option.'&amp;view=category&catid='.$files[$i]->catid.'&amp;Itemid='.$files[$i]->menuc_cat_itemid);
                                } else {
                                    $link = JRoute::_('index.php?option='.$option.'&amp;view=category&catid='.$files[$i]->catid.'&amp;Itemid='.$Itemid);
                                }
                            }   
                        }    
                    } else {
                        // create a link to the details view
                        if ($files[$i]->menuf_itemid){
                            $link = JRoute::_('index.php?option='.$option.'&amp;view=download&id='.$files[$i]->slug.'&catid='.$files[$i]->catid.'&amp;Itemid='.$files[$i]->menuf_itemid);                    
                        } else {
                            if ($files[$i]->menuc_cat_itemid){
                                $link = JRoute::_('index.php?option='.$option.'&amp;view=download&id='.$files[$i]->slug.'&catid='.$files[$i]->catid.'&amp;Itemid='.$files[$i]->menuc_cat_itemid);                    
                            } else {
                            	$link = JRoute::_('index.php?option='.$option.'&amp;view=download&id='.$files[$i]->slug.'&catid='.$files[$i]->catid.'&amp;Itemid='.$Itemid);                    
                            }    
                        }
                    }                       
                } else {    
                    // create a link to the Downloads category
                    if ($files[$i]->menuc_cat_itemid){
                        $link = JRoute::_('index.php?option='.$option.'&amp;view=category&catid='.$files[$i]->catid.'&amp;Itemid='.$files[$i]->menuc_cat_itemid);
                    } else {
                        $link = JRoute::_('index.php?option='.$option.'&amp;view=category&catid='.$files[$i]->catid.'&amp;Itemid='.$Itemid);
                    }
                }    
            } else {
// the user has NO access to view this item
                $link = $files[$i]->link;
            }
           
            if (!$files[$i]->release) $version = '';
			// add mime file pic
            $size = 0;
			$files_pic = '';
			$number = '';
			if ($view_pics){
				$size = (int)$view_pics_size;
				if ($view_pics_link) {
					$pic_link = '<a href="'.$link.'">';
					$pic_end = '</a>';
				}
				else {
					$pic_link = '';
					$pic_end = '';
				}
				$files_pic = $pic_link.'<img src="'.$file_pic_folder.$files[$i]->file_pic.'" style="text-align:top;border:0px;" width="'.$size.'" height="'.$size.'" alt="'.substr($files[$i]->file_pic, 0, -4).'-'.$i.'"/>'.$pic_end; 
			}
			if ($view_numerical_list){
				$num = $i+1;
				$number = "$num. ";
			}    
			//  make version message including space char
			$version_msg = '';
			if ($files[$i]->release) {
				$version_msg = '&nbsp;'.$version.$files[$i]->release;
			}
            // add description in tooltip 
            if ($view_tooltip && $files[$i]->description != ''){
				$link_text = '<a href="'.$link.'">'.JHtml::tooltip(strip_tags(substr($files[$i]->description,0,$view_tooltip_length)).$short_char,JText::_('MOD_JDOWNLOADS_RELATED_DESCRIPTION_TITLE'),$files[$i]->title.$version_msg,$files[$i]->title.'</a>'.$version_msg);
			} else {    
				$link_text = '<a href="'.$link.'">'.$files[$i]->title.'</a>'.$version_msg;
			}    
			//main link msg
            $link_div = '<div style="text-align:'.$alignment.'">'.$number.$files_pic.$link_text;
			$link_end = '</div>';
			// make hits msg
			$hits_same_line = '';  //when hits not shown or hits are zero
			$hits_own_line = '';			
            if ($view_hits) {
				$hits_msg = $hits_label.modJdownloadsTopHelper::strToNumber($files[$i]->downloads);
				if ($files[$i]->downloads){
					if ($view_hits_same_line){
						$hits_same_line = '<scan class="jd_module_hits_sameline" style="text-align:'.$hits_alignment.';">&nbsp;'.$hits_msg.'</scan>'; // add space before msg
					} else {
						$hits_own_line = '<div class="jd_module_hits_newline" style="text-align:'.$hits_alignment.';">'.$hits_msg.'</div>';
					}
				}    
			}
            // make date msg
			$date_same_line = '';  //when date not shown or not set
			$date_own_line = '';
            if ($view_date) {
                if ($files[$i]->created){
                    if ($view_date_same_line){
						$date_same_line = '<scan class="jd_module_date_sameline" style="text-align:'.$date_alignment.';">&nbsp;'.$view_date_text.substr(JHTML::Date($files[$i]->created,$date_format),0,10).'</scan>';
                    } else {
						$date_own_line= '<div class="jd_module_date_newline" style="text-align:'.$date_alignment.';">'.$view_date_text.substr(JHTML::Date($files[$i]->created,$date_format),0,10).'</div>';
                    } 
                }    
            }
			// add dates and hits to html
			if ($view_hits_same_line && $view_date_same_line) {
				$html .= $link_div.$hits_same_line.$date_same_line.$link_end;  //both hits and date on same line
			} else {
				if ($view_hits_same_line && !$view_date_same_line) {
					$html .= $link_div.$hits_same_line.$link_end;		//only hits on same line
					if ($view_date) {
					$html .= $date_own_line;			//show date on separate line
					}
				} else {
					if (!$view_hits_same_line && $view_date_same_line) {
						$html .= $link_div.$date_same_line.$link_end;		//only date on same line
						if ($view_hits) {
							$html .= $hits_own_line;			//show hits on separate line
						}
					} else {
						if (!$view_hits_same_line && !$view_date_same_line) {
							$html .= $link_div.$link_end.$hits_own_line.$date_own_line;  //show on separate lines
						}
					}
				} 
			}
            // add the first download screenshot when exists and activated in options
            if ($view_thumbnails){
				if ($view_thumbnails_link) {
					$pic_link = '<a href="'.$link.'">';
					$pic_end = '</a>';
				} else {
					$pic_link = '';
					$pic_end = '';
				}	
                if ($first_image){
                   $thumbnail = $pic_link.'<img class="img jd_module_thumbnail" src="'.$thumbfolder.$first_image.'" style="text-align:top;padding:5px;border:'.$border.';" width="'.$view_thumbnails_size.'" height="'.$view_thumbnails_size.'" alt="'.substr($first_image,0,-4).'-'.$i.'" />'.$pic_end;
                } else {
                    // use placeholder
                    if ($view_thumbnails_dummy){
                        $thumbnail = $pic_link.'<img class="img jd_module_thumbnail" src="'.$thumbfolder.'no_pic.gif" style="text-align:top;padding:5px;border:'.$border.';" width="'.$view_thumbnails_size.'" height="'.$view_thumbnails_size.'" alt="no_pic-'.$i.'"/>'.$pic_end;    
                    }
                }
                if ($thumbnail) {
//					$html .= '<div style="clear:both;"></div>';
					$html .= '<div style="text-align:'.$alignment.';">'.$thumbnail.'</div>';
				}
            } 
			
			// add category info 
            if ($cat_show_text2) {
				$html .= '<div style="clear:both;"></div>';
				if ($cat_show_as_link){
                    if ($files[$i]->menuc_cat_itemid){
						$html .= '<div style="text-align:'.$alignment.';font-size:'.$cat_show_text_size.'; color:'.$cat_show_text_color.';">'.$cat_show_text.'<a href="index.php?option='.$option.'&amp;view=category&catid='.$files[$i]->catid.'&amp;Itemid='.$files[$i]->menuc_cat_itemid.'">'.$cat_show_text2.'</a></div>';
				    } else {
						$html .= '<div style="text-align:'.$alignment.';font-size:'.$cat_show_text_size.'; color:'.$cat_show_text_color.';">'.$cat_show_text.'<a href="index.php?option='.$option.'&amp;view=category&catid='.$files[$i]->catid.'&amp;Itemid='.$Itemid.'">'.$cat_show_text2.'</a></div>';
                    }    
                } else {    
					$html .= '<div style="text-align:'.$alignment.';font-size:'.$cat_show_text_size.'; color:'.$cat_show_text_color.';">'.$cat_show_text.$cat_show_text2.'</div>';
                }
			}
				$html .= '<div style="margin-bottom: 10px;></div>';
				
		}
		$html .= '<div style="clear:both;"></div>';
		if ($text_after <> ''){
			$html .= '<div class="jd_module_after" style="text-align:'.$alignment.';">'.$text_after.'</div>';
		}
		echo $html.'</div>';
    }
?>