<?php
        /**
        * Contribution Credits extension - Adds contribution credits to the footer
        * @version 2.3 - 2012/05/09
        *
        * @link http://www.mediawiki.org/wiki/Extension:Contribution_Credits Documentation
        *
        * @file ContributionCredits.php
        * @ingroup Extensions
        * @package MediaWiki
        * @author Jaime Prilusky
        * @author Al Maghi
        * @author Manuel Wendel
        * @copyright © 2008 Jaime Prilusky
        * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
        *
        * Configuration:
        *       LocalSettings.php => $wgContributionCreditsShowUserSignature = true;
        *                                                       Default: true
        *                                                       true:   shows user specific user signature (if configured and not empty; else just the username)
                                                                false:  shows only the username instead of the user signature
        */
 
        if( !defined( 'MEDIAWIKI' ) ) {
                echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
                die( 1 );
        }
 
        define ('CONTRIBUTIONCREDITS_VERSION','2.3, 2012-05-09');
 
        $wgHooks['OutputPageBeforeHTML'][] = 'addFooter';
 
        $wgExtensionCredits['other'][] = array(
                'name' => 'Contribution Credits',
                'version' => CONTRIBUTIONCREDITS_VERSION,
                'author' => array( 'Jaime Prilusky', 'Al Maghi' ),
                'description' => 'Adds contribution credits to the footer',
                'url' => 'https://www.mediawiki.org/wiki/Extension:Contribution_Credits'
        );
 
        function addFooter (&$articleTitle, &$text) {
                global $wgTitle,$wgOut,$wgRequest;
                global $wgContributionCreditsHeader;
//      -- DIFFERENCE
                global $wgContributionCreditsShowUserSignature;
                if (is_null($wgContributionCreditsShowUserSignature)) {$wgContributionCreditsShowUserSignature = true;}
//      -------------
 
                $NS = $wgTitle->getNamespace();
                $action = $wgRequest->getVal('action');
 
                if (($NS==0 or $NS==1) and ($action != 'edit')) {
                        $dbr = wfGetDB( DB_SLAVE );
                        $page_id = $wgTitle->getArticleID(); $list= '';
 
                        $res = $dbr->select(
//      -- DIFFERENCE
                        //      'revision',
                        //      array('distinct rev_user_text'),
                        //      array("rev_page = $page_id","rev_user >= 1"),
                        //      __METHOD__,
                        //      array('ORDER BY' => 'rev_user_text ASC',));
                        array('revision', 'user', 'user_properties'),
                        array('distinct wk_user.user_id', 'wk_user.user_real_name', 'wk_user.user_name', 'wk_revision.rev_user_text', 'wk_user_properties.up_value AS signature'),
                        array("wk_user.user_name = wk_revision.rev_user_text", "wk_user_properties.up_user = wk_user.user_id", "wk_user_properties.up_property = 'nickname'", "rev_page = $page_id","rev_user >= 1"),
                        __METHOD__,
                        array('ORDER BY' => 'wk_user.user_name ASC',));
//      -------------
                                if( $res && $dbr->numRows( $res ) > 0 ) {
                                        while( $row = $dbr->fetchObject( $res ) ) {
                                                $deletedUser = preg_match("/ZDelete/",$row->rev_user_text); # deleted users are renamed as ZDelete####
                                                if (!$deletedUser) {
//      -- DIFFERENCE
                                                //      $list .= "[[User:".$row->rev_user_text."|".$row->rev_user_text."]], ";
                                                        if($row->signature != "" && $wgContributionCreditsShowUserSignature == true ) {
                                                                $list .= "<p>&raquo; " . $row->signature . "</p>";
                                                        } else {
                                                                $list .= "<p>&raquo; [[User:" .  $row->user_name . "|" . $row->user_name . "]]</p>";
                                                        }
//      -------------
                                                }
                                        }
                                }
                        $dbr->freeResult( $res );
//      -- DIFFERENCE
                //      $list = preg_replace('/\,\s*$/','',$list);
//      -------------
                        $contributorsBlock = '';
                        if (!empty($list)) {
                                if (!$wgContributionCreditsHeader) {$wgContributionCreditsHeader = "==Contributors==\n";}
                                $contributorsBlock = $wgOut->parse("__NOEDITSECTION__\n" . $wgContributionCreditsHeader . $list);
                        }
                        $text = $text."\n<div id=\"ContributionCredits\">$contributorsBlock</div>";
                }
                return true;
        }
