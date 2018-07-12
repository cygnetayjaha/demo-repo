<?php

defined('APP_NAME') or die(header('HTTP/1.0 403 Forbidden'));

/*
 * @author Balaji
 * @name: Turbo Website Reviewer
 * @copyright © 2017 ProThemes.Biz
 *
 */

$pageTitle = 'Manage Themes';
$subTitle = 'All Themes';
$defaultTheme = getTheme($con);
$fullLayout = 1; $footerAdd = false; $footerAddArr = array();

if($pointOut == 'success')
    $msg = successMsgAdmin('Selected theme applied successfully.');

if($pointOut == 'failed')
    $msg = successMsgAdmin('Something went wrong!');

?>