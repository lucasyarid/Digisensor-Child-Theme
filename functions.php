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
	    'hide_empty'    => 1
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
	$term 			 = get_queried_object();
	$parent_id 		 = empty( $term->term_id ) ? 0 : $term->term_id;
	$grand_parent_id = $term->parent;

	// Count Categories
	$args = array(
		'taxonomy'		=> 'product_cat',
	    'parent'        => $parent_id,
	    'number'        => 10,
	    'hide_empty'    => 1
	);

	$categories = get_terms( $args );
	$main_cat_number = count( $categories );

	// Check if is last child
	if ( $main_cat_number == 0 ) {
		$args = array(
			'taxonomy'		=> 'product_cat',
		    'parent'        => $grand_parent_id,
		    'number'        => 10,
		    'hide_empty'    => 1
		);
		$categories = get_terms( $args );
		$main_cat_number = count( $categories );
		$cat_column_number = floor(100 / $main_cat_number);
		
		// Separates classes with a single space, collates classes for post DIV
		echo 'class="' . 'cat-menu-col-' . $cat_column_number . ' ' . esc_attr( join( ' ', wc_get_product_cat_class( $class, $category ) ) ) . '"';
	} else {
		$cat_column_number = floor(100 / $main_cat_number);
		
		// Separates classes with a single space, collates classes for post DIV
		echo 'class="' . 'cat-menu-col-' . $cat_column_number . ' ' . esc_attr( join( ' ', wc_get_product_cat_class( $class, $category ) ) ) . '"';
	}
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

				<div class="x-section bg-image" style="margin: 0px;padding: 50px 0px 350px; background-image: url(/wp-content/uploads/2016/07/Bigstock_93179717.jpg); background-color: transparent;">
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
				<div class="nav-categories">
					<?php woocommerce_product_subcategories_new(); ?>
				</div>
				
				<hr class="x-clear">
				<div class="x-container max width">

			<?php elseif ( ! woocommerce_product_subcategories_new( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

				<?php wc_get_template( 'loop/no-products-found.php' ); ?>

			<?php endif; ?>
				<?php do_action( 'woocommerce_archive_description' );?>
			<?php woocommerce_product_loop_start(); ?>

				<?php while ( have_posts() ) : the_post(); ?>
						<?php wc_get_template_part( 'content', 'product' ); ?>
				<?php endwhile; // end of the loop. ?>
			</div>
			<?php woocommerce_product_loop_end(); ?>
			<?php do_action('woocommerce_after_shop_loop'); ?>

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
			'hide_empty'   => 1,
			'hierarchical' => 1,
			'taxonomy'     => 'product_cat',
			'pad_counts'   => 1
		) ) );

		if ( empty($product_categories) ) {
			$category = get_queried_object();
			$current = $category->term_id;
			$parent = $term->parent;
			
			$product_categories = get_categories( apply_filters( 'woocommerce_product_subcategories_new_args', array(
				'parent'       => $parent,
				'menu_order'   => 'ASC',
				'hide_empty'   => 1,
				'hierarchical' => 1,
				'taxonomy'     => 'product_cat',
				'pad_counts'   => 1
			) ) );
		}

		if ( ! apply_filters( 'woocommerce_product_subcategories_new_hide_empty', false ) ) {
			$product_categories = wp_list_filter( $product_categories, array( 'count' => 0 ), 'NOT' );
		}

		if ( $product_categories ) {
			echo $before;

			if ($parent_id == 0) {
				foreach ( $product_categories as $category ) {
					wc_get_template( 'content-product_cat_main.php', array(
						'category' => $category
					) );
				}
			}else {
				foreach ( $product_categories as $category ) {
					wc_get_template( 'content-product_cat_sub.php', array(
						'category' => $category
					) );
				}
			}

			// If we are hiding products disable the loop and pagination
			if ( is_product_category() ) {
				$display_type = get_woocommerce_term_meta( $term->term_id, 'display_type', true );
				update_option( 'woocommerce_category_archive_display', 'both' );
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
	    ?>

	    <div class="x-section bg-image" style="margin: 0px;padding: 50px 0px 350px; background-image: url(<?php echo $image ?>); background-color: transparent;">
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

// Remove orderby
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
function woocommerce_result_count() {
        return;
}

if (  ! function_exists( 'woocommerce_template_loop_category_title_new' ) ) {

	/**
	 * Show the subcategory title in the product loop.
	 */
	function woocommerce_template_loop_category_title_new( $category ) {
		?>
		<h3>
			<?php
				echo $category->name;

				if ( $category->count > 0 )
					echo apply_filters( 'woocommerce_subcategory_count_html', '', $category );
			?>
		</h3>
		<?php
	}
}
remove_action( 'woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title', 10 );
add_action( 'woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title_new', 10 );

// Configure products
function x_woocommerce_before_shop_loop_item_title_new() {
  echo '<div class="entry-wrap"><header class="entry-header">';
}

remove_action( 'woocommerce_shop_loop_item_title', 'x_woocommerce_before_shop_loop_item_title', 99 );
add_action( 'woocommerce_shop_loop_item_title', 'x_woocommerce_before_shop_loop_item_title_new', 10 );

function x_woocommerce_after_shop_loop_item_title_new() {
  echo '</header></div>';
}

remove_action( 'woocommerce_shop_loop_item_title', 'x_woocommerce_after_shop_loop_item_title', 99 );
add_action( 'woocommerce_shop_loop_item_title', 'x_woocommerce_after_shop_loop_item_title_new', 10 );

// Remove pagination
add_filter( 'loop_shop_per_page', create_function( '$cols', 'return 90;' ), 20 );

// Add product content
add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_product_content', 9);
add_action('the_content','woo_content_div');

function woo_content_div( $content ){
	return '<div class="product-content">'.$content.'</div>';
};

if (!function_exists('woocommerce_product_content')) {
	function woocommerce_product_content() {
		echo the_content();
	}
};

function woocommerce_img_pdf() {
	$productImg = get_the_post_thumbnail($post_id, 'full');
	$file = get_field('pdf_file');
	if ($file) {
		$productFile = '<a target="_blank" class="productPdf" href="' . $file['url'] .'">'. $file['filename'].'</a>';
		$productFileContainer = '<div class="product-file-container"><p>Downloads disponíveis:</p>'.$productFile.'</div>';
	};
	echo '<div class="entry-featured">'.$productImg.$productFileContainer.'</div>';
}

add_action( 'woocommerce_before_shop_loop_item_title_new', 'woocommerce_img_pdf', 11 );
add_action( 'woocommerce_before_shop_loop_item_title_new', 'x_woocommerce_before_shop_loop_item_title', 10 );