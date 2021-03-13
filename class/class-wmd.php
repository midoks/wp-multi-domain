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
 * WMD Main
 */
class Class_Wmd {

    /**
     * WxRobot Admin Instance
     */
    public static $_instance = null;

    public $current_domain = null;
    public $site_url       = null;
    public $theme          = null;
    public $platform       = null;

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
     * WxRobot 后台类实例化
     *
     * @return WxRobot Admin instance
     */
    private function define($name, $value) {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    /**
     * 请求的类型
     * string $type ajax, frontend or admin
     * @return bool
     */
    private function is_request($type) {
        switch ($type) {
        case 'admin':
            return is_admin();
        case 'ajax':
            return defined('DOING_AJAX');
        case 'cron':
            return defined('DOING_CRON');
        case 'frontend':
            return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON');
        }
    }

    /**
     * 主题切换方法
     * @return string
     */
    public function themeSwitch($theme) {
        if ($this->theme) {

            if ($this->platform == 'all') {
                return $this->theme;
            }

            if ($this->platform == 'mobile' && wp_is_mobile()) {
                return $this->theme;
            }
        }
        return $theme;
    }

    public function getSiteurl() {
        return $this->site_url;
    }

    public function getHome() {

        // var_dump($this->site_url);
        return $this->current_domain;
    }

    /**
     * 设置域名
     * @return void
     */
    public function settingDomain() {

        $domain = $_SERVER['HTTP_HOST'];

        $data = Class_Wmd_Api::instance()->getDomain($domain);

        if (empty($data)) {
        } else {
            $this->theme    = $data[0]->theme;
            $this->platform = $data[0]->platform;
        }
    }

    /**
     * 构造函数
     *
     * @return void
     */
    public function __construct() {
        $this->define('WMD_ROOT_URL', plugins_url('', dirname(__FILE__)));
        $this->define('WMD_OPTIONS', 'wmd_options');

        include_once WMD_ROOT . '/class/class-api.php';
        if ($this->is_request('admin')) {
            include_once WMD_ROOT . '/class/class-admin.php';
        }

        $this->init_hooks();

        $current_domain = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) == 'on' ? 'https' : 'http';
        $current_domain .= '://' . $_SERVER['HTTP_HOST'];

        $this->current_domain = $current_domain;
        $this->site_url       = site_url();

        // add_action('init', array($this, 'settingDomain'));
        $this->settingDomain();

        add_filter('pre_option_template', array(&$this, 'themeSwitch'));
        add_filter('pre_option_stylesheet', array(&$this, 'themeSwitch'));
        add_filter('pre_option_siteurl', array(&$this, 'getSiteurl'));
        add_filter('pre_option_home', array(&$this, 'getHome'));

    }

    /**
     * 初始化钩子和过滤
     */
    public function init_hooks() {
        register_activation_hook(WMD_ROOT_POS, array($this, 'install'));
        register_deactivation_hook(WMD_ROOT_POS, array($this, 'uninstall'));
    }

    /**
     * 插件安装
     */
    public function install() {
        include_once WMD_ROOT . 'class/class-install.php';
    }

    /**
     * 插件卸载
     */
    public function uninstall() {
        include_once WMD_ROOT . 'class/class-uninstall.php';
    }
}

Class_Wmd::instance();

?>
