<?php
/**
 * Server-side rendering for the Stat Box block.
 *
 * Uses the aggregated sidebar stats cache for better performance.
 * Includes data-stat attribute for AJAX lazy loading updates.
 *
 * @package wpamesh-theme
 */

$stat = $attributes['stat'] ?? 'total_nodes';

// Define stat configurations.
$stat_configs = array(
    'total_nodes' => array(
        'label'   => 'Nodes',
        'tooltip' => 'Nodes witnessed in the last 7 days.',
        'key'     => 'total_nodes',
        'format'  => 'number',
    ),
    'active_nodes' => array(
        'label'   => 'Active',
        'tooltip' => 'Nodes witnessed in the last 3 days.',
        'key'     => 'active_nodes',
        'format'  => 'number',
    ),
    'routers' => array(
        'label'   => 'Routers',
        'tooltip' => 'Routers witnessed in the last 7 days.',
        'key'     => 'routers',
        'format'  => 'number',
    ),
    'packets_24h' => array(
        'label'   => 'Msgs/24h',
        'tooltip' => 'Messages (including telemetry) heard over the past 24 hours.',
        'key'     => 'packets_24h',
        'format'  => 'number',
    ),
    'channel_utilization' => array(
        'label'   => 'Ch. Util',
        'tooltip' => 'The average bandwidth usage on our preset as witnessed by our routers.',
        'key'     => 'channel_utilization',
        'format'  => 'percent',
    ),
    'air_util_tx' => array(
        'label'   => 'Airtime',
        'tooltip' => "Our router's average transmit time on this preset.",
        'key'     => 'air_util_tx',
        'format'  => 'percent',
    ),
);

// Get the config for the selected stat.
$config = $stat_configs[ $stat ] ?? $stat_configs['total_nodes'];

// Fetch from aggregated sidebar stats cache (single API call for all stats).
$sidebar_stats = wpamesh_get_sidebar_stats();
$value = $sidebar_stats[ $config['key'] ] ?? null;

// Format the value.
if ( 'percent' === $config['format'] ) {
    $display_value = $value !== null ? esc_html( $value . '%' ) : '—';
} else {
    $display_value = $value !== null ? esc_html( number_format( $value ) ) : '—';
}
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'wpamesh-stat-box' ) ); ?> data-stat="<?php echo esc_attr( $stat ); ?>" title="<?php echo esc_attr( $config['tooltip'] ); ?>">
    <p class="number"><?php echo $display_value; ?></p>
    <p class="label"><?php echo esc_html( $config['label'] ); ?></p>
</div>
