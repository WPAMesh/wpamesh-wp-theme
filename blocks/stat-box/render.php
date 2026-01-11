<?php
/**
 * Server-side rendering for the Stat Box block.
 *
 * @package wpamesh-theme
 */

$stat = $attributes['stat'] ?? 'total_nodes';

// Define stat configurations.
$stat_configs = array(
    'total_nodes' => array(
        'label'   => 'Nodes',
        'tooltip' => 'Nodes witnessed in the last 7 days.',
        'source'  => 'stats',
        'key'     => 'total_nodes',
        'format'  => 'number',
    ),
    'active_nodes' => array(
        'label'   => 'Active',
        'tooltip' => 'Nodes witnessed in the last 3 days.',
        'source'  => 'stats',
        'key'     => 'active_nodes',
        'format'  => 'number',
    ),
    'routers' => array(
        'label'   => 'Routers',
        'tooltip' => 'Routers witnessed in the last 7 days.',
        'source'  => 'stats',
        'key'     => 'routers',
        'format'  => 'number',
    ),
    'packets_24h' => array(
        'label'   => 'Msgs/24h',
        'tooltip' => 'Messages (including telemetry) heard over the past 24 hours.',
        'source'  => 'stats',
        'key'     => 'packets_24h',
        'format'  => 'number',
    ),
    'channel_utilization' => array(
        'label'   => 'Ch. Util',
        'tooltip' => 'The average bandwidth usage on our preset as witnessed by our routers.',
        'source'  => 'channel',
        'key'     => 'channel_utilization',
        'format'  => 'percent',
    ),
    'air_util_tx' => array(
        'label'   => 'Airtime',
        'tooltip' => "Our router's average transmit time on this preset.",
        'source'  => 'channel',
        'key'     => 'air_util_tx',
        'format'  => 'percent',
    ),
);

// Get the config for the selected stat.
$config = $stat_configs[ $stat ] ?? $stat_configs['total_nodes'];

// Fetch the appropriate data.
if ( 'channel' === $config['source'] ) {
    $data  = wpamesh_get_channel_metrics();
    $value = $data[ $config['key'] ] ?? null;
} else {
    $data  = wpamesh_get_network_stats();
    $value = $data[ $config['key'] ] ?? 0;
}

// Format the value.
if ( 'percent' === $config['format'] ) {
    $display_value = $value !== null ? esc_html( $value . '%' ) : '—';
} else {
    $display_value = esc_html( number_format( $value ) );
}
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'wpamesh-stat-box' ) ); ?> title="<?php echo esc_attr( $config['tooltip'] ); ?>">
    <p class="number"><?php echo $display_value; ?></p>
    <p class="label"><?php echo esc_html( $config['label'] ); ?></p>
</div>
