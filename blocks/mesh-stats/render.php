<?php
/**
 * "The mesh right now": per-network numbers as a table, and as a list on narrow screens.
 *
 * @package WPAMesh
 */

$stats = wpamesh_mesh_stats();
if ( ! $stats || ! $stats['updated'] ) {
    return;
}

$num  = function ( $value ) {
    return esc_html( number_format_i18n( $value ) );
};
$dash = '<span class="muted">—</span>';
$mc   = $stats['meshcore'];
$cu   = $stats['channel_utilization'];

$load = '';
if ( null !== $cu ) {
    $load = sprintf(
        '<span class="%s">%s</span> · %s',
        esc_attr( wpamesh_channel_load_class( $cu ) ),
        esc_html( wpamesh_channel_load_label( $cu ) ),
        /* translators: 1: channel utilization %, 2: TX airtime % */
        esc_html( sprintf( __( '%1$s%% util · %2$s%% air', 'wpamesh' ), $cu, $stats['air_util_tx'] ) )
    );
}

/*
 * Each row: chip protocol, name, then table cells (nodes, active, infrastructure, messages,
 * channel health) and the phone list's main line, sub line and side note. All values are
 * escaped HTML.
 */
$rows = array(
    array(
        'meshcore', 'MeshCore',
        $mc ? $num( $mc['nodes'] ) : $dash,
        $mc ? $num( $mc['active_24h'] ) : $dash,
        $mc ? esc_html( sprintf( __( '%1$s repeaters · %2$s rooms', 'wpamesh' ), number_format_i18n( $mc['repeaters'] ), number_format_i18n( $mc['rooms'] ) ) ) : $dash,
        $dash,
        $dash,
        $mc ? esc_html( sprintf( __( '%1$s of %2$s heard today', 'wpamesh' ), number_format_i18n( $mc['active_24h'] ), number_format_i18n( $mc['nodes'] ) ) ) : esc_html__( 'Not available right now', 'wpamesh' ),
        $mc ? esc_html( sprintf( __( '%1$s repeaters · %2$s rooms', 'wpamesh' ), number_format_i18n( $mc['repeaters'] ), number_format_i18n( $mc['rooms'] ) ) ) : '',
        '',
    ),
    array(
        'meshtastic', 'Meshtastic',
        $num( $stats['total_nodes'] ),
        $num( $stats['active_24h'] ),
        esc_html( sprintf( _n( '%s router', '%s routers', $stats['routers'], 'wpamesh' ), number_format_i18n( $stats['routers'] ) ) ),
        $num( $stats['packets_24h'] ),
        $load ?: $dash,
        esc_html( sprintf( __( '%1$s of %2$s heard today', 'wpamesh' ), number_format_i18n( $stats['active_24h'] ), number_format_i18n( $stats['total_nodes'] ) ) ),
        esc_html( sprintf( __( '%1$s routers · %2$s msgs/24h', 'wpamesh' ), number_format_i18n( $stats['routers'] ), number_format_i18n( $stats['packets_24h'] ) ) ),
        null !== $cu ? sprintf( '<span class="%s">%s</span><br>%s%%', esc_attr( wpamesh_channel_load_class( $cu ) ), esc_html( wpamesh_channel_load_label( $cu ) ), esc_html( $cu ) ) : '',
    ),
    array(
        'reticulum', 'Reticulum',
        $dash, $dash, $dash,
        '<span class="muted">' . esc_html__( 'Not tracked', 'wpamesh' ) . '</span>',
        '<span class="muted">' . esc_html__( 'Not tracked', 'wpamesh' ) . '</span>',
        esc_html__( 'Not tracked yet', 'wpamesh' ),
        esc_html__( 'Experimental network', 'wpamesh' ),
        '',
    ),
);
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'wpamesh-mesh-stats' ) ); ?>>
    <div class="wpamesh-card wpamesh-stats-table">
        <table>
            <thead>
                <tr>
                    <th scope="col"><?php esc_html_e( 'Network', 'wpamesh' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Nodes', 'wpamesh' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Active 24h', 'wpamesh' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Infrastructure', 'wpamesh' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Messages 24h', 'wpamesh' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Channel health', 'wpamesh' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $rows as $row ) : ?>
                    <tr>
                        <td><span class="net"><?php echo wpamesh_protocol_chip( $row[0] ); ?><?php echo esc_html( $row[1] ); ?></span></td>
                        <?php for ( $i = 2; $i <= 6; $i++ ) : ?>
                            <td><?php echo $row[ $i ]; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped when built. ?></td>
                        <?php endfor; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="wpamesh-card wpamesh-stats-list">
        <ul>
            <?php foreach ( $rows as $row ) : ?>
                <li>
                    <?php echo wpamesh_protocol_chip( $row[0] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper. ?>
                    <span><span class="main"><?php echo $row[7]; // phpcs:ignore WordPress.Security.EscapeOutput ?></span><span class="sub"><?php echo $row[8]; // phpcs:ignore WordPress.Security.EscapeOutput ?></span></span>
                    <span class="side"><?php echo $row[9] ?: '—'; // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
