<?php
if (!defined('ABSPATH')) {
    exit;
}

define('NOTIFIER_XML_FILE', 'https://www.solwininfotech.com/documents/assets/solwin-free-themes-plugins.xml');
$body = get_option('solwin-free-themes-plugins');
if($body == '') {
    $response = wp_remote_post(NOTIFIER_XML_FILE);
    $body = wp_remote_retrieve_body($response);
    update_option('solwin-free-themes-plugins', $body);
}
$xml = simplexml_load_string($body);

$settings = get_option("wp_blog_designer_settings");
$template_name = (isset($settings['template_name']) && $settings['template_name'] != '') ? $settings['template_name'] : 'Classical';

$bd_version = get_option('bd_version');
?>
<div class="wrap getting-started-wrap">
    <h2 style="display: none;"></h2>
    <div class="intro">
        <div class="intro-content">
            <h3><?php esc_html_e('Getting Started', 'blog-designer'); ?></h3>
            <h4><?php esc_html_e('You will find everything you need to get started here with Blog Designer plugin.', 'blog-designer'); ?></h4>
        </div>
        <div class="intro-logo">
            <div class="intro-logo-cover">
                <img src="<?php echo BLOGDESIGNER_URL . '/images/bdp-logo.png'; ?>" alt="<?php _e('Blog Designer PRO', 'blog-designer')?>" />
                <span class="bdp-version"><?php echo __('Version', 'blog-designer') .' '. $bd_version; ?></span>
            </div>
        </div>
    </div>

    <div class="blog-designer-panel">
        <ul class="blog-designer-panel-list">
            <li class="panel-item active">
                <a data-id="bd-help-files" href="javascript:void(0)"  ><?php _e('Read This First', 'blog-designer'); ?></a>
            </li>
            <li class="panel-item">
                <a data-id="bd-free-plugins" href="javascript:void(0)"><?php _e('More Free Plugins', 'blog-designer'); ?></a>
            </li>
            <li class="panel-item">
                <a data-id="bd-free-themes" href="javascript:void(0)"><?php _e('More Free Themes', 'blog-designer'); ?></a>
            </li>
        </ul>
        <div class="blog-designer-panel-wrap">
            <div id="bd-help-files" class="bd-help-files" style="display: block;">
                <div class="bd-panel-left">
                    <div class="bd-notification">
                        <h2>
                            <?php printf( __('Success, The Blog Designer is now activated! &#x1F60A', 'blog-designer')); ?>
                        </h2>
                        <?php
                        $create_test = true;
                        $post_link = get_option('blog_page_display', 0);
                        $view_post_link = '';
                        if ($post_link == '' || $post_link == 0) {
                            $create_test = false;
                        } else {
                            $view_post_link = get_permalink($post_link);
                        }
                        ?>
                        <h4 class="do-create-test-page" <?php echo ($create_test) ? 'style="display: none;"' : ''; ?>>
                            <?php _e('Would you like to create one test blog page to check usage of Blog Designer plugin?', 'blog-designer'); ?> <br/>
                            <a class="create-test-page" href="javascript:void(0)"><?php _e('Yes, Please do it', 'blog-designer'); ?></a> | <a href="<?php echo esc_url('https://www.solwininfotech.com/documents/wordpress/blog-designer/#quick_guide'); ?>" target="_blank"> <?php _e('No, I will configure my self (Give me steps)', 'blog-designer'); ?> </a>
                            <img src="<?php echo BLOGDESIGNER_URL . '/images/ajax-loader.gif' ?>" style="display: none;"/>
                        </h4>
                        <p class="done-create-test-page" <?php echo (!$create_test) ? 'style="display: none;"' : ''; ?>>
                            <?php echo __('We have created a', 'blog-designer') .' <b>'. __('Blog Page', 'blog-designer') .'</b> '. __('with', 'blog-designer') .' <span class="template_name">"' . $template_name . '"</span> '. __('blog template.', 'blog-designer'); ?>
                            <a href="<?php echo $view_post_link; ?>" target="_blank"><?php _e('Visit blog page', 'blog-designer'); ?></a>
                        </p>
                        <p><?php echo __('To customize the Blog Page design after complete installation,', 'blog-designer') . ' <a href="admin.php?page=designer_settings">' . __('Go to Blog Designer Settings', 'blog-designer') . '</a>. ' . __('In case of an any doubt,', 'blog-designer') . ' <a href="http://solwininfotech.com/documents/wordpress/blog-designer/" target="_blank"> '. __('Read Documentation', 'blog-designer') .' </a> ' . __('or write to us via', 'blog-designer') .' <a href="http://support.solwininfotech.com/" target="_blank">'. __('support portal', 'blog-designer') .'</a> or <a href="https://wordpress.org/support/plugin/blog-designer" target="_blank">'. __('support forum', 'blog-designer') .'</a>.'?> </p>
                    </div>

                    <h3>
                        <?php _e('Getting Started', 'blog-designer'); ?> <span>(<?php _e('Must Read', 'blog-designer')?>)</span>
                    </h3>
                    <p><?php _e('Once you’ve activated your plugin, you’ll be redirected to this Getting Started page (Blog Designer > Getting Started). Here, you can view the required and helpful steps to use plugin.', 'blog-designer'); ?></p>
                    <p><?php _e('We recommed that please read the below sections for more details.', 'blog-designer'); ?></p>

                    <hr id="bd-important-things">
                    <h3>
                        <?php _e('Important things', 'blog-designer'); ?> <span>(<?php _e('Required', 'blog-designer')?>)</span> <a href="#bd-important-things">#</a>
                        <a class="back-to-top" href="#bd-help-files"><?php _e('Back to Top', 'blog-designer'); ?></a>
                    </h3>
                    <p><?php _e('To use Blog Designer, follow the below steps for initial setup - Correct the Reading Settings.', 'blog-designer'); ?></p>
                    <ul>
                        <li><?php echo __('To check the reading settings, click', 'blog-designer') . ' <b><a href="options-reading.php" target="_blank">' . __('Settings > Reading', 'blog-designer') . '</a></b> ' . __('in the WordPress admin menu.', 'blog-designer'); ?></li>
                        <li><?php echo __('If your ', 'blog-designer'). '<b>' .__('Posts page', 'blog-designer') .' </b> '. __(' selection selected with the same exact', 'blog-designer') .' <b>'.__('Blog Page', 'blog-designer').'</b> '.  __('selection that same page you seleced under Blog Designer settings then change that selection to default one (','blog-designer') .' <b>'.__('" — Select — "', 'blog-designer').'</b> '.  __(') from the dropdown.', 'blog-designer'); ?></li>
                    </ul>

                    <hr id="bd-shortcode-usage">
                    <h3>
                        <?php _e('How to use Blog Designer Shortcode?', 'blog-designer'); ?> <span>(<?php _e('Optional', 'blog-designer')?>)</span> <a href="#bd-shortcode-usage">#</a>
                        <a class="back-to-top" href="#bd-help-files"><?php _e('Back to Top', 'blog-designer'); ?></a>
                    </h3>
                    <p><?php _e('Blog Designer is flexible to be used with any page builders like Visual Composer, Elementor, Beaver Builder, SiteOrigin, Tailor, etc.', 'blog-designer'); ?></p>
                    <ul>
                        <li><?php echo __('Use shortcode', 'blog-designer') . ' <b>' . __('[wp_blog_designer]', 'blog-designer') . '</b> ' . __('in any WordPress post or page.', 'blog-designer'); ?></li>
                        <li><?php echo __('Use', 'blog-designer') . ' <b> &lt;&quest;php echo do_shortcode("[wp_blog_designer]"); &nbsp;&quest;&gt; </b>' . __('into a template file within your theme files.', 'blog-designer'); ?></li>
                    </ul>

                    <hr id="bd-dummy-posts">
                    <h3>
                        <?php _e('Import Dummy Posts', 'blog-designer'); ?> <span>(<?php _e('Optional', 'blog-designer')?>)</span> <a href="#bd-dummy-posts">#</a>
                        <a class="back-to-top" href="#bd-help-files"><?php _e('Back to Top', 'blog-designer'); ?></a>
                    </h3>
                    <p><?php _e('We have craeted a dummy set of posts for you to get started with Blog Designer.', 'blog-designer'); ?></p>
                    <p><?php _e('To import the dummy posts, follow the below process:', 'blog-designer'); ?></p>
                    <ul>
                        <li><?php echo __('Go to', 'blog-designer') . ' <b>' . __('Tools > Import', 'blog-designer') . '</b> '. __('in WordPress Admin panel.', 'blog-designer'); ?></li>
                        <li><?php echo __('Run ', 'blog-designer') . ' <b>' . __('WordPress Importer ', 'blog-designer') . '</b> '. __(' at the end of the presentated list.', 'blog-designer'); ?></li>
                        <li><?php echo __('You will be redirected on ', 'blog-designer') .' <b>' . __('Import WordPress ', 'blog-designer') . '</b> '. __(' where we need to select actual sample posts XML file.', 'blog-designer'); ?></li>
                        <li><?php echo __('Select','blog-designer') .' <b> import-sample_posts.xml </b> '. __('from','blog-designer') .' <b>'. __('blog-designer > includes > dummy-data', 'blog-designer') .'</b> '.  __('folder.', 'blog-designer'); ?></li>
                        <li><?php echo __('Click on', 'blog-designer') . ' <b>' . __('Upload file and import', 'blog-designer') . '</b> ' . __('and with next step please select','blog-designer') .' <b>'. __('Download and import file attachments', 'blog-designer') .'</b> '.  __('checkbox. Enjoy your cuppa joe with WordPress imports.', 'blog-designer'); ?></li>
                        <li><?php _e('All done! Your website is ready with sample blog posts.', 'blog-designer'); ?></li>
                    </ul>

                    <hr id="bd-plugin-support">
                    <h3>
                        <?php _e('Blog Designer Plugin Support', 'blog-designer'); ?> <a href="#bd-plugin-support">#</a>
                        <a class="back-to-top" href="#bd-help-files"><?php _e('Back to Top', 'blog-designer'); ?></a>
                    </h3>
                    <p><?php _e('Blog Designer comes with this handy help file to help you get started with setting up the plugin and showcasing blog page in beautiful ways.', 'blog-designer'); ?></p>
                    <p><?php echo __(' Please consider purchasing a', 'blog-designer') . ' <a href="' . esc_url('https://codecanyon.net/item/blog-designer-pro-for-wordpress/17069678?ref=solwin') . '" target="_blank">' . __(' PRO version', 'blog-designer') . '</a>, ' . __('which grants you access to more blog templates instead of limited templates, useful features like to design category/tag/author pages as well as single post pages, hassle-free regular updates, and a premium support for 6 months or one year based on your purchase!', 'blog-designer'); ?></p>

                </div>
                <div class="bd-panel-right">
                    <div class="panel-aside panel-club">
                        <img src="<?php echo BLOGDESIGNER_URL . '/images/bd-getting-started.jpg'; ?>" alt="<?php esc_attr_e('Blog Designer PRO', 'blog-designer'); ?>"/>
                        <div class="panel-club-inside">
                            <h4><?php _e('Get an entire collection of beautiful blog templates for one low price.', 'blog-designer'); ?></h4>
                            <p><?php _e('Blog Designer PRO for WordPress grants you access to our collection of pixel-perfect blog templates, support of multiple blog pages and premium support for 6 months — a complete value of price!', 'blog-designer'); ?></p>
                            <a class="button button-primary bdp-button" target="_blank" href="<?php echo esc_url('https://codecanyon.net/item/blog-designer-pro-for-wordpress/17069678?ref=solwin'); ?>"><?php _e('Learn about the Blog Designer PRO', 'blog-designer'); ?></a>
                        </div>
                    </div>

                    <h3><?php _e('Other Premium Plugins', 'blog-designer'); ?></h3>
                    <div class="panel-aside">
                        <a href="<?php echo esc_url('https://www.solwininfotech.com/product/wordpress-plugins/avartan-slider/'); ?>" target="_blank">
                            <img src="https://solwincdn-79e1.kxcdn.com/wp-content/uploads/2015/10/avartan-responsive-slider.png" alt="<?php _e('Avartan Slider', 'blog-designer'); ?>" />
                        </a>
                        <div class="panel-club-inside">
                            <p><?php echo '<b>' . __('Avartan Slider', 'blog-designer') . '</b> ' . __('is a responsive WordPress plugin to create stunning image slider and video slider for your WordPress website. It has unique features like drag and drop visual slider builder, multi-media content, etc.', 'blog-designer'); ?></p>
                            <a href="<?php echo esc_url('https://www.solwininfotech.com/product/wordpress-plugins/avartan-slider/'); ?>" target="_blank"><?php _e('Read More', 'blog-designer'); ?></a>
                        </div>
                    </div>
                    <div class="panel-aside">
                        <a href="<?php echo esc_url('https://www.solwininfotech.com/product/wordpress-plugins/portfolio-designer/'); ?>" target="_blank">
                            <img src="https://solwincdn-79e1.kxcdn.com/wp-content/uploads/2017/02/Portfolio-Designer-WordPress-Plugin.png" alt="<?php _e('Portfolio Designer', 'blog-designer'); ?>" />
                        </a>
                        <div class="panel-club-inside">
                            <p><?php echo '<b>' . __('Portfolio Designer', 'blog-designer') . '</b> ' . __('is a WordPress plugin used to build portfolio in any desired layout. This plugin is user friendly, So no matter if you are a beginner, WordPress user, Designer or a Developer, no additional coding knowledge is required in creating portfolio layouts.', 'blog-designer'); ?></p>
                            <a href="<?php echo esc_url('https://www.solwininfotech.com/product/wordpress-plugins/portfolio-designer/'); ?>" target="_blank"><?php _e('Read More', 'blog-designer'); ?></a>
                        </div>
                    </div>

                    <h3><?php _e('Other Premium Themes', 'blog-designer'); ?></h3>
                    <div class="panel-aside">
                        <a href="<?php echo esc_url('https://www.solwininfotech.com/product/wordpress-themes/kosmic/'); ?>" target="_blank">
                            <img src="https://solwincdn-79e1.kxcdn.com/wp-content/uploads/2016/07/Kosmic-Multipurpose-Responsive-WordPress-Theme.png" alt="<?php _e('Kosmic', 'blog-designer'); ?>" />
                        </a>
                        <div class="panel-club-inside">
                            <p><?php echo '<b>' . __('Kosmic', 'blog-designer') . '</b> ' . __('is a multipurpose WordPress theme which is suitable for almost all types of websites. This eCommerce theme is very simple, clean and professional. It comes with an extensive theme options panel to customize your site easily as per your requirements.', 'blog-designer'); ?></p>
                            <a href="<?php echo esc_url('https://www.solwininfotech.com/product/wordpress-themes/kosmic/'); ?>" target="_blank"><?php _e('Read More', 'blog-designer'); ?></a>
                        </div>
                    </div>
                    <div class="panel-aside">
                        <a href="<?php echo esc_url('https://www.solwininfotech.com/product/wordpress-themes/foodfork/'); ?>" target="_blank">
                            <img src="https://solwincdn-79e1.kxcdn.com/wp-content/uploads/2016/06/FoodFork-Restaturant-WordPress-Theme.jpg" alt="<?php _e('FoodFork', 'blog-designer'); ?>" />
                        </a>
                        <div class="panel-club-inside">
                            <p><?php echo '<b>' . __('FoodFork', 'blog-designer') . '</b> ' . __('is a premium WordPress theme for Restaurants and food business websites. You can use this theme for your business websites like restaurant, cafe, coffee shop, fast food or pizza store.', 'blog-designer'); ?></p>
                            <a href="<?php echo esc_url('https://www.solwininfotech.com/product/wordpress-themes/foodfork/'); ?>" target="_blank"><?php _e('Read More', 'blog-designer'); ?></a>
                        </div>
                    </div>
                    <div class="panel-aside">
                        <a href="<?php echo esc_url('https://www.solwininfotech.com/product/wordpress-themes/jewelux/'); ?>" target="_blank">
                            <img src="https://solwincdn-79e1.kxcdn.com/wp-content/uploads/2016/02/JewelUX-WordPress-Premium-Theme.jpg" alt="<?php _e('JewelUX', 'blog-designer'); ?>" />
                        </a>
                        <div class="panel-club-inside">
                            <p><?php echo '<b>' . __('JewelUX', 'blog-designer') . '</b> ' . __('is a clean and modern jewelry WordPress theme designed for any online jewelry website. It’s a WooCommerce theme with responsive layout, fully widgetized and animated home page.', 'blog-designer'); ?></p>
                            <a href="<?php echo esc_url('https://www.solwininfotech.com/product/wordpress-themes/jewelux/'); ?>" target="_blank"><?php _e('Read More', 'blog-designer'); ?></a>
                        </div>
                    </div>
                </div>
            </div>

            <div id="bd-free-plugins" class="bd-free-plugins">
                <div class="bd-intro">
                    <p>
                        <?php echo __('Enhance your website with one the best free WordPress plugins by', 'blog-designer') . ' <b>' . __('Solwin Infotech!', 'blog-designer') . '</b>' ?>
                    </p>
                    <a class="button button-primary bd-intro-btn" href="<?php echo esc_url('https://www.solwininfotech.com/products/wordpress-plugins/'); ?>" target="_blank"><?php _e('Check out our Free and Premium plugins', 'blog-designer'); ?></a>
                </div>
                <div>
                    <?php
                    if ($xml->plugins) {
                        foreach ($xml->plugins as $single_plugin) {
                            unset($single_plugin->blog_designer);
                            foreach ($single_plugin as $value) {
                                ?>
                                <div class="image_div_upper">
                                    <a href="<?php echo $value->link; ?>" target="_blank">
                                        <img src="<?php echo $value->img; ?>" alt="<?php echo $value->name; ?>">
                                    </a>
                                    <a href="<?php echo $value->link; ?>" target="_blank">
                                        <div class="bd_theme_plugin_name"><span><?php echo $value->name; ?></span></div>
                                    </a>
                                    <div class="bd_theme_plugin_desc"><span><?php echo $value->desc; ?></span></div>
                                </div>
                                <?php
                            }
                        }
                    }
                    ?>
                </div>
            </div>

            <div id="bd-free-themes" class="bd-free-themes">
                <div class="bd-intro">
                    <p>
                        <?php echo __('Build a professional looking website with one of the best free WordPress themes by', 'blog-designer') . ' <b>' . __('Solwin Infotech!', 'blog-designer') . '</b>' ?>
                    </p>
                    <a class="button button-primary bd-intro-btn" href="<?php echo esc_url('https://www.solwininfotech.com/products/wordpress-themes/'); ?>" target="_blank"><?php _e('Check out our Free and Premium themes', 'blog-designer'); ?></a>
                </div>
                <div>
                    <?php
                    if ($xml->themes) {
                        foreach ($xml->themes as $single_theme) {
                            foreach ($single_theme as $value) {
                                ?>
                                <div class="image_div_upper">
                                    <a href="<?php echo $value->link; ?>" target="_blank">
                                        <img src="<?php echo $value->img; ?>" alt="<?php echo $value->name; ?>">
                                    </a>
                                    <a href="<?php echo $value->link; ?>" target="_blank">
                                        <div class="bd_theme_plugin_name"><span><?php echo $value->name; ?></span></div>
                                    </a>
                                    <div class="bd_theme_plugin_desc"><span><?php echo $value->desc; ?></span></div>
                                </div>
                                <?php
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>