<?php
// Query featured entries
// child theme hueman-xili
//$featured = new WP_Query(

if ( class_exists('xili_language') && $curlang = xili_curlang() ) {
	$featured_query =	array(
			'no_found_rows'				=> false,
			'update_post_meta_cache'	=> false,
			'update_post_term_cache'	=> false,
			'ignore_sticky_posts'		=> 1,
			'posts_per_page'			=> ot_get_option('featured-posts-count'),
			'tax_query' => array(

				array(
					'field'    => 'slug',
					'taxonomy' => 'language', // language is a taxonomy
					'terms'    => $curlang,
				),
			),
		);

		if ( ot_get_option('featured-category') ){
			$featured_query['tax_query']['relation'] = 'AND';
			$featured_query['tax_query'][] =
				array(
					'field'    => 'term_id',
					'taxonomy' => 'category',
					'terms'    => ot_get_option('featured-category') // not an array - only one cat
				);
		}



} else {
	$featured_query =	array(
			'no_found_rows'				=> false,
			'update_post_meta_cache'	=> false,
			'update_post_term_cache'	=> false,
			'ignore_sticky_posts'		=> 1,
			'posts_per_page'			=> ot_get_option('featured-posts-count'),
			'cat'						=> ot_get_option('featured-category')
		//)
	);
}

$featured = new WP_Query( $featured_query );

?>

<?php if ( is_home() && !is_paged() && ( ot_get_option('featured-posts-count') =='1') ): // No slider if 1 post is featured ?>
	
	<div class="featured">
		<?php while ( $featured->have_posts() ): $featured->the_post(); ?>
			<?php get_template_part('content-featured'); ?>
		<?php endwhile; ?>	
	</div><!--/.featured-->
	
<?php elseif ( is_home() && !is_paged() && ( ot_get_option('featured-posts-count') !='0') ): // Show slider if posts are not 1 or 0 ?>
	
	<script type="text/javascript">
		// Check if first slider image is loaded, and load flexslider on document ready
		jQuery(document).ready(function(){
		 var firstImage = jQuery('#flexslider-featured').find('img').filter(':first'),
			checkforloaded = setInterval(function() {
				var image = firstImage.get(0);
				if (image.complete || image.readyState == 'complete' || image.readyState == 4) {
					clearInterval(checkforloaded);
					
					jQuery('#flexslider-featured').flexslider({
						animation: "slide",
						useCSS: false, // Fix iPad flickering issue
						slideshow: false,
						directionNav: true,
						controlNav: true,
						pauseOnHover: true,
						slideshowSpeed: 7000,
						animationSpeed: 400,
						smoothHeight: true,
						touch: false
					});
					
				}
			}, 20);
		});
	</script>
		
	<div class="featured flexslider" id="flexslider-featured">
		<ul class="slides">				
			<?php while ( $featured->have_posts() ): $featured->the_post(); ?>
			<li>	
				<?php get_template_part('content-featured'); ?>
			</li>
			<?php endwhile; ?>			
		</ul>
	</div><!--/.featured-->
	
<?php endif; ?>
<?php wp_reset_postdata(); ?>