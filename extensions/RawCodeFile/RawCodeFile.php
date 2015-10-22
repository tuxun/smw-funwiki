<?php
if(!defined('MEDIAWIKI')) {
  echo("This is an extension to the MediaWiki package and cannot be run standalone.\n");
  die(-1);
}
else
{
 
 
//Avoid unstubbing $wgParser on setHook() too early on modern (1.12+) MW versions, as per r35980
if ( defined( 'MW_SUPPORTS_PARSERFIRSTCALLINIT' ) ) {
   // $wgHooks['ParserFirstCallInit'][] = 'efRawFile_Setup';
$wgHooks['ParserFirstCallInit'][]='efCodeExtensionInit';
 
 
} else { // Otherwise do things the old fashioned way
  //  $wgExtensionFunctions[] = 'efRawFile_Setup';
  //  $wgExtensionFunctions[] = 'efCodeExtensionInit';
}
 
 //important
//$wgHooks['LanguageGetMagic'][]       = 'efRawFile_Magic';
$wgHooks['RawPageViewBeforeOutput'][] = 'fnRawFile_Strip';
 
/*
function efRawFile_Magic( &$magicWords, $langCode ) {
//    $magicWords['file'] = array( 0, 'file' );
//important
   //  $magicWords['code'] = array( 0, 'code' );
  //  $magicWords['code'] = array( 0, 'code' );
 
//    $magicWords['filelink'] = array( 0, 'filelink' );
//    $magicWords['fileanchor'] = array( 0, 'fileanchor' );
    return true;
}
*/

$wgExtensionCredits['parserhook'][] = array(
	'name' => 'RawCodeFile',
	'path'=>__FILE__,
	'author' => 'Philippe Teuwen, Michael Peeters, Paul Grinberg, Tuxun',
	'version' => '0.1a',
	'url' => 'http://www.mediawiki.org/wiki/Extension:RawCodeFile',
	'description'=>'Allows syntax highlighting using GeSHi<br>'.
		'Downloads a RAW copy of <nowiki><tag>data</tag></nowiki> in a file<br>'.
	    'Useful e.g. to download a script or a patch<br>'.
        'It also allows what is called [http://en.wikipedia.org/wiki/Literate_programming Literate Programming]<br>'
		.'See https://www.mediawiki.org/wiki/Extension:Code,https://www.mediawiki.org/wiki/Extension:RawFile');
 
 
//important
function efRawFile_FileTagRender( $input, $args, $parser, $frame ='') {
        $title = $parser->mTitle;
if(isset($args['title']))
	{
      if( $args['title'] != '' )
{
		$title = Title::newFromText($parser->recursiveTagParse( $args['name'], $frame ));
		//$title = $args['title'];		
		}    
 
 
 
	}
//cf l304
		//$title = Title::newFromText($parser->recursiveTagParse( $args['name'], $frame ));
 
	//We expand templates, so <file> tag cannot be mixed with {{#fileanchor}} anchors
    $link=$title->getFullURL( 'action=raw&templates=expand' );
    if( $args['name'] != '' )
        $link.='&name='.urlencode( $parser->recursiveTagParse( $args['name'], $frame ) );
    if( $args['anchor'] != '' )
        $link.='&anchor='.urlencode( $parser->recursiveTagParse( $args['anchor'], $frame ) );
    if( $args['tag'] != '' )
        $link.='&tag='.urlencode( $parser->recursiveTagParse( $args['tag'], $frame ) );
 
    //return $args['name'].$parser->recursiveTagParse( "[$link $input]", $frame );
    return /*$args['name'].*/$parser->recursiveTagParse( "[$link ".$args['name']."]", $frame );
 
}
 
function fnRawFile_Strip_Error($msg,$out,&$text) {
    $text=$msg;
    if($out != '')
        $text.="\nCandidate match: $out";
    return true;
}
 
function fnRawFile_Strip(&$rawPage, &$text) {
    $filename=$_GET['name'];
    $anchor=$_GET['anchor'];
    // for backward compatibility, accept also URLs with parameter 'file'
    if( $anchor=='' )
        $anchor=$_GET['file'];
    $tag=$_GET['tag'];
    // Either anchor or name must be specified
    if( $filename=='' )
        $filename=$anchor;
    if ( $filename=='' )
        return true;
    // Uncomment the following line to avoid output buffering and gzipping:
     wfResetOutputBuffers();
    header("Content-disposition: attachment;filename={$filename}");
    header("Content-type: application/octet-stream"); 
    header("Content-Transfer-Encoding: binary"); 
    header("Expires: 0");
    header("Pragma: no-cache"); 
    header("Cache-Control: no-store");
    $maskedtext=preg_replace_callback('!<nowiki>.*?</nowiki>!s',
        function($m) { return ereg_replace(".","X",$m[0]); },
        $text);
//echo $maskedtext;
    if (($anchor!='') && preg_match_all('/({{#fileanchor: *'.$anchor.' *}})|(<[^>]+ class *= *"([^"]*\w)?'.$anchor.'(\w[^"]*)?"[^>]*>)/i', $maskedtext, $matches, PREG_OFFSET_CAPTURE))
        $offsets=$matches[0];
    else if (preg_match_all('/{{#file: *'.$anchor.' *}}/i', $maskedtext, $matches, PREG_OFFSET_CAPTURE))
        $offsets=array($matches[0][0]);
    else if (preg_match_all('/<file( [^>]*)? name *= *"'.$filename.'"[^>]*>/i', $maskedtext, $matches, PREG_OFFSET_CAPTURE))
        $offsets=array($matches[0][0]);
   else if (preg_match_all('/<code( [^>]*)? name *= *"'.$filename.'"[^>]*>/i', $maskedtext, $matches, PREG_OFFSET_CAPTURE))
        $offsets=array($matches[0][0]);
  else if (preg_match_all('/<code( [^>]*)? name *="'.$filename, $maskedtext, $matches, PREG_OFFSET_CAPTURE))
{  
      $offsets=array($matches[0][0]);
 }   else {
//var_dump($matches);
        // We didn't find our anchor
        return fnRawFile_Strip_Error("FIRST ERROR - RawFile: anchor not found (anchor=$anchor, name=$filename, tag=$tag)","",$text);
    }
    unset($maskedtext);
    $textorig=$text;
    $text='';
    foreach ($offsets as $offset) {
        $out = substr($textorig, $offset[1]);
        // If no tag specified, we take the first one
        if ($tag == '')
        {
            // With a regex assertion, we can easily ignore 'br' and 'file' tags
            if (!preg_match('/<((?!br\b|code\b)\w+\b)/', $out, $matches))
                return fnRawFile_Strip_Error ("ERROR - RawFile: Can't find opening tag after anchor '$offset[0]' (anchor=$anchor, name=$filename, tag=$tag)",$out,$text);
            $tag=$matches[1];
        }
        // Find the first tag matching $tag, and return enclosed text
        if (!preg_match('/<'.$tag.'( [^>]*)?>\n?(.*?)<\/'.$tag.'>/s', $out, $matches))
            return fnRawFile_Strip_Error ("ERROR - RawFile: no closing '$tag' found after anchor '$offset[0]' (anchor=$anchor, name=$filename, tag=$tag)",$out,$text);
        $text .= $matches[2];
    }
    return true;
}
 
 
 
 
 
function efCodeExtensionInit(Parser&$parser) {


global $magicWords;


//intercepte "code"
  $parser->setHook("code","efCodeExtensionRenderCode");
 $parser->setHook( 'file', 'efRawFile_FileTagRender' );
  //  $parser->setFunctionHook( 'code', 'efRawFile_Render' );
  return true;
}
function efCodeExtensionRenderCode($input,$argv,$parser) {
	$filename="";
  global $wgShowHideDivi,$wgOut;
  // default values
  $language='text';
  $showLineNumbers=true;
  $showDownloadLink=false;
  $source=$input;
  $tabwidth=4;
  foreach($argv as $key=>$value) {
    switch($key) {
      case 'lang':
        $language=$value;
        break;
      case 'linenumbers':
        $showLineNumbers=tr;
        break;
      case 'tabwidth':
        $tabwidth=$value;
        break;
 case 'name':
            $filename=$value;
  case 'download':
 
        $showDownloadLink=true;
 
 
        break;
      case 'fileurl':
        $html=$parser->unstrip($parser->recursiveTagParse($value),$parser->mStripState);
        $i=preg_match('/<a.*?>(.*?)<\/a>/',$html,$matches);
        $url=$matches[1];
        //print("URL is '$url'");
        #$source = "file_get_contents disabled! Contact your wiki admin with questions.";
        $source=file_get_contents($url);
        break;
      default:
        wfDebug(__METHOD__.": Requested '$key ==> $value'\n");
        break;
    }
  }
  if(!defined('GESHI_VERSION')) {
require_once __DIR__."/../geshi-1.0/src/geshi.php";
    // include only once or else wiki dies
  }
  $geshi=new GeSHi($source,$language);
  $error=$geshi->error();
  // die gracefully if errors found
  if($error) {
    return "Code Extension Error: $error";
  }
  //$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
  // always display line numbers
  $geshi->set_tab_width($tabwidth);
  $code=$geshi->parse_code();
  $code_pieces=preg_split('/\<ol/',$code);
  $output='';
  $ol_tag='<ol';

 
 if ($showDownloadLink) {
$output.=efRawFile_FileTagRender( $output,array('tag'=>"code",'name'=>$filename,'anchor'=>$filename),$parser );
//$output.="{{#file: relais_celsius.ino}}\n";
 
 
}
 
 
 
  if($showLineNumbers) {
    /* if not asked to show line numbers, then we should hide them. 
This is the preferred method
     because this allows for a means of a block of code in the 
middle of a numbered list*/
   $output .= "<style type='text/css'>".
"<!-- ol.codelinenumbers { list-style: none; margin-left: 0; padding-left: 0em;} --></style>";
        $ol_tag = "<ol class='codelinenumbers' ";
    
    $output .= $code_pieces[0];
 
  $output.=$ol_tag.$code_pieces[1];
  }
return $output;
}
 
 
} 
//$id=uniqid();
/*$wgOut.=" <script type='text/javascript'>
function popup(id){
var piece=document.getElementById(id);
var codex=piece.innerHTML;
var win3=window.open('', 'code','width=320,height=210,scrollbars=yes');
win3.document.writeln(codex);
 
}
</script>";
*/
 
 
 
//$wgOut.="<a href='tryyujgu.php#koihugy' class='external free' rel='nofollow' onclick='popup(this);'>popup</a>";
 
//parser, name= dans <code>, pagetitre
 
 
 
/*
$ouput.="
 <script type='text/javascript'>
function popup(id){
var piece=document.getElementById(id);
var codex=piece.innerHTML;
var win3=window.open('', 'code','width=320,height=210,scrollbars=yes');
win3.document.writeln(codex);
 
}
</script>
";
*/
 
/*
http://funlab.fr/core/index.php?title=Codeext&action=raw&anchor=relais_celsius.ino
"<a href='http://funlab.fr/core/index.php?title=Codeext&amp;action=raw&amp;anchor=relais_celsius.ino' 
class='external free' rel='nofollow'>http://funlab.fr/core/index.php?title=Codeext&amp;action=raw&amp;anchor=relais_celsius.ino</a>"
 
 
 
*/
  /*
function efRawFile_Render( &$parser, $filename = '', $titleText = '') {
    if( $titleText == '' )
        $title = $parser->mTitle;
    else
        $title = Title::newFromText( $titleText );
    //Don't expand templates or we'll lose our anchors {{#...}}
    return $title->getFullURL( 'action=raw&anchor='.urlencode( $filename ) );
}
 
function efRawFile_Empty( &$parser, $filename = '') {
    return '';
}
 */
 
 
 /*
function efRawFile_Setup() {
    global $wgParser;
//    $wgParser->setFunctionHook( 'file', 'efRawFile_Render' );
//    $wgParser->setFunctionHook( 'filelink', 'efRawFile_Render' );
//    $wgParser->setFunctionHook( 'fileanchor', 'efRawFile_Empty' );
    $wgParser->setHook( 'file', 'efRawFile_FileTagRender' );
    $wgParser->setFunctionHook( 'code', 'efRawFile_Render' );
    return true;
}
*/
  
 
/*
$wgExtensionCredits['other'][]=array('path'=>__FILE__,
'name'=>'Code','version'=>'0.9',
'author'=>'Paul Grinberg',
'url'=>'https://www.mediawiki.org/wiki/Extension:Code',
'description'=>'Allows syntax highlighting using GeSHi');
 
 
 
 
$wgExtensionCredits['parserhook'][] = array(
	'name' => 'RawFile',
	'version' => '0.5.1',
	'author' => 'Philippe Teuwen, Michael Peeters',
	'url' => 'http://www.mediawiki.org/wiki/Extension:RawFile', //'url' => 'http://wiki.yobi.be/wiki/Mediawiki_RawFile',
	'description' => 'Downloads a RAW copy of <nowiki><tag>data</tag></nowiki> in a file<br>'.
	    'Useful e.g. to download a script or a patch<br>'.
        'It also allows what is called [http://en.wikipedia.org/wiki/Literate_programming Literate Programming]');
*/
?>
