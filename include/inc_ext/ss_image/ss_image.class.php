<?php
/******************************************************************************
 *
 * Purpose:	This is Image Processor core script. 
 *   				It contains the Image Processor class
 * File:		ss_image.php
 * Author:		Yuriy Horobey, SmiledSoft.com
 * Copyright:	Yuriy Horobey, SmiledSoft.com
 * Contact:	http://smiledsoft.com/
 * 
 * Please do not remove this notice.
 *
 * License information:
 * this version of ss_image.class is a special licenced version for
 * using inside of phpwcms only. There is no license for forked versions.
 * It is NOT released under the GPL and it is NOT FREE.
 * 
 *****************************************************************************/
/*
 * 2005.02.05 Oliver Georgi
 * - function set_parameter(JPEG_QUALITY, USE_GD2, USE_COLOR_NAMES) implemented
 * - $cfg is no longer a global var - and no array anymore
 * - ss_image.config.php is obsolete with this too
 * - all $cfg var are $this->var
 *
 *****************************************************************************/
 

//require_once(dirname(__FILE__)."/ss_image.config.php");

class ss_image{
	var $img_original;
	var $img_changed;
	var $img_original_type; //the format of original image. only has meaning when created from file
	
	// 2005.02.05 -start-
	// all "global $cfg" -> "//$global $cfg"
	// $cfg[] replaced by following "real" var
	var $cfg_JPEG_QUALITY = 80;
	var $cfg_USE_GD2 = true;
	var $cfg_USE_COLOR_NAMES = true;
	
	var $trans_rgb = array(); //RGB for transparent color for gifs and PNG
	
	function set_parameter($jpeg_quality=80, $use_gd2=true, $use_color_names=true) {
		$this->cfg_JPEG_QUALITY		= $jpeg_quality;
		$this->cfg_USE_GD2			= $use_gd2;
		$this->cfg_USE_COLOR_NAMES	= $use_color_names;		
	}
	
	
	// a simple workaround in case GD Lib has wrong permissions
	function _tryNullByteFile($where)
	{
		if($fp = @fopen($where, "w+b")) {
			fwrite($fp, '');
			fclose($fp);
			return true;	
		} else {
			return false;
		}
	}
	
	
	
	function ss_image(
	$src,          //source of the image. Currently can be file or string
	// use string if your image comes from the database
	//or is generated by other script
	$src_kind="f" //"f" -- $src is path to file
	//"u"--uploaded file, in this case $src is name
	//of form field <input name='HERE' type='file' ...>
	//"s" -- $src is a string containing valid image stream
	//"s64"--$src is base64 encoded
	){
		//global $_FILES;
		$this->img_original_type='';
		switch(strtoupper(trim($src_kind))){
			case "S64":
			//base 64 encoding -- decode and proceed to create from string
			$src = base64_decode($src);
			case "S":
			//create from string

			$this->img_original = imagecreatefromstring($src);

			break;
			case "U":
			$src=$_FILES[$src]['tmp_name'];

			case "F":
			default:
			//create from file. this is also the default behaviour
			$img_info=getimagesize($src);
			//to prevent divide by zero error in resizing
			$this->img_original_type=$img_info[2];
			switch($img_info[2]){//2nd index contains type information
			case "1"://GIF supported was canceled since GD1.6 and is inspecting
			//to be back in middle of 2004
			$this->img_original = imagecreatefromgif($src);
			
			break;
			
			case "2"://JPEG
			$this->img_original = imagecreatefromjpeg($src);
			break;
			
			case "3"://PNG
			$this->img_original = imagecreatefrompng($src);
			break;
			
			case "15"://WBMP
			$this->img_original = imagecreatefromwbmp($src);
			break;
			
			case "16"://XBM
			$this->img_original = imagecreatefromxbm($src);
			break;
			
			default://well format for which we dont have imagecreatefromXX() function
			//lets try create from GD? most lieklly it will fail,
			//but what to do?

			$this->img_original = imagecreatefromGD2($src);

			break;

			}
			break;
		}
		if(!$this->img_original) {
			return null;
		} else {
			$transparency_color_index = imagecolortransparent($this->img_original);
			if($transparency_color_index>-1){
				$this->trans_rgb = @imagecolorsforindex($this->img_original,$transparency_color_index);

			} else {
				$this->trans_rgb = array();
			}
		}
	}


	function output(

	$where="", //specify here file name where to output, or
	//leave blank to output directly to browser
	$what="c",		// original image or changed imege?
	//"o"-original "c"-changed
	$method="" // how to output?
	//JPG, JPEG, GIF, PNG, XBMP etc,
	//see get_supported_types()
	){
		
		$method = strtoupper(trim($method));
		if(!$method){
			$method=$this->img_original_type;
		}

		$what	= strtoupper(trim($what));
		//what to output? original or changed?
		if($what=="O") {
			$image = $this->img_original;
		} else {
			$image = $this->img_changed;
		}
		
		switch($method){
			case "1":
			case "GIF":
			
			if(!$where){
				header("Content-Type: image/gif");
				imagegif($image);
			}else{
				if(!imagegif($image, $where)) {
					$this->_tryNullByteFile($where);
					imagegif($image, $where);
				}
			}
			break;
			case "3"://PNG
			case "PNG":
			@imagesavealpha($imgage, true);
			if(!$where){
				header("Content-Type: image/png");
				imagepng($image, '', 9);
			} else {
				if(!imagepng($image, $where, 9)) {
					$this->_tryNullByteFile($where);
					imagepng($image, $where, 9);
				}
			}
			break;
			//Note: WBMP support is only available if PHP was compiled against GD-1.8 or later
			case "15"://WBMP
			case "WBMP":
			if(!$where){
				header("Content-Type: image/wbmp");
				imagewbmp($image);
			} else {
				if(!imagewbmp($image, $where)) {
					$this->_tryNullByteFile($where);
					imagewbmp($image, $where);
				}
			}
			break;
			case "16"://XBM
			case "XBM":
			/*
			Warning  {from manual!}
			This function is currently not documented; only the argument list is available.
			Note: This function is only available if PHP is compiled with the bundled version of the GD library.

			*/
			if(!$where){
				header("Content-Type: image/xbm");
				imagexbm($image);
			} else {
				if(!imagexbm($image, $where)) {
					$this->_tryNullByteFile($where);
					imagexbm($image, $where);
				}
			}
			break;
			case "2":
			case "JPEG":
			case "JPG":
			default:
			if(!$where) {
				header("Content-Type: image/jpeg");
				imagejpeg($image, "", $this->cfg_JPEG_QUALITY);
			} else {
				if(!imagejpeg($image, $where, $this->cfg_JPEG_QUALITY)) {
					$this->_tryNullByteFile($where);
					imagejpeg($image, $where, $this->cfg_JPEG_QUALITY);
				}
			}
			break;
		}


	}


	function get_supported_types(){
		$bits = imagetypes();
		$res = ""; // variable for result
		if($bits & IMG_PNG)		$res .= "PNG ";
		if($bits & IMG_GIF)		$res .= "GIF ";
		if($bits & IMG_JPG)		$res .= "JPG ";
		if($bits & IMG_JPEG)	$res .= "JPEG ";
		if($bits & IMG_WBMP)	$res .= "WBMP ";
		if($bits & IMG_XPM)		$res .= "XPM ";
		$res = trim($res);
		return $res;

	}
	function commit(){
		//copy changed image to original so all next transformation will be
		// based on already made ones.
		@imagedestroy($this->img_original);
		$this->img_original = $this->img_changed;
		$this->img_changed=NULL;

	}
	//free memory
	function destroy(){
		@imagedestroy($this->img_original);
		@imagedestroy($this->img_changed);
	}


	function get_w(	$which="o" ){ 
		// return width of: o-original c-changed image
		return trim(strtoupper($which))=="O" ? imagesx($this->img_original) : imagesx($this->img_changed);
	}

	function get_h(	$which="o" ){
		// return height of: o-original c-changed image
		return trim(strtoupper($which))=="O" ? imagesy($this->img_original) : imagesy($this->img_changed);
	}



	//smart imagecreate() function
	function createimage($width, $height){
		
		if($this->trans_rgb) {
			$image = imagecreate($width,$height);

			$trans_clr=imagecolorallocate($image,$this->trans_rgb['red'],$this->trans_rgb['green'],$this->trans_rgb['blue']);
			imagefilledrectangle($image,0,0,$width,$height,$trans_clr);
			imagecolortransparent($image,$trans_clr);
		} else {
			$image = $this->cfg_USE_GD2 ? imagecreatetruecolor($width,$height) : imagecreate($width,$height);
		}
		return $image;
	}

	//this will set transparent color to whatewer color is pixel with given x, y
	function set_transparentcolor($x=0,$y=0){
		$color=imagecolorat($this->img_original,$x,$y);
		return imagecolortransparent($this->img_original,$color);
	}


	function get_color($color,	//color in hex form like FF00FF without leading # or anything like that
	//Just RRGGBB
	//if use_color_names is true you may specify color by its web standard name
	$dest="c" // For which image allocate the color? o-original, c-changed
	//default is C - becaus usually we do something with changed image
	){

		

		if($this->cfg_USE_COLOR_NAMES){
			//translate color name to RGB hex string
			require_once(dirname(__FILE__)."/ss_image.colortohex.php");
			$color=colortohex($color);
		}

		// we are not going to check this
		$r = hexdec("0x".substr($color, 0,2));
		$g = hexdec("0x".substr($color, 2,2));
		$b = hexdec("0x".substr($color, 4,2));

		if(strtoupper(trim($dest))=="O") {
			$img = $this->img_original;
		} else {
			$img = $this->img_changed;
		}
		$c = imagecolorallocate($img, $r, $g, $b);
		return $c;

	}

	############################################################
	#
	#  Here are Image Processing functions
	#  They are independant one from each other so you can delete those which
	#  you are not going to use to save space
	#


	#resizer function
	function set_size(	$desired_width,		//new width
	$desired_height,		//new height
	//if one of these parameters is set to * that means that we dont care
	//about mode and result image will be set to fit other paramter
	// example if desired_width=="*" and _height="108" -- result image will
	// be 108 of height no matter which will be the width, W to H ratio
	// will remain
	// in strange case when both of them == "*" then we just copy the image

	$mode="-"	//how to resize?
	//"e" or "0"  -- exactly to given dimensions,
	// "0" is here for compatibility with ImageResizer 5.x
	//in this case geometrical distortions may occure.
	//"+"- resize to cover rectangle with given W & H
	//"-"- resize to fit into rectangle with given W & H
	//in last two cases no geometrical distortions will occure
	//new in 6.3
	// "--" -- resize to fit into rectangle, but only if the image is larger than it.
	//so if image is laready smaller it will not resize
	// "++" -- resize to cover the rectangle, but only if the image is smaller than it.
	//if image is already larger than given rectangle by width _and_ height -- no resize.

	){
		
		//prepare parameteres
		$res = false;
		$new_img = NULL;
		$mode=strtoupper(trim($mode));
		$desired_width  = trim($desired_width);
		$desired_height = trim($desired_height);

		$org_w = $this->get_w();
		$org_h = $this->get_h();


		if( $mode=="--" ){
			if (($org_w <= $desired_width ) and ($org_h <= $desired_height ) ){
				// image is already smaller than the given region do not resize.

				$desired_width="*";
				$desired_height="*";


			} else {
				$mode ="-";
			}

		}


		if( $mode=="++" ){
			if (($org_w >= $desired_width ) and ($org_h >= $desired_height ) ){
				// image is already larger than the given region do not resize.

				$desired_width="*";
				$desired_height="*";


			} else {
				$mode ="+";
			}

		}


		//calculate new sizes:
		if($desired_width=="*" and $desired_height=="*"){
			//no resizing just copy parameters:
			$this->img_changed = $this->createimage($this->get_w(), $this->get_h());
			return imagecopy($this->img_changed, $this->img_original, 0, 0, 0, 0, $this->get_w(), $this->get_h());

		} else {

			if(!$org_h) {
				$org_h=1; // prevent divide by zero
			}

			$ratio = $org_w / $org_h;


			if($desired_width=="*"){
				//we dont care what will be new width, image must be of given height
				//and width should be according to the ration
				$desired_height = (int)$desired_height;
				$new_w = $desired_height*$ratio;
				$new_h = $desired_height;
			}elseif($desired_height=="*"){
				//we dont care what will be new height, image must be of given width
				//and height should be according to the ration

				$desired_width  = (int)$desired_width;
				$new_w = $desired_width;
				$new_h = $desired_width/$ratio;
			}else{
				// the desired width and height are given as well as resizing mode
				// lets calculate new width and height..
				$desired_width  = (int)$desired_width;
				$desired_height = (int)$desired_height;
				if(!$desired_width)	$desired_width=1;
				if(!$desired_height)$desired_height=1;

				switch($mode){
					case "0":// compatibility with hft_image 5.x series
					case "E"://resize to fit exactly
					$new_w=$desired_width;
					$new_h=$desired_height;
					break;
					case "+"://overlap given region
					//suppose $new_w will be $desired_width
					$new_h = $desired_width / $ratio;
					if($new_h>=$desired_height) $new_w=$desired_width;
					else{//wrong idea.. right idea was new_h=$desired_height
					$new_w=$ratio*$desired_height;
					$new_h=$desired_height;
					}
					break;
					case "-": //fit into given region
					default:
					//suppose $new_w will be $desired_width
					$new_h = $desired_width / $ratio;
					if($new_h<=$desired_height) $new_w=$desired_width;
					else{//wrong idea.. right idea was new_h=$desired_height
					$new_w=$ratio*$desired_height;
					$new_h=$desired_height;
					}

					break;
				}
			}
			$new_img = $this->createimage($new_w,$new_h);

			if($this->cfg_USE_GD2){
				//use GD2 -- better quality, but not all server have proper installation of it.


				$res= imagecopyresampled(

				$new_img, // destination
				$this->img_original, //source
				0,0,// destination coords
				0,0,//source coords
				$new_w,//new width
				$new_h,//new height
				$org_w,
				$org_h
				);


			} else {
				//worse quality but better compatibility


				$res = imagecopyresized(
				$new_img, // destination
				$this->img_original, //source
				0,0,// destination coords
				0,0,//source coords
				$new_w,//new width
				$new_h,//new height
				$org_w,
				$org_h
				);

			}
		}

		if($res){
			$this->img_changed = $new_img;
		}
		return $res;

	}




	function rotate(
	$a, //angle in degrees 0-359,999..99
	$bkcolor="#FFFFFF" // background color for zone not covered with rotation
	// in html format 'RRGGBB'	-- Hex digits like 'FF0000' - red
	// 'FFFFFF' -- white etc or use word description like red green white
	// blue black .. see below
	){
		
		//process color

		$c=$this->get_color($bkcolor,"o");

		$this->img_changed = imagerotate($this->img_original, $a, $c);

	}


	function gray(){ //this will convert image to B/W
	@imagedestroy($this->img_changed);


	$this->img_changed=$this->createimage($this->get_w(), $this->get_h()); //not true color!!
	//it won't produce grayscale!!

	//allocate gray scale
	for ($c = 0; $c < 256; $c++) {
		ImageColorAllocate($this->img_changed, $c,$c,$c);
	}


	imagecopymerge(	$this->img_changed,
	$this->img_original,
	0,0,//destination x y
	0,0,//source x y
	$this->get_w(), //source sizes
	$this->get_h(),
	100//no alpha -- 100% copy
	);

	}



	function watermark(
	$src,		// source of watermark can be file, string, base64 encoded string
	// see create()
	$transparency=75, // how visible should be watermark? 0-100
	//(invisible-fullvisible)

	$x=0,
	$y=0, //where to place the watermark -- x,y are its top-left corner


	$makegray=false, // should watermark be grayed?

	//rare used params:
	$src_kind="f", // same as in create()

	$desired_w="*",//watermark can be resized
	$desired_h="*" // values same as in set_Size()

	){

		$img_watermark = new ss_image($src, $src_kind);
		if(trim($desired_w) != "*" or trim($desired_h) !="*" ){
			$img_watermark->set_size($desired_w, $desired_h);
			$img_watermark->commit();
		}

		@imagedestroy($this->img_changed);
		$this->img_changed= $this->createimage($this->get_w(),$this->get_h());
		imagecopy($this->img_changed,$this->img_original,0,0,0,0,$this->get_w(),$this->get_h());


		imagealphablending($this->img_changed, true);
		imagealphablending($img_watermark->img_original, true);

		if($makegray){
			$img_watermark->gray();
			$img_watermark->commit();//save changes
		}

		$img_watermark->set_transparentcolor();

		$res= imagecopymerge(	$this->img_changed,
		$img_watermark->img_original,
		$x,$y,//destination x y
		0,0,//source x y
		$img_watermark->get_w(), //source sizes
		$img_watermark->get_h(),
		$transparency
		);



		$img_watermark->destroy();

		return $res;



	}

	//crop() will take rectangular part of the image and erase everything outside it.
	//the rectangle is specified by $x,$y and has sizes $width,$height
	//if $delprev==true result image will have size $width by $height otherwise the rectangular portion
	// of the original image will be copied over the existing "changed image"
	//for example if you have resized the original image -- resized copy is stored in $this->img_changed resource variable
	//
	function crop($x, $y,		//coordinates of left top corner of the rectangle
	$width, $height	//sizes of the rectangle
	){

		//lets change the height width of the specified crop region if it is out of original image bounds
		$org_w = $this->get_w();
		$org_h = $this->get_h();

		if($x+$width>$org_w)$width=$org_w-$x;
		if($y+$height>$org_h)$height=$org_h-$y;

		@ImageDestroy($this->img_changed);
		$this->img_changed= $this->createimage($width,$height);

		return imagecopy($this->img_changed, $this->img_original, 0,0,$x,$y, $width, $height);

	}

	//write a text over the image
	function text($text, //what to write?
	$x=0,$y=0, //where? x,y=upper left corner of the text rectangle
	$color="000000",//color -- HTML syntax here like FF0000- red, or if "use_color_names"  config variable is true
	//then you can use standard color names like blue, white, etc
	$font=3,//either number or ttf file name. Number 1-5 are built-in fonts
	//you can create and load your own font, see PHP manual for this, imageloadfont() will return you
	//an identifier which you can put here
	//if you will specify here True Type font file name here we will use it
	$size=12.0,//TTF font size, only used with TTF fonts
	$angle=0//angle of writting -0 left to right, 90 bottom to top 180- right to left etc
	//only used with TTF fonts
	){
		//prepare changed image
		@imagedestroy($this->img_changed);
		$this->img_changed= $this->createimage($this->get_w(),$this->get_h());

		imagecopy($this->img_changed,$this->img_original,0,0,0,0,$this->get_w(),$this->get_h());
		$color=$this->get_color($color);

		//which writting method to use? TTF or standard?
		if(intval($font)){
			$font=(int)$font;
			return imagestring($this->img_changed,$font,$x,$y,$text,$color);
		}else{
			//this is TTF
			imagettftext ( $this->img_changed, $size, $angle, $x, $y, $color, $font, $text);
		}

	}

	function shadow(
	$dx=8,				//ofset by x
	$dy=8,				//ofset by y
	$transparency=75, // how transparent should be shaddow 0..100 0-grayed mirror of the image 100-invisible, transparent
	$bkcolor="FFFFFF" // color for background for uncovered zones, if CFG allows you may use word names like white yellow etc
	){

		$this->gray();
		$img_gray = $this->img_changed;


		$new_w =$this->get_w()+abs($dx);
		$new_h=$this->get_h()+abs($dy);
		$this->img_changed= $this->createimage($new_w,$new_h);

		$color= $this->get_color($bkcolor);

		imagefilledrectangle($this->img_changed, 0,0, $new_w,$new_h, $color);

		if($dx<0){
			$org_x=-$dx;
			$shd_x = 0;
		}else{
			$org_x=0;
			$shd_x = $dx;
		}
		if($dy<0){
			$org_y=-$dy;
			$shd_y = 0;
		}else{
			$org_y=0;
			$shd_y = $dy;
		}

		imagealphablending($this->img_changed, true);


		//the shadow
		imagecopymerge($this->img_changed,$img_gray,$shd_x,$shd_y,0,0,$this->get_w(),$this->get_h(),$transparency);
		imagedestroy($img_gray);

		//the original
		imagecopy($this->img_changed,$this->img_original,$org_x,$org_y,0,0,$this->get_w(),$this->get_h());


	}

	//this function will make image looking sharper. It is especially useful for thumbnails
	function unsharp($amount=80, $radius=0.5, $threshold=3){
		require_once(dirname(__FILE__)."/ss_image.unsharp.php");
		return $this->img_changed = UnsharpMask($this->img_original, $amount, $radius, $threshold);
	}


	function mask(
	$src,		// source of watermark can be file, string, base64 encoded string
	// see create()
	$transparency=100, // how visible should be mask? 0-100
	//(invisible-fullvisible)

	$transparent_x=0,
	$transparent_y=0, //coordinates of pixel which color will be considered to be "transparent"


	//rare used params:
	$src_kind="f" // same as in create()

	){

		$img_mask = new ss_image($src, $src_kind);

		@imagedestroy($this->img_changed);
		$this->img_changed= $this->createimage($this->get_w(),$this->get_h());
		imagecopy($this->img_changed,$this->img_original,0,0,0,0,$this->get_w(),$this->get_h());


		imagealphablending($this->img_changed, true);
		imagealphablending($img_mask->img_original, true);


		$img_mask->set_transparentcolor($transparent_x,$transparent_y);

		$res= imagecopymerge(	$this->img_changed,
		$img_mask->img_original,
		$x,$y,//destination x y
		0,0,//source x y
		$img_mask->get_w(), //source sizes
		$img_mask->get_h(),
		$transparency
		);

		$img_mask->destroy();

		return $res;

	}

}

//class--------------------------------------------------------------------------------------------------------------------

?>