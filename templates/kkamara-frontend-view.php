<?php
// Check if file is accessed directly.
if (!defined("ABSPATH") || !defined("WPINC")) {
    exit("Do not access this file directly.");
}

// Get contact body for $post_id
$contact_body = get_post_meta(
    $post_id,
    "kkamara_form_content",
    true,
);
?>
<div class="kkamara-contact-form-frontend">
    <div class="kkamara-contact-header">
        <h3>
        <?php echo esc_html($title); ?>
        </h3>
    </div>
    <div class="kkamara-contact-body">
        <form
            action=""
            id="kkamara-contact-form-submit"
        >
            <input
                type="hidden"
                name="action"
                value="kkamara_send_message"
            />
            <input
                type="hidden"
                name="nonce"
                value="<?php echo wp_create_nonce("kkamara-contact-message"); ?>"
            />
            <?php
                // Get the shortcode regex
                $shortcode_regex = get_shortcode_regex();
                // Get the shortcodes regex
                if (
                    preg_match_all(
                        "/".$shortcode_regex."/s",
                        $contact_body,
                        $matches,
                        PREG_SET_ORDER,
                    )
                ) {
                    // Loop through the matches
                    foreach($matches as $match) {
                        // Do the shortcode
                        echo do_shortcode($match[0]);
                    }
                }
            ?>
            <div class="kkamara-form-group">
                <button type="submit">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>