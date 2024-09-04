<?php
// Check if file is accessed directly.
if (!defined("ABSPATH") || !defined("WPINC")) {
    exit("Do not access this file directly.");
}
?>
<p>
    Am working <?php echo esc_html($id) . " " . esc_html($title); ?>
</p>