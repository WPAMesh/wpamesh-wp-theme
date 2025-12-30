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
<div class="wpamesh-stat-box">
<p class="number"><?php echo esc_html( number_format( $stats['total_nodes'] ) ); ?></p>
<p class="label">Nodes</p>
</div>
<div class="wpamesh-stat-box">
<p class="number"><?php echo esc_html( number_format( $stats['active_nodes'] ) ); ?></p>
<p class="label">Active</p>
</div>
<div class="wpamesh-stat-box">
<p class="number"><?php echo esc_html( number_format( $stats['routers'] ) ); ?></p>
<p class="label">Routers</p>
</div>
<div class="wpamesh-stat-box">
<p class="number"><?php echo esc_html( number_format( $stats['packets_24h'] ) ); ?></p>
<p class="label">Msgs/24h</p>
</div>
<div class="wpamesh-stat-box">
<p class="number"><?php echo $channel_metrics['channel_utilization'] !== null ? esc_html( $channel_metrics['channel_utilization'] . '%' ) : '—'; ?></p>
<p class="label">Ch. Util</p>
</div>
<div class="wpamesh-stat-box">
<p class="number"><?php echo $channel_metrics['air_util_tx'] !== null ? esc_html( $channel_metrics['air_util_tx'] . '%' ) : '—'; ?></p>
<p class="label">Airtime</p>
</div>
</div>
<!-- /wp:html --></div>
<!-- /wp:group -->
