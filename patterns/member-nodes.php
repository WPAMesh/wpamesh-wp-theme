<?php
/**
 * Title: Member Nodes
 * Slug: wpamesh/member-nodes
 * Categories: wpamesh
 * Keywords: nodes, members, infrastructure, routers
 * Inserter: true
 *
 * Displays all member nodes grouped by tier.
 * For individual tier lists with custom content around them, use the shortcode:
 *   [wpamesh_node_list tier="core_router"]
 *   [wpamesh_node_list tier="supplemental"]
 *   [wpamesh_node_list tier="gateway"]
 *   [wpamesh_node_list tier="service"]
 *   [wpamesh_node_list tier="core_router" show_title="false"]
 */
?>
<!-- wp:group {"className":"wpamesh-member-nodes","layout":{"type":"default"}} -->
<div class="wpamesh-member-nodes wp-block-group">

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Member Nodes', 'wpamesh' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'This page shows a running list of nodes recognized as a core part of the WPA mesh. The nodes on this page are distinguished as devices which broaden the mesh reach and add to its overall reliability. While all nodes are helpful to the mesh, these ones are officially recognized (and coordinated) by WPA Mesh to be optimally positioned and configured to provide Western Pennsylvania a robust Meshtastic network.', 'wpamesh' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[wpamesh_node_list]
<!-- /wp:shortcode -->

</div>
<!-- /wp:group -->
