<?php

// =============================================================================
// VIEWS/INTEGRITY/WOOCOMMERCE.PHP
// -----------------------------------------------------------------------------
// WooCommerce page output for Integrity.
// =============================================================================

?>

<?php get_header(); ?>

	<div class="<?php x_main_content_class(); ?>" role="main">

	  <?php woocommerce_content_new(); ?>

	</div>

	<?php get_sidebar(); ?>


<?php get_footer(); ?>