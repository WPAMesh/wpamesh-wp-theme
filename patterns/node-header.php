<?php
/**
 * Title: Node Header
 * Slug: wpamesh/node-header
 * Categories: wpamesh
 * Keywords: node, meshtastic, header, title
 * Inserter: true
 */

// Get the current post ID for ACF field retrieval
$post_id = get_the_ID();

// Get ACF/SCF fields with explicit post ID
$long_name  = get_field( 'long_name', $post_id ) ?: 'Node Name';
$short_name = get_field( 'short_name', $post_id ) ?: '📡';

// Role returns array with 'value' and 'label' keys
$role_field = get_field( 'role', $post_id );
$role_value = is_array( $role_field ) ? $role_field['value'] : 'unknown';
$role_label = is_array( $role_field ) ? $role_field['label'] : ( $role_field ?: 'Unknown' );

// Check for featured image
$has_thumbnail = has_post_thumbnail( $post_id );
?>
<!-- wp:html -->
<div class="wpamesh-node-page-header wpamesh-node-role-<?php echo esc_attr( $role_value ); ?>">
<span class="wpamesh-node-page-icon"><?php echo esc_html( $short_name ); ?></span>
<div class="wpamesh-node-page-title">
<h1 class="wpamesh-node-page-name"><?php echo esc_html( $long_name ); ?></h1>
<span class="wpamesh-node-mode wpamesh-role-<?php echo esc_attr( $role_value ); ?>"><?php echo esc_html( $role_label ); ?></span>
</div>
</div>
<?php if ( $has_thumbnail ) : ?>
<div class="wpamesh-node-featured-image">
<?php echo get_the_post_thumbnail( $post_id, 'large', array( 'alt' => esc_attr( $long_name ) ) ); ?>
</div>
<?php endif; ?>
<!-- /wp:html -->
