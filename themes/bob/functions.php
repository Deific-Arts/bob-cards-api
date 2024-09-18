<?php

namespace bob;

use bob\Config as Config;

if (!class_exists('bob\Theme')) {
  class Theme
  {
    private static $instance = null;

    public static function get_instance()
    {
      if (self::$instance === null) {
        self::$instance = new self;
      }

      return self::$instance;
    }

    private function __construct()
    {
      // Includes
      // --------

      $classes = preg_grep('/^([^.])/', scandir(get_template_directory() . '/includes/classes'));
      $postTypes = preg_grep('/^([^.])/', scandir(get_template_directory() . '/includes/post-types'));
      $taxonomies = preg_grep('/^([^.])/', scandir(get_template_directory() . '/includes/taxonomies'));

      foreach ($classes as $class) {
        require_once(get_template_directory() . '/includes/classes/' . $class);
      }

      foreach ($postTypes as $postType) {
        require_once(get_template_directory() . '/includes/post-types/' . $postType);
      }

      foreach ($taxonomies as $taxonomy) {
        require_once(get_template_directory() . '/includes/taxonomies/' . $taxonomy);
      }

      // Hooks
      // -----

      // disable admin bar
      add_filter('show_admin_bar', '__return_false');

      // disable wp-embed
      add_action('wp_footer', array($this, 'disable_wp_embed'));

      // add svg upload
      add_filter('upload_mimes', array($this, 'add_svg_upload'), 10, 1);

      // enqueue scripts and styles
      add_action('admin_enqueue_styles', array($this, 'enqueue_styles'));

      // allows CORS
      add_action('init', array($this, 'add_cors_http_header'));

      // whitelist auth endpoints
      add_filter('jwt_auth_whitelist', array($this, 'whitelist_auth_endpoints'));

      // add user id and role to jwt response
      add_filter('jwt_auth_token_before_dispatch', array($this, 'add_user_id_and_role_to_jwt_response'), 10, 2);
    }

    public static function disable_wp_embed()
    {
      wp_deregister_script('wp-embed');
    }

    public static function add_svg_upload($upload_mimes)
    {
      $upload_mimes['svg'] = 'image/svg+xml';
      $upload_mimes['svgz'] = 'image/svg+xml';
      return $upload_mimes;
    }

    public static function enqueue_styles()
    {
      wp_enqueue_style('admin-css', get_theme_file_uri('/admin.css'));
    }

    public static function add_cors_http_header()
    {
      header("Access-Control-Allow-Origin: *");
    }

    public static function whitelist_auth_endpoints($endpoints)
    {
      $endpoints[] = '/wp-json/bdpwr/v1/reset-password';
      $endpoints[] = '/wp-json/bdpwr/v1/set-password';
      $endpoints[] = '/wp-json/bdpwr/v1/validate-code';
      return $endpoints;
    }

    public static function add_user_id_and_role_to_jwt_response($data, $user)
    {
      $data['user_id'] = $user->data->ID;
      $data['role'] = $user->roles[0];
      return $data;
    }
  }
}
Theme::get_instance();
