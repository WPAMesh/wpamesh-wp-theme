( function( wp ) {
    const { registerBlockType } = wp.blocks;
    const { useBlockProps, InspectorControls } = wp.blockEditor;
    const { PanelBody, SelectControl, ToggleControl } = wp.components;
    const { createElement: el } = wp.element;

    const tierOptions = [
        { value: '', label: 'All Tiers (grouped)' },
        { value: 'core_router', label: 'Routers only' },
        { value: 'supplemental', label: 'Medium Profile only' },
        { value: 'gateway', label: 'Gateways only' },
        { value: 'service', label: 'Other Services only' },
    ];

    const tierLabels = {
        '': 'All Nodes',
        'core_router': 'Routers',
        'supplemental': 'Medium Profile',
        'gateway': 'Gateways',
        'service': 'Other Services',
    };

    registerBlockType( 'wpamesh/node-list', {
        edit: function( props ) {
            const { attributes, setAttributes } = props;
            const { tier, showTitle } = attributes;
            const blockProps = useBlockProps( { className: 'wpamesh-node-list-block' } );

            const displayLabel = tierLabels[ tier ] || 'All Nodes';

            return el(
                'div',
                null,
                el(
                    InspectorControls,
                    null,
                    el(
                        PanelBody,
                        { title: 'Node List Settings', initialOpen: true },
                        el( SelectControl, {
                            label: 'Filter by tier',
                            value: tier,
                            options: tierOptions,
                            onChange: function( value ) {
                                setAttributes( { tier: value } );
                            },
                        } ),
                        el( ToggleControl, {
                            label: 'Show tier headings',
                            checked: showTitle,
                            onChange: function( value ) {
                                setAttributes( { showTitle: value } );
                            },
                        } )
                    )
                ),
                el(
                    'div',
                    blockProps,
                    el(
                        'div',
                        { className: 'wpamesh-node-list-placeholder' },
                        el( 'span', { className: 'dashicons dashicons-networking' } ),
                        el( 'p', null, 'Node List' ),
                        el( 'p', { className: 'description' }, 'Displaying: ' + displayLabel ),
                        el( 'p', { className: 'description' }, showTitle ? 'With tier headings' : 'Without tier headings' )
                    )
                )
            );
        },
    } );
} )( window.wp );
