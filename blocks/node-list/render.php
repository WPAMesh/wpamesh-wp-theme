<?php
/**
 * Server-side rendering for the Node List block.
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

// Query posts in the Node-Detail category.
$nodes_query = new WP_Query( array(
    'category_name'  => 'node-detail',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
) );

// Group nodes by tier.
$grouped_nodes = array();
foreach ( $tier_labels as $key => $label ) {
    $grouped_nodes[ $key ] = array();
}
$grouped_nodes['uncategorized'] = array();

if ( $nodes_query->have_posts() ) {
    while ( $nodes_query->have_posts() ) {
        $nodes_query->the_post();
        $post_id = get_the_ID();

        // Get tier field.
        $node_tier_field = get_field( 'node_tier', $post_id );
        $node_tier       = is_array( $node_tier_field ) ? $node_tier_field['value'] : ( $node_tier_field ?: 'uncategorized' );

        // If filtering by tier, skip non-matching nodes.
        if ( $tier_filter && $node_tier !== $tier_filter ) {
            continue;
        }

        // Get node fields.
        $long_name  = get_field( 'long_name', $post_id ) ?: get_the_title();
        $short_name = get_field( 'short_name', $post_id ) ?: '📡';
        $node_id    = get_field( 'node_id', $post_id );
        $location   = get_field( 'location_name', $post_id );

        // Get role field.
        $role_field = get_field( 'role', $post_id );
        $role_label = is_array( $role_field ) ? $role_field['label'] : ( $role_field ?: '' );

        // Get live status and channel metrics if node_id is set.
        $is_online     = null;
        $has_live_data = false;
        $channel_util  = null;
        $air_util      = null;
        if ( $node_id ) {
            $live_node = wpamesh_get_node_by_hex_id( $node_id );
            if ( $live_node ) {
                $has_live_data = true;
                $is_online     = $live_node['is_online'];

                // Get channel metrics for this node.
                $node_metrics = wpamesh_get_node_channel_metrics( $live_node['node_id'] );
                if ( $node_metrics ) {
                    $channel_util = $node_metrics['channel_utilization'];
                    $air_util     = $node_metrics['air_util_tx'];
                }
            }
        }

        $node_data = array(
            'long_name'     => $long_name,
            'short_name'    => $short_name,
            'permalink'     => get_permalink(),
            'location'      => $location,
            'role'          => $role_label,
            'has_live_data' => $has_live_data,
            'is_online'     => $is_online,
            'channel_util'  => $channel_util,
            'air_util'      => $air_util,
        );

        if ( isset( $grouped_nodes[ $node_tier ] ) ) {
            $grouped_nodes[ $node_tier ][] = $node_data;
        } else {
            $grouped_nodes['uncategorized'][] = $node_data;
        }
    }
    wp_reset_postdata();
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
            <li class="wpamesh-node-item">
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
