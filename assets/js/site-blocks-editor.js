/* Editor previews for the server-rendered theme blocks, plus settings for the ones that have any. */
( function ( wp ) {
    const el = wp.element.createElement;
    const ServerSideRender = wp.serverSideRender;
    const { useBlockProps, InspectorControls } = wp.blockEditor;
    const { PanelBody, SelectControl } = wp.components;
    const { useSelect } = wp.data;

    const preview = ( name, attributes, context ) => el( ServerSideRender, {
        block: 'wpamesh/' + name,
        attributes,
        urlQueryArgs: context && context.postId ? { post_id: context.postId } : {},
    } );

    const settings = {
        'network-numbers': ( { attributes, setAttributes } ) => el( SelectControl, {
            label: 'Network',
            value: attributes.protocol,
            options: [
                { value: 'meshcore', label: 'MeshCore' },
                { value: 'meshtastic', label: 'Meshtastic' },
                { value: 'reticulum', label: 'Reticulum' },
            ],
            onChange: ( protocol ) => setAttributes( { protocol } ),
        } ),
        'featured-site': function SitePicker( { attributes, setAttributes } ) {
            const sites = useSelect( ( select ) => select( 'core' ).getEntityRecords( 'postType', 'wpamesh_site', { per_page: -1, orderby: 'title', order: 'asc' } ), [] );
            return el( SelectControl, {
                label: 'Site',
                value: attributes.siteId,
                options: [ { value: 0, label: 'A different site with a photo each day' } ].concat(
                    ( sites || [] ).map( ( s ) => ( { value: s.id, label: s.title.rendered } ) )
                ),
                onChange: ( id ) => setAttributes( { siteId: parseInt( id, 10 ) } ),
            } );
        },
    };

    [ 'site-header', 'site-photo', 'site-info', 'site-pager', 'site-list', 'network-numbers', 'mesh-stats', 'featured-site', 'guide-head', 'guide-toc', 'guide-index' ].forEach( ( name ) => {
        wp.blocks.registerBlockType( 'wpamesh/' + name, {
            edit: ( props ) => el(
                'div',
                useBlockProps(),
                settings[ name ] && el( InspectorControls, null, el( PanelBody, { title: 'Settings' }, el( settings[ name ], props ) ) ),
                preview( name, props.attributes, props.context )
            ),
        } );
    } );
} )( window.wp );
