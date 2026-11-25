/* Editors for the Callout and Copyable commands blocks, and the guide section picker. */
( function ( wp ) {
    const el = wp.element.createElement;
    const { useBlockProps, InnerBlocks, InspectorControls, RichText } = wp.blockEditor;
    const { PanelBody, SelectControl, TextControl, TextareaControl } = wp.components;
    const ServerSideRender = wp.serverSideRender;

    wp.blocks.registerBlockType( 'wpamesh/callout', {
        edit: ( { attributes, setAttributes } ) => el(
            'div',
            useBlockProps( { className: ( 'wpamesh-callout ' + attributes.variant ).trim() } ),
            el( InspectorControls, null, el( PanelBody, { title: 'Callout' },
                el( SelectControl, {
                    label: 'Color',
                    value: attributes.variant,
                    options: [
                        { value: '', label: 'Gold (note)' },
                        { value: 'tip', label: 'Blue (tip)' },
                        { value: 'danger', label: 'Orange (danger)' },
                        { value: 'success', label: 'Green (success)' },
                    ],
                    onChange: ( variant ) => setAttributes( { variant } ),
                } ),
                el( SelectControl, {
                    label: 'Icon',
                    value: attributes.icon,
                    options: [ 'info', 'warn', 'tip', 'check', 'map', 'globe', 'key' ].map( ( v ) => ( { value: v, label: v } ) ),
                    onChange: ( icon ) => setAttributes( { icon } ),
                } )
            ) ),
            el( RichText, {
                tagName: 'span',
                className: 'title',
                placeholder: 'Title',
                value: attributes.title,
                onChange: ( title ) => setAttributes( { title } ),
            } ),
            el( 'div', null, el( InnerBlocks, { template: [ [ 'core/paragraph' ] ] } ) )
        ),
        save: () => el( InnerBlocks.Content ),
    } );

    wp.blocks.registerBlockType( 'wpamesh/cli', {
        edit: ( { attributes, setAttributes, isSelected } ) => el(
            'div',
            useBlockProps(),
            el( InspectorControls, null, el( PanelBody, { title: 'Commands' },
                el( TextControl, { label: 'Label', value: attributes.label, onChange: ( label ) => setAttributes( { label } ) } ),
                el( SelectControl, {
                    label: 'Copy buttons',
                    value: attributes.mode,
                    options: [
                        { value: 'lines', label: 'One per line, plus Copy all' },
                        { value: 'file', label: 'One for the whole block (config files)' },
                    ],
                    onChange: ( mode ) => setAttributes( { mode } ),
                } )
            ) ),
            isSelected
                ? el( TextareaControl, {
                    label: attributes.label + ( attributes.mode === 'file' ? ' (file)' : ' (one command per line)' ),
                    value: attributes.code,
                    rows: Math.max( 4, attributes.code.split( '\n' ).length + 1 ),
                    style: { fontFamily: 'monospace' },
                    onChange: ( code ) => setAttributes( { code } ),
                } )
                : el( ServerSideRender, { block: 'wpamesh/cli', attributes } )
        ),
    } );

    // Guide section, shown in the page's document sidebar.
    const { PluginDocumentSettingPanel } = wp.editor;
    const { useSelect, useDispatch } = wp.data;
    wp.plugins.registerPlugin( 'wpamesh-guide-section', {
        render: () => {
            const { type, meta } = useSelect( ( select ) => ( {
                type: select( 'core/editor' ).getCurrentPostType(),
                meta: select( 'core/editor' ).getEditedPostAttribute( 'meta' ),
            } ), [] );
            const { editPost } = useDispatch( 'core/editor' );
            if ( type !== 'page' || ! meta ) return null;
            return el( PluginDocumentSettingPanel, { name: 'wpamesh-guide-section', title: 'Guide' },
                el( SelectControl, {
                    label: 'Guide section',
                    help: 'Where this page appears on the guides index, and the badge above its title.',
                    value: meta.wpamesh_guide_section || '',
                    options: [ { value: '', label: 'Not a guide' } ].concat(
                        Object.entries( window.wpameshGuideSections ).map( ( [ value, label ] ) => ( { value, label } ) )
                    ),
                    onChange: ( value ) => editPost( { meta: { wpamesh_guide_section: value } } ),
                } )
            );
        },
    } );
} )( window.wp );
