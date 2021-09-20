<?php
defined('XOOPS_ROOT_PATH') || die('Restricted access.');

class 首字大寫模組目錄CorePreload extends XoopsPreloadItem
{
    // to add PSR-4 autoloader

    /**
     * @param $args
     */
    public static function eventCoreIncludeCommonEnd($args)
    {
        require __DIR__ . '/autoloader.php';
    }
}
