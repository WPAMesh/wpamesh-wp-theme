<?php
/**
 * Title: Featured Node
 * Slug: wpamesh/featured-node
 * Categories: wpamesh
 * Keywords: node, featured, infrastructure, post
 * Inserter: true
 */
?>
<!-- wp:group {"className":"wpamesh-right-widget","layout":{"type":"default"}} -->
<div class="wpamesh-right-widget wp-block-group"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Featured Node</h3>
<!-- /wp:heading -->

<!-- wp:query {"queryId":20,"query":{"perPage":1,"postType":"post","sticky":"only","inherit":false}} -->
<div class="wp-block-query"><!-- wp:post-template {"className":"wpamesh-featured-node-wrapper","layout":{"type":"default"}} -->

<!-- wp:group {"className":"wpamesh-featured-node","layout":{"type":"default"}} -->
<div class="wpamesh-featured-node wp-block-group">

<!-- wp:post-featured-image {"isLink":true,"sizeSlug":"medium"} /-->

<!-- wp:post-title {"level":4,"isLink":true,"className":"node-name"} /-->

<!-- wp:post-excerpt {"excerptLength":20,"className":"node-desc"} /-->

</div>
<!-- /wp:group -->

<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:paragraph {"className":"node-desc"} -->
<p class="node-desc">No featured post selected. Mark a post as "sticky" to feature it here.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results -->

</div>
<!-- /wp:query -->

</div>
<!-- /wp:group -->
