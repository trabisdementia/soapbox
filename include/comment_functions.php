<?php
// $Id: comment_functions.php,v 0.0.1 2005/10/24 20:30:00 domifara Exp $
/**
 * $Id: comment_functions.php v 1.5 25 April 2004 hsalazar Exp $
 * Module: Soapbox
 * Version: v 1.5
 * Release Date: 25 April 2004
 * Author: hsalazar
 * Licence: GNU
 * @param $art_id
 * @param $total_num
 */
// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');
function sb_com_update($art_id, $total_num)
{
    //HACK
    //get soapbox moduleConfig
    global $xoopsModule;
    $hModConfig            =& xoops_gethandler('config');
    $soapModuleConfig      =& $hModConfig->getConfigList((int)($xoopsModule->getVar('mid')));
    $globaldisplaycomments = 0;
    if (isset($soapModuleConfig['globaldisplaycomments'])) {
        $globaldisplaycomments = $soapModuleConfig['globaldisplaycomments'];
    }
    if ($globaldisplaycomments == 0) {
        $db  =& XoopsDatabaseFactory::getDatabaseConnection();
        $sql = 'UPDATE ' . $db->prefix('sbarticles') . ' SET commentable = ' . (int)($total_num) . ' WHERE articleID = ' . (int)($art_id);
        $db->query($sql);
    }
}

/**
 * @param $comment
 */
function sb_com_approve(&$comment)
{
    // notification mail here
}
