<?php
/**
 * Copyable commands. In "lines" mode each non-empty line gets its own copy button, plus
 * "Copy all"; in "file" mode the text is one preformatted block with a single copy button.
 * The buttons are wired up in assets/js/guides.js.
 *
 * @package WPAMesh
 */

$code  = str_replace( "\r\n", "\n", (string) $attributes['code'] );
$label = (string) $attributes['label'];
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'wpamesh-card wpamesh-cli' ) ); ?>>
    <?php if ( 'file' === $attributes['mode'] ) : ?>
        <div class="bar"><span><?php echo esc_html( $label ); ?></span><button class="wpamesh-copy" type="button"><?php esc_html_e( 'Copy', 'wpamesh' ); ?></button></div>
        <pre><?php echo esc_html( trim( $code, "\n" ) ); ?></pre>
    <?php else : ?>
        <div class="bar"><span><?php echo esc_html( $label ); ?></span><button class="wpamesh-copy" type="button"><?php esc_html_e( 'Copy all', 'wpamesh' ); ?></button></div>
        <ol>
            <?php foreach ( array_filter( array_map( 'trim', explode( "\n", $code ) ), 'strlen' ) as $line ) : ?>
                <li><code><?php echo esc_html( $line ); ?></code><button class="wpamesh-copy" type="button" aria-label="<?php echo esc_attr( sprintf( __( 'Copy: %s', 'wpamesh' ), $line ) ); ?>"><?php esc_html_e( 'Copy', 'wpamesh' ); ?></button></li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
</div>
