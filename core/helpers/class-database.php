<?php
/**
 * @package Helpful
 * @subpackage Core\Helpers
 * @version 4.5.0
 * @since 4.3.0
 */
namespace Helpful\Core\Helpers;

use Helpful\Core\Helper;
use Helpful\Core\Services as Services;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class Database
{
    /**
     * Checks whether a database table exists.
     *
     * @global $wpdb
     *
     * @param string $table_name
     *
     * @return bool
     */
    public static function table_exists($table_name)
    {
        global $wpdb;

        if ($table_name != $wpdb->get_var("SHOW TABLES LIKE '$table_name'")) {
            return false;
        }

        return true;
    }

    /**
     * Checks if tables exists and creates tables if not
     *
     * @global $wpdb
     *
     * @param string $table_name database table name.
     *
     * @return array
     */
    public static function table_exists_or_setup($table_name)
    {
        global $wpdb;

        $response = [];
        $table = $wpdb->base_prefix . $table_name;
        $query = $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table));

        if (!$wpdb->get_var($query) == $table_name) {

            if ('helpful_feedback' === $table_name) {
                $response[] = self::handle_table_feedback();
            }

            if ('helpful' === $table_name) {
                $response[] = self::handle_table_helpful();
            }

            if ('instances' === $table_name) {
                $response[] = self::handle_table_instances();
            }
        }

        return $response;
    }

    /**
     * Create database table for helpful
     *
     * @global $wpdb
     * @version 4.4.51
     * @since 1.0.0
     * @return void
     */
    public static function handle_table_helpful()
    {
        global $wpdb;

        self::update_tables();

        $table_name = $wpdb->prefix . 'helpful';

        $queries = [];

        $charset_collate = $wpdb->get_charset_collate();

        $queries[] = "
		CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00',
		user varchar(55) DEFAULT NULL,
		pro mediumint(1) DEFAULT NULL,
		contra mediumint(1) DEFAULT NULL,
		post_id bigint(20) DEFAULT NULL,
		instance_id bigint(20) DEFAULT NULL,
        INDEX search  (pro, contra, post_id, instance_id),
		PRIMARY KEY  (id)
		) $charset_collate;
		";

        include_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($queries);
    }

    /**
     * Create database table for feedback
     *
     * @global $wpdb
     * @version 4.4.51
     * @since 4.4.0
     * @return void
     */
    public static function handle_table_feedback()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'helpful_feedback';

        $queries = [];

        $charset_collate = $wpdb->get_charset_collate();

        $queries[] = "
		CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00',
		user varchar(55) DEFAULT NULL,
		pro mediumint(1) DEFAULT NULL,
		contra mediumint(1) DEFAULT NULL,
		post_id bigint(20) DEFAULT NULL,
		message text DEFAULT NULL,
		fields text DEFAULT NULL,
		instance_id bigint(20) DEFAULT NULL,
		PRIMARY KEY  (id)
		) $charset_collate;
		";

        include_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($queries);
    }

    /**
     * Create database table for helpful instances
     *
     * @global $wpdb
     * @version 4.4.51
     * @since 4.4.49
     * @return void
     */
    public static function handle_table_instances()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'helpful_instances';

        $queries = [];

        $charset_collate = $wpdb->get_charset_collate();

        $queries[] = "
		CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		instance_key varchar(32) DEFAULT NULL,
        instance_name text DEFAULT NULL,
		post_id bigint(20) DEFAULT NULL,
		created datetime DEFAULT NOW(),
		PRIMARY KEY  (id)
		) $charset_collate;
		";

        include_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($queries);
    }

    /**
     * Updates database tables.
     *
     * @global $wpdb
     * @version 4.5.0
     * 
     * @return void
     */
    public static function update_tables()
    {
        $options = new Services\Options();

        if ($options->get_option('helpful_update_table_integer', false, 'intval')) {
            return;
        }

        global $wpdb;

        $table_name = $wpdb->prefix . 'helpful';

        $columns = [
            'id' => 'bigint(20) NOT NULL AUTO_INCREMENT',
            'post_id' => 'bigint(20) DEFAULT NULL',
        ];

        foreach ($columns as $column => $type) {
            $sql = "ALTER TABLE $table_name MODIFY $column $type";
            $wpdb->query($sql);
        }

        $table_name = $wpdb->prefix . 'helpful_feedback';

        foreach ($columns as $column => $type) {
            $sql = "ALTER TABLE $table_name MODIFY $column $type";
            $wpdb->query($sql);
        }

        update_option('helpful_update_table_integer', time());
    }
}
