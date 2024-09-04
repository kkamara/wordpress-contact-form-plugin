<?php
// Check if file is accessed directly.
if (!defined("ABSPATH") || !defined("WPINC")) {
    exit("Do not access this file directly.");
}
?>
<div class="kkamara-contact-form-description">
    <p>Copy this shortcode and paste it into your post, page, or text widget content:</p>
    <p class="kkamara-contact-highlight">
        [kkamara-contact-form id="<?php echo esc_html($checkForId->generated_id); ?>" title="<?php echo esc_html(get_post_field("post_title", $checkForId->post_id)); ?>"]
    </p>
</div>