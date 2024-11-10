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
                name="name"
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
                name="phone"
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
                name="email"
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
                name="message"
                id="kkamara_email"
                cols="30"
                rows="10"
                placeholder="Enter your message"
            ></textarea>
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
            // Check if post name kkamara-form-content
            if (isset($_POST["kkamara-form-content"])) {
                // Get the value
                $kkamara_form_content = sanitize_textarea_field($_POST["kkamara-form-content"]);
                // Update post meta
                update_post_meta(
                    $post_id,
                    "kkamara_form_content",
                    $kkamara_form_content,
                );
            }
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