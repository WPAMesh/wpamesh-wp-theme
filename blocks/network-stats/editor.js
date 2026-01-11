( function( wp ) {
    const { registerBlockType } = wp.blocks;
    const { useBlockProps, InnerBlocks } = wp.blockEditor;
    const { createElement: el } = wp.element;

    const ALLOWED_BLOCKS = [ 'wpamesh/stat-box' ];

    // Default stat boxes to insert.
    const DEFAULT_TEMPLATE = [
        [ 'wpamesh/stat-box', { stat: 'total_nodes' } ],
        [ 'wpamesh/stat-box', { stat: 'active_nodes' } ],
        [ 'wpamesh/stat-box', { stat: 'routers' } ],
        [ 'wpamesh/stat-box', { stat: 'packets_24h' } ],
        [ 'wpamesh/stat-box', { stat: 'channel_utilization' } ],
        [ 'wpamesh/stat-box', { stat: 'air_util_tx' } ],
    ];

    registerBlockType( 'wpamesh/network-stats', {
        edit: function( props ) {
            const blockProps = useBlockProps( { className: 'wpamesh-right-widget' } );

            return el(
                'div',
                blockProps,
                el( 'h3', { className: 'wp-block-heading' }, 'Network Stats' ),
                el(
                    'div',
                    { className: 'wpamesh-stats-grid' },
                    el( InnerBlocks, {
                        allowedBlocks: ALLOWED_BLOCKS,
                        template: DEFAULT_TEMPLATE,
                        orientation: 'horizontal',
                    } )
                )
            );
        },

        save: function() {
            // Server-side rendered, but we need to save inner blocks.
            return el( InnerBlocks.Content );
        },
    } );
} )( window.wp );
