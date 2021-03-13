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
 * WMD Api Main
 */
class Class_Wmd_Api {

    /**
     * WMD Api Instance
     */
    public static $_instance = null;

    public $current_domain = null;
    public $site_url       = null;

    /**
     * WMD Api 后台类实例化
     *
     * @return WMD Api instance
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getTableName() {
        return 'wmd_domain_theme_bind';
    }

    public function rest_only_for_authorized_users($wp_rest_server) {
        if (!is_user_logged_in()) {
            wp_die('Illegal operation!');
        }
    }

    /**
     * 构造函数
     *
     * @return void
     */
    public function __construct() {
        //禁用未登录用户获取 API
        add_filter('rest_api_init', array($this, 'rest_only_for_authorized_users'), 99);
        add_action('rest_api_init', array($this, 'init_api'));
    }

    /**
     * 初始化钩子和过滤
     */
    public function init_api() {
        register_rest_route('wp-multi-domain/v1', 'list', ['methods' => 'GET', 'callback' => array($this, 'lists')]);
        register_rest_route('wp-multi-domain/v1', 'add', ['methods' => 'GET', 'callback' => array($this, 'add_html')]);
        register_rest_route('wp-multi-domain/v1', 'add_item', ['methods' => 'POST', 'callback' => array($this, 'add_item')]);
        register_rest_route('wp-multi-domain/v1', 'delete_item', ['methods' => 'POST', 'callback' => array($this, 'delete_item')]);
        register_rest_route('wp-multi-domain/v1', 'theme_list',
            ['methods' => 'GET', 'callback' => array($this, 'theme_list')]);
    }

    public function lists() {
        $page  = isset($_GET['pages']) ? $_GET['pages'] : 1;
        $limit = isset($_GET['limit']) ? $_GET['limit'] : 10;

        global $wpdb;

        $table_name = $this->getTableName();
        $theme_list = $this->theme_list();
        $start      = ($page - 1) * $limit;
        $sql        = "select * from `{$table_name}` order by `id` desc limit {$start},{$limit}";
        $data       = $wpdb->get_results($sql);

        foreach ($data as $k => $value) {
            $t = $value->theme;
            if (!in_array($t, $theme_list)) {
                $data[$k]->theme = $data[$k]->theme . '[不存在了,删除重新设置]';
            }
        }

        $ret = [
            'data' => $data,
            'code' => 0,
        ];

        return $ret;
    }

    public function add_html() {
        header("Content-type:text/html;charset=utf-8");
        if (isset($_GET['id'])) {
            global $wpdb;
            $table_name = $this->getTableName();

            $id   = $_GET['id'];
            $sql  = "select * from `{$table_name}` where id='$id'";
            $data = $wpdb->get_results($sql);

            $content = file_get_contents(WMD_ROOT . 'assets/edit.html');

            $content = str_replace('{$THEME}', $data[0]->theme, $content);
            $content = str_replace('{$DOMAIN}', $data[0]->domain, $content);
            $content = str_replace('{$PLATFORM}', $data[0]->platform, $content);
            $content = str_replace('{$ID}', $data[0]->id, $content);
            echo $content;
        } else {

            echo file_get_contents(WMD_ROOT . 'assets/add.html');
        }
        wp_die();
    }

    public function getDomain($domain) {
        global $wpdb;
        $table_name = $this->getTableName();
        $sql        = "select id,domain,theme,platform from `{$table_name}` where domain like '%$domain%' limit 1";
        $data       = $wpdb->get_results($sql);
        return $data;
    }

    public function add_item() {
        global $wpdb;
        $table_name = $this->getTableName();

        if (isset($_POST['id']) && is_numeric($_POST['id'])) {
            $id       = $_POST['id'];
            $domain   = $_POST['domain'];
            $platform = $_POST['platform'];
            $cptime   = date('Y-m-d H:i:s');

            $domain_list = explode(',', $domain);
            foreach ($domain_list as $value) {
                $sql  = "select * from `{$table_name}` where domain like '%$value%' and id<>'$id'";
                $data = $wpdb->get_results($sql);

                if (!empty($data)) {
                    return ['code' => -1, 'msg' => '【' . $value . '】域名已经重复!'];
                }
            }

            $sql  = "update `{$table_name}` set `domain`='{$domain}',`platform`='{$platform}',`updated_time`='{$cptime}' where id='{$id}'";
            $data = $wpdb->query($sql);
            if (!$data) {
                return ['code' => -1, 'msg' => '更新失败!'];
            }
        } else {
            if (isset($_POST['theme'])) {
                $theme    = $_POST['theme'];
                $domain   = $_POST['domain'];
                $platform = $_POST['platform'];
                $cptime   = date('Y-m-d H:i:s');

                $sql  = "select * from `{$table_name}` where theme='$theme' or domain like '$domain%' limit 1";
                $data = $wpdb->get_results($sql);
                if (empty($data)) {
                    $sql  = "insert into `{$table_name}` (`theme`,`domain`,`platform`,`created_time`,`updated_time`) values('{$theme}','{$domain}','{$platform}','{$cptime}','{$cptime}')";
                    $data = $wpdb->query($sql);
                } else {
                    return ['code' => -1, 'msg' => '主题和域名或存在重复信息!'];
                }
            }
        }
        return ['code' => 0, 'msg' => 'ok'];
    }

    public function delete_item() {
        global $wpdb;
        $table_name = $this->getTableName();
        if (isset($_POST['id'])) {
            $id     = $_POST['id'];
            $sql    = "delete from `{$table_name}` where id='{$id}'";
            $result = $wpdb->query($sql);
            if ($result) {
                return ['code' => 0, 'msg' => 'ok'];
            }
        }
        return ['code' => -1, 'msg' => 'ok'];
    }

    public function theme_list() {
        $theme = ABSPATH . 'wp-content/themes';
        $file  = scandir($theme);

        $list = [];
        foreach ($file as $key => $value) {
            if ($value == '.' || $value == '..') {
                continue;
            }

            if (is_dir($theme . '/' . $value)) {
                $list[] = $value;
            }
        }
        return $list;
    }
}

Class_Wmd_Api::instance();

?>
