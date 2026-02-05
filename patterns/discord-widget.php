<?php
/**
 * Title: Discord Widget
 * Slug: wpamesh/discord-widget
 * Categories: wpamesh
 * Keywords: discord, community, chat, join
 * Inserter: true
 */

$discord_data  = wpamesh_get_discord_widget_data();
$online_count  = $discord_data['online_count'];
$count_display = $online_count !== null ? sprintf(
    /* translators: %s: number of online members */
    _n( '%s member online', '%s members online', $online_count, 'wpamesh' ),
    number_format_i18n( $online_count )
) : '—';
?>
<!-- wp:group {"className":"wpamesh-right-widget","layout":{"type":"default"}} -->
<div class="wpamesh-right-widget wp-block-group"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Join the Community</h3>
<!-- /wp:heading -->

<!-- wp:html -->
<div class="wpamesh-discord-widget">
<p class="online" data-discord-online><?php echo esc_html( $count_display ); ?></p>
<div class="wp-block-buttons" style="justify-content:center;display:flex;">
<div class="wpamesh-discord-btn wp-block-button"><a class="wp-block-button__link wp-element-button" href="https://wpamesh.net/discord-access/" target="_blank" rel="noreferrer noopener">Join Discord</a></div>
</div>
</div>
<!-- /wp:html --></div>
<!-- /wp:group -->
