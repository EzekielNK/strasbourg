<?php
/**
  Plugin Name: Blog Designer
  Plugin URI: https://wordpress.org/plugins/blog-designer/
  Description: To make your blog design more pretty, attractive and colorful.
  Version: 2.0.1
  Author: Solwin Infotech
  Author URI: https://www.solwininfotech.com/
  Requires at least: 4.0
  Tested up to: 5.2.2

  Text Domain: blog-designer
  Domain Path: /languages/
 */
/**
 * Exit if accessed directly
 */
if (!defined('ABSPATH')) {
    exit;
}

define('BLOGDESIGNER_URL', plugin_dir_url(__FILE__));
define('BLOGDESIGNER_DIR', plugin_dir_path(__FILE__));
register_activation_hook(__FILE__, 'bd_plugin_activate');
register_deactivation_hook(__FILE__, 'bd_update_optin');
add_action('admin_menu', 'bd_add_menu');
add_action('admin_init', 'bd_redirection', 1);
add_action('admin_init', 'bd_reg_function', 5);
add_action('admin_init', 'bd_session_start',1);
add_action('admin_head', 'bd_subscribe_mail', 10);
add_action('admin_enqueue_scripts', 'bd_admin_stylesheet', 7);
add_action('admin_init', 'bd_save_settings', 10);
add_action('wp_enqueue_scripts', 'bd_front_stylesheet');
add_action('admin_enqueue_scripts', 'bd_enqueue_color_picker', 9);
add_action('admin_init', 'bd_admin_scripts');
add_action('wp_head', 'bd_stylesheet', 20);
add_action('wp_head', 'bd_ajaxurl', 5);
add_action('wp_ajax_nopriv_bd_get_page_link', 'bd_get_page_link');
add_action('wp_ajax_bd_get_page_link', 'bd_get_page_link');
add_action('wp_ajax_bd_closed_bdboxes', 'bd_closed_bdboxes');
add_action('wp_ajax_bd_template_search_result', 'bd_template_search_result');
add_action('wp_ajax_bd_create_sample_layout', 'bd_create_sample_layout');
add_filter('excerpt_length', 'bd_excerpt_length', 999);
add_action('admin_head', 'bd_upgrade_link_css');
add_action('plugins_loaded', 'bd_load_language_files');
add_action('current_screen', 'bd_footer');
add_action('init', 'bd_fsn_block', 12);
add_action('admin_notices', 'bd_update_notice_hack');

add_shortcode('wp_blog_designer', 'bd_views');

add_action('vc_before_init', 'bd_add_vc_support');
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'bd_plugin_links');

//require_once BLOGDESIGNER_DIR . 'includes/promo_notice.php';

/**
 * Gutenberg block for blog designer shortcode
 */
if (function_exists('register_block_type')) {
    require_once BLOGDESIGNER_DIR . 'includes/blog_designer_block/index.php';
}

/**
 * Add support for visual composer
 */
function bd_add_vc_support() {
    vc_map(
            array(
                'name' => esc_html__('Blog Designer', 'blog-designer'),
                'base' => 'wp_blog_designer',
                'class' => 'blog_designer_section',
                'show_settings_on_create' => false,
                'category' => esc_html__('Content'),
                'icon' => 'blog_designer_icon',
                'description' => __('Custom Blog Layout', 'blog-designer'),
            )
    );
}

/**
 * Add css for upgrade link
 */
function bd_upgrade_link_css() {
    echo '<style>.row-actions a.bd_upgrade_link { color: #4caf50; }</style>';
}

/**
 * Enqueue colorpicket and chosen
 */
function bd_enqueue_color_picker($hook_suffix) {
    // first check that $hook_suffix is appropriate for your admin page
    if (isset($_GET['page']) && ( $_GET['page'] == 'designer_settings' || $_GET['page'] == 'bd_getting_started' || $_GET['page'] == 'designer_welcome_page' ) || $hook_suffix == 'plugins.php') {
        global $wp_version;
        wp_enqueue_style(array('wp-color-picker', 'wp-jquery-ui-dialog'));
        if (function_exists('wp_enqueue_code_editor')) {
            wp_enqueue_code_editor(array('type' => 'text/css'));
        }
        wp_enqueue_script('my-script-handle', plugins_url('js/admin_script.js', __FILE__), array('wp-color-picker', 'jquery-ui-core', 'jquery-ui-dialog'), false, true);
        wp_localize_script(
                'my-script-handle', 'bdlite_js', array(
                    'wp_version' => $wp_version,
                    'nothing_found' => __('Oops, nothing found!', 'blog-designer'),
                    'reset_data' => __('Do you want to reset data?', 'blog-designer'),
                    'choose_blog_template' => __('Choose the blog template you love', 'blog-designer'),
                    'close' => __('Close', 'blog-designer'),
                    'set_blog_template' => __('Set Blog Template', 'blog-designer'),
                    'default_style_template' => __('Apply default style of this selected template', 'blog-designer'),
                    'no_template_exist' => __('No template exist for selection', 'blog-designer'),
                )
        );
        wp_enqueue_script('my-chosen-handle', plugins_url('js/chosen.jquery.js', __FILE__));
    }
}

/**
 * add menu at admin panel
 */
function bd_add_menu() {
    $bd_is_optin = get_option('bd_is_optin');
    if ($bd_is_optin == 'yes' || $bd_is_optin == 'no') {
        add_menu_page(__('Blog Designer', 'blog-designer'), __('Blog Designer', 'blog-designer'), 'administrator', 'designer_settings', 'bd_main_menu_function', BLOGDESIGNER_URL . 'images/blog-designer.png');
    } else {
        add_menu_page(__('Blog Designer', 'blog-designer'), __('Blog Designer', 'blog-designer'), 'administrator', 'designer_welcome_page', 'bd_welcome_function', BLOGDESIGNER_URL . 'images/blog-designer.png');
    }
    add_submenu_page('designer_settings', __('Blog designer Settings', 'blog-designer'), __('Blog Designer Settings', 'blog-designer'), 'manage_options', 'designer_settings', 'bd_add_menu');
    add_submenu_page('designer_settings', __('Getting Started', 'blog-designer'), __('Getting Started', 'blog-designer'), 'manage_options', 'bd_getting_started', 'bd_getting_started');
}

/**
 * Include admin getting started  page
 */
function bd_getting_started() {
    include_once 'includes/getting_started.php';
}

/**
 * Loads plugin textdomain
 */
function bd_load_language_files() {
    load_plugin_textdomain('blog-designer', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

/**
 * Deactivate pro version when lite version is activated
 */
function bd_plugin_activate() {
    if (is_plugin_active('blog-designer-pro/blog-designer-pro.php')) {
        deactivate_plugins('/blog-designer-pro/blog-designer-pro.php');
    }
    add_option('bd_plugin_do_activation_redirect', true);
}

/**
 * Delete optin on deactivation of plugin
 */
function bd_update_optin() {
    update_option('bd_is_optin', '');
}

/**
 * Redirection on welcome page
 */
function bd_redirection() {
    if (is_user_logged_in()) {
        if (get_option('bd_plugin_do_activation_redirect', false)) {
            delete_option('bd_plugin_do_activation_redirect');
            if (!isset($_GET['activate-multi'])) {
                $bd_is_optin = get_option('bd_is_optin');
                if ($bd_is_optin == 'yes' || $bd_is_optin == 'no') {
                    exit(wp_redirect(admin_url('admin.php?page=bd_getting_started')));
                } else {
                    exit(wp_redirect(admin_url('admin.php?page=designer_welcome_page')));
                }
            }
        }
    }
}

/**
 * Custom Admin Footer
 */
function bd_footer() {
    if (isset($_GET['page']) && ( $_GET['page'] == 'designer_settings' || $_GET['page'] == 'bd_getting_started' )) {
        add_filter('admin_footer_text', 'bd_remove_footer_admin', 11);

        function bd_remove_footer_admin() {
            ob_start();
            ?>
            <p id="footer-left" class="alignleft">
                <?php _e('If you like ', 'blog-designer'); ?>
                <a href="<?php echo esc_url('https://www.solwininfotech.com/product/wordpress-plugins/blog-designer/'); ?>" target="_blank"><strong><?php _e('Blog Designer', 'blog-designer'); ?></strong></a>
                <?php _e('please leave us a', 'blog-designer'); ?>
                <a class="bdp-rating-link" data-rated="Thanks :)" target="_blank" href="<?php echo esc_url('https://wordpress.org/support/plugin/blog-designer/reviews?filter=5#new-post'); ?>">&#x2605;&#x2605;&#x2605;&#x2605;&#x2605;</a>
                <?php _e('rating. A huge thank you from Solwin Infotech in advance!', 'blog-designer'); ?>
            </p>
            <?php
            return ob_get_clean();
        }

    }
}

/**
 * Get template list
 */
function bd_template_list() {
    $tempate_list = array(
        'boxy' => array(
            'template_name' => __('Boxy Template', 'blog-designer'),
            'class' => 'masonry',
            'image_name' => 'boxy.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-boxy-blog-template/'),
        ),
        'boxy-clean' => array(
            'template_name' => __('Boxy Clean Template', 'blog-designer'),
            'class' => 'grid free',
            'image_name' => 'boxy-clean.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-boxy-clean-blog-template/'),
        ),
        'brit_co' => array(
            'template_name' => __('Brit Co Template', 'blog-designer'),
            'class' => 'grid',
            'image_name' => 'brit_co.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-brit-co-blog-template/'),
        ),
        'classical' => array(
            'template_name' => __('Classical Template', 'blog-designer'),
            'class' => 'full-width free',
            'image_name' => 'classical.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-classical-blog-template/'),
        ),
        'cool_horizontal' => array(
            'template_name' => __('Cool Horizontal Template', 'blog-designer'),
            'class' => 'timeline slider',
            'image_name' => 'cool_horizontal.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-cool-horizontal-timeline-blog-template/'),
        ),
        'crayon_slider' => array(
            'template_name' => __('Crayon Slider Template', 'blog-designer'),
            'class' => 'slider free',
            'image_name' => 'crayon_slider.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-crayon-slider-blog-template/')
        ),
        'cover' => array(
            'template_name' => __('Cover Template', 'blog-designer'),
            'class' => 'full-width',
            'image_name' => 'cover.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-cover-blog-template/'),
        ),
        'clicky' => array(
            'template_name' => __('Clicky Template', 'blog-designer'),
            'class' => 'full-width',
            'image_name' => 'clicky.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-clicky-blog-template/'),
        ),
        'deport' => array(
            'template_name' => __('Deport Template', 'blog-designer'),
            'class' => 'magazine',
            'image_name' => 'deport.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-deport-blog-template/'),
        ),
        'easy_timeline' => array(
            'template_name' => __('Easy Timeline', 'blog-designer'),
            'class' => 'timeline',
            'image_name' => 'easy_timeline.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-easy-timeline-blog-template/'),
        ),
        'elina' => array(
            'template_name' => __('Elina Template', 'blog-designer'),
            'class' => 'full-width',
            'image_name' => 'elina.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-elina-blog-template/'),
        ),
        'evolution' => array(
            'template_name' => __('Evolution Template', 'blog-designer'),
            'class' => 'full-width free',
            'image_name' => 'evolution.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-evolution-blog-template/'),
        ),
        'fairy' => array(
            'template_name' => __('Fairy Template', 'blog-designer'),
            'class' => 'full-width',
            'image_name' => 'fairy.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-fairy-blog-template/'),
        ),
        'famous' => array(
            'template_name' => __('Famous Template', 'blog-designer'),
            'class' => 'grid',
            'image_name' => 'famous.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-famous-blog-template/'),
        ),
        'foodbox' => array(
            'template_name' => __('Food Box Template', 'blog-designer'),
            'class' => 'full-width',
            'image_name' => 'foodbox.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-foodbox-blog-template/'),
        ),
        'glamour' => array(
            'template_name' => __('Glamour Template', 'blog-designer'),
            'class' => 'grid',
            'image_name' => 'glamour.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-glamour-blog-template/'),
        ),
        'glossary' => array(
            'template_name' => __('Glossary Template', 'blog-designer'),
            'class' => 'masonry',
            'image_name' => 'glossary.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-glossary-blog-template/'),
        ),
        'explore' => array(
            'template_name' => __('Explore Template', 'blog-designer'),
            'class' => 'grid',
            'image_name' => 'explore.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-explore-blog-template/'),
        ),
        'hoverbic' => array(
            'template_name' => __('Hoverbic Template', 'blog-designer'),
            'class' => 'grid',
            'image_name' => 'hoverbic.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-hoverbic-blog-template/'),
        ),
        'hub' => array(
            'template_name' => __('Hub Template', 'blog-designer'),
            'class' => 'full-width',
            'image_name' => 'hub.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-hub-blog-template/'),
        ),
        'minimal' => array(
            'template_name' => __('Minimal Template', 'blog-designer'),
            'class' => 'grid',
            'image_name' => 'minimal.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-minimal-grid-blog-template/'),
        ),
        'masonry_timeline' => array(
            'template_name' => __('Masonry Timeline', 'blog-designer'),
            'class' => 'magazine timeline',
            'image_name' => 'masonry_timeline.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-masonry-timeline-blog-template/'),
        ),
        'invert-grid' => array(
            'template_name' => __('Invert Grid Template', 'blog-designer'),
            'class' => 'grid',
            'image_name' => 'invert-grid.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-invert-grid-blog-template/'),
        ),
        'lightbreeze' => array(
            'template_name' => __('Lightbreeze Template', 'blog-designer'),
            'class' => 'full-width free',
            'image_name' => 'lightbreeze.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-light-breeze-blog-template/'),
        ),
        'media-grid' => array(
            'template_name' => __('Media Grid Template', 'blog-designer'),
            'class' => 'grid',
            'image_name' => 'media-grid.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-media-grid-blog-template/'),
        ),
        'my_diary' => array(
            'template_name' => __('My Diary Template', 'blog-designer'),
            'class' => 'full-width',
            'image_name' => 'my_diary.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-my-diary-blog-template/'),
        ),
        'navia' => array(
            'template_name' => __('Navia Template', 'blog-designer'),
            'class' => 'magazine',
            'image_name' => 'navia.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-navia-blog-template/'),
        ),
        'news' => array(
            'template_name' => __('News Template', 'blog-designer'),
            'class' => 'magazine free',
            'image_name' => 'news.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-news-blog-template/'),
        ),
        'neaty_block' => array(
            'template_name' => __('Neaty Block Template', 'blog-designer'),
            'class' => 'full-width',
            'image_name' => 'neaty_block.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-neaty-block-blog-template/'),
        ),
        'offer_blog' => array(
            'template_name' => __('Offer Blog Template', 'blog-designer'),
            'class' => 'full-width',
            'image_name' => 'offer_blog.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-offer-blog-template/'),
        ),
        'overlay_horizontal' => array(
            'template_name' => __('Overlay Horizontal Template', 'blog-designer'),
            'class' => 'timeline slider',
            'image_name' => 'overlay_horizontal.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-overlay-horizontal-timeline-blog-template/'),
        ),
        'nicy' => array(
            'template_name' => __('Nicy Template', 'blog-designer'),
            'class' => 'full-width',
            'image_name' => 'nicy.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-nicy-blog-template/'),
        ),
        'region' => array(
            'template_name' => __('Region Template', 'blog-designer'),
            'class' => 'full-width',
            'image_name' => 'region.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-region-blog-template/'),
        ),
        'roctangle' => array(
            'template_name' => __('Roctangle Template', 'blog-designer'),
            'class' => 'masonry',
            'image_name' => 'roctangle.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-roctangle-blog-template/'),
        ),
        'schedule' => array(
            'template_name' => __('Schedule Template', 'blog-designer'),
            'class' => 'full-width',
            'image_name' => 'schedule.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-schedule-blog-template/'),
        ),
        'sharpen' => array(
            'template_name' => __('Sharpen Template', 'blog-designer'),
            'class' => 'full-width',
            'image_name' => 'sharpen.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-sharpen-blog-template/'),
        ),
        'spektrum' => array(
            'template_name' => __('Spektrum Template', 'blog-designer'),
            'class' => 'full-width free',
            'image_name' => 'spektrum.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-spektrum-blog-template/'),
        ),
        'soft_block' => array(
            'template_name' => __('Soft Block Template', 'blog-designer'),
            'class' => 'full-width',
            'image_name' => 'soft_block.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-soft-block-blog-template/'),
        ),
        'story' => array(
            'template_name' => __('Story Template', 'blog-designer'),
            'class' => 'timeline',
            'image_name' => 'story.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-story-timeline-blog-template/'),
        ),
        'timeline' => array(
            'template_name' => __('Timeline Template', 'blog-designer'),
            'class' => 'timeline free',
            'image_name' => 'timeline.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-timeline-blog-template/'),
        ),
        'winter' => array(
            'template_name' => __('Winter Template', 'blog-designer'),
            'class' => 'full-width',
            'image_name' => 'winter.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-winter-blog-template/'),
        ),
        'wise_block' => array(
            'template_name' => __('Wise Block Template', 'blog-designer'),
            'class' => 'grid',
            'image_name' => 'wise_block.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-wise-block-blog-template/'),
        ),
        'crayon_slider' => array(
            'template_name' => __('Crayon Slider Template', 'blog-designer'),
            'class' => 'slider free',
            'image_name' => 'crayon_slider.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-crayon-slider-blog-template/'),
        ),
        'sallet_slider' => array(
            'template_name' => __('Sallet Slider Template', 'blog-designer'),
            'class' => 'slider',
            'image_name' => 'sallet_slider.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-sallet-slider-blog-template/'),
        ),
        'sunshiny_slider' => array(
            'template_name' => __('Sunshiny Slider Template', 'blog-designer'),
            'class' => 'slider',
            'image_name' => 'sunshiny_slider.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-sunshiny-slider-blog-template/'),
        ),
        'pretty' => array(
            'template_name' => __('Pretty Template', 'blog-designer'),
            'class' => 'full-width',
            'image_name' => 'pretty.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-pretty-blog-template/'),
        ),
        'tagly' => array(
            'template_name' => __('Tagly Template', 'blog-designer'),
            'class' => 'full-width',
            'image_name' => 'tagly.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-tagly-blog-template/'),
        ),
        'brite' => array(
            'template_name' => __('Brite Template', 'blog-designer'),
            'class' => 'full-width',
            'image_name' => 'brite.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-brite-blog-template/'),
        ),
        'chapter' => array(
            'template_name' => __('Chapter Template', 'blog-designer'),
            'class' => 'grid',
            'image_name' => 'chapter.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-chapter-blog-template/'),
        ),
        'steps' => array(
            'template_name' => __('Steps Template', 'blog-designer'),
            'class' => 'timeline',
            'image_name' => 'steps.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-steps-timeline-blog-template/'),
        ),
        'miracle' => array(
            'template_name' => __('Miracle Template', 'blog-designer'),
            'class' => 'full-width',
            'image_name' => 'miracle.jpg',
            'demo_link' => esc_url('http://blogdesigner.solwininfotech.com/demo/blog-miracle-blog-template/'),
        ),
    );
    ksort($tempate_list);
    return $tempate_list;
}

/**
 * Ajax handler for Store closed box id
 */
function bd_closed_bdboxes() {
    $closed = isset($_POST['closed']) ? explode(',', $_POST['closed']) : array();
    $page = isset($_POST['page']) ? $_POST['page'] : '';
    if ($page != sanitize_key($page)) {
        wp_die(0);
    }
    if (!$user = wp_get_current_user()) {
        wp_die(-1);
    }
    if (is_array($closed)) {
        update_user_option($user->ID, "bdpclosedbdpboxes_$page", $closed, true);
    }
    wp_die(1);
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
function bd_get_page_link() {
    if (isset($_POST['page_id'])) {
        $page_id = intval($_POST['page_id']);
        echo '<a target="_blank" href="' . get_permalink($page_id) . '">' . __('View Blog', 'blog-designer') . '</a>';
    }
    exit();
}

/**
 *
 * @param type $id
 * @param type $page
 * @return type closed class
 */
function bd_postbox_classes($id, $page) {
    if (!isset($_GET['action'])) {
        $closed = array('bdpgeneral');
        $closed = array_filter($closed);
        $page = 'designer_settings';
        $user = wp_get_current_user();
        if (is_array($closed)) {
            update_user_option($user->ID, "bdpclosedbdpboxes_$page", $closed, true);
        }
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

/**
 * Set default value
 */
function bd_reg_function() {
    if (is_user_logged_in()) {
        $settings = get_option('wp_blog_designer_settings');
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
            update_option('posts_per_page', '5');
            update_option('display_sticky', '1');
            update_option('display_category', '0');
            update_option('social_icon_style', '0');
            update_option('rss_use_excerpt', '1');
            update_option('template_alternativebackground', '1');
            update_option('display_tag', '0');
            update_option('display_author', '0');
            update_option('display_date', '0');
            update_option('social_share', '1');
            update_option('facebook_link', '0');
            update_option('twitter_link', '0');
            update_option('linkedin_link', '0');
            update_option('pinterest_link', '0');
            update_option('display_comment_count', '0');
            update_option('excerpt_length', '75');
            update_option('display_html_tags', '0');
            update_option('read_more_on', '2');
            update_option('read_more_text', 'Read More');
            update_option('template_titlefontsize', '35');
            update_option('content_fontsize', '14');
            update_option('wp_blog_designer_settings', $settings);
        }
    }
}

/**
 * Save plugin options
 */
function bd_save_settings() {
    if (is_user_logged_in() && isset($_POST['blog_nonce']) && wp_verify_nonce($_POST['blog_nonce'], 'blog_nonce_ac')) {
        if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'save' && isset($_REQUEST['updated']) && $_REQUEST['updated'] === 'true') {
            $blog_page_display = '';
            if (isset($_POST['blog_page_display'])) {
                $blog_page_display = intval($_POST['blog_page_display']);
                update_option('blog_page_display', $blog_page_display);
            }
            if (isset($_POST['posts_per_page'])) {
                $posts_per_page = intval($_POST['posts_per_page']);
                update_option('posts_per_page', $posts_per_page);
            }
            if (isset($_POST['rss_use_excerpt'])) {
                $rss_use_excerpt = intval($_POST['rss_use_excerpt']);
                update_option('rss_use_excerpt', $rss_use_excerpt);
            }
            if (isset($_POST['display_date'])) {
                $display_date = intval($_POST['display_date']);
                update_option('display_date', $display_date);
            }
            if (isset($_POST['display_author'])) {
                $display_author = intval($_POST['display_author']);
                update_option('display_author', $display_author);
            }
            if (isset($_POST['display_sticky'])) {
                $display_sticky = intval($_POST['display_sticky']);
                update_option('display_sticky', $display_sticky);
            }
            if (isset($_POST['display_category'])) {
                $display_category = intval($_POST['display_category']);
                update_option('display_category', $display_category);
            }
            if (isset($_POST['display_tag'])) {
                $display_tag = intval($_POST['display_tag']);
                update_option('display_tag', $display_tag);
            }
            if (isset($_POST['txtExcerptlength'])) {
                $txtExcerptlength = intval($_POST['txtExcerptlength']);
                update_option('excerpt_length', $txtExcerptlength);
            }
            if (isset($_POST['display_html_tags'])) {
                $display_html_tags = intval($_POST['display_html_tags']);
                update_option('display_html_tags', $display_html_tags);
            } else {
                update_option('display_html_tags', 0);
            }
            if (isset($_POST['readmore_on'])) {
                $readmore_on = intval($_POST['readmore_on']);
                update_option('read_more_on', $readmore_on);
            }
            if (isset($_POST['txtReadmoretext'])) {
                $txtReadmoretext = sanitize_text_field($_POST['txtReadmoretext']);
                update_option('read_more_text', $txtReadmoretext);
            }
            if (isset($_POST['template_alternativebackground'])) {
                $template_alternativebackground = sanitize_text_field($_POST['template_alternativebackground']);
                update_option('template_alternativebackground', $template_alternativebackground);
            }
            if (isset($_POST['social_icon_style'])) {
                $social_icon_style = intval($_POST['social_icon_style']);
                update_option('social_icon_style', $social_icon_style);
            }
            if (isset($_POST['social_share'])) {
                $social_share = intval($_POST['social_share']);
                update_option('social_share', $social_share);
            }
            if (isset($_POST['facebook_link'])) {
                $facebook_link = intval($_POST['facebook_link']);
                update_option('facebook_link', $facebook_link);
            }
            if (isset($_POST['twitter_link'])) {
                $twitter_link = intval($_POST['twitter_link']);
                update_option('twitter_link', $twitter_link);
            }
            if (isset($_POST['pinterest_link'])) {
                $pinterest_link = intval($_POST['pinterest_link']);
                update_option('pinterest_link', $pinterest_link);
            }
            if (isset($_POST['linkedin_link'])) {
                $linkedin_link = intval($_POST['linkedin_link']);
                update_option('linkedin_link', $linkedin_link);
            }
            if (isset($_POST['display_comment_count'])) {
                $display_comment_count = intval($_POST['display_comment_count']);
                update_option('display_comment_count', $display_comment_count);
            }
            if (isset($_POST['template_titlefontsize'])) {
                $template_titlefontsize = intval($_POST['template_titlefontsize']);
                update_option('template_titlefontsize', $template_titlefontsize);
            }
            if (isset($_POST['content_fontsize'])) {
                $content_fontsize = intval($_POST['content_fontsize']);
                update_option('content_fontsize', $content_fontsize);
            }
            if (isset($_POST['custom_css'])) {
                update_option('custom_css', wp_strip_all_tags($_POST['custom_css']));
            }

            $templates = array();
            $templates['ID'] = $blog_page_display;
            $templates['post_content'] = '[wp_blog_designer]';
            wp_update_post($templates);

            $settings = $_POST;
            if (isset($settings) && !empty($settings)) {
                foreach ($settings as $single_key => $single_val) {
                    if (is_array($single_val)) {
                        foreach ($single_val as $s_key => $s_val) {
                            $settings[$single_key][$s_key] = sanitize_text_field($s_val);
                        }
                    } else {
                        $settings[$single_key] = sanitize_text_field($single_val);
                    }
                }
            }
            $settings = is_array($settings) ? $settings : unserialize($settings);
            $updated = update_option('wp_blog_designer_settings', $settings);
        }
    }
}

/**
 * Display total downloads of plugin
 */
function bd_get_total_downloads() {
    // Set the arguments. For brevity of code, I will set only a few fields.
    $plugins = $response = '';
    $args = array(
        'author' => 'solwininfotech',
        'fields' => array(
            'downloaded' => true,
            'downloadlink' => true,
        ),
    );
    // Make request and extract plug-in object. Action is query_plugins
    $response = wp_remote_post(
            'http://api.wordpress.org/plugins/info/1.0/', array(
        'body' => array(
            'action' => 'query_plugins',
            'request' => serialize((object) $args),
        ),
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

/**
 * Display rating of plugin
 */
$wp_version = get_bloginfo('version');
if ($wp_version > 3.8) {

    function bd_custom_star_rating($args = array()) {
        $plugins = $response = '';
        $args = array(
            'author' => 'solwininfotech',
            'fields' => array(
                'downloaded' => true,
                'downloadlink' => true,
            ),
        );

        // Make request and extract plug-in object. Action is query_plugins
        $response = wp_remote_post(
                'http://api.wordpress.org/plugins/info/1.0/', array(
            'body' => array(
                'action' => 'query_plugins',
                'request' => serialize((object) $args),
            ),
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

/**
 * Enqueue admin panel required css
 */
function bd_admin_stylesheet() {
    $screen = get_current_screen();
    $plugin_data = get_plugin_data(BLOGDESIGNER_DIR . 'blog-designer.php', $markup = true, $translate = true);
    $current_version = $plugin_data['Version'];
    $old_version = get_option('bd_version');
    if ($old_version != $current_version) {
        update_option('is_user_subscribed_cancled', '');
        update_option('bd_version', $current_version);
    }
    if (( get_option('is_user_subscribed') != 'yes' && get_option('is_user_subscribed_cancled') != 'yes' ) || ( $screen->base == 'plugins' )) {
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');
    }
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-slider');

    wp_register_style('wp-blog-designer-admin-support-stylesheets', plugins_url('css/blog_designer_editor_support.css', __FILE__));
    wp_enqueue_style('wp-blog-designer-admin-support-stylesheets');

    if (( isset($_GET['page']) && ( $_GET['page'] == 'designer_settings' || $_GET['page'] == 'bd_getting_started' || $_GET['page'] == 'designer_welcome_page' ) ) || $screen->id == 'dashboard' || $screen->id == 'plugins') {

        $adminstylesheetURL = plugins_url('css/admin.css', __FILE__);
        $adminrtlstylesheetURL = plugins_url('css/admin-rtl.css', __FILE__);
        $adminstylesheet = BLOGDESIGNER_DIR . 'css/admin.css';
        if (file_exists($adminstylesheet)) {
            wp_register_style('wp-blog-designer-admin-stylesheets', $adminstylesheetURL);
            wp_enqueue_style('wp-blog-designer-admin-stylesheets');
        }

        if (is_rtl()) {
            wp_register_style('wp-blog-designer-admin-rtl-stylesheets', $adminrtlstylesheetURL);
            wp_enqueue_style('wp-blog-designer-admin-rtl-stylesheets');
        }

        $adminstylechosenURL = plugins_url('css/chosen.min.css', __FILE__);
        $adminstylechosen = BLOGDESIGNER_DIR . 'css/chosen.min.css';
        if (file_exists($adminstylechosen)) {
            wp_register_style('wp-blog-designer-chosen-stylesheets', $adminstylechosenURL);
            wp_enqueue_style('wp-blog-designer-chosen-stylesheets');
        }

        if (isset($_GET['page']) && $_GET['page'] == 'designer_settings') {
            $adminstylearistoURL = plugins_url('css/aristo.css', __FILE__);
            $adminstylearisto = BLOGDESIGNER_DIR . 'css/aristo.css';
            if (file_exists($adminstylearisto)) {
                wp_register_style('wp-blog-designer-aristo-stylesheets', $adminstylearistoURL);
                wp_enqueue_style('wp-blog-designer-aristo-stylesheets');
            }
        }

        $fontawesomeiconURL = plugins_url('css/fontawesome-all.min.css', __FILE__);
        $fontawesomeicon = BLOGDESIGNER_DIR . 'css/fontawesome-all.min.css';
        if (file_exists($fontawesomeicon)) {
            wp_register_style('wp-blog-designer-fontawesome-stylesheets', $fontawesomeiconURL);
            wp_enqueue_style('wp-blog-designer-fontawesome-stylesheets');
        }
    }
}

/**
 * Enqueue front side required css
 */
function bd_front_stylesheet() {
    $fontawesomeiconURL = plugins_url('css/fontawesome-all.min.css', __FILE__);
    $fontawesomeicon = BLOGDESIGNER_DIR . 'css/fontawesome-all.min.css';
    
    if (file_exists($fontawesomeicon)) {
        wp_register_style('wp-blog-designer-fontawesome-stylesheets', $fontawesomeiconURL);
        wp_enqueue_style('wp-blog-designer-fontawesome-stylesheets');
    }
    $designer_cssURL = plugins_url('css/designer_css.css', __FILE__);
    $designerrtl_cssURL = plugins_url('css/designerrtl_css.css', __FILE__);
    $designer_css = BLOGDESIGNER_DIR . 'css/designer_css.css';
    if (file_exists($designer_css)) {
        wp_register_style('wp-blog-designer-css-stylesheets', $designer_cssURL);
        wp_enqueue_style('wp-blog-designer-css-stylesheets');
    }
    if (is_rtl()) {
        wp_register_style('wp-blog-designer-rtl-css-stylesheets', $designerrtl_cssURL);
        wp_enqueue_style('wp-blog-designer-rtl-css-stylesheets');
    }
    wp_enqueue_script('jquery');
    wp_enqueue_script('wp-blog-designer-script', plugins_url('js/designer.js', __FILE__), '', false, true);
    $settings = get_option('wp_blog_designer_settings');
    if (isset($settings['template_name']) && $settings['template_name'] == 'crayon_slider') {
        $bd_gallery_sliderURL = plugins_url('css/flexslider.css', __FILE__);
        $bd_gallery_slider = dirname(__FILE__) . '/css/flexslider.css';
        if (file_exists($bd_gallery_slider)) {
            wp_enqueue_style('bd-galleryslider-stylesheets', $bd_gallery_sliderURL);
        }
        wp_enqueue_script('bd-galleryimage-script', plugins_url('js/jquery.flexslider-min.js', __FILE__), '', false, true);
    }
}

/**
 * enqueue admin side plugin js
 */
function bd_admin_scripts() {
    if (is_user_logged_in()) {
        wp_enqueue_script('jquery');
    }
}

/**
 * include plugin dynamic css
 */
function bd_stylesheet() {
    if (!is_admin()) {
        $stylesheet = BLOGDESIGNER_DIR . 'designer_css.php';

        if (file_exists($stylesheet)) {
            include 'designer_css.php';
        }
    }
    if (!is_admin() && is_rtl()) {
        $stylesheet = BLOGDESIGNER_DIR . 'designerrtl_css.php';

        if (file_exists($stylesheet)) {
            include 'designerrtl_css.php';
        }
    }
}

/**
 * Change content length
 */
function bd_excerpt_length($length) {
    if (get_option('excerpt_length') != '') {
        return get_option('excerpt_length');
    } else {
        return 50;
    }
}

/**
 * Return Blog posts
 */
function bd_views() {
    ob_start();
    add_filter('excerpt_more', 'bd_remove_continue_reading', 50);
    $settings = get_option('wp_blog_designer_settings');
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
    if (isset($settings['template_category'])) {
        $cat = $settings['template_category'];
    }

    if (!empty($cat)) {
        foreach ($cat as $catObj) :
            $category .= $catObj . ',';
        endforeach;
        $cat = rtrim($category, ',');
    } else {
        $cat = array();
    }

    if (isset($settings['template_tags'])) {
        $tag = $settings['template_tags'];
    }
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
                'operator' => 'IN',
            ),
            array(
                'taxonomy' => 'post_tag',
                'field' => 'term_id',
                'terms' => $tag,
                'operator' => 'IN',
            ),
        );
    } elseif (!empty($tag)) {
        $tax_query = array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'post_tag',
                'field' => 'term_id',
                'terms' => $tag,
                'operator' => 'IN',
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
    $paged = bd_paged();

    $args = array(
        'posts_per_page' => $posts_per_page,
        'paged' => $paged,
        'tax_query' => $tax_query,
        'author' => $author,
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
        if ($main_container_class != '') {
            echo '<div class="' . $main_container_class . '">';
        }
        if ($theme == 'timeline') {
            ?>
            <div class="timeline_bg_wrap">
                <div class="timeline_back clearfix">
                    <?php
                }
                if ($theme == "boxy-clean") {
                    ?>
                    <div class="blog_template boxy-clean">
                        <ul>
                            <?php
                        }
                        if ($theme == 'crayon_slider') {
                            $slider_navigation = '';
                            $template_slider_scroll = isset($settings['template_slider_scroll']) ? $settings['template_slider_scroll'] : 1;
                            $display_slider_navigation = isset($settings['display_slider_navigation']) ? $settings['display_slider_navigation'] : 1;
                            $display_slider_controls = isset($settings['display_slider_controls']) ? $settings['display_slider_controls'] : 1;
                            $slider_autoplay = isset($settings['slider_autoplay']) ? $settings['slider_autoplay'] : 1;
                            $slider_autoplay_intervals = isset($settings['slider_autoplay_intervals']) ? $settings['slider_autoplay_intervals'] : 7000;
                            $slider_speed = isset($settings['slider_speed']) ? $settings['slider_speed'] : 600;
                            $template_slider_effect = isset($settings['template_slider_effect']) ? $settings['template_slider_effect'] : 'slide';
                            if (is_rtl()) {
                                $template_slider_effect = 'fade';
                            }
                            $slider_column = 1;
                            if (isset($settings['template_slider_effect']) && $settings['template_slider_effect'] == 'slide') {
                                $slider_column = isset($settings['template_slider_columns']) ? $settings['template_slider_columns'] : 1;
                                $slider_column_ipad = isset($settings['template_slider_columns_ipad']) ? $settings['template_slider_columns_ipad'] : 1;
                                $slider_column_tablet = isset($settings['template_slider_columns_tablet']) ? $settings['template_slider_columns_tablet'] : 1;
                                $slider_column_mobile = isset($settings['template_slider_columns_mobile']) ? $settings['template_slider_columns_mobile'] : 1;
                            } else {
                                $slider_column = $slider_column_ipad = $slider_column_tablet = $slider_column_mobile = 1;
                            }
                            $slider_arrow = isset($settings['arrow_style_hidden']) ? $settings['arrow_style_hidden'] : 'arrow1';
                            if ($slider_arrow == '') {
                                $prev = "<i class='fas fa-chevron-left'></i>";
                                $next = "<i class='fas fa-chevron-right'></i>";
                            } else {
                                $prev = "<div class='" . $slider_arrow . "'></div>";
                                $next = "<div class='" . $slider_arrow . "'></div>";
                            }
                            ?>
                            <script type="text/javascript" id="flexslider_script">
                                jQuery(document).ready(function () {
                                var $maxItems = 1;
                                        if (jQuery(window).width() > 980) {
                                $maxItems = <?php echo $slider_column; ?>;
                                } else if (jQuery(window).width() <= 980 && jQuery(window).width() > 720) {
                                $maxItems = <?php echo $slider_column_ipad; ?>;
                                } else if (jQuery(window).width() <= 720 && jQuery(window).width() > 480) {
                                $maxItems = <?php echo $slider_column_tablet; ?>;
                                } else if (jQuery(window).width() <= 480) {
                                $maxItems = <?php echo $slider_column_mobile; ?>;
                                }
                                jQuery('.slider_template').flexslider({
                                move: <?php echo $template_slider_scroll; ?>,
                                        animation: '<?php echo $template_slider_effect; ?>',
                                        itemWidth: 10,
                                        itemMargin: 15,
                                        minItems: 1,
                                        maxItems: $maxItems,
                                        <?php echo ($display_slider_controls == 1) ? "directionNav: true," : "directionNav: false,"; ?>
                                        <?php echo ($display_slider_navigation == 1) ? "controlNav: true," : "controlNav: false,"; ?>
                                        <?php echo ($slider_autoplay == 1) ? "slideshow: true," : "slideshow: false,"; ?>
                                        <?php echo ($slider_autoplay == 1) ? "slideshowSpeed: $slider_autoplay_intervals," : ''; ?>
                                        <?php echo ($slider_speed) ? "animationSpeed: $slider_speed," : ''; ?>
                                                            prevText: "<?php echo $prev; ?>",
                                                                    nextText: "<?php echo $next; ?>",
                                                                    rtl: <?php
                                        if (is_rtl()) {
                                            echo 1;
                                        } else {
                                            echo 0;
                                        }
                                        ?>
                                });
                                }
                                );</script><?php
            ?>
                            <div class="blog_template slider_template crayon_slider navigation4 <?php echo $slider_navigation; ?>">
                                <ul class="slides">
                                    <?php
                                }
                                while (have_posts()) :
                                    the_post();
                                    if ($theme == 'classical') {
                                        $class = ' classical';
                                        bd_classical_template($alter_class);
                                    } elseif ($theme == 'boxy-clean') {
                                        $class = ' boxy-clean';
                                        bd_boxy_clean_template($settings);
                                    } elseif ($theme == 'crayon_slider') {
                                        $class = ' crayon_slider';

                                        bd_crayon_slider_template($settings);
                                    } elseif ($theme == 'lightbreeze') {
                                        if (get_option('template_alternativebackground') == 0) {
                                            if ($alter % 2 == 0) {
                                                $alter_class = ' alternative-back';
                                            } else {
                                                $alter_class = ' ';
                                            }
                                        }
                                        $class = ' lightbreeze';
                                        bd_lightbreeze_template($alter_class);
                                        $alter ++;
                                    } elseif ($theme == 'spektrum') {
                                        $class = ' spektrum';
                                        bd_spektrum_template();
                                    } elseif ($theme == 'evolution') {
                                        if (get_option('template_alternativebackground') == 0) {
                                            if ($alter % 2 == 0) {
                                                $alter_class = ' alternative-back';
                                            } else {
                                                $alter_class = ' ';
                                            }
                                        }
                                        $class = ' evolution';
                                        bd_evolution_template($alter_class);
                                        $alter ++;
                                    } elseif ($theme == 'timeline') {
                                        if ($alter % 2 == 0) {
                                            $alter_class = ' even';
                                        } else {
                                            $alter_class = ' ';
                                        }
                                        $class = 'timeline';
                                        bd_timeline_template($alter_class);
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
                                        bd_news_template($alter_class);
                                        $alter ++;
                                    }
                                endwhile;
                                if ($theme == 'timeline') {
                                    ?>
                            </div>
                    </div>
                    <?php
                }
                if ($theme == 'boxy-clean') {
                    ?>
                    </ul>
                </div>
                <?php
            }
            if ($theme == 'crayon_slider') {
                ?>
            </ul>
            </div>
            <?php
        }
    }
    if ($theme != 'crayon_slider') {
        echo '<div class="wl_pagination_box bd_pagination_box ' . $class . '">';
        echo bd_pagination();
        echo '</div>';
    }

    if ($main_container_class != '') {
        echo '</div>';
    }
    wp_reset_query();
    $wp_query = null;
    $wp_query = $temp_query;
    $content = ob_get_clean();
    return $content;
}

/**
 * html display classical design
 */
function bd_classical_template($alterclass) {
    ?>
    <div class="blog_template bdp_blog_template classical">
        <?php
        if (has_post_thumbnail()) {
            ?>
            <div class="bd-post-image"><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('full'); ?></a></div>
            <?php
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
                <div class="bd-metadatabox"><p>
                        <?php
                        if ($display_author == 0 && $display_date == 0) {
                            _e('Posted by ', 'blog-designer');
                            ?>
                            <a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>"><span><?php the_author(); ?></span></a>&nbsp;<?php _e('on', 'blog-designer'); ?>&nbsp;
                            <?php
                            $date_format = get_option('date_format');
                            echo get_the_time($date_format);
                        } elseif ($display_author == 0) {
                            _e('Posted by ', 'blog-designer');
                            ?>
                            <a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>"><span><?php the_author(); ?></span></a>&nbsp;
                            <?php
                        } elseif ($display_date == 0) {
                            _e('Posted on ', 'blog-designer');
                            $date_format = get_option('date_format');
                            echo get_the_time($date_format);
                        }
                        ?>
                    </p>
                    <?php
                    if ($display_comment_count == 0) {
                        ?>
                        <div class="bd-metacomments">
                            <i class="fas fa-comment"></i><?php comments_popup_link('0', '1', '%'); ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <?php
            }

            if (get_option('display_category') == 0) {
                ?>
                <div><span class="bd-category-link">
                        <?php
                        echo '<span class="bd-link-label">';
                        echo '<i class="fas fa-folder-open"></i>';
                        _e('Category', 'blog-designer');
                        echo ':&nbsp;';
                        echo '</span>';
                        $categories_list = get_the_category_list(', ');
                        if ($categories_list) :
                            print_r($categories_list);
                            $show_sep = true;
                        endif;
                        ?>
                    </span></div>
                <?php
            }

            if (get_option('display_tag') == 0) {
                $tags_list = get_the_tag_list('', ', ');
                if ($tags_list) :
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
                    </div>
                    <?php
                endif;
            }
            ?>
        </div>
        <div class="bd-post-content">
            <?php echo bd_get_content(get_the_ID()); ?>
            <?php
            if (get_option('rss_use_excerpt') == 1 && get_option('read_more_on') == 1) {
                $read_more_class = ( get_option('read_more_on') == 1 ) ? 'bd-more-tag-inline' : 'bd-more-tag';
                if (get_option('read_more_text') != '') {
                    echo '<a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . get_option('read_more_text') . ' </a>';
                } else {
                    echo ' <a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . __('Continue Reading...', 'blog-designer') . '</a>';
                }
            }
            ?>
        </div>
        <div class="bd-post-footer">
            <?php if (get_option('social_share') != 0 && ( ( get_option('facebook_link') == 0 ) || ( get_option('twitter_link') == 0 ) || ( get_option('linkedin_link') == 0 ) || ( get_option('pinterest_link') == 0 ) )) { ?>
                <div class="social-component">
                    <?php if (get_option('facebook_link') == 0) : ?>
                        <a data-share="facebook" data-href="https://www.facebook.com/sharer/sharer.php" data-url="<?php echo get_the_permalink(); ?>" class="bd-facebook-share bd-social-share"><i class="fab fa-facebook-f"></i></a>
                    <?php endif; ?>
                    <?php if (get_option('twitter_link') == 0) : ?>
                        <a data-share="twitter" data-href="https://twitter.com/share" data-text="<?php echo get_the_title(); ?>" data-url="<?php echo get_the_permalink(); ?>" class="bd-twitter-share bd-social-share"><i class="fab fa-twitter"></i></a>
                    <?php endif; ?>
                    <?php if (get_option('linkedin_link') == 0) : ?>
                        <a data-share="linkedin" data-href="https://www.linkedin.com/shareArticle" data-url="<?php echo get_the_permalink(); ?>" class="bd-linkedin-share bd-social-share"><i class="fab fa-linkedin-in"></i></a>
                    <?php endif; ?>
                    <?php
                    $pinterestimage = '';
                    if (get_option('pinterest_link') == 0) :
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
                    echo ' <a class="bd-more-tag" href="' . get_the_permalink() . '">' . __('Read More', 'blog-designer') . '</a>';
                }
            }
            ?>
        </div></div>
    <?php
}

/**
 * Column layout template class
 * @since 2.0
 * @global object $pagenow;
 */
if (!function_exists('bd_column_class')) {

    function bd_column_class($settings) {
        $column_class = '';

        $total_col = (isset($settings['template_columns']) && $settings['template_columns'] != '') ? $settings['template_columns'] : 2;
        if ($total_col == 1) {
            $col_class = 'one_column';
        }
        if ($total_col == 2) {
            $col_class = 'two_column';
        }
        if ($total_col == 3) {
            $col_class = 'three_column';
        }
        if ($total_col == 4) {
            $col_class = 'four_column';
        }

        $total_col_ipad = (isset($settings['template_columns_ipad']) && $settings['template_columns_ipad'] != '') ? $settings['template_columns_ipad'] : 1;
        if ($total_col_ipad == 1) {
            $col_class_ipad = 'one_column_ipad';
        }
        if ($total_col_ipad == 2) {
            $col_class_ipad = 'two_column_ipad';
        }
        if ($total_col_ipad == 3) {
            $col_class_ipad = 'three_column_ipad';
        }
        if ($total_col_ipad == 4) {
            $col_class_ipad = 'four_column_ipad';
        }

        $total_col_tablet = (isset($settings['template_columns_tablet']) && $settings['template_columns_tablet'] != '') ? $settings['template_columns_tablet'] : 1;
        if ($total_col_tablet == 1) {
            $col_class_tablet = 'one_column_tablet';
        }
        if ($total_col_tablet == 2) {
            $col_class_tablet = 'two_column_tablet';
        }
        if ($total_col_tablet == 3) {
            $col_class_tablet = 'three_column_tablet';
        }
        if ($total_col_tablet == 4) {
            $col_class_tablet = 'four_column_tablet';
        }

        $total_col_mobile = (isset($settings['template_columns_mobile']) && $settings['template_columns_mobile'] != '') ? $settings['template_columns_mobile'] : 1;
        if ($total_col_mobile == 1) {
            $col_class_mobile = 'one_column_mobile';
        }
        if ($total_col_mobile == 2) {
            $col_class_mobile = 'two_column_mobile';
        }
        if ($total_col_mobile == 3) {
            $col_class_mobile = 'three_column_mobile';
        }
        if ($total_col_mobile == 4) {
            $col_class_mobile = 'four_column_mobile';
        }

        $column_class = $col_class . ' ' . $col_class_ipad . ' ' . $col_class_tablet . ' ' . $col_class_mobile;
        return $column_class;
    }

}

/**
 * html display crayon_slider design
 */
function bd_crayon_slider_template() {
    $display_date = get_option('display_date');
    $display_author = get_option('display_author');
    $display_category = get_option('display_category');
    $display_comment_count = get_option('display_comment_count');
    ?>
    <li class="blog_template bdp_blog_template crayon_slider">
        <div class="bdp-post-image">
            <?php
            if (has_post_thumbnail()) {
                ?>
                <div class="bd-post-image"><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('full'); ?></a></div> 
                <?php
            }
            ?>
        </div>
        <div class="blog_header">
            <?php
            if ($display_category == 0) {
                ?>
                <div class="category-link">
                    <?php
                    $categories_list = get_the_category_list(', ');
                    if ($categories_list) :
                        echo ' ';
                        print_r($categories_list);
                        $show_sep = true;
                    endif;
                    ?>
                </div>
                <?php
            }
            ?>
            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            <?php
            if ($display_author == 0 || $display_date == 0 || $display_comment_count == 0) {
                ?>
                <div class="metadatabox">
                    <?php
                    if ($display_author == 0 || $display_date == 0) {
                        if ($display_author == 0) {
                            ?>
                            <div class="mauthor">
                                <span class="author">
                                    <i class="fas fa-user"></i>
                                    <a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>"><span><?php the_author(); ?></span></a>
                                </span>
                            </div>
                            <?php
                        }
                        if ($display_date == 0) {
                            $date_format = get_option('date_format');
                            ?>
                            <div class="post-date">
                                <span class="mdate"><i class="far fa-calendar-alt"></i> <?php echo get_the_time($date_format); ?></span>
                            </div>
                            <?php
                        }
                    }
                    if ($display_comment_count == 0) {
                        ?>
                        <div class="post-comment">
                            <?php
                            comments_popup_link('<i class="fas fa-comment"></i>' . __('Leave a Comment', 'blog-designer'), '<i class="fas fa-comment"></i>' . __('1 comment', 'blog-designer'), '<i class="fas fa-comment"></i>% ' . __('comments', 'blog-designer'), 'comments-link', '<i class="fas fa-comment"></i>' . __('Comments are off', 'blog-designer'));
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <?php
            }
            ?>
            <div class="post_content">
                <div class="post_content-inner">
                    <?php echo bd_get_content(get_the_ID()); ?>
                    <?php
                    if (get_option('rss_use_excerpt') == 1 && get_option('read_more_on') == 1) {
                        if (get_option('read_more_text') != '') {
                            echo '<a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . get_option('read_more_text') . ' </a>';
                        } else {
                            echo ' <a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . __('Continue Reading...', 'blog-designer') . '</a>';
                        }
                    }
                    ?>
                </div>
            </div>
            <?php
            if (get_option('display_tag') == 0) {
                $tags_list = get_the_tag_list('', ', ');
                if ($tags_list) :
                    ?>
                    <div class="tags"><i class="fas fa-bookmark"></i>&nbsp;
                        <?php
                        print_r($tags_list);
                        $show_sep = true;
                        ?>
                    </div>
                    <?php
                endif;
            }
            ?>
            <div class='bd_social_share_wrap'>
                <?php if (get_option('social_share') != 0 && ( ( get_option('facebook_link') == 0 ) || ( get_option('twitter_link') == 0 ) || ( get_option('linkedin_link') == 0 ) || ( get_option('pinterest_link') == 0 ) )) { ?>
                    <div class="social-component">
                        <?php if (get_option('facebook_link') == 0) : ?>
                            <a data-share="facebook" data-href="https://www.facebook.com/sharer/sharer.php" data-url="<?php echo get_the_permalink(); ?>" class="bd-facebook-share bd-social-share"><i class="fab fa-facebook-f"></i></a>
                        <?php endif; ?>
                        <?php if (get_option('twitter_link') == 0) : ?>
                            <a data-share="twitter" data-href="https://twitter.com/share" data-text="<?php echo get_the_title(); ?>" data-url="<?php echo get_the_permalink(); ?>" class="bd-twitter-share bd-social-share"><i class="fab fa-twitter"></i></a>
                        <?php endif; ?>
                        <?php if (get_option('linkedin_link') == 0) : ?>
                            <a data-share="linkedin" data-href="https://www.linkedin.com/shareArticle" data-url="<?php echo get_the_permalink(); ?>" class="bd-linkedin-share bd-social-share"><i class="fab fa-linkedin-in"></i></a>
                        <?php endif; ?>
                        <?php
                        if (get_option('pinterest_link') == 0) :
                            $pinterestimage = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
                            ?>
                            <a data-share="pinterest" data-href="https://pinterest.com/pin/create/button/" data-url="<?php echo get_the_permalink(); ?>" data-mdia="<?php echo $pinterestimage[0]; ?>" data-description="<?php echo get_the_title(); ?>" class="bd-pinterest-share bd-social-share"> <i class="fab fa-pinterest-p"></i></a>
                    <?php endif; ?>
                    </div>
    <?php } ?>
            </div>
        </div>
    </li>
    <?php
}

/**
 * html display boxy-clean design
 */
function bd_boxy_clean_template($settings) {
    $col_class = bd_column_class($settings);
    ?>
    <li class="blog_wrap bdp_blog_template <?php echo ($col_class != '') ? $col_class : ''; ?> bdp_blog_single_post_wrapp">
        <?php
        $display_date = get_option('display_date');
        $display_author = get_option('display_author');
        $display_category = get_option('display_category');
        $display_comment_count = get_option('display_comment_count');
        ?>
        <div class="post-meta">
            <?php
            if ($display_date == 0) {
                $date_format = get_option('date_format');
                ?>
                <div class="postdate">
                    <span class="month"><?php echo get_the_time('M d'); ?></span>
                    <span class="year"><?php echo get_the_time('Y'); ?></span>
                </div>
                <?php
            }
            if ($display_comment_count == 0) {
                if (comments_open()) {
                    ?>
                    <span class="post-comment">
                        <i class="fas fa-comment"></i>
                        <?php
                        comments_popup_link('0', '1', '%');
                        ?>
                    </span>  
                    <?php
                }
            }
            ?>
        </div>
        <div class="post-media">
            <?php
            if (has_post_thumbnail()) {
                ?>
                <div class="bd-post-image"><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('full'); ?></a></div> 
                <?php
            }
            if ($display_author == 0) {
                ?>
                <span class="author">
                    <a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>"><span><?php the_author(); ?></span></a>
                </span>
                <?php
            }
            ?>
        </div>
        <div class="post_summary_outer">
            <div class="blog_header">
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            </div>
            <div class="post_content">
                <?php echo bd_get_content(get_the_ID()); ?>
                <?php
                if (get_option('rss_use_excerpt') == 1 && get_option('read_more_on') == 1) {
                    if (get_option('read_more_text') != '') {
                        echo '<a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . get_option('read_more_text') . ' </a>';
                    } else {
                        echo ' <a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . __('Continue Reading...', 'blog-designer') . '</a>';
                    }
                }
                ?>
            </div>
        </div>
        <div class="blog_footer">
            <div class="footer_meta">
                <?php
                if ($display_category == 0) {
                    ?>
                    <div class="bd-metacats">
                        <i class="fas fa-bookmark"></i>&nbsp;
                        <?php
                        $categories_list = get_the_category_list(', ');
                        if ($categories_list) :
                            print_r($categories_list);
                            $show_sep = true;
                        endif;
                        ?>
                    </div>
                    <?php
                }
                ?>
                <?php
                if (get_option('display_tag') == 0) {
                    $tags_list = get_the_tag_list('', ', ');
                    if ($tags_list) :
                        ?>
                        <div class="bd-tags"><i class="fas fa-tags"></i>&nbsp;
                            <?php
                            print_r($tags_list);
                            $show_sep = true;
                            ?>
                        </div>
                        <?php
                    endif;
                }
                ?>
            </div>
            <div class='bd_social_share_wrap'>
                    <?php if (get_option('social_share') != 0 && ( ( get_option('facebook_link') == 0 ) || ( get_option('twitter_link') == 0 ) || ( get_option('linkedin_link') == 0 ) || ( get_option('pinterest_link') == 0 ) )) { ?>
                    <div class="social-component">
                        <?php if (get_option('facebook_link') == 0) : ?>
                            <a data-share="facebook" data-href="https://www.facebook.com/sharer/sharer.php" data-url="<?php echo get_the_permalink(); ?>" class="bd-facebook-share bd-social-share"><i class="fab fa-facebook-f"></i></a>
                        <?php endif; ?>
                        <?php if (get_option('twitter_link') == 0) : ?>
                            <a data-share="twitter" data-href="https://twitter.com/share" data-text="<?php echo get_the_title(); ?>" data-url="<?php echo get_the_permalink(); ?>" class="bd-twitter-share bd-social-share"><i class="fab fa-twitter"></i></a>
                        <?php endif; ?>
                        <?php if (get_option('linkedin_link') == 0) : ?>
                            <a data-share="linkedin" data-href="https://www.linkedin.com/shareArticle" data-url="<?php echo get_the_permalink(); ?>" class="bd-linkedin-share bd-social-share"><i class="fab fa-linkedin-in"></i></a>
                        <?php endif; ?>
                        <?php
                        if (get_option('pinterest_link') == 0) :
                            $pinterestimage = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
                            ?>
                            <a data-share="pinterest" data-href="https://pinterest.com/pin/create/button/" data-url="<?php echo get_the_permalink(); ?>" data-mdia="<?php echo $pinterestimage[0]; ?>" data-description="<?php echo get_the_title(); ?>" class="bd-pinterest-share bd-social-share"> <i class="fab fa-pinterest-p"></i></a>
                    <?php endif; ?>
                    </div>
    <?php } ?>
            </div>
        </div>
    </li>

    <?php
}

/**
 * html display lightbreeze design
 */
function bd_lightbreeze_template($alterclass) {
    ?>
    <div class="blog_template bdp_blog_template box-template active lightbreeze <?php echo $alterclass; ?>">
        <?php
        if (has_post_thumbnail()) {
            ?>
            <div class="bd-post-image"><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('full'); ?></a></div> 
            <?php
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
                <div class="bd-meta-data-box">
                    <?php
                    if ($display_author == 0) {
                        ?>
                        <div class="bd-metadate">
                            <i class="fas fa-user"></i><?php _e('Posted by ', 'blog-designer'); ?><a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>"><span><?php the_author(); ?></span></a><br />
                        </div>
                        <?php
                    }
                    if ($display_date == 0) {
                        $date_format = get_option('date_format');
                        ?>
                        <div class="bd-metauser">
                            <span class="mdate"><i class="far fa-calendar-alt"></i> <?php echo get_the_time($date_format); ?></span>
                        </div>
                        <?php
                    }
                    if ($display_category == 0) {
                        ?>
                        <div class="bd-metacats">
                            <i class="fas fa-bookmark"></i>&nbsp;
                            <?php
                            $categories_list = get_the_category_list(', ');
                            if ($categories_list) :
                                print_r($categories_list);
                                $show_sep = true;
                            endif;
                            ?>
                        </div>
                        <?php
                    }
                    if ($display_comment_count == 0) {
                        ?>
                        <div class="bd-metacomments"><i class="fas fa-comment"></i><?php comments_popup_link(__('No Comments', 'blog-designer'), __('1 Comment', 'blog-designer'), '% ' . __('Comments', 'blog-designer')); ?></div>
                <?php } ?>
                </div>
    <?php } ?>

        </div>
        <div class="bd-post-content">
            <?php echo bd_get_content(get_the_ID()); ?>
            <?php
            if (get_option('rss_use_excerpt') == 1 && get_option('read_more_on') == 1) {
                if (get_option('read_more_text') != '') {
                    echo '<a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . get_option('read_more_text') . ' </a>';
                } else {
                    echo ' <a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . __('Continue Reading...', 'blog-designer') . '</a>';
                }
            }
            ?>
        </div>

        <?php
        if (get_option('display_tag') == 0) {
            $tags_list = get_the_tag_list('', ', ');
            if ($tags_list) :
                ?>
                <div class="bd-tags"><i class="fas fa-tags"></i>&nbsp;
                    <?php
                    print_r($tags_list);
                    $show_sep = true;
                    ?>
                </div>
                <?php
            endif;
        }
        ?>

        <div class="bd-post-footer">
                <?php if (get_option('social_share') != 0 && ( ( get_option('facebook_link') == 0 ) || ( get_option('twitter_link') == 0 ) || ( get_option('linkedin_link') == 0 ) || ( get_option('pinterest_link') == 0 ) )) { ?>
                <div class="social-component">
                    <?php if (get_option('facebook_link') == 0) : ?>
                        <a data-share="facebook" data-href="https://www.facebook.com/sharer/sharer.php" data-url="<?php echo get_the_permalink(); ?>" class="bd-facebook-share bd-social-share"><i class="fab fa-facebook-f"></i></a>
                    <?php endif; ?>
                    <?php if (get_option('twitter_link') == 0) : ?>
                        <a data-share="twitter" data-href="https://twitter.com/share" data-text="<?php echo get_the_title(); ?>" data-url="<?php echo get_the_permalink(); ?>" class="bd-twitter-share bd-social-share"><i class="fab fa-twitter"></i></a>
                    <?php endif; ?>
                    <?php if (get_option('linkedin_link') == 0) : ?>
                        <a data-share="linkedin" data-href="https://www.linkedin.com/shareArticle" data-url="<?php echo get_the_permalink(); ?>" class="bd-linkedin-share bd-social-share"><i class="fab fa-linkedin-in"></i></a>
                    <?php endif; ?>
                    <?php
                    if (get_option('pinterest_link') == 0) :
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
                    echo '<a class="bd-more-tag" href="' . get_the_permalink() . '">' . __('Read More', 'blog-designer') . '</a>';
                }
            }
            ?>
        </div></div> 
    <?php
}

/**
 * Html display spektrum design
 */
function bd_spektrum_template() {
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
            <div class="bd-blog-header
            <?php
            if (get_option('display_date') != 0) {
                echo ' disable_date';
            }
            ?>
                 ">
    <?php if (get_option('display_date') == 0) { ?>
                    <p class="date"><span class="number-date"><?php the_time('d'); ?></span><?php the_time('F'); ?></p>
                <?php } ?>
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2></div>
            <div class="bd-post-content">
                <?php
                echo bd_get_content(get_the_ID());

                if (get_option('rss_use_excerpt') == 1 && get_option('excerpt_length') > 0) {
                    if (get_option('read_more_on') == 1) {
                        if (get_option('read_more_text') != '') {
                            echo '<a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . get_option('read_more_text') . ' </a>';
                        } else {
                            echo ' <a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . __('Read More', 'blog-designer') . '</a>';
                        }
                    }
                }

                if (get_option('rss_use_excerpt') == 1 && get_option('read_more_on') != 0) :
                    ?>
                    <span class="details">
                        <?php
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
                        <span class="bd-categories"><i class="fas fa-bookmark"></i>&nbsp;
                            <?php
                            $categories_list = get_the_category_list(', ');
                            if ($categories_list) :
                                echo '<span class="bd-link-label">';
                                _e('Categories', 'blog-designer');
                                echo '</span>';
                                echo ' : ';
                                print_r($categories_list);
                                $show_sep = true;
                            endif;
                            ?>
                        </span>
                        <?php
                    }
                    if ($display_author == 0) {
                        ?>
                        <span class="post-by"><i class="fas fa-user"></i>&nbsp;<?php _e('Posted by ', 'blog-designer'); ?><a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>"><span><?php the_author(); ?></span></a>
                        </span>
                        <?php
                    }
                    if ($display_tag == 0) {
                        $tags_list = get_the_tag_list('', ', ');
                        if ($tags_list) :
                            ?>
                            <span class="bd-tags"><i class="fas fa-tags"></i>&nbsp;
                                <?php
                                print_r($tags_list);
                                $show_sep = true;
                                ?>
                            </span>
                            <?php
                        endif;
                    }
                    if ($display_comment_count == 0) {
                        ?>
                        <span class="bd-metacomments"><i class="fas fa-comment"></i>&nbsp;<?php comments_popup_link(__('No Comments', 'blog-designer'), __('1 Comment', 'blog-designer'), '% ' . __('Comments', 'blog-designer')); ?>
                        </span>
                    <?php
                }
                ?>
                </div>
                <?php } ?>

                <?php if (get_option('social_share') != 0 && ( ( get_option('facebook_link') == 0 ) || ( get_option('twitter_link') == 0 ) || ( get_option('linkedin_link') == 0 ) || ( get_option('pinterest_link') == 0 ) )) { ?>
                <div class="social-component spektrum-social">
                    <?php if (get_option('facebook_link') == 0) : ?>
                        <a href="<?php echo 'https://www.facebook.com/sharer/sharer.php?u=' . get_the_permalink(); ?>" target= _blank class="bd-facebook-share"><i class="fab fa-facebook-f"></i></a>
                        <?php
                    endif;
                    if (get_option('twitter_link') == 0) :
                        ?>
                        <a href="<?php echo 'http://twitter.com/share?&url=' . get_the_permalink(); ?>" target= _blank class="bd-twitter-share"><i class="fab fa-twitter"></i></a>
                        <?php
                    endif;
                    if (get_option('linkedin_link') == 0) :
                        ?>
                        <a href="<?php echo 'http://www.linkedin.com/shareArticle?url=' . get_the_permalink(); ?>" target= _blank class="bd-linkedin-share"><i class="fab fa-linkedin-in"></i></a>
                        <?php
                    endif;
                    if (get_option('pinterest_link') == 0) :
                        $pinterestimage = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
                        ?>
                        <a href="<?php echo '//pinterest.com/pin/create/button/?url=' . get_the_permalink(); ?>" target= _blank class="bd-pinterest-share"> <i class="fab fa-pinterest-p"></i></a>
        <?php endif; ?>
                </div>
    <?php } ?>
        </div>
    </div>
    <?php
}

/**
 * Html display evolution design
 */
function bd_evolution_template($alterclass) {
    ?>
    <div class="blog_template bdp_blog_template evolution <?php echo $alterclass; ?>">
            <?php if (get_option('display_category') == 0) { ?>
            <div class="bd-categories">
                <?php
                $categories_list = get_the_category_list(', ');
                if ($categories_list) :
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
            <div class="post-entry-meta">
                <?php
                if ($display_date == 0) {
                    $date_format = get_option('date_format');
                    ?>
                    <span class="date"><i class="far fa-clock"></i><?php echo get_the_time($date_format); ?></span>
                    <?php
                }
                if ($display_author == 0) {
                    ?>
                    <span class="author"><i class="fas fa-user"></i><?php _e('Posted by ', 'blog-designer'); ?><a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>"><?php the_author(); ?></a></span>
                    <?php
                }
                if ($display_comment_count == 0) {
                    if (!post_password_required() && ( comments_open() || get_comments_number() )) :
                        ?>
                        <span class="comment"><i class="fas fa-comment"></i><?php comments_popup_link('0', '1', '%'); ?></span>
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
            echo bd_get_content(get_the_ID());

            if (get_option('rss_use_excerpt') == 1 && get_option('read_more_on') == 1) {
                if (get_option('read_more_text') != '') {
                    echo '<a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . get_option('read_more_text') . ' </a>';
                } else {
                    echo ' <a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . __('Continue Reading...', 'blog-designer') . '</a>';
                }
            }
            ?>
        </div>

        <?php
        $display_tag = get_option('display_tag');
        if ($display_tag == 0) {
            $tags_list = get_the_tag_list('', ', ');
            if ($tags_list) :
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
                </div>
                <?php
            endif;
        }
        ?>

        <div class="bd-post-footer">
                <?php if (get_option('social_share') != 0 && ( ( get_option('facebook_link') == 0 ) || ( get_option('twitter_link') == 0 ) || ( get_option('linkedin_link') == 0 ) || ( get_option('pinterest_link') == 0 ) )) { ?>
                <div class="social-component">
                    <?php
                    if (get_option('facebook_link') == 0) :
                        ?>
                        <a data-share="facebook" data-href="https://www.facebook.com/sharer/sharer.php" data-url="<?php echo get_the_permalink(); ?>" class="bd-facebook-share bd-social-share"><i class="fab fa-facebook-f"></i></a>
                    <?php endif; ?>
                    <?php if (get_option('twitter_link') == 0) : ?>
                        <a data-share="twitter" data-href="https://twitter.com/share" data-text="<?php echo get_the_title(); ?>" data-url="<?php echo get_the_permalink(); ?>" class="bd-twitter-share bd-social-share"><i class="fab fa-twitter"></i></a>
                    <?php endif; ?>
                    <?php if (get_option('linkedin_link') == 0) : ?>
                        <a data-share="linkedin" data-href="https://www.linkedin.com/shareArticle" data-url="<?php echo get_the_permalink(); ?>" class="bd-linkedin-share bd-social-share"><i class="fab fa-linkedin-in"></i></a>
                    <?php endif; ?>																			<?php
            if (get_option('pinterest_link') == 0) :
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
                    echo ' <a class="bd-more-tag" href="' . get_the_permalink() . '">' . __('Read More', 'blog-designer') . '</a>';
                }
            }
            ?>
        </div></div>
    <?php
}

/**
 * Html display timeline design
 */
function bd_timeline_template($alterclass) {
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
                                    <p class='bd-margin-0'><span title="Posted By <?php the_author(); ?>"><i class="fas fa-user"></i>&nbsp;<a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>"><span><?php the_author(); ?></span></a></span>&nbsp;&nbsp;
            <?php
        } if ($display_comment_count == 0) {
            ?>
                                        <span class="bd-metacomments"><i class="fas fa-comment"></i>&nbsp;<?php comments_popup_link(__('No Comments', 'blog-designer'), __('1 Comment', 'blog-designer'), '% ' . __('Comments', 'blog-designer')); ?>
                                        </span></p>
            <?php
        } if ($display_date == 0) {
            ?>
                                    <div class="bd-datetime">
                                        <span class="month"><?php the_time('M'); ?></span><span class="date"><?php the_time('d'); ?></span>
                                    </div><?php } ?></div>
                            <?php } ?>
                        <div class="bd-post-content">
                            <?php
                            echo bd_get_content(get_the_ID());

                            if (get_option('rss_use_excerpt') == 1 && get_option('excerpt_length') > 0) {
                                if (get_option('read_more_on') == 1) {
                                    if (get_option('read_more_text') != '') {
                                        echo '<a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . get_option('read_more_text') . ' </a>';
                                    } else {
                                        echo ' <a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . __('Read More', 'blog-designer') . '</a>';
                                    }
                                }
                            }
                            ?>
                        </div>
                            <?php if (get_option('rss_use_excerpt') == 1 && get_option('read_more_on') != 0) : ?>
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
                    <?php if (get_option('display_category') == 0 || ( get_option('social_share') != 0 && ( get_option('display_tag') == 0 || ( get_option('facebook_link') == 0 ) || ( get_option('twitter_link') == 0 ) || ( get_option('linkedin_link') == 0 ) || ( get_option('pinterest_link') == 0 ) ) )) { ?>
                    <footer class="blog_footer text-capitalize">
                                <?php
                                if (get_option('display_category') == 0) {
                                    ?>
                            <p class="bd-margin-0"><span class="bd-categories"><i class="fas fa-folder"></i>
                                    <?php
                                    $categories_list = get_the_category_list(', ');
                                    if ($categories_list) :
                                        echo '<span class="bd-link-label">';
                                        _e('Categories', 'blog-designer');
                                        echo ' :&nbsp;';
                                        echo '</span>';
                                        print_r($categories_list);
                                        $show_sep = true;
                                    endif;
                                    ?>
                                </span></p>
                            <?php
                        }
                        if (get_option('display_tag') == 0) {
                            $tags_list = get_the_tag_list('', ', ');
                            if ($tags_list) :
                                ?>
                                <p class="bd-margin-0"><span class="bd-tags"><i class="fas fa-bookmark"></i>
                                        <?php
                                        echo '<span class="bd-link-label">';
                                        _e('Tags', 'blog-designer');
                                        echo ' :&nbsp;';
                                        echo '</span>';
                                        print_r($tags_list);
                                        $show_sep = true;
                                        ?>
                                    </span></p>
                                <?php
                            endif;
                        }

                        if (get_option('social_share') != 0 && ( ( get_option('facebook_link') == 0 ) || ( get_option('twitter_link') == 0 ) || ( get_option('linkedin_link') == 0 ) || ( get_option('pinterest_link') == 0 ) )) {
                            ?>
                            <div class="social-component">
                                <?php if (get_option('facebook_link') == 0) : ?>
                                    <a data-share="facebook" data-href="https://www.facebook.com/sharer/sharer.php" data-url="<?php echo get_the_permalink(); ?>" class="bd-facebook-share bd-social-share"><i class="fab fa-facebook-f"></i></a>
                                    <?php
                                endif;
                                if (get_option('twitter_link') == 0) :
                                    ?>
                                    <a data-share="twitter" data-href="https://twitter.com/share" data-text="<?php echo get_the_title(); ?>" data-url="<?php echo get_the_permalink(); ?>" class="bd-twitter-share bd-social-share"><i class="fab fa-twitter"></i></a>
                                    <?php
                                endif;
                                if (get_option('linkedin_link') == 0) :
                                    ?>
                                    <a data-share="linkedin" data-href="https://www.linkedin.com/shareArticle" data-url="<?php echo get_the_permalink(); ?>" class="bd-linkedin-share bd-social-share"><i class="fab fa-linkedin-in"></i></a>
                                    <?php
                                endif;
                                if (get_option('pinterest_link') == 0) :
                                    $pinterestimage = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
                                    ?>
                                    <a data-share="pinterest" data-href="https://pinterest.com/pin/create/button/" data-url="<?php echo get_the_permalink(); ?>" data-mdia="<?php echo $pinterestimage[0]; ?>" data-description="<?php echo get_the_title(); ?>" class="bd-pinterest-share bd-social-share"> <i class="fab fa-pinterest-p"></i></a>
                            <?php endif; ?>
                            </div>
                        <?php
                    }
                    ?>
                    </footer>
    <?php } ?>
            </div></div></div>
    <?php
}

/**
 * Html display news design
 */
function bd_news_template($alter) {
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
                    ?>
                    <p class="bd_date_cover"><span class="date"><?php echo get_the_time($date_format); ?></span></p><?php } ?><h2 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <?php
                $display_author = get_option('display_author');
                $display_comment_count = get_option('display_comment_count');
                if ($display_author == 0 || $display_comment_count == 0) {
                    ?>
                    <div class="bd-metadatabox">
        <?php
        if ($display_author == 0) {
            ?>
                            <a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>"><?php the_author(); ?>
                            </a>
                            <?php
                        }
                        if ($display_comment_count == 0) {
                            comments_popup_link(__('Leave a Comment', 'blog-designer'), __('1 Comment', 'blog-designer'), '% ' . __('Comments', 'blog-designer'), 'comments-link', __('Comments are off', 'blog-designer'));
                        }
                        ?>
                    </div>
                <?php } ?>
            </div>
            <div class="bd-post-content">
                <?php
                echo bd_get_content(get_the_ID());
                if (get_option('rss_use_excerpt') == 1 && get_option('read_more_on') == 1) {
                    if (get_option('read_more_text') != '') {
                        echo '<a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . get_option('read_more_text') . ' </a>';
                    } else {
                        echo '<a class="bd-more-tag-inline" href="' . get_the_permalink() . '">' . __('Read More', 'blog-designer') . '</a>';
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
                        <span class="bd-category-link">
                            <?php
                            $categories_list = get_the_category_list(', ');
                            if ($categories_list) :
                                echo '<i class="fas fa-bookmark"></i>';
                                print_r($categories_list);
                                $show_sep = true;
                            endif;
                            ?>
                        </span>
                        <?php
                    }
                    if ($display_tag == 0) {
                        $tags_list = get_the_tag_list('', ', ');
                        if ($tags_list) :
                            ?>
                            <span class="bd-tags"><i class="fas fa-tags"></i>&nbsp;
                                <?php
                                print_r($tags_list);
                                $show_sep = true;
                                ?>
                            </span>
                            <?php
                        endif;
                    }
                    ?>
                </div>
                <?php } ?>

            <div class="bd-post-footer">
                    <?php if (get_option('social_share') != 0 && ( ( get_option('facebook_link') == 0 ) || ( get_option('twitter_link') == 0 ) || ( get_option('linkedin_link') == 0 ) || ( get_option('pinterest_link') == 0 ) )) { ?>
                    <div class="social-component">
                        <?php if (get_option('facebook_link') == 0) : ?>
                            <a data-share="facebook" data-href="https://www.facebook.com/sharer/sharer.php" data-url="<?php echo get_the_permalink(); ?>" class="bd-facebook-share bd-social-share"><i class="fab fa-facebook-f"></i></a>
                        <?php endif; ?>
                        <?php if (get_option('twitter_link') == 0) : ?>
                            <a data-share="twitter" data-href="https://twitter.com/share" data-text="<?php echo get_the_title(); ?>" data-url="<?php echo get_the_permalink(); ?>" class="bd-twitter-share bd-social-share"><i class="fab fa-twitter"></i></a><?php endif; ?>
                        <?php if (get_option('linkedin_link') == 0) : ?>
                            <a data-share="linkedin" data-href="https://www.linkedin.com/shareArticle" data-url="<?php echo get_the_permalink(); ?>" class="bd-linkedin-share bd-social-share"><i class="fab fa-linkedin-in"></i></a>
                    <?php endif; ?>																		<?php if (get_option('pinterest_link') == 0) : $pinterestimage = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full'); ?>
                            <a data-share="pinterest" data-href="https://pinterest.com/pin/create/button/" data-url="<?php echo get_the_permalink(); ?>" data-mdia="<?php echo $pinterestimage[0]; ?>" data-description="<?php echo get_the_title(); ?>" class="bd-pinterest-share bd-social-share"> <i class="fab fa-pinterest-p"></i></a>
                    <?php endif; ?>
                    </div>
                <?php } ?>

                <?php
                if (get_option('rss_use_excerpt') == 1 && get_option('read_more_on') == 2) {
                    if (get_option('read_more_text') != '') {
                        echo '<a class="bd-more-tag" href="' . get_the_permalink() . '">' . get_option('read_more_text') . ' </a>';
                    } else {
                        echo ' <a class="bd-more-tag" href="' . get_the_permalink() . '">' . __('Read More', 'blog-designer') . '</a>';
                    }
                }
                ?>
            </div></div></div>
    <?php
}

/**
 * Html Display setting options
 */
function bd_main_menu_function() {
    global $wp_version;
    ?>
    <div class="wrap">
        <h2><?php _e('Blog Designer Settings', 'blog-designer'); ?></h2>
        <div class="updated notice notice-success" id="message">
            <p><a href="<?php echo esc_url('https://www.solwininfotech.com/documents/wordpress/blog-designer/'); ?>" target="_blank"><?php _e('Read Online Documentation', 'blog-designer'); ?></a></p>
            <p><a href="<?php echo esc_url('http://blogdesigner.solwininfotech.com'); ?>" target="blank"><?php _e('See Live Demo', 'blog-designer'); ?></a></p>
            <p><?php echo __('Get access of ', 'blog-designer') . ' <b>' . __('50 new layouts', '') . '</b> ' . __('and', 'blog-designer') . ' <b>' . __('150+ new premium', 'blog-designer') . '</b> ' . __(' features.', 'blog-designer'); ?> <b><a href="<?php echo esc_url('https://codecanyon.net/item/blog-designer-pro-for-wordpress/17069678?ref=solwin'); ?>" target="blank"><?php _e('Upgrade to PRO now', 'blog-designer'); ?></a></b></p>
        </div>
        <?php
        $view_post_link = ( get_option('blog_page_display') != 0 ) ? '<span class="page_link"> <a target="_blank" href="' . get_permalink(get_option('blog_page_display')) . '"> ' . __('View Blog', 'blog-designer') . ' </a></span>' : '';
        if (isset($_REQUEST['bdRestoreDefault']) && isset($_GET['updated']) && 'true' == esc_attr($_GET['updated'])) {
            echo '<div class="updated" ><p>' . __('Blog Designer setting restored successfully.', 'blog-designer') . ' ' . $view_post_link . '</p></div>';
        } elseif (isset($_GET['updated']) && 'true' == esc_attr($_GET['updated'])) {
            echo '<div class="updated" ><p>' . __('Blog Designer settings updated.', 'blog-designer') . ' ' . $view_post_link . '</p></div>';
        }
        $settings = get_option('wp_blog_designer_settings');
        if (isset($_SESSION['success_msg'])) {
            ?>
            <div class="updated is-dismissible notice settings-error">
                <?php
                echo '<p>' . $_SESSION['success_msg'] . '</p>';
                unset($_SESSION['success_msg']);
                ?>
            </div>
                <?php
            }
            ?>
        <form method="post" action="?page=designer_settings&action=save&updated=true" class="bd-form-class">
            <?php
            $page = '';
            if (isset($_GET['page']) && $_GET['page'] != '') {
                $page = esc_attr($_GET['page']);
                ?>
                <input type="hidden" name="originalpage" class="bdporiginalpage" value="<?php echo $page; ?>">
    <?php } ?>
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
                        $bdpgeneral_class = $dbptimeline_class = $bdpstandard_class = $bdptitle_class = $bdpcontent_class = $bdpmedia_class = $bdpslider_class = $bdpcustomreadmore_class = $bdpsocial_class = $bdpslider_class = '';
                        $bdpgeneral_class_show = $dbptimeline_class_show = $bdpstandard_class_show = $bdptitle_class_show = $bdpcontent_class_show = $bdpmedia_class_show = $bdpslider_class_show = $bdpcustomreadmore_class_show = $bdpsocial_class_show = '';

                        if (bd_postbox_classes('bdpgeneral', $page)) {
                            $bdpgeneral_class = 'class="bd-active-tab"';
                            $bdpgeneral_class_show = 'style="display: block;"';
                        } elseif (bd_postbox_classes('dbptimeline', $page)) {
                            $dbptimeline_class = 'class="bd-active-tab"';
                            $dbptimeline_class_show = 'style="display: block;"';
                        } elseif (bd_postbox_classes('bdpstandard', $page)) {
                            $bdpstandard_class = 'class="bd-active-tab"';
                            $bdpstandard_class_show = 'style="display: block;"';
                        } elseif (bd_postbox_classes('bdptitle', $page)) {
                            $bdptitle_class = 'class="bd-active-tab"';
                            $bdptitle_class_show = 'style="display: block;"';
                        } elseif (bd_postbox_classes('bdpcontent', $page)) {
                            $bdpcontent_class = 'class="bd-active-tab"';
                            $bdpcontent_class_show = 'style="display: block;"';
                        } elseif (bd_postbox_classes('bdpmedia', $page)) {
                            $bdpmedia_class = 'class="bd-active-tab"';
                            $bdpmedia_class_show = 'style="display: block;"';
                        } elseif (bd_postbox_classes('bdpslider', $page)) {
                            $bdpslider_class = 'class="bd-active-tab"';
                            $bdpslider_class_show = 'style="display: block;"';
                        } elseif (bd_postbox_classes('bdpcustomreadmore', $page)) {
                            $bdpcustomreadmore_class = 'class="bd-active-tab"';
                            $bdpcustomreadmore_class_show = 'style="display: block;"';
                        } elseif (bd_postbox_classes('bdpsocial', $page)) {
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
                            <li data-show="bdpslider" <?php echo $bdpslider_class; ?>>
                                <i class="fas fa-sliders-h"></i>
                                <span><?php _e('Slider Settings', 'blog-designer'); ?></span>
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
                                    <p class="bd-margin-bottom-50"><?php _e('Select your favorite layout from 8 free layouts.', 'blog-designer'); ?> <b><?php _e('Upgrade for just $39 to access 50 brand new layouts and other premium features.', 'blog-designer'); ?></b></p>
                                    <p class="bd-margin-bottom-30"><b><?php _e('Current Template:', 'blog-designer'); ?></b> &nbsp;&nbsp;
                                        <span class="bd-template-name">
                                            <?php
                                            if (isset($settings['template_name'])) {
                                                echo str_replace('_', '-', $settings['template_name']) . ' ';
                                                _e('Template', 'blog-designer');
                                            }
                                            ?>
                                        </span></p>
                                    <div class="bd_select_template_button_div">
                                        <input type="button" class="bd_select_template" value="<?php esc_attr_e('Select Other Template', 'blog-designer'); ?>">
                                    </div>
                                    <input type="hidden" name="template_name" id="template_name" value="<?php
                                if (isset($settings['template_name']) && $settings['template_name'] != '') {
                                    echo $settings['template_name'];
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
                                            <div 
                                            <?php
                                            if (isset($settings['template_name']) && empty($settings['template_name'])) {
                                                echo ' class="bd_no_template_found"';
                                            }
                                            ?>
                                                >
                                                <?php
                                                if (isset($settings['template_name']) && !empty($settings['template_name'])) {
                                                    $image_name = $settings['template_name'] . '.jpg';
                                                    ?>
                                                    <img src="<?php echo BLOGDESIGNER_URL . 'images/layouts/' . $image_name; ?>" alt="
                                                    <?php
                                                    if (isset($settings['template_name'])) {
                                                        echo str_replace('_', '-', $settings['template_name']) . ' ';
                                                        esc_attr_e('Template', 'blog-designer');
                                                    }
                                                    ?>
                                                         " title="
                                                         <?php
                                                         if (isset($settings['template_name'])) {
                                                             echo str_replace('_', '-', $settings['template_name']) . ' ';
                                                             esc_attr_e('Template', 'blog-designer');
                                                         }
                                                         ?>
                                                         " />
                                                    <label id="bd_template_select_name">
                                                        <?php
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
    _e('You are about to select the page for your layout. This will overwrite all the content on the page that you will select. Changes once lost can not be recovered. Please be cautious!', 'blog-designer');
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
                                        echo wp_dropdown_pages(
                                                array(
                                                    'name' => 'blog_page_display',
                                                    'echo' => 0,
                                                    'depth' => -1,
                                                    'show_option_none' => '-- ' . __('Select Page', 'blog-designer') . ' --',
                                                    'option_none_value' => '0',
                                                    'selected' => get_option('blog_page_display'),
                                                )
                                        );
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
                                    <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e(' Select post categories to filter posts via categories', 'blog-designer'); ?></span></span>
                                    <?php
                                    $categories = get_categories(
                                            array(
                                                'child_of' => '',
                                                'hide_empty' => 1,
                                            )
                                    );
                                    ?>
                                    <select data-placeholder="<?php esc_attr_e('Choose Post Categories', 'blog-designer'); ?>" class="chosen-select" multiple style="width:220px;" name="template_category[]" id="template_category">
                                        <?php foreach ($categories as $categoryObj) : ?>
                                            <option value="<?php echo $categoryObj->term_id; ?>" 
                                            <?php
                                            if (@in_array($categoryObj->term_id, $settings['template_category'])) {
                                                echo 'selected="selected"';
                                            }
                                            ?>
                                                    ><?php echo $categoryObj->name; ?>
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
                                            <option value="<?php echo $tag->term_id; ?>"
                                            <?php
                                            if (@in_array($tag->term_id, $template_tags)) {
                                                echo 'selected="selected"';
                                            }
                                            ?>
                                                    ><?php echo $tag->name; ?></option>
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
                                            <option value="<?php echo $user->ID; ?>" 
                                            <?php
                                            if (@in_array($user->ID, $template_authors)) {
                                                echo 'selected="selected"';
                                            }
                                            ?>
                                                    ><?php echo esc_html($user->display_name); ?></option>
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
                                            <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Show post comment count on blog layout', 'blog-designer'); ?></span></span>
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
                                                <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
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
                                    <textarea class="widefat textarea" name="custom_css" id="custom_css" placeholder=".class_name{ color:#ffffff }"><?php echo wp_unslash(get_option('custom_css')); ?></textarea>
                                    <div class="bd-setting-description bd-note">
                                        <b class=""><?php _e('Example', 'blog-designer'); ?>:</b>
    <?php echo '.class_name{ color:#ffffff }'; ?>
                                    </div>
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
                                    <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Enter main container class name.', 'blog-designer'); ?> <br/> <?php _e('Leave it blank if you do not want to use it', 'blog-designer'); ?></span></span>
                                    <input type="text" name="main_container_class" id="main_container_class" placeholder="<?php esc_attr_e('main cover class name', 'blog-designer'); ?>" value="<?php echo isset($settings['main_container_class']) ? $settings['main_container_class'] : ''; ?>"/>
                                </div>
                            </li>

                            <li class="blog-columns-tr">
                                <div class="bd-left">
                                    <span class="bd-key-title">
    <?php echo _e('Blog Grid Columns', 'blog-designer'); ?>
                                    <?php echo '<br />(<i>' . __('Desktop - Above', 'blog-designer') . ' 980px</i>)'; ?>
                                    </span>
                                </div>
                                <div class="bd-right">
                                    <span class="fas fa-question-circle bd-tooltips-icon bd-tooltips-icon-cosettingslor"><span class="bd-tooltips"><?php _e('Select column for post', 'blog-designer'); ?></span></span>
    <?php $settings["template_columns"] = isset($settings["template_columns"]) ? $settings["template_columns"] : 2; ?>
                                    <select name="template_columns" id="template_columns" class="chosen-select">
                                        <option value="1" <?php if ($settings["template_columns"] == '1') { ?> selected="selected"<?php } ?>>
    <?php _e('1 Column', 'blog-designer'); ?>
                                        </option>
                                        <option value="2" <?php if ($settings["template_columns"] == '2') { ?> selected="selected"<?php } ?>>
    <?php _e('2 Columns', 'blog-designer'); ?>
                                        </option>
                                        <option value="3" <?php if ($settings["template_columns"] == '3') { ?> selected="selected"<?php } ?>>
    <?php _e('3 Columns', 'blog-designer'); ?>
                                        </option>
                                        <option value="4" <?php if ($settings["template_columns"] == '4') { ?> selected="selected"<?php } ?>>
    <?php _e('4 Columns', 'blog-designer'); ?>
                                        </option>
                                    </select>
                                </div>
                            </li>
                            <li class="blog-columns-tr">
                                <div class="bd-left">
                                    <span class="bd-key-title">
    <?php echo _e('Blog Grid Columns', 'blog-designer'); ?>
                                    <?php echo '<br />(<i>' . __('iPad', 'blog-designer') . ' - 720px - 980px</i>)'; ?>
                                    </span>
                                </div>
                                <div class="bd-right">
                                    <span class="fas fa-question-circle bd-tooltips-icon bd-tooltips-icon-color"><span class="bd-tooltips"><?php _e('Select column for post', 'blog-designer'); ?></span></span>
    <?php $settings["template_columns_ipad"] = isset($settings["template_columns_ipad"]) ? $settings["template_columns_ipad"] : 2; ?>
                                    <select name="template_columns_ipad" id="template_columns_ipad" class="chosen-select">
                                        <option value="1" <?php if ($settings["template_columns_ipad"] == '1') { ?> selected="selected"<?php } ?>>
    <?php _e('1 Column', 'blog-designer'); ?>
                                        </option>
                                        <option value="2" <?php if ($settings["template_columns_ipad"] == '2') { ?> selected="selected"<?php } ?>>
    <?php _e('2 Columns', 'blog-designer'); ?>
                                        </option>
                                        <option value="3" <?php if ($settings["template_columns_ipad"] == '3') { ?> selected="selected"<?php } ?>>
    <?php _e('3 Columns', 'blog-designer'); ?>
                                        </option>
                                        <option value="4" <?php if ($settings["template_columns_ipad"] == '4') { ?> selected="selected"<?php } ?>>
    <?php _e('4 Columns', 'blog-designer'); ?>
                                        </option>
                                    </select>
                                </div>
                            </li>
                            <li class="blog-columns-tr">
                                <div class="bd-left">
                                    <span class="bd-key-title">
    <?php echo _e('Blog Grid Columns', 'blog-designer'); ?>
                                    <?php echo '<br />(<i>' . __('Tablet', 'blog-designer') . ' - 480px - 720px</i>)'; ?>
                                    </span>
                                </div>
                                <div class="bd-right">
                                    <span class="fas fa-question-circle bd-tooltips-icon bd-tooltips-icon-color"><span class="bd-tooltips"><?php _e('Select column for post', 'blog-designer'); ?></span></span>
    <?php $settings["template_columns_tablet"] = isset($settings["template_columns_tablet"]) ? $settings["template_columns_tablet"] : 2; ?>
                                    <select name="template_columns_tablet" id="template_columns_tablet" class="chosen-select">
                                        <option value="1" <?php if ($settings["template_columns_tablet"] == '1') { ?> selected="selected"<?php } ?>>
    <?php _e('1 Column', 'blog-designer'); ?>
                                        </option>
                                        <option value="2" <?php if ($settings["template_columns_tablet"] == '2') { ?> selected="selected"<?php } ?>>
    <?php _e('2 Columns', 'blog-designer'); ?>
                                        </option>
                                        <option value="3" <?php if ($settings["template_columns_tablet"] == '3') { ?> selected="selected"<?php } ?>>
    <?php _e('3 Columns', 'blog-designer'); ?>
                                        </option>
                                        <option value="4" <?php if ($settings["template_columns_tablet"] == '4') { ?> selected="selected"<?php } ?>>
    <?php _e('4 Columns', 'blog-designer'); ?>
                                        </option>
                                    </select>
                                </div>
                            </li>
                            <li class="blog-columns-tr">
                                <div class="bd-left">
                                    <span class="bd-key-title">
    <?php echo _e('Blog Grid Columns', 'blog-designer'); ?>
                                    <?php echo '<br />(<i>' . __('Mobile - Smaller Than', 'blog-designer') . ' 480px </i>)'; ?>
                                    </span>
                                </div>
                                <div class="bd-right">
                                    <span class="fas fa-question-circle bd-tooltips-icon bd-tooltips-icon-color"><span class="bd-tooltips"><?php _e('Select column for post', 'blog-designer'); ?></span></span>
    <?php $settings["template_columns_mobile"] = isset($settings["template_columns_mobile"]) ? $settings["template_columns_mobile"] : 2; ?>
                                    <select name="template_columns_mobile" id="template_columns_mobile" class="chosen-select">
                                        <option value="1" <?php if ($settings["template_columns_mobile"] == '1') { ?> selected="selected"<?php } ?>>
    <?php _e('1 Column', 'blog-designer'); ?>
                                        </option>
                                        <option value="2" <?php if ($settings["template_columns_mobile"] == '2') { ?> selected="selected"<?php } ?>>
    <?php _e('2 Columns', 'blog-designer'); ?>
                                        </option>
                                        <option value="3" <?php if ($settings["template_columns_mobile"] == '3') { ?> selected="selected"<?php } ?>>
    <?php _e('3 Columns', 'blog-designer'); ?>
                                        </option>
                                        <option value="4" <?php if ($settings["template_columns_mobile"] == '4') { ?> selected="selected"<?php } ?>>
    <?php _e('4 Columns', 'blog-designer'); ?>
                                        </option>
                                    </select>
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
                                    <input type="text" name="template_color" id="template_color" value="<?php echo isset($settings['template_color']) ? $settings['template_color'] : ''; ?>"/>
                                </div>
                            </li>

                            <li class="hoverbackcolor-tr">
                                <div class="bd-left">
                                    <span class="bd-key-title">
    <?php _e('Blog Posts hover Background Color', 'blog-designer'); ?>
                                    </span>
                                </div>
                                <div class="bd-right">
                                    <span class="fas fa-question-circle bd-tooltips-icon bd-tooltips-icon-color"><span class="bd-tooltips"><?php _e('Select post background color', 'blog-designer'); ?></span></span>
                                    <input type="text" name="grid_hoverback_color" id="grid_hoverback_color" value="<?php echo ( isset($settings['grid_hoverback_color']) ) ? $settings['grid_hoverback_color'] : ''; ?>"/>
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
                                    <input type="text" name="template_bgcolor" id="template_bgcolor" value="<?php echo ( isset($settings['template_bgcolor']) ) ? $settings['template_bgcolor'] : ''; ?>"/>
                                </div>
                            </li>
                            <li class="blog-template-tr alternative-tr">
                                <div class="bd-left">
                                    <span class="bd-key-title">
                                    <?php _e('Alternative Background Color', 'blog-designer'); ?>
                                    </span>
                                </div>
                                <div class="bd-right">
                                    <span class="fas fa-question-circle bd-tooltips-icon bd-tooltips-icon-color"><span class="bd-tooltips"><?php _e('Display alternative background color', 'blog-designer'); ?></span></span>
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
                                    <input type="text" name="template_alterbgcolor" id="template_alterbgcolor" value="<?php echo ( isset($settings['template_alterbgcolor']) ) ? $settings['template_alterbgcolor'] : ''; ?>"/>
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
                                    <input type="text" name="template_ftcolor" id="template_ftcolor" value="<?php echo ( isset($settings['template_ftcolor']) ) ? $settings['template_ftcolor'] : ''; ?>"/>
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
                                    <input type="text" name="template_fthovercolor" id="template_fthovercolor" value="<?php echo ( isset($settings['template_fthovercolor']) ) ? $settings['template_fthovercolor'] : ''; ?>" data-default-color="<?php echo ( isset($settings['template_fthovercolor']) ) ? $settings['template_fthovercolor'] : ''; ?>"/>
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
                                    <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
                                </div>
                                <div class="bd-right">
                                    <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select post title link', 'blog-designer'); ?></span></span>
                                    <fieldset class="buttonset">
                                        <input id="bdp_post_title_link_1" name="bdp_post_title_link" type="radio" value="1" checked="checked"/>
                                        <label for="bdp_post_title_link_1"><?php _e('Yes', 'blog-designer'); ?></label>
                                        <input id="bdp_post_title_link_0" name="bdp_post_title_link" type="radio" value="0"/>
                                        <label for="bdp_post_title_link_0"><?php _e('No', 'blog-designer'); ?></label>
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
                                    <input type="text" name="template_titlecolor" id="template_titlecolor" value="<?php echo ( isset($settings['template_titlecolor']) ) ? $settings['template_titlecolor'] : ''; ?>"/>
                                </div>
                            </li>
                            <li class="pro-feature">
                                <div class="bd-left">
                                    <span class="bd-key-title">
    <?php _e('Post Title Link Hover Color', 'blog-designer'); ?>
                                    </span>
                                    <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
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
                                    <input type="text" name="template_titlebackcolor" id="template_titlebackcolor" value="<?php echo ( isset($settings['template_titlebackcolor']) ) ? $settings['template_titlebackcolor'] : ''; ?>"/>
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
                                            <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
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
                                            <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
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
                                            <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
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
                                            <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
                                            <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Display italic font style', 'blog-designer'); ?></span></span>
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
                                            <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
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
                                            <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
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
                                            <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
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
                                    <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
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
                                    <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
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
                                    <input type="text" name="template_contentcolor" id="template_contentcolor" value="<?php echo $settings['template_contentcolor']; ?>"/>
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
    $read_more_on = ( $read_more_on != '' ) ? $read_more_on : 2;
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
                                            <input type="text" name="template_readmorecolor" id="template_readmorecolor" value="<?php echo ( isset($settings['template_readmorecolor']) ) ? $settings['template_readmorecolor'] : ''; ?>"/>
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
                                            <input type="text" name="template_readmorebackcolor" id="template_readmorebackcolor" value="<?php echo ( isset($settings['template_readmorebackcolor']) ) ? $settings['template_readmorebackcolor'] : ''; ?>"/>
                                        </div>
                                    </div>
                                    <div class="bd-typography-cover read_more_text_background pro-feature">
                                        <div class="bdp-typography-label">
                                            <span class="bd-key-title">
    <?php _e('Hover Background Color', 'blog-designer'); ?>
                                            </span>
                                            <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
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
                                            <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
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
                                            <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
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
                                            <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
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
                                            <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
                                            <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Display italic font style', 'blog-designer'); ?></span></span>
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
                                            <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
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
                                            <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
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
                                            <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
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
                    <div id="bdpslider" class="postbox postbox-with-fw-options" <?php echo $bdpslider_class_show; ?>>
                        <ul class="bd-settings">
                            <li>
                                <div class="bd-left">
                                    <span class="bdp-key-title">
                                    <?php _e('Slider Effect', 'blog-designer'); ?>
                                    </span>
                                </div>
                                <div class="bd-right">
                                    <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select effect for slider layout', 'blog-designer'); ?></span></span>
    <?php $settings["template_slider_effect"] = (isset($settings["template_slider_effect"])) ? $settings["template_slider_effect"] : ''; ?>
                                    <select name="template_slider_effect" id="template_slider_effect" class="chosen-select">
                                        <option value="slide" <?php if ($settings["template_slider_effect"] == 'slide') { ?> selected="selected"<?php } ?>>
    <?php _e('Slide', 'blog-designer'); ?>
                                        </option>
                                        <option value="fade" <?php if ($settings["template_slider_effect"] == 'fade') { ?> selected="selected"<?php } ?>>
    <?php _e('Fade', 'blog-designer'); ?>
                                        </option>
                                    </select>
                                </div>
                            </li>

                            <li class="slider_columns_tr">
                                <div class="bd-left">
                                    <span class="bdp-key-title">
    <?php _e('Slider Columns', 'blog-designer'); ?>
                                    <?php echo '<br />(<i>' . __('Desktop - Above', 'blog-designer') . ' 980px</i>)'; ?>
                                    </span>
                                </div>
                                <div class="bd-right">
                                    <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select column for slider', 'blog-designer'); ?></span></span>
    <?php $settings["template_slider_columns"] = (isset($settings["template_slider_columns"])) ? $settings["template_slider_columns"] : 2; ?>
                                    <select name="template_slider_columns" id="template_slider_columns" class="chosen-select">
                                        <option value="1" <?php if ($settings["template_slider_columns"] == '1') { ?> selected="selected"<?php } ?>>
    <?php _e('1 Column', 'blog-designer'); ?>
                                        </option>
                                        <option value="2" <?php if ($settings["template_slider_columns"] == '2') { ?> selected="selected"<?php } ?>>
    <?php _e('2 Columns', 'blog-designer'); ?>
                                        </option>
                                        <option value="3" <?php if ($settings["template_slider_columns"] == '3') { ?> selected="selected"<?php } ?>>
    <?php _e('3 Columns', 'blog-designer'); ?>
                                        </option>
                                        <option value="4" <?php if ($settings["template_slider_columns"] == '4') { ?> selected="selected"<?php } ?>>
    <?php _e('4 Columns', 'blog-designer'); ?>
                                        </option>
                                        <option value="5" <?php if ($settings["template_slider_columns"] == '5') { ?> selected="selected"<?php } ?>>
    <?php _e('5 Columns', 'blog-designer'); ?>
                                        </option>
                                        <option value="6" <?php if ($settings["template_slider_columns"] == '6') { ?> selected="selected"<?php } ?>>
    <?php _e('6 Columns', 'blog-designer'); ?>
                                        </option>
                                    </select>
                                </div>
                            </li>

                            <li class="slider_columns_tr">
                                <div class="bd-left">
                                    <span class="bdp-key-title">
    <?php _e('Slider Columns', 'blog-designer'); ?>
                                    <?php echo '<br />(<i>' . __('iPad', 'blog-designer') . ' - 720px - 980px</i>)'; ?>
                                    </span>
                                </div>
                                <div class="bd-right">
                                    <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select column for slider', 'blog-designer'); ?></span></span>
    <?php $settings["template_slider_columns_ipad"] = (isset($settings["template_slider_columns_ipad"])) ? $settings["template_slider_columns_ipad"] : 2; ?>
                                    <select name="template_slider_columns_ipad" id="template_slider_columns_ipad" class="chosen-select">
                                        <option value="1" <?php if ($settings["template_slider_columns_ipad"] == '1') { ?> selected="selected"<?php } ?>>
    <?php _e('1 Column', 'blog-designer'); ?>
                                        </option>
                                        <option value="2" <?php if ($settings["template_slider_columns_ipad"] == '2') { ?> selected="selected"<?php } ?>>
    <?php _e('2 Columns', 'blog-designer'); ?>
                                        </option>
                                        <option value="3" <?php if ($settings["template_slider_columns_ipad"] == '3') { ?> selected="selected"<?php } ?>>
    <?php _e('3 Columns', 'blog-designer'); ?>
                                        </option>
                                        <option value="4" <?php if ($settings["template_slider_columns_ipad"] == '4') { ?> selected="selected"<?php } ?>>
    <?php _e('4 Columns', 'blog-designer'); ?>
                                        </option>
                                        <option value="5" <?php if ($settings["template_slider_columns_ipad"] == '5') { ?> selected="selected"<?php } ?>>
    <?php _e('5 Columns', 'blog-designer'); ?>
                                        </option>
                                        <option value="6" <?php if ($settings["template_slider_columns_ipad"] == '6') { ?> selected="selected"<?php } ?>>
    <?php _e('6 Columns', 'blog-designer'); ?>
                                        </option>
                                    </select>
                                </div>
                            </li>

                            <li class="slider_columns_tr">
                                <div class="bd-left">
                                    <span class="bdp-key-title">
    <?php _e('Slider Columns', 'blog-designer'); ?>
                                    <?php echo '<br />(<i>' . __('Tablet', 'blog-designer') . ' - 480px - 720px</i>)'; ?>
                                    </span>
                                </div>
                                <div class="bd-right">
                                    <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select column for slider', 'blog-designer'); ?></span></span>
    <?php $settings["template_slider_columns_tablet"] = (isset($settings["template_slider_columns_tablet"])) ? $settings["template_slider_columns_tablet"] : 2; ?>
                                    <select name="template_slider_columns_tablet" id="template_slider_columns_tablet" class="chosen-select">
                                        <option value="1" <?php if ($settings["template_slider_columns_tablet"] == '1') { ?> selected="selected"<?php } ?>>
    <?php _e('1 Column', 'blog-designer'); ?>
                                        </option>
                                        <option value="2" <?php if ($settings["template_slider_columns_tablet"] == '2') { ?> selected="selected"<?php } ?>>
    <?php _e('2 Columns', 'blog-designer'); ?>
                                        </option>
                                        <option value="3" <?php if ($settings["template_slider_columns_tablet"] == '3') { ?> selected="selected"<?php } ?>>
    <?php _e('3 Columns', 'blog-designer'); ?>
                                        </option>
                                        <option value="4" <?php if ($settings["template_slider_columns_tablet"] == '4') { ?> selected="selected"<?php } ?>>
    <?php _e('4 Columns', 'blog-designer'); ?>
                                        </option>
                                        <option value="5" <?php if ($settings["template_slider_columns_tablet"] == '5') { ?> selected="selected"<?php } ?>>
    <?php _e('5 Columns', 'blog-designer'); ?>
                                        </option>
                                        <option value="6" <?php if ($settings["template_slider_columns_tablet"] == '6') { ?> selected="selected"<?php } ?>>
    <?php _e('6 Columns', 'blog-designer'); ?>
                                        </option>
                                    </select>
                                </div>
                            </li>

                            <li class="slider_columns_tr">
                                <div class="bd-left">
                                    <span class="bdp-key-title">
    <?php _e('Slider Columns', 'blog-designer'); ?>
    <?php echo '<br />(<i>' . __('Mobile - Smaller Than', 'blog-designer') . ' 480px </i>)'; ?>
                                    </span>
                                </div>
                                <div class="bd-right">
                                    <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select column for slider', 'blog-designer'); ?></span></span>

    <?php $settings["template_slider_columns_mobile"] = (isset($settings["template_slider_columns_mobile"])) ? $settings["template_slider_columns_mobile"] : 1; ?>
                                    <select name="template_slider_columns_mobile" id="template_slider_columns_mobile" class="chosen-select">
                                        <option value="1" <?php if ($settings["template_slider_columns_mobile"] == '1') { ?> selected="selected"<?php } ?>>
    <?php _e('1 Column', 'blog-designer'); ?>
                                        </option>
                                        <option value="2" <?php if ($settings["template_slider_columns_mobile"] == '2') { ?> selected="selected"<?php } ?>>
    <?php _e('2 Columns', 'blog-designer'); ?>
                                        </option>
                                        <option value="3" <?php if ($settings["template_slider_columns_mobile"] == '3') { ?> selected="selected"<?php } ?>>
    <?php _e('3 Columns', 'blog-designer'); ?>
                                        </option>
                                        <option value="4" <?php if ($settings["template_slider_columns_mobile"] == '4') { ?> selected="selected"<?php } ?>>
    <?php _e('4 Columns', 'blog-designer'); ?>
                                        </option>
                                        <option value="5" <?php if ($settings["template_slider_columns_mobile"] == '5') { ?> selected="selected"<?php } ?>>
    <?php _e('5 Columns', 'blog-designer'); ?>
                                        </option>
                                        <option value="6" <?php if ($settings["template_slider_columns_mobile"] == '6') { ?> selected="selected"<?php } ?>>
    <?php _e('6 Columns', 'blog-designer'); ?>
                                        </option>
                                    </select>
                                </div>
                            </li>

                            <li class="slider_scroll_tr pro-feature">
                                <div class="bd-left">
                                    <span class="bdp-key-title">
    <?php _e('Slide to Scroll', 'blog-designer'); ?>
                                    </span>
                                    <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
                                </div>
                                <div class="bd-right">
                                    <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select number of slide to scroll', 'blog-designer'); ?></span></span>
    <?php $template_slider_scroll = isset($settings['template_slider_scroll']) ? $settings['template_slider_scroll'] : '1'; ?>
                                    <select name="template_slider_scroll" id="template_slider_scroll" class="chosen-select">
                                        <option value="1" <?php if ($template_slider_scroll == '1') { ?> selected="selected"<?php } ?>>1</option>
                                        <option value="2" <?php if ($template_slider_scroll == '2') { ?> selected="selected"<?php } ?>>2</option>
                                        <option value="3" <?php if ($template_slider_scroll == '3') { ?> selected="selected"<?php } ?>>3</option>
                                    </select>
                                </div>
                            </li>

                            <li class="pro-feature">
                                <div class="bd-left">
                                    <span class="bdp-key-title">
    <?php _e('Display Slider Navigation', 'blog-designer'); ?>
                                    </span>
                                    <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
                                </div>
                                <div class="bd-right">
                                    <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Show slider navigation', 'blog-designer'); ?></span></span>
    <?php $display_slider_navigation = isset($settings['display_slider_navigation']) ? $settings['display_slider_navigation'] : '1'; ?>
                                    <fieldset class="bdp-social-options bdp-display_slider_navigation buttonset buttonset-hide ui-buttonset">
                                        <input id="display_slider_navigation_1" name="display_slider_navigation" type="radio" value="1" <?php checked(1, $display_slider_navigation); ?> />
                                        <label for="display_slider_navigation_1" <?php checked(1, $display_slider_navigation); ?>><?php _e('Yes', 'blog-designer'); ?></label>
                                        <input id="display_slider_navigation_0" name="display_slider_navigation" type="radio" value="0" <?php checked(0, $display_slider_navigation); ?> />
                                        <label for="display_slider_navigation_0" <?php checked(0, $display_slider_navigation); ?>><?php _e('No', 'blog-designer'); ?></label>
                                    </fieldset>
                                </div>
                            </li>

                            <li class="pro-feature select_slider_navigation_tr">
                                <div class="bd-left">
                                    <span class="bdp-key-title">
    <?php _e('Slider Navigation Icon', 'blog-designer'); ?>
                                    </span>
                                    <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
                                </div>
                                <div class="bd-right">
                                    <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select Slider navigation icon', 'blog-designer'); ?></span></span>
    <?php $slider_navigation = isset($settings['navigation_style_hidden']) ? $settings['navigation_style_hidden'] : 'navigation3'; ?>
                                    <div class="select_button_upper_div ">
                                        <div class="bdp_select_template_button_div">
                                            <input type="button" class="button bdp_select_navigation" value="<?php esc_attr_e('Select Navigation', 'blog-designer'); ?>">
                                            <input style="visibility: hidden;" type="hidden" id="navigation_style_hidden" class="navigation_style_hidden" name="navigation_style_hidden" value="<?php echo $slider_navigation; ?>" />
                                        </div>
                                        <div class="bdp_selected_navigation_image">
                                            <div class="bdp-dialog-navigation-style slider_controls" >
                                                <div class="bdp_navigation_image_holder navigation_hidden" >
                                                    <img src="<?php echo BLOGDESIGNER_URL . '/images/navigation/' . $slider_navigation . '.png'; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            <li>
                                <div class="bd-left">
                                    <span class="bdp-key-title">
                                    <?php _e('Display Slider Controls', 'blog-designer'); ?>
                                    </span>
                                </div>
                                <div class="bd-right">
                                    <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Show slider control', 'blog-designer'); ?></span></span>
    <?php $display_slider_controls = isset($settings['display_slider_controls']) ? $settings['display_slider_controls'] : '1'; ?>
                                    <fieldset class="bdp-social-options bdp-display_slider_controls buttonset buttonset-hide ui-buttonset">
                                        <input id="display_slider_controls_1" name="display_slider_controls" type="radio" value="1" <?php checked(1, $display_slider_controls); ?> />
                                        <label for="display_slider_controls_1" <?php checked(1, $display_slider_controls); ?>><?php _e('Yes', 'blog-designer'); ?></label>
                                        <input id="display_slider_controls_0" name="display_slider_controls" type="radio" value="0" <?php checked(0, $display_slider_controls); ?> />
                                        <label for="display_slider_controls_0" <?php checked(0, $display_slider_controls); ?>><?php _e('No', 'blog-designer'); ?></label>
                                    </fieldset>
                                </div>
                            </li>

                            <li class="select_slider_controls_tr pro-feature">
                                <div class="bd-left">
                                    <span class="bdp-key-title">
    <?php _e('Select Slider Arrow', 'blog-designer'); ?>
                                    </span>
                                    <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
                                </div>
                                <div class="bd-right">
                                    <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Select slider arrow icon', 'blog-designer'); ?></span></span>
    <?php $slider_arrow = isset($settings['arrow_style_hidden']) ? $settings['arrow_style_hidden'] : 'arrow1'; ?>
                                    <div class="select_button_upper_div ">
                                        <div class="bdp_select_template_button_div">
                                            <input type="button" class="button bdp_select_arrow" value="<?php esc_attr_e('Select Arrow', 'blog-designer'); ?>">
                                            <input style="visibility: hidden;" type="hidden" id="arrow_style_hidden" class="arrow_style_hidden" name="arrow_style_hidden" value="<?php echo $slider_arrow; ?>" />
                                        </div>
                                        <div class="bdp_selected_arrow_image">
                                            <div class="bdp-dialog-arrow-style slider_controls" >
                                                <div class="bdp_arrow_image_holder arrow_hidden" >
                                                    <img src="<?php echo BLOGDESIGNER_URL . '/images/arrow/' . $slider_arrow . '.png'; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            <li>
                                <div class="bd-left">
                                    <span class="bdp-key-title">
                                    <?php _e('Slider Autoplay', 'blog-designer'); ?>
                                    </span>
                                </div>
                                <div class="bd-right">
                                    <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Show slider autoplay', 'blog-designer'); ?></span></span>
    <?php $slider_autoplay = isset($settings['slider_autoplay']) ? $settings['slider_autoplay'] : '1'; ?>
                                    <fieldset class="bdp-social-options bdp-slider_autoplay buttonset buttonset-hide ui-buttonset">
                                        <input id="slider_autoplay_1" name="slider_autoplay" type="radio" value="1" <?php checked(1, $slider_autoplay); ?> />
                                        <label for="slider_autoplay_1" <?php checked(1, $slider_autoplay); ?>><?php _e('Yes', 'blog-designer'); ?></label>
                                        <input id="slider_autoplay_0" name="slider_autoplay" type="radio" value="0" <?php checked(0, $slider_autoplay); ?> />
                                        <label for="slider_autoplay_0" <?php checked(0, $slider_autoplay); ?>><?php _e('No', 'blog-designer'); ?></label>
                                    </fieldset>
                                </div>
                            </li>

                            <li class="slider_autoplay_tr">
                                <div class="bd-left">
                                    <span class="bdp-key-title">
                                    <?php _e('Enter slider autoplay intervals (ms)', 'blog-designer'); ?>
                                    </span>
                                </div>
                                <div class="bd-right">
                                    <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Enter slider autoplay intervals', 'blog-designer'); ?></span></span>
    <?php $slider_autoplay_intervals = isset($settings['slider_autoplay_intervals']) ? $settings['slider_autoplay_intervals'] : '1'; ?>
                                    <input type="number" id="slider_autoplay_intervals" name="slider_autoplay_intervals" step="1" min="0" value="<?php echo isset($settings['slider_autoplay_intervals']) ? $settings['slider_autoplay_intervals'] : '3000'; ?>" placeholder="<?php esc_attr_e('Enter slider intervals', 'blog-designer'); ?>" onkeypress="return isNumberKey(event)">
                                </div>
                            </li>

                            <li class="slider_autoplay_tr">
                                <div class="bd-left">
                                    <span class="bdp-key-title">
                                    <?php _e('Slider Speed (ms)', 'blog-designer'); ?>
                                    </span>
                                </div>
                                <div class="bd-right">
                                    <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Enter slider speed', 'blog-designer'); ?></span></span>
    <?php $slider_speed = isset($settings['slider_speed']) ? $settings['slider_speed'] : '300'; ?>
                                    <input type="number" id="slider_speed" name="slider_speed" step="1" min="0" value="<?php echo isset($settings['slider_speed']) ? $settings['slider_speed'] : '300'; ?>" placeholder="<?php esc_attr_e('Enter slider intervals', 'blog-designer'); ?>" onkeypress="return isNumberKey(event)">
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
                                    <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
                                </div>
                                <div class="bd-right">
                                    <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Enable/Disable post image link', 'blog-designer'); ?></span></span>
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
                                    <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
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
                                    <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
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
                                    <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
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
                                    <span class="bdp-pro-tag"><?php _e('PRO', 'blog-designer'); ?></span>
                                </div>
                                <div class="bd-right">
                                    <span class="fas fa-question-circle bd-tooltips-icon bd-tooltips-icon-social"><span class="bd-tooltips"><?php _e('Select icon theme from available icon theme', 'blog-designer'); ?></span></span>
                                    <div class="social-share-theme social-share-td">
    <?php for ($i = 1; $i <= 10; $i++) { ?>
                                            <div class="social-cover social_share_theme_<?php echo $i; ?>">
                                                <label>
                                                    <input type="radio" id="default_icon_theme_<?php echo $i; ?>" value="" name="default_icon_theme" />
                                                    <span class="bdp-social-icons facebook-icon bdp_theme_wrapper"></span>
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
                                            <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Display facebook share link', 'blog-designer'); ?></span></span>
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
                                            <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Display linkedin share link', 'blog-designer'); ?></span></span>
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
    <?php _e('Pinterest Share link', 'blog-designer'); ?>
                                            </span>
                                            <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Display Pinterest share link', 'blog-designer'); ?></span></span>
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
                                            <span class="fas fa-question-circle bd-tooltips-icon"><span class="bd-tooltips"><?php _e('Display twitter share link', 'blog-designer'); ?></span></span>
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
    <?php wp_nonce_field('blog_nonce_ac', 'blog_nonce'); ?>
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
                        <?php _e('Downloads:', 'blog-designer'); ?><?php bd_get_total_downloads(); ?>
                        <?php
                        if ($wp_version > 3.8) {
                            bd_custom_star_rating();
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
                            <li><?php _e('50 Beautiful Blog Templates', 'blog-designer'); ?></li>
                            <li><?php _e('5+ Unique Timeline Templates', 'blog-designer'); ?></li>
                            <li><?php _e('10 Unique Grid Templates', 'blog-designer'); ?></li>
                            <li><?php _e('3 Unique Slider Templates', 'blog-designer'); ?></li>
                            <li><?php _e('200+ Blog Layout Variations', 'blog-designer'); ?></li>
                            <li><?php _e('Multiple Single Post Layout options', 'blog-designer'); ?></li>
                            <li><?php _e('Category, Tag, Author & Date Layouts', 'blog-designer'); ?></li>
                            <li><?php _e('Post Type & Taxonomy Filter', 'blog-designer'); ?></li>
                            <li><?php _e('800+ Google Font Support', 'blog-designer'); ?></li>
                            <li><?php _e('600+ Font Awesome Icons Support', 'blog-designer'); ?></li>
                        </ul>
                        <p class="pricing_change"><?php _e('Now only at', 'blog-designer'); ?> <ins>39</ins></p>
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
                foreach ($classes as $class) {
                    $all_class[] = $class;
                }
            }
            $count = array_count_values($all_class);
            ?>
            <ul class="bd_template_tab">
                <li class="bd_current_tab">
                    <a href="#all"><?php _e('All', 'blog-designer'); ?></a>
                </li>
                <li>
                    <a href="#free"><?php echo __('Free', 'blog-designer') . ' (' . $count['free'] . ')'; ?></a>
                </li>
                <li>
                    <a href="#full-width"><?php echo __('Full Width', 'blog-designer') . ' (' . $count['full-width'] . ')'; ?></a>
                </li>
                <li>
                    <a href="#grid"><?php echo __('Grid', 'blog-designer') . ' (' . $count['grid'] . ')'; ?></a>
                </li>
                <li>
                    <a href="#masonry"><?php echo __('Masonry', 'blog-designer') . ' (' . $count['masonry'] . ')'; ?></a>
                </li>
                <li>
                    <a href="#magazine"><?php echo __('Magazine', 'blog-designer') . ' (' . $count['magazine'] . ')'; ?></a>
                </li>
                <li>
                    <a href="#timeline"><?php echo __('Timeline', 'blog-designer') . ' (' . $count['timeline'] . ')'; ?></a>
                </li>
                <li>
                    <a href="#slider"><?php echo __('Slider', 'blog-designer') . ' (' . $count['slider'] . ')'; ?></a>
                </li>
                <div class="bd-template-search-cover">
                    <input type="text" class="bd-template-search" id="bd-template-search" placeholder="<?php _e('Search Template', 'blog-designer'); ?>" />
                    <span class="bd-template-search-clear"></span>
                </div>
            </ul>

            <?php
            echo '<div class="bd-template-cover">';
            foreach ($tempate_list as $key => $value) {
                if ($key == 'boxy-clean' || $key == 'crayon_slider' || $key == 'classical' || $key == 'lightbreeze' || $key == 'spektrum' || $key == 'evolution' || $key == 'timeline' || $key == 'news') {
                    $class = 'bd-lite';
                } else {
                    $class = 'bp-pro';
                }
                ?>
                <div class="bd-template-thumbnail <?php echo $value['class'] . ' ' . $class; ?>">
                    <div class="bd-template-thumbnail-inner">
                        <img src="<?php echo BLOGDESIGNER_URL . 'images/layouts/' . $value['image_name']; ?>" data-value="<?php echo $key; ?>" alt="<?php echo $value['template_name']; ?>" title="<?php echo $value['template_name']; ?>">
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
                                <img src="<?php echo BLOGDESIGNER_URL . 'images/pro-tag.png'; ?>" alt="Available in Pro" />
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
                    <img src="<?php echo BLOGDESIGNER_URL . 'images/bd_advertisement_popup.png'; ?>" />
                </a>
            </div>
        </div>
    </div>
    <?php
}

/*
 * Display Optin form
 */

function bd_welcome_function() {
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
            <a href="javascript:void(0)" class='button button-secondary' onclick="bd_submit_optin('cancel')">
                <?php
                echo esc_attr(__('Skip', 'blog-designer'));
                echo ' &amp; ';
                echo esc_attr(__('Continue', 'blog-designer'));
                ?>
            </a>
            <a href="javascript:void(0)" class='button button-primary' onclick="bd_submit_optin('submit')">
                <?php
                echo esc_attr(__('Opt-In', 'blog-designer'));
                echo ' &amp; ';
                echo esc_attr(__('Continue', 'blog-designer'));
                ?>
            </a>
        </p>
    </div>
    <?php
}

/**
 * Display Pagination
 */
function bd_pagination($args = array()) {
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
    $links = paginate_links(
            array(
                'base' => $pagenum_link,
                'format' => $format,
                'total' => $GLOBALS['wp_query']->max_num_pages,
                'current' => $paged,
                'mid_size' => 1,
                'add_args' => array_map('urlencode', $query_args),
                'prev_text' => '&larr; ' . __('Previous', 'blog-designer'),
                'next_text' => __('Next', 'blog-designer') . ' &rarr;',
                'type' => 'list',
            )
    );

    if ($links) :
        $navigation .= '<nav class="navigation paging-navigation" role="navigation">';
        $navigation .= $links;
        $navigation .= '</nav>';
    endif;
    return $navigation;
}

/**
 * Return page
 */
function bd_paged() {
    if (strstr($_SERVER['REQUEST_URI'], 'paged') || strstr($_SERVER['REQUEST_URI'], 'page')) {
        if (isset($_REQUEST['paged'])) {
            $paged = intval($_REQUEST['paged']);
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

/**
 * Start session if not
 */
function bd_session_start() {
    if (version_compare(phpversion(), '5.4.0') != -1) {
        if (session_status() == PHP_SESSION_DISABLED) {
            session_start();
        }
    } else {
        if (session_id() == '') {
            session_start();
        }
    }
}

/**
 * Subscribe email form
 */
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
                <a onclick='bd_submit_optin("deactivate")' class="button button-secondary">
                    <?php
                    _e('Submit', 'blog-designer');
                    echo ' &amp; ';
                    _e('Deactivate', 'blog-designer');
                    ?>
                </a>
                <input type="submit" name="sbtDeactivationFormClose" id="sbtDeactivationFormClosebd" class="button button-primary" value="<?php _e('Cancel', 'blog-designer'); ?>" />
                <a href="javascript:void(0)" class="bd-deactivation" aria-label="<?php _e('Deactivate Blog Designer', 'blog-designer'); ?>">
                    <?php
                    _e('Skip', 'blog-designer');
                    echo ' &amp; ';
                    _e('Deactivate', 'blog-designer');
                    ?>
                </a>
            </form>
            <div class="support-ticket-section">
                <h3><?php _e('Would you like to give us a chance to help you?', 'blog-designer'); ?></h3>
                <img src="<?php echo BLOGDESIGNER_URL . 'images/support-ticket.png'; ?>">
                <a href="<?php echo esc_url('http://support.solwininfotech.com/'); ?>"><?php _e('Create a support ticket', 'blog-designer'); ?></a>
            </div>
        </div>

    </div>
    <a style="display:none" href="#TB_inline?height=800&inlineId=sol_deactivation_widget_cover_bd" class="thickbox" id="deactivation_thickbox_bd"></a>
    <?php
}

/**
 * Remove read more
 */
function bd_remove_continue_reading($more) {
    return '';
}

/**
 * Display links
 */
function bd_plugin_links($links) {
    $bd_is_optin = get_option('bd_is_optin');
    if ($bd_is_optin == 'yes' || $bd_is_optin == 'no') {
        $start_page = 'designer_settings';
    } else {
        $start_page = 'designer_welcome_page';
    }
    $action_links = array(
        'settings' => '<a href="' . admin_url("admin.php?page=$start_page") . '" title="' . esc_attr(__('View Blog Designer Settings', 'blog-designer')) . '">' . __('Settings', 'blog-designer') . '</a>',
    );
    $links = array_merge($action_links, $links);
    $links['documents'] = '<a class="documentation_bd_plugin" target="_blank" href="' . esc_url('https://www.solwininfotech.com/documents/wordpress/blog-designer/') . '">' . __('Documentation', 'blog-designer') . '</a>';
    $links['upgrade'] = '<a target="_blank" href="' . esc_url('https://codecanyon.net/item/blog-designer-pro-for-wordpress/17069678?ref=solwin') . '" class="bd_upgrade_link">' . __('Upgrade', 'blog-designer') . '</a>';
    return $links;
}

/**
 * Fusion page builder support
 */
function bd_fsn_block() {
    if (function_exists('fsn_map')) {
        fsn_map(
                array(
                    'name' => __('Blog Designer', 'blog-designer'),
                    'shortcode_tag' => 'fsn_blog_designer',
                    'description' => __('To make your blog design more pretty, attractive and colorful.', 'blog-designer'),
                    'icon' => 'fsn_blog',
                )
        );
    }
}

/**
 * Fusion page builder support shortcode
 */
add_shortcode('fsn_blog_designer', 'bd_fsn_shortcode');

function bd_fsn_shortcode($atts, $content) {
    ob_start();
    ?>
    <div class="fsn-bdp <?php echo fsn_style_params_class($atts); ?>">
    <?php echo do_shortcode('[wp_blog_designer]'); ?>
    </div>
    <?php
    $output = ob_get_clean();
    return $output;
}

/**
 * Template search
 */
function bd_template_search_result() {
    $template_name = sanitize_text_field($_POST['temlate_name']);

    $tempate_list = bd_template_list();
    foreach ($tempate_list as $key => $value) {
        if ($template_name == '') {
            if ($key == 'boxy-clean' || $key == 'crayon_slider' || $key == 'classical' || $key == 'lightbreeze' || $key == 'spektrum' || $key == 'evolution' || $key == 'timeline' || $key == 'news') {
                $class = 'bd-lite';
            } else {
                $class = 'bp-pro';
            }
            ?>
            <div class="bd-template-thumbnail <?php echo $value['class'] . ' ' . $class; ?>">
                <div class="bd-template-thumbnail-inner">
                    <img src="<?php echo BLOGDESIGNER_URL . 'images/layouts/' . $value['image_name']; ?>" data-value="<?php echo $key; ?>" alt="<?php echo $value['template_name']; ?>" title="<?php echo $value['template_name']; ?>">
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
                            <img src="<?php echo BLOGDESIGNER_URL . 'images/pro-tag.png'; ?>" alt="Available in Pro" />
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
            if ($key == 'boxy-clean' || $key == 'crayon_slider' || $key == 'classical' || $key == 'lightbreeze' || $key == 'spektrum' || $key == 'evolution' || $key == 'timeline' || $key == 'news') {
                $class = 'bd-lite';
            } else {
                $class = 'bp-pro';
            }
            ?>
            <div class="bd-template-thumbnail <?php echo $value['class'] . ' ' . $class; ?>">
                <div class="bd-template-thumbnail-inner">
                    <img src="<?php echo BLOGDESIGNER_URL . 'images/layouts/' . $value['image_name']; ?>" data-value="<?php echo $key; ?>" alt="<?php echo $value['template_name']; ?>" title="<?php echo $value['template_name']; ?>">
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
                            <img src="<?php echo BLOGDESIGNER_URL . 'images/pro-tag.png'; ?>" alt="Available in Pro" />
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

/**
 * Get content in posts
 */
function bd_get_content($postid) {
    global $post;
    $content = '';
    $excerpt_length = get_option('excerpt_length');
    $display_html_tags = get_option('display_html_tags', true);
    if (get_option('rss_use_excerpt') == 0) {
        $content = apply_filters('the_content', get_the_content($postid));
    } elseif (get_option('excerpt_length') > 0) {

        if ($display_html_tags == 1) {
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
                $bp_excerpt_data = bp_close_tags($bp_excerpt_data);
            }
            $content = apply_filters('the_content', $bp_excerpt_data);
        } else {
            $text = $post->post_content;
            $text = str_replace('<!--more-->', '', $text);
            $text = apply_filters('the_content', $text);
            $text = str_replace(']]>', ']]&gt;', $text);
            $bp_excerpt_data = wp_trim_words($text, $excerpt_length, '');
            $bp_excerpt_data = apply_filters('wp_bd_excerpt_change', $bp_excerpt_data, $postid);
            $content = $bp_excerpt_data;
        }
    }
    return $content;
}

/**
 * Get html close tag
 */
function bp_close_tags($html = '') {
    if ($html == '') {
        return;
    }
    // put all opened tags into an array
    preg_match_all('#<([a-z]+)( .*)?(?!/)>#iU', $html, $result);
    $openedtags = $result[1];
    // put all closed tags into an array
    preg_match_all('#</([a-z]+)>#iU', $html, $result);
    $closedtags = $result[1];
    $len_opened = count($openedtags);
    // all tags are closed
    if (count($closedtags) == $len_opened) {
        return $html;
    }
    $openedtags = array_reverse($openedtags);
    // close tags
    for ($i = 0; $i < $len_opened; $i++) {
        if (!in_array($openedtags[$i], $closedtags)) {
            $html .= '</' . $openedtags[$i] . '>';
        } else {
            unset($closedtags[array_search($openedtags[$i], $closedtags)]);
        }
    }
    return $html;
}

/**
 * Create sample layout of blog
 */
function bd_create_sample_layout() {
    $page_id = '';
    $blog_page_id = wp_insert_post(
            array(
                'post_title' => __('Test Blog Page', 'blog-designer'),
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_content' => '[wp_blog_designer]',
            )
    );
    if ($blog_page_id) {
        $page_id = $blog_page_id;
    }
    update_option('blog_page_display', $page_id);
    $post_link = get_permalink($page_id);
    echo $post_link;
    exit;
}

/**
 * Submit optin data
 */
add_action('wp_ajax_bd_submit_optin', 'bd_submit_optin');

function bd_submit_optin() {
    global $wpdb, $wp_version;
    $bd_submit_type = '';
    if (isset($_POST['email'])) {
        $bd_email = sanitize_email($_POST['email']);
    } else {
        $bd_email = get_option('admin_url');
    }
    if (isset($_POST['type'])) {
        $bd_submit_type = sanitize_text_field($_POST['type']);
    }
    if ($bd_submit_type == 'submit') {
        $status_type = get_option('bd_is_optin');
        $theme_details = array();
        if ($wp_version >= 3.4) {
            $active_theme = wp_get_theme();
            $theme_details['theme_name'] = strip_tags($active_theme->name);
            $theme_details['theme_version'] = strip_tags($active_theme->version);
            $theme_details['author_url'] = strip_tags($active_theme->{'Author URI'});
        }
        $active_plugins = (array) get_option('active_plugins', array());
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

        $plugin_data = get_plugin_data(BLOGDESIGNER_DIR . 'blog-designer.php', $markup = true, $translate = true);
        $current_version = $plugin_data['Version'];

        $plugin_data = array();
        $plugin_data['plugin_name'] = 'Blog Designer';
        $plugin_data['plugin_slug'] = 'blog-designer';
        $plugin_data['plugin_version'] = $current_version;
        $plugin_data['plugin_status'] = $status_type;
        $plugin_data['site_url'] = home_url();
        $plugin_data['site_language'] = defined('WPLANG') && WPLANG ? WPLANG : get_locale();
        $current_user = wp_get_current_user();
        $f_name = $current_user->user_firstname;
        $l_name = $current_user->user_lastname;
        $plugin_data['site_user_name'] = esc_attr($f_name) . ' ' . esc_attr($l_name);
        $plugin_data['site_email'] = false !== $bd_email ? $bd_email : get_option('admin_email');
        $plugin_data['site_wordpress_version'] = $wp_version;
        $plugin_data['site_php_version'] = esc_attr(phpversion());
        $plugin_data['site_mysql_version'] = $wpdb->db_version();
        $plugin_data['site_max_input_vars'] = ini_get('max_input_vars');
        $plugin_data['site_php_memory_limit'] = ini_get('max_input_vars');
        $plugin_data['site_operating_system'] = ini_get('memory_limit') ? ini_get('memory_limit') : 'N/A';
        $plugin_data['site_extensions'] = get_loaded_extensions();
        $plugin_data['site_activated_plugins'] = $plugins;
        $plugin_data['site_activated_theme'] = $theme_details;
        $url = 'http://analytics.solwininfotech.com/';
        $response = wp_safe_remote_post(
                $url, array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(),
            'body' => array(
                'data' => maybe_serialize($plugin_data),
                'action' => 'plugin_analysis_data',
            ),
                )
        );
        update_option('bd_is_optin', 'yes');
    } elseif ($bd_submit_type == 'cancel') {
        update_option('bd_is_optin', 'no');
    } elseif ($bd_submit_type == 'deactivate') {
        $status_type = get_option('bd_is_optin');
        $theme_details = array();
        if ($wp_version >= 3.4) {
            $active_theme = wp_get_theme();
            $theme_details['theme_name'] = strip_tags($active_theme->name);
            $theme_details['theme_version'] = strip_tags($active_theme->version);
            $theme_details['author_url'] = strip_tags($active_theme->{'Author URI'});
        }
        $active_plugins = (array) get_option('active_plugins', array());
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

        $plugin_data = get_plugin_data(BLOGDESIGNER_DIR . 'blog-designer.php', $markup = true, $translate = true);
        $current_version = $plugin_data['Version'];

        $plugin_data = array();
        $plugin_data['plugin_name'] = 'Blog Designer';
        $plugin_data['plugin_slug'] = 'blog-designer';
        $reason_id = sanitize_text_field($_POST['selected_option_de']);
        $plugin_data['deactivation_option'] = $reason_id;
        $plugin_data['deactivation_option_text'] = sanitize_text_field($_POST['selected_option_de_text']);
        if ($reason_id == 9) {
            $plugin_data['deactivation_option_text'] = sanitize_text_field($_POST['selected_option_de_other']);
        }
        $plugin_data['plugin_version'] = $current_version;
        $plugin_data['plugin_status'] = $status_type;
        $plugin_data['site_url'] = home_url();
        $plugin_data['site_language'] = defined('WPLANG') && WPLANG ? WPLANG : get_locale();
        $current_user = wp_get_current_user();
        $f_name = $current_user->user_firstname;
        $l_name = $current_user->user_lastname;
        $plugin_data['site_user_name'] = esc_attr($f_name) . ' ' . esc_attr($l_name);
        $plugin_data['site_email'] = false !== $bd_email ? $bd_email : get_option('admin_email');
        $plugin_data['site_wordpress_version'] = $wp_version;
        $plugin_data['site_php_version'] = esc_attr(phpversion());
        $plugin_data['site_mysql_version'] = $wpdb->db_version();
        $plugin_data['site_max_input_vars'] = ini_get('max_input_vars');
        $plugin_data['site_php_memory_limit'] = ini_get('max_input_vars');
        $plugin_data['site_operating_system'] = ini_get('memory_limit') ? ini_get('memory_limit') : 'N/A';
        $plugin_data['site_extensions'] = get_loaded_extensions();
        $plugin_data['site_activated_plugins'] = $plugins;
        $plugin_data['site_activated_theme'] = $theme_details;
        $url = 'http://analytics.solwininfotech.com/';
        $response = wp_safe_remote_post(
                $url, array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(),
            'body' => array(
                'data' => maybe_serialize($plugin_data),
                'action' => 'plugin_analysis_data_deactivate',
            ),
                )
        );
        update_option('bd_is_optin', '');
    }
    exit();
}

/*
 * Add Notice about css box
 */

function bd_update_notice_hack() {
    global $wpdb;
    if (isset($_GET['blog_designer_hack']) && (int) $_GET['blog_designer_hack'] == 0) {
        update_option('blog_designer_hack', 'yes');
    }
    $blog_designer_hack = get_option('blog_designer_hack', 'no');
    if ($blog_designer_hack != 'yes') {
        ?>
        <div class="notice notice-error " id="blog_designer_hack">
            <a style="float: right; text-decoration: none; margin: 5px 10px 0px 0px;" class="blog_designer_hack-close" href="javascript:" aria-label="Dismiss this Notice">
                <span class="dashicons dashicons-dismiss"></span> Dismiss
            </a>
            <img style="margin: 20px 20px 10px 10px; float:left" src="<?php echo BLOGDESIGNER_URL; ?>images/blog-designer-vc.png" alt="" />
            <p style="font-size:16px; color: #ff0000">Thank you for updating Blog Designer Plugin to latest version. We have added security patches to our plugin. If you found any malicious code other than CSS code in 'Custom CSS' field of <a href="<?php echo admin_url('admin.php?page=designer_settings'); ?>">blog designer settings page</a>, then please remove that code from that field. </p>
        </div>

        <script>
            /*
             * Close hack notice
             */
            jQuery('.blog_designer_hack-close').click(function () {
                var data = '';
                jQuery("#blog_designer_hack").hide();
                // Save this preference
                jQuery.post('<?php echo admin_url('?blog_designer_hack=0'); ?>', data, function (response) {
                    //alert(response);
                });
            });
        </script>
        <?php
    }
}
