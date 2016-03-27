<?php
 /*
Divi Commerce
Version: 1.2
Template: Divi
*/
function myprefix_add_post_types_2($post_types) {
    foreach(get_post_types() as $pt) {
        if (!in_array($pt, $post_types) and post_type_supports($pt, 'editor')) {
            $post_types[] = $pt;
        }
    } 
    return $post_types;
}
add_filter('et_builder_post_types', 'myprefix_add_post_types_2');

function myprefix_add_meta_boxes_2() {
    foreach(get_post_types() as $pt) {
        if (post_type_supports($pt, 'editor')) {
            add_meta_box('et_settings_meta_box', __('Divi Custom Post Settings', 'Divi'), 'et_single_settings_meta_box', $pt, 'side', 'high');
        }
    } 
    return $post_types;
}
add_action('add_meta_boxes', 'myprefix_add_meta_boxes_2');
?>