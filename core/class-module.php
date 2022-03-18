<?php
/**
 * This trait is used by other classes to unify the setting of the instance.
 * 
 * @package Helpful
 * @subpackage Core
 * @version 4.5.0
 * @since 4.5.0
 */
namespace Helpful\Core;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

trait Module
{
    /**
     * @var self
     */
    public static $instance;

    /**
     * @return self
     */
    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}