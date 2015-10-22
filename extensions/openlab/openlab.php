<?php
if(!defined('MEDIAWIKI')) {
  echo("This is an extension to the MediaWiki package and cannot be run standalone.\n");
  die(-1);
}


//$wgExtensionMessagesFiles['openlabMagic'] = dirname(__FILE__) . '/openlab.i18n.magic.php';

$wgExtensionCredits['other'][]=array('path'=>__FILE__,
'name'=>'openlab',
'version'=>'0.1a',
'author'=>'Tuxun',
'url'=>'',
'description'=>'Allows switching logo');

//$wgHooks['LanguageGetMagic'][]       = 'openlab_Magic';

if ( defined( 'MW_SUPPORTS_PARSERFIRSTCALLINIT' ) ) {
    $wgHooks['ParserFirstCallInit'][] = 'openlab';
} else { // Otherwise do things the old fashioned way
    $wgExtensionFunctions[] = 'openlab';
}


function openlab(&$parser ) {
	global $wgParser,$wgLogo;

    global $wgOut, $wgRequest, $wgUser;

	$file="";
	$data="";
	if(isset($_GET['openlab']))
		{
			if (wfReadOnly()) {

				$wgOut->wrapWikiMsg(
					"<div id=\"mw-read-only-warning\">\n$1\n</div>",
					array( 'switchinglogo', wfReadOnlyReason() )
					);
		}
	else 
		{
			
      		  $wgOut->wrapWikiMsg(
					"<div id=\"mw-read-only-warning\">\n$1\n</div>",
					array( 'switchinglogoOK')
					); //le nom du logo actuel
			$logofile = file_get_contents(__DIR__.'/logoname', true);
			$data = explode("\n", $logofile);
			$logofile=$data[0];

			//le nom de l'autre
			$exlogofile= file_get_contents(__DIR__.'/exlogoname', true);
			$data = explode("\n", $exlogofile);
			$exlogofile=$data[0];
			//le nouveau nom du logo actual
			$nulogofile=$logofile.rand(1, 99);
			//le nouveau nom de l'ancien logo
			$nuexlogofile= $exlogofile.rand(1, 99);
			//on range le nouveau nom dde l'ancien logo dans le fichier stockant le nom de l'actuel
			file_put_contents(__DIR__.'/logoname', $nuexlogofile);

			//on range le nouveau nom du logo actuel dans le fichier stockant le nom de l'anicien
			file_put_contents(__DIR__.'/exlogoname',$nulogofile);

				$wgLogo = "http://wiki.funlab.fr/extensions/openlab/$nulogofile.png";







			if (strlen($nuexlogofile) >30)
				{
					$nuexlogofile=substr($nuexlogofile,1,8);
				}


			if (strlen($nulogofile) >30)
				{

					$nulogofile=substr($nulogofile,1,8);
				}



				//on renome le fichier correspondant au logo actuel vers un nom temp
				rename (dirname(__FILE__)."$wgScriptPath/$logofile.png",
				dirname(__FILE__)."$wgScriptPath/temp");

				//on renome le fichier correspondant au logo ancien vers le nouveau nom
				rename (dirname(__FILE__)."$wgScriptPath/$exlogofile.png",
				dirname(__FILE__)."$wgScriptPath/$nuexlogofile.png");

				//on re,nomme le temp vers l'ancien
				rename (dirname(__FILE__)."$wgScriptPath/temp",
				dirname(__FILE__)."$wgScriptPath/$nulogofile.png");


			}
}
		else
			{



				$file = file_get_contents(__DIR__."/logoname", true);
				$data = explode("\n", $file);
				$file=$data[0];
$state=					substr($file,0,6);
if($state=="fermer")$state="fermé";
				$wgOut->wrapWikiMsg(
					"<div id=\"mw-read-only-warning\"><hr>\n$1<hr>\n</div>",
					array(
	strftime("%A %d %B %R").
	": le lab est  $state")	
					);



				$wgLogo = "http://wiki.funlab.fr/extensions/openlab/$file.png";

			}				 return true;
}


