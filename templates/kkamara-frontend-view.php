<?php
// Check if file is accessed directly.
if (!defined("ABSPATH") || !defined("WPINC")) {
    exit("Do not access this file directly.");
}
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
            <div class="kkamara-form-group">
                <button type="submit">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>