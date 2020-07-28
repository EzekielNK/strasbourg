<?php
/**
  Plugin Name: Blog Designer
  Plugin URI: https://wordpress.org/plugins/blog-designer/
  Description: To make your blog design more pretty, attractive and colorful.
  Version: 1.8.10
  Author: Solwin Infotech
  Author URI: https://www.solwininfotech.com/
  Requires at least: 4.0
  Tested up to: 5.1.1

  Text Domain: blog-designer
  Domain Path: /languages/
 */
/**
 * Exit if accessed directly
 */
if (!defined('ABSPATH')) {
    exit;
}

define('BLOGDESIGNER_URL', plugins_url() . '/blog-designer');
define('BLOGDESIGNER_DIR', WP_PLUGIN_DIR . '/' . plugin_basename(dirname(__FILE__)));
register_activation_hook(__FILE__, 'wp_blog_designer_plugin_activate');
register_deactivation_hook(__FILE__, 'wp_blog_designer_update_optin');
add_action('admin_menu', 'wp_blog_designer_add_menu');
add_action('admin_init', 'wp_blog_designer_redirection', 1);
add_action('admin_init', 'wp_blog_designer_reg_function', 5);
add_action('admin_enqueue_scripts', 'wp_blog_designer_admin_stylesheet', 7);
add_action('admin_init', 'wp_blog_designer_save_settings', 10);
add_action('wp_enqueue_scripts', 'wp_blog_designer_front_stylesheet');
add_action('admin_init', 'wp_blog_designer_admin_scripts');
add_action('wp_head', 'wp_blog_designer_stylesheet', 20);
add_shortcode('wp_blog_designer', 'wp_blog_designer_views');
add_action('admin_enqueue_scripts', 'wp_blog_designer_enqueue_color_picker',9);
add_action('wp_head', 'bd_ajaxurl', 5);
add_action('wp_ajax_nopriv_bd_get_page_link', 'bd_get_page_link');
add_action('wp_ajax_bd_get_page_link', 'bd_get_page_link');
add_action('wp_ajax_bd_closed_bdboxes', 'bd_closed_bdboxes');
add_action('wp_ajax_bd_template_search_result', 'bd_template_search_result');
add_action('wp_ajax_bd_create_sample_layout', 'bd_create_sample_layout');
add_filter('excerpt_length', 'wp_blog_designer_excerpt_length', 999);
add_action('plugins_loaded', 'latest_news_solwin_feed');
add_action('admin_head', 'bdp_upgrade_link_css');

add_action('vc_before_init', 'bd_add_vc_support');
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'bd_plugin_links');

require_once BLOGDESIGNER_DIR . '/includes/promo_notice.php';

// Gutenberg block for blog designer shortcode
if (function_exists( 'register_block_type' ) ) {
    require_once BLOGDESIGNER_DIR . '/includes/blog_designer_block/index.php';
}

/**
 * Add support for visual composer
 */
if (!function_exists('bd_add_vc_support')) {

    function bd_add_vc_support() {
        vc_map(array(
            "name" => esc_html__("Blog Designer", "blog-designer"),
            "base" => "wp_blog_designer",
            "class" => "blog_designer_section",
            'show_settings_on_create' => false,
            "category" => esc_html__('Content'),
            "icon" => 'blog_designer_icon',
            "description" => __("Custom Blog Layout", "blog-designer")
        ));
    }

}

/**
 * Add css for upgrade link
 */
if (!function_exists('bdp_upgrade_link_css')) {

    function bdp_upgrade_link_css() {
        echo '<style>.row-actions a.bd_upgrade_link { color: #4caf50; }</style>';
    }

}

if (!function_exists('wp_blog_designer_enqueue_color_picker')) {

    function wp_blog_designer_enqueue_color_picker($hook_suffix) {
        // first check that $hook_suffix is appropriate for your admin page
        if (isset($_GET['page']) && ($_GET['page'] == 'designer_settings' || $_GET['page'] == 'bd_getting_started' || $_GET['page'] == 'designer_welcome_page') || $hook_suffix == 'plugins.php') {
            wp_enqueue_style(array('wp-color-picker', 'wp-jquery-ui-dialog'));

            wp_enqueue_script('my-script-handle', plugins_url('js/admin_script.js', __FILE__), array('wp-color-picker', 'jquery-ui-core', 'jquery-ui-dialog'), false, true);
            wp_localize_script('my-script-handle', 'bdlite_js', array(
                    'nothing_found' => __("Oops, nothing found!", "blog-designer"),
                    'reset_data' => __("Do you want to reset data?", "blog-designer"),
                    'choose_blog_template' => __("Choose the blog template you love", "blog-designer"),
                    'close' => __("Close", "blog-designer"),
                    'set_blog_template' => __("Set Blog Template", "blog-designer"),
                    'default_style_template' => __("Apply default style of this selected template", "blog-designer"),
                    'no_template_exist' => __("No template exist for selection", "blog-designer"),
                )
            );
            wp_enqueue_script('my-chosen-handle', plugins_url('js/chosen.jquery.js', __FILE__));
        }
    }

}

/**
 *
 * @return add menu at admin panel
 */
if (!function_exists('wp_blog_designer_add_menu')) {

    function wp_blog_designer_add_menu() {
        $bd_is_optin = get_option('bd_is_optin');
        if($bd_is_optin == 'yes' || $bd_is_optin == 'no') {
            add_menu_page(__('Blog Designer', 'blog-designer'), __('Blog Designer', 'blog-designer'), 'administrator', 'designer_settings', 'wp_blog_designer_menu_function', BLOGDESIGNER_URL . '/images/blog-designer.png');
        }
        else {
            add_menu_page(__('Blog Designer', 'blog-designer'), __('Blog Designer', 'blog-designer'), 'administrator', 'designer_welcome_page', 'wp_blog_designer_welcome_function', BLOGDESIGNER_URL . '/images/blog-designer.png');
        }        
        add_submenu_page('designer_settings', __('Blog designer Settings', 'blog-designer'), __('Blog Designer Settings', 'blog-designer'), 'manage_options', 'designer_settings', 'wp_blog_designer_add_menu');
        add_submenu_page('designer_settings', __('Getting Started', 'blog-designer'), __('Getting Started', 'blog-designer'), 'manage_options', 'bd_getting_started', 'wp_blog_designer_getting_started');
    }

}

/**
 * Include admin getting started  page
 */
if (!function_exists('wp_blog_designer_getting_started')) {

    function wp_blog_designer_getting_started() {
        include_once( 'includes/getting_started.php' );
    }

}

/**
 *
 * @return Loads plugin textdomain
 */
if (!function_exists('load_language_files')) {

    function load_language_files() {
        load_plugin_textdomain('blog-designer', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

}

add_action('plugins_loaded', 'load_language_files');

/**
 * Deactive pro version when lite version is activated
 */
if (!function_exists('wp_blog_designer_plugin_activate')) {

    function wp_blog_designer_plugin_activate() {
        if (is_plugin_active('blog-designer-pro/blog-designer-pro.php')) {
            deactivate_plugins('/blog-designer-pro/blog-designer-pro.php');
        }
        add_option('bd_plugin_do_activation_redirect', true);
    }

}

// Delete optin on deactivation of plugin
if (!function_exists('wp_blog_designer_update_optin')) {

    function wp_blog_designer_update_optin() {
        update_option('bd_is_optin','');
    }

}

if(!function_exists('wp_blog_designer_redirection')) {
    function wp_blog_designer_redirection() {
        if (get_option('bd_plugin_do_activation_redirect', false)) {
            delete_option('bd_plugin_do_activation_redirect');
            if (!isset($_GET['activate-multi'])) {
                $bd_is_optin = get_option('bd_is_optin');
                if($bd_is_optin == 'yes' || $bd_is_optin == 'no') {
                    exit( wp_redirect( admin_url( 'admin.php?page=bd_getting_started' ) ) );
                }
                else {
                    exit( wp_redirect( admin_url( 'admin.php?page=designer_welcome_page' ) ) );
                }
            }
        }
    }
}

if (!function_exists('latest_news_solwin_feed')) {

    function latest_news_solwin_feed() {
        // Register the new dashboard widget with the 'wp_dashboard_setup' action
        add_action('wp_dashboard_setup', 'solwin_latest_news_with_product_details');
        if (!function_exists('solwin_latest_news_with_product_details')) {

            function solwin_latest_news_with_product_details() {
                add_screen_option('layout_columns', array('max' => 3, 'default' => 2));
                add_meta_box('wp_blog_designer_dashboard_widget', __('News From Solwin Infotech', 'blog-designer'), 'solwin_dashboard_widget_news', 'dashboard', 'normal', 'high');
            }

        }
        if (!function_exists('solwin_dashboard_widget_news')) {

            function solwin_dashboard_widget_news() {
                echo '<div class="rss-widget">'
                . '<div class="solwin-news"><p><strong>' . __('Solwin Infotech News', 'blog-designer') . '</strong></p>';
                wp_widget_rss_output(array(
                    'url' => esc_url('https://www.solwininfotech.com/feed/'),
                    'title' => __('News From Solwin Infotech', 'blog-designer'),
                    'items' => 5,
                    'show_summary' => 0,
                    'show_author' => 0,
                    'show_date' => 1
                ));
                echo '</div>';
                $title = $link = $thumbnail = "";
                //get Latest product detail from xml file

                $file = 'https://www.solwininfotech.com/documents/assets/latest_product.xml';
                define('LATEST_PRODUCT_FILE', $file);
                echo '<div class="display-product">'
                . '<div class="product-detail"><p><strong>' . __('Latest Product', 'blog-designer') . '</strong></p>';
                $response = wp_remote_post(LATEST_PRODUCT_FILE);
                if (is_wp_error($response)) {
                    $error_message = $response->get_error_message();
                    echo "<p>" . __('Something went wrong', 'blog-designer') . " : $error_message" . "</p>";
                } else {
                    $body = wp_remote_retrieve_body($response);
                    $xml = simplexml_load_string($body);
                    $title = $xml->item->name;
                    $thumbnail = $xml->item->img;
                    $link = $xml->item->link;

                    $allProducttext = $xml->item->viewalltext;
                    $allProductlink = $xml->item->viewalllink;
                    $moretext = $xml->item->moretext;
                    $needsupporttext = $xml->item->needsupporttext;
                    $needsupportlink = $xml->item->needsupportlink;
                    $customservicetext = $xml->item->customservicetext;
                    $customservicelink = $xml->item->customservicelink;
                    $joinproductclubtext = $xml->item->joinproductclubtext;
                    $joinproductclublink = $xml->item->joinproductclublink;


                    echo '<div class="product-name"><a href="' . $link . '" target="_blank">'
                    . '<img alt="' . $title . '" src="' . $thumbnail . '"> </a>'
                    . '<a href="' . $link . '" target="_blank">' . $title . '</a>'
                    . '<p><a href="' . $allProductlink . '" target="_blank" class="button button-default">' . $allProducttext . ' &RightArrow;</a></p>'
                    . '<hr>'
                    . '<p><strong>' . $moretext . '</strong></p>'
                    . '<ul>'
                    . '<li><a href="' . $needsupportlink . '" target="_blank">' . $needsupporttext . '</a></li>'
                    . '<li><a href="' . $customservicelink . '" target="_blank">' . $customservicetext . '</a></li>'
                    . '<li><a href="' . $joinproductclublink . '" target="_blank">' . $joinproductclubtext . '</a></li>'
                    . '</ul>'
                    . '</div>';
                }
                echo '</div></div><div class="clear"></div></div>';
            }

        }
    }

}

/**
 * Custom Admin Footer
 */
add_action('current_screen', 'bd_footer');
if (!function_exists('bd_footer')) {

    function bd_footer() {
        if (isset($_GET['page']) && ($_GET['page'] == 'designer_settings' || $_GET['page'] == 'bd_getting_started')) {
            add_filter('admin_footer_text', 'bd_remove_footer_admin', 11);
            if (!function_exists('bd_remove_footer_admin')) {

                function bd_remove_footer_admin() {
                    ob_start();
                    ?>
                    <p id="footer-left" class="alignleft">
                        <?php _e('If you like ', 'blog-designer'); ?>
                        <a href="<?php echo esc_url('https://www.solwininfotech.com/product/wordpress-plugins/blog-designer/'); ?>" target="_blank"><strong><?php _e('Blog Designer', 'blog-designer'); ?></strong></a>
                        <?php _e('please leave us a', 'blog-designer'); ?>
                        <a class="bdp-rating-link" data-rated="Thanks :)" target="_blank" href="<?php echo esc_url('https://wordpress.org/support/plugin/blog-designer/reviews?filter=5#new-post'); ?>">&#x2605;&#x2605;&#x2605;&#x2605;&#x2605;</a>
                        <?php _e('rating. A huge thank you from Solwin Infotech in advance!', 'blog-designer'); ?>
                    </p><?php
                    return ob_get_clean();
                }

            }
        }
    }

}

/**
 * Get template list
 */
if (!function_exists('bd_template_list')) {

    function bd_template_list() {
        $tempate_list = array(
            'boxy' => array(
                'template_name' => __('Boxy Template', 'blog-designer'),
                'class' => 'masonry',
                'image_name' => 'boxy.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-boxy-blog-template/')
            ),
            'boxy-clean' => array(
                'template_name' => __('Boxy Clean Template', 'blog-designer'),
                'class' => 'grid',
                'image_name' => 'boxy-clean.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-boxy-clean-blog-template/')
            ),
            'brit_co' => array(
                'template_name' => __('Brit Co Template', 'blog-designer'),
                'class' => 'grid',
                'image_name' => 'brit_co.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-brit-co-blog-template/')
            ),
            'classical' => array(
                'template_name' => __('Classical Template', 'blog-designer'),
                'class' => 'full-width free',
                'image_name' => 'classical.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-classical-blog-template/')
            ),
            'cool_horizontal' => array(
                'template_name' => __('Cool Horizontal Template', 'blog-designer'),
                'class' => 'timeline slider',
                'image_name' => 'cool_horizontal.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-cool-horizontal-timeline-blog-template/')
            ),
            'cover' => array(
                'template_name' => __('Cover Template', 'blog-designer'),
                'class' => 'full-width',
                'image_name' => 'cover.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-cover-blog-template/')
            ),
            'clicky' => array(
                'template_name' => __('Clicky Template', 'blog-designer'),
                'class' => 'full-width',
                'image_name' => 'clicky.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-clicky-blog-template/')
            ),
            'deport' => array(
                'template_name' => __('Deport Template', 'blog-designer'),
                'class' => 'magazine',
                'image_name' => 'deport.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-deport-blog-template/')
            ),
            'easy_timeline' => array(
                'template_name' => __('Easy Timeline', 'blog-designer'),
                'class' => 'timeline',
                'image_name' => 'easy_timeline.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-easy-timeline-blog-template/')
            ),
            'elina' => array(
                'template_name' => __('Elina Template', 'blog-designer'),
                'class' => 'full-width',
                'image_name' => 'elina.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-elina-blog-template/')
            ),
            'evolution' => array(
                'template_name' => __('Evolution Template', 'blog-designer'),
                'class' => 'full-width free',
                'image_name' => 'evolution.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-evolution-blog-template/')
            ),
            'fairy' => array(
                'template_name' => __('Fairy Template', 'blog-designer'),
                'class' => 'full-width',
                'image_name' => 'fairy.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-fairy-blog-template/')
            ),
            'famous' => array(
                'template_name' => __('Famous Template', 'blog-designer'),
                'class' => 'grid',
                'image_name' => 'famous.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-famous-blog-template/')
            ),
            'glamour' => array(
                'template_name' => __('Glamour Template', 'blog-designer'),
                'class' => 'grid',
                'image_name' => 'glamour.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-glamour-blog-template/')
            ),
            'glossary' => array(
                'template_name' => __('Glossary Template', 'blog-designer'),
                'class' => 'masonry',
                'image_name' => 'glossary.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-glossary-blog-template/')
            ),
            'explore' => array(
                'template_name' => __('Explore Template', 'blog-designer'),
                'class' => 'grid',
                'image_name' => 'explore.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-explore-blog-template/')
            ),
            'hoverbic' => array(
                'template_name' => __('Hoverbic Template', 'blog-designer'),
                'class' => 'grid',
                'image_name' => 'hoverbic.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-hoverbic-blog-template/')
            ),
            'hub' => array(
                'template_name' => __('Hub Template', 'blog-designer'),
                'class' => 'full-width',
                'image_name' => 'hub.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-hub-blog-template/')
            ),
            'minimal' => array(
                'template_name' => __('Minimal Template', 'blog-designer'),
                'class' => 'grid',
                'image_name' => 'minimal.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-minimal-grid-blog-template/')
            ),
            'masonry_timeline' => array(
                'template_name' => __('Masonry Timeline', 'blog-designer'),
                'class' => 'magazine timeline',
                'image_name' => 'masonry_timeline.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-masonry-timeline-blog-template/')
            ),
            'invert-grid' => array(
                'template_name' => __('Invert Grid Template', 'blog-designer'),
                'class' => 'grid',
                'image_name' => 'invert-grid.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-invert-grid-blog-template/')
            ),
            'lightbreeze' => array(
                'template_name' => __('Lightbreeze Template', 'blog-designer'),
                'class' => 'full-width free',
                'image_name' => 'lightbreeze.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-light-breeze-blog-template/')
            ),
            'media-grid' => array(
                'template_name' => __('Media Grid Template', 'blog-designer'),
                'class' => 'grid',
                'image_name' => 'media-grid.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-media-grid-blog-template/')
            ),
            'my_diary' => array(
                'template_name' => __('My Diary Template', 'blog-designer'),
                'class' => 'full-width',
                'image_name' => 'my_diary.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-my-diary-blog-template/')
            ),
            'navia' => array(
                'template_name' => __('Navia Template', 'blog-designer'),
                'class' => 'magazine',
                'image_name' => 'navia.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-navia-blog-template/')
            ),
            'news' => array(
                'template_name' => __('News Template', 'blog-designer'),
                'class' => 'magazine free',
                'image_name' => 'news.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-news-blog-template/')
            ),
            'offer_blog' => array(
                'template_name' => __('Offer Blog Template', 'blog-designer'),
                'class' => 'full-width',
                'image_name' => 'offer_blog.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-offer-blog-template/')
            ),
            'overlay_horizontal' => array(
                'template_name' => __('Overlay Horizontal Template', 'blog-designer'),
                'class' => 'timeline slider',
                'image_name' => 'overlay_horizontal.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-overlay-horizontal-timeline-blog-template/')
            ),
            'nicy' => array(
                'template_name' => __('Nicy Template', 'blog-designer'),
                'class' => 'full-width',
                'image_name' => 'nicy.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-nicy-blog-template/')
            ),
            'region' => array(
                'template_name' => __('Region Template', 'blog-designer'),
                'class' => 'full-width',
                'image_name' => 'region.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-region-blog-template/')
            ),
            'roctangle' => array(
                'template_name' => __('Roctangle Template', 'blog-designer'),
                'class' => 'masonry',
                'image_name' => 'roctangle.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-roctangle-blog-template/')
            ),
            'sharpen' => array(
                'template_name' => __('Sharpen Template', 'blog-designer'),
                'class' => 'full-width',
                'image_name' => 'sharpen.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-sharpen-blog-template/')
            ),
            'spektrum' => array(
                'template_name' => __('Spektrum Template', 'blog-designer'),
                'class' => 'full-width free',
                'image_name' => 'spektrum.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-spektrum-blog-template/')
            ),
            'story' => array(
                'template_name' => __('Story Template', 'blog-designer'),
                'class' => 'timeline',
                'image_name' => 'story.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-story-timeline-blog-template/')
            ),
            'timeline' => array(
                'template_name' => __('Timeline Template', 'blog-designer'),
                'class' => 'timeline free',
                'image_name' => 'timeline.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-timeline-blog-template/')
            ),
            'winter' => array(
                'template_name' => __('Winter Template', 'blog-designer'),
                'class' => 'full-width',
                'image_name' => 'winter.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-winter-blog-template/')
            ),
            'crayon_slider' => array(
                'template_name' => __('Crayon Slider Template', 'blog-designer'),
                'class' => 'slider',
                'image_name' => 'crayon_slider.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-crayon-slider-blog-template/')
            ),
            'sallet_slider' => array(
                'template_name' => __('Sallet Slider Template', 'blog-designer'),
                'class' => 'slider',
                'image_name' => 'sallet_slider.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-sallet-slider-blog-template/')
            ),
            'sunshiny_slider' => array(
                'template_name' => __('Sunshiny Slider Template', 'blog-designer'),
                'class' => 'slider',
                'image_name' => 'sunshiny_slider.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-sunshiny-slider-blog-template/')
            ),
            'pretty' => array(
                'template_name' => __('Pretty Template', 'blog-designer'),
                'class' => 'full-width',
                'image_name' => 'pretty.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-pretty-blog-template/')
            ),
            'tagly' => array(
                'template_name' => __('Tagly Template', 'blog-designer'),
                'class' => 'full-width',
                'image_name' => 'tagly.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-tagly-blog-template/')
            ),
            'brite' => array(
                'template_name' => __('Brite Template', 'blog-designer'),
                'class' => 'full-width',
                'image_name' => 'brite.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-brite-blog-template/')
            ),
            'chapter' => array(
                'template_name' => __('Chapter Template', 'blog-designer'),
                'class' => 'grid',
                'image_name' => 'chapter.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-chapter-blog-template/')
            ),
            'steps' => array(
                'template_name' => __('Steps Template', 'blog-designer'),
                'class' => 'timeline',
                'image_name' => 'steps.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-steps-timeline-blog-template/')
            ),
            'miracle' => array(
                'template_name' => __('Miracle Template', 'blog-designer'),
                'class' => 'full-width',
                'image_name' => 'miracle.jpg',
                'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-miracle-blog-template/')
            ),
        );
        ksort($tempate_list);
        return $tempate_list;
    }

}

/**
 * Ajax handler for Store closed box id
 */
if (!function_exists('bd_closed_bdboxes')) {

    function bd_closed_bdboxes() {
        $closed = isset($_POST['closed']) ? explode(',', $_POST['closed']) : array();
        $closed = array_filter($closed);
        $page = isset($_POST['page']) ? $_POST['page'] : '';
        if ($page != sanitize_key($page))
            wp_die(0);
        if (!$user = wp_get_current_user())
            wp_die(-1);
        if (is_array($closed))
            update_user_option($user->ID, "bdpclosedbdpboxes_$page", $closed, true);
        wp_die(1);
    }

}

function bd_ajaxurl() {
    ?>
    <script type="text/javascript">
        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    </script>
    <?php
}

/**
 * Ajax handler for page link
 */
if (!function_exists('bd_get_page_link')) {

    function bd_get_page_link() {
        if (isset($_POST['page_id'])) {
            echo '<a target="_blank" href="' . get_permalink($_POST['page_id']) . '">' . __('View Blog', 'blog-designer') . '</a>';
        }
        exit();
    }

}

/**
 *
 * @param type $id
 * @param type $page
 * @return type closed class
 */
if (!function_exists('bdp_postbox_classes')) {

    function bdp_postbox_classes($id, $page) {
        if (!isset($_GET['action'])) {
            $closed = array('bdpgeneral');
            $closed = array_filter($closed);
            $page = 'designer_settings';
            $user = wp_get_current_user();
            if (is_array($closed))
                update_user_option($user->ID, "bdpclosedbdpboxes_$page", $closed, true);
        }
        if ($closed = get_user_option('bdpclosedbdpboxes_' . $page)) {
            if (!is_array($closed)) {
                $classes = array('');
            } else {
                $classes = in_array($id, $closed) ? array('closed') : array('');
            }
        } else {
            $classes = array('');
        }
        return implode(' ', $classes);
    }

}

/**
 *
 * @return Set default value
 */
if (!function_exists('wp_blog_designer_reg_function')) {

    function wp_blog_designer_reg_function() {
        $settings = get_option("wp_blog_designer_settings");
        if (empty($settings)) {
            $settings = array(
                'template_category' => '',
                'template_tags' => '',
                'template_authors' => '',
                'template_name' => 'classical',
                'template_bgcolor' => '#ffffff',
                'template_color' => '#ffffff',
                'template_ftcolor' => '#2a97ea',
                'template_fthovercolor' => '#999999',
                'template_titlecolor' => '#222222',
                'template_titlebackcolor' => '#ffffff',
                'template_contentcolor' => '#999999',
                'template_readmorecolor' => '#cecece',
                'template_readmorebackcolor' => '#2e93ea',
                'template_alterbgcolor' => '#ffffff',
            );
            update_option("posts_per_page", '5');
            update_option("display_sticky", '1');
            update_option("display_category", '0');
            update_option("social_icon_style", '0');
            update_option("rss_use_excerpt", '1');
            update_option("template_alternativebackground", '1');
            update_option("display_tag", '0');
            update_option("display_author", '0');
            update_option("display_date", '0');
            update_option("social_share", '1');
            update_option("facebook_link", '0');
            update_option("twitter_link", '0');
            update_option("google_link", '0');
            update_option("linkedin_link", '0');
            update_option("pinterest_link", '0');
            update_option("display_comment_count", '0');
            update_option("excerpt_length", '75');
            update_option("display_html_tags", '0');
            update_option("read_more_on", '2');
            update_option("read_more_text", 'Read More');
            update_option("template_titlefontsize", '35');
            update_option("content_fontsize", '14');
            update_option("wp_blog_designer_settings", $settings);
        }
    }

}

if (!function_exists('wp_blog_designer_save_settings')) {

    function wp_blog_designer_save_settings() {
        if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'save' && isset($_REQUEST['updated']) && $_REQUEST['updated'] === 'true') {
            if (isset($_POST['blog_page_display'])) {
                update_option("blog_page_display", $_POST['blog_page_display']);
            }
            if (isset($_POST['posts_per_page'])) {
                update_option("posts_per_page", $_POST['posts_per_page']);
            }
            if (isset($_POST['rss_use_excerpt'])) {
                update_option("rss_use_excerpt", $_POST['rss_use_excerpt']);
            }
            if (isset($_POST['display_date'])) {
                update_option("display_date", $_POST['display_date']);
            }
            if (isset($_POST['display_author'])) {
                update_option("display_author", $_POST['display_author']);
            }
            if (isset($_POST['display_sticky'])) {
                update_option("display_sticky", $_POST['display_sticky']);
            }
            if (isset($_POST['display_category'])) {
                update_option("display_category", $_POST['display_category']);
            }
            if (isset($_POST['display_tag'])) {
                update_option("display_tag", $_POST['display_tag']);
            }
            if (isset($_POST['txtExcerptlength'])) {
                update_option("excerpt_length", $_POST['txtExcerptlength']);
            }
            if (isset($_POST['display_html_tags'])) {
                update_option("display_html_tags", $_POST['display_html_tags']);
            } else {
                update_option("display_html_tags", 0);
            }
            if (isset($_POST['readmore_on'])) {
                update_option("read_more_on", $_POST['readmore_on']);
            }
            if (isset($_POST['txtReadmoretext'])) {
                update_option("read_more_text", $_POST['txtReadmoretext']);
            }
            if (isset($_POST['template_alternativebackground'])) {
                update_option("template_alternativebackground", $_POST['template_alternativebackground']);
            }
            if (isset($_POST['social_icon_style'])) {
                update_option("social_icon_style", $_POST['social_icon_style']);
            }
            if (isset($_POST['social_share'])) {
                update_option("social_share", $_POST['social_share']);
            }
            if (isset($_POST['facebook_link'])) {
                update_option("facebook_link", $_POST['facebook_link']);
            }
            if (isset($_POST['twitter_link'])) {
                update_option("twitter_link", $_POST['twitter_link']);
            }
            if (isset($_POST['google_link'])) {
                update_option("google_link", $_POST['google_link']);
            }
            if (isset($_POST['pinterest_link'])) {
                update_option("pinterest_link", $_POST['pinterest_link']);
            }
            if (isset($_POST['linkedin_link'])) {
                update_option("linkedin_link", $_POST['linkedin_link']);
            }
            if (isset($_POST['display_comment_count'])) {
                update_option("display_comment_count", $_POST['display_comment_count']);
            }
            if (isset($_POST['template_titlefontsize'])) {
                update_option("template_titlefontsize", $_POST['template_titlefontsize']);
            }
            if (isset($_POST['content_fontsize'])) {
                update_option("content_fontsize", $_POST['content_fontsize']);
            }
            if (isset($_POST['custom_css'])) {
                update_option("custom_css", stripslashes($_POST['custom_css']));
            }

            $templates = array();
            $templates['ID'] = $_POST['blog_page_display'];
            $templates['post_content'] = '[wp_blog_designer]';
            wp_update_post($templates);

            $settings = $_POST;
            $settings = is_array($settings) ? $settings : unserialize($settings);
            $updated = update_option("wp_blog_designer_settings", $settings);
        }
    }

}

/**
 *
 * @return Display total downloads of plugin
 */
if (!function_exists('get_total_downloads')) {

    function get_total_downloads() {
        // Set the arguments. For brevity of code, I will set only a few fields.
        $plugins = $response = "";
        $args = array(
            'author' => 'solwininfotech',
            'fields' => array(
                'downloaded' => true,
                'downloadlink' => true
            )
        );
        // Make request and extract plug-in object. Action is query_plugins
        $response = wp_remote_post(
                'http://api.wordpress.org/plugins/info/1.0/', array(
            'body' => array(
                'action' => 'query_plugins',
                'request' => serialize((object) $args)
            )
                )
        );
        if (!is_wp_error($response)) {
            $returned_object = unserialize(wp_remote_retrieve_body($response));
            $plugins = $returned_object->plugins;
        }

        $current_slug = 'blog-designer';
        if ($plugins) {
            foreach ($plugins as $plugin) {
                if ($current_slug == $plugin->slug) {
                    if ($plugin->downloaded) {
                        ?>
                        <span class="total-downloads">
                            <span class="download-number"><?php echo $plugin->downloaded; ?></span>
                        </span>
                        <?php
                    }
                }
            }
        }
    }

}

/**
 *
 * @return Display rating of plugin
 */
$wp_version = get_bloginfo('version');
if ($wp_version > 3.8) {
    if (!function_exists('wp_custom_star_rating')) {

        function wp_custom_star_rating($args = array()) {
            $plugins = $response = "";
            $args = array(
                'author' => 'solwininfotech',
                'fields' => array(
                    'downloaded' => true,
                    'downloadlink' => true
                )
            );

            // Make request and extract plug-in object. Action is query_plugins
            $response = wp_remote_post(
                    'http://api.wordpress.org/plugins/info/1.0/', array(
                'body' => array(
                    'action' => 'query_plugins',
                    'request' => serialize((object) $args)
                )
                    )
            );
            if (!is_wp_error($response)) {
                $returned_object = unserialize(wp_remote_retrieve_body($response));
                $plugins = $returned_object->plugins;
            }
            $current_slug = 'blog-designer';
            if ($plugins) {
                foreach ($plugins as $plugin) {
                    if ($current_slug == $plugin->slug) {
                        $rating = $plugin->rating * 5 / 100;
                        if ($rating > 0) {
                            $args = array(
                                'rating' => $rating,
                                'type' => 'rating',
                                'number' => $plugin->num_ratings,
                            );
                            wp_star_rating($args);
                        }
                    }
                }
            }
        }

    }
}

/**
 *
 * @return Enqueue admin panel required css
 */
if (!function_exists('wp_blog_designer_admin_stylesheet')) {

    function wp_blog_designer_admin_stylesheet() {
        $screen = get_current_screen();

        wp_register_style('wp-blog-designer-admin-support-stylesheets', plugins_url('css/blog_designer_editor_support.css', __FILE__));
        wp_enqueue_style('wp-blog-designer-admin-support-stylesheets');

        if ((isset($_GET['page']) && ( $_GET['page'] == 'designer_settings' || $_GET['page'] == 'bd_getting_started' || $_GET['page'] == "designer_welcome_page")) || $screen->id == 'dashboard' || $screen->id == "plugins") {

            $adminstylesheetURL = plugins_url('css/admin.css', __FILE__);
            $adminrtlstylesheetURL = plugins_url('css/admin-rtl.css', __FILE__);
            $adminstylesheet = dirname(__FILE__) . '/css/admin.css';
            if (file_exists($adminstylesheet)) {
                wp_register_style('wp-blog-designer-admin-stylesheets', $adminstylesheetURL);
                wp_enqueue_style('wp-blog-designer-admin-stylesheets');
            }

            if (is_rtl()) {
                wp_register_style('wp-blog-designer-admin-rtl-stylesheets', $adminrtlstylesheetURL);
                wp_enqueue_style('wp-blog-designer-admin-rtl-stylesheets');
            }

            $adminstylechosenURL = plugins_url('css/chosen.min.css', __FILE__);
            $adminstylechosen = dirname(__FILE__) . '/css/chosen.min.css';
            if (file_exists($adminstylechosen)) {
                wp_register_style('wp-blog-designer-chosen-stylesheets', $adminstylechosenURL);
                wp_enqueue_style('wp-blog-designer-chosen-stylesheets');
            }

            if (isset($_GET['page']) && $_GET['page'] == 'designer_settings') {
                $adminstylearistoURL = plugins_url('css/aristo.css', __FILE__);
                $adminstylearisto = dirname(__FILE__) . '/css/aristo.css';
                if (file_exists($adminstylearisto)) {
                    wp_register_style('wp-blog-designer-aristo-stylesheets', $adminstylearistoURL);
                    wp_enqueue_style('wp-blog-designer-aristo-stylesheets');
                }
            }

            $fontawesomeiconURL = plugins_url('css/fontawesome-all.min.css', __FILE__);
            $fontawesomeicon = dirname(__FILE__) . '/css/fontawesome-all.min.css';
            if (file_exists($fontawesomeicon)) {
                wp_register_style('wp-blog-designer-fontawesome-stylesheets', $fontawesomeiconURL);
                wp_enqueue_style('wp-blog-designer-fontawesome-stylesheets');
            }
        }
    }

}

/**
 *
 * @return Enqueue front side required css
 */
if (!function_exists('wp_blog_designer_front_stylesheet')) {

    function wp_blog_designer_front_stylesheet() {
        $fontawesomeiconURL = plugins_url('css/fontawesome-all.min.css', __FILE__);
        $fontawesomeicon = dirname(__FILE__) . '/css/fontawesome-all.min.css';
        if (file_exists($fontawesomeicon)) {
            wp_register_style('wp-blog-designer-fontawesome-stylesheets', $fontawesomeiconURL);
            wp_enqueue_style('wp-blog-designer-fontawesome-stylesheets');
        }
        $designer_cssURL = plugins_url('css/designer_css.css', __FILE__);
        $designerrtl_cssURL = plugins_url('css/designerrtl_css.css', __FILE__);
        $designer_css = dirname(__FILE__) . '/css/designer_css.css';
        $designerrtl_css = dirname(__FILE__) . '/css/designerrtl_css.css';
        if (file_exists($designer_css)) {
            wp_register_style('wp-blog-designer-css-stylesheets', $designer_cssURL);
            wp_enqueue_style('wp-blog-designer-css-stylesheets');
        }
        if (is_rtl()) {
            wp_register_style('wp-blog-designer-rtl-css-stylesheets', $designerrtl_cssURL);
            wp_enqueue_style('wp-blog-designer-rtl-css-stylesheets');
        }
        //wp_enqueue_scripts('wp-blog-designer-script', plugins_url('js/designer.js'. __FILE__));
        wp_enqueue_script('wp-blog-designer-script', plugins_url().'/blog-designer/js/designer.js', '', false, true);
    }

}

/**
 *
 * @return enqueue admin side plugin js
 */
if (!function_exists('wp_blog_designer_admin_scripts')) {

    function wp_blog_designer_admin_scripts() {
        wp_enqueue_script('jquery');
    }

}

/**
 *
 * @return include plugin dynamic css
 */
if (!function_exists('wp_blog_designer_stylesheet')) {

    function wp_blog_designer_stylesheet() {
        if (!is_admin()) {
            $stylesheet = dirname(__FILE__) . '/designer_css.php';

            if (file_exists($stylesheet)) {
                include('designer_css.php');
            }
        } 
        if (!is_admin()  && is_rtl()) {
            $stylesheet = dirname(__FILE__) . '/designerrtl_css.php';

            if (file_exists($stylesheet)) {
                include('designerrtl_css.php');
            }
        }
    }

}

/**
 *
 *  @param type $length
 *  @return int get content length
 */
if (!function_exists('wp_blog_designer_excerpt_length')) {

    function wp_blog_designer_excerpt_length($length) {
        if (get_option('excerpt_length') != '') {
            return get_option('excerpt_length');
        } else {
            return 50;
        }
    }

}

/**
 * @return type
 */
if (!function_exists('wp_blog_designer_views')) {

    function wp_blog_designer_views() {
        ob_start();
        add_filter('excerpt_more', 'bd_remove_continue_reading', 50);
        $settings = get_option("wp_blog_designer_settings");
        if (!isset($settings['template_name']) || empty($settings['template_name'])) {
            $link_message = '';
            if (is_user_logged_in()) {
                $link_message = __('plz go to ', 'blog-designer') . '<a href="' . admin_url('admin.php?page=designer_settings') . '" target="_blank">' . __('Blog Designer Panel', 'blog-designer') . '</a> , ' . __('select Blog Designs & save settings.', 'blog-designer');
            }
            return __("You haven't created any blog designer shortcode.", 'blog-designer') . ' ' . $link_message;
        }
        $theme = $settings['template_name'];
        $author = $cat = $tag = array();
        $category = '';
        if (isset($settings['template_category']))
            $cat = $settings['template_category'];

        if (!empty($cat)) {
            foreach ($cat as $catObj):
                $category .= $catObj . ',';
            endforeach;
            $cat = rtrim($category, ',');
        } else {
            $cat = array();
        }

        if (isset($settings['template_tags']))
            $tag = $settings['template_tags'];
        if (empty($tag)) {
            $tag = array();
        }

        $tax_query = array();
        if (!empty($cat) && !empty($tag)) {
            $cat = explode(',', $cat);

            $tax_query = array(
                'relation' => 'OR',
                array(
                    'taxonomy' => 'category',
                    'field' => 'term_id',
                    'terms' => $cat,
                    'operator' => 'IN'
                ),
                array(
                    'taxonomy' => 'post_tag',
                    'field' => 'term_id',
                    'terms' => $tag,
                    'operator' => 'IN'
                ),
            );
        } elseif (!empty($tag)) {
            $tax_query = array(
                'relation' => 'AND',
                array(
                    'taxonomy' => 'post_tag',
                    'field' => 'term_id',
                    'terms' => $tag,
                    'operator' => 'IN'
                ),
            );
        } elseif (!empty($cat)) {
            $cat = explode(',', $cat);
            $tax_query = array(
                'relation' => 'AND',
                array(
                    'taxonomy' => 'category',
                    'field' => 'term_id',
                    'terms' => $cat,
                    'operator' => 'IN',
                ),
            );
        }

        if (isset($settings['template_authors']) && $settings['template_authors'] != '') {
            $author = $settings['template_authors'];
            $author = implode(',', $author);
        }

        $posts_per_page = get_option('posts_per_page');
        $paged = blogdesignerpaged();

        $args = array(
            'posts_per_page' => $posts_per_page,
            'paged' => $paged,
            'tax_query' => $tax_query,
            'author' => $author
        );

        $display_sticky = get_option('display_sticky');
        if ($display_sticky != '' && $display_sticky == 1) {
            $args['ignore_sticky_posts'] = 1;
        }


        global $wp_query;
        $temp_query = $wp_query;
        $loop = new WP_Query($args);
        $wp_query = $loop;

        $alter = 1;
        $class = '';
        $alter_class = '';
        $main_container_class = isset($settings['main_container_class']) && $settings['main_container_class'] != '' ? $settings['main_container_class'] : '';
        if ($loop->have_posts()) {
            if($main_container_class != '') {
                echo '<div class="'. $main_container_class .'">';
            }
            if ($theme == 'timeline') {
                ?>
                <div class="timeline_bg_wrap">
                    <div class="timeline_back clearfix"><?php
                    }
                    while (have_posts()) : the_post();
                        if ($theme == 'classical') {
                            $class = ' classical';
                            wp_classical_template($alter_class);
                        } elseif ($theme == 'lightbreeze') {
                            if (get_option('template_alternativebackground') == 0) {
                                if ($alter % 2 == 0) {
                                    $alter_class = ' alternative-back';
                                } else {
                                    $alter_class = ' ';
                                }
                            }
                            $class = ' lightbreeze';
                            wp_lightbreeze_template($alter_class);
                            $alter ++;
                        } elseif ($theme == 'spektrum') {
                            $class = ' spektrum';
                            wp_spektrum_template();
                        } elseif ($theme == 'evolution') {
                            if (get_option('template_alternativebackground') == 0) {
                                if ($alter % 2 == 0) {
                                    $alter_class = ' alternative-back';
                                } else {
                                    $alter_class = ' ';
                                }
                            }
                            $class = ' evolution';
                            wp_evolution_template($alter_class);
                            $alter ++;
                        } elseif ($theme == 'timeline') {
                            if ($alter % 2 == 0) {
                                $alter_class = ' even';
                            } else {
                                $alter_class = ' ';
                            }
                            $class = 'timeline';
                            wp_timeline_template($alter_class);
                            $alter ++;
                        } elseif ($theme == 'news') {
                            if (get_option('template_alternativebackground') == 0) {
                                if ($alter % 2 == 0) {
                                    $alter_class = ' alternative-back';
                                } else {
                                    $alter_class = ' ';
                                }
                            }
                            $class = ' news';
                            wp_news_template($alter_class);
                            $alter ++;
                        }
                    endwhile;
                    if ($theme == 'timeline') {
                        ?>
                    </div>
                </div><?php
            }
        }
        echo '<div class="wl_pagination_box bd_pagination_box ' . $class . '">';
        echo designer_pagination();
        echo '</div>';
        if($main_container_class != '') {
            echo '</div>';
        }
        wp_reset_query();
        $wp_query = NULL;
        $wp_query = $temp_query;
        $content = ob_get_clean();
        return $content;
    }

}

/**
 *
 * @global type $post
 * @return html display classical design
 */
if (!function_exists('wp_classical_template')) {

    function wp_classical_template($alterclass) {
        ?>
        <div class="blog_template bdp_blog_template classical">
            <?php
            if (has_post_thumbnail()) {
                ?><div class="bd-post-image"><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('full'); ?></a></div><?php
            }
            ?>
            <div class="bd-blog-header">
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <?php
                $display_date = get_option('display_date');
                $display_author = get_option('display_author');
                $display_comment_count = get_option('display_comment_count');
                if ($display_date == 0 || $display_author == 0 || $display_comment_count == 0) {
                    ?>
                    <div class="bd-metadatabox"><p><?php
                        if ($display_author == 0 && $display_date == 0) {
                             _e('Posted by ', 'blog-designer'); ?><a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>"><span><?php the_author(); ?></span></a>&nbsp;<?php _e('on', 'blog-designer'); ?>&nbsp;<?php
                            $date_format = get_option('date_format');
                            echo get_the_time($date_format);
                        } elseif ($display_author == 0) {
                             _e('Posted by ', 'blog-designer'); ?><a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>"><span><?php the_author(); ?></span></a>&nbsp;<?php
                        } elseif ($display_date == 0) {
                            _e('Posted on ', 'blog-designer');
                            $date_format = get_option('date_format');
                            echo get_the_time($date_format);
                        } ?></p><?php
                        if ($display_comment_count == 0) {
                            ?><div class="bd-metacomments">
                                <i class="fas fa-comment"></i><?php comments_popup_link('0', '1', '%'); ?>
                            </div><?php
                        }
                        ?></div><?php
                }

                if (get_option('display_category') == 0) {
                    ?>
                    <div><span class="bd-category-link"><?php
                        echo '<span class="bd-link-label">';
                        echo '<i class="fas fa-folder-open"></i>';
                        _e('Category', 'blog-designer');
                        echo ':&nbsp;';
                        echo '</span>';
                        $categories_list = get_the_category_list(', ');
                        if ($categories_list):
                            print_r($categories_list);
                            $show_sep = true;
                        endif;
                        ?></span></div><?php
                }

                if (get_option('display_tag') == 0) {
                    $tags_list = get_the_tag_list('', ', ');
                    if ($tags_list):
                        ?><div class="bd-tags">
                            <?php
                            echo '<span class="bd-link-label">';
                            echo '<i class="fas fa-tags"></i>';
                            _e('Tags', 'blog-designer');
                            echo ':&nbsp;';
                            echo '</span>';
                            print_r($tags_list);
                            $show_sep = true;
                            ?></div><?php
                    endif;
                }
                ?></div>
            <div class="bd-post-content">
                <?php echo wp_bd_get_content(get_the_ID()); ?>
                <?php
                if (get_option('rss_use_excerpt') == 1 && get_option('read_more_on') == 1) {
                    $read_more_class = (get_option('read_more_on') == 1) ? 'bd-more-tag-inline' : 'bd-more-tag';
                    if (get_option('read_more_text') != '') {
                        echo '<a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . get_option('read_more_text') . ' </a>';
                    } else {
                        echo ' <a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . __("Continue Reading...", "blog-designer") . '</a>';
                    }
                }
                ?>
            </div>
            <div class="bd-post-footer">
                <?php if (get_option('social_share') != 0 && ((get_option('facebook_link') == 0) || (get_option('twitter_link') == 0) || (get_option('google_link') == 0) || (get_option('linkedin_link') == 0) || ( get_option('pinterest_link') == 0 ))) { ?>
                    <div class="social-component">
                        <?php if (get_option('facebook_link') == 0): ?>
                            <a data-share="facebook" data-href="https://www.facebook.com/sharer/sharer.php" data-url="<?php echo get_the_permalink(); ?>" class="bd-facebook-share bd-social-share"><i class="fab fa-facebook-f"></i></a><?php endif; ?><?php if (get_option('twitter_link') == 0): ?>
                            <a data-share="twitter" data-href="https://twitter.com/share" data-text="<?php echo get_the_title(); ?>" data-url="<?php echo get_the_permalink(); ?>" class="bd-twitter-share bd-social-share"><i class="fab fa-twitter"></i></a><?php endif; ?><?php if (get_option('google_link') == 0): ?>
                            <a data-share="google" data-href="https://plus.google.com/share" data-url="<?php echo get_the_permalink();?>" class="bd-google-share bd-social-share"><i class="fab fa-google-plus-g"></i></a><?php endif; ?><?php if (get_option('linkedin_link') == 0): ?>
                            <a data-share="linkedin" data-href="https://www.linkedin.com/shareArticle" data-url="<?php echo get_the_permalink(); ?>" class="bd-linkedin-share bd-social-share"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
                        <?php
                        $pinterestimage = '';
                        if (get_option('pinterest_link') == 0):
                            $pinterestimage = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
                        ?>
                            <a data-share="pinterest" data-href="https://pinterest.com/pin/create/button/" data-url="<?php echo get_the_permalink(); ?>" data-mdia="<?php echo $pinterestimage[0]; ?>" data-description="<?php echo get_the_title(); ?>" class="bd-pinterest-share bd-social-share"> <i class="fab fa-pinterest-p"></i></a>
                        <?php endif; ?>
                    </div>
                <?php } ?>
                <?php
                if (get_option('rss_use_excerpt') == 1 && get_option('read_more_on') == 2) {
                    if (get_option('read_more_text') != '') {
                        echo '<a class="bd-more-tag" href="' . get_the_permalink() . '">' . get_option('read_more_text') . ' </a>';
                    } else {
                        echo ' <a class="bd-more-tag" href="' . get_the_permalink() . '">' . __("Read More", "blog-designer") . '</a>';
                    }
                }
                ?></div></div><?php
    }

}

/**
 *
 * @global type $post
 * @param type $alterclass
 * @return html display lightbreeze design
 */
if (!function_exists('wp_lightbreeze_template')) {

    function wp_lightbreeze_template($alterclass) {
        ?>
        <div class="blog_template bdp_blog_template box-template active lightbreeze <?php echo $alterclass; ?>">
            <?php
            if (has_post_thumbnail()) {
                ?> <div class="bd-post-image"><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('full'); ?></a></div> <?php
            }
            ?>
            <div class="bd-blog-header">
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <?php
                $display_date = get_option('display_date');
                $display_author = get_option('display_author');
                $display_category = get_option('display_category');
                $display_comment_count = get_option('display_comment_count');
                if ($display_date == 0 || $display_author == 0 || $display_category == 0 || $display_comment_count == 0) {
                    ?>
                    <div class="bd-meta-data-box"><?php
                        if ($display_author == 0) {
                            ?>
                            <div class="bd-metadate">
                                <i class="fas fa-user"></i><?php _e('Posted by ', 'blog-designer'); ?><a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>"><span><?php the_author(); ?></span></a><br />
                            </div><?php
                        }
                        if ($display_date == 0) {
                            $date_format = get_option('date_format');
                            ?>
                            <div class="bd-metauser">
                                <span class="mdate"><i class="far fa-calendar-alt"></i> <?php echo get_the_time($date_format); ?></span>
                            </div><?php
                        }
                        if ($display_category == 0) {
                            ?>
                            <div class="bd-metacats">
                                <span class="bd-icon-cats"></span><?php
                                $categories_list = get_the_category_list(', ');
                                if ($categories_list):
                                    print_r($categories_list);
                                    $show_sep = true;
                                endif;
                                ?></div><?php
                        }
                        if ($display_comment_count == 0) {
                            ?>
                            <div class="bd-metacomments"><span class="bd-icon-comment"></span><?php comments_popup_link(__('No Comments', 'blog-designer'), __('1 Comment', 'blog-designer'), '% ' . __('Comments', 'blog-designer')); ?></div><?php }
                            ?>
                    </div>
                <?php } ?>

            </div>
            <div class="bd-post-content">
                <?php echo wp_bd_get_content(get_the_ID()); ?>
                <?php
                if (get_option('rss_use_excerpt') == 1 && get_option('read_more_on') == 1) {
                    if (get_option('read_more_text') != '') {
                        echo '<a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . get_option('read_more_text') . ' </a>';
                    } else {
                        echo ' <a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . __("Continue Reading...", "blog-designer") . '</a>';
                    }
                }
                ?>
            </div>

            <?php
            if (get_option('display_tag') == 0) {
                $tags_list = get_the_tag_list('', ', ');
                if ($tags_list):
                    ?>
                    <div class="bd-tags"><span class="bd-icon-tags"></span>&nbsp;<?php
                        print_r($tags_list);
                        $show_sep = true;
                        ?></div><?php
                endif;
            }
            ?>

            <div class="bd-post-footer">
                <?php if (get_option('social_share') != 0 && ((get_option('facebook_link') == 0) || (get_option('twitter_link') == 0) || (get_option('google_link') == 0) || (get_option('linkedin_link') == 0) || ( get_option('pinterest_link') == 0 ))) { ?>
                    <div class="social-component">
                        <?php if (get_option('facebook_link') == 0): ?>
                            <a data-share="facebook" data-href="https://www.facebook.com/sharer/sharer.php" data-url="<?php echo get_the_permalink(); ?>" class="bd-facebook-share bd-social-share"><i class="fab fa-facebook-f"></i></a><?php endif; ?><?php if (get_option('twitter_link') == 0): ?>
                            <a data-share="twitter" data-href="https://twitter.com/share" data-text="<?php echo get_the_title(); ?>" data-url="<?php echo get_the_permalink(); ?>" class="bd-twitter-share bd-social-share"><i class="fab fa-twitter"></i></a><?php endif; ?><?php if (get_option('google_link') == 0): ?>
                            <a data-share="google" data-href="https://plus.google.com/share" data-url="<?php echo get_the_permalink();?>" class="bd-google-share bd-social-share"><i class="fab fa-google-plus-g"></i></a><?php endif; ?><?php if (get_option('linkedin_link') == 0): ?>
                            <a data-share="linkedin" data-href="https://www.linkedin.com/shareArticle" data-url="<?php echo get_the_permalink(); ?>" class="bd-linkedin-share bd-social-share"><i class="fab fa-linkedin-in"></i></a><?php endif; ?><?php if (get_option('pinterest_link') == 0):
                            $pinterestimage = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full'); ?><a data-share="pinterest" data-href="https://pinterest.com/pin/create/button/" data-url="<?php echo get_the_permalink(); ?>" data-mdia="<?php echo $pinterestimage[0]; ?>" data-description="<?php echo get_the_title(); ?>" class="bd-pinterest-share bd-social-share"> <i class="fab fa-pinterest-p"></i></a>
                        <?php endif; ?>
                    </div>
                <?php } ?>

                <?php
                if (get_option('rss_use_excerpt') == 1 && get_option('read_more_on') == 2) {
                    if (get_option('read_more_text') != '') {
                        echo '<a class="bd-more-tag" href="' . get_the_permalink() . '">' . get_option('read_more_text') . ' </a>';
                    } else {
                        echo '<a class="bd-more-tag" href="' . get_the_permalink() . '">' . __("Read More", "blog-designer") . '</a>';
                    }
                }
                ?></div></div> <?php
    }

}

/**
 *
 * @global type $post
 * @return html display spektrum design
 */
if (!function_exists('wp_spektrum_template')) {

    function wp_spektrum_template() {
        ?>
        <div class="blog_template bdp_blog_template spektrum">
            <?php if (has_post_thumbnail()) { ?>
                <div class="bd-post-image">
                    <?php the_post_thumbnail('full'); ?>
                    <div class="overlay">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </div>
                </div>
            <?php } ?>
            <div class="spektrum_content_div">
                <div class="bd-blog-header<?php
                if (get_option('display_date') != 0) {
                    echo ' disable_date';
                }
                ?>">
                 <?php if (get_option('display_date') == 0) { ?>
                <p class="date"><span class="number-date"><?php the_time('d'); ?></span><?php the_time('F'); ?></p>
                <?php } ?>
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2></div>
                <div class="bd-post-content"><?php
                    echo wp_bd_get_content(get_the_ID());

                    if (get_option('rss_use_excerpt') == 1 &&  get_option('excerpt_length') > 0) {
                        if (get_option('read_more_on') == 1) {
                            if (get_option('read_more_text') != '') {
                                echo '<a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . get_option('read_more_text') . ' </a>';
                            } else {
                                echo ' <a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . __("Read More", "blog-designer") . '</a>';
                            }
                        }
                    }

                    if (get_option('rss_use_excerpt') == 1 && get_option('read_more_on') != 0):
                        ?>
                        <span class="details"><?php
                            global $post;
                            if (get_option('read_more_on') == 2) {
                                if (get_option('read_more_text') != '') {
                                    echo '<a class="bd-more-tag" href="' . get_permalink($post->ID) . '">' . get_option('read_more_text') . ' </a>';
                                } else {
                                    echo ' <a class="bd-more-tag" href="' . get_permalink($post->ID) . '">' . __('Read More', 'blog-designer') . '</a>';
                                }
                            }
                            ?>
                        </span><?php endif; ?>
                </div>
                <?php
                $display_category = get_option('display_category');
                $display_author = get_option('display_author');
                $display_tag = get_option('display_tag');
                $display_comment_count = get_option('display_comment_count');
                if ($display_category == 0 || $display_author == 0 || $display_tag == 0 || $display_comment_count == 0) {
                    ?>
                    <div class="post-bottom">
                        <?php if ($display_category == 0) { ?>
                            <span class="bd-categories"><span class="bd-icon-cats"></span>&nbsp;<?php
                                $categories_list = get_the_category_list(', ');
                                if ($categories_list):
                                    echo '<span class="bd-link-label">';
                                    _e('Categories', 'blog-designer');
                                    echo '</span>';
                                    echo ' : ';
                                    print_r($categories_list);
                                    $show_sep = true;
                                endif;
                                ?></span><?php
                        }
                        if ($display_author == 0) {
                            ?>
                            <span class="post-by"><span class="bd-icon-author"></span><?php _e('Posted by ', 'blog-designer'); ?><a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>"><span><?php the_author(); ?></span></a>
                            </span><?php
                        }
                        if ($display_tag == 0) {
                            $tags_list = get_the_tag_list('', ', ');
                            if ($tags_list):
                                ?>
                                <span class="bd-tags"><span class="bd-icon-tags"></span>&nbsp;<?php
                                    print_r($tags_list);
                                    $show_sep = true;
                                    ?>
                                </span><?php
                            endif;
                        }
                        if ($display_comment_count == 0) {
                            ?>
                            <span class="bd-metacomments"><span class="bd-icon-comment"></span>&nbsp;<?php comments_popup_link(__('No Comments', 'blog-designer'), __('1 Comment', 'blog-designer'), '% ' . __('Comments', 'blog-designer')); ?>
                            </span><?php
                        }
                        ?>
                    </div>
                <?php } ?>

                <?php if (get_option('social_share') != 0 && ((get_option('facebook_link') == 0) || (get_option('twitter_link') == 0) || (get_option('google_link') == 0) || (get_option('linkedin_link') == 0) || ( get_option('pinterest_link') == 0 ))) { ?>
                    <div class="social-component spektrum-social">
                        <?php if (get_option('facebook_link') == 0): ?>
                            <a href="<?php echo 'https://www.facebook.com/sharer/sharer.php?u=' . get_the_permalink(); ?>" target= _blank class="bd-facebook-share"><i class="fab fa-facebook-f"></i></a><?php endif; 
                        if (get_option('twitter_link') == 0): ?>
                            <a href="<?php echo 'http://twitter.com/share?&url=' . get_the_permalink(); ?>" target= _blank class="bd-twitter-share"><i class="fab fa-twitter"></i></a><?php endif; 
                        if (get_option('google_link') == 0): ?>
                            <a href="<?php echo 'https://plus.google.com/share?url=' . get_the_permalink(); ?>" target= _blank class="bd-google-share"><i class="fab fa-google-plus-g"></i></a><?php endif; 
                        if (get_option('linkedin_link') == 0): ?>
                            <a href="<?php echo 'http://www.linkedin.com/shareArticle?url=' . get_the_permalink(); ?>" target= _blank class="bd-linkedin-share"><i class="fab fa-linkedin-in"></i></a><?php endif; 
                        if (get_option('pinterest_link') == 0):
                            $pinterestimage = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
                        ?><a href="<?php echo '//pinterest.com/pin/create/button/?url=' . get_the_permalink(); ?>" target= _blank class="bd-pinterest-share"> <i class="fab fa-pinterest-p"></i></a>
                        <?php endif; ?>
                    </div>
                <?php } ?>
            </div>
        </div><?php
    }

}

/**
 *
 * @global type $post
 * @param type $alterclass
 * @return html display evolution design
 */
if (!function_exists('wp_evolution_template')) {

    function wp_evolution_template($alterclass) {
        ?>
        <div class="blog_template bdp_blog_template evolution <?php echo $alterclass; ?>">
            <?php if (get_option('display_category') == 0) { ?>
                <div class="bd-categories">
                    <?php
                    $categories_list = get_the_category_list(', ');
                    if ($categories_list):
                        print_r($categories_list);
                        $show_sep = true;
                    endif;
                    ?>
                </div>
            <?php } ?>

            <div class="bd-blog-header"><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2></div>

            <?php
            $display_date = get_option('display_date');
            $display_author = get_option('display_author');
            $display_comment_count = get_option('display_comment_count');
            if ($display_date == 0 || $display_author == 0 || $display_comment_count == 0) {
                ?>
                <div class="post-entry-meta"><?php
                    if ($display_date == 0) {
                        $date_format = get_option('date_format');
                        ?>
                        <span class="date"><span class="bd-icon-date"></span><?php echo get_the_time($date_format); ?></span><?php
                    }
                    if ($display_author == 0) {
                        ?>
                        <span class="author"><span class="bd-icon-author"></span><?php _e('Posted by ', 'blog-designer'); ?><a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>"><?php the_author(); ?></a></span><?php
                    }
                    if ($display_comment_count == 0) {
                        if (!post_password_required() && ( comments_open() || get_comments_number() )) :
                            ?>
                            <span class="comment"><span class="bd-icon-comment"></span><?php comments_popup_link('0', '1', '%'); ?></span>
                            <?php
                        endif;
                    }
                    ?>
                </div>
            <?php } ?>

            <?php if (has_post_thumbnail()) { ?>
                <div class="bd-post-image">
                    <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('full'); ?>
                        <span class="overlay"></span>
                    </a>
                </div>
            <?php } ?>

            <div class="bd-post-content">
                <?php
                echo wp_bd_get_content(get_the_ID());

                if (get_option('rss_use_excerpt') == 1 && get_option('read_more_on') == 1) {
                    if (get_option('read_more_text') != '') {
                        echo '<a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . get_option('read_more_text') . ' </a>';
                    } else {
                        echo ' <a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . __("Continue Reading...", "blog-designer") . '</a>';
                    }
                }
                ?>
            </div>

            <?php
            $display_tag = get_option('display_tag');
            if ($display_tag == 0) {
                $tags_list = get_the_tag_list('', ', ');
                if ($tags_list):
                    ?>
                    <div class="bd-tags">
                        <?php
                        echo '<span class="bd-link-label">';
                        echo '<i class="fas fa-tags"></i>';
                        _e('Tags', 'blog-designer');
                        echo ':&nbsp;';
                        echo '</span>';
                        print_r($tags_list);
                        $show_sep = true;
                        ?>
                    </div><?php
                endif;
            }
            ?>

            <div class="bd-post-footer">
                <?php if (get_option('social_share') != 0 && ((get_option('facebook_link') == 0) || (get_option('twitter_link') == 0) || (get_option('google_link') == 0) || (get_option('linkedin_link') == 0) || ( get_option('pinterest_link') == 0 ))) { ?>
                    <div class="social-component">
                        <?php if (get_option('facebook_link') == 0): ?><a data-share="facebook" data-href="https://www.facebook.com/sharer/sharer.php" data-url="<?php echo get_the_permalink(); ?>" class="bd-facebook-share bd-social-share"><i class="fab fa-facebook-f"></i></a><?php endif; ?><?php if (get_option('twitter_link') == 0): ?>
                        <a data-share="twitter" data-href="https://twitter.com/share" data-text="<?php echo get_the_title(); ?>" data-url="<?php echo get_the_permalink(); ?>" class="bd-twitter-share bd-social-share"><i class="fab fa-twitter"></i></a><?php endif; ?><?php if (get_option('google_link') == 0): ?>
                        <a data-share="google" data-href="https://plus.google.com/share" data-url="<?php echo get_the_permalink();?>" class="bd-google-share bd-social-share"><i class="fab fa-google-plus-g"></i></a><?php endif; ?><?php if (get_option('linkedin_link') == 0): ?>
                        <a data-share="linkedin" data-href="https://www.linkedin.com/shareArticle" data-url="<?php echo get_the_permalink(); ?>" class="bd-linkedin-share bd-social-share"><i class="fab fa-linkedin-in"></i></a><?php endif; ?><?php
                        if (get_option('pinterest_link') == 0):
                            $pinterestimage = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
                        ?><a data-share="pinterest" data-href="https://pinterest.com/pin/create/button/" data-url="<?php echo get_the_permalink(); ?>" data-mdia="<?php echo $pinterestimage[0]; ?>" data-description="<?php echo get_the_title(); ?>" class="bd-pinterest-share bd-social-share"> <i class="fab fa-pinterest-p"></i></a>
                        <?php endif; ?>
                    </div>
                <?php } ?>
                <?php
                if (get_option('rss_use_excerpt') == 1 && get_option('read_more_on') == 2) {
                    if (get_option('read_more_text') != '') {
                        echo '<a class="bd-more-tag" href="' . get_the_permalink() . '">' . get_option('read_more_text') . ' </a>';
                    } else {
                        echo ' <a class="bd-more-tag" href="' . get_the_permalink() . '">' . __("Read More", "blog-designer") . '</a>';
                    }
                }
                ?></div></div><?php
    }

}

/**
 *
 * @global type $post
 * @return html display timeline design
 */
if (!function_exists('wp_timeline_template')) {

    function wp_timeline_template($alterclass) {
        ?>
        <div class="blog_template bdp_blog_template timeline blog-wrap <?php echo $alterclass; ?>">
            <div class="post_hentry"><p><i class="fas" data-fa-pseudo-element=":before"></i></p><div class="post_content_wrap">
                    <div class="post_wrapper box-blog">
                        <?php if (has_post_thumbnail()) { ?>
                            <div class="bd-post-image photo">
                                <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('full'); ?>
                                    <span class="overlay"></span>
                                </a>
                            </div>
                        <?php } ?>
                        <div class="desc">
                            <h3 class="entry-title text-center text-capitalize"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <?php
                            $display_author = get_option('display_author');
                            $display_comment_count = get_option('display_comment_count');
                            $display_date = get_option('display_date');
                            if ($display_date == 0 || $display_comment_count == 0 || $display_date == 0) {
                                ?>
                                <div class="date_wrap">
                                    <?php if ($display_author == 0) { ?>
                                        <p class='bd-margin-0'><span title="Posted By <?php the_author(); ?>"><i class="fas fa-user"></i>&nbsp;<a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>"><span><?php the_author(); ?></span></a></span>&nbsp;&nbsp;<?php } if ($display_comment_count == 0) { ?><span class="bd-metacomments"><span class="bd-icon-comment"></span>&nbsp;<?php comments_popup_link(__('No Comments', 'blog-designer'), __('1 Comment', 'blog-designer'), '% ' . __('Comments', 'blog-designer')); ?>
                                        </span></p><?php } if ($display_date == 0) { ?><div class="bd-datetime">
                                            <span class="month"><?php the_time('M'); ?></span><span class="date"><?php the_time('d'); ?></span>
                                        </div><?php } ?></div>
                            <?php } ?>
                            <div class="bd-post-content">
                                <?php
                                echo wp_bd_get_content(get_the_ID());

                                if (get_option('rss_use_excerpt') == 1 && get_option('excerpt_length') > 0) {
                                    if (get_option('read_more_on') == 1) {
                                        if (get_option('read_more_text') != '') {
                                            echo '<a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . get_option('read_more_text') . ' </a>';
                                        } else {
                                            echo ' <a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . __("Read More", "blog-designer") . '</a>';
                                        }
                                    }
                                }
                                ?>
                            </div>
                            <?php if (get_option('rss_use_excerpt') == 1 && get_option('read_more_on') != 0): ?>
                                <div class="read_more">
                                    <?php
                                    global $post;
                                    if (get_option('read_more_on') == 2) {
                                        if (get_option('read_more_text') != '') {
                                            echo '<a class="bd-more-tag" href="' . get_permalink($post->ID) . '"><i class="fas fa-plus"></i> ' . get_option('read_more_text') . ' </a>';
                                        } else {
                                            echo ' <a class="bd-more-tag" href="' . get_permalink($post->ID) . '"><i class="fas fa-plus"></i> ' . __('Read more', 'blog-designer') . ' &raquo;</a>';
                                        }
                                    }
                                    ?>
                                </div><?php endif; ?></div></div>
                    <?php if (get_option('display_category') == 0 || (get_option('social_share') != 0 && (get_option('display_tag') == 0 || (get_option('facebook_link') == 0) || (get_option('twitter_link') == 0) || (get_option('google_link') == 0) || (get_option('linkedin_link') == 0) || ( get_option('pinterest_link') == 0 )))) { ?>
                        <footer class="blog_footer text-capitalize">
                            <?php if (get_option('display_category') == 0) { ?><p class="bd-margin-0"><span class="bd-categories"><i class="fas fa-folder"></i><?php
                                    $categories_list = get_the_category_list(', ');
                                    if ($categories_list):
                                        echo '<span class="bd-link-label">';
                                        _e('Categories', 'blog-designer');
                                        echo ' :&nbsp;';
                                        echo '</span>';                                        
                                        print_r($categories_list);
                                        $show_sep = true;
                                    endif;
                                    ?>
                                </span></p><?php
                            }
                            if (get_option('display_tag') == 0) {
                                $tags_list = get_the_tag_list('', ', ');
                                if ($tags_list):
                                    ?><p class="bd-margin-0"><span class="bd-tags"><i class="fas fa-bookmark"></i><?php
                                        echo '<span class="bd-link-label">';
                                        _e('Tags', 'blog-designer');
                                        echo ' :&nbsp;';
                                        echo '</span>';
                                        print_r($tags_list);
                                        $show_sep = true;
                                        ?>
                                    </span></p><?php
                                endif;
                            }

                            if (get_option('social_share') != 0 && ((get_option('facebook_link') == 0) || (get_option('twitter_link') == 0) || (get_option('google_link') == 0) || (get_option('linkedin_link') == 0) || ( get_option('pinterest_link') == 0 ))) {
                                ?><div class="social-component">
                                    <?php if (get_option('facebook_link') == 0): ?>
                                        <a data-share="facebook" data-href="https://www.facebook.com/sharer/sharer.php" data-url="<?php echo get_the_permalink(); ?>" class="bd-facebook-share bd-social-share"><i class="fab fa-facebook-f"></i></a><?php endif; 
                                    if (get_option('twitter_link') == 0): ?>
                                        <a data-share="twitter" data-href="https://twitter.com/share" data-text="<?php echo get_the_title(); ?>" data-url="<?php echo get_the_permalink(); ?>" class="bd-twitter-share bd-social-share"><i class="fab fa-twitter"></i></a><?php endif; 
                                    if (get_option('google_link') == 0): ?>
                                        <a data-share="google" data-href="https://plus.google.com/share" data-url="<?php echo get_the_permalink();?>" class="bd-google-share bd-social-share"><i class="fab fa-google-plus-g"></i></a><?php endif; 
                                    if (get_option('linkedin_link') == 0): ?>
                                        <a data-share="linkedin" data-href="https://www.linkedin.com/shareArticle" data-url="<?php echo get_the_permalink(); ?>" class="bd-linkedin-share bd-social-share"><i class="fab fa-linkedin-in"></i></a><?php endif; 
                                    if (get_option('pinterest_link') == 0):
                                        $pinterestimage = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
                                    ?><a data-share="pinterest" data-href="https://pinterest.com/pin/create/button/" data-url="<?php echo get_the_permalink(); ?>" data-mdia="<?php echo $pinterestimage[0]; ?>" data-description="<?php echo get_the_title(); ?>" class="bd-pinterest-share bd-social-share"> <i class="fab fa-pinterest-p"></i></a>
                                    <?php endif; ?>
                                </div><?php }
                                ?>
                        </footer>
                    <?php } ?>
                </div></div></div>
        <?php
    }

}

/**
 *
 * @global type $post
 * @return html display news design
 */
if (!function_exists('wp_news_template')) {

    function wp_news_template($alter) {
        ?>
        <div class="blog_template bdp_blog_template news <?php echo $alter; ?>">
            <?php
            $full_width_class = ' full_with_class';
            if (has_post_thumbnail()) {
                $full_width_class = '';
                ?>
                <div class="bd-post-image">
                    <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('full'); ?></a>
                </div>
                <?php
            }
            ?>
            <div class="post-content-div<?php echo $full_width_class; ?>">
                <div class="bd-blog-header">
                    <?php
                    $display_date = get_option('display_date');
                    if ($display_date == 0) {
                        $date_format = get_option('date_format');
                        ?><p class="bd_date_cover"><span class="date"><?php echo get_the_time($date_format); ?></span></p><?php } ?><h2 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php
                    $display_author = get_option('display_author');
                    $display_comment_count = get_option('display_comment_count');
                    if ($display_author == 0 || $display_comment_count == 0) {
                        ?>
                        <div class="bd-metadatabox"><?php
                            if ($display_author == 0) {
                                ?><a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>"><?php the_author(); ?>
                                </a><?php
                            }
                            if ($display_comment_count == 0) {
                                comments_popup_link(__('Leave a Comment', 'blog-designer'), __('1 Comment', 'blog-designer'), '% ' . __('Comments', 'blog-designer'), 'comments-link', __('Comments are off', 'blog-designer'));
                            }
                            ?></div><?php } ?></div>
                <div class="bd-post-content">
                    <?php
                    echo wp_bd_get_content(get_the_ID());
                    ?>

                    <?php
                    if (get_option('rss_use_excerpt') == 1 && get_option('read_more_on') == 1) {
                        if (get_option('read_more_text') != '') {
                            echo '<a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . get_option('read_more_text') . ' </a>';
                        } else {
                            echo '<a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . __("Read More", "blog-designer") . '</a>';
                        }
                    }
                    ?>
                </div>

                <?php
                $display_category = get_option('display_category');
                $display_tag = get_option('display_tag');
                if ($display_category == 0 || $display_tag == 0) {
                    ?>
                    <div class="post_cat_tag">
                        <?php if ($display_category == 0) { ?>
                            <span class="bd-category-link"><?php
                                $categories_list = get_the_category_list(', ');
                                if ($categories_list):
                                    echo '<i class="fas fa-bookmark"></i>';
                                    print_r($categories_list);
                                    $show_sep = true;
                                endif;
                                ?>
                            </span><?php
                        }
                        if ($display_tag == 0) {
                            $tags_list = get_the_tag_list('', ', ');
                            if ($tags_list):
                                ?>
                                <span class="bd-tags"><span class="bd-icon-tags"></span>&nbsp;<?php
                                    print_r($tags_list);
                                    $show_sep = true;
                                    ?>
                                </span><?php
                            endif;
                        }
                        ?>
                    </div>
                <?php } ?>

                <div class="bd-post-footer">
                    <?php if (get_option('social_share') != 0 && ((get_option('facebook_link') == 0) || (get_option('twitter_link') == 0) || (get_option('google_link') == 0) || (get_option('linkedin_link') == 0) || ( get_option('pinterest_link') == 0 ))) { ?>
                        <div class="social-component">
                            <?php if (get_option('facebook_link') == 0): ?>
                                <a data-share="facebook" data-href="https://www.facebook.com/sharer/sharer.php" data-url="<?php echo get_the_permalink(); ?>" class="bd-facebook-share bd-social-share"><i class="fab fa-facebook-f"></i></a><?php endif; ?><?php if (get_option('twitter_link') == 0): ?>
                                <a data-share="twitter" data-href="https://twitter.com/share" data-text="<?php echo get_the_title(); ?>" data-url="<?php echo get_the_permalink(); ?>" class="bd-twitter-share bd-social-share"><i class="fab fa-twitter"></i></a><?php endif; ?><?php if (get_option('google_link') == 0): ?>
                                <a data-share="google" data-href="https://plus.google.com/share" data-url="<?php echo get_the_permalink();?>" class="bd-google-share bd-social-share"><i class="fab fa-google-plus-g"></i></a><?php endif; ?><?php if (get_option('linkedin_link') == 0): ?>
                                <a data-share="linkedin" data-href="https://www.linkedin.com/shareArticle" data-url="<?php echo get_the_permalink(); ?>" class="bd-linkedin-share bd-social-share"><i class="fab fa-linkedin-in"></i></a><?php endif; ?><?php
                                if (get_option('pinterest_link') == 0):
                                    $pinterestimage = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
                                ?>
                                <a data-share="pinterest" data-href="https://pinterest.com/pin/create/button/" data-url="<?php echo get_the_permalink(); ?>" data-mdia="<?php echo $pinterestimage[0]; ?>" data-description="<?php echo get_the_title(); ?>" class="bd-pinterest-share bd-social-share"> <i class="fab fa-pinterest-p"></i></a>
                            <?php endif; ?>
                        </div>
                    <?php } ?>

                    <?php
                    if (get_option('rss_use_excerpt') == 1 && get_option('read_more_on') == 2) {
                        if (get_option('read_more_text') != '') {
                            echo '<a class="bd-more-tag" href="' . get_the_permalink() . '">' . get_option('read_more_text') . ' </a>';
                        } else {
                            echo ' <a class="bd-more-tag" href="' . get_the_permalink() . '">' . __("Read More", "blog-designer") . '</a>';
                        }
                    }
                    ?></div></div></div><?php
    }

}

/**
 *
 * @global type $wp_version
 * @return html Display setting options
 */
if (!function_exists('wp_blog_designer_menu_function')) {

    function wp_blog_designer_menu_function() {
        global $wp_version;
        ?>
        <div class="wrap">
            <h2><?php _e('Blog Designer Settings', 'blog-designer'); ?></h2>
            <div class="updated notice notice-success" id="message">
                <p><a href="<?php echo esc_url('https://www.solwininfotech.com/documents/wordpress/blog-designer/'); ?>" target="_blank"><?php _e('Read Online Documentation', 'blog-designer'); ?></a></p>
                <p><a href="<?php echo esc_url('http://blogdesigner.solwininfotech.com'); ?>" target="blank"><?php _e('See Live Demo', 'blog-designer'); ?></a></p>
                <p><?php echo __('Get access of ','blog-designer') .' <b>' .__('45+ new layouts', '') . '</b> ' . __('and', 'blog-designer') .' <b>' .__('150+ new premium', 'blog-designer') .'</b> ' .__(' features.', 'blog-designer'); ?> <b><a href="<?php echo esc_url('https://codecanyon.net/item/blog-designer-pro-for-wordpress/17069678?ref=solwin'); ?>" target="blank"><?php _e('Upgrade to PRO now', 'blog-designer'); ?></a></b></p>
            </div>
            <?php
            $view_post_link = (get_option('blog_page_display') != 0) ? '<span class="page_link"> <a target="_blank" href="' . get_permalink(get_option('blog_page_display')) . '"> ' . __('View Blog', 'blog-designer') . ' </a></span>' : '';
            if (isset($_REQUEST['bdRestoreDefault']) && isset($_GET['updated']) && 'true' == esc_attr($_GET['updated'])) {
                echo '<div class="updated" ><p>' . __('Blog Designer setting restored successfully.', 'blog-designer') . ' ' . $view_post_link . '</p></div>';  
               
            } else if (isset($_GET['updated']) && 'true' == esc_attr($_GET['updated'])) {    
                echo '<div class="updated" ><p>' . __('Blog Designer settings updated.', 'blog-designer') . ' ' . $view_post_link . '</p></div>';
              
            }
            $settings = get_option("wp_blog_designer_settings");
            if (isset($_SESSION['success_msg'])) {
                ?>
                <div class="updated is-dismissible notice settings-error"><?php
                    echo '<p>' . $_SESSION['success_msg'] . '</p>';
                    unset($_SESSION['success_msg']);
                    ?>
                </div><?php }
                ?>
            <form method="post" action="?page=designer_settings&action=save&updated=true" class="bd-form-class"><?php
                $page = '';
                if (isset($_GET['page']) && $_GET['page'] != '') {
                    $page = $_GET['page'];
                    ?>
                    <input type="hidden" name="originalpage" class="bdporiginalpage" value="<?php echo $page; ?>"><?php }
                ?>
                <div class="wl-pages" >
                    <div class="bd-settings-wrappers bd_poststuff">
                        <div class="bd-header-wrapper">
                            <div class="bd-logo-wrapper pull-left">
                                <h3><?php _e('Blog designer settings', 'blog-designer'); ?></h3>
                            </div>
                            <div class="pull-right">
                                <a id="bd-submit-button" title="<?php _e('Save Changes', 'blog-designer'); ?>" class="button">
                                    <span><i class="fas fa-check"></i>&nbsp;&nbsp;<?php _e('Save Changes', 'blog-designer'); ?></span>
                                </a>
                                <a id="bd-show-preview" title="<?php _e('Show Preview', 'blog-designer'); ?>" class="button show_preview button-hero pro-feature" href="#">
                                    <span><i class="fas fa-eye"></i>&nbsp;&nbsp;<?php _e('Preview', 'blog-designer'); ?></span>
                                </a>
                            </div>
                        </div>
                        <div class="bd-menu-setting">
                            <?php
                            $bdpgeneral_class = $dbptimeline_class = $bdpstandard_class = $bdptitle_class = $bdpcontent_class = $bdpmedia_class = $bdpslider_class = $bdpcustomreadmore_class = $bdpsocial_class = '';
                            $bdpgeneral_class_show = $dbptimeline_class_show = $bdpstandard_class_show = $bdptitle_class_show = $bdpcontent_class_show = $bdpmedia_class_show = $bdpslider_class_show = $bdpcustomreadmore_class_show = $bdpsocial_class_show = '';

                            if (bdp_postbox_classes('bdpgeneral', $page)) {
                                $bdpgeneral_class = 'class="bd-active-tab"';
                                $bdpgeneral_class_show = 'style="display: block;"';
                            } elseif (bdp_postbox_classes('dbptimeline', $page)) {
                                $dbptimeline_class = 'class="bd-active-tab"';
                                $dbptimeline_class_show = 'style="display: block;"';
                            } elseif (bdp_postbox_classes('bdpstandard', $page)) {
                                $bdpstandard_class = 'class="bd-active-tab"';
                                $bdpstandard_class_show = 'style="display: block;"';
                            } elseif (bdp_postbox_classes('bdptitle', $page)) {
                                $bdptitle_class = 'class="bd-active-tab"';
                                $bdptitle_class_show = 'style="display: block;"';
                            } elseif (bdp_postbox_classes('bdpcontent', $page)) {
                                $bdpcontent_class = 'class="bd-active-tab"';
                                $bdpcontent_class_show = 'style="display: block;"';
                            } elseif (bdp_postbox_classes('bdpmedia', $page)) {
                                $bdpmedia_class = 'class="bd-active-tab"';
                                $bdpmedia_class_show = 'style="display: block;"';
                            } elseif (bdp_postbox_classes('bdpslider', $page)) {
                                $bdpslider_class = 'class="bd-active-tab"';
                                $bdpslider_class_show = 'style="display: block;"';
                            } elseif (bdp_postbox_classes('bdpcustomreadmore', $page)) {
                                $bdpcustomreadmore_class = 'class="bd-active-tab"';
                                $bdpcustomreadmore_class_show = 'style="display: block;"';
                            } elseif (bdp_postbox_classes('bdpsocial', $page)) {
                                $bdpsocial_class = 'class="bd-active-tab"';
                                $bdpsocial_class_show = 'style="display: block;"';
                            } else {
                                $bdpgeneral_class = 'class="bd-active-tab"';
                                $bdpgeneral_class_show = 'style="display: block;"';
                            }
                            ?>
                            <ul class="bd-setting-handle">
                                <li data-show="bdpgeneral" <?php echo $bdpgeneral_class; ?>>
                                    <i class="fas fa-cog"></i>
                                    <span><?php _e('General Settings', 'blog-designer'); ?></span>
                                </li>
                                <li data-show="bdpstandard" <?php echo $bdpstandard_class; ?>>
                                    <i class="fas fa-gavel"></i>
                                    <span><?php _e('Standard Settings', 'blog-designer'); ?></span>
                                </li>
                                <li data-show="bdptitle" <?php echo $bdptitle_class; ?>>
                                    <i class="fas fa-text-width"></i>
                                    <span><?php _e('Post Title Settings', 'blog-designer'); ?></span>
                                </li>
                                <li data-show="bdpcontent" <?php echo $bdpcontent_class; ?>>
                                    <i class="far fa-file-alt"></i>
                                    <span><?php _e('Post Content Settings', 'blog-designer'); ?></span>
                                </li>
                                <li data-show="bdpmedia" <?php echo $bdpmedia_class; ?>>
                                    <i class="far fa-image"></i>
                                    <span><?php _e('Media Settings', 'blog-designer'); ?></span>
                                </li>
                                <li data-show="bdpsocial" <?php echo $bdpsocial_class; ?>>
                                    <i class="fas fa-share-alt"></i>
                                    <span><?php _e('Social Share Settings', 'blog-designer'); ?></span>
                                </li>
                            </ul>
                        </div>
                        <div id="bdpgeneral" class="postbox postbox-with-fw-options" <?php echo $bdpgeneral_class_show; ?>>
                            <ul class="bd-settings">
                                <li>
                                    <h3 class="bd-table-title"><?php _e('Select Blog Layout', 'blog-designer'); ?></h3>
                                    <div class="bd-left">
                                        <p class="bd-margin-bottom-50"><?php _e('Select your favorite layout from 6 free layouts.', 'blog-designer'); ?> <b><?php _e('Upgrade for just $39 to access 45+ brand new layouts and other premium features.', 'blog-designer');?></b></p>
                                        <p class="bd-margin-bottom-30"><b><?php _e('Current Template:', 'blog-designer'); ?></b> &nbsp;&nbsp;
                                            <span class="bd-template-name">
                                            <?php
                                            if (isset($settings['template_name'])) {
                                                echo str_replace('_', '-', $settings['template_name']) . ' ';
                                                _e('Template', 'blog-designer');
                                            }
                                            ?></span></p>
                                        <div class="bd_select_template_button_div">
                                            <input type="button" class="bd_select_template" value="<?php esc_attr_e('Select Other Template', 'blog-designer'); ?>">
                                        </div>
                                        <input type="hidden" name="template_name" id="template_name" value="<?php
                                        if (isset($settings["template_name"]) && $settings["template_name"] != '') {
                                            echo $settings["template_name"];
                                        }
                                        ?>" />
                                        <div class="bd_select_template_button_div">
                                            <a id="bd-reset-button" title="<?php _e('Reset Layout Settings', 'blog-designer'); ?>" class="bdp-restore-default button change-theme">
                                                <span><?php _e('Reset Layout Settings', 'blog-designer'); ?></span>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="bd-right">
                                        <div class="select-cover select-cover-template">
                                            <div class="bd_selected_template_image">
                                                <div <?php
                                                if (isset($settings['template_name']) && empty($settings['template_name'])) {
                                                    echo ' class="bd_no_template_found"';
                                                }
                                                ?>>
                                                        <?php
                                                        if (isset($settings['template_name']) && !empty($settings['template_name'])) {
                                                            $image_name = $settings['template_name'] . '.jpg';
                                                            ?>
                                                        <img src="<?php echo BLOGDESIGNER_URL . '/images/layouts/' . $image_name; ?>" alt="<?php
                                                        if (isset($settings['template_name'])) {
                                                            echo str_replace('_', '-', $settings['template_name']) . ' ';
                                                            esc_attr_e('Template', 'blog-designer');
                                                        }
                                                        ?>" title="<?php
                                                             if (isset($settings['template_name'])) {
                                                                 echo str_replace('_', '-', $settings['template_name']) . ' ';
                                                                 esc_attr_e('Template', 'blog-designer');
                                                             }
                                                             ?>" />
                                                        <label id="bd_template_select_name"><?php
                                                            if (isset($settings['template_name'])) {
                                                                echo str_replace('_', '-', $settings['template_name']) . ' ';
                                                                _e('Template', 'blog-designer');
                                                            }
                                                            ?>
                                                        </label>
                                                        <?php
                                                    } else {
                                                        _e('No template exist for selection', 'blog-designer');
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="bd-caution">
                                    <div class="bdp-setting-caution">
                                        <b><?php _e('Caution:', 'blog-designer'); ?></b>
                                        <?php
                                        _e('You are about to select the page for your layout. This will overwrite all the content on the page that you will select. Changes once lost connot be recovered. Please be cautious!', 'blog-designer');
                                        ?>
                                    </div>
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                            <?php _e(' Select Page for Blog ', 'blog-designer'); ?>
                                        </span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select page for display blog layout', 'blog-designer'); ?></span></span>
                                        <div class="select-cover">
                                            <?php
                                            echo wp_dropdown_pages(array(
                                                'name' => 'blog_page_display',
                                                'echo' => 0,
                                                'depth' => -1,
                                                'show_option_none' => '-- ' . __('Select Page', 'blog-designer') . ' --',
                                                'option_none_value' => '0',
                                                'selected' => get_option('blog_page_display')));
                                            ?>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                            <?php _e('Number of Posts to Display', 'blog-designer'); ?>
                                        </span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e(' Select number of posts to display on blog page', 'blog-designer'); ?></span></span>
                                        <div class="quantity">
                                            <input name="posts_per_page" type="number" step="1" min="1" id="posts_per_page" value="<?php echo get_option('posts_per_page'); ?>" class="small-text" onkeypress="return isNumberKey(event)" />
                                            <div class="quantity-nav">
                                                <div class="quantity-button quantity-up">+</div>
                                                <div class="quantity-button quantity-down">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                            <?php _e('Select Post Categories', 'blog-designer'); ?>
                                        </span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e(' Select post categories to filter posts via category', 'blog-designer'); ?></span></span>
                                        <?php $categories = get_categories(array('child_of' => '', 'hide_empty' => 1)); ?>
                                        <select data-placeholder="<?php esc_attr_e('Choose Post Categories', 'blog-designer'); ?>" class="chosen-select" multiple style="width:220px;" name="template_category[]" id="template_category">
                                            <?php foreach ($categories as $categoryObj): ?>
                                                <option value="<?php echo $categoryObj->term_id; ?>" <?php
                                                if (@in_array($categoryObj->term_id, $settings['template_category'])) {
                                                    echo 'selected="selected"';
                                                }
                                                ?>><?php echo $categoryObj->name; ?>
                                                </option><?php endforeach; ?>
                                        </select>
                                    </div>
                                </li>
                                <li>
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                            <?php _e('Select Post Tags', 'blog-designer'); ?>
                                        </span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e(' Select post tag to filter posts via tags', 'blog-designer'); ?></span></span>
                                        <?php
                                        $tags = get_tags();
                                        $template_tags = isset($settings['template_tags']) ? $settings['template_tags'] : array();
                                        ?>
                                        <select data-placeholder="<?php esc_attr_e('Choose Post Tags', 'blog-designer'); ?>" class="chosen-select" multiple style="width:220px;" name="template_tags[]" id="template_tags">
                                            <?php foreach ($tags as $tag) : ?>
                                            <option value="<?php echo $tag->term_id; ?>"<?php
                                                if (@in_array($tag->term_id, $template_tags)) {
                                                    echo 'selected="selected"';
                                                }
                                                ?>><?php echo $tag->name; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </li>
                                <li>
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                            <?php _e('Select Post Authors', 'blog-designer'); ?>
                                        </span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e(' Select post authors to filter posts via authors', 'blog-designer'); ?></span></span>
                                        <?php
                                        $blogusers = get_users('orderby=nicename&order=asc');
                                        $template_authors = isset($settings['template_authors']) ? $settings['template_authors'] : array();
                                        ?>
                                        <select data-placeholder="<?php esc_attr_e('Choose Post Authors', 'blog-designer'); ?>" class="chosen-select" multiple style="width:220px;" name="template_authors[]" id="template_authors">
                                            <?php foreach ($blogusers as $user) : ?>
                                            <option value="<?php echo $user->ID; ?>" <?php
                                            if (@in_array($user->ID, $template_authors)) {
                                                echo 'selected="selected"';
                                            }
                                            ?>><?php echo esc_html($user->display_name); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </li>
                                <li class="bd-display-settings">
                                    <h3 class="bd-table-title"><?php _e('Display Settings', 'blog-designer'); ?></h3>

                                    <div class="bd-typography-wrapper bd-button-settings">

                                        <div class="bd-typography-cover">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                    <?php _e('Post Category', 'blog-designer'); ?>
                                                </span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Show post category on blog layout', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <fieldset class="buttonset">
                                                    <input id="display_category_0" name="display_category" type="radio" value="0" <?php echo checked(0, get_option('display_category')); ?>/>
                                                    <label for="display_category_0"><?php _e('Yes', 'blog-designer'); ?></label>
                                                    <input id="display_category_1" name="display_category" type="radio" value="1" <?php echo checked(1, get_option('display_category')); ?> />
                                                    <label for="display_category_1"><?php _e('No', 'blog-designer'); ?></label>
                                                </fieldset>
                                            </div>
                                        </div>

                                        <div class="bd-typography-cover">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                    <?php _e('Post Tag', 'blog-designer'); ?>
                                                </span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Show post tag on blog layout', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <fieldset class="buttonset">
                                                    <input id="display_tag_0" name="display_tag" type="radio" value="0" <?php echo checked(0, get_option('display_tag')); ?>/>
                                                    <label for="display_tag_0"><?php _e('Yes', 'blog-designer'); ?></label>
                                                    <input id="display_tag_1" name="display_tag" type="radio" value="1" <?php echo checked(1, get_option('display_tag')); ?> />
                                                    <label for="display_tag_1"><?php _e('No', 'blog-designer'); ?></label>
                                                </fieldset>
                                            </div>
                                        </div>

                                        <div class="bd-typography-cover">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                    <?php _e('Post Author ', 'blog-designer'); ?>
                                                </span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Show post author on blog layout', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <fieldset class="buttonset">
                                                    <input id="display_author_0" name="display_author" type="radio" value="0" <?php echo checked(0, get_option('display_author')); ?>/>
                                                    <label for="display_author_0"><?php _e('Yes', 'blog-designer'); ?></label>
                                                    <input id="display_author_1" name="display_author" type="radio" value="1" <?php echo checked(1, get_option('display_author')); ?> />
                                                    <label for="display_author_1"><?php _e('No', 'blog-designer'); ?></label>
                                                </fieldset>
                                            </div>
                                        </div>

                                        <div class="bd-typography-cover">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                    <?php _e('Post Published Date', 'blog-designer'); ?>
                                                </span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Show post published date on blog layout', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <fieldset class="buttonset">
                                                    <input id="display_date_0" name="display_date" type="radio" value="0" <?php echo checked(0, get_option('display_date')); ?>/>
                                                    <label for="display_date_0"><?php _e('Yes', 'blog-designer'); ?></label>
                                                    <input id="display_date_1" name="display_date" type="radio" value="1" <?php echo checked(1, get_option('display_date')); ?> />
                                                    <label for="display_date_1"><?php _e('No', 'blog-designer'); ?></label>
                                                </fieldset>
                                            </div>
                                        </div>
                                        <div class="bd-typography-cover">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                    <?php _e('Comment Count', 'blog-designer'); ?>
                                                </span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Show post comment on blog layout', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <fieldset class="buttonset">
                                                    <input id="display_comment_count_0" name="display_comment_count" type="radio" value="0" <?php echo checked(0, get_option('display_comment_count')); ?>/>
                                                    <label for="display_comment_count_0"><?php _e('Yes', 'blog-designer'); ?></label>
                                                    <input id="display_comment_count_1" name="display_comment_count" type="radio" value="1" <?php echo checked(1, get_option('display_comment_count')); ?> />
                                                    <label for="display_comment_count_1"><?php _e('No', 'blog-designer'); ?></label>
                                                </fieldset>
                                            </div>
                                        </div>
                                        <div class="bd-typography-cover pro-feature">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                    <?php _e('Post Like', 'blog-designer'); ?>
                                                    <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                                </span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Show post like on blog layout', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                               <fieldset class="buttonset">
                                                    <input id="display_postlike_0" name="display_postlike" type="radio" value="0" />
                                                    <label for="display_postlike_0"><?php _e('Yes', 'blog-designer'); ?></label>
                                                    <input id="display_postlike_1" name="display_postlike" type="radio" value="1" checked="checked"/>
                                                    <label for="display_postlike_1"><?php _e('No', 'blog-designer'); ?></label>
                                                </fieldset>
                                            </div>
                                        </div>
                                        <div class="bd-typography-cover">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                    <?php _e('Display Sticky Post First', 'blog-designer'); ?>
                                                </span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Show Sticky Post first on blog layout', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <?php
                                                $display_sticky = get_option('display_sticky');
                                                $display_sticky = ($display_sticky != '') ? $display_sticky : 1;
                                                ?>
                                                <fieldset class="buttonset">
                                                    <input id="display_sticky_0" name="display_sticky" type="radio" value="0" <?php echo checked(0, $display_sticky); ?>/>
                                                    <label for="display_sticky_0"><?php _e('Yes', 'blog-designer'); ?></label>
                                                    <input id="display_sticky_1" name="display_sticky" type="radio" value="1" <?php echo checked(1, $display_sticky); ?> />
                                                    <label for="display_sticky_1"><?php _e('No', 'blog-designer'); ?></label>
                                                </fieldset>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                            <?php _e('Custom CSS', 'blog-designer'); ?>
                                        </span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon bd-tooltips-icon-textarea"><span class="bd-tooltips"><?php _e('Write a "Custom CSS" to add your additional design for blog page', 'blog-designer'); ?></span></span>
                                        <textarea name="custom_css" id="custom_css"><?php echo stripslashes(get_option('custom_css')); ?></textarea>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div id="bdpstandard" class="postbox postbox-with-fw-options" <?php echo $bdpstandard_class_show; ?>>
                            <ul class="bd-settings">
                                <li>
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                            <?php _e('Main Container Class Name', 'blog-designer'); ?>
                                        </span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Enter main container class name.', 'blog-designer'); ?> <br/> <?php _e('Leave it blank if you do not want to use it', 'blog-designer')?></span></span>
                                        <input type="text" name="main_container_class" id="main_container_class" placeholder="<?php esc_attr_e('main cover class name', 'blog-designer'); ?>" value="<?php echo isset($settings["main_container_class"]) ? $settings["main_container_class"] : ''; ?>"/>
                                    </div>
                                </li>

                                <li class="blog-templatecolor-tr">
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                            <?php _e('Blog Posts Template Color', 'blog-designer'); ?>
                                        </span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon bd-tooltips-icon-color"><span class="bd-tooltips"><?php _e('Select post template color', 'blog-designer'); ?></span></span>
                                        <input type="text" name="template_color" id="template_color" value="<?php echo isset($settings["template_color"]) ? $settings["template_color"] : ''; ?>"/>
                                    </div>
                                </li>

                                <li class="blog-template-tr">
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                            <?php _e('Background Color for Blog Posts', 'blog-designer'); ?>
                                        </span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon bd-tooltips-icon-color"><span class="bd-tooltips"><?php _e('Select post background color', 'blog-designer'); ?></span></span>
                                        <input type="text" name="template_bgcolor" id="template_bgcolor" value="<?php echo (isset($settings["template_bgcolor"])) ? $settings["template_bgcolor"] : ''; ?>"/>
                                    </div>
                                </li>
                                <li class="blog-template-tr alternative-tr">
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                            <?php _e('Alternative Background Color', 'blog-designer'); ?>
                                        </span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon bd-tooltips-icon-color"><span class="bd-tooltips"><?php _e('Enable/Disable alternative background color', 'blog-designer'); ?></span></span>
                                        <?php $bd_alter = get_option('template_alternativebackground'); ?>
                                        <fieldset class="buttonset">
                                            <input id="template_alternativebackground_0" name="template_alternativebackground" type="radio" value="0" <?php echo checked(0, $bd_alter); ?>/>
                                            <label for="template_alternativebackground_0"><?php _e('Yes', 'blog-designer'); ?></label>
                                            <input id="template_alternativebackground_1" name="template_alternativebackground" type="radio" value="1" <?php echo checked(1, $bd_alter); ?> />
                                            <label for="template_alternativebackground_1"><?php _e('No', 'blog-designer'); ?></label>
                                        </fieldset>
                                    </div>
                                </li>
                                <li class="alternative-color-tr">
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                            <?php _e('Choose Alternative Background Color', 'blog-designer'); ?>
                                        </span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon bd-tooltips-icon-color"><span class="bd-tooltips"><?php _e('Select alternative background color', 'blog-designer'); ?></span></span>
                                        <input type="text" name="template_alterbgcolor" id="template_alterbgcolor" value="<?php echo (isset($settings["template_alterbgcolor"])) ? $settings["template_alterbgcolor"] : ''; ?>"/>
                                    </div>
                                </li>
                                <li>
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                            <?php _e('Choose Link Color', 'blog-designer'); ?>
                                        </span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon bd-tooltips-icon-color"><span class="bd-tooltips"><?php _e('Select link color', 'blog-designer'); ?></span></span>
                                        <input type="text" name="template_ftcolor" id="template_ftcolor" value="<?php echo (isset($settings["template_ftcolor"])) ? $settings["template_ftcolor"] : ''; ?>"/>
                                    </div>
                                </li>
                                <li>
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                            <?php _e('Choose Link Hover Color', 'blog-designer'); ?>
                                        </span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon bd-tooltips-icon-color"><span class="bd-tooltips"><?php _e('Select link hover color', 'blog-designer'); ?></span></span>
                                        <input type="text" name="template_fthovercolor" id="template_fthovercolor" value="<?php echo (isset($settings["template_fthovercolor"])) ? $settings["template_fthovercolor"] : ''; ?>" data-default-color="<?php echo (isset($settings["template_fthovercolor"])) ? $settings["template_fthovercolor"] : ''; ?>"/>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div id="bdptitle" class="postbox postbox-with-fw-options" <?php echo $bdptitle_class_show; ?>>
                            <ul class="bd-settings">
                                <li class="pro-feature">
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                            <?php _e('Post Title Link', 'blog-designer'); ?>
                                        </span>
                                        <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select post title link', 'blog-designer'); ?></span></span>
                                        <fieldset class="buttonset">
                                            <input id="bdp_post_title_link_1" name="bdp_post_title_link" type="radio" value="1" checked="checked"/>
                                            <label for="bdp_post_title_link_1"><?php _e('Enable', 'blog-designer'); ?></label>
                                            <input id="bdp_post_title_link_0" name="bdp_post_title_link" type="radio" value="0"/>
                                            <label for="bdp_post_title_link_0"><?php _e('Disable', 'blog-designer'); ?></label>
                                        </fieldset>
                                    </div>
                                </li>
                                <li>
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                            <?php _e('Post Title Color', 'blog-designer'); ?>
                                        </span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon bd-tooltips-icon-color"><span class="bd-tooltips"><?php _e('Select post title color', 'blog-designer'); ?></span></span>
                                        <input type="text" name="template_titlecolor" id="template_titlecolor" value="<?php echo (isset($settings["template_titlecolor"])) ? $settings["template_titlecolor"] : ''; ?>"/>
                                    </div>
                                </li>
                                <li class="pro-feature">
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                            <?php _e('Post Title Link Hover Color', 'blog-designer'); ?>
                                        </span>
                                        <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon bd-tooltips-icon-color"><span class="bd-tooltips"><?php _e('Select post title link hover color', 'blog-designer'); ?></span></span>
                                        <input type="text" name="template_titlehovercolor" id="template_titlehovercolor" value=""/>
                                    </div>
                                </li>
                                <li>
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                            <?php _e('Post Title Background Color', 'blog-designer'); ?>
                                        </span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon bd-tooltips-icon-color"><span class="bd-tooltips"><?php _e('Select post title background color', 'blog-designer'); ?></span></span>
                                        <input type="text" name="template_titlebackcolor" id="template_titlebackcolor" value="<?php echo (isset($settings["template_titlebackcolor"])) ? $settings["template_titlebackcolor"] : ''; ?>"/>
                                    </div>
                                </li>
                                <li>
                                    <h3 class="bd-table-title"><?php _e('Typography Settings', 'blog-designer'); ?></h3>

                                    <div class="bd-typography-wrapper bd-typography-options">

                                        <div class="bd-typography-cover pro-feature">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                    <?php _e('Font Family', 'blog-designer'); ?>
                                                </span>
                                                <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select post title font family', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <div class="select-cover">
                                                    <select name="" id=""></select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bd-typography-cover">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                    <?php _e('Font Size (px)', 'blog-designer'); ?>
                                                </span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select post title font size', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <div class="grid_col_space range_slider_fontsize" id="template_postTitlefontsizeInput" data-value="<?php echo get_option('template_titlefontsize'); ?>"></div>
                                                <div class="slide_val">
                                                    <span></span>
                                                    <input class="grid_col_space_val range-slider__value" name="template_titlefontsize" id="template_titlefontsize" value="<?php echo get_option('template_titlefontsize'); ?>" onkeypress="return isNumberKey(event)" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bd-typography-cover pro-feature">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                    <?php _e('Font Weight', 'blog-designer'); ?>
                                                </span>
                                                <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select font weight', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <div class="select-cover">
                                                    <select name="" id="">
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bd-typography-cover pro-feature">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                    <?php _e('Line Height (px)', 'blog-designer'); ?>
                                                </span>
                                                <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Enter line height', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <div class="quantity">
                                                    <input type="number" name="" id="" step="0.1" min="0" value="1.5" onkeypress="return isNumberKey(event)">
                                                    <div class="quantity-nav">
                                                        <div class="quantity-button quantity-up">+</div>
                                                        <div class="quantity-button quantity-down">-</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bd-typography-cover pro-feature">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                    <?php _e('Italic Font Style', 'blog-designer'); ?>
                                                </span>
                                                <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Enable/Disable italic font style', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content ">
                                                <fieldset class="buttonset">
                                                    <input id="italic_font_title_0" name="italic_font_title" type="radio" value="0" />
                                                    <label for="italic_font_title_0"><?php _e('Yes', 'blog-designer'); ?></label>
                                                    <input id="italic_font_title_1" name="italic_font_title" type="radio" value="1" checked="checked" />
                                                    <label for="italic_font_title_1"><?php _e('No', 'blog-designer'); ?></label>
                                                </fieldset>
                                            </div>
                                        </div>
                                        <div class="bd-typography-cover pro-feature">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                    <?php _e('Text Transform', 'blog-designer'); ?>
                                                </span>
                                                <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select text transform style', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <div class="select-cover">
                                                    <select name="" id=""></select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bd-typography-cover pro-feature">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                    <?php _e('Text Decoration', 'blog-designer'); ?>
                                                </span>
                                                <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select text decoration style', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <div class="select-cover">
                                                    <select name="" id=""></select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bd-typography-cover pro-feature">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                    <?php _e('Letter Spacing (px)', 'blog-designer'); ?>
                                                </span>
                                                <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Enter letter spacing', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <div class="quantity">
                                                    <input type="number" name="" id="" step="1" min="0" value="0" onkeypress="return isNumberKey(event)">
                                                    <div class="quantity-nav">
                                                        <div class="quantity-button quantity-up">+</div>
                                                        <div class="quantity-button quantity-down">-</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div id="bdpcontent" class="postbox postbox-with-fw-options" <?php echo $bdpcontent_class_show; ?>>
                            <ul class="bd-settings">
                                <li>
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                            <?php _e('For each Article in a Feed, Show ', 'blog-designer'); ?>
                                        </span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('To display full text for each post, select full text option, otherwise select the summary option.', 'blog-designer'); ?></span></span>
                                        <?php
                                        $rss_use_excerpt = get_option('rss_use_excerpt');
                                        ?>
                                        <fieldset class="buttonset green">
                                            <input id="rss_use_excerpt_0" name="rss_use_excerpt" type="radio" value="0" <?php echo checked(0, $rss_use_excerpt); ?> />
                                            <label for="rss_use_excerpt_0"><?php _e('Full Text', 'blog-designer'); ?></label>
                                            <input id="rss_use_excerpt_1" name="rss_use_excerpt" type="radio" value="1" <?php echo checked(1, $rss_use_excerpt); ?> />
                                            <label for="rss_use_excerpt_1"><?php _e('Summary', 'blog-designer'); ?></label>
                                        </fieldset>
                                    </div>
                                </li>
                                <li class="excerpt_length">
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                            <?php _e('Post Content Length (words)', 'blog-designer'); ?>
                                        </span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Enter number of words for post content length', 'blog-designer'); ?></span></span>
                                        <div class="quantity">
                                            <input type="number" id="txtExcerptlength" name="txtExcerptlength" value="<?php echo get_option('excerpt_length'); ?>" min="0" step="1" class="small-text" onkeypress="return isNumberKey(event)">
                                            <div class="quantity-nav">
                                                <div class="quantity-button quantity-up">+</div>
                                                <div class="quantity-button quantity-down">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="excerpt_length pro-feature">
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                            <?php _e('Show Content From', 'blog-designer'); ?>
                                        </span>
                                        <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('To display text from post content or from post excerpt', 'blog-designer'); ?></span></span>
                                        <div class="select-cover">
                                            <select name="" id=""></select>
                                        </div>
                                    </div>
                                </li>
                                <li class="excerpt_length">
                                    <?php $display_html_tags = get_option('display_html_tags', 0); ?>
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                            <?php _e('Display HTML tags with Summary', 'blog-designer'); ?>
                                        </span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Show HTML tags with summary', 'blog-designer'); ?></span></span>
                                        <fieldset class="buttonset">
                                            <input id="display_html_tags_1" name="display_html_tags" type="radio" value="1" <?php echo checked(1, $display_html_tags); ?>/>
                                            <label for="display_html_tags_1"><?php _e('Yes', 'blog-designer'); ?></label>
                                            <input id="display_html_tags_0" name="display_html_tags" type="radio" value="0" <?php echo checked(0, $display_html_tags); ?> />
                                            <label for="display_html_tags_0"><?php _e('No', 'blog-designer'); ?></label>
                                        </fieldset>
                                    </div>
                                </li>
                                <li class="pro-feature">
                                    <?php $firstletter_big = 0; ?>
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                            <?php _e('First letter as Dropcap', 'blog-designer'); ?>
                                        </span>
                                        <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Enable first letter as Dropcap', 'blog-designer'); ?></span></span>
                                        <fieldset class="buttonset">
                                            <input id="firstletter_big_1" name="firstletter_big" type="radio" value="1" <?php echo checked(1, $firstletter_big); ?>/>
                                            <label for="firstletter_big_1"><?php _e('Yes', 'blog-designer'); ?></label>
                                            <input id="firstletter_big_0" name="firstletter_big" type="radio" value="0" <?php echo checked(0, $firstletter_big); ?> />
                                            <label for="firstletter_big_0"><?php _e('No', 'blog-designer'); ?></label>
                                        </fieldset>
                                    </div>
                                </li>
                                <li>
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                            <?php _e('Post Content Color', 'blog-designer'); ?>
                                        </span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon bd-tooltips-icon-color"><span class="bd-tooltips"><?php _e('Select post content color', 'blog-designer'); ?></span></span>
                                        <input type="text" name="template_contentcolor" id="template_contentcolor" value="<?php echo $settings["template_contentcolor"]; ?>"/>
                                    </div>
                                </li>

                                <li class="read_more_on">
                                    <h3 class="bd-table-title"><?php _e('Read More Settings', 'blog-designer'); ?></h3>

                                    <div style="margin-bottom: 15px;">
                                        <div class="bd-left">
                                            <span class="bd-key-title">
                                                <?php _e('Display Read More On', 'blog-designer'); ?>
                                            </span>
                                        </div>
                                        <div class="bd-right">
                                            <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select option for display read more button where to display', 'blog-designer'); ?></span></span>
                                            <?php
                                            $read_more_on = get_option('read_more_on');
                                            $read_more_on = ($read_more_on != '') ? $read_more_on : 2;
                                            ?>
                                            <fieldset class="buttonset three-buttomset">
                                                <input id="readmore_on_1" name="readmore_on" type="radio" value="1" <?php checked(1, $read_more_on); ?> />
                                                <label id="bdp-options-button" for="readmore_on_1" <?php checked(1, $read_more_on); ?>><?php _e('Same Line', 'blog-designer'); ?></label>
                                                <input id="readmore_on_2" name="readmore_on" type="radio" value="2" <?php checked(2, $read_more_on); ?> />
                                                <label id="bdp-options-button" for="readmore_on_2" <?php checked(2, $read_more_on); ?>><?php _e('Next Line', 'blog-designer'); ?></label>
                                                <input id="readmore_on_0" name="readmore_on" type="radio" value="0" <?php checked(0, $read_more_on); ?>/>
                                                <label id="bdp-options-button" for="readmore_on_0" <?php checked(0, $read_more_on); ?>><?php _e('Disable', 'blog-designer'); ?></label>
                                            </fieldset>
                                        </div>
                                    </div>

                                    <div class="bd-typography-wrapper bd-typography-options bd-readmore-options">
                                        <div class="bd-typography-cover read_more_text">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                <?php _e('Read More Text', 'blog-designer'); ?>
                                                </span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Enter text for read more button', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <input type="text" name="txtReadmoretext" id="txtReadmoretext" value="<?php echo get_option('read_more_text'); ?>" placeholder="Enter read more text">
                                            </div>
                                        </div>
                                        <div class="bd-typography-cover read_more_text_color">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                    <?php _e('Text Color', 'blog-designer'); ?>
                                                </span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select read more text color', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <input type="text" name="template_readmorecolor" id="template_readmorecolor" value="<?php echo (isset($settings["template_readmorecolor"])) ? $settings["template_readmorecolor"] : ''; ?>"/>
                                            </div>
                                        </div>
                                        <div class="bd-typography-cover read_more_text_background">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                <?php _e('Background Color', 'blog-designer'); ?>
                                                </span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select read more text background color', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <input type="text" name="template_readmorebackcolor" id="template_readmorebackcolor" value="<?php echo (isset($settings["template_readmorebackcolor"])) ? $settings["template_readmorebackcolor"] : ''; ?>"/>
                                            </div>
                                        </div>
                                        <div class="bd-typography-cover read_more_text_background pro-feature">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                <?php _e('Hover Background Color', 'blog-designer'); ?>
                                                </span>
                                                <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select Read more text hover background color', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <input type="text" name="" id="template_readmorebackcolor" value=""/>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                <li>
                                    <h3 class="bd-table-title"><?php _e('Typography Settings', 'blog-designer'); ?></h3>
                                    <div class="bd-typography-wrapper bd-typography-options">

                                        <div class="bd-typography-cover pro-feature">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                <?php _e('Font Family', 'blog-designer'); ?>
                                                </span>
                                                <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select post content font family', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <div class="select-cover">
                                                    <select name="" id=""></select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bd-typography-cover">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                <?php _e('Font Size (px)', 'blog-designer'); ?>
                                                </span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select font size for post content', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <div class="grid_col_space range_slider_fontsize" id="template_postContentfontsizeInput" data-value="<?php echo get_option('content_fontsize'); ?>"></div>
                                                <div class="slide_val">
                                                    <span></span>
                                                    <input class="grid_col_space_val range-slider__value" name="content_fontsize" id="content_fontsize" value="<?php echo get_option('content_fontsize'); ?>" onkeypress="return isNumberKey(event)" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bd-typography-cover pro-feature">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                    <?php _e('Font Weight', 'blog-designer'); ?>
                                                </span>
                                                <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select font weight', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <div class="select-cover">
                                                    <select name="" id="">
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bd-typography-cover pro-feature">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                    <?php _e('Line Height (px)', 'blog-designer'); ?>
                                                </span>
                                                <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Enter line height', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <div class="quantity">
                                                    <input type="number" name="" id="" step="0.1" min="0" value="1.5" onkeypress="return isNumberKey(event)">
                                                    <div class="quantity-nav">
                                                        <div class="quantity-button quantity-up">+</div>
                                                        <div class="quantity-button quantity-down">-</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bd-typography-cover pro-feature">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                    <?php _e('Italic Font Style', 'blog-designer'); ?>
                                                </span>
                                                <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Enable/Disable italic font style', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <fieldset class="buttonset">
                                                    <input id="italic_font_content_0" name="italic_font_content" type="radio" value="0" />
                                                    <label for="italic_font_content_0"><?php _e('Yes', 'blog-designer'); ?></label>
                                                    <input id="italic_font_content_1" name="italic_font_content" type="radio" value="1" checked="checked" />
                                                    <label for="italic_font_content_1"><?php _e('No', 'blog-designer'); ?></label>
                                                </fieldset>
                                            </div>
                                        </div>
                                        <div class="bd-typography-cover pro-feature">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                <?php _e('Text Transform', 'blog-designer'); ?>
                                                </span>
                                                <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select text transform style', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <div class="select-cover">
                                                    <select name="" id=""></select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bd-typography-cover pro-feature">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                <?php _e('Text Decoration', 'blog-designer'); ?>
                                                </span>
                                                <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select text decoration style', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <div class="select-cover">
                                                    <select name="" id=""></select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bd-typography-cover pro-feature">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                <?php _e('Letter Spacing (px)', 'blog-designer'); ?>
                                                </span>
                                                <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Enter letter spacing', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <div class="quantity">
                                                    <input type="number" name="" id="" step="1" min="0" value="0" onkeypress="return isNumberKey(event)">
                                                    <div class="quantity-nav">
                                                        <div class="quantity-button quantity-up">+</div>
                                                        <div class="quantity-button quantity-down">-</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div id="bdpmedia" class="postbox postbox-with-fw-options" <?php echo $bdpmedia_class_show; ?>>
                            <ul class="bd-settings">
                                <li class="pro-feature">
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                        <?php _e('Post Image Link', 'blog-designer'); ?>
                                        </span>
                                        <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Enable/Disable post image link','blog-designer'); ?></span></span>
                                        <fieldset class="buttonset">
                                            <input id="bdp_post_image_link_1" name="bdp_post_image_link" type="radio" value="1" checked="checked"/>
                                            <label for="bdp_post_image_link_1"><?php _e('Enable', 'blog-designer'); ?></label>
                                            <input id="bdp_post_image_link_0" name="bdp_post_image_link" type="radio" value="0" />
                                            <label for="bdp_post_image_link_0"><?php _e('Disable', 'blog-designer'); ?></label>
                                        </fieldset>
                                    </div>
                                </li>
                                <li class="pro-feature">
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                        <?php _e('Select Post Default Image', 'blog-designer'); ?>
                                        </span>
                                        <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select post default image', 'blog-designer'); ?></span></span>
                                        <input class="button bdp-upload_image_button" type="button" value="<?php esc_attr_e('Upload Image', 'blog-designer'); ?>">
                                    </div>
                                </li>
                                <li class="pro-feature">
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                        <?php _e('Select Post Media Size', 'blog-designer'); ?>
                                        </span>
                                        <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select size of post media', 'blog-designer'); ?></span></span>
                                        <div class="select-cover">
                                            <select name="" id=""> </select>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div id="bdpsocial" class="postbox postbox-with-fw-options" <?php echo $bdpsocial_class_show; ?>>
                            <ul class="bd-settings">
                                <li>
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                        <?php _e('Social Share', 'blog-designer'); ?>
                                        </span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Enable/Disable social share link', 'blog-designer'); ?></span></span>
                                        <fieldset class="bdp-social-options buttonset buttonset-hide" data-hide='1'>
                                            <input id="social_share_1" name="social_share" type="radio" value="1" <?php echo checked(1, get_option('social_share')); ?>/>
                                            <label id="social_share_1" for="social_share_1" <?php checked(1, get_option('social_share')); ?>><?php _e('Enable', 'blog-designer'); ?></label>
                                            <input id="social_share_0" name="social_share" type="radio" value="0" <?php echo checked(0, get_option('social_share')); ?> />
                                            <label id="social_share_0" for="social_share_0" <?php checked(0, get_option('social_share')); ?>><?php _e('Disable', 'blog-designer'); ?></label>
                                        </fieldset>
                                    </div>
                                </li>
                                <li class="pro-feature bd-social-share-options">
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                        <?php _e('Social Share Style', 'blog-designer'); ?>
                                        </span>
                                        <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select social share style', 'blog-designer'); ?></span></span>
                                        <fieldset class="buttonset green">
                                            <input id="social_style_0" name="social_style" type="radio" value="0" />
                                            <label for="social_style_0"><?php _e('Custom', 'blog-designer'); ?></label>
                                            <input id="social_style_1" name="social_style" type="radio" value="1" checked="checked" />
                                            <label for="social_style_1"><?php _e('Default', 'blog-designer'); ?></label>
                                        </fieldset>
                                    </div>
                                </li>
                                <li class="pro-feature bd-social-share-options">
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                        <?php _e('Available Icon Themes', 'blog-designer'); ?>
                                        </span>
                                        <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer');?></span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon bd-tooltips-icon-social"><span class="bd-tooltips"><?php _e('Select icon theme from available icon theme', 'blog-designer'); ?></span></span>
                                        <div class="social-share-theme social-share-td">
                                            <?php for ($i = 1; $i <= 10; $i++) { ?>
                                                <div class="social-cover social_share_theme_<?php echo $i; ?>">
                                                    <label>
                                                        <input type="radio" id="default_icon_theme_<?php echo $i; ?>" value="" name="default_icon_theme" />
                                                        <span class="bdp-social-icons facebook-icon bdp_theme_wrapper"></span>
                                                        <span class="bdp-social-icons gmail-icon bdp_theme_wrapper"></span>
                                                        <span class="bdp-social-icons twitter-icon bdp_theme_wrapper"></span>
                                                        <span class="bdp-social-icons linkdin-icon bdp_theme_wrapper"></span>
                                                        <span class="bdp-social-icons pin-icon bdp_theme_wrapper"></span>
                                                        <span class="bdp-social-icons whatsup-icon bdp_theme_wrapper"></span>
                                                        <span class="bdp-social-icons telegram-icon bdp_theme_wrapper"></span>
                                                        <span class="bdp-social-icons pocket-icon bdp_theme_wrapper"></span>
                                                        <span class="bdp-social-icons mail-icon bdp_theme_wrapper"></span>
                                                        <span class="bdp-social-icons reddit-icon bdp_theme_wrapper"></span>
                                                        <span class="bdp-social-icons tumblr-icon bdp_theme_wrapper"></span>
                                                        <span class="bdp-social-icons skype-icon bdp_theme_wrapper"></span>
                                                        <span class="bdp-social-icons wordpress-icon bdp_theme_wrapper"></span>
                                                    </label>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </li>
                                <li class="bd-social-share-options">
                                    <div class="bd-left">
                                        <span class="bd-key-title">
                                        <?php _e('Shape of Social Icon', 'blog-designer'); ?>
                                        </span>
                                    </div>
                                    <div class="bd-right">
                                        <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select shape of social icon', 'blog-designer'); ?></span></span>
                                        <fieldset class="buttonset green">
                                            <input id="social_icon_style_0" name="social_icon_style" type="radio" value="0" <?php echo checked(0, get_option('social_icon_style')); ?>/>
                                            <label for="social_icon_style_0"><?php _e('Circle', 'blog-designer'); ?></label>
                                            <input id="social_icon_style_1" name="social_icon_style" type="radio" value="1" <?php echo checked(1, get_option('social_icon_style')); ?> />
                                            <label for="social_icon_style_1"><?php _e('Square', 'blog-designer'); ?></label>
                                        </fieldset>
                                    </div>
                                </li>
                                <li class="bd-display-settings bd-social-share-options">
                                    <h3 class="bd-table-title"><?php _e('Social Share Links Settings', 'blog-designer'); ?></h3>
                                    <div class="bd-typography-wrapper">
                                        <div class="bd-typography-cover">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                <?php _e('Facebook Share Link', 'blog-designer'); ?>
                                                </span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Enable/Disable facebook share link', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <fieldset class="buttonset">
                                                    <input id="facebook_link_0" name="facebook_link" type="radio" value="0" <?php echo checked(0, get_option('facebook_link')); ?>/>
                                                    <label for="facebook_link_0"><?php _e('Yes', 'blog-designer'); ?></label>
                                                    <input id="facebook_link_1" name="facebook_link" type="radio" value="1" <?php echo checked(1, get_option('facebook_link')); ?> />
                                                    <label for="facebook_link_1"><?php _e('No', 'blog-designer'); ?></label>
                                                </fieldset>
                                            </div>
                                        </div>
                                        <div class="bd-typography-cover">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                    <?php _e('Linkedin Share Link', 'blog-designer'); ?>
                                                </span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Enable/Disable linkedin share link', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <fieldset class="buttonset">
                                                    <input id="linkedin_link_0" name="linkedin_link" type="radio" value="0" <?php echo checked(0, get_option('linkedin_link')); ?>/>
                                                    <label for="linkedin_link_0"><?php _e('Yes', 'blog-designer'); ?></label>
                                                    <input id="linkedin_link_1" name="linkedin_link" type="radio" value="1" <?php echo checked(1, get_option('linkedin_link')); ?> />
                                                    <label for="linkedin_link_1"><?php _e('No', 'blog-designer'); ?></label>
                                                </fieldset>
                                            </div>
                                        </div>
                                        <div class="bd-typography-cover">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                <?php _e('Google+ Share Link', 'blog-designer'); ?>
                                                </span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Enable/Disable Google+ Share link', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <fieldset class="buttonset">
                                                    <input id="google_link_0" name="google_link" type="radio" value="0" <?php echo checked(0, get_option('google_link')); ?>/>
                                                    <label for="google_link_0"><?php _e('Yes', 'blog-designer'); ?></label>
                                                    <input id="google_link_1" name="google_link" type="radio" value="1" <?php echo checked(1, get_option('google_link')); ?> />
                                                    <label for="google_link_1"><?php _e('No', 'blog-designer'); ?></label>
                                                </fieldset>
                                            </div>
                                        </div>
                                        <div class="bd-typography-cover">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                <?php _e('Pinterest Share link', 'blog-designer'); ?>
                                                </span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Enable/Disable Pinterest share link', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <fieldset class="buttonset">
                                                    <input id="pinterest_link_0" name="pinterest_link" type="radio" value="0" <?php echo checked(0, get_option('pinterest_link')); ?>/>
                                                    <label for="pinterest_link_0"><?php _e('Yes', 'blog-designer'); ?></label>
                                                    <input id="pinterest_link_1" name="pinterest_link" type="radio" value="1" <?php echo checked(1, get_option('pinterest_link')); ?> />
                                                    <label for="pinterest_link_1"><?php _e('No', 'blog-designer'); ?></label>
                                                </fieldset>
                                            </div>
                                        </div>
                                        <div class="bd-typography-cover">
                                            <div class="bdp-typography-label">
                                                <span class="bd-key-title">
                                                <?php _e('Twitter Share Link', 'blog-designer'); ?>
                                                </span>
                                                <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Enable/Disable twitter share link', 'blog-designer'); ?></span></span>
                                            </div>
                                            <div class="bd-typography-content">
                                                <fieldset class="buttonset">
                                                    <input id="twitter_link_0" name="twitter_link" type="radio" value="0" <?php echo checked(0, get_option('twitter_link')); ?>/>
                                                    <label for="twitter_link_0"><?php _e('Yes', 'blog-designer'); ?></label>
                                                    <input id="twitter_link_1" name="twitter_link" type="radio" value="1" <?php echo checked(1, get_option('twitter_link')); ?> />
                                                    <label for="twitter_link_1"><?php _e('No', 'blog-designer'); ?></label>
                                                </fieldset>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="inner">
                    <input type="submit" style="display: none;" class="save_blogdesign" value="<?php _e('Save Changes', 'blog-designer'); ?>" />
                    <p class="wl-saving-warning"></p>
                    <div class="clear"></div>
                </div>
            </form>
            <div class="bd-admin-sidebar hidden">
                <div class="bd-help">
                    <h2><?php _e('Help to improve this plugin!', 'blog-designer'); ?></h2>
                    <div class="help-wrapper">
                        <span><?php _e('Enjoyed this plugin?', 'blog-designer'); ?>&nbsp;</span>
                        <span><?php _e('You can help by', 'blog-designer'); ?>
                            <a href="https://wordpress.org/support/plugin/blog-designer/reviews?filter=5#new-post" target="_blank">&nbsp;
                                <?php _e('rate this plugin 5 stars!', 'blog-designer'); ?>
                            </a>
                        </span>
                        <div class="bd-total-download">
                            <?php _e('Downloads:', 'blog-designer'); ?><?php get_total_downloads(); ?>
                            <?php
                            if ($wp_version > 3.8) {
                                wp_custom_star_rating();
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="useful_plugins">
                    <h2>
                        <?php _e('Blog Designer PRO', 'blog-designer'); ?>
                    </h2>
                    <div class="help-wrapper">
                        <div class="pro-content">
                            <ul class="advertisementContent">
                                <li><?php _e("45+ Beautiful Blog Templates", 'blog-designer'); ?></li>
                                <li><?php _e("5+ Unique Timeline Templates", 'blog-designer'); ?></li>
                                <li><?php _e("10 Unique Grid Templates", 'blog-designer'); ?></li>
                                <li><?php _e("3 Unique Slider Templates", 'blog-designer'); ?></li>
                                <li><?php _e("200+ Blog Layout Variations", 'blog-designer'); ?></li>
                                <li><?php _e("Multiple Single Post Layout options", 'blog-designer'); ?></li>
                                <li><?php _e("Category, Tag, Author & Date Layouts", 'blog-designer'); ?></li>
                                <li><?php _e("Post Type & Taxonomy Filter", 'blog-designer'); ?></li>
                                <li><?php _e("800+ Google Font Support", 'blog-designer'); ?></li>
                                <li><?php _e("600+ Font Awesome Icons Support", 'blog-designer'); ?></li>
                            </ul>
                            <p class="pricing_change"><?php _e("Now only at", 'blog-designer'); ?> <ins>39</ins></p>
                        </div>
                        <div class="pre-book-pro">
                            <a href="<?php echo esc_url('https://codecanyon.net/item/blog-designer-pro-for-wordpress/17069678?ref=solwin'); ?>" target="_blank">
                                <?php _e('Buy Now on Codecanyon', 'blog-designer'); ?>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="bd-support">
                    <h3><?php _e('Need Support?', 'blog-designer'); ?></h3>
                    <div class="help-wrapper">
                        <span><?php _e('Check out the', 'blog-designer'); ?>
                            <a href="<?php echo esc_url('https://wordpress.org/plugins/blog-designer/faq/'); ?>" target="_blank"><?php _e('FAQs', 'blog-designer'); ?></a>
                            <?php _e('and', 'blog-designer'); ?>
                            <a href="<?php echo esc_url('https://wordpress.org/support/plugin/blog-designer'); ?>" target="_blank"><?php _e('Support Forums', 'blog-designer'); ?></a>
                        </span>
                    </div>
                </div>
                <div class="bd-support">
                    <h3><?php _e('Share & Follow Us', 'blog-designer'); ?></h3>
                    <!-- Twitter -->
                    <div class="help-wrapper">
                        <div style='display:block;margin-bottom:8px;'>
                            <a href="<?php echo esc_url('https://twitter.com/solwininfotech'); ?>" class="twitter-follow-button" data-show-count="false" data-show-screen-name="true" data-dnt="true">Follow @solwininfotech</a>
                            <script>!function (d, s, id) {
                                    var js, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location) ? 'http' : 'https';
                                    if (!d.getElementById(id)) {
                                        js = d.createElement(s);
                                        js.id = id;
                                        js.src = p + '://platform.twitter.com/widgets.js';
                                        fjs.parentNode.insertBefore(js, fjs);
                                    }
                                }(document, 'script', 'twitter-wjs');</script>
                        </div>
                        <!-- Facebook -->
                        <div style='display:block;margin-bottom:10px;'>
                            <div id="fb-root"></div>
                            <script>(function (d, s, id) {
                                    var js, fjs = d.getElementsByTagName(s)[0];
                                    if (d.getElementById(id))
                                        return;
                                    js = d.createElement(s);
                                    js.id = id;
                                    js.src = "//connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v2.5";
                                    fjs.parentNode.insertBefore(js, fjs);
                                }(document, 'script', 'facebook-jssdk'));</script>
                            <div class="fb-share-button" data-href="https://wordpress.org/plugins/blog-designer/" data-layout="button"></div>
                        </div>
                        <!-- Google Plus -->
                        <div style='display:block;margin-bottom:8px;'>
                            <!-- Place this tag where you want the +1 button to render. -->
                            <div class="g-plusone" data-count="false" data-href="https://wordpress.org/plugins/blog-designer/"></div>
                            <!-- Place this tag after the last +1 button tag. -->
                            <script type="text/javascript">
                                (function () {
                                    var po = document.createElement('script');
                                    po.type = 'text/javascript';
                                    po.async = true;
                                    po.src = 'https://apis.google.com/js/platform.js';
                                    var s = document.getElementsByTagName('script')[0];
                                    s.parentNode.insertBefore(po, s);
                                })();
                            </script>
                        </div>
                        <div style='display:block;margin-bottom:8px;'>
                            <script src="//platform.linkedin.com/in.js" type="text/javascript"></script>
                            <script type="IN/Share" data-url="https://wordpress.org/plugins/blog-designer/" ></script>
                        </div>
                    </div>
                </div>
            </div>
            <div id="bd_popupdiv" class="bd-template-popupdiv" style="display: none;">
                <?php
                $tempate_list = bd_template_list();

                foreach ($tempate_list as $key => $value) {
                    $classes = explode(' ', $value['class']);
                    foreach ($classes as $class)
                        $all_class[] = $class;
                }
                $count = array_count_values($all_class);
                ?>
                <ul class="bd_template_tab">
                    <li class="bd_current_tab">
                        <a href="#all"><?php _e('All', 'blog-designer'); ?></a>
                    </li>
                    <li>
                        <a href="#free"><?php echo __('Free', 'blog-designer') . ' ('. $count['free'] .')'; ?></a>
                    </li>
                    <li>
                        <a href="#full-width"><?php echo __('Full Width', 'blog-designer') . ' ('. $count['full-width'] .')'; ?></a>
                    </li>
                    <li>
                        <a href="#grid"><?php echo __('Grid', 'blog-designer') . ' ('. $count['grid'] .')'; ?></a>
                    </li>
                    <li>
                        <a href="#masonry"><?php echo __('Masonry', 'blog-designer') . ' ('. $count['masonry'] .')'; ?></a>
                    </li>
                    <li>
                        <a href="#magazine"><?php echo __('Magazine', 'blog-designer') . ' ('. $count['magazine'] .')'; ?></a>
                    </li>
                    <li>
                        <a href="#timeline"><?php echo __('Timeline', 'blog-designer') . ' ('. $count['timeline'] .')'; ?></a>
                    </li>
                    <li>
                        <a href="#slider"><?php echo __('Slider', 'blog-designer') . ' ('. $count['slider'] .')'; ?></a>
                    </li>
                    <div class="bd-template-search-cover">
                        <input type="text" class="bd-template-search" id="bd-template-search" placeholder="<?php _e('Search Template', 'blog-designer'); ?>" />
                        <span class="bd-template-search-clear"></span>
                    </div>
                </ul>

                <?php
                echo '<div class="bd-template-cover">';
                foreach ($tempate_list as $key => $value) {
                    if ($key == 'classical' || $key == 'lightbreeze' || $key == 'spektrum' || $key == 'evolution' || $key == 'timeline' || $key == 'news') {
                        $class = 'bd-lite';
                    } else {
                        $class = 'bp-pro';
                    }
                    ?>
                    <div class="bd-template-thumbnail <?php echo $value['class'] . ' ' . $class; ?>">
                        <div class="bd-template-thumbnail-inner">
                            <img src="<?php echo BLOGDESIGNER_URL . '/images/layouts/' . $value['image_name']; ?>" data-value="<?php echo $key; ?>" alt="<?php echo $value['template_name']; ?>" title="<?php echo $value['template_name']; ?>">
                            <?php if ($class == 'bd-lite') { ?>
                                <div class="bd-hover_overlay">
                                    <div class="bd-popup-template-name">
                                        <div class="bd-popum-select"><a href="#"><?php _e('Select Template', 'blog-designer'); ?></a></div>
                                        <div class="bd-popup-view"><a href="<?php echo $value['demo_link']; ?>" target="_blank"><?php _e('Live Demo', 'blog-designer'); ?></a></div>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div class="bd_overlay"></div>
                                <div class="bd-img-hover_overlay">
                                    <img src="<?php echo BLOGDESIGNER_URL . '/images/pro-tag.png' ?>" alt="Available in Pro" />
                                </div>
                                <div class="bd-hover_overlay">
                                    <div class="bd-popup-template-name">
                                        <div class="bd-popup-view"><a href="<?php echo $value['demo_link']; ?>" target="_blank"><?php _e('Live Demo', 'blog-designer'); ?></a></div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <span class="bd-span-template-name"><?php echo $value['template_name']; ?></span>
                    </div>
                    <?php
                }
                echo '</div>';
                echo '<h3 class="no-template" style="display: none;">' . __('No template found. Please try again', 'blog-designer') . '</h3>';
                ?>

            </div>
            <div id="bd-advertisement-popup">
                <div class="bd-advertisement-cover">
                    <a class="bd-advertisement-link" target="_blank" href="<?php echo esc_url('https://codecanyon.net/item/blog-designer-pro-for-wordpress/17069678?ref=solwin'); ?>">
                        <img src="<?php echo BLOGDESIGNER_URL . '/images/bd_advertisement_popup.png'; ?>" />
                    </a>
                </div>
            </div>
        </div>
        <?php
    }

}

/*
 * Display Optin form
 */
if(!function_exists('wp_blog_designer_welcome_function')) {
    function wp_blog_designer_welcome_function() {
        global $wpdb;
        $bd_admin_email = get_option('admin_email');
        ?>
        <div class='bd_header_wizard'>
            <p><?php echo esc_attr(__('Hi there!', 'blog-designer')); ?></p>
            <p><?php echo esc_attr(__("Don't ever miss an opportunity to opt in for Email Notifications / Announcements about exciting New Features and Update Releases.", 'blog-designer')); ?></p>
            <p><?php echo esc_attr(__('Contribute in helping us making our plugin compatible with most plugins and themes by allowing to share non-sensitive information about your website.', 'blog-designer')); ?></p>
            <p><b><?php echo esc_attr(__('Email Address for Notifications', 'blog-designer')); ?> :</b></p>
            <p><input type='email' value='<?php echo $bd_admin_email; ?>' id='bd_admin_email' /></p>
            <p><?php echo esc_attr(__("If you're not ready to Opt-In, that's ok too!", 'blog-designer')); ?></p>
            <p><b><?php echo esc_attr(__('Blog Designer will still work fine.', 'blog-designer')); ?> :</b></p>
            <p onclick="bd_show_hide_permission()" class='bd_permission'><b><?php echo esc_attr(__('What permissions are being granted?', 'blog-designer')); ?></b></p>
            <div class='bd_permission_cover' style='display:none'>
                <div class='bd_permission_row'>
                    <div class='bd_50'>
                        <i class='dashicons dashicons-admin-users gb-dashicons-admin-users'></i>
                        <div class='bd_50_inner'>
                            <label><?php echo esc_attr(__('User Details', 'blog-designer')); ?></label>
                            <label><?php echo esc_attr(__('Name and Email Address', 'blog-designer')); ?></label>
                        </div>
                    </div>
                    <div class='bd_50'>
                        <i class='dashicons dashicons-admin-plugins gb-dashicons-admin-plugins'></i>
                        <div class='bd_50_inner'>
                            <label><?php echo esc_attr(__('Current Plugin Status', 'blog-designer')); ?></label>
                            <label><?php echo esc_attr(__('Activation, Deactivation and Uninstall', 'blog-designer')); ?></label>
                        </div>
                    </div>
                </div>
                <div class='bd_permission_row'>
                    <div class='bd_50'>
                        <i class='dashicons dashicons-testimonial gb-dashicons-testimonial'></i>
                        <div class='bd_50_inner'>
                            <label><?php echo esc_attr(__('Notifications', 'blog-designer')); ?></label>
                            <label><?php echo esc_attr(__('Updates & Announcements', 'blog-designer')); ?></label>
                        </div>
                    </div>
                    <div class='bd_50'>
                        <i class='dashicons dashicons-welcome-view-site gb-dashicons-welcome-view-site'></i>
                        <div class='bd_50_inner'>
                            <label><?php echo esc_attr(__('Website Overview', 'blog-designer')); ?></label>
                            <label><?php echo esc_attr(__('Site URL, WP Version, PHP Info, Plugins & Themes Info', 'blog-designer')); ?></label>
                        </div>
                    </div>
                </div>
            </div>
            <p>
                <input type='checkbox' class='bd_agree' id='bd_agree_gdpr' value='1' />
                <label for='bd_agree_gdpr' class='bd_agree_gdpr_lbl'><?php echo esc_attr(__('By clicking this button, you agree with the storage and handling of your data as mentioned above by this website. (GDPR Compliance)', 'blog-designer')); ?></label>
            </p>
            <p class='bd_buttons'>
                <a href="javascript:void(0)" class='button button-secondary' onclick="bd_submit_optin('cancel')"><?php echo esc_attr(__('Skip', 'blog-designer')); echo ' &amp; '; echo esc_attr(__('Continue', 'blog-designer')); ?></a>
                <a href="javascript:void(0)" class='button button-primary' onclick="bd_submit_optin('submit')"><?php echo esc_attr(__('Opt-In', 'blog-designer')); echo ' &amp; '; echo esc_attr(__('Continue', 'blog-designer')); ?></a>
            </p>
        </div>
        <?php
    }
}

/**
 *
 * @param type $args
 * @return type Display Pagination
 */
if (!function_exists('designer_pagination')) {

    function designer_pagination($args = array()) {
        // Don't print empty markup if there's only one page.
        if ($GLOBALS['wp_query']->max_num_pages < 2) {
            return;
        }
        $navigation = '';
        $paged = get_query_var('paged') ? intval(get_query_var('paged')) : 1;
        $pagenum_link = html_entity_decode(get_pagenum_link());
        $query_args = array();
        $url_parts = explode('?', $pagenum_link);

        if (isset($url_parts[1])) {
            wp_parse_str($url_parts[1], $query_args);
        }

        $pagenum_link = remove_query_arg(array_keys($query_args), $pagenum_link);
        $pagenum_link = trailingslashit($pagenum_link) . '%_%';

        $format = $GLOBALS['wp_rewrite']->using_index_permalinks() && !strpos($pagenum_link, 'index.php') ? 'index.php/' : '';
        $format .= $GLOBALS['wp_rewrite']->using_permalinks() ? user_trailingslashit('page/%#%', 'paged') : '?paged=%#%';

        // Set up paginated links.
        $links = paginate_links(array(
            'base' => $pagenum_link,
            'format' => $format,
            'total' => $GLOBALS['wp_query']->max_num_pages,
            'current' => $paged,
            'mid_size' => 1,
            'add_args' => array_map('urlencode', $query_args),
            'prev_text' => '&larr; ' . __('Previous', 'blog-designer'),
            'next_text' => __('Next', 'blog-designer') . ' &rarr;',
            'type' => 'list',
        ));

        if ($links) :
            $navigation .= '<nav class="navigation paging-navigation" role="navigation">';
            $navigation .= $links;
            $navigation .= '</nav>';
        endif;
        return $navigation;
    }

}

class BDesigner {

    protected $args;

    function __construct($args) {
        $this->args = $args;
    }

    function __get($key) {
        return $this->args[$key];
    }

    function get_pagination_args() {
        global $numpages;

        $query = $this->query;

        switch ($this->type) {
            case 'multipart':
                // Multipart page
                $posts_per_page = 1;
                $paged = max(1, absint(get_query_var('page')));
                $total_pages = max(1, $numpages);
                break;
            case 'users':
                // WP_User_Query
                $posts_per_page = $query->query_vars['number'];
                $paged = max(1, floor($query->query_vars['offset'] / $posts_per_page) + 1);
                $total_pages = max(1, ceil($query->total_users / $posts_per_page));
                break;
            default:
                // WP_Query
                $posts_per_page = intval($query->get('posts_per_page'));
                $paged = max(1, absint($query->get('paged')));
                $total_pages = max(1, absint($query->max_num_pages));
                break;
        }

        return array($posts_per_page, $paged, $total_pages);
    }

    function get_single($page, $class, $raw_text, $format = '%PAGE_NUMBER%') {
        if (empty($raw_text))
            return '';

        $text = str_replace($format, number_format_i18n($page), $raw_text);

        return "<a href='" . esc_url($this->get_url($page)) . "' class='$class'>$text</a>";
    }

    function get_url($page) {
        return ( 'multipart' == $this->type ) ? get_multipage_link($page) : get_pagenum_link($page);
    }

}

/**
 *
 * @return int
 */
if (!function_exists('blogdesignerpaged')) {

    function blogdesignerpaged() {
        if (strstr($_SERVER['REQUEST_URI'], 'paged') || strstr($_SERVER['REQUEST_URI'], 'page')) {
            if (isset($_REQUEST['paged'])) {
                $paged = $_REQUEST['paged'];
            } else {
                $uri = explode('/', $_SERVER['REQUEST_URI']);
                $uri = array_reverse($uri);
                $paged = $uri[1];
            }
        } else {
            $paged = 1;
        }
        /* Pagination issue on home page */
        if (is_front_page()) {
            $paged = get_query_var('page') ? intval(get_query_var('page')) : 1;
        } else {
            $paged = get_query_var('paged') ? intval(get_query_var('paged')) : 1;
        }

        return $paged;
    }

}

/**
 * admin scripts
 */
if (!function_exists('bd_admin_scripts')) {

    function bd_admin_scripts() {
        $screen = get_current_screen();
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/blog-designer/blog-designer.php', $markup = true, $translate = true);
        $current_version = $plugin_data['Version'];
        $old_version = get_option('bd_version');
        if ($old_version != $current_version) {
            update_option('is_user_subscribed_cancled', '');
            update_option('bd_version', $current_version);
        }
        if ((get_option('is_user_subscribed') != 'yes' && get_option('is_user_subscribed_cancled') != 'yes') || ($screen->base == 'plugins')) {
            wp_enqueue_script('thickbox');
            wp_enqueue_style('thickbox');
        }
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-slider');
    }

}
add_action('admin_enqueue_scripts', 'bd_admin_scripts');

/**
 * start session if not
 */
if (!function_exists('bd_session_start')) {

    function bd_session_start() {
        if (version_compare(phpversion(), "5.4.0") != -1) {
            if (session_status() == PHP_SESSION_DISABLED) {
                session_start();
            }
        } else {
            if (session_id() == '') {
                session_start();
            }
        }
    }

}
add_action('init', 'bd_session_start');

/**
 * subscribe email form
 */
if (!function_exists('bd_subscribe_mail')) {

    function bd_subscribe_mail() {
        ?>
        <div id="sol_deactivation_widget_cover_bd" style="display:none;">
            <div class="sol_deactivation_widget">
                <h3><?php _e('If you have a moment, please let us know why you are deactivating. We would like to help you in fixing the issue.', 'blog-designer'); ?></h3>
                <form id="frmDeactivationbd" name="frmDeactivation" method="post" action="">
                    <ul class="sol_deactivation_reasons_ul">
                        <?php $i = 1; ?>
                        <li>
                            <input class="sol_deactivation_reasons" checked="checked" name="sol_deactivation_reasons_bd" type="radio" value="<?php echo $i; ?>" id="bd_reason_<?php echo $i; ?>">
                            <label for="bd_reason_<?php echo $i; ?>"><?php _e('I am going to upgrade to PRO version', 'blog-designer'); ?></label>
                        </li>
                        <?php $i++; ?>
                        <li>
                            <input class="sol_deactivation_reasons" name="sol_deactivation_reasons_bd" type="radio" value="<?php echo $i; ?>" id="bd_reason_<?php echo $i; ?>">
                            <label for="bd_reason_<?php echo $i; ?>"><?php _e('The plugin suddenly stopped working', 'blog-designer'); ?></label>
                        </li>
                        <?php $i++; ?>
                        <li>
                            <input class="sol_deactivation_reasons" name="sol_deactivation_reasons_bd" type="radio" value="<?php echo $i; ?>" id="bd_reason_<?php echo $i; ?>">
                            <label for="bd_reason_<?php echo $i; ?>"><?php _e('The plugin was not working', 'blog-designer'); ?></label>
                        </li>
                        <li class="sol_deactivation_reasons_solution">
                            <b>Please check your <a target="_blank" href="<?php echo admin_url('options-reading.php'); ?>">reading settings</a>. Read our <a href="https://www.solwininfotech.com/knowledgebase/#" target="_blank">knowdgebase</a> for more detail.</b>
                        </li>
                        <?php $i++; ?>
                        <li>
                            <input class="sol_deactivation_reasons" name="sol_deactivation_reasons_bd" type="radio" value="<?php echo $i; ?>" id="bd_reason_<?php echo $i; ?>">
                            <label for="bd_reason_<?php echo $i; ?>"><?php _e('I have configured plugin but not working with my blog page', 'blog-designer'); ?></label>
                        </li>
                        <?php $i++; ?>
                        <li>
                            <input class="sol_deactivation_reasons" name="sol_deactivation_reasons_bd" type="radio" value="<?php echo $i; ?>" id="bd_reason_<?php echo $i; ?>">
                            <label for="bd_reason_<?php echo $i; ?>"><?php _e('Installed & configured well but disturbed my blog page design', 'blog-designer'); ?></label>
                        </li>
                        <?php $i++; ?>
                        <li>
                            <input class="sol_deactivation_reasons" name="sol_deactivation_reasons_bd" type="radio" value="<?php echo $i; ?>" id="bd_reason_<?php echo $i; ?>">
                            <label for="bd_reason_<?php echo $i; ?>"><?php _e("My theme's blog page is better than plugin's blog page design", 'blog-designer'); ?></label>
                        </li>
                        <?php $i++; ?>
                        <li>
                            <input class="sol_deactivation_reasons" name="sol_deactivation_reasons_bd" type="radio" value="<?php echo $i; ?>" id="bd_reason_<?php echo $i; ?>">
                            <label for="bd_reason_<?php echo $i; ?>"><?php _e('The plugin broke my site completely', 'blog-designer'); ?></label>
                        </li>
                        <?php $i++; ?>
                        <li>
                            <input class="sol_deactivation_reasons" name="sol_deactivation_reasons_bd" type="radio" value="<?php echo $i; ?>" id="bd_reason_<?php echo $i; ?>">
                            <label for="bd_reason_<?php echo $i; ?>"><?php _e('No any reason', 'blog-designer'); ?></label>
                        </li>
                        <?php $i++; ?>
                        <li>
                            <input class="sol_deactivation_reasons" name="sol_deactivation_reasons_bd" type="radio" value="<?php echo $i; ?>" id="bd_reason_<?php echo $i; ?>">
                            <label for="bd_reason_<?php echo $i; ?>"><?php _e('Other', 'blog-designer'); ?></label><br/>
                            <input style="display:none;width: 90%" value="" type="text" name="sol_deactivation_reason_other_bd" class="sol_deactivation_reason_other_bd" />
                        </li>
                    </ul>
                    <p>
                        <input type='checkbox' class='bd_agree' id='bd_agree_gdpr_deactivate' value='1' />
                        <label for='bd_agree_gdpr_deactivate' class='bd_agree_gdpr_lbl'><?php echo esc_attr(__('By clicking this button, you agree with the storage and handling of your data as mentioned above by this website. (GDPR Compliance)', 'blog-designer')); ?></label>
                    </p>
                    <a onclick='bd_submit_optin("deactivate")' class="button button-secondary"><?php _e('Submit', 'blog-designer'); echo ' &amp; '; _e('Deactivate', 'blog-designer'); ?></a>
                    <input type="submit" name="sbtDeactivationFormClose" id="sbtDeactivationFormClosebd" class="button button-primary" value="<?php _e('Cancel', 'blog-designer'); ?>" />
                    <a href="javascript:void(0)" class="bd-deactivation" aria-label="<?php _e('Deactivate Blog Designer','blog-designer'); ?>"><?php _e('Skip', 'blog-designer'); echo ' &amp; '; _e('Deactivate', 'blog-designer'); ?></a>
                </form>
                <div class="support-ticket-section">
                    <h3><?php _e('Would you like to give us a chance to help you?', 'blog-designer'); ?></h3>
                    <img src="<?php echo BLOGDESIGNER_URL . '/images/support-ticket.png'; ?>">
                    <a href="<?php echo esc_url('http://support.solwininfotech.com/')?>"><?php _e('Create a support ticket', 'blog-designer'); ?></a>
                </div>
            </div>

        </div>
        <a style="display:none" href="#TB_inline?height=800&inlineId=sol_deactivation_widget_cover_bd" class="thickbox" id="deactivation_thickbox_bd"></a>
        <?php
    }

}
add_action('admin_head', 'bd_subscribe_mail', 10);


if (!function_exists('bd_remove_continue_reading')) {

    function bd_remove_continue_reading($more) {
        return '';
    }

}


if (!function_exists('bd_plugin_links')) {

    function bd_plugin_links($links) {
        $bd_is_optin = get_option('bd_is_optin');
        if($bd_is_optin == 'yes' || $bd_is_optin == 'no') {
            $start_page = 'designer_settings';
        }
        else {
            $start_page = 'designer_welcome_page';
        }
        $action_links = array(
            'settings' => '<a href="' . admin_url("admin.php?page=$start_page") . '" title="' . esc_attr(__('View Blog Designer Settings', 'blog-designer')) . '">' . __('Settings', 'blog-designer') . '</a>'
        );
        $links = array_merge($action_links, $links);
        $links['documents'] = '<a class="documentation_bd_plugin" target="_blank" href="' . esc_url('https://www.solwininfotech.com/documents/wordpress/blog-designer/') . '">' . __('Documentation', 'blog-designer') . '</a>';
        $links['upgrade'] = '<a target="_blank" href="' . esc_url('https://codecanyon.net/item/blog-designer-pro-for-wordpress/17069678?ref=solwin') . '" class="bd_upgrade_link">' . __('Upgrade', 'blog-designer') . '</a>';
        return $links;
    }

}


/**
 * Fusion Page Builder Support
 */
add_action('init', 'fsn_init_blog_designer', 12);

if (!function_exists('fsn_init_blog_designer')) {

    function fsn_init_blog_designer() {
        if (function_exists('fsn_map')) {
            fsn_map(array(
                'name' => __('Blog Designer', 'blog-designer'),
                'shortcode_tag' => 'fsn_blog_designer',
                'description' => __('To make your blog design more pretty, attractive and colorful.', 'blog-designer'),
                'icon' => 'fsn_blog',
            ));
        }
    }

}

add_shortcode('fsn_blog_designer', 'fsn_blog_designer_shortcode');

if (!function_exists('fsn_blog_designer_shortcode')) {

    function fsn_blog_designer_shortcode($atts, $content) {

        ob_start();
        ?>
        <div class="fsn-bdp <?php echo fsn_style_params_class($atts); ?>">
            <?php echo do_shortcode('[wp_blog_designer]'); ?>
        </div>
        <?php
        $output = ob_get_clean();
        return $output;
    }

}

if (!function_exists('bd_template_search_result')) {

    function bd_template_search_result() {
        $template_name = $_POST['temlate_name'];

        $tempate_list = bd_template_list();
        foreach ($tempate_list as $key => $value) {
            if ($template_name == '') {
                if ($key == 'classical' || $key == 'lightbreeze' || $key == 'spektrum' || $key == 'evolution' || $key == 'timeline' || $key == 'news') {
                    $class = 'bd-lite';
                } else {
                    $class = 'bp-pro';
                }
                ?>
                <div class="bd-template-thumbnail <?php echo $value['class'] . ' ' . $class; ?>">
                    <div class="bd-template-thumbnail-inner">
                        <img src="<?php echo BLOGDESIGNER_URL . '/images/layouts/' . $value['image_name']; ?>" data-value="<?php echo $key; ?>" alt="<?php echo $value['template_name']; ?>" title="<?php echo $value['template_name']; ?>">
                        <?php if ($class == 'bd-lite') { ?>
                            <div class="bd-hover_overlay">
                                <div class="bd-popup-template-name">
                                    <div class="bd-popum-select"><a href="#"><?php _e('Select Template', 'blog-designer'); ?></a></div>
                                    <div class="bd-popup-view"><a href="<?php echo $value['demo_link']; ?>" target="_blank"><?php _e('Live Demo', 'blog-designer'); ?></a></div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="bd_overlay"></div>
                            <div class="bd-img-hover_overlay">
                                <img src="<?php echo BLOGDESIGNER_URL . '/images/pro-tag.png' ?>" alt="Available in Pro" />
                            </div>
                            <div class="bd-hover_overlay">
                                <div class="bd-popup-template-name">
                                    <div class="bd-popup-view"><a href="<?php echo $value['demo_link']; ?>" target="_blank"><?php _e('Live Demo', 'blog-designer'); ?></a></div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <span class="bd-span-template-name"><?php echo $value['template_name']; ?></span>
                </div>
                <?php
            } elseif (preg_match('/' . trim($template_name) . '/', $key)) {
                if ($key == 'classical' || $key == 'lightbreeze' || $key == 'spektrum' || $key == 'evolution' || $key == 'timeline' || $key == 'news') {
                    $class = 'bd-lite';
                } else {
                    $class = 'bp-pro';
                }
                ?>
                <div class="bd-template-thumbnail <?php echo $value['class'] . ' ' . $class; ?>">
                    <div class="bd-template-thumbnail-inner">
                        <img src="<?php echo BLOGDESIGNER_URL . '/images/layouts/' . $value['image_name']; ?>" data-value="<?php echo $key; ?>" alt="<?php echo $value['template_name']; ?>" title="<?php echo $value['template_name']; ?>">
                        <?php if ($class == 'bd-lite') { ?>
                            <div class="bd-hover_overlay">
                                <div class="bd-popup-template-name">
                                    <div class="bd-popum-select"><a href="#"><?php _e('Select Template', 'blog-designer'); ?></a></div>
                                    <div class="bd-popup-view"><a href="<?php echo $value['demo_link']; ?>" target="_blank"><?php _e('Live Demo', 'blog-designer'); ?></a></div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="bd_overlay"></div>
                            <div class="bd-img-hover_overlay">
                                <img src="<?php echo BLOGDESIGNER_URL . '/images/pro-tag.png' ?>" alt="Available in Pro" />
                            </div>
                            <div class="bd-hover_overlay">
                                <div class="bd-popup-template-name">
                                    <div class="bd-popup-view"><a href="<?php echo $value['demo_link']; ?>" target="_blank"><?php _e('Live Demo', 'blog-designer'); ?></a></div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <span class="bd-span-template-name"><?php echo $value['template_name']; ?></span>
                </div>
                <?php
            }
        }
        exit();
    }

}

if(!function_exists('wp_bd_get_content')) {
    function wp_bd_get_content($postid) {
        global $post;
        $content = '';
        $excerpt_length = get_option('excerpt_length');
        $display_html_tags = get_option('display_html_tags', true);
        if (get_option('rss_use_excerpt') == 0) {
            $content = apply_filters('the_content', get_the_content($postid));
        } elseif (get_option('excerpt_length') > 0) {

            if($display_html_tags == 1) {
                $text = get_the_content($postid);
                if (strpos(_x('words', 'Word count type. Do not translate!', 'blog-designer'), 'characters') === 0 && preg_match('/^utf\-?8$/i', get_option('blog_charset'))) {
                    $text = trim(preg_replace("/[\n\r\t ]+/", ' ', $text), ' ');
                    preg_match_all('/./u', $text, $words_array);
                    $words_array = array_slice($words_array[0], 0, $excerpt_length + 1);
                    $sep = '';
                } else {
                    $words_array = preg_split("/[\n\r\t ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);
                    $sep = ' ';
                }
                if (count($words_array) > $excerpt_length) {
                    array_pop($words_array);
                    $text = implode($sep, $words_array);
                    $bp_excerpt_data = $text;
                } else {
                    $bp_excerpt_data = implode($sep, $words_array);
                }
                $first_letter = $bp_excerpt_data;
                if (preg_match('#(>|]|^)(([A-Z]|[a-z]|[0-9]|[\p{L}])(.*\R)*(\R)*.*)#m', $first_letter, $matches)) {
                    $top_content = str_replace($matches[2], '', $first_letter);
                    $content_change = ltrim($matches[2]);
                    $bp_content_first_letter = mb_substr($content_change, 0, 1);
                    if (mb_substr($content_change, 1, 1) === ' ') {
                        $bp_remaining_letter = ' ' . mb_substr($content_change, 2);
                    } else {
                        $bp_remaining_letter = mb_substr($content_change, 1);
                    }
                    $spanned_first_letter = '<span class="bp-first-letter">' . $bp_content_first_letter . '</span>';
                    $bottom_content = $spanned_first_letter . $bp_remaining_letter;
                    $bp_excerpt_data = $top_content . $bottom_content;
                    $bp_excerpt_data = wp_bp_close_tags($bp_excerpt_data);
                }
                $content = apply_filters('the_content', $bp_excerpt_data);
            } else {
                $text = $post->post_content;
				$text = str_replace( '<!--more-->', '', $text );
                $text = apply_filters('the_content', $text);
                $text = str_replace(']]>', ']]&gt;', $text);
                $bp_excerpt_data = wp_trim_words($text, $excerpt_length, '');
                $bp_excerpt_data = apply_filters('wp_bd_excerpt_change', $bp_excerpt_data, $postid);
                $content = $bp_excerpt_data;
            }

        }
        return $content;
    }
}

if(!function_exists('wp_bp_close_tags')) {
    function wp_bp_close_tags($html = '') {
        if($html == '') {
            return;
        }
        #put all opened tags into an array
        preg_match_all ( "#<([a-z]+)( .*)?(?!/)>#iU", $html, $result );
        $openedtags = $result[1];
        #put all closed tags into an array
        preg_match_all ( "#</([a-z]+)>#iU", $html, $result );
        $closedtags = $result[1];
        $len_opened = count ( $openedtags );
        # all tags are closed
        if( count ( $closedtags ) == $len_opened ) {
            return $html;
        }
        $openedtags = array_reverse ( $openedtags );
        # close tags
        for( $i = 0; $i < $len_opened; $i++ ) {
            if ( !in_array ( $openedtags[$i], $closedtags ) ) {
                $html .= "</" . $openedtags[$i] . ">";
            } else {
                unset ( $closedtags[array_search ( $openedtags[$i], $closedtags)] );
            }
        }
        return $html;
    }
}

if(!function_exists('bd_create_sample_layout')) {
    function bd_create_sample_layout() {
        $page_id = '';
        $blog_page_id = wp_insert_post(
                array(
                    'post_title' => __('Test Blog Page', 'blog-designer'),
                    'post_type' => 'page',
                    'post_status' => 'publish',
                    'post_content' => '[wp_blog_designer]'
                )
        );
        if ($blog_page_id) {
            $page_id = $blog_page_id;
        }
        update_option("blog_page_display", $page_id);
        $post_link = get_permalink($page_id);
        echo $post_link;
        exit;
    }
}

add_action('wp_ajax_bd_submit_optin','bd_submit_optin');
if(!function_exists('bd_submit_optin')) {
    function bd_submit_optin() {
        global $wpdb, $wp_version;
        $bd_submit_type = '';
        if(isset($_POST['email'])) {
            $bd_email = $_POST['email'];
        }
        else {
            $bd_email = get_option('admin_url');
        }
        if(isset($_POST['type'])) {
            $bd_submit_type = $_POST['type'];
        }
        if($bd_submit_type == 'submit') {
            $status_type = get_option('bd_is_optin');
            $theme_details = array();
            if ( $wp_version >= 3.4 ) {
                $active_theme                   = wp_get_theme();
                $theme_details['theme_name']    = strip_tags( $active_theme->name );
                $theme_details['theme_version'] = strip_tags( $active_theme->version );
                $theme_details['author_url']    = strip_tags( $active_theme->{'Author URI'} );
            }
            $active_plugins = (array) get_option( 'active_plugins', array() );
            if (is_multisite()) {
                $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
            }
            $plugins = array();
            if (count($active_plugins) > 0) {
                $get_plugins = array();
                foreach ($active_plugins as $plugin) {
                    $plugin_data = @get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);

                    $get_plugins['plugin_name'] = strip_tags($plugin_data['Name']);
                    $get_plugins['plugin_author'] = strip_tags($plugin_data['Author']);
                    $get_plugins['plugin_version'] = strip_tags($plugin_data['Version']);
                    array_push($plugins, $get_plugins);
                }
            }

            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/blog-designer/blog-designer.php', $markup = true, $translate = true);
            $current_version = $plugin_data['Version'];

            $plugin_data = array();
            $plugin_data['plugin_name'] = 'Blog Designer';
            $plugin_data['plugin_slug'] = 'blog-designer';
            $plugin_data['plugin_version'] = $current_version;
            $plugin_data['plugin_status'] = $status_type;
            $plugin_data['site_url'] = home_url();
            $plugin_data['site_language'] = defined( 'WPLANG' ) && WPLANG ? WPLANG : get_locale();
            $current_user = wp_get_current_user();
            $f_name = $current_user->user_firstname;
            $l_name = $current_user->user_lastname;
            $plugin_data['site_user_name'] = esc_attr( $f_name ).' '.esc_attr( $l_name );
            $plugin_data['site_email'] = false !== $bd_email ? $bd_email : get_option( 'admin_email' );
            $plugin_data['site_wordpress_version'] = $wp_version;
            $plugin_data['site_php_version'] = esc_attr( phpversion() );
            $plugin_data['site_mysql_version'] = $wpdb->db_version();
            $plugin_data['site_max_input_vars'] = ini_get( 'max_input_vars' );
            $plugin_data['site_php_memory_limit'] = ini_get( 'max_input_vars' );
            $plugin_data['site_operating_system'] = ini_get( 'memory_limit' ) ? ini_get( 'memory_limit' ) : 'N/A';
            $plugin_data['site_extensions']       = get_loaded_extensions();
            $plugin_data['site_activated_plugins'] = $plugins;
            $plugin_data['site_activated_theme'] = $theme_details;
            $url = 'http://analytics.solwininfotech.com/';
            $response = wp_safe_remote_post(
                $url, array(
                    'method'      => 'POST',
                    'timeout'     => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking'    => true,
                    'headers'     => array(),
                    'body'        => array(
                        'data'    => maybe_serialize( $plugin_data ),
                        'action'  => 'plugin_analysis_data',
                    ),
                )
            );
            update_option( 'bd_is_optin', 'yes' );
        }
        elseif($bd_submit_type == 'cancel') {
            update_option( 'bd_is_optin', 'no' );
        }
        elseif($bd_submit_type == 'deactivate') {
            $status_type = get_option('bd_is_optin');
            $theme_details = array();
            if ( $wp_version >= 3.4 ) {
                $active_theme                   = wp_get_theme();
                $theme_details['theme_name']    = strip_tags( $active_theme->name );
                $theme_details['theme_version'] = strip_tags( $active_theme->version );
                $theme_details['author_url']    = strip_tags( $active_theme->{'Author URI'} );
            }
            $active_plugins = (array) get_option( 'active_plugins', array() );
            if (is_multisite()) {
                $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
            }
            $plugins = array();
            if (count($active_plugins) > 0) {
                $get_plugins = array();
                foreach ($active_plugins as $plugin) {
                    $plugin_data = @get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
                    $get_plugins['plugin_name'] = strip_tags($plugin_data['Name']);
                    $get_plugins['plugin_author'] = strip_tags($plugin_data['Author']);
                    $get_plugins['plugin_version'] = strip_tags($plugin_data['Version']);
                    array_push($plugins, $get_plugins);
                }
            }

            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/blog-designer/blog-designer.php', $markup = true, $translate = true);
            $current_version = $plugin_data['Version'];

            $plugin_data = array();
            $plugin_data['plugin_name'] = 'Blog Designer';
            $plugin_data['plugin_slug'] = 'blog-designer';
            $reason_id = $_POST['selected_option_de'];
            $plugin_data['deactivation_option'] = $reason_id;
            $plugin_data['deactivation_option_text'] = $_POST['selected_option_de_text'];
            if ($reason_id == 9) {
                $plugin_data['deactivation_option_text'] = $_POST['selected_option_de_other'];
            }
            $plugin_data['plugin_version'] = $current_version;
            $plugin_data['plugin_status'] = $status_type;
            $plugin_data['site_url'] = home_url();
            $plugin_data['site_language'] = defined( 'WPLANG' ) && WPLANG ? WPLANG : get_locale();
            $current_user = wp_get_current_user();
            $f_name = $current_user->user_firstname;
            $l_name = $current_user->user_lastname;
            $plugin_data['site_user_name'] = esc_attr( $f_name ).' '.esc_attr( $l_name );
            $plugin_data['site_email'] = false !== $bd_email ? $bd_email : get_option( 'admin_email' );
            $plugin_data['site_wordpress_version'] = $wp_version;
            $plugin_data['site_php_version'] = esc_attr( phpversion() );
            $plugin_data['site_mysql_version'] = $wpdb->db_version();
            $plugin_data['site_max_input_vars'] = ini_get( 'max_input_vars' );
            $plugin_data['site_php_memory_limit'] = ini_get( 'max_input_vars' );
            $plugin_data['site_operating_system'] = ini_get( 'memory_limit' ) ? ini_get( 'memory_limit' ) : 'N/A';
            $plugin_data['site_extensions']       = get_loaded_extensions();
            $plugin_data['site_activated_plugins'] = $plugins;
            $plugin_data['site_activated_theme'] = $theme_details;
            $url = 'http://analytics.solwininfotech.com/';
            $response = wp_safe_remote_post(
                $url, array(
                    'method'      => 'POST',
                    'timeout'     => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking'    => true,
                    'headers'     => array(),
                    'body'        => array(
                        'data'    => maybe_serialize( $plugin_data ),
                        'action'  => 'plugin_analysis_data_deactivate',
                    ),
                )
            );
            update_option( 'bd_is_optin', '' );
        }
        exit();
    }
}

