<?php
/**
 * @package Helpful
 * @version 4.4.50
 * @since 1.0.0
 */
use Helpful\Core\Helper;
use Helpful\Core\Helpers as Helpers;

/* Prevent direct access */
if (!defined('ABSPATH')) {
	exit;
}
?>

<p>Hi!</p>

<p>You have received new {type} feedback for your post.</p>

<p><strong>Post:</strong> <a href="{post_url}">{post_title}</a><br>
<strong>Name:</strong> {name}<br>
<strong>Email:</strong> {email}<br>
<strong>Message:</strong> {message}</p>

<p>This message was sent by {blog_name}.</p>