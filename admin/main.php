<?php
// $Id: main.php,v 0.0.1 2005/10/27 20:30:00 domifara Exp $
/**
 * $Id: main.php v 1.5 23 August 2004 hsalazar Exp $
 * Module: Soapbox
 * Version: v 1.5
 * Release Date: 23 August 2004
 * Author: hsalazar
 * Licence: GNU
 */

require_once __DIR__ . '/admin_header.php';
$indexAdmin = new ModuleAdmin();

$op = '';
if (isset($_GET['op'])) {
    $op = trim(strip_tags($myts->stripSlashesGPC($_GET['op'])));
}
if (isset($_POST['op'])) {
    $op = trim(strip_tags($myts->stripSlashesGPC($_POST['op'])));
}

$entries = isset($_POST['entries']) ? (int)($_POST['entries']) : 0;

/* Available operations */
switch ($op) {
    case 'default':
    default:
        include_once XOOPS_ROOT_PATH . '/class/pagenav.php';

        $startart = isset($_GET['startart']) ? (int)($_GET['startart']) : 0;
        $startcol = isset($_GET['startcol']) ? (int)($_GET['startcol']) : 0;
        $startsub = isset($_GET['startsub']) ? (int)($_GET['startsub']) : 0;
        $datesub  = isset($_GET['datesub']) ? (int)($_GET['datesub']) : 0;

        xoops_cp_header();
        echo $indexAdmin->addNavigation('main.php');
        $indexAdmin->addItemButton(_MI_SOAPBOX_ADD_ARTICLE, 'article.php', 'add', '');
        $indexAdmin->addItemButton(_MI_SOAPBOX_ADD_COLUMN, 'column.php', 'add', '');
        echo $indexAdmin->renderButton('left', '');

        include_once XOOPS_ROOT_PATH . '/class/xoopsform/grouppermform.php';
        include_once XOOPS_ROOT_PATH . "/modules/" . $xoopsModule->dirname() . "/include/cleantags.php";
        $module_id = $xoopsModule->getVar('mid');

        showArticles($xoopsModuleConfig['buttonsadmin']);
        showColumns($xoopsModuleConfig['buttonsadmin']);
}

include_once __DIR__ . '/admin_footer.php';
