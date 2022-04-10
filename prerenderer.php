<?php
include(dirname(__FILE__).'/functions.php');

$text='';
if(isset($_REQUEST['t'])) {
	$text=$_REQUEST['t'].'';
}
if(empty($text)) {
	header('Content-Type: text/html; charset=utf-8');
	echo '<!DOCTYPE html>';
	echo '<html>';
	echo '<head>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
	echo '<title>Medarot 3 Text Pre-renderer</title>';
	echo '</head>';
	echo '<body>';
	echo '<form action="" method="get">';
	echo '<input name="d" type="hidden" value="1">';
	echo '<p><textarea name="t" cols="50" rows="5"></textarea></p>';
	echo '<p><select name="f">';
	echo '<option value="0" selected>Normal</option>';
	echo '<option value="1">Narrow</option>';
	echo '<option value="2">Bold</option>';
	echo '<option value="3">Robotic</option>';
	echo '<option value="4">Robotic Bold</option>';
	echo '</select></p>';
	echo '<p><input type="submit" value="Download"></p>';
	echo '</form>';
	echo '</body>';
	echo '</html>';
} else {
	// Normalise linebreaks.
	
	$text=str_replace("\r\n","\n",$text);
	$text=str_replace("\r","\n",$text);
	
	$basedir=dirname(__FILE__);
	$cachedir=$basedir.'/cache';
	if(!file_exists($cachedir)) {
		mkdir($cachedir);
	}
	
	$charwidthtable=get_char_width_table();
	$fontnum=0;
	if(isset($_REQUEST['f'])) {
		$fontnum=trim($_REQUEST['f'].'');
		$fontnum=clean_fontnum($fontnum);
	}
	$font=load_vwf_font($fontnum,$cachedir,$basedir);
	$lines=explode("\n",$text);
	$longestlinelength=8;
	
	$dl=0;
	if(isset($_REQUEST['d'])) {
		$dl=trim($_REQUEST['d'].'');
		$dl=intval($dl,10);
	}
	$dl=($dl>0?true:false);
	
	$i=0;
	$c=strlen($text);
	$linestart=0;
	$currentline=0;
	
	foreach($lines as $linenum => $linetext) {
		
		// Replace ASCII control characters and tabs with ?.
		
		$linetext=preg_replace('/[\x00-\x1F\x7F]/','?',$linetext);
		
		// Re-encode special characters.
		
		$linetext=reenc_special_chars($linetext);
		
		// Convert any remaining UTF8 characters to ?.
		
		$linetext=mb_convert_encoding($linetext,'ASCII','UTF8');
		
		// Make damn sure there are no stragglers in the $80 to $FF range.
		
		$linetext=preg_replace('/[\x80-\xFF]/','?',$linetext);
		
		// Store modified text.
		
		$lines[$linenum]=$linetext;
		
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
				
				$linelength+=$charwidthtable[$fontnum][$charcode]+1;
			}
			$i++;
		}
		if($linelength>$longestlinelength) {
			$longestlinelength=$linelength;
		}
	}
	
	// Calculate the dimensions of our canvas.
	
	$canvaswidth=($longestlinelength-($longestlinelength%8))+8;
	$canvasheight=count($lines)*8;
	if($canvasheight<8) {
		$canvasheight=8;
	}
	
	// Create our canvas for compositing images.
	
	$im=imagecreatetruecolor($canvaswidth,$canvasheight);
	
	// Make the background white.
	
	imagealphablending($im,false);
	$white=imagecolorallocate($im,255,255,255);
	imagefill($im,0,0,$white);
	imagealphablending($im,true);
	
	// Draw text.
	
	foreach($lines as $linenum => $linetext) {
		$i=0;
		$c=strlen($linetext);
		$linelength=0;
		while($i<$c) {
			$char=$linetext[$i];
			$charcode=ord($char);
			if($charcode<0x80) {
				
				// Get font character width.
				
				$charwidth=$charwidthtable[$fontnum][$charcode]+1;
				
				// Calculate font character x and y positions divided by 8. 
				
				$charxindex=$charcode%0x10;
				$charyindex=round(($charcode-$charxindex)/0x10);
				
				// Draw font character to canvas image.
				
				imagecopy($im,$font,$linelength,($linenum*8),$charxindex*8,$charyindex*8,$charwidth,8);
				$linelength+=$charwidth;
			}
			$i++;
		}
	}
	
	// Font is no longer needed. Remove it from memory.
	
	imagedestroy($font);
	
	// Output final image.
	
	header('Content-Type: image/png');
	if($dl) {
		header('Content-Disposition: attachment; filename=RenderedText.png');
	}
	header('X-Content-Type-Options: nosniff');
	imagesavealpha($im,false);
	imagetruecolortopalette($im,false,2);
	//imagecolortransparent($im,imagecolorat($im,$canvaswidth-1,0));
	imagepng($im,null,7);
	imagedestroy($im);
}
?>