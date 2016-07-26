<?php

// =============================================================================
// FUNCTIONS.PHP
// -----------------------------------------------------------------------------
// Overwrite or add your own custom functions to X in this file.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. Enqueue Parent Stylesheet
//   02. Additional Functions
// =============================================================================

// Enqueue Parent Stylesheet
// =============================================================================

add_filter( 'x_enqueue_parent_stylesheet', '__return_true' );



// Additional Functions
// =============================================================================

add_filter( 'gform_enable_field_label_visibility_settings', '__return_true' );

// Do Not Remove Woocommerce Plugin Settings
// =============================================================================
function x_woocommerce_donot_remove_plugin_setting(){
  if ( ! is_admin() ) {
    return;
  }
  remove_filter( 'woocommerce_product_settings', 'x_woocommerce_remove_plugin_settings', 10 );
}
add_action('init', 'x_woocommerce_donot_remove_plugin_setting');

// Add classes to main categories
function wc_product_cat_class_mainCat( $class = '', $category = null ) {
	// Count Categories
	$args = array(
		'taxonomy'		=> 'product_cat',
	    'parent'        => 0,
	    'number'        => 10,
	    'hide_empty'    => false
	);

	$categories = get_terms( $args );
	$main_cat_number = count( $categories );
	$cat_column_number = floor(100 / $main_cat_number);
	
	// Separates classes with a single space, collates classes for post DIV
	echo 'class="' . 'cat-menu-col-' . $cat_column_number . ' ' . esc_attr( join( ' ', wc_get_product_cat_class( $class, $category ) ) ) . '"';
}

// Add classes to sub categories
function wc_product_cat_class_subCat( $class = '', $category = null ) {

	// Find the category + category parent, if applicable
	$term 			= get_queried_object();
	$parent_id 		= empty( $term->term_id ) ? 0 : $term->term_id;

	// Count Categories
	$args = array(
		'taxonomy'		=> 'product_cat',
	    'parent'        => $parent_id,
	    'number'        => 10,
	    'hide_empty'    => false
	);

	$categories = get_terms( $args );
	$main_cat_number = count( $categories );
	$cat_column_number = floor(100 / $main_cat_number);
	
	// Separates classes with a single space, collates classes for post DIV
	echo 'class="' . 'cat-menu-col-' . $cat_column_number . ' ' . esc_attr( join( ' ', wc_get_product_cat_class( $class, $category ) ) ) . '"';
}

// Edit woocommerce content
if ( ! function_exists( 'woocommerce_content_new' ) ) {

	/**
	 * Output WooCommerce content.
	 *
	 * This function is only used in the optional 'woocommerce.php' template.
	 * which people can add to their themes to add basic woocommerce support.
	 * without hooks or modifying core templates.
	 *
	 */
	function woocommerce_content_new() {

		if ( is_singular( 'product' ) ) {

			while ( have_posts() ) : the_post();

				wc_get_template_part( 'content', 'single-product' );

			endwhile;

		} else { ?>
			<?php if (is_shop()) { ?>

				<div class="x-section bg-image" style="margin: 0px;padding: 350px 0px 50px; background-image: url(/wp-content/uploads/2016/07/Bigstock_93179717.jpg); background-color: transparent;">
					<div class="x-container max width" style="margin: 0px auto;padding: 0px;">
						<div class="x-column x-sm x-1-1" style="padding: 0px;">
							<div class="x-text cs-ta-right">
								<h1 class="page-title"><?php woocommerce_page_title(); ?></h1>
							</div>
						</div>
					</div>
				</div>

			<?php	
			}

			?>

			<?php if ( have_posts() ) : ?>

				<?php do_action('woocommerce_before_shop_loop'); ?>

				<?php woocommerce_product_loop_start(); ?>

					<?php woocommerce_product_subcategories_new(); ?>

					<?php while ( have_posts() ) : the_post(); ?>

						<?php wc_get_template_part( 'content', 'product' ); ?>

					<?php endwhile; // end of the loop. ?>

				<?php woocommerce_product_loop_end(); ?>
				
				<hr class="x-clear">
				<?php do_action('woocommerce_after_shop_loop'); ?>

			<?php elseif ( ! woocommerce_product_subcategories_new( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

				<?php wc_get_template( 'loop/no-products-found.php' ); ?>

			<?php endif; ?>
			<div class="x-container max width"
				<?php do_action( 'woocommerce_archive_description' );?>
			</div>
			<?php
		}
	}
}

// Edit woocommerce_product_subcategories_new
if ( ! function_exists( 'woocommerce_product_subcategories_new' ) ) {

	/**
	 * Display product sub categories as thumbnails.
	 *
	 * @subpackage	Loop
	 * @param array $args
	 * @return null|boolean
	 */
	function woocommerce_product_subcategories_new( $args = array() ) {
		global $wp_query;

		$defaults = array(
			'before'        => '',
			'after'         => '',
			'force_display' => false
		);

		$args = wp_parse_args( $args, $defaults );

		extract( $args );

		// Main query only
		if ( ! is_main_query() && ! $force_display ) {
			return;
		}

		// Don't show when filtering, searching or when on page > 1 and ensure we're on a product archive
		if ( is_search() || is_filtered() || is_paged() || ( ! is_product_category() && ! is_shop() ) ) {
			return;
		}

		// Check categories are enabled
		if ( is_shop() && '' === get_option( 'woocommerce_shop_page_display' ) ) {
			return;
		}

		// Find the category + category parent, if applicable
		$term 			= get_queried_object();
		$parent_id 		= empty( $term->term_id ) ? 0 : $term->term_id;

		if ( is_product_category() ) {
			$display_type = get_woocommerce_term_meta( $term->term_id, 'display_type', true );

			switch ( $display_type ) {
				case 'products' :
					return;
				break;
				case '' :
					if ( '' === get_option( 'woocommerce_category_archive_display' ) ) {
						return;
					}
				break;
			}
		}

		// NOTE: using child_of instead of parent - this is not ideal but due to a WP bug ( https://core.trac.wordpress.org/ticket/15626 ) pad_counts won't work
		$product_categories = get_categories( apply_filters( 'woocommerce_product_subcategories_new_args', array(
			'parent'       => $parent_id,
			'menu_order'   => 'ASC',
			'hide_empty'   => 0,
			'hierarchical' => 1,
			'taxonomy'     => 'product_cat',
			'pad_counts'   => 1
		) ) );

		if ( ! apply_filters( 'woocommerce_product_subcategories_new_hide_empty', false ) ) {
			$product_categories = wp_list_filter( $product_categories, array( 'count' => 0 ), 'NOT' );
		}

		if ( $product_categories ) {
			echo $before;

			if ($parent_id == 0) {
				foreach ( $product_categories as $category ) {
					wc_get_template( 'content-product_cat.php', array(
						'category' => $category
					) );
				}
			}else {
				foreach ( $product_categories as $category ) {
					wc_get_template( 'content-product_cat2.php', array(
						'category' => $category
					) );
				}
			}

			// If we are hiding products disable the loop and pagination
			if ( is_product_category() ) {
				$display_type = get_woocommerce_term_meta( $term->term_id, 'display_type', true );

				switch ( $display_type ) {
					case 'subcategories' :
						$wp_query->post_count    = 0;
						$wp_query->max_num_pages = 0;
					break;
					case '' :
						if ( 'subcategories' === get_option( 'woocommerce_category_archive_display' ) ) {
							$wp_query->post_count    = 0;
							$wp_query->max_num_pages = 0;
						}
					break;
				}
			}

			if ( is_shop() && 'subcategories' === get_option( 'woocommerce_shop_page_display' ) ) {
				$wp_query->post_count    = 0;
				$wp_query->max_num_pages = 0;
			}

			echo $after;

			return true;
		}
	}
}

// Add category image
add_action( 'woocommerce_before_shop_loop', 'woocommerce_category_image', 2 );
function woocommerce_category_image() {
    if ( is_product_category() ){
	    global $wp_query;
	    $cat = $wp_query->get_queried_object();
	    $thumbnail_id = get_woocommerce_term_meta( $cat->term_id, 'thumbnail_id', true );
	    $image = wp_get_attachment_url( $thumbnail_id );
	    if ( $image ) { ?>

	    	<div class="x-section bg-image" style="margin: 0px;padding: 350px 0px 50px; background-image: url(<?php echo $image ?>); background-color: transparent;">
	    		<div class="x-container max width" style="margin: 0px auto;padding: 0px;">
	    			<div class="x-column x-sm x-1-1" style="padding: 0px;">
	    				<div class="x-text cs-ta-right">
	    					<h1 class="page-title"><?php woocommerce_page_title(); ?></h1>
	    				</div>
	    			</div>
	    		</div>
	    	</div>

	    	<?php
		}
	}
}