( function( wp ) {
    const { registerBlockType } = wp.blocks;
    const { useBlockProps, InspectorControls } = wp.blockEditor;
    const { PanelBody, SelectControl } = wp.components;
    const { createElement: el } = wp.element;

    const statOptions = [
        { value: 'total_nodes', label: 'Nodes (7 days)' },
        { value: 'active_nodes', label: 'Active Nodes (3 days)' },
        { value: 'routers', label: 'Routers (7 days)' },
        { value: 'packets_24h', label: 'Messages (24h)' },
        { value: 'channel_utilization', label: 'Channel Utilization' },
        { value: 'air_util_tx', label: 'Airtime TX' },
    ];

    const statLabels = {
        total_nodes: 'Nodes',
        active_nodes: 'Active',
        routers: 'Routers',
        packets_24h: 'Msgs/24h',
        channel_utilization: 'Ch. Util',
        air_util_tx: 'Airtime',
    };

    registerBlockType( 'wpamesh/stat-box', {
        edit: function( props ) {
            const { attributes, setAttributes } = props;
            const { stat } = attributes;
            const blockProps = useBlockProps( { className: 'wpamesh-stat-box' } );

            return el(
                'div',
                null,
                el(
                    InspectorControls,
                    null,
                    el(
                        PanelBody,
                        { title: 'Stat Settings', initialOpen: true },
                        el( SelectControl, {
                            label: 'Statistic to display',
                            value: stat,
                            options: statOptions,
                            onChange: function( value ) {
                                setAttributes( { stat: value } );
                            },
                        } )
                    )
                ),
                el(
                    'div',
                    blockProps,
                    el( 'p', { className: 'number' }, '—' ),
                    el( 'p', { className: 'label' }, statLabels[ stat ] || 'Stat' )
                )
            );
        },
    } );
} )( window.wp );
