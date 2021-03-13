<?php
/**
 * WxRobot Uninstall
 *
 * WP微信机器人卸载程序
 *
 * @author         midoks
 * @category     Uninstall
 * @package        WxRobot/Uninstall
 * @since        5.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class_Wmd_Uninstall 插件卸载类
 */
class Class_Wmd_Uninstall {

    /**
     * 构造函数
     *
     * @return void
     */
    public function __construct() {
        $this->delete_options();
        $this->delete_tables();
    }

    /**
     * 删除配置内容
     *
     * @return void
     */
    public function delete_options() {
        delete_option(WEIXIN_ROBOT_OPTIONS);
    }

    /**
     * 删除表
     *
     * @return void
     */
    public function delete_tables() {
        global $wpdb;

        $sqls[] = "DROP TABLE wmd_domain_theme_bind;";

        foreach ($sqls as $sql) {
            $wpdb->query($sql);
        }
    }
}

return new Class_Wmd_Uninstall();
?>
