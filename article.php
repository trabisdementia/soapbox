<?php
// $Id: article.php,v 0.0.1 2005/10/29 17:38:00 domifara Exp $
/**
 * $Id: article.php v 1.5 23 August 2004 hsalazar Exp $
 * Module: Soapbox
 * Version: v 1.5
 * Release Date: 23 August 2004
 * Author: hsalazar
 * Licence: GNU
 */

include __DIR__ . '/header.php';

$xoopsConfig['module_cache']  = 0; //disable caching since the URL will be the same, but content different from one user to another
$xoopsOption['template_main'] = 'sb_article.tpl';
include_once(XOOPS_ROOT_PATH . "/header.php");
global $xoopsModule;
//$pathIcon16 = $xoopsModule->getInfo('icons16');
$pathIcon16 = $GLOBALS['xoops']->url('www/' . $GLOBALS['xoopsModule']->getInfo('icons16'));

$moduleDirName = $myts->htmlSpecialChars(basename(__DIR__));
if ($moduleDirName !== "soapbox" && $moduleDirName !== "" && !preg_match('/^(\D+)(\d*)$/', $moduleDirName)) {
    echo("invalid dirname: " . htmlspecialchars($moduleDirName, ENT_QUOTES));
}
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
include_once XOOPS_ROOT_PATH . "/modules/" . $moduleDirName . "/include/cleantags.php";
//for ratefile update by domifara
include_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
include_once XOOPS_ROOT_PATH . "/modules/" . $moduleDirName . "/include/gtickets.php";

$articleID = isset($_GET['articleID']) ? (int)($_GET['articleID']) : 0;
$startpage = isset($_GET['page']) ? (int)($_GET['page']) : 0;
//-------------------------------------
//move here  form ratefile.php
if (isset($_POST['submit']) && !empty($_POST['lid'])) {
    if (file_exists(XOOPS_ROOT_PATH . '/modules/' . $moduleDirName . '/include/ratefile.inc.php')) {
        require XOOPS_ROOT_PATH . '/modules/' . $moduleDirName . '/include/ratefile.inc.php';
    }
    trigger_error("not updated rate :");
    exit();
}
//-------------------------------------
//view start
$articles = array();
$category = array();
//module entry data handler
$_entrydata_handler =& xoops_getmodulehandler('entryget', $moduleDirName);
if (empty($articleID)) {
    //get entry object
    $_entryob_arr =& $_entrydata_handler->getArticlesAllPermcheck(1, 0, true, true, 0, 0, null, $sortname, $sortorder, null, null, true, false);
    //    $totalarts = $_entrydata_handler->total_getArticlesAllPermcheck;
    if (empty($_entryob_arr) || count($_entryob_arr) == 0) {
        redirect_header(XOOPS_URL . "/modules/" . $moduleDirName . "/index.php", 1, _MD_SOAPBOX_NOTHING);
    }
    $_entryob =& $_entryob_arr[0];
} else {
    //get entry object
    $_entryob =& $_entrydata_handler->getArticleOnePermcheck($articleID, true, true);
    if (!is_object($_entryob)) {
        redirect_header(XOOPS_URL . "/modules/" . $moduleDirName . "/index.php", 1, "Not Found");
    }
}
//-------------------------------------
$articles = $_entryob->toArray();
//get category object
$_categoryob =& $_entryob->_sbcolumns;
//get vars
$category = $_categoryob->toArray();
//-------------------------------------
//update count
$_entrydata_handler->getUpArticlecount($_entryob, true);

//assign
$articles['id']     = $articles['articleID'];
$articles['posted'] = $myts->htmlSpecialChars(formatTimestamp($articles['datesub'], $xoopsModuleConfig['dateformat']));

// includes code by toshimitsu
if (trim($articles['bodytext']) != '') {
    $articletext    = explode("[pagebreak]", $_entryob->getVar('bodytext', 'none'));
    $articles_pages = count($articletext);
    if ($articles_pages > 1) {
        include_once XOOPS_ROOT_PATH . '/class/pagenav.php';
        $pagenav = new XoopsPageNav($articles_pages, 1, $startpage, 'page', 'articleID=' . $articles['articleID']);
        $xoopsTpl->assign('pagenav', $pagenav->renderNav());
        if ($startpage == 0) {
            $articles['bodytext'] = $articles['lead'] . '<br /><br />' . $myts->displayTarea($articletext[$startpage], $articles['html'], $articles['smiley'], $articles['xcodes'], 1, $articles['breaks']);
        } else {
            $articles['bodytext'] = $myts->displayTarea($articletext[$startpage], $articles['html'], $articles['smiley'], $articles['xcodes'], 1, $articles['breaks']);
        }
    } else {
        $articles['bodytext'] = $articles['lead'] . '<br /><br />' . $myts->displayTarea($_entryob->getVar('bodytext', 'none'), $articles['html'], $articles['smiley'], $articles['xcodes'], 1, $articles['breaks']);
    }
}
//Cleantags
$articles['bodytext'] = $GLOBALS['SoapboxCleantags']->cleanTags($articles['bodytext']);

if ($xoopsModuleConfig['includerating'] == 1) {
    $xoopsTpl->assign('showrating', '1');
    //-------------------------------------
    //for ratefile update by domifara
    $xoopsTpl->assign('rate_gtickets', $xoopsGTicket->getTicketHtml(__LINE__));
    //-------------------------------------
    if ($articles['rating'] != 0.0000) {
        $articles['rating'] = "" . _MD_SOAPBOX_RATING . ": " . $myts->htmlSpecialChars(number_format($articles['rating'], 2));
        $articles['votes']  = "" . _MD_SOAPBOX_VOTES . ": " . $myts->htmlSpecialChars($articles['votes']);
    } else {
        $articles['rating'] = _MD_SOAPBOX_NOTRATED;
    }
}

if (is_object($xoopsUser)) {
    $xoopsTpl->assign('authorpm_link', "<a href=\"javascript:openWithSelfMain('" . XOOPS_URL . "/pmlite.php?send2=1&amp;to_userid=" . $category['author'] . "', 'pmlite', 450, 380);\"><img src='" . $pathIcon16 . "/mail_new.png' alt=\"" . _MD_SOAPBOX_WRITEAUTHOR . "\" /></a>");
} else {
    $xoopsTpl->assign('user_pmlink', '');
}

// Functional links
$articles['adminlinks'] = $_entrydata_handler->getadminlinks($_entryob, $_categoryob);
$articles['userlinks']  = $_entrydata_handler->getuserlinks($_entryob);

$articles['author']     = getLinkedUnameFromId($category['author'], 0);
$articles['authorname'] = getAuthorName($category['author']);
$articles['colname']    = $category['name'];
$articles['coldesc']    = $category['description'];
$articles['colimage']   = $category['colimage'];

$xoopsTpl->assign('xoops_pagetitle', $articles['headline']);
$xoopsTpl->assign('story', $articles);
//-----------------------------
$mbmail_subject = sprintf(_MD_SOAPBOX_INTART, $xoopsConfig['sitename']);
$mbmail_body    = sprintf(_MD_SOAPBOX_INTARTFOUND, $xoopsConfig['sitename']);
$al             = soapbox_getacceptlang();
if ($al == "ja") {
    if (function_exists('mb_convert_encoding') && function_exists('mb_encode_mimeheader') && @mb_internal_encoding(_CHARSET)) {
        $mbmail_subject = mb_convert_encoding($mbmail_subject, 'SJIS', _CHARSET);
        $mbmail_body    = mb_convert_encoding($mbmail_body, 'SJIS', _CHARSET);
    }
}
$mbmail_subject = rawurlencode($mbmail_subject);
$mbmail_body    = rawurlencode($mbmail_body);
//-----------------------------
$xoopsTpl->assign('mail_link', 'mailto:?subject=' . $myts->htmlSpecialChars($mbmail_subject) . '&amp;body=' . $myts->htmlSpecialChars($mbmail_body) . ':  ' . XOOPS_URL . '/modules/' . $moduleDirName . '/article.php?articleID=' . $articles['articleID']);
$xoopsTpl->assign('articleID', $articles['articleID']);
$xoopsTpl->assign('lang_ratethis', _MD_SOAPBOX_RATETHIS);
$xoopsTpl->assign('lang_modulename', $xoopsModule->name());
$xoopsTpl->assign('lang_moduledirname', $moduleDirName);
$xoopsTpl->assign('imgdir', $myts->htmlSpecialChars($xoopsModuleConfig['sbimgdir']));
$xoopsTpl->assign('uploaddir', $myts->htmlSpecialChars($xoopsModuleConfig['sbuploaddir']));

//-------------------------------------
//box view
$listarts = array();
//-------------------------------------
$_other_entryob_arr =& $_entrydata_handler->getArticlesAllPermcheck((int)($xoopsModuleConfig['morearts']), 0, true, true, 0, 0, null, $sortname, $sortorder, $_categoryob, $articles['articleID'], true, false);
$totalartsbyauthor  = (int)($_entrydata_handler->total_getArticlesAllPermcheck) + 1;

if (!empty($_other_entryob_arr)) {
    foreach ($_other_entryob_arr as $_other_entryob) {
        $link = array();
        $link = $_other_entryob->toArray();
        //--------------------
        $link['id']        = $link['articleID'];
        $link['arttitle']  = $_other_entryob->getVar('headline');
        $link['published'] = $myts->htmlSpecialChars(formatTimestamp($_other_entryob->getVar('datesub'), $xoopsModuleConfig['dateformat']));
        //        $link['poster'] = XoopsUserUtility::getUnameFromId( $link['uid'] );
        $link['poster']      = getLinkedUnameFromId($category['author']);
        $link['bodytext']    = xoops_substr($link['bodytext'], 0, 255);
        $listarts['links'][] = $link;
    }
    $xoopsTpl->assign('listarts', $listarts);
    $xoopsTpl->assign('readmore', "<a style='font-size: 9px;' href=" . XOOPS_URL . "/modules/" . $moduleDirName . "/column.php?columnID=" . $articles['columnID'] . ">" . _MD_SOAPBOX_READMORE . "[" . $totalartsbyauthor . "]</a> ");
}

if (isset($GLOBALS['xoopsModuleConfig']['globaldisplaycomments']) && $GLOBALS['xoopsModuleConfig']['globaldisplaycomments'] == 1) {
    if ($articles['commentable'] == 1) {
        include XOOPS_ROOT_PATH . "/include/comment_view.php";
    }
} else {
    include XOOPS_ROOT_PATH . "/include/comment_view.php";
}
$xoopsTpl->assign("xoops_module_header", '<link rel="stylesheet" type="text/css" href="' . XOOPS_URL . '/modules/' . $moduleDirName . '/style.css" />');

include_once XOOPS_ROOT_PATH . '/footer.php';
