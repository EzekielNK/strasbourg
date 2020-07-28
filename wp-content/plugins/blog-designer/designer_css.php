<?php
$settings                       = get_option( 'wp_blog_designer_settings' );
$background                     = ( isset( $settings['template_bgcolor'] ) && $settings['template_bgcolor'] != '' ) ? $settings['template_bgcolor'] : '';
$templatecolor                  = ( isset( $settings['template_color'] ) && $settings['template_color'] != '' ) ? $settings['template_color'] : '';
$grid_hoverback_color                  = ( isset( $settings['grid_hoverback_color'] ) && $settings['grid_hoverback_color'] != '' ) ? $settings['grid_hoverback_color'] : '';
$color                          = ( isset( $settings['template_ftcolor'] ) && $settings['template_ftcolor'] != '' ) ? $settings['template_ftcolor'] : '';
$linkhover                      = ( isset( $settings['template_fthovercolor'] ) && $settings['template_fthovercolor'] != '' ) ? $settings['template_fthovercolor'] : '';
$titlecolor                     = ( isset( $settings['template_titlecolor'] ) && $settings['template_titlecolor'] != '' ) ? $settings['template_titlecolor'] : '';
$contentcolor                   = ( isset( $settings['template_contentcolor'] ) && $settings['template_contentcolor'] != '' ) ? $settings['template_contentcolor'] : '';
$readmorecolor                  = ( isset( $settings['template_readmorecolor'] ) && $settings['template_readmorecolor'] != '' ) ? $settings['template_readmorecolor'] : '';
$readmorebackcolor              = ( isset( $settings['template_readmorebackcolor'] ) && $settings['template_readmorebackcolor'] != '' ) ? $settings['template_readmorebackcolor'] : '';
$alterbackground                = ( isset( $settings['template_alterbgcolor'] ) && $settings['template_alterbgcolor'] != '' ) ? $settings['template_alterbgcolor'] : '';
$titlebackcolor                 = ( isset( $settings['template_titlebackcolor'] ) && $settings['template_titlebackcolor'] != '' ) ? $settings['template_titlebackcolor'] : '';
$social_icon_style              = get_option( 'social_icon_style' );
$template_alternativebackground = get_option( 'template_alternativebackground' );
$template_titlefontsize         = get_option( 'template_titlefontsize' );
$content_fontsize               = get_option( 'content_fontsize' );
$custom_css                     = get_option( 'custom_css' );
?>

<style type="text/css">

	/**
	 * Table of Contents
	 *
	 * 1.0 - Pagination
	 * 2.0 - Social Media Icon
	 * 3.0 - Default Blog Template
	 * 4.0 - Classical Template
	 * 5.0 - Light Breeze Template
	 * 6.0 - Spektrum Template
	 * 7.0 - Evolution Template
	 * 8.0 - Timeline Template
	 * 9.0 - News Template
	 *
	 */

	/**
	 * 1.0 - Pagination
	 */

	.bd_pagination_box.wl_pagination_box .paging-navigation ul.page-numbers li a.page-numbers:hover,
	.bd_pagination_box.wl_pagination_box .paging-navigation ul.page-numbers li > span.current {
		<?php echo ( $readmorebackcolor != '' ) ? 'background-color: ' . $readmorebackcolor . ';' : ''; ?>
		<?php echo ( $readmorecolor != '' ) ? 'color: ' . $readmorecolor . ';' : ''; ?>
		<?php echo ( $content_fontsize != '' ) ? 'font-size:' . $content_fontsize . 'px;' : ''; ?>
	}

	.bd_pagination_box.wl_pagination_box .paging-navigation ul.page-numbers li a.page-numbers {
		<?php echo ( $readmorecolor != '' ) ? 'background-color: ' . $readmorecolor . ';' : ''; ?>
		<?php echo ( $readmorebackcolor != '' ) ? 'color: ' . $readmorebackcolor . ';' : ''; ?>
		<?php echo ( $content_fontsize != '' ) ? 'font-size:' . $content_fontsize . 'px;' : ''; ?>
	}
	.bd_pagination_box.wl_pagination_box .paging-navigation ul.page-numbers li a.page-numbers.dots {
		<?php echo ( $content_fontsize != '' ) ? 'font-size:' . $content_fontsize . 'px !important;' : ''; ?>
	}
	/**
	 * 2.0 - Social Media Icon
	 */    

	.bdp_blog_template .social-component a {
		<?php
		if ( $social_icon_style == 0 ) {
			echo 'border-radius: 100%;';
		}
		?>
	}

	/**
	 * 3.0 - Default Blog Template
	 */    

	.bdp_blog_template .bd-blog-header h2 {        
		<?php
		if ( $titlebackcolor != '' ) {
			echo 'background: ' . $titlebackcolor;
		}
		?>
	}
	.blog_template .bd-more-tag-inline {
		<?php echo ( $readmorecolor != '' ) ? 'color: ' . $readmorecolor . ' !important;' : ''; ?>
		<?php echo ( $content_fontsize != '' ) ? 'font-size:' . $content_fontsize . 'px;' : ''; ?>
	}

	<?php if ( $titlecolor != '' || $template_titlefontsize != '' ) { ?>
		.bdp_blog_template .blog_header h2,
		.bdp_blog_template .blog_header h2 a,
		.bdp_blog_template .bd-blog-header h2,
		.bdp_blog_template .bd-blog-header h2 a {
			<?php echo ( $titlecolor != '' ) ? 'color: ' . $titlecolor . ' !important;' : ''; ?>
			<?php echo ( $template_titlefontsize != '' ) ? 'font-size: ' . $template_titlefontsize . 'px;' : ''; ?>
		}
	<?php } ?>

	.bd-post-content {
		<?php echo ( $contentcolor != '' ) ? 'color:' . $contentcolor . ';' : ''; ?>
		<?php echo ( $content_fontsize != '' ) ? 'font-size:' . $content_fontsize . 'px;' : ''; ?>
	}
	.bdp_blog_template .bd-metacats,
	.bd-tags,
	span.bd-category-link,
	.bdp_blog_template .author,
	.bdp_blog_template .date,
	.bdp_blog_template .bd-categories,
	.bdp_blog_template.evolution .bd-categories a,
	.bdp_blog_template .bd-categories a,
	.bd-meta-data-box .bd-metacats,
	.bd-meta-data-box .bd-metacomments,
	.bd-meta-data-box .bd-metacomments span,
	.bdp_blog_template .date i, .bdp_blog_template .author i, .bdp_blog_template .comment i,
	.bd-tags a,
	span.bd-category-link a,
	.bd-metadatabox p,
	span .bd-link-label,
	.bdp_blog_template .blog_footer span,
	.bd-metacomments i,
	.date_wrap i,
	.bdp_blog_template a,
	.bdp_blog_template .post_content,
	.bd-categories i,
	.bdp_blog_template.spektrum .post-bottom .post-by,
	.bdp_blog_template.spektrum .post-bottom .bd-tags,
	.bd-metadatabox,
	.bdp_blog_template.news .bd-blog-header .bd-metadatabox a,
	.bdp_blog_template.news .post-content-div .post_cat_tag > span i,
	.bdp_blog_template .bd-meta-data-box .bd-metadate, .bdp_blog_template .bd-meta-data-box .bd-metauser, .bdp_blog_template .bd-meta-data-box .bd-metacats,
	.bd-meta-data-box .bd-metacats a,
	.bd-meta-data-box .bd-metacomments a,
	.bdp_blog_template.box-template .bd-tags,
	.bdp_blog_template .post-date {
		<?php echo ( $content_fontsize != '' ) ? 'font-size:' . $content_fontsize . 'px;' : ''; ?>
	}
	.bdp_blog_template .date,
	.bdp_blog_template .comment,
	.bdp_blog_template .author .bd-icon-author,
	.bdp_blog_template .author,
	.bdp_blog_template.news .post-content-div .post_cat_tag > span i ,
	.bdp_blog_template .post-by,
	.bdp_blog_template .bd-categories i,
	.bdp_blog_template .bd-metacats i,
	.bdp_blog_template .bd-tags i,
	.bdp_blog_template .bd-metacomments,
	.bdp_blog_template .date_wrap i,
	.bdp_blog_template .post-date,
	.bdp_blog_template .tags i {
		<?php echo ( $contentcolor != '' ) ? 'color:' . $contentcolor . ';' : ''; ?>
	}
	.bdp_blog_template .bd-meta-data-box {
		<?php echo ( $contentcolor != '' ) ? 'color:' . $contentcolor . ';' : ''; ?>
	}

	.bdp_blog_template .bd-meta-data-box i {
		<?php echo ( $titlecolor != '' ) ? 'color: ' . $titlecolor . ';' : ''; ?>
	}

	<?php if ( $contentcolor != '' ) { ?>
		.bd-metadatabox {
			<?php echo ( $contentcolor != '' ) ? 'color:' . $contentcolor . ';' : ''; ?>
		}
	<?php } ?>
	.bd-link-label {
		<?php echo ( $contentcolor != '' ) ? 'color:' . $contentcolor . ';' : ''; ?>
	}

	.bdp_blog_template a.bd-more-tag {
		<?php echo ( $readmorebackcolor != '' ) ? 'background-color: ' . $readmorebackcolor . '!important;;' : ''; ?>
		<?php echo ( $readmorecolor != '' ) ? 'color: ' . $readmorecolor . '!important;;' : ''; ?>
	}

	<?php if ( $readmorebackcolor != '' || $readmorecolor != '' ) { ?>
		.bdp_blog_template a.bd-more-tag:hover {
			<?php echo ( $readmorecolor != '' ) ? 'background-color: ' . $readmorecolor . '!important;;' : ''; ?>
			<?php echo ( $readmorebackcolor != '' ) ? 'color: ' . $readmorebackcolor . '!important;;' : ''; ?>
		}
	<?php } ?>

	.bdp_blog_template i {
		font-style: normal !important;
	}
	<?php if ( $color != '' ) { ?>
		.bd-tags,
		span.bd-category-link,
		.bdp_blog_template .bd-categories,
		.bd-meta-data-box .bd-metacats,
		.bd-meta-data-box .bd-metacats a,
		.bd-meta-data-box .bd-metacomments a,
		.bdp_blog_template .bd-categories a,
		.bd-post-content a,
		.bd-tags a,
		span.bd-category-link a,
		.bdp_blog_template a {
			color:<?php echo $color; ?> !important;
			font-weight: normal !important;
		}
	<?php } ?>

	<?php if ( $linkhover != '' ) { ?>

		.bd-meta-data-box .bd-metacats a:hover,
		.bd-meta-data-box .bd-metacomments a:hover,
		.bdp_blog_template .bd-categories a:hover,
		.spektrum .post-bottom .bd-categories a:hover,
		.bd-post-content a:hover,
		.bd-tags a:hover,
		span.bd-category-link a:hover,
		.bdp_blog_template a:hover,
		.bd-post-content a:hover {
			color:<?php echo $linkhover; ?> !important;
		}
	<?php } ?>

	<?php if ( $background != '' ) { ?>
		.bdp_blog_template.evolution,
		.bdp_blog_template.lightbreeze {
			background: <?php echo $background; ?>;
		}
	<?php } ?>

	<?php
	if ( $template_alternativebackground == '0' ) {
		if ( $alterbackground != '' ) {
			?>
			.bdp_blog_template.evolution.alternative-back,
			.bdp_blog_template.lightbreeze.alternative-back {
				background: <?php echo $alterbackground; ?>;
			}
		<?php } else { ?>
			.bdp_blog_template.evolution.alternative-back,
			.bdp_blog_template.lightbreeze.alternative-back {
				background: transparent;
			}
			<?php
		}
	}
	?>

	/**
	 * 4.0 - Classical Template
	 */

	.bdp_blog_template.classical .bd-blog-header .bd-tags {
		<?php echo ( $color != '' ) ? 'color: ' . $color . ';' : ''; ?>
	}

	/**
	 * 5.0 - Light Breeze Template
	 */

	/**
	 * 6.0 - Spektrum Template
	 */

	.bdp_blog_template.spektrum .bd-blog-header {
		<?php echo ( $titlebackcolor != '' ) ? 'background:' . $titlebackcolor . ';' : ''; ?>
	}

	<?php if ( $titlecolor != '' ) { ?>
		.spektrum .date {
			background-color: <?php echo $titlecolor; ?>;
		}
		<?php
	}
	if ( $color != '' ) {
		?>
		.spektrum .post-bottom .bd-categories a {
			color: <?php echo $color; ?>;
		}
		<?php
	}
	if ( $readmorecolor != '' ) {
		?>
		.spektrum .details a {
			color :<?php echo $readmorecolor; ?> !important;
			<?php echo ( $content_fontsize != '' ) ? 'font-size:' . $content_fontsize . 'px;' : ''; ?>
		}
		<?php
	}
	if ( $readmorebackcolor != '' ) {
		?>
		.spektrum .details a:hover {
			color :<?php echo $readmorebackcolor; ?>;
		}
	<?php } ?>

	/**
	 * 7.0 - Evolution Template
	 */


	/**
	 * 8.0 - Timeline Template
	 */

	.timeline_bg_wrap:before {
		background: none repeat scroll 0 0 <?php echo $templatecolor; ?>;
	}

	.bd-datetime {
		background: none repeat scroll 0 0 <?php echo $templatecolor; ?>;
	}

	.bdp_blog_template.timeline .post_hentry > p > i {
		<?php echo ( $templatecolor != '' ) ? 'background: ' . $templatecolor . ';' : ''; ?>
	}

	.bdp_blog_template.timeline:nth-child(2n+1) .post_content_wrap:before,
	.bdp_blog_template.timeline:nth-child(2n+1) .post_content_wrap:after {
		border-left: 8px solid <?php echo $templatecolor; ?>;
	}

	.rtl .bdp_blog_template.timeline:nth-child(2n+1) .post_content_wrap:before,
	.rtl .bdp_blog_template.timeline:nth-child(2n+1) .post_content_wrap:after {
		border-right: 8px solid <?php echo $templatecolor; ?>;
	}

	.bdp_blog_template.timeline:nth-child(2n) .post_content_wrap:before,
	.bdp_blog_template.timeline:nth-child(2n) .post_content_wrap:after {
		border-right: 8px solid <?php echo $templatecolor; ?>;
	}

	.rtl .bdp_blog_template.timeline:nth-child(2n) .post_content_wrap:before,
	.rtl .bdp_blog_template.timeline:nth-child(2n) .post_content_wrap:after {
		border-left: 8px solid <?php echo $templatecolor; ?>;
	}

	.post_content_wrap {
		border:1px solid <?php echo $templatecolor; ?>;
	}

	.bdp_blog_template .post_content_wrap .blog_footer {
		border-top: 1px solid <?php echo $templatecolor; ?> ;
	}

	.bdp_blog_template .post-icon {
		<?php echo ( $titlebackcolor != '' ) ? 'background:' . $titlebackcolor . ';' : ''; ?>
	}

	.bdp_blog_template.timeline .desc h3 a{
		<?php echo ( $titlecolor != '' ) ? 'color: ' . $titlecolor . ' !important;' : ''; ?>
		<?php echo ( $titlebackcolor != '' ) ? 'background:' . $titlebackcolor . ' !important;' : ''; ?>
		<?php echo ( $template_titlefontsize != '' ) ? 'font-size: ' . $template_titlefontsize . 'px;' : ''; ?>
	}

	/**
	 * 9.0 - News Template
	 */

	<?php if ( $titlecolor != '' || $template_titlefontsize != '' ) { ?>
		.bdp_blog_template.news .bd-blog-header h2.title a{
			<?php echo ( $titlecolor != '' ) ? 'color: ' . $titlecolor . ';' : ''; ?>
			<?php echo ( $template_titlefontsize != '' ) ? 'font-size: ' . $template_titlefontsize . 'px;' : ''; ?>
		}
	<?php } ?>

	.bdp_blog_template.news .bd-blog-header h2.title{
		<?php
		if ( $titlebackcolor != '' ) {
			echo 'background: ' . $titlebackcolor;
		}
		?>
	}
	.bdp_blog_template.news a.bd-more-tag{
		float: right !important;
	}
	.blog_template.boxy-clean ul li:hover,
	.blog_template.boxy-clean ul li:hover .blog_footer,
	.blog_template.boxy-clean ul li:hover .blog_div,
	.blog_template.boxy-clean ul li:hover .blog_header h2 {
		background: <?php echo $grid_hoverback_color; ?>;
	}
	<?php echo wp_unslash( $custom_css ); ?>

</style>
