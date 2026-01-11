<?php
/**
 * Server-side rendering for the Network Stats block.
 *
 * This block is a container for Stat Box blocks. The $content variable
 * contains the rendered inner blocks (the individual stat boxes).
 *
 * @package wpamesh-theme
 */
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'wpamesh-right-widget' ) ); ?>>
    <h3 class="wp-block-heading">Network Stats</h3>
    <div class="wpamesh-stats-grid">
        <?php echo $content; ?>
    </div>
</div>
