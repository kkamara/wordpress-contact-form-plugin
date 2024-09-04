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
                <label for="name">Name</label>
                <input
                    type="text"
                    name="name"
                    id="kkamara_name"
                    placeholder="Enter your name"
                />
            </div>
            <div class="kkamara-form-group">
                <label for="phone">Phone Number</label>
                <input
                    type="text"
                    name="phone"
                    id="kkamara_phone"
                    placeholder="Enter your phone number"
                />
            </div>
            <div class="kkamara-form-group">
                <label for="email">Email</label>
                <input
                    type="text"
                    name="email"
                    id="kkamara_email"
                    placeholder="Enter your email"
                />
            </div>
            <div class="kkamara-form-group">
                <label for="message">Message</label>
                <textarea
                    type="text"
                    name="message"
                    id="kkamara_email"
                    cols="30"
                    rows="10"
                    placeholder="Enter your message"
                ></textarea>
            </div>
        </form>
    </div>
</div>