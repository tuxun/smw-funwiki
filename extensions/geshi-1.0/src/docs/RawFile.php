<?php

if (defined('MEDIAWIKI')) {

//Avoid unstubbing $wgParser on setHook() too early on modern (1.12+) MW versions, as per r35980
if ( defined( 'MW_SUPPORTS_PARSERFIRSTCALLINIT' ) ) {
    $wgHooks['ParserFirstCallInit'][] = 'efRawFile_Setup';
} else { // Otherwise do things the old fashioned way
    $wgExtensionFunctions[] = 'efRawFile_Setup';
}
$wgHooks['LanguageGetMagic'][]       = 'efRawFile_Magic';
$wgHooks['RawPageViewBeforeOutput'][] = 'fnRawFile_Strip';

function efRawFile_Setup() {
    global $wgParser;
    $wgParser->setFunctionHook( 'file', 'efRawFile_Render' );
    $wgParser->setFunctionHook( 'filelink', 'efRawFile_Render' );
    $wgParser->setFunctionHook( 'fileanchor', 'efRawFile_Empty' );
    $wgParser->setHook( 'file', 'efRawFile_FileTagRender' );
    $wgParser->setFunctionHook( 'code', 'efRawFile_Render' );
    return true;
}

function efRawFile_Magic( &$magicWords, $langCode ) {
    $magicWords['file'] = array( 0, 'file' );

    $magicWords['code'] = array( 0, 'code' );

    $magicWords['filelink'] = array( 0, 'filelink' );
    $magicWords['fileanchor'] = array( 0, 'fileanchor' );
    return true;
}

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

function efRawFile_FileTagRender( $input, $args, $parser, $frame ='') {
    if( $args['title'] == '' )
        $title = $parser->mTitle;
    else
        $title = Title::newFromText($parser->recursiveTagParse( $args['name'], $frame ));

	//We expand templates, so <file> tag cannot be mixed with {{#fileanchor}} anchors
    $link=$title->getFullURL( 'action=raw&templates=expand' );
    if( $args['name'] != '' )
        $link.='&name='.urlencode( $parser->recursiveTagParse( $args['name'], $frame ) );
    if( $args['anchor'] != '' )
        $link.='&anchor='.urlencode( $parser->recursiveTagParse( $args['anchor'], $frame ) );
    if( $args['tag'] != '' )
        $link.='&tag='.urlencode( $parser->recursiveTagParse( $args['tag'], $frame ) );

    return $args['name'].$parser->recursiveTagParse( "[$link $input]", $frame );
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
    // wfResetOutputBuffers();
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

$wgExtensionCredits['parserhook'][] = array('name' => 'RawFile',
                           'version' => '0.5.1',
                           'author' => 'Philippe Teuwen, Michael Peeters',
                           'url' => 'http://www.mediawiki.org/wiki/Extension:RawFile',
//                         'url' => 'http://wiki.yobi.be/wiki/Mediawiki_RawFile',
                           'description' => 'Downloads a RAW copy of <nowiki><tag>data</tag></nowiki> in a file<br>'.
                                            'Useful e.g. to download a script or a patch<br>'.
                                            'It also allows what is called [http://en.wikipedia.org/wiki/Literate_programming Literate Programming]');
}

?>
