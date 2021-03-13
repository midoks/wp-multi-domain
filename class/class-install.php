<?php
/**
 * WxRobot Install
 *
 * WP微信机器人安装准备
 *
 * @author         midoks
 * @category     Install
 * @package        WxRobot/Install
 * @since        5.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class_Wmd_Install 安装类
 */
class Class_Wmd_Install {

    /**
     * 表前缀
     */
    public static $table_prefix = 'wmd_';

    /**
     * 构造函数
     *
     * @return void
     */
    public function __construct() {

        var_dump('123123');
        // $this->create_options();
        $this->create_tables();
    }

    /**
     * 保存基本配置信息
     *
     * @return void
     */
    public function create_options() {
        $options['status'] = 'ok';
        add_option(WMD_OPTIONS, $options);
    }

    /**
     * 创建表
     *
     * @return void
     */
    public function create_tables() {
        global $wpdb;
        $wpdb->hide_errors();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $wpdb->query(self::get_schema_domain_bind_list());

        //此方法未生效
        //dbDelta();
    }

    /**
     * 获取表属性
     */
    public static function get_schema_attr() {
        global $wpdb;
        $collate = '';
        if ($wpdb->has_cap('collation')) {
            if (!empty($wpdb->charset)) {
                $collate .= " DEFAULT CHARACTER SET $wpdb->charset";
            }
            if (!empty($wpdb->collate)) {
                $collate .= " COLLATE $wpdb->collate";
            }
        }
        return $collate;
    }

    /**
     * 获取创建微信通信的SQL
     *
     * @return sql
     */
    public static function get_schema_domain_bind_list() {

        $prefix  = self::$table_prefix;
        $collate = self::get_schema_attr();

        return "create table if not exists {$prefix}domain_theme_bind (
					`id` bigint(20) not null auto_increment,
					`domain` varchar(64) not null,
					`theme` varchar(32) not null,
					`platform` varchar(32) not null,
					`updated_time` datetime DEFAULT NULL,
					`created_time` datetime DEFAULT NULL,
                    UNIQUE KEY `theme` (`theme`),
					primary key(`id`)
				) $collate;";
    }

}

return new Class_Wmd_Install();
?>
