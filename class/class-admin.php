<?php
/**
 * WxRobot Admin
 *
 * WP微信机器人后台
 *
 * @author         midoks
 * @category     Admin
 * @package        WxRobot/Admin
 * @since        5.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class_Wmd_Admin 后台控制类
 */
class Class_Wmd_Admin {

    /**
     * WxRobot Admin Instance
     */
    public static $_instance = null;

    /**
     * WxRobot 后台类实例化
     *
     * @return WxRobot Admin instance
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 构造函数
     *
     * @return void
     */
    public function __construct() {

        add_filter('plugin_action_links', array($this, 'wmd_action_links'), 10, 2);
        add_filter('plugin_row_meta', array($this, 'wmd_row_meta'), 10, 2);

        add_action('admin_menu', array($this, 'wmd_menu'), 1);
    }

    /**
     * 插件右侧过滤功能
     */
    public function wmd_row_meta($input, $file) {
        $file_arr = explode('/', $file);
        if ('wp-multi-domain' == $file_arr[0]) {
            array_push($input, '<a href="https://github.com/midoks/midoks/wiki" target="_blank">API文档</a>');
            array_push($input, '<a href="https://github.com/midoks/midoks" target="_blank">代码版本</a>');
        }
        return $input;
    }

    /**
     * 过滤设置功能插件功能显示
     */
    public function wmd_action_links($links, $file) {
        if (basename($file) != basename(plugin_basename(WMD_ROOT_POS))) {
            return $links;
        }
        $settings_link = '<a href="admin.php?page=wmd-list">设置</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * 后台菜单
     */
    public function wmd_menu() {
        add_menu_page('多域名管理',
            _('多域名管理'),
            'manage_options',
            'wmd',
            array(&$this, 'wmd_instro'),
            WMD_ROOT_URL . '/ico.png');

        add_submenu_page('wmd',
            'wmd',
            '列表',
            'manage_options',
            'wmd-list',
            array($this, 'wmd_list'));

        add_action('admin_head', array(&$this, 'wmd_menu_js'), 10);
    }

    public function wmd_instro() {
        echo file_get_contents(WMD_ROOT . 'assets/instro.html');
    }

    /**
     * 加载需要的js
     *
     * @return void
     */
    public function wmd_menu_js() {
        $url = WMD_ROOT_URL;
        if (!empty($_GET['page']) && 'wmd-list' == $_GET['page']) {
            echo '<link type="text/css" rel="stylesheet" href="' . $url . '/assets/layui/css/layui.css" />';
            echo '<script type="text/javascript" src="' . $url . '/assets/layui/layui.js"></script>';
        }
    }

    public function wmd_list() {
        // echo '<div class="wrap">';
        // echo '<h2>多域名管理</h2>';
        // echo '</div>';
        echo file_get_contents(WMD_ROOT . 'assets/list.html');
    }

}

return Class_Wmd_Admin::instance();

?>
