<?php

class Functions
{
    // 101 UNDER TESTING
    function count_PostViews($post_ID)
    {

        //Set the name of the Posts Custom Field.
        $count_key = 'post_views_count';

        //Returns values of the custom field with the specified key from the specified post.
        $count = get_post_meta($post_ID, $count_key, true);

        //If the the Post Custom Field value is empty.
        if ($count == '') {
            $count = 0; // set the counter to zero.

            //Delete all custom fields with the specified key from the specified post.
            delete_post_meta($post_ID, $count_key);

            //Add a custom (meta) field (Name/value)to the specified post.
            add_post_meta($post_ID, $count_key, '0');
            return $count . ' View';

            //If the the Post Custom Field value is NOT empty.
        } else {
            $count++; //increment the counter by 1.
            //Update the value of an existing meta key (custom field) for the specified post.
            update_post_meta($post_ID, $count_key, $count);

            //If statement, is just to have the singular form 'View' for the value '1'
            if ($count == '1') {
                return $count . ' View';
            } //In all other cases return (count) Views
            else {
                return $count . ' Views';
            }
        }
    }
}