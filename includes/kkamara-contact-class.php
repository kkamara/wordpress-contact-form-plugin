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
            // Check if post has postmeta of contact id
            $contact_id = get_post_meta(
                $post->ID,
                "contact-id",
                true, 
            );
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