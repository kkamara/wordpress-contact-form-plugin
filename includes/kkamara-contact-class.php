<?php
// Check if file is accessed directly.
if (!defined("ABSPATH") || !defined("WPINC")) {
    exit("Do not access this file directly.");
}

/**
 * KKamara Contact Form
 * @author Kelvin Kamara
 * @link https://www.kelvinkamara.com
 * @since 1.0.0
 */
class KKamaraContactForm {
    /**
     * KKamara Contact Form instance
     * @var KKamaraContactForm
     */
    private static $instance;

    /**
     * Get KKamaraContactForm instance
     * @return KKamaraContactForm
     */
    public static function getInstance() {
        // Check if instance is null
        if (self::$instance === null) {
            self::$instance = new self();
        }
        // Return instance
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        // Init the plugin
        // Add action init
        add_action(
            "init",
            array($this, "registerPostType"),
        );
        // Add action edit_form_after_title
        add_action(
            "edit_form_after_title",
            array($this, "editFormAfterTitle"),
            10,
        );
        // Add action edit_form_after_title for body
        add_action(
            "edit_form_after_title",
            array($this, "editFormAfterTitleBody"),
            11,
        );
        // Add admin script
        add_action(
            "admin_enqueue_scripts",
            array($this, "adminEnqueueScripts"),
        );
        // Init
        add_action(
            "init",
            array($this, "init"),
        );
        // Save post action
        add_action(
            "save_post",
            array($this, "savePostGeneratedId"),
            10,
            3,
        );
        // Save post
        add_action(
            "save_post",
            array($this, "savePostOthersData"),
            11,
            3,
        );
        // Create shortcode
        add_shortcode(
            "kkamara-contact-form",
            array($this, "kkamaraContactFormShortcode"),
        );
        // Column table
        add_filter(
            "manage_kkamara_contact_posts_columns",
            array($this, "addCustomColumns"),
        );
        // Column table content
        add_action(
            "manage_kkamara_contact_posts_custom_column",
            array($this, "addCustomColumnsContent"),
            10,
            2,
        );
        // Add frontend assets
        add_action(
            "wp_enqueue_scripts",
            array($this, "frontendAssetsScripts"),
        );
        // Add multiple shortcodes
        $this->addMultipleShortcodes();
        // Add Ajax kkamara_send_message
        add_action(
            "wp_ajax_kkamara_send_message",
            array($this, "kkamaraSendMessage"),
        ); // Send with user login
        add_action(
            "wp_ajax_nopriv_kkamara_send_message",
            array($this, "kkamaraSendMessage"),
        ); // Send without user login
    }

    /**
     * kkamaraSendMessage
     */
    public function kkamaraSendMessage() {
        try {
            // Get the nonce data
            if (!wp_verify_nonce($_POST["nonce"], "kkamara-contact-message")) {
                wp_send_json_error([
                    "message" => "Invalid nonce, please reload the page.",
                ]);
            }
            // Get the form field
            $kkamara_subject = sanitize_text_field($_POST["kkamara-subject"]);
            $kkamara_name = sanitize_text_field($_POST["kkamara-name"]);
            $kkamara_email = sanitize_text_field($_POST["kkamara-email"]);
            $kkamara_phone = sanitize_text_field($_POST["kkamara-phone"]);
            $kkamara_message = sanitize_textarea_field($_POST["kkamara-message"]);
            // Get the post id
            $post_id = sanitize_text_field($_POST["kkamara-post-id"]);

            // Format the message
            $response = $this->kkamaraMessageFormat([
                "subject" => $kkamara_subject,
                "name" => $kkamara_name,
                "email" => $kkamara_email,
                "phone" => $kkamara_phone,
                "message" => $kkamara_message,
                "post_id" => $post_id,
            ]);

            if ($response["response"]) {
                wp_send_json_success([
                    "message" => $response["form_fields"]["kkamara-message-success"],
                ]);
            } else {
                wp_send_json_error([
                    "message" => $response["form_fields"]["kkamara-message-error"],
                ]);
            }
        } catch (\Exception $e) {
            // Log to debug
            $errorMessage = $e->getMessage();
            error_log(
                "KKamara contact error: ".$errorMessage,
            );
            wp_send_json_error([
                "message" => "Something went wrong: ".$errorMessage,
            ]);
        }
    }

    /**
     * KKamara Message Formatter
     * @param array $args
     * @return array
     */
    public function kkamaraMessageFormat($args): array {
        try {
            // Extract
            extract($args); // Create variables from the array
            // Get saved form fields
            $form_fields = $this->getKKamaraFormFields($post_id);
            // Site title
            $site_title = get_option("blogname");
            // Site URL
            $site_url = site_url();
            // Admin email
            $admin_email = get_option("admin_email");

            // Prepare replacements
            $replacements = [
                "[your-subject]" => $subject,
                "[your-name]" => $name,
                "[your-email]" => $email,
                "[your-phone]" => $phone,
                "[your-message]" => $message,
                "[_site_title]" => $site_title,
                "[_site_url]" => $site_url,
                "[_site_admin_email]" => $admin_email,
            ];

            // Loop through form fields
            foreach($form_fields as $key => $value) {
                // Skip if key match kkamara-form-content
                if ($key === "kkamara-form-content") {
                    continue; // Skip
                }
                // Replace the value
                $form_fields[$key] = strtr($value, $replacements);
            }

            // Send mail
            $response = wp_mail(
                $form_fields["kkamara-mail-to"],
                $form_fields["kkamara-mail-subject"],
                $form_fields["kkamara-mail-body"],
                $form_fields["kkamara-mail-additional-headers"],
            );

            // Return message
            return [
                "response" => $response,
                "form_fields" => $form_fields,
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get the form fields
     * @param int $post_id
     */
    public function getKKamaraFormFields($post_id): array {
        // Create default array
        $kkamara_default_array = [
            "kkamara-form-content" => "[kkamara_contact_subject]

[kkamara_contact_name]

[kkamara_contact_phone]

[kkamara_contact_email]

[kkamara_contact_message]",
            "kkamara-mail-to" => "[_site_admin_mail]",
            "kkamara-mail-from" => "[_site_title]",
            "kkamara-mail-subject" => "[_site_title] - [your-subject]",
            "kkamara-mail-additional-headers" => "Reply-To: [your-email]",
            "kkamara-mail-body" => "From: [your-name] [your-email]
Subject: [your-subject]

Message Body:
[your-message]

--
This is a notification that a contact form was submitted on your website ([_site_title] [_site_url]).",
            "kkamara-message-success" => "Message sent!",
            "kkamara-message-error" => "Something went wrong.",
        ];

        try {
            // Get saved form fields
            $form_fields = get_post_meta(
                $post_id,
                "kkamara_form_fields",
                true,
            );
            // Check if form fields is empty, if empty return default form fields
            if (empty($form_fields)) {
                // Return default form fields
                return $kkamara_default_array;
            }
            // Return form fields
            return $form_fields;
        } catch (\Exception $e) {
            // Log to debug
            error_log("KKamara contact error: ".$e->getMessage());
            return [];
        }
    }

    /**
     * addMultipleShortcodes
     */
    public function addMultipleShortcodes() {
        // Shortcodes
        $shortcodes = [
            "name" => "nameShortCode",
            "phone" => "phoneShortCode",
            "email" => "emailShortCode",
            "message" => "messageShortCode",
            "subject" => "subjectShortCode",
        ];
        // Loop through Shortcodes
        foreach ($shortcodes as $shortcode => $callback) {
            // Add Shortcode
            add_shortcode(
                KKAMARA_CONTACT_PLUGIN_SHORT_CODE_PREFIX.$shortcode,
                array($this, $callback),
            );
        }
    }

    public function nameShortCode($attr) {
        ob_start();
        ?>
        <div class="kkamara-form-group">
            <label for="name">Name</label>
            <input
                type="text"
                name="kkamara-name"
                id="kkamara_name"
                placeholder="Enter your name"
            />
        </div>
        <?php
        return ob_get_clean();
    }

    public function phoneShortCode($attr) {
        ob_start();
        ?>
        <div class="kkamara-form-group">
            <label for="phone">Phone Number</label>
            <input
                type="text"
                name="kkamara-phone"
                id="kkamara_phone"
                placeholder="Enter your phone number"
            />
        </div>
        <?php
        return ob_get_clean();
    }

    public function emailShortCode($attr) {
        ob_start();
        ?>
        <div class="kkamara-form-group">
            <label for="email">Email</label>
            <input
                type="text"
                name="kkamara-email"
                id="kkamara_email"
                placeholder="Enter your email"
            />
        </div>
        <?php
        return ob_get_clean();
    }

    public function messageShortCode($attr) {
        ob_start();
        ?>
        <div class="kkamara-form-group">
            <label for="message">Message</label>
            <textarea
                type="text"
                name="kkamara-message"
                id="kkamara_email"
                cols="30"
                rows="10"
                placeholder="Enter your message"
            ></textarea>
        </div>
        <?php
        return ob_get_clean();
    }

    public function subjectShortCode($attr) {
        ob_start();
        ?>
        <div class="kkamara-form-group">
            <label for="message">Subject</label>
            <input
                type="text"
                name="kkamara-subject"
                id="kkamara_subject"
                placeholder="Enter your subject"
            />
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * frontendAssetsScripts
     */
    public function frontendAssetsScripts() {
        // Add styles
        wp_enqueue_style(
            "kkamara-frontend-assets",
            KKAMARA_CONTACT_PLUGIN_ASSETS_URL .
                "/css/frontend-styles.css",
            [],
            KKAMARA_CONTACT_PLUGIN_VERSION,
        );
    }

    /**
     * editFormAfterTitleBody
     */
    public function editFormAfterTitleBody($post) {
        if ($post->post_type === "kkamara_contact") {
            $post_id = $post->ID;
            // ob start
            ob_start();
            // Include the file
            include KKAMARA_CONTACT_PLUGIN_DIR .
                "/templates/edit-form-after-title-body.php";
            // Echo the output
            echo ob_get_clean();
        }
    }

    /**
     * addCustomColumns
     */
    public function addCustomColumns($columns) {
        // Unset date
        unset($columns["date"]);
        // Add Shortcode
        $columns["shortcode"] = "Shortcode";
        // Add author
        $columns["author"] = "Author";
        // Add date to the be the last column
        $columns["date"] = "Date";
        // Return columns
        return $columns;
    }

    /**
     * addCustomColumnsContent
     */
    public function addCustomColumnsContent($column, $post_id) {
        // Check for Shortcode
        switch ($column) {
            case "shortcode":
                printf(
                    '[kkamara-contact-form id="%s" title="%s"]',
                    $post_id,
                    get_the_title($post_id),
                );
                break;
            case "author":
                echo esc_html(get_the_author_meta(
                    "display_name",
                    get_post_field("post_author", $post_id),
                ));
                break;
        }
    }

    /**
     * kkamaraContactFormShortcode
     */
    public function kkamaraContactFormShortcode($atts) {
        // Extract
        extract(shortcode_atts(
            [
                "id" => "",
                "title" => "",
            ],
            $atts,
        ));
        // Check for Id
        if (!$id) {
            // Do nothing
            return;
        }
        // Check for title
        if (!$title) {
            $title = "KKamara Contact Form";
        }
        // Get the post ID
        $postData = $this->getPostDataByGeneratedId($id);
        // post id
        $post_id = $postData->post_id;
        // Get the template
        ob_start();
        include_once KKAMARA_CONTACT_PLUGIN_DIR .
            "/templates/kkamara-frontend-view.php";
        $htmlView = ob_get_clean();
        return $htmlView;
    }

    /**
     * savePostGeneratedId
     */
    public function savePostGeneratedId($post_id, $post, $update) {
        // Check if post type is kkamara_contact
        if ($post->post_type === "kkamara_contact") {
            // Check for checkGeneratedId
            if ($this->checkGeneratedId($post_id)) {
                return;
            }
            // Generate ID
            $generated_id = substr(
                md5($post_id . time()),
                0,
                7,
            );
            // Table
            global $wpdb;
            $table = $wpdb->prefix . "kkamara_contacts";
            // Insert
            $wpdb->insert(
                $table,
                [
                    "post_id" => $post_id,
                    "generated_id" => $generated_id,
                ]
            );
        }
    }

    /**
     * savePostOthersData
     */
    public function savePostOthersData($post_id, $post, $update) {
        // Check if post type is kkamara_contact
        if ($post->post_type === "kkamara_contact") {
            // Collect all form fields
            $form_fields = []; // Initialize the form_fields array
            // Loop through $_POST
            foreach($_POST as $key => $value) {
                // If key is matching kkamara-
                if (strpos($key, "kkamara-") !== false) {
                    // Check if key is matching kkamara-form-content
                    switch($key) {
                        case "kkamara-form-content":
                            // Pass the key to the form_fields array
                            $form_fields[$key] = sanitize_textarea_field($value);
                            break;
                        case "kkamara-mail-additional-headers":
                            // Pass the key to the form_fields array
                            $form_fields[$key] = sanitize_textarea_field($value);
                            break;
                        case "kkamara-mail-body":
                            // Pass the key to the form_fields array
                            $form_fields[$key] = sanitize_textarea_field($value);
                            break;
                        default:
                            // Add to form_fields
                            $form_fields[$key] = sanitize_text_field($value);
                            break;
                    }
                }
            }
            // Update post meta
            update_post_meta(
                $post_id,
                "kkamara_form_fields",
                $form_fields,
            );
        }
    }

    /**
     * Init
     */
    public function init() {
        try {
            global $wpdb;
            // Table
            $table = $wpdb->prefix . "kkamara_contacts";
            // Check if table exists
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
                // SQL
                $sql = "CREATE TABLE $table (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    post_id INT(11) NOT NULL,
                    generated_id VARCHAR(100) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id)
                )";
                // Create table
                require_once(ABSPATH . "wp-admin/includes/upgrade.php");
                dbDelta($sql);
            }
        } catch (\Exception $e) {
            error_log(
                "KKamara contact error: " .
                    $e->getMessage(),
            );
        }
    }

    /**
     * Check for generated_id
     * @param int $post_id,
     * @return mixed|bool
     */
    public function checkGeneratedId($post_id) {
        global $wpdb;
        // Table
        $table = $wpdb->prefix."kkamara_contacts";
        // SQL
        $sql = $wpdb->prepare(
            "SELECT * FROM $table WHERE post_id = %d",
            $post_id,
        );
        // Get results
        $results = $wpdb->get_results($sql);
        // Check if results
        if ($results) {
            return $results[0];
        }
        return false;
    }

    /**
     * Get post data by generated ID
     * @param int $generated_id,
     * @return mixed|bool
     */
    public function getPostDataByGeneratedId($generated_id) {
        global $wpdb;
        // Table
        $table = $wpdb->prefix."kkamara_contacts";
        // SQL
        $sql = $wpdb->prepare(
            "SELECT * FROM $table WHERE generated_id = %s",
            $generated_id,
        );
        // Get results
        $results = $wpdb->get_results($sql);
        // Check if results
        if ($results) {
            return $results[0];
        }
        return false;
    }


    /**
     * adminEnqueueScripts
     */
    public function adminEnqueueScripts($hook) {
        if ($hook === "post-new.php" || $hook === "post.php") {
            global $post;
            if ($post->post_type === "kkamara_contact") {
                // Enqueue style
                wp_enqueue_style(
                    "kkamara-contact-admin-style",
                    KKAMARA_CONTACT_PLUGIN_ASSETS_URL .
                        "/css/kkamara-styles.css",
                    [],
                    KKAMARA_CONTACT_PLUGIN_VERSION,
                );
                // JQuery UI kkamara CSS
                wp_enqueue_style(
                    "jquery-ui-kkamara",
                    KKAMARA_CONTACT_PLUGIN_ASSETS_URL .
                        "/css/jquery-ui.css",
                );
                // JQuery UI kkamara JS
                wp_enqueue_script(
                    "jquery-ui-kkamara",
                    KKAMARA_CONTACT_PLUGIN_ASSETS_URL .
                        "/js/jquery-ui.js",
                    ["jquery"],
                    KKAMARA_CONTACT_PLUGIN_VERSION,
                    true,
                );
            }
        }
    }

    /**
     * editFormAfterTitle
     */
    public function editFormAfterTitle($post) {
        // Check if post type is kkamara_contact
        if ($post->post_type === "kkamara_contact") {
            // Check for checkGeneratedId
            $checkForId = $this->checkGeneratedId(
                $post->ID
            );
            if (!$checkForId) {
                // Do nothing
                return;
            }
            // ob start
            ob_start();
            // Include the file
            include_once KKAMARA_CONTACT_PLUGIN_DIR .
                "/templates/edit-form-after-title.php";
            // Echo the output
            echo ob_get_clean();
        }
    }

    /**
     * Register post type
     */
    public function registerPostType() {
        $args = [
            "label" => "KKamara Contact",
            "labels" => [
                "name" => "Contact",
                "singular_name" => "Contact",
                "menu_name" => "KKamara Contact",
                "name_admin_bar" => "Contact",
                "add_new" => "Add New Contact",
                "add_new_item" => "Add New Contact",
                "new_item" => "New Contact",
                "edit_time" => "Edit Contact",
                "view_item" => "View Contact",
                "all_items" => "All Contact",
                "search_items" => "Search Contact",
                "parent_item_colon" => "Parent Contact:",
                "not_found" => "No Contact found.",
                "not_found_in_trash" => "No Contact found in Trash.",
            ],
            "description" => "KKamara Contact for WordPress",
            "show_ui" => true,
            "supports" => ["title"],
            "menu_icon" => "dashicons-email-alt",
        ];

        register_post_type("kkamara_contact", $args);
    }
}