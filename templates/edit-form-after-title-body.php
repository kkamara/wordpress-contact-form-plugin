<?php
// Check if file is accessed directly.
if (!defined("ABSPATH") || !defined("WPINC")) {
    exit("Do not access this file directly.");
}
?>
<style>
    .ui-state-active,
    .ui-widget-content .ui-state-active,
    .ui-widget-header .ui-state-active,
    a.ui-button:active,
    .ui-button:active,
    .ui-button.ui-state-active:hover {
        border: 1px solid #333;
        background: #333;
        font-weight: normal;
        color: #000;
    }
    #kkamara-form-content {
        width: 100%;
        height: 300px;
    }
    #kkamara-mail-body {
        height: 300px;
        width: 100%;
    }
    #kkamara-mail-additional-headers {
        height: 70px;
        width: 100%;
    }
    .kkamara-form input {
        width: 100%;
    }
    .kkamara-contact-messages {
        display: flex;
        flex-direction: column;
        gap: 2;
        margin-bottom: 10px;
    }
    .kkamara-contact-messages label {
        font-weight: normal;
    }
    .kkamara-contact-messages input {
        width: 100%;
        padding: 8px;
        outline: none;
    }
</style>
<div class="kkamara-contact-tabs">
    <div id="kkamara-contact-tabs-content">
        <ul>
            <li><a href="#tabs-form">Form</a></li>
            <li><a href="#tabs-mail">Mail</a></li>
            <li><a href="#tabs-messages">Messages</a></li>
        </ul>
        <div id="tabs-form">
            <h3>Form</h3>
            <p>
                You can edit the form template here.
            </p>
            <div class="kkamara-contact-edit-area">
                <textarea
                    name="kkamara-form-content"
                    id="kkamara-form-content"
                    placeholder="Enter form content."
                ><?php echo esc_html(get_post_meta($post_id, "kkamara_form_content", true)); ?></textarea>
            </div>
        </div>
        <div id="tabs-mail">
            <h3>Mail</h3>
            <p>
                You can edit the mail template here.
            </p>
            <p>
                In the following fields, you can use these mail-tags:
                <br />
                <code>[your-name] [your-email] [your-subject] [your-message]</code>
            </p>
            <div class="kkamara-mail-settings">
                <table class="form-table kkamara-form">
                    <tr>
                        <th>
                            <label for="kkamara-mail-to">To:</label>
                        </th>
                        <td>
                            <input
                                type="text"
                                class="regular-text"
                                name="kkamara-mail-to"
                                id="kkamara-mail-to"
                                value="[_site_admin_mail]"
                            >
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="kkamara-mail-from">From:</label>
                        </th>
                        <td>
                            <input
                                type="text"
                                class="regular-text"
                                name="kkamara-mail-from"
                                id="kkamara-mail-from"
                                value="[_site_title] <usermail@gmail.com>"
                            >
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="kkamara-mail-subject">Subject:</label>
                        </th>
                        <td>
                            <input
                                type="text"
                                class="regular-text"
                                name="kkamara-mail-subject"
                                id="kkamara-mail-subject"
                                value="[_site_title] - [your-subject]"
                            >
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="kkamara-mail-additional-headers">Additional Headers:</label>
                        </th>
                        <td>
                            <textarea
                                type="text"
                                name="kkamara-mail-additional-headers"
                                id="kkamara-mail-additional-headers"
                                placeholder="Enter additional headers"
                            >Reply-To: [your-email]</textarea>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="kkamara-mail-body">Message Body:</label>
                        </th>
                        <td>
                            <textarea
                                type="text"
                                name="kkamara-mail-body"
                                id="kkamara-mail-body"
                                placeholder="Enter mail body"
                            >From: [your-name] [your-email]
Subject: [your-subject]

Message Body:
[your-message]

--
This is a notification that a contact form was submitted on your website ([_site_title] [_site_url]).</textarea>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div id="tabs-messages">
            <h3>Messages</h3>
            <p>
                You can edit messages used in various situations here.
            </p>
            <div class="kkamara-contact-messages">
                <label for="kkamara-message-success">Success message:</label>
                <input
                    type="text"
                    name="kkamara-message-success"
                    id="kkamara-message-success"
                    placeholder="Enter success message"
                />
            </div>
            <div class="kkamara-contact-messages">
                <label for="kkamara-message-error">Error message:</label>
                <input
                    type="text"
                    name="kkamara-message-error"
                    id="kkamara-message-error"
                    placeholder="Enter error message"
                />
            </div>
        </div>
    </div>
</div>
<script>
    jQuery(document).ready(function ($) {
        // Init kkamara-contact-tabs-content
        $("#kkamara-contact-tabs-content").tabs();
    });
</script>