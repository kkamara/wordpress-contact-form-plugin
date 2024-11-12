<?php
/**
 * Plugin Name: KKamara Contact
 * Plugin URI:  https://github.com/kkamara/wordpress-contact-plugin
 * Author:      Kelvin Kamara
 * Author URI:  https://www.kelvinkamara.com
 * Description: A simple contact form plugin for WordPress.
 * Version:     1.0.0
 * License:     BSD-3-Clause
 * License URL: https://opensource.org/license/bsd-3-clause
 * text-domain: kkamara-contact
*/

// Check if file is accessed directly.
if (!defined("ABSPATH") || !defined("WPINC")) {
    exit("Do not access this file directly.");
}

// Define plugin constants.
define("KKAMARA_CONTACT_PLUGIN_VERSION", time());
// Plugin file.
define("KKAMARA_CONTACT_PLUGIN_FILE", __FILE__);
// Plugin directory.
define(
    "KKAMARA_CONTACT_PLUGIN_DIR",
    dirname(KKAMARA_CONTACT_PLUGIN_FILE),
);
// Plugin URL.
define(
    "KKAMARA_CONTACT_PLUGIN_URL",
    plugins_url("", KKAMARA_CONTACT_PLUGIN_FILE),
);
// Assets URL
define(
    "KKAMARA_CONTACT_PLUGIN_ASSETS_URL",
    KKAMARA_CONTACT_PLUGIN_URL . "/assets",
);
// Shortcode prefix
define(
    "KKAMARA_CONTACT_PLUGIN_SHORT_CODE_PREFIX",
    "kkamara_contact_"
);

// Check if KKamaraContactForm exists
if (!defined("KKamaraContactForm")) {
    // Include the class file
    include_once KKAMARA_CONTACT_PLUGIN_DIR."/includes/kkamara-contact-class.php";
    // Create an instance
    new KKamaraContactForm();
    // Include helper file
    include_once KKAMARA_CONTACT_PLUGIN_DIR."/includes/helper.php";
}