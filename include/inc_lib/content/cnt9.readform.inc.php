<?php
/*************************************************************************************
   Copyright notice
   
   (c) 2002-2007 Oliver Georgi (oliver@phpwcms.de) // All rights reserved.
 
   This script is part of PHPWCMS. The PHPWCMS web content management system is
   free software; you can redistribute it and/or modify it under the terms of
   the GNU General Public License as published by the Free Software Foundation;
   either version 2 of the License, or (at your option) any later version.
  
   The GNU General Public License can be found at http://www.gnu.org/copyleft/gpl.html
   A copy is found in the textfile GPL.txt and important notices to the license 
   from the author is found in LICENSE.txt distributed with these scripts.
  
   This script is distributed in the hope that it will be useful, but WITHOUT ANY 
   WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
   PARTICULAR PURPOSE.  See the GNU General Public License for more details.

   This copyright notice MUST APPEAR in all copies of the script!
*************************************************************************************/


// ----------------------------------------------------------------
// obligate check for phpwcms constants
if (!defined('PHPWCMS_ROOT')) {
   die("You Cannot Access This Script Directly, Have a Nice Day.");
}
// ----------------------------------------------------------------



// Content Type Multimedia
$content["media_type"]			= isset($_POST["cmedia_type"]) ? intval($_POST["cmedia_type"]) : 0;
$content["media_player"]		= isset($_POST["cmedia_player"]) ? intval($_POST["cmedia_player"]) : 0;
$content["media_src"]			= isset($_POST["cmedia_src"]) ? intval($_POST["cmedia_src"]) : 0;
$content["media_auto"]			= isset($_POST["cmedia_auto"]) ? intval($_POST["cmedia_auto"]) : 0;
$content["media_transparent"]	= empty($_POST["cmedia_transparent"]) ? 0 : 1;
$content["media_control"] 		= intval($_POST["cmedia_control"]);
$content["media_pos"] 			= intval($_POST["cimage_pos"]);
$content["media_width"] 		= intval($_POST["cmedia_width"]);
$content["media_width"] 		= ($content["media_width"]) ? $content["media_width"] : '';
$content["media_height"] 		= intval($_POST["cmedia_height"]);
$content["media_height"] 		= ($content["media_height"]) ? $content["media_height"] : '';
$content["media_id"] 			= intval($_POST["cmedia_id"]);
$content["media_name"] 			= clean_slweg($_POST["cmedia_name"]);
$content["media_extern"] 		= clean_slweg($_POST["cmedia_extern"]);
/*
$content["media"] = $content["media_type"] . ":" . $content["media_player"] . ":";
$content["media"] .= $content["media_pos"] . ":" . $content["media_width"] . ":";
$content["media"] .= $content["media_height"] . ":" . $content["media_src"] . ":";
$content["media"] .= ($content["media_src"]) ? base64_encode($content["media_extern"]) : base64_encode($content["media_id"] . ":" . $content["media_name"]);
$content["media"] .= ":" . $content["media_control"] . ":" . $content["media_auto"] . ":" . $content["media_transparent"];
*/

$content['media']	= array();

$content['media']["media_type"]			= $content["media_type"];
$content['media']["media_player"]		= $content["media_player"];
$content['media']["media_src"]			= $content["media_src"];
$content['media']["media_auto"]			= $content["media_auto"];
$content['media']["media_transparent"]	= $content["media_transparent"];
$content['media']["media_control"]		= $content["media_control"];
$content['media']["media_pos"]			= $content["media_pos"];
$content['media']["media_width"]		= $content["media_width"];
$content['media']["media_height"]		= $content["media_height"];
$content['media']["media_id"]			= $content["media_id"];
$content['media']["media_name"]			= $content["media_name"];
$content['media']["media_extern"]		= $content["media_extern"];



?>