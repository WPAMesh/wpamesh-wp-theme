<?php
/**
 * Title: Node Details
 * Slug: wpamesh/node-details
 * Categories: wpamesh
 * Keywords: node, meshtastic, details, specs
 * Inserter: true
 */

// Get the current post ID for ACF field retrieval
$post_id = get_the_ID();

// Get ACF/SCF fields with explicit post ID
// Required fields (always displayed)
$long_name  = get_field( 'long_name', $post_id ) ?: 'Node Name';
$short_name = get_field( 'short_name', $post_id ) ?: '📡';
$hardware   = get_field( 'hardware', $post_id ) ?: 'Unknown';
$maintainer = get_field( 'maintainer', $post_id ) ?: 'Unknown';

// Role returns array with 'value' and 'label' keys
$role_field = get_field( 'role', $post_id );
$role_value = is_array( $role_field ) ? $role_field['value'] : 'unknown';
$role_label = is_array( $role_field ) ? $role_field['label'] : ( $role_field ?: 'Unknown' );

// Optional fields (only displayed if set)
$antenna_gain = get_field( 'antenna_gain', $post_id );
$height_agl   = get_field( 'height_agl', $post_id );
$height_msl   = get_field( 'height_msl', $post_id );

// Location fields (lat/long pair)
$latitude  = get_field( 'latitude', $post_id );
$longitude = get_field( 'longitude', $post_id );
$has_location = $latitude && $longitude;
$maps_url = $has_location ? 'https://www.google.com/maps?q=' . urlencode( $latitude . ',' . $longitude ) : '';

// Format optional values using helper functions from functions.php
$antenna_gain_formatted = wpamesh_format_antenna_gain( $antenna_gain );
$height_agl_formatted   = wpamesh_format_height( $height_agl );
$height_msl_formatted   = wpamesh_format_height( $height_msl );
?>
<!-- wp:group {"className":"wpamesh-node-details wpamesh-node-role-<?php echo esc_attr( $role_value ); ?>","layout":{"type":"default"}} -->
<div class="wpamesh-node-details wpamesh-node-role-<?php echo esc_attr( $role_value ); ?> wp-block-group">

<!-- wp:html -->
<div class="wpamesh-node-header">
<span class="wpamesh-node-icon"><?php echo esc_html( $short_name ); ?></span>
<div class="wpamesh-node-title">
<h2 class="wpamesh-node-name"><?php echo esc_html( $long_name ); ?></h2>
<span class="wpamesh-node-mode wpamesh-role-<?php echo esc_attr( $role_value ); ?>"><?php echo esc_html( $role_label ); ?></span>
</div>
</div>
<!-- /wp:html -->

<!-- wp:html -->
<dl class="wpamesh-node-specs">
<div class="wpamesh-spec-row">
<dt>Hardware</dt>
<dd><?php echo esc_html( $hardware ); ?></dd>
</div>
<?php if ( $antenna_gain_formatted ) : ?>
<div class="wpamesh-spec-row">
<dt>Antenna</dt>
<dd><?php echo esc_html( $antenna_gain_formatted ); ?></dd>
</div>
<?php endif; ?>
<?php if ( $height_agl_formatted ) : ?>
<div class="wpamesh-spec-row">
<dt>Height AGL</dt>
<dd><?php echo esc_html( $height_agl_formatted ); ?></dd>
</div>
<?php endif; ?>
<?php if ( $height_msl_formatted ) : ?>
<div class="wpamesh-spec-row">
<dt>Height MSL</dt>
<dd><?php echo esc_html( $height_msl_formatted ); ?></dd>
</div>
<?php endif; ?>
<?php if ( $has_location ) : ?>
<div class="wpamesh-spec-row">
<dt>Location</dt>
<dd><a href="<?php echo esc_url( $maps_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $latitude . ', ' . $longitude ); ?></a></dd>
</div>
<?php endif; ?>
<div class="wpamesh-spec-row">
<dt>Maintainer</dt>
<dd><?php echo esc_html( $maintainer ); ?></dd>
</div>
</dl>
<!-- /wp:html -->

</div>
<!-- /wp:group -->
