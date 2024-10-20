<?php

namespace bob;

use WP_Error;
use WP_REST_Response;

class Endpoints
{
  public static function init()
  {
    add_action('rest_api_init', array(get_class(), 'add_user_registration'));
    add_action('rest_api_init', array(get_class(), 'add_profile_image'));
    add_action('rest_api_init', array(get_class(), 'add_media_upload'));
    add_action('rest_api_init', array(get_class(), 'add_media_delete'));
    add_action('rest_api_init', array(get_class(), 'add_change_password'));
    add_action('rest_api_init', array(get_class(), 'add_get_business_by_fsq_id'));
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

  public static function add_profile_image()
  {
    register_rest_route('bob/v1', 'profile-image', array(
      'methods' => 'POST',
      'callback' => function ($request) {
        $image_src = $request->get_param('image_src');
        $image_id = $request->get_param('image_id');
        $user_id = $request->get_param('user_id');
        $user = get_user_by('id', $user_id);

        if (empty($user)) {
          return new WP_Error('error', 'User does not exist', array('status' => 400));
        }

        if (empty($user)) {
          return new WP_Error('error', 'image source is empty', array('status' => 400));
        }

        update_user_meta($user_id, 'bob_profile_image', $image_src);
        update_user_meta($user_id, 'bob_profile_image_id', $image_id);

        return new WP_REST_Response(array(
          'status' => 'success',
          'user_id' => $user_id,
          'image_src' => $image_src,
          "message" => "Profile image uploaded successfully"
        ), 200);
      },
      'permission_callback' => '__return_true',
    ));
  }

  public static function add_media_upload()
  {
    register_rest_route('bob/v1', 'media-upload', array(
      'methods' => 'POST',
      'callback' => function ($request) {
        return get_class()::upload_file();
      }
    ));
  }

  public static function add_media_delete()
  {
    register_rest_route('bob/v1', 'media-delete', array(
      'methods' => 'POST',
      'callback' => function ($request) {
        $user_id = $request->get_param('user_id');
        $image_id = $request->get_param('image_id');

        $media_post = get_post($image_id);

        if (empty($media_post)) {
          return new WP_Error('error', 'Could not find the media to delete.', array('status' => 400));
        }

        if (gettype($user_id) !== 'string') {
          return new WP_Error('error', 'User ID must be a present.', array('status' => 400));
        }

        if ($media_post->post_author !== $user_id) {
          return new WP_Error('error', 'User does not have permission to delete this image' . $media_post->post_author, array('status' => 403));
        }

        wp_delete_attachment($image_id, true);

        return new WP_REST_Response(array(
          'status' => 'success',
          "message" => "Media has been deleted."
        ), 200);
      }
    ));
  }

  public static function add_change_password()
  {
    register_rest_route('bob/v1', 'change-password', array(
      'methods' => 'POST',
      'callback' => function ($request) {
        $user_id = $request->get_param('user_id');
        $user = get_user_by('id', $user_id);

        $current_password = $request->get_param('current_password');
        $new_password = $request->get_param('new_password');

        if (empty($user)) {
          return new WP_REST_Response(array(
            'status' => 'error',
            "message" => 'User does not exist'
          ), 400);
        }

        if (empty($current_password)) {
          return new WP_REST_Response(array(
            'status' => 'error',
            "message" => 'Please enter current password'
          ), 400);
        }

        if (empty($new_password)) {
          return new WP_REST_Response(array(
            'status' => 'error',
            "message" => 'Please enter new password'
          ), 400);
        }

        if (wp_check_password($current_password, $user->data->user_pass)) {
          wp_set_password($new_password, $user_id);
          return new WP_REST_Response(array(
            'status' => 'success',
            "message" => 'Password updated successfully'
          ), 200);
        } else {
          return new WP_REST_Response(array(
            'status' => 'error',
            "message" => 'Incorrect current password'
          ), 400);
        }
      },
      'permission_callback' => '__return_true',
    ));
  }

  public static function add_get_business_by_fsq_id()
  {
    register_rest_route('bob/v1', '/get-business-by-fsq-id', array(
      'methods'  => 'GET',
      'callback' =>  function ($request) {
        $meta_key = $request->get_param('meta_key');
        $meta_value = $request->get_param('meta_value');

        $args = array(
          'post_type'      => 'business',
          'meta_query'     => array(
            array(
              'key'     => $meta_key,
              'value'   => $meta_value,
              'compare' => '=',
            ),
          ),
        );

        $businesses = get_posts($args);

        if (empty($businesses)) {
          return new WP_REST_Response(array(
            'status' => 'success',
            "message" => 'This business is unique.'
          ), 200);
        }

        return new WP_REST_Response(array(
          'status' => 'error',
          "message" => 'This business has already been added.'
        ), 400);
      },
    ));
  }

  public static function upload_file()
  {
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    //upload only images and files with the following extensions
    $file_extension_type = array('jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp', 'tiff', 'tif', 'ico', 'zip', 'pdf', 'docx');
    $file_extension = strtolower(pathinfo($_FILES['async-upload']['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $file_extension_type)) {
      return wp_send_json(
        array(
          'success' => false,
          'data'    => array(
            'message'  => __('The uploaded file is not a valid file. Please try again.'),
            'filename' => esc_html($_FILES['async-upload']['name']),
            'extension' => $file_extension
          ),
        )
      );
    }

    $attachment_id = media_handle_upload('async-upload', null, []);

    if (is_wp_error($attachment_id)) {
      return wp_send_json(
        array(
          'success' => false,
          'data'    => array(
            'message'  => $attachment_id->get_error_message(),
            'filename' => esc_html($_FILES['async-upload']['name']),
          ),
        )
      );
    }

    if (isset($post_data['context']) && isset($post_data['theme'])) {
      if ('custom-background' === $post_data['context']) {
        update_post_meta($attachment_id, '_wp_attachment_is_custom_background', $post_data['theme']);
      }

      if ('custom-header' === $post_data['context']) {
        update_post_meta($attachment_id, '_wp_attachment_is_custom_header', $post_data['theme']);
      }
    }

    $attachment = wp_prepare_attachment_for_js($attachment_id);
    if (!$attachment) {
      return wp_send_json(
        array(
          'success' => false,
          'data'    => array(
            'message'  => __('Image cannot be uploaded.'),
            'filename' => esc_html($_FILES['async-upload']['name']),
          ),
        )
      );
    }

    return wp_send_json(
      array(
        'success' => true,
        'data'    => $attachment,
      )
    );
  }
}
Endpoints::init();
