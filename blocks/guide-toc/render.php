<?php
/**
 * "On this page" links to the guide's h2 headings, plus a help card. The current section is
 * highlighted on scroll by assets/js/guides.js.
 *
 * @package WPAMesh
 */

$post_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : (int) get_the_ID();
if ( ! $post_id ) {
    return;
}

$headings = wpamesh_guide_headings( $post_id );
$section  = (string) get_post_meta( $post_id, 'wpamesh_guide_section', true );
?>
<aside <?php echo get_block_wrapper_attributes( array( 'class' => 'wpamesh-toc' ) ); ?> aria-label="<?php esc_attr_e( 'On this page', 'wpamesh' ); ?>">
    <?php if ( $headings ) : ?>
        <p class="wpamesh-aside-title"><?php esc_html_e( 'On this page', 'wpamesh' ); ?></p>
        <ol>
            <?php foreach ( $headings as $id => $text ) : ?>
                <li><a href="#<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $text ); ?></a></li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
    <div class="wpamesh-card switch">
        <?php if ( 'meshtastic' === $section ) : ?>
            <strong><?php esc_html_e( 'Buying your first radio?', 'wpamesh' ); ?></strong>
            <?php esc_html_e( 'We recommend MeshCore for new users.', 'wpamesh' ); ?>
            <a href="<?php echo esc_url( home_url( '/#networks' ) ); ?>"><?php esc_html_e( 'Compare the networks', 'wpamesh' ); ?></a>
        <?php else : ?>
            <strong><?php esc_html_e( 'Stuck?', 'wpamesh' ); ?></strong>
            <?php esc_html_e( 'Most help happens in our Discord.', 'wpamesh' ); ?>
            <a href="<?php echo esc_url( home_url( '/discord-access/' ) ); ?>"><?php esc_html_e( 'Ask there', 'wpamesh' ); ?></a>
        <?php endif; ?>
    </div>
</aside>
