<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   Utalii Hotel Reservation System
 * @author    Mtaandao
 * @link      http://mtaandao.co.ke/
 * @copyright 2016 Mtaandao
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
        exit;
}
require_once 'define.php';
global $wpdb;
