<?php
/**
 * Server-side rendering for the Node List block.
 *
 * Uses the aggregated node list cache for better performance.
 * Includes data-node-id attribute for AJAX lazy loading updates.
 *
 * @package wpamesh-theme
 */

$tier_filter = isset( $attributes['tier'] ) ? sanitize_key( $attributes['tier'] ) : '';
$show_title  = isset( $attributes['showTitle'] ) ? (bool) $attributes['showTitle'] : true;

// Define tier labels.
$tier_labels = array(
    'core_router'  => __( 'Routers', 'wpamesh' ),
    'supplemental' => __( 'Medium Profile', 'wpamesh' ),
    'gateway'      => __( 'Gateways', 'wpamesh' ),
    'service'      => __( 'Other Services', 'wpamesh' ),
);

// Get nodes from aggregated cache (background-refreshed, no blocking API calls).
$cached_nodes = wpamesh_get_node_list_data();

// If cache is empty, fall back to basic WP query without live data.
if ( empty( $cached_nodes ) ) {
    // Query posts in the Node-Detail category for basic info only.
    $nodes_query = new WP_Query( array(
        'category_name'  => 'node-detail',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ) );

    $cached_nodes = array();
    if ( $nodes_query->have_posts() ) {
        while ( $nodes_query->have_posts() ) {
            $nodes_query->the_post();
            $post_id = get_the_ID();

            $node_tier_field = get_field( 'node_tier', $post_id );
            $node_tier = is_array( $node_tier_field ) ? $node_tier_field['value'] : ( $node_tier_field ?: 'uncategorized' );

            $role_field = get_field( 'role', $post_id );
            $role_label = is_array( $role_field ) ? $role_field['label'] : ( $role_field ?: '' );

            $cached_nodes[] = array(
                'post_id'       => $post_id,
                'node_id'       => get_field( 'node_id', $post_id ),
                'tier'          => $node_tier,
                'long_name'     => get_field( 'long_name', $post_id ) ?: get_the_title(),
                'short_name'    => get_field( 'short_name', $post_id ) ?: '',
                'permalink'     => get_permalink(),
                'location'      => get_field( 'location_name', $post_id ),
                'role'          => $role_label,
                'has_live_data' => false,
                'is_online'     => null,
                'channel_util'  => null,
                'air_util'      => null,
            );
        }
        wp_reset_postdata();
    }
}

// Group nodes by tier.
$grouped_nodes = array();
foreach ( $tier_labels as $key => $label ) {
    $grouped_nodes[ $key ] = array();
}
$grouped_nodes['uncategorized'] = array();

foreach ( $cached_nodes as $node ) {
    // If filtering by tier, skip non-matching nodes.
    if ( $tier_filter && $node['tier'] !== $tier_filter ) {
        continue;
    }

    $tier_key = isset( $grouped_nodes[ $node['tier'] ] ) ? $node['tier'] : 'uncategorized';
    $grouped_nodes[ $tier_key ][] = $node;
}

// Remove empty groups.
$grouped_nodes = array_filter( $grouped_nodes );

if ( empty( $grouped_nodes ) ) {
    return '';
}

// Build output.
$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'wpamesh-node-list-block' ) );
?>
<div <?php echo $wrapper_attributes; ?>>
<?php foreach ( $grouped_nodes as $tier_key => $nodes ) : ?>
    <?php if ( empty( $nodes ) ) continue; ?>
    <?php $tier_label = isset( $tier_labels[ $tier_key ] ) ? $tier_labels[ $tier_key ] : __( 'Other', 'wpamesh' ); ?>
    <div class="wpamesh-node-tier wpamesh-tier-<?php echo esc_attr( $tier_key ); ?>">
        <?php if ( $show_title ) : ?>
        <h3 class="wpamesh-tier-title"><?php echo esc_html( $tier_label ); ?></h3>
        <?php endif; ?>
        <ul class="wpamesh-node-list">
            <?php foreach ( $nodes as $node ) : ?>
            <li class="wpamesh-node-item"<?php echo $node['node_id'] ? ' data-node-id="' . esc_attr( $node['node_id'] ) . '"' : ''; ?>>
                <div class="wpamesh-node-info">
                    <h4>
                        <?php if ( $node['has_live_data'] ) : ?>
                        <span class="wpamesh-status-dot <?php echo $node['is_online'] ? 'online' : 'offline'; ?>" title="<?php echo $node['is_online'] ? esc_attr__( 'Online', 'wpamesh' ) : esc_attr__( 'Offline', 'wpamesh' ); ?>"></span>
                        <?php endif; ?>
                        <a href="<?php echo esc_url( $node['permalink'] ); ?>">
                            <?php echo esc_html( $node['long_name'] ); ?>
                        </a>
                    </h4>
                    <div class="meta">
                        <?php if ( $node['role'] ) : ?>
                        <span class="role"><?php echo esc_html( $node['role'] ); ?></span>
                        <?php endif; ?>
                        <?php if ( $node['role'] && $node['location'] ) : ?>
                        <span class="separator">·</span>
                        <?php endif; ?>
                        <?php if ( $node['location'] ) : ?>
                        <span class="location"><?php echo esc_html( $node['location'] ); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ( $node['channel_util'] !== null || $node['air_util'] !== null ) : ?>
                <div class="wpamesh-node-metrics">
                    <?php if ( $node['channel_util'] !== null ) : ?>
                    <span class="metric" title="<?php esc_attr_e( 'Channel Utilization', 'wpamesh' ); ?>"><?php echo esc_html( $node['channel_util'] ); ?>% <span class="label"><?php esc_html_e( 'Ch', 'wpamesh' ); ?></span></span>
                    <?php endif; ?>
                    <?php if ( $node['air_util'] !== null ) : ?>
                    <span class="metric" title="<?php esc_attr_e( 'Airtime TX', 'wpamesh' ); ?>"><?php echo esc_html( $node['air_util'] ); ?>% <span class="label"><?php esc_html_e( 'Air', 'wpamesh' ); ?></span></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endforeach; ?>
</div>
