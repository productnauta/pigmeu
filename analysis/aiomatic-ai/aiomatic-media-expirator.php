<?php
defined('ABSPATH') or die();
add_filter('attachment_fields_to_edit', 'aiomatic_attachment_expiration_field', 10, 2);
function aiomatic_attachment_expiration_field($form_fields, $post) 
{
    $values = get_post_meta($post->ID, 'expiry_check', true);
    if(is_array($values))
    {
        $values = $values[0];
    }
    if ($values == "1") 
    {
        $values_val = "checked";
    } 
    else 
    {
        $values_val = "";
    }
    $form_fields['expiry_check'] = array(
        'label' => esc_html__('Enable Expiration', 'aiomatic-automatic-ai-content-writer'),
        'input' => 'html',
        'html'  => '<input type="checkbox" value="1" '.$values_val.' name="attachments['.$post->ID.'][expiry_check]" id="attachments-'.$post->ID.'-expiry_check" />',
        'value' => get_post_meta($post->ID, 'expiry_check', true),
        'helps' => esc_html__('Set a date on which the image will be automatically deleted (by Aiomatic)', 'aiomatic-automatic-ai-content-writer')
    );
    $form_fields['expiry_date'] = array(
        'label' => esc_html__('Expiration Date', 'aiomatic-automatic-ai-content-writer'),
        'input' => 'text',
        'value' => get_post_meta($post->ID, 'expiry_date', true),
        'helps' => esc_html__('Date format: YYYY-MM-DD, +3 days, +1 day', 'aiomatic-automatic-ai-content-writer')
    );
    return $form_fields;
}

add_filter('attachment_fields_to_save', 'aiomatic_attachment_expiration_field_save', 10, 2);
function aiomatic_attachment_expiration_field_save($post, $attachment) {
    if (isset($attachment['expiry_check'])) {
        update_post_meta($post['ID'], 'expiry_check', $attachment['expiry_check']);
    } else {
        update_post_meta($post['ID'], 'expiry_check', '0');
    }
    if (isset($attachment['expiry_date']))
    {
        $mydate = strtotime($attachment['expiry_date']);
        if($mydate !== false)
        {
            $tdate = date('Y-m-d', $mydate);
            update_post_meta($post['ID'], 'expiry_date', $tdate);
        }
    } 

    return $post;
}

add_action('aiomatic_expired_post_delete', 'aiomatic_delete_expired_posts');
function aiomatic_delete_expired_posts() 
{
    $todays_date = date("Y-m-d");
    $paged = 1;
    $per_page = 100; 
    do {
        $args = array(
            'post_status'    => 'any',
            'post_type'      => 'attachment',
            'posts_per_page' => $per_page,
            'paged'          => $paged,
            'meta_query'     => array(
                array(
                    'key'     => 'expiry_date',
                    'value'   => $todays_date,
                    'type'    => 'DATE',
                    'compare' => '<',
                ),
                array(
                    'key'   => 'expiry_check',
                    'value' => 1,
                ),
            ),
        );
        $posts = new WP_Query($args);
        if ($posts->have_posts()) {
            while ($posts->have_posts()) {
                $posts->the_post();
                wp_delete_post(get_the_ID());
            }
        }
        wp_reset_postdata(); 
        $paged++;
    } while ($posts->have_posts());
}

add_action('init', 'aiomatic_register_daily_post_delete_event');
function aiomatic_register_daily_post_delete_event() 
{
    if (!wp_next_scheduled('aiomatic_expired_post_delete')) 
    {
        wp_schedule_event(time(), 'daily', 'aiomatic_expired_post_delete');
    }
}
?>