<?php

namespace bob;

class Endpoints
{
  public static function init()
  {
    add_action('rest_api_init', array(get_class(), 'add_user_registration'));
  }

  public static function add_user_registration()
  {
    register_rest_route('bob/v1', 'register', array(
      'methods' => 'POST',
      'callback' => function ($request) {
        // Reference: https://developer.wordpress.org/reference/classes/wp_rest_request/
        $userData = wp_create_user($request->get_param('user_name'), $request->get_param('user_pass'), $request->get_param('user_email'));

        if (is_int($userData)) {
          return array(
            'status' => 'ok',
            'message' => 'Successfully created ' . $request->get_param('user_name') . '.',
            'data' => array(
              'user_id' => $userData,
              'user_name' => $request->get_param('user_name'),
              'user_email' => $request->get_param('user_email'),
              'user_pass' => $request->get_param('user_pass'),
            )
          );
        } else {
          return array(
            'status' => 'error',
            'message' => 'There was a problem creating the user.',
            'data' => $userData
          );
        }
      },
      'permission_callback' => function () {
        return true;
      }
    ));
  }
}
Endpoints::init();
