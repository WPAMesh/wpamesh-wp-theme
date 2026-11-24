/* Editor previews for the server-rendered site blocks. Each renders from the current site's fields. */
( function ( wp ) {
    const el = wp.element.createElement;
    const ServerSideRender = wp.serverSideRender;
    const { useBlockProps } = wp.blockEditor;

    [ 'site-header', 'site-photo', 'site-info', 'site-pager', 'site-list' ].forEach( ( name ) => {
        wp.blocks.registerBlockType( 'wpamesh/' + name, {
            edit: ( { attributes, context } ) => el(
                'div',
                useBlockProps(),
                el( ServerSideRender, {
                    block: 'wpamesh/' + name,
                    attributes,
                    urlQueryArgs: context && context.postId ? { post_id: context.postId } : {},
                } )
            ),
        } );
    } );
} )( window.wp );
