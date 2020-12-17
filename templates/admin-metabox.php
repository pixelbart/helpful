<?php
/**
 * Callback for metabox.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 *
 * @since 1.0.0
 */
use Helpful\Core\Helpers as Helpers;
use Helpful\Core\Helper;

/* Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<table class="form-table meta_box">
    <tbody>
        <tr>
            <th style="width:20%"><label><?php _ex( 'Pro', 'meta box label', 'helpful' ); ?></label></th>
            <td><?php echo $pro; ?> <?php printf( "(%s%%)", $pro_percent ); ?></td>
        </tr>
        <tr>
            <th style="width:20%"><label><?php _ex( 'Contra', 'meta box label', 'helpful' ); ?></label></th>
            <td><?php echo $contra; ?> <?php printf( "(%s%%)", $contra_percent ); ?></td>
        </tr>
        <tr>
            <th style="width:20%">
                <label for="helpful_hide_on_post"><?php _ex( 'Disable Helpful', 'meta box label', 'helpful' ); ?></label>
            </th>
            <td>
                <input type="checkbox" name="helpful_hide_on_post" id="helpful_hide_on_post" value="yes" <?php checked( $hide, 'on' ); ?>>
                <label for="helpful_hide_on_post">
                    <span class="description"><?php _ex( 'Select to disable Helpful in this post.', 'checkbox label', 'helpful' ); ?></span>
                </label>
            </td>
        </tr>
        <tr>
            <th style="width:20%">
                <label for="helpful_hide_feedback_on_post"><?php _ex( 'Disable Feedback', 'meta box label', 'helpful' ); ?></label>
            </th>
            <td>
                <input type="checkbox" name="helpful_hide_feedback_on_post" id="helpful_hide_feedback_on_post" value="yes" <?php checked( $hide_feedback, 'on' ); ?>>
                <label for="helpful_hide_feedback_on_post">
                    <span class="description"><?php _ex( 'Select to disable Feedback in this post.', 'checkbox label', 'helpful' ); ?></span>
                </label>
            </td>
        </tr>
        <tr>
            <th style="width:20%">
                <label for="helpful_remove_data"><font color="#b52727"><?php _ex( 'Reset Post', 'meta box label', 'helpful' ); ?></font></label>
            </th>
            <td>
                <input type="checkbox" name="helpful_remove_data" id="helpful_remove_data" value="yes">
                <label for="helpful_remove_data">
                    <span class="description"><?php _ex( 'Select to reset the entries of Helpful for this post.', 'checkbox label', 'helpful' ); ?></span>
                </label>
            </td>
        </tr>
        <tr>
            <th style="width:20%">
                <label for="helpful_feedback_receivers"><?php _ex( 'Feedback Email Receivers', 'meta box label', 'helpful' ); ?></label>
            </th>
            <td>
                <input type="text" class="widefat" name="helpful_feedback_receivers" id="helpful_feedback_receivers" value="<?php echo $receivers; ?>" placeholder="email, email, ..."><br>
                <label for="helpful_feedback_receivers">
                    <span class="description"><?php _ex( 'Here you can enter email receivers (separated by a comma). Email receivers will then also receive the feedback by email. This does not include voting and there is <strong>no spam protection</strong>.', 'checkbox label', 'helpful' ); ?></span>
                </label>
            </td>
        </tr>
        <tr>
            <th style="width:20%">
                <label for="helpful_heading"><?php _ex( 'Headline', 'meta box label', 'helpful' ); ?></label>
            </th>
            <td>
                <textarea class="widefat" name="helpful_heading" id="helpful_heading"><?php echo $helpful_heading; ?></textarea><br>
                <span class="description">You can also use HTML.</span>
            </td>
        </tr>
        <tr>
            <th style="width:20%">
                <label for="helpful_exists"><?php _ex( 'Already voted', 'meta box label', 'helpful' ); ?></label>
            </th>
            <td>
                <textarea class="widefat" name="helpful_exists" id="helpful_exists"><?php echo $helpful_exists; ?></textarea><br>
                <span class="description">You can also use HTML.</span>
            </td>
        </tr>
        <tr>
            <th style="width:20%">
                <label for="helpful_after_pro"><?php _ex( 'After voting (pro)', 'meta box label', 'helpful' ); ?></label>
            </th>
            <td>
                <textarea class="widefat" name="helpful_after_pro" id="helpful_after_pro"><?php echo $helpful_after_pro; ?></textarea><br>
                <span class="description">You can also use HTML.</span>
            </td>
        </tr>
        <tr>
            <th style="width:20%">
                <label for="helpful_after_contra"><?php _ex( 'After voting (contra)', 'meta box label', 'helpful' ); ?></label>
            </th>
            <td>
                <textarea class="widefat" name="helpful_after_contra" id="helpful_after_contra"><?php echo $helpful_after_contra; ?></textarea><br>
                <span class="description">You can also use HTML.</span>
            </td>
        </tr>
        <tr>
            <th style="width:20%">
                <label for="helpful_after_fallback"><?php _ex( 'After voting (fallback)', 'meta box label', 'helpful' ); ?></label>
            </th>
            <td>
                <textarea class="widefat" name="helpful_after_fallback" id="helpful_after_fallback"><?php echo $helpful_after_fallback; ?></textarea><br>
                <span class="description">You can also use HTML.</span>
            </td>
        </tr>
        <tr>
            <th style="width:20%">
                <label for="helpful_pro"><?php _ex( 'Button (pro)', 'meta box label', 'helpful' ); ?></label>
            </th>
            <td>
                <textarea class="widefat" name="helpful_pro" id="helpful_pro"><?php echo $helpful_pro; ?></textarea><br>
                <span class="description">You can also use HTML.</span>
            </td>
        </tr>
        <tr>
            <th style="width:20%">
                <label for="helpful_contra"><?php _ex( 'Button (contra)', 'meta box label', 'helpful' ); ?></label>
            </th>
            <td>
                <textarea class="widefat" name="helpful_contra" id="helpful_contra"><?php echo $helpful_contra; ?></textarea><br>
                <span class="description">You can also use HTML.</span>
            </td>
        </tr>
    </tbody>
</table><!-- .form-table.meta_box -->