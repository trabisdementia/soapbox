<?php
// $Id: notification.inc.php,v 0.0.1 2005/10/24 20:30:00 domifara Exp $
/**
 * $Id: notification.inc.php v 1.5 23 August 2004 hsalazar Exp $
 * Module: Soapbox
 * Version: v 1.5
 * Release Date: 23 August 2004
 * Author: hsalazar
 * Licence: GNU
 * @param       $category
 * @param       $item_id
 * @param  null $event
 * @return array
 */
// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');
function sb_notify_iteminfo($category, $item_id, $event = null)
{
    /*
        global $xoopsModule, $xoopsModuleConfig, $xoopsConfig;

        if ( empty( $xoopsModule ) || $xoopsModule -> getVar( 'dirname' ) != 'soapbox' ) {
            $moduleHandler = & xoops_gethandler( 'module' );
            $module = & $moduleHandler -> getByDirname( 'soapbox' );
            $configHandler = & xoops_gethandler( 'config' );
            $config = & $configHandler -> getConfigsByCat( 0, $module -> getVar( 'mid' ) );
        } else {
            $module = & $xoopsModule;
            if ( empty( $xoopsModuleConfig ) ) {
                $configHandler = & xoops_gethandler( 'config' );
                $config = & $configHandler -> getConfigsByCat( 0, $module -> getVar( 'mid' ) );
            } else {
                $config = & $xoopsModuleConfig;
            }
        }
    */
    //    $moduleDirName = 'soapbox';
    $pathparts     = explode("/", __DIR__);
    $moduleDirName = $pathparts[array_search('modules', $pathparts) + 1];
    $item_id       = (int)($item_id);
    $item          = array();
    if ($category == 'global') {
        $item['name'] = '';
        $item['url']  = '';

        return $item;
    }

    global $xoopsDB;

    if ($category == 'column') {
        // Assume we have a valid category id

        $sql    = 'SELECT name FROM ' . $xoopsDB->prefix('sbcolumns') . ' WHERE columnID  = ' . $item_id;
        $result = $xoopsDB->query($sql);
        if (!$result) {
            return $item;
        }
        $result_array = $xoopsDB->fetchArray($result);
        $item['name'] = $result_array['name'];
        $item['url']  = XOOPS_URL . '/modules/' . $moduleDirName . '/column.php?columnID=' . $item_id;

        return $item;
    }

    if ($category == 'article') {
        // Assume we have a valid story id
        $sql    = 'SELECT headline FROM ' . $xoopsDB->prefix('sbarticles') . ' WHERE articleID = ' . $item_id;
        $result = $xoopsDB->query($sql);
        if (!$result) {
            return $item;
        }
        $result_array = $xoopsDB->fetchArray($result);
        $item['name'] = $result_array['headline'];
        $item['url']  = XOOPS_URL . '/modules/' . $moduleDirName . '/article.php?articleID=' . $item_id;

        return $item;
    }
}
