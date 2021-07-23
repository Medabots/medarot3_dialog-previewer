<?php
$text='';
if(isset($_REQUEST['t'])) {
	$text=$_REQUEST['t'].'';
}

// Normalise linebreaks.

$text=str_replace("\r\n","\n",$text);
$text=str_replace("\r","\n",$text);

// Check the global cache.

$basedir=dirname(__FILE__);
$cachedir=$basedir.'/cache';
if(!file_exists($cachedir)) {
	mkdir($cachedir);
}

$texthash=preg_replace('/[^0-9a-z]/','',strtolower(hash('sha256',$text)));
if(strlen($texthash)<64) {
	$texthash=str_pad($texthash,64,'0',STR_PAD_RIGHT);
}
if(strlen($texthash)>64) {
	$texthash=substr($texthash,0,64);
}

$globalcachefilename=$cachedir.'/_'.$texthash.'.png';

if(file_exists($globalcachefilename)) {
	header('Content-Type: image/png');
	touch($globalcachefilename);
	readfile($globalcachefilename);
} else {
	$charwidthtable=array(
		array(
			2, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			2, 1, 3, 5, 4, 5, 5, 2, 2, 2, 3, 5, 2, 4, 1, 4,
			4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 1, 2, 4, 4, 4, 5,
			5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5,
			5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 2, 4, 2, 3, 4,
			2, 5, 4, 4, 4, 4, 4, 4, 4, 1, 2, 4, 1, 5, 4, 4,
			4, 4, 4, 4, 4, 4, 4, 5, 4, 4, 4, 3, 1, 3, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7
		),
		array(
			2, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 6, 5,
			2, 1, 3, 5, 4, 5, 5, 2, 2, 2, 3, 5, 2, 4, 1, 4,
			4, 2, 4, 4, 4, 4, 4, 4, 4, 4, 1, 2, 4, 4, 4, 4,
			5, 4, 4, 4, 4, 4, 4, 4, 4, 3, 4, 4, 4, 5, 4, 4,
			4, 4, 4, 4, 5, 4, 5, 5, 5, 5, 4, 2, 4, 2, 3, 4,
			2, 3, 3, 3, 3, 3, 2, 3, 3, 1, 2, 3, 1, 5, 3, 3,
			3, 3, 3, 3, 3, 3, 4, 5, 4, 3, 3, 3, 1, 3, 6, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7
		),
		array(
			2, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			2, 2, 3, 5, 5, 5, 6, 2, 3, 3, 3, 5, 2, 4, 1, 4,
			5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 1, 2, 4, 4, 4, 6,
			6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6,
			6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 3, 4, 3, 3, 4,
			2, 6, 5, 5, 5, 5, 4, 5, 5, 2, 3, 5, 2, 6, 5, 5,
			5, 5, 5, 5, 5, 5, 5, 6, 6, 5, 4, 4, 2, 4, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7
		),
		array(
			2, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			2, 1, 3, 5, 4, 5, 5, 1, 2, 2, 3, 5, 2, 4, 1, 4,
			4, 2, 4, 4, 4, 4, 4, 4, 4, 4, 1, 2, 4, 4, 4, 5,
			5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5,
			5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 2, 4, 2, 3, 4,
			2, 4, 4, 4, 4, 4, 4, 4, 4, 1, 2, 4, 1, 5, 4, 4,
			4, 4, 4, 4, 4, 4, 4, 5, 5, 4, 4, 3, 1, 3, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7
		),
		array(
			2, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			2, 2, 3, 5, 5, 5, 6, 1, 3, 3, 3, 5, 2, 4, 1, 4,
			5, 3, 5, 5, 5, 5, 5, 5, 5, 5, 1, 2, 4, 4, 4, 6,
			6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6,
			6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 3, 4, 3, 3, 4,
			2, 5, 5, 5, 5, 5, 5, 5, 5, 2, 3, 5, 2, 6, 5, 5,
			5, 5, 5, 5, 5, 5, 5, 6, 6, 5, 5, 4, 2, 4, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7,
			7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7
		)
	);
	$fonts=array(
		false,
		false,
		false,
		false,
		false
	);
	$fontsloaded=array(
		false,
		false,
		false,
		false,
		false
	);
	$numberoffonts=5;
	
	
	// Disregard everything after an end code.
	
	$text=preg_replace('/\<\*[0-9A-Fa-f][0-9A-Fa-f]\>/','<*00>',$text);
	$text=explode("<*00>",$text);
	$text=$text[0];
	
	// Parse linebreaks and pagebreaks into pages and lines.
	
	$pages=array(array('',''));
	$indicatorsbypage=array(true);
	$linelengthsbypage=array(array(0,0));
	$portraitbypage=array('');
	$errorbypage=array(array(false,false));
	
	$text=str_ireplace('<$CD>','<CD>',$text);
	$text=str_ireplace('<$CF>','<CF>',$text);
	$text=str_ireplace('<$D1>','<D1>',$text);
	$text=str_ireplace('<$D3>','<D3>',$text);
	$text=str_replace('<Cd>','<CD>',$text);
	$text=str_replace('<Cf>','<CF>',$text);
	$text=str_replace("\n\n",'<CF>',$text);
	$text=str_replace("\n",'<D3>',$text);
	
	$i=0;
	$c=strlen($text);
	$linestart=0;
	$currentpage=0;
	$currentline=0;
	
	while($i<$c) {
		$pos=strpos($text,'<',$i);
		$newpage=false;
		$endline=false;
		$indicator=false;
		if(!is_bool($pos)) {
			$replacecodemaybe=substr($text,$pos,4);
			$endline=false;
			switch($replacecodemaybe) {
				case '<D3>':
					$endline=true;
					if($currentline>0) {
						$indicator=true;
						$newpage=true;
					}
					break;
				case '<CF>':
					$indicator=true;
				case '<D1>':
					$newpage=true;
				case '<CD>':
					$endline=true;
					break;
			}
		} else {
			$pos=$c;
			$endline=true;
		}
		if($endline) {
			if($currentline<2) {
				$pages[$currentpage][$currentline]=substr($text,$linestart,$pos-$linestart);
				$linelengthsbypage[$currentpage][$currentline]=0;
				$currentline++;
			} else {
				$errorbypage[$currentpage][1]=true;
			}
			if($newpage) {
				if($currentpage<10) {
					$indicatorsbypage[$currentpage]=$indicator;
					$currentpage++;
					$pages[$currentpage]=array('','');
					$linelengthsbypage[$currentpage]=array(0,0);
					$indicatorsbypage[$currentpage]=true;
					$portraitbypage[$currentpage]='';
					$errorbypage[$currentpage]=array(false,false);
					$currentline=0;
				} else {
					
					// Discard everything after the first 10 pages.
					
					$indicatorsbypage[$currentpage]=$indicator;
					$i=$c;
				}
			}
			$i=$pos+4;
			$linestart=$i;
		} else {
			$i=$pos+1;
		}
	}
	
	$font=0;
	$fontsloaded[$font]=true;
	$fontforcounting=$font;
	
	// Set default portrait.
	
	$portrait='CC,FF,FF';
	$portraitbypage[0]=$portrait;
	$currentportrait=$portrait;
	
	// Parse lines.
	
	$canvascachefilename='';
	$numpages=count($pages);
	$pagenum=0;
	while($pagenum<$numpages) {
		$lines=$pages[$pagenum];
		foreach($lines as $linenum => $linetext) {
			
			// Replace ASCII control characters and tabs with ?.
			
			$linetext=preg_replace('/[\x00-\x1F\x7F]/','?',$linetext);
			
			// Remove speed control code.
			
			$linetext=preg_replace('/\<S[0-9A-Fa-f][0-9A-Fa-f]\>/','',$linetext);
			
			// Re-encode note character.
			
			$note=chr(0xF0).chr(0x9D).chr(0x85).chr(0xA0);
			$notefinal=chr(0x1E);
			$linetext=str_replace($note,$notefinal,$linetext);
			
			// Re-encode yen character.
			
			$yen=chr(0xC2).chr(0xA5);
			$yenfinal=chr(0x1F);
			$linetext=str_replace($yen,$yenfinal,$linetext);
			
			// Convert any remaining UTF8 characters to ?.
			
			$linetext=mb_convert_encoding($linetext,'ASCII','UTF8');
			
			// Make damn sure there are no stragglers in the $80 to $FF range.
			
			$linetext=preg_replace('/[\x80-\xFF]/','?',$linetext);
			
			// Process literal byte codes.
			
			$matches=array();
			while(preg_match('/\<\$[0-9A-Fa-f][0-9A-Fa-f]\>/',$linetext,$matches,PREG_OFFSET_CAPTURE)) {
				$match=$matches[0][0];
				$pos=intval($matches[0][1],10);
				$len=strlen($match);
				$byteord=hexdec(substr($match,1,-1));
				$replace=chr($byteord%128);
				$linetext=substr($linetext,0,$pos).$replace.substr($linetext,$pos+$len);
				$matches=array();
			}
			
			// Clean up kanji replace code.
			
			$linetext=preg_replace('/\<K[0-9A-Fa-f][0-9A-Fa-f]\>/','?',$linetext);
			
			// Replace subtext with character $01 repeated 8 times.
			
			$placeholder=chr(0x01);
			$placeholder.=$placeholder;
			$placeholder.=$placeholder;
			$placeholder.=$placeholder;
			$linetext=preg_replace('/\<\&[0-9A-Za-z\_\-]+\>/',$placeholder,$linetext);
			
			// Process all portrait codes.
			
			$matches=array();
			while(preg_match('/\<\@[0-9A-Za-z\s\,]+\>/',$linetext,$matches,PREG_OFFSET_CAPTURE)) {
			
				// For each match in order update the current portrait variable and remove the replace code from the system.
				
				$match=$matches[0][0];
				$pos=intval($matches[0][1],10);
				$len=strlen($match);
				$linetext=substr($linetext,0,$pos).substr($linetext,$pos+$len);
				$portraitbypage[$pagenum]=substr($match,2,-1);
				$matches=array();
			}
			
			// Replace font control codes with single byte placeholders.
			
			$i=0;
			while($i<$numberoffonts) {
				
				// We are assuming that fonts go no higher than 9.
				// Since $80 onwards is not used in our font we can abuse it for single byte placeholders for this control code, for ease of parsing.
				// $80 = <f00>, $81 = <f01>, etc. This is only for the purposes of this script, this isn't used anywhere else.
				
				$placeholder=chr(0x80+$i);
				$linetext=str_replace('<f0'.$i.'>',$placeholder,$linetext);
				$i++;
			}
			
			// Store modified text.
			
			$pages[$pagenum][$linenum]=$linetext;
			
			// Count text length in pixels and note fonts used.
			// Strings can be uses as char arrays in php.
			
			$linelength=0;
			
			$i=0;
			$c=strlen($linetext);
			while($i<$c) {
				$char=$linetext[$i];
				$charcode=ord($char);
				if($charcode<0x80) {
				
					// Add to pixel length total.
					
					$linelength+=$charwidthtable[$fontforcounting][$charcode]+1;
					
				} else {
					
					// A font switching single byte placeholder was encountered.
					// Switch font tables and note that the font file needs to be loaded.
					
					$fontforcounting=$charcode-0x80;
					if($fontforcounting>=$numberoffonts||$fontforcounting<0) {
						$fontforcounting=0;
					}
					$fontsloaded[$fontforcounting]=true;
				}
				$i++;
			}
			
			// Check if line overflows.
			
			if($linelength>137) {
				$errorbypage[$pagenum][$linenum]=true;
			}
		}
		
		// Carry over the portrait from the previous page if not set.
		
		if(!empty($portraitbypage[$pagenum])) {
			$currentportrait=$portraitbypage[$pagenum];
		} else {
			$portraitbypage[$pagenum]=$currentportrait;
		}
		
		// No indicator on the last page.
		
		if($pagenum+1==$numpages) {
			$indicatorsbypage[$pagenum]=false;
		}
		
		// Generate cache filename for later use.
		
		$canvascachefilename.=(1+($indicatorsbypage[$pagenum]?1:0)+($errorbypage[$pagenum][0]?2:0)+($errorbypage[$pagenum][1]?4:0));
		
		$pagenum++;
	}
	
	// Calculate canvas size.
	
	$canvaswidth=160;
	$canvasheight=72*$numpages;
	
	// Check cache.
	
	$canvascachefilename=$cachedir.'/w'.$canvascachefilename.'.png';
	if(file_exists($canvascachefilename)) {
		
		// Load all windows without text or portraits from the cache.
		
		$im=imagecreatefrompng($canvascachefilename);
		imagealphablending($im,true);
		
	} else {
		
		// Create our canvas for compositing images.
		
		$im=imagecreatetruecolor($canvaswidth,$canvasheight);
		
		// Make the background green.
		
		imagealphablending($im,false);
		$green=imagecolorallocate($im,0,255,0);
		imagefill($im,0,0,$green);
		imagealphablending($im,true);
		
		// Set window background colours.
		
		$yellow=imagecolorallocate($im,249,214,83);
		$red=imagecolorallocate($im,255,0,0);
		
		// Preload window.
		
		$windowim=imagecreatefrompng($basedir.'/window/window.png');
		imagealphablending($windowim,true);
		
		// Preload indicator.
		
		$indicatorim=imagecreatefrompng($basedir.'/window/indicator.png');
		imagealphablending($indicatorim,true);
		$indicatorhorizontaloffset=$canvaswidth-16;
			
		// Draw windows and indicators.
		
		$pagenum=0;
		while($pagenum<$numpages) {
			
			// Basic positioning variables.
			
			$windowverticaloffset=(72*$pagenum)+32;
			$indicatorverticaloffset=$windowverticaloffset+24;
			
			// Load window background.
			
			imagefilledrectangle($im,0,$windowverticaloffset,$canvaswidth-1,$windowverticaloffset+19,($errorbypage[$pagenum][0]?$red:$yellow));
			imagefilledrectangle($im,0,$windowverticaloffset+20,$canvaswidth-1,$windowverticaloffset+39,($errorbypage[$pagenum][1]?$red:$yellow));
			
			// Insert window into canvas image.
			
			imagecopy($im,$windowim,0,$windowverticaloffset,0,0,160,40);
			
			// Insert indicator if applicable.
			
			if($indicatorsbypage[$pagenum]) {
				imagecopy($im,$indicatorim,$indicatorhorizontaloffset,$indicatorverticaloffset,0,0,8,8);
			}
			
			$pagenum++;
		}
	
		// Unload window and indicator from memory.
		
		imagedestroy($windowim);
		imagedestroy($indicatorim);
		
		// Save everything drawn up to now into the cache.
		
		imagesavealpha($im,true);
		imagepng($im,$canvascachefilename,7);
	}
	
	// Load required fonts and make font background transparent.
	// This traverses each pixel, checks if it is white, and then if so makes it transparent.
	// Caches for subsequent requests.
	
	foreach($fontsloaded as $fontnum => $fontsloadedtest) {
		if($fontsloadedtest) {
			$fontcachefilename=$cachedir.'/f'.$fontnum.'.png';
			if(file_exists($fontcachefilename)) {
				$fonts[$fontnum]=imagecreatefrompng($fontcachefilename);
				imagealphablending($fonts[$fontnum],true);
			} else {
				$fonts[$fontnum]=imagecreatefrompng($basedir.'/fonts/'.$fontnum.'.png');
				$transparent=imagecolorallocatealpha($fonts[$fontnum],0,0,0,127);
				$width=128;
				$height=64;
				imagealphablending($fonts[$fontnum],false);
				$x=0;
				while($x<$width) {
					$y=0;
					while($y<$height) {
						$rgb = imagecolorat($fonts[$fontnum],$x,$y);
						$r = ($rgb >> 16) & 0xFF;
						$g = ($rgb >> 8) & 0xFF;
						$b = $rgb & 0xFF;
						if($r==255&&$g==255&&$b==255) {
							imagesetpixel($fonts[$fontnum],$x,$y,$transparent);
						}
						$y++;
					}
					$x++;
				}
				imagealphablending($fonts[$fontnum],true);
				imagesavealpha($fonts[$fontnum],true);
				imagepng($fonts[$fontnum],$fontcachefilename,7);
			}
		}
	}
	
	// Render pages.
	
	$texthorizontaloffset=8;
	$textlineheight=16;
	$pagenum=0;
	while($pagenum<$numpages) {
		$lines=$pages[$pagenum];
		$portrait=$portraitbypage[$pagenum];
		
		// Separate portrait data.
	
		list($orientation,$portraitcharacter,$portraitexpression)=explode(',',$portrait.',,');
		
		// Parse orientation.
		// If the orientation isn't LL/LR/RL/RR then assume CC.
		
		$orientation=trim(strtolower($orientation));
		$rightside=false;
		$rightfacing=false;
		$showportrait=true;
		switch($orientation) {
			case 'll':
				break;
			case 'lr':
				$rightfacing=true;
				break;
			case 'rl':
				$rightside=true;
				break;
			case 'rr':
				$rightside=true;
				$rightfacing=true;
				break;
			default:
				$showportrait=false;
				$orientation='cc';
		}
		
		// Parse character index.
		// There are 80 characters. The 81st is for characters specified outside that range.
		
		$portraitcharacter=preg_replace('/[^0-9a-f]/','',strtolower($portraitcharacter));
		if(empty($portraitcharacter)) {
			$portraitcharacter=0;
		} else {
			$portraitcharacter=substr($portraitcharacter,-2);
			$portraitcharacter=hexdec($portraitcharacter);
		}
		$portraitcharacter=$portraitcharacter%256;
		
		if($portraitcharacter>80) {
			$portraitcharacter=80;
		}
		
		// Parse expression index.
		// Each character is expected to have exactly 64 expressions. No more, no less. 
		
		$portraitexpression=preg_replace('/[^0-9a-f]/','',strtolower($portraitexpression));
		if(empty($portraitexpression)) {
			$portraitexpression=0;
		} else {
			$portraitexpression=substr($portraitexpression,-2);
			$portraitexpression=hexdec($portraitexpression);
		}
		$portraitexpression=$portraitexpression%256;
		
		if($portraitexpression>63) {
			$portraitexpression=63;
		}
		
		// Set basic size and positioning variables.
		
		$portraithorizontaloffset=0;
		$portraitverticaloffset=72*$pagenum;
		$textverticaloffset=$portraitverticaloffset+40;
		
		// Load portrait image.
		
		$portraitim=false;
		if($showportrait) {
			
			// Load the portrait image.
			
			$portraitim=imagecreatefrompng('./portraits/'.$portraitcharacter.'/'.$portraitexpression.'.png');
			
			// Change the horizontal position to right-align the portrait if required.
			
			if($rightside) {
				$portraithorizontaloffset=$canvaswidth-32;
			}
			
			// Flip the image if required.
			
			if($rightfacing) {
				imageflip($portraitim,IMG_FLIP_HORIZONTAL);
			}
		}
		
		// Insert portrait into canvas image.
		
		if($showportrait) {
			imagecopy($im,$portraitim,$portraithorizontaloffset,$portraitverticaloffset,0,0,32,32);
			imagedestroy($portraitim);
		}
		
		// Draw text.
		
		foreach($lines as $linenum => $linetext) {
			$i=0;
			$c=strlen($linetext);
			while($i<$c) {
		
				// If the length of the current printed text is 136 then don't draw.
		
				if($linelengthsbypage[$pagenum][$linenum]<136) {
					$char=$linetext[$i];
					$charcode=ord($char);
					if($charcode<0x80) {
						
						// Get font character width.
						// If too long to fit then adjust width to fit remaining space.
						
						$charwidth=$charwidthtable[$font][$charcode]+1;
						if($linelengthsbypage[$pagenum][$linenum]+$charwidth>136) {
							$charwidth=136-$linelengthsbypage[$pagenum][$linenum];
						}
						
						// Calculate font character x and y positions divided by 8. 
						
						$charxindex=$charcode%0x10;
						$charyindex=round(($charcode-$charxindex)/0x10);
						
						// Draw font character to canvas image.
						
						imagecopy($im,$fonts[$font],$linelengthsbypage[$pagenum][$linenum]+$texthorizontaloffset,($linenum*$textlineheight)+$textverticaloffset,$charxindex*8,$charyindex*8,$charwidth,8);
						$linelengthsbypage[$pagenum][$linenum]+=$charwidth;
					} else {
						
						// A font switching single byte placeholder was encountered.
						// Switch fonts.
						
						$font=$charcode-0x80;
						if($font>=$numberoffonts||$font<0) {
							$font=0;
						}
					}
				}
				$i++;
			}
		}
		$pagenum++;
	}
	
	// Fonts are no longer needed. Remove them from memory.
	
	foreach($fontsloaded as $fontnum => $fontsloadedtest) {
		if($fontsloadedtest) {
			imagedestroy($fonts[$fontnum]);
		}
	}
	
	// Make sure the cache is limited to 20000 entries.
	
	$cacheoffset=0;
	$cacheoffsetfilename=$cachedir.'/c.txt';
	if(file_exists($cacheoffsetfilename)) {
		$cacheoffset=intval(file_get_contents($cacheoffsetfilename),10)%20000;
	}
	$cachehashfilename=$cachedir.'/c'.$cacheoffset.'.txt';
	$oldhash='';
	if(file_exists($cachehashfilename)) {
		$oldhash=preg_replace('/[^0-9a-z]/','',strtolower(file_get_contents($cacheoffsetfilename)));
		if(!empty($oldhash)) {
			$oldglobalcachefilename=$cachedir.'/_'.$oldhash.'.png';
			if(file_exists($oldglobalcachefilename)) {
				unlink($oldglobalcachefilename);
			}
		}
	}
	file_put_contents($cacheoffsetfilename,($cacheoffset+1)%20000);
	file_put_contents($cachehashfilename,$texthash);
	
	// Save new cached image.
	
	imagesavealpha($im,true);
	imagetruecolortopalette($im,false,255);
	imagecolortransparent($im,imagecolorat($im,50,0));
	imagepng($im,$globalcachefilename,7);
	imagedestroy($im);
	
	// Output final image.
	
	header('Content-Type: image/png');
	readfile($globalcachefilename);
}
?>