<?php
/**
 * Title: Network Stats
 * Slug: wpamesh/network-stats
 * Categories: wpamesh
 * Keywords: stats, statistics, numbers, nodes
 * Inserter: true
 */

// Fetch live stats from meshview API
$stats = wpamesh_get_network_stats();
$channel_metrics = wpamesh_get_channel_metrics();
?>
<!-- wp:group {"className":"wpamesh-right-widget","layout":{"type":"default"}} -->
<div class="wpamesh-right-widget wp-block-group"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Network Stats</h3>
<!-- /wp:heading -->

<!-- wp:html -->
<div class="wpamesh-stats-grid">
<div title="Nodes witnessed in the last 7 days." class="wpamesh-stat-box">
<p class="number"><?php echo esc_html( number_format( $stats['total_nodes'] ) ); ?></p>
<p class="label">Nodes</p>
</div>
<div title="Nodes witnessed in the last 3 days." class="wpamesh-stat-box">
<p class="number"><?php echo esc_html( number_format( $stats['active_nodes'] ) ); ?></p>
<p class="label">Active</p>
</div>
<div title="Routers witnessed in the last 7 days." class="wpamesh-stat-box">
<p class="number"><?php echo esc_html( number_format( $stats['routers'] ) ); ?></p>
<p class="label">Routers</p>
</div>
<div title="Messages (including telemetry) heard over the past 24 hours." class="wpamesh-stat-box">
<p class="number"><?php echo esc_html( number_format( $stats['packets_24h'] ) ); ?></p>
<p class="label">Msgs/24h</p>
</div>
<div title="The average bandwidth usage on our preset as witnessed by our routers." class="wpamesh-stat-box">
<p class="number"><?php echo $channel_metrics['channel_utilization'] !== null ? esc_html( $channel_metrics['channel_utilization'] . '%' ) : '—'; ?></p>
<p class="label">Ch. Util</p>
</div>
<div title="Our router's average transmit time on this preset." class="wpamesh-stat-box">
<p class="number"><?php echo $channel_metrics['air_util_tx'] !== null ? esc_html( $channel_metrics['air_util_tx'] . '%' ) : '—'; ?></p>
<p class="label">Airtime</p>
</div>
</div>
<!-- /wp:html --></div>
<!-- /wp:group -->
