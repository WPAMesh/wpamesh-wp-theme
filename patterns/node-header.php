<?php
/**
 * Title: Node Header
 * Slug: wpamesh/node-header
 * Categories: wpamesh
 * Keywords: node, meshtastic, header, title
 * Inserter: true
 *
 * Uses per-node cache for O(1) live status lookup (no blocking API calls).
 * Includes data-node-id attribute for AJAX lazy loading updates.
 */

// Get the current post ID for ACF field retrieval
$post_id = get_the_ID();

// Get ACF/SCF fields with explicit post ID
$long_name  = get_field( 'long_name', $post_id ) ?: 'Node Name';
$short_name = get_field( 'short_name', $post_id ) ?: '📡';
$node_id    = get_field( 'node_id', $post_id ); // Hex ID like "!a5060ad0"

// Role returns array with 'value' and 'label' keys
$role_field = get_field( 'role', $post_id );
$role_value = is_array( $role_field ) ? $role_field['value'] : 'unknown';
$role_label = is_array( $role_field ) ? $role_field['label'] : ( $role_field ?: 'Unknown' );

// Check for featured image
$has_thumbnail = has_post_thumbnail( $post_id );

// Get live node status from per-node cache (O(1) lookup, no blocking API calls)
$is_online     = null;
$last_seen     = '';
$has_live_data = false;
$channel_util  = null;
$load_level    = null;

if ( $node_id ) {
    $cached_node = wpamesh_get_single_node_data( $node_id );
    if ( $cached_node ) {
        $has_live_data = $cached_node['has_live_data'];
        $is_online     = $cached_node['is_online'];
        $last_seen     = $cached_node['last_seen'] ?? '';
        $channel_util  = $cached_node['channel_util'];
        $load_level    = wpamesh_get_channel_load_level( $channel_util );
    }
}

$online_class = $has_live_data ? ( $is_online ? 'wpamesh-node-online' : 'wpamesh-node-offline' ) : '';
?>
<!-- wp:html -->
<div class="wpamesh-node-page-header wpamesh-node-role-<?php echo esc_attr( $role_value ); ?> <?php echo esc_attr( $online_class ); ?>"<?php echo $node_id ? ' data-node-id="' . esc_attr( $node_id ) . '"' : ''; ?>>
<span class="wpamesh-node-page-icon"><?php echo esc_html( $short_name ); ?></span>
<div class="wpamesh-node-page-title">
<h1 class="wpamesh-node-page-name"><?php echo esc_html( $long_name ); ?></h1>
<div class="wpamesh-node-meta">
<span class="wpamesh-badge wpamesh-node-mode wpamesh-role-<?php echo esc_attr( $role_value ); ?>"><?php echo esc_html( $role_label ); ?></span>
<?php if ( $has_live_data ) : ?>
<span class="wpamesh-badge wpamesh-node-status <?php echo $is_online ? 'online' : 'offline'; ?>">
<?php echo $is_online ? esc_html__( 'Online', 'wpamesh' ) : esc_html__( 'Offline', 'wpamesh' ); ?>
</span>
<?php if ( ! $is_online && $last_seen ) : ?>
<span class="wpamesh-node-last-seen"><?php echo esc_html( $last_seen ); ?></span>
<?php endif; ?>
<?php endif; ?>
</div>
</div>
<?php if ( $channel_util !== null && $load_level ) : ?>
<div class="wpamesh-node-page-metrics">
<span class="wpamesh-channel-util wpamesh-load-<?php echo esc_attr( $load_level['level'] ); ?>" title="<?php echo esc_attr( sprintf( __( 'Channel Utilization: %s%% (%s load)', 'wpamesh' ), $channel_util, $load_level['label'] ) ); ?>">
<span class="value"><?php echo esc_html( $channel_util ); ?>%</span>
<span class="label"><?php echo esc_html( $load_level['label'] ); ?></span>
</span>
</div>
<?php endif; ?>
</div>
<?php if ( $has_thumbnail ) : ?>
<div class="wpamesh-node-featured-image">
<?php echo get_the_post_thumbnail( $post_id, 'large', array( 'alt' => esc_attr( $long_name ) ) ); ?>
</div>
<?php endif; ?>
<!-- /wp:html -->
