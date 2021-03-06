<?php
// $Id: column.php,v 0.0.1 2005/10/24 20:30:00 domifara Exp $
/**
 * $Id: column.php v 1.5 23 August 2004 hsalazar Exp $
 * Module: Soapbox
 * Version: v 1.5
 * Release Date: 23 August 2004
 * Author: hsalazar
 * Licence: GNU
 */

include __DIR__ . '/header.php';
$op = '';
//HACK for cache by domifara
if (is_object($xoopsUser)) {
    $xoopsConfig['module_cache'] = 0; //disable caching since the URL will be the same, but content different from one user to another
}

$xoopsOption['template_main'] = 'sb_column.tpl';
include_once(XOOPS_ROOT_PATH . "/header.php");

$moduleDirName = $myts->htmlSpecialChars(basename(__DIR__));
if ($moduleDirName !== "soapbox" && $moduleDirName !== "" && !preg_match('/^(\D+)(\d*)$/', $moduleDirName)) {
    echo("invalid dirname: " . htmlspecialchars($moduleDirName, ENT_QUOTES));
}

$columnID = XoopsRequest::getInt('columnID', 0, 'GET');
//---GET view sort --
$sortname = isset($_GET['sortname']) ? strtolower(trim(strip_tags($myts->stripSlashesGPC($_GET['sortname'])))) : 'datesub';
if (!in_array($sortname, array('datesub', 'weight', 'counter', 'rating', 'headline'))) {
    $sortname = 'datesub';
}
$sortorder = isset($_GET['sortorder']) ? strtoupper(trim(strip_tags($myts->stripSlashesGPC($_GET['sortorder'])))) : 'DESC';
if (!in_array($sortorder, array('ASC', 'DESC'))) {
    $sortorder = 'DESC';
}
//---------------
include_once XOOPS_ROOT_PATH . '/class/pagenav.php';
$start = XoopsRequest::getInt('start', 0, 'GET');

//-------------------------------------
$_entrydata_handler =& xoops_getmodulehandler('entryget', $xoopsModule->dirname());
//-------------------------------------
$_entryob_arr =& $_entrydata_handler->getArticlesAllPermcheck((int)($xoopsModuleConfig['indexperpage']), $start, true, true, 0, 0, null, $sortname, $sortorder, $columnID, null, true, false);
$totalarts    = $_entrydata_handler->total_getArticlesAllPermcheck;
if (empty($_entryob_arr) || $totalarts == 0) {
    redirect_header(XOOPS_URL . "/modules/" . $moduleDirName . "/index.php", 1, _MD_SOAPBOX_MAINNOTOPICS);
}
//get category object
$_categoryob =& $_entryob_arr[0]->_sbcolumns;
//get vars

$category = array();
$category = $_categoryob->toArray(); //all assign

$category['colid']      = $columnID;
$category['author']     = getLinkedUnameFromId($category['author'], 0);
$category['authorname'] = getAuthorName($category['author']);
$category['image']      = $category['colimage'];
$category['total']      = $totalarts;
$xoopsTpl->assign('category', $category);

//------------------------------------------------------
foreach ($_entryob_arr as $_entryob) {
    //-----------
    unset($articles);
    $articles = array();
    //get vars
    $articles = $_entryob->toArray();
    //--------------------
    $articles['id']      = $articles['articleID'];
    $articles['datesub'] = $myts->htmlSpecialChars(formatTimestamp($articles['datesub'], $xoopsModuleConfig['dateformat']));;
    //        $articles['poster'] = XoopsUserUtility::getUnameFromId( $articles['uid'] );
    $articles['poster']   = getLinkedUnameFromId($category['author']);
    $articles['bodytext'] = xoops_substr($articles['bodytext'], 0, 255);
    //--------------------
    if ($articles['submit'] != 0) {
        $articles['headline'] = '[' . _MD_SOAPBOX_SELSUBMITS . ']' . $articles['headline'];
        $articles['teaser']   = $xoopsUser->getVar('uname') . _MD_SOAPBOX_SUB_SNEWNAMEDESC;
        $articles['lead']     = $xoopsUser->getVar('uname') . _MD_SOAPBOX_SUB_SNEWNAMEDESC;
    } elseif ($_entryob->getVar('datesub') == 0 || $_entryob->getVar('datesub') > time()) {
        $articles['headline'] = '[' . _MD_SOAPBOX_SELWAITEPUBLISH . ']' . $articles['headline'];
        $articles['teaser']   = $xoopsUser->getVar('uname') . _MD_SOAPBOX_SUB_SNEWNAMEDESC;
        $articles['lead']     = $xoopsUser->getVar('uname') . _MD_SOAPBOX_SUB_SNEWNAMEDESC;
    }
    //--------------------
    if (!empty($articles['artimage']) && $articles['artimage'] != 'blank.png' && file_exists(XOOPS_ROOT_PATH . '/' . $myts->htmlSpecialChars($xoopsModuleConfig['sbuploaddir']) . '/' . $articles['artimage'])) {
        $articles['image'] = XOOPS_URL . '/' . $myts->htmlSpecialChars($xoopsModuleConfig['sbuploaddir']) . '/' . $articles['artimage'];
    } else {
        $articles['image'] = '';
    }

    if ($xoopsModuleConfig['includerating'] == 1) {
        $xoopsTpl->assign('showrating', 1);
        $rating = $articles['rating'];
        $votes  = $articles['votes'];
        if ($rating != 0.00) {
            $articles['rating'] = _MD_SOAPBOX_RATING . ": " . $myts->htmlSpecialChars(number_format($rating, 2));
            $articles['votes']  = _MD_SOAPBOX_VOTES . ": " . $myts->htmlSpecialChars($votes);
        } else {
            $articles['rating'] = _MD_SOAPBOX_RATING . ": 0.00";
            $articles['votes']  = _MD_SOAPBOX_VOTES . ": 0";
        }
    }
    //--------------------
    // Functional links
    $articles['adminlinks'] = $_entrydata_handler->getadminlinks($_entryob, $_categoryob);
    $articles['userlinks']  = $_entrydata_handler->getuserlinks($_entryob);

    $xoopsTpl->append('articles', $articles);
}

$pagenav            = new XoopsPageNav($totalarts, (int)($xoopsModuleConfig['indexperpage']), $start, 'start', 'columnID=' . $articles['columnID'] . '&sortname=' . $sortname . '&sortorder=' . $sortorder);
$category['navbar'] = '<div style="text-align:right;">' . $pagenav->renderNav() . '</div>';

$xoopsTpl->assign('xoops_pagetitle', $category['name']);
$xoopsTpl->assign('category', $category);

$xoopsTpl->assign('lang_modulename', $xoopsModule->name());
$xoopsTpl->assign('lang_moduledirname', $moduleDirName);
$xoopsTpl->assign('imgdir', $myts->htmlSpecialChars($xoopsModuleConfig['sbimgdir']));
$xoopsTpl->assign('uploaddir', $myts->htmlSpecialChars($xoopsModuleConfig['sbuploaddir']));

$xoopsTpl->assign('sortname', $sortname);
$xoopsTpl->assign('sortorder', $sortorder);

$xoopsTpl->assign("xoops_module_header", '<link rel="stylesheet" type="text/css" href="' . XOOPS_URL . '/modules/' . $moduleDirName . '/style.css" />');

include(XOOPS_ROOT_PATH . "/footer.php");
