<?php
/**
 * Headline numbers for one network's card on the homepage.
 *
 * @package WPAMesh
 */

$stats = wpamesh_mesh_stats();
if ( ! $stats || ! $stats['updated'] ) {
    return;
}

$numbers = array();
switch ( $attributes['protocol'] ) {
    case 'meshcore':
        if ( $stats['meshcore'] ) {
            $numbers = array(
                __( 'Repeaters', 'wpamesh' )    => $stats['meshcore']['repeaters'],
                __( 'Heard in 24h', 'wpamesh' ) => $stats['meshcore']['active_24h'],
            );
        }
        break;
    case 'meshtastic':
        $numbers = array(
            __( 'Nodes', 'wpamesh' )   => $stats['total_nodes'],
            __( 'Routers', 'wpamesh' ) => $stats['routers'],
        );
        break;
    case 'reticulum':
        if ( ! empty( $stats['reticulum'] ) ) {
            $numbers = array(
                __( 'Nodes', 'wpamesh' )        => $stats['reticulum']['nodes'],
                __( 'Heard in 24h', 'wpamesh' ) => $stats['reticulum']['active_24h'],
            );
        }
        break;
}
if ( ! $numbers ) {
    return;
}
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'nums' ) ); ?>>
    <?php foreach ( $numbers as $label => $value ) : ?>
        <div><b><?php echo esc_html( number_format_i18n( $value ) ); ?></b><span><?php echo esc_html( $label ); ?></span></div>
    <?php endforeach; ?>
</div>
