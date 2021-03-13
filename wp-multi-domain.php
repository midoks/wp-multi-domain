<?php
/*
 * Plugin Name: WP多域名管理
 * Plugin URI: http://www.cachecha.com/
 * Description:不同的域名绑定不同的主题
 * Version: 1.0.0
 * Author: Midoks
 * Author URI: http://www.cachecha.com/
 */

if (!defined('ABSPATH')) {
    exit; //不能直接进入
}

define('WMD_ROOT', str_replace('\\', '/', dirname(__FILE__)) . '/');
define('WMD_ROOT_POS', __FILE__);

include_once WMD_ROOT . 'class/class-wmd.php';

?>