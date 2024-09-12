<?php

  function add_business_post_type() {
    $labels = array(
      'name' => __('Businesses'),
      'singular_name' => __('Business')
    );

    register_post_type('business', array(
      'labels' => $labels,
      'public' => true,
      'has_archive' => false,
      'show_in_rest' => true,
      'taxonomies' => array('category', 'states'),
      'supports' => array( 'title', 'editor', 'custom-fields')
    ));
  }

  add_action('init', 'add_business_post_type');
