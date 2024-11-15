<?php
// Check if file is accessed directly.
if (!defined("ABSPATH") || !defined("WPINC")) {
    exit("Do not access this file directly.");
}

// Get form fields
$form_fields = get_kkamara_post_fields($post_id);
// Get contact body
$contact_body = $form_fields["kkamara-form-content"];
?>
<style>
    .kkamara-form-message-error {
        color: red;
        border: 1px solid red;
        padding: 10px;
        border-radius: 5px;
    }
    .kkamara-form-message-success {
        color: green;
        border: 1px solid green;
        padding: 10px;
        border-radius: 5px;
    }
</style>
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
            <input
                type="hidden"
                name="kkamara-post-id"
                value="<?php echo esc_attr($post_id); ?>"
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
            <div class="kkamara-form-group">
                <div class="kkamara-form-message">
                    <p id="kkamara-form-message"></p>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    jQuery(document).ready(function ($) {
        $("#kkamara-contact-form-submit").on("submit", function(e) {
            e.preventDefault();
            var form = $(this);
            var data = form.serialize();
            console.log(data);
            // Send Ajax
            $.ajax({
                type: "POST",
                url: "<?php echo admin_url("admin-ajax.php") ?>",
                data: data,
                dataType: "json",
                beforeSend: function (response) {
                    var submitButton = form.find("button[type='submit']");
                    submitButton.prop("disabled", true);
                    // Change the button text
                    submitButton.text("Sending...");
                },
                success: function (response) {
                    console.log(response);
                    if (response.success) {
                        // Reset the form
                        form[0].reset();
                        // Show success message
                        form.find("#kkamara-form-message").html(
                            "<p class='kkamara-form-message-success'>"+response.data.message+"</p>"
                        );
                    } else {
                        // Show error message
                        form.find("#kkamara-form-message").html(
                            "<p class='kkamara-form-message-error'>"+response.data.message+"</p>"
                        );
                    }
                    // Restore the button
                    var submitButton = form.find("button[type='submit']");
                    submitButton.prop("disabled", false);
                    submitButton.text("Submit");
                },
                error: function(response) {
                    console.log(response);
                    // Show error message
                    form.find("#kkamara-form-message").html(
                        "<p class='kkamara-form-message-error'>Something went wrong.</p>"
                    );
                    // Restore the button
                    var submitButton = form.find("button[type='submit']");
                    submitButton.prop("disabled", false);
                    submitButton.text("Submit");
                },
            });
        });
    });
</script>