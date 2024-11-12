<?php
// Check if file is accessed directly.
if (!defined("ABSPATH") || !defined("WPINC")) {
    exit("Do not access this file directly.");
}

// Check if get_kkamara_post_fields is already defined
if (!function_exists("get_kkamara_post_fields")) {
    /**
     * Get KKamara form fields
     * @param int $post_id
     * @return array
     */
    function get_kkamara_post_fields($post_id) {
        // Get KKamara contact form
        $kkamara_contact_form = KKamaraContactForm::getInstance();
        // Get KKamara form fields
        return $kkamara_contact_form->getKKamaraFormFields($post_id);
    }
}