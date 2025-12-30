<?php
/**
 * Title: Recent Posts Grid
 * Slug: wpamesh/recent-posts
 * Categories: wpamesh
 * Keywords: posts, recent, grid, articles
 * Inserter: true
 */
?>
<!-- wp:group {"className":"wpamesh-content-section","layout":{"type":"default"}} -->
<div class="wpamesh-content-section wp-block-group">

<!-- wp:heading {"level":2,"className":"wpamesh-section-title"} -->
<h2 class="wp-block-heading wpamesh-section-title">Recent Posts</h2>
<!-- /wp:heading -->

<!-- wp:query {"queryId":10,"query":{"perPage":4,"postType":"post","order":"desc","orderBy":"date","inherit":false}} -->
<!-- wp:post-template {"className":"wpamesh-posts-grid","layout":{"type":"grid","columnCount":2}} -->

<!-- wp:group {"className":"wpamesh-post-card","layout":{"type":"default"}} -->
<div class="wpamesh-post-card wp-block-group">

<!-- wp:post-title {"level":4,"isLink":true} /-->

<!-- wp:group {"className":"meta","layout":{"type":"flex","flexWrap":"wrap"},"fontSize":"small"} -->
<div class="meta wp-block-group">
<!-- wp:post-terms {"term":"category"} /-->
<!-- wp:paragraph -->
<p>·</p>
<!-- /wp:paragraph -->
<!-- wp:post-excerpt {"excerptLength":10,"moreText":""} /-->
</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->

<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">No posts yet. Check back soon!</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results -->
<!-- /wp:query -->

</div>
<!-- /wp:group -->
