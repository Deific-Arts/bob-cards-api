<?php

add_action( 'init', 'add_states_taxonomy', 0);

function add_states_taxonomy() {
    $labels = array(
        'name'                => 'States',
        'singular_name'       => 'State',
        'search_items'        => 'Search all States',
        'all_items'           => 'All States',
        'most_used_items'     => null,
        'parent_item'         => null,
        'parent_item_colon'   => null,
        'edit_item'           => 'Edit States',
        'update_item'         => 'Update States',
        'add_new_item'        => 'Add a State',
        'new_item_name'       => 'New State',
        'menu_name'           => 'States'
    );

    register_taxonomy(
        'states',
        array(''),
        array(
            'hierarchical'    => true,
            'labels'          => $labels,
            'singular_name'   => 'State',
            'show_ui'         => true,
            'query_var'       => false,
            'show_in_rest'    => true
        )
    );
}
