<?php
/**
 * Lists the published child pages of the current page, grouped by guide section in the order
 * of wpamesh_guide_sections(). Within a section, pages follow their Order attribute.
 *
 * @package WPAMesh
 */

$parent = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : (int) get_the_ID();
if ( ! $parent ) {
    return;
}

$guides = get_pages( array(
    'parent'      => $parent,
    'sort_column' => 'menu_order,post_title',
) );

$grouped = array();
foreach ( $guides as $guide ) {
    $grouped[ (string) get_post_meta( $guide->ID, 'wpamesh_guide_section', true ) ][] = $guide;
}

$sections = wpamesh_guide_sections() + array( '' => __( 'More', 'wpamesh' ) );
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'wpamesh-guide-index' ) ); ?>>
    <?php
    foreach ( $sections as $key => $title ) :
        if ( empty( $grouped[ $key ] ) ) {
            continue;
        }
        ?>
        <section class="wpamesh-guide-group">
            <div class="head">
                <?php if ( in_array( $key, array( 'meshcore', 'meshtastic', 'reticulum' ), true ) ) : ?>
                    <?php echo str_replace( 'wpamesh-chip ', 'wpamesh-chip lg ', wpamesh_protocol_chip( $key ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper. ?>
                <?php endif; ?>
                <h2><?php echo esc_html( $title ); ?></h2>
            </div>
            <div class="wpamesh-guide-list">
                <?php foreach ( $grouped[ $key ] as $guide ) : ?>
                    <a class="wpamesh-card" href="<?php echo esc_url( get_permalink( $guide ) ); ?>">
                        <b><?php echo esc_html( get_the_title( $guide ) ); ?></b>
                        <?php if ( has_excerpt( $guide ) ) : ?>
                            <span><?php echo esc_html( wp_trim_words( get_the_excerpt( $guide ), 16 ) ); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>
