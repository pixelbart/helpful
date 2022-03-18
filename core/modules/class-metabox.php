<?php
/**
 * @package Helpful
 * @subpackage Core\Modules
 * @version 4.4.59
 * @since 4.3.0
 */
namespace Helpful\Core\Modules;

use Helpful\Core\Helper;
use Helpful\Core\Module;
use Helpful\Core\Helpers as Helpers;
use Helpful\Core\Services as Services;

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

class Metabox
{
    use Module;

    /**
     * Class constructor
     *
     * @version 4.4.59
     *
     * @return void
     */
    public function __construct()
    {
        $service = new Services\Options();

        if ('on' !== $service->get_option('helpful_metabox', 'off', 'esc_attr')) {
            return;
        }

        add_action('add_meta_boxes', [ & $this, 'add_metabox']);

        add_action('save_post', [ & $this, 'save_data']);
        add_action('save_post', [ & $this, 'save_data'], 10, 1);
    }

    /**
     * Add metabox to post types.
     *
     * @version 4.4.59
     *
     * @return void
     */
    public function add_metabox()
    {
        $options = new Services\Options();

        $post_types = $options->get_option('helpful_post_types', [], 'esc_attr');

        if (isset($post_types) && is_array($post_types)) {
            add_meta_box(
                'helpful-meta-box',
                esc_html_x('Helpful', 'meta box name', 'helpful'),
                [$this, 'render_metabox'],
                $post_types
            );
        }
    }

    /**
     * Render metabox content.
     *
     * @global $post
     *
     * @return void
     */
    public function render_metabox()
    {
        global $post;

        $pro = Helpers\Stats::get_pro($post->ID);
        $pro_percent = Helpers\Stats::get_pro($post->ID, true);
        $contra = Helpers\Stats::get_contra($post->ID);
        $contra_percent = Helpers\Stats::get_contra($post->ID, true);
        $hide = get_post_meta($post->ID, 'helpful_hide_on_post', true);
        $hide_feedback = get_post_meta($post->ID, 'helpful_hide_feedback_on_post', true);
        $receivers = get_post_meta($post->ID, 'helpful_feedback_receivers', true);

        // Not working =
        // $helpful_heading = get_post_meta($post->ID, 'helpful_heading', true);
        // $helpful_pro = get_post_meta($post->ID, 'helpful_pro', true);
        // $helpful_contra = get_post_meta($post->ID, 'helpful_contra', true);
        // $helpful_exists = get_post_meta($post->ID, 'helpful_exists', true);
        // $helpful_after_pro = get_post_meta($post->ID, 'helpful_after_pro', true);
        // $helpful_after_contra = get_post_meta($post->ID, 'helpful_after_contra', true);
        // $helpful_after_fallback = get_post_meta($post->ID, 'helpful_after_fallback', true);

        wp_nonce_field('helpful_save_metabox', 'helpful_metabox_nonce');

        include HELPFUL_PATH . 'templates/admin-metabox.php';
    }

    /**
     * Save meta box data.
     *
     * @param integer $post_id post id.
     *
     * @return void
     */
    public function save_data($post_id)
    {
        if (!isset($_POST['helpful_metabox_nonce']) || !wp_verify_nonce($_POST['helpful_metabox_nonce'], 'helpful_save_metabox')) {
            return;
        }

        if (isset($_POST['helpful_remove_data']) && 'yes' === $_POST['helpful_remove_data']) {
            Helpers\Values::remove_data($post_id);
        }

        if (isset($_POST['helpful_hide_on_post'])) {
            update_post_meta($post_id, 'helpful_hide_on_post', 'on');
        } else {
            update_post_meta($post_id, 'helpful_hide_on_post', 'off');
        }

        if (isset($_POST['helpful_hide_feedback_on_post'])) {
            update_post_meta($post_id, 'helpful_hide_feedback_on_post', 'on');
        } else {
            update_post_meta($post_id, 'helpful_hide_feedback_on_post', 'off');
        }

        $metas = [
            // 'helpful_heading',
            // 'helpful_pro',
            // 'helpful_contra',
            // 'helpful_exists',
            // 'helpful_after_pro',
            // 'helpful_after_contra',
            // 'helpful_after_fallback',
            'helpful_feedback_receivers',
        ];

        foreach ($metas as $meta):
            if (isset($_POST[$meta]) && '' !== trim($_POST[$meta])) {
                update_post_meta($post_id, $meta, $_POST[$meta]);
            } else {
                delete_post_meta($post_id, $meta);
            }
        endforeach;
    }
}
