<?php
include(dirname(__FILE__).'/functions.php');

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

if(!file_exists($globalcachefilename)) {
	$charwidthtable=get_char_width_table();
	$fonts=array();
	$fontsloaded=array();
	$numberoffonts=num_vwf_fonts();
	$i=0;
	while($i<$numberoffonts) {
		$fonts[$i]=false;
		$fontsloaded[$i]=false;
		$i++;
	}
	
	// Disregard everything after an end code.
	
	$text=preg_replace('/\<\*[0-9A-Fa-f][0-9A-Fa-f]\>/','<*00>',$text);
	$text=explode("<*00>",$text);
	$text=$text[0];
	
	// Extract linebreaks and pagebreaks.
	
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
	
	$lines=array(array('',0));
	$linenumber=0;
	
	while($i<$c) {
		if(!isset($lines[$linenumber])) {
			$lines[$linenumber]=array('',0);
		}
		$pos=strpos($text,'<',$i);
		$linebreaktype=0;
		if(!is_bool($pos)) {
			$replacecodemaybe=substr($text,$pos,4);
			switch($replacecodemaybe) {
				case '<D3>': // Universal linebreak
					$linebreaktype=4;
					break;
				case '<D1>': // No-Input Pagebreak
					$linebreaktype=3;
					break;
				case '<CF>': // Pagebreak
					$linebreaktype=2;
					break;
				case '<CD>': // Newline
					$linebreaktype=1;
					break;
			}
		} else {
			$pos=$c;
		}
		if($linebreaktype>0) {
			$lines[$linenumber][0].=substr($text,$i,$pos-$i);
			$lines[$linenumber][1]=$linebreaktype;
			$i=$pos+4;
			$linenumber++;
		} else {
			$lines[$linenumber][0].=substr($text,$i,($pos+1)-$i);
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
	$pagenum=0;
	$linenum=0;
	
	$pages=array(array('',''));
	$indicatorsbypage=array(false);
	$linelengthsbypage=array(array(0,0));
	$portraitbypage=array('');
	$errorbypage=array(array(false,false));
	$linestart=0;
	$currentpage=0;
	
	$numlines=count($lines);
	$currentline=0;
	while($currentline<$numlines) {
		if($pagenum<24) {
			$linetext=$lines[$currentline][0];
			
			// Replace ASCII control characters and tabs with ?.
			
			$linetext=preg_replace('/[\x00-\x1F\x7F]/','?',$linetext);
			
			// Remove speed control code.
			
			$linetext=preg_replace('/\<S[0-9A-Fa-f][0-9A-Fa-f]\>/','',$linetext);
	
			// Re-encode special characters.
			
			$linetext=reenc_special_chars($linetext);
			
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
				$currentportrait=substr($match,2,-1);
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
			
			$lines[$currentline][0]=$linetext;
			
			// Count text length in pixels and note fonts used.
			// Strings can be uses as char arrays in php.
			
			$wordlength=0;
			$wordfont=0;
			
			$currentautoline=0;
			$autolines=array(array('',0));
			
			$i=0;
			$c=strlen($linetext);
			while($i<$c) {
				list($word,$wordlength,$wordnumbytes,$fontforward)=count_to_next_space($linetext,$charwidthtable,$fontsloaded,$i,$c,$fontforcounting);
				if($autolines[$currentautoline][1]>0&&$autolines[$currentautoline][1]+$wordlength>137) {
					$currentautoline++;
					$autolines[$currentautoline]=array('',0);
				}
				$autolines[$currentautoline][0].=$word;
				$autolines[$currentautoline][1]+=$wordlength;
				$fontforcounting=$fontforward;
				$i+=$wordnumbytes;
			}
			
			$i=0;
			$c=count($autolines);
			while($i<$c) {
				if(!isset($pages[$pagenum])) {
					$pages[$pagenum]=array('','');
					$indicatorsbypage[$pagenum]=false;
					$linelengthsbypage[$pagenum]=array(0,0);
					$portraitbypage[$pagenum]='';
					$errorbypage[$pagenum]=array(false,false);
				}
				
				list($linetext,$linelength)=$autolines[$i];
				$pages[$pagenum][$linenum]=$linetext;
				
				// Check line length (in case a single word is over 137 pixels in width).
				
				//$linelengthsbypage[$pagenum][$linenum]=$linelength;
				if($linelength>137) {
					$errorbypage[$pagenum][$linenum]=true;
				}
				
				// Parse line breaks.
				
				if($i==$c-1) {
					$linebreaktype=$lines[$currentline][1];
				} else {
					$linebreaktype=4;
				}
				
				// Set portrait for page.
				
				if(empty($portraitbypage[$pagenum])) {
					$portraitbypage[$pagenum]=$currentportrait;
				}
				
				// Determine the behaviour of linebreaks.
				
				$newpage=false;
				$discardcode=false;
				$indicator=false;
				
				switch($linebreaktype) {
					case 4:
						if($linenum>0) {
							$indicator=true;
							$newpage=true;
						}
						break;
					case 3:
						if($linenum<1&&empty($linetext)) {
							$discardcode=true;
						} else {
							$newpage=true;
						}
						break;
					case 2:
						$indicator=true;
						$newpage=true;
						break;
					case 1:
						if($linenum>0) {
							$discardcode=true;
							$errorbypage[$pagenum][$linenum]=true;
						}
						break;
				}
				if(!$discardcode) {
					if($newpage||$linenum>0) {
						$indicatorsbypage[$pagenum]=$indicator;
						$pagenum++;
						$linenum=0;
					} else if($linenum<1) {
						$linenum=1;
					}
				}
				
				$i++;
			}
		}
		$currentline++;
	}
	$numpages=count($pages);
	$pagenum=0;
	while($pagenum<$numpages) {
		
		// Generate cache filename for later use.
		
		$canvascachefilename.=(1+($indicatorsbypage[$pagenum]?1:0)+($errorbypage[$pagenum][0]?2:0)+($errorbypage[$pagenum][1]?4:0));
		$pagenum++;
	}
	
	// Calculate canvas size.
	
	$canvaswidth=160;
	$canvasheight=72*$numpages;
	
	// Check cache if 3 pages or less.
	
	$hascache=false;
	$canvascachefilenamefull='';
	if(strlen($canvascachefilename)<=3) {
		$canvascachefilenamefull=$cachedir.'/w'.$canvascachefilename.'.png';
		if(file_exists($canvascachefilenamefull)) {
			$hascache=true;
		}
	}
	if($hascache) {
		
		// Load all windows without text or portraits from the cache.
		
		$im=imagecreatefrompng($canvascachefilenamefull);
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
		
		// Save everything drawn up to now into the cache if 3 pages or less.
		
		imagesavealpha($im,true);
		if(!empty($canvascachefilenamefull)) {
			imagepng($im,$canvascachefilenamefull,7);
		}
	}
	
	// Load required fonts and make font background transparent.
	// This traverses each pixel, checks if it is white, and then if so makes it transparent.
	// Caches for subsequent requests.
	
	foreach($fontsloaded as $fontnum => $fontsloadedtest) {
		if($fontsloadedtest) {
			$fonts[$fontnum]=load_vwf_font($fontnum,$cachedir,$basedir);
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
}
	
// Output final image.
	
header('Content-Type: image/png');
header('X-Content-Type-Options: nosniff');
readfile($globalcachefilename);
?>