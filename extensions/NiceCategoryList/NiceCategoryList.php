<?php
/*
 *  Nice Category List extension.
 *
 *  This extension implements a new tag, <ncl>, which generates a list of all pages
 *     and sub-categories in a given category.  The list can display multiple levels
 *     of subcategories, and has several options for the display style.
 *
 *  The complete description with examples can be found at
 *     [http://www.mediawiki.org/wiki/Extension:NiceCategoryList2].
 *
 *  Usage:
 *     <ncl [options]>Category:Some Category</ncl>
 *
 *  The following options are available:
 *  ----------- -------   ------------------------------------------------------
 *  option      default   values  : description
 *  ----------- -------   ------------------------------------------------------
 *  headings    head      bullet  :  display category headings as list items
 *                        head    :  display category headings as headings
 *  headstart   2         1..n    :  heading level for top-level categories
 *  showfirst   0         0       :  do not display the first header
 *                        1       :         display the first header
 *  maxdepth    32        1..32   :  maximum category tree depth
 *  style       bullet    bullet  :  show category contents as bullet lists
 *                        compact :  show a more compact listing
 *  showcats    0         0       :  do not display sub-category links
 *                        1       :         display sub-category links
 *  showarts    1         0       :  do not display article links
 *                        1       :         display article links
 *  number      0         0       :  show all articles
 *                        1..n    :  show only number of articles
 *  random      0         0       :  show the first {number} articles
 *                        1       :  show {number} articles randomly
 *  sort        0         0       :  sort articles according to index key
 *                        1       :  sort articles alphabetically
 *  ----------- -------   ------------------------------------------------------
 */
 
  if (!defined('MEDIAWIKI')) die();
 
  define( 'NICE_CATEGORY_LIST_VERSION', '2.2.3' );
  $wgExtensionFunctions[] = 'wfNiceCategoryList';
  $wgExtensionCredits['parserhook'][] = array(
    'path'            => __FILE__,
    'name'            => 'NiceCategoryList',
    'version'         => NICE_CATEGORY_LIST_VERSION,
    'author'          => 'Kichik, Johan the Ghost, [http://www.mediawiki.org/wiki/User:*Surak* *Surak*]',
    'url'             => 'http://www.mediawiki.org/wiki/Extension:NiceCategoryList2',
    'description' => 'Generates a category list showing all subcategories and pages in a category.'
  );
 
# Set global default values.
  $egNiceCategoryListDisableCache = false;
  $egNiceCategoryListHeadStart    = 2;
  $egNiceCategoryListShowFirst    = 0;
  $egNiceCategoryListInstallDir   = "NiceCategoryList";
 
# Set parser hook for <ncl></ncl> Nice Category List extension.
  function wfNiceCategoryList() {
    new NiceCategoryList();
  }
 
/*------------------------------------------------------------------------------
  Class to hold category's title, links list, and categories list.
  ------------------------------------------------------------------------------ */
 
class NiceCategoryList_Links {
  private $title;
  private $articles   = array();
  private $categories = array();
  private $subcats    = array();
 
  public function __construct($title) {
    $this->title = $title;
  }
 
  public function addCategory($title, $links) {
    $this->subcats[] = $title;
    if ($links)
        $this->categories[] = $links;
  }
 
  public function addArticle($title) {
    $this->articles[] = $title;
  }
 
# Get the title of this category.
  public function getTitle() {
    return $this->title;
  }
 
# Get the titles of the sub-categories of this category.
  public function getCatTitles() {
    return $this->subcats;
  }
 
# Get the titles of the articles in this category.
  public function getArtTitles() {
    return $this->articles;
  }
 
# Get the link records of the sub-categories of this category and returns an
# array of NiceCategoryList_Links objects.
  public function getCategories() {
    return $this->categories;
  }
 
# Return true if we have link records for the sub-categories of this category.
  public function hasCatLinks() {
    return count($this->categories) > 0;
  }
 
# Title comparison function
  private function titleCmp($a, $b) {
    return $a->getText() > $b->getText();
  }
 
# NiceCategoryList_Links comparison function
  private function categoryCmp($a, $b) {
    return self::titleCmp($a->title, $b->title);
  }
 
# Sort links and categories alphabetically.
  public function sort() {
    usort($this->articles, array(&$this, "titleCmp"));
    usort($this->categories, array(&$this, "categoryCmp"));
  }
 
}
 
/*------------------------------------------------------------------------------
  Main class.
  ------------------------------------------------------------------------------ */
 
class NiceCategoryList {
 
/*------------------------------------------------------------------------------
  Configuration: Default settings for the category list.
  ------------------------------------------------------------------------------ */
  private $settings = array(
    'maxdepth'  => 32,
    'headings'  => 'head',
    'showfirst' => -1,      # value from options or $wgNiceCategoryListShowFirst
    'headstart' => -1,      # value from options or $wgNiceCategoryListHeadstart
    'style'     => 'bullet',
    'showcats'  => 0,
    'showarts'  => 1,
    'sort'      => 0,
    'number'    => 0,
    'random'    => 0
  );
 
/*------------------------------------------------------------------------------
  Constructor: Set parser hook for <ncl></ncl> Nice Category List extension.
  ------------------------------------------------------------------------------ */
  public function __construct() {
    global $wgParser;
    $wgParser->setHook('ncl', array(&$this, 'hookNcl'));
  }
 
/*------------------------------------------------------------------------------
  Hook: The hook function to handle <ncl></ncl>.
        $category    the tag's text content (between <ncl> and </ncl>);
                     this is the name of the category to index
        $argv        list of tag parameters, can be any of $this->settings[]
        $parser      parser handle
  ------------------------------------------------------------------------------ */
# reference removed by dunpealslyr to enable extension for PHP 5.3
# public function hookNcl($category, $argv, &$parser) {
  public function hookNcl($category, $argv, $parser) {
 
  # get any user-specified parameters, and save them in $this->settings
    $this->settings = array_merge($this->settings, $argv);
 
  # replace variables in $category by calling the parser on it
    $localParser = new Parser();
    $category = $localParser->preprocess($category, $parser->mTitle, $parser->mOptions, false);
 
  # make a title object for the requested category
    $title = Title::newFromText($category);
    if (!$title)
    return '<p>Failed to create title!</p>';
 
  # get the database handle, and get all the subcategory links for the given category
    $dbr =& wfGetDB(DB_SLAVE);
    $catData = $this->searchCategory($dbr, $title, 0);
 
  # generate the category listing
    $output = "<div class='ncl-nicecategorylist'>" . $this->outputCategory($catData) . "</div>";
 
  # suppress TOC
    $output .= "__NOTOC__\n";
 
  # add stylesheet and do not cache
    global $wgOut, $wgScriptPath, $egNiceCategoryListInstallDir;
    $wgOut->addExtensionStyle("{$wgScriptPath}/extensions/{$egNiceCategoryListInstallDir}/NiceCategoryList.css");
    # code taken from [[mw:Extension_talk:NiceCategoryList2#Stale_Content_.2F_Extension_Doesn.27t_refresh]]
    global $egNiceCategoryListDisableCache;
    if ($egNiceCategoryListDisableCache) {
        global $wgVersion;
        $dbr =& wfGetDB( DB_SLAVE );
        # Do not cache this wiki page.
        # for details see http://public.kitware.com/Wiki/User:Barre/MediaWiki/Extensions
        global $wgTitle, $wgDBprefix;
        $ts = mktime();
        $now = gmdate("YmdHis", $ts +120);
        $ns = $wgTitle->getNamespace();
        $ti = $dbr->addQuotes($wgTitle->getDBkey());
        $version = preg_replace("/^([1-9]).([1-9]).*/", "\\1\\2", $wgVersion);
        $sql = "UPDATE $wgDBprefix" . "page SET page_touched='$now' WHERE page_namespace=$ns AND page_title=$ti";
        $dbr->query($sql, __METHOD__);
    }
 
    # convert the listing wikitext into HTML and return it
      $localParser = new Parser();
      $output = $localParser->parse($output, $parser->mTitle, $parser->mOptions);
      return $output->getText();
  }
 
////////////////////////////////////////////////////////////////////////////////
//  Database Access
////////////////////////////////////////////////////////////////////////////////
 
/*
 *  Get all of the direct and indirect members of a given category: i.e. all of
 *  the articles and categories which belong to that category and its children.
 *      $dbr        database handle
 *      $catTitle   title object for the category to search
 *      $depth      current recursion depth: starts at 0
 *      $processed  list of categories that have been searched to date
 *                  (to prevent looping)
 *
 *  Returns NULL if this category has already been searched; otherwise, a
 *  NiceCategoryList_Links object for the given category, containing all the
 *  subcategories and member articles.
 */
    private function searchCategory($dbr, $catTitle, $depth, $processed = array()) {
        // avoid endless recursion by making sure we haven't been here before
        if (in_array($catTitle->getText(), $processed))
            return null;
        $processed[] = $catTitle->getText();
 
        // get all of the category links for this category
        $links = $this->getCategoryLinks($dbr, $catTitle);
 
        // build a list of items which belong to this category
        $cl = new NiceCategoryList_Links($catTitle);
        foreach ($links as $l) {
            // make title for this item
            $title = Title::makeTitle($l->page_namespace, $l->page_title);
 
            if ($title->getNamespace() == NS_CATEGORY) {
                // this item is itself a category: recurse up to mexdepth to
                // find all of its members
                $subLinks = null;
                if ($depth + 1 < $this->settings['maxdepth'])
                    $subLinks = $this->searchCategory($dbr, $title, $depth + 1, $processed);
 
                // record the subcategory name, and its members
                $cl->addCategory($title, $subLinks);
            } else {
                // add regular page to the list
                $cl->addArticle($title);
            }
        }
 
        // sort the item lists, if requested (Thanks, Jej.)
        if ($this->settings['sort'])
            $cl->sort();
 
        return $cl;
    }
 
/*
 *  Get all of the direct members of a given category.
 *      $dbr        database handle
 *      $title      title object for the category to search
 *
 *  Returns an array of objects, each representing a member of the given category.
 *  Each object contains the following fields from the database:
 *      page_title
 *      page_namespace
 *      cl_sortkey
 */
    private function getCategoryLinks($dbr, $title) {
        // query database
        $res = $dbr->select(
            array('page', 'categorylinks'),
            array('page_title', 'page_namespace', 'cl_sortkey'),
            array('cl_from = page_id', 'cl_to' => $title->getDBKey()),
            '',
            array('ORDER BY' => 'cl_sortkey')
        );
        if ($res === false)
                return array();
 
        // convert results list into an array
        $list = array();
        while ($x = $dbr->fetchObject($res))
                $list[] = $x;
 
        // free the results
        $dbr->freeResult($res);
 
        return $list;
    }
 
////////////////////////////////////////////////////////////////////////////////
//  Generate Output
////////////////////////////////////////////////////////////////////////////////
 
    function outputCategory($category, $level = 0) {
        global $wgContLang;
        global $egNiceCategoryListHeadStart, $egNiceCategoryListShowFirst;
 
        // New category
        $output = '';
 
        // Find $headstart, $showfirst and &headings
        if ($this->settings['headstart'] < 0) {
            $headstart = $egNiceCategoryListHeadStart;
        } else {
            $headstart = $this->settings['headstart'];
        }
        if ($this->settings['showfirst'] < 0) {
            $showfirst = $egNiceCategoryListShowFirst;
        } else {
            $showfirst = $this->settings['showfirst'];
        }
        if ($showfirst) {
            $heading = $level + $headstart;
        } else {
            $heading = $level + $headstart - 1;
        }
 
        // Each level is displayed in an own DIV block with a classname of
        // 'ncl-block-{heading}-#'.
        // Each heading block has a classname of 'ncl-heading-#'.
        // If {headings} == 'head', then the heading is a real wiki heading,
        // otherwise, it is a bullet list item.
        $title = $category->getTitle();
        $ctitle = $title->getPrefixedText();
        $title   = $wgContLang->convert($title->getText());
        $link    = "[[:" . $ctitle . "|" . $title . "]]";
        if ($showfirst || $level > 0) {
            if($heading > 6) {
                //heading should be increased, but the header tag can't be more than 6.
                $heading = 6;
            }
            if ($this->settings['headings'] == 'head' || $heading > 6) {
                $output .= "<div class='ncl-block ncl-block-head ncl-block-head-" . $level . "'>";
                $output .= "<h" . $heading . " class='ncl-heading ncl-heading-" . $level . "'>" . $title . "</h" . $heading . ">";
            } else {
                $output .= "<div class='ncl-block ncl-block-bullet ncl-block-bullet-" . $level . "'>";
                $output .= "<div class='ncl-heading ncl-heading-" . $level . "'>\n* " . $link . "\n</div>";
            }
        }
 
        // Generate the category output, and put the various items in $pieces at first.
        $pieces = array();
 
        // Output each subcategory's name, if settings['showcats'] and we don't
        // have a real listing of its contents, because we hit maxdepth.
        if ($this->settings['showcats'] && !$category->hasCatLinks()) {
            $subCatTitles = $category->getCatTitles();
            foreach ($subCatTitles as $title) {
                $ptitle   = $title->getPrefixedText();
                $title    = $wgContLang->convert($title->getText());
                $disp     = "<span class='ncl-subcategory'>[[:" . $ptitle . "|" . $title . "]]</span>";
                $pieces[] = $disp;
            }
        }
 
        // Output each article in the category, if settings['showarts'].
        # for 'number' see http://www.mediawiki.org/wiki/Extension_talk:NiceCategoryList2#Some_update_for_this_Extension
        if ($this->settings['showarts']) {
            $articleTitles = $category->getArtTitles();
            $number = $this->settings['number'];
            if (count($articleTitles) <= $number || $number == 0) {
                foreach ($articleTitles as $link) {
                    $ptitle   = $link->getPrefixedText();
                    $title    = $link->getText();
                    $disp     = "<span class='ncl-article'>[[:" . $ptitle . "|" . $title . "]]</span>";
                    $pieces[] = $disp;
                }
            } else {
                if ($this->settings['random']) {
                    $articleShow = array_rand($articleTitles, $number);
                    for ($i=0; $i<$number; $i++) {
                        $ptitle   = $articleTitles[$articleShow[$i]]->getPrefixedText();
                        $title    = $articleTitles[$articleShow[$i]]->getText();
                        $disp     = "<span class='ncl-article'>[[:" . $ptitle . "|" . $title . "]]</span>";
                        $pieces[] = $disp;
                    }
                } else {
                    $articleShow = $articleTitles;
                    for ($i=0; $i<$number; $i++) {
                        $ptitle   = $articleTitles[$i]->getPrefixedText();
                        $title    = $articleTitles[$i]->getText();
                        $disp     = "<span class='ncl-article'>[[:" . $ptitle . "|" . $title . "]]</span>";
                        $pieces[] = $disp;
                    }
                }
                $pieces[] .= "&nbsp;[[:" . $ctitle . "|&hellip;]]";
            }
        }
 
        // Display them with requested style parameter
        if (count($pieces) > 0) {
            if ($this->settings['style'] == 'bullet') {
                $output .= "<div class='ncl-content ncl-content-bullet ncl-content-bullet-" . $level . "'>";
                $output .= "\n* " . implode("\n* ", $pieces) . "\n";
                $output .= "</div>";
            } else {
                $output .= "<div class='ncl-content ncl-content-compact ncl-content-compact-" . $level . "'>";
                $output .= implode("&nbsp;&bull; ", $pieces);
                $output .= "</div>";
            }
        }
 
        if ($showfirst || $level > 0) {
            $output .= "</div>"; // end of DIV block with classname='ncl-block*'
        }
 
        // Recurse into each subcategory.
        $subCategories = $category->getCategories();
        foreach ($subCategories as $cat)
            $output .= $this->outputCategory($cat, $level + 1);
 
        return $output;
    }
 
}
 
?>
