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
<p>Hello!</p>

<p>You receive this e-mail because you gave feedback on {blog_name}. This data was submitted by you:</p>

<p><strong>Name:</strong> {name}<br>
<strong>Email:</strong> {email}<br>
<strong>Message:</strong> {message}</p>

<p>Thank you for your feedback!</p>

<p>This message was sent by {blog_name}.</p>