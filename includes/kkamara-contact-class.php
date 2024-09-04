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
                        "/css/kkamara-style.css",
                    [],
                    KKAMARA_CONTACT_PLUGIN_VERSION,
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