/*
 * The help desk's blocks. (Roadmap 4.0-UX-07)
 *
 * Written against the APIs WordPress already ships and nothing else - no JSX,
 * no bundler, no package manager. This plugin has none of those, and adding a
 * toolchain to register seven blocks would be a larger change than the blocks
 * themselves and a heavier thing to maintain for ever.
 *
 * Every block is dynamic: it stores its name and its settings in the post, and
 * the markup is produced by PHP on each request. That is not a shortcut, it is
 * the correct shape - a ticket list saved into post content would be somebody's
 * queue as it looked on the day the page was last edited.
 */
(function (blocks, element, blockEditor, components, i18n, data) {
    if (!blocks || !element || !data || !data.blocks) {
        return;
    }
    var el = element.createElement;
    var useBlockProps = blockEditor && blockEditor.useBlockProps ? blockEditor.useBlockProps : null;
    var InspectorControls = blockEditor ? blockEditor.InspectorControls : null;
    var PanelBody = components ? components.PanelBody : null;
    var SelectControl = components ? components.SelectControl : null;
    /* Available since WordPress 5.3. Where it is missing the block still works
       and simply describes itself instead of previewing - which is better than
       failing to register. */
    var ServerSideRender = window.wp && window.wp.serverSideRender ? window.wp.serverSideRender : null;

    data.blocks.forEach(function (definition) {
        blocks.registerBlockType(definition.name, {
            apiVersion: 2,
            title: definition.title,
            description: definition.description,
            icon: definition.icon,
            category: data.category,
            attributes: {
                variant: { type: 'string', 'default': 'all' }
            },
            edit: function (props) {
                var children = [];

                if (definition.variants && definition.variants.length && InspectorControls && SelectControl) {
                    children.push(el(InspectorControls, { key: 'inspector' },
                        el(PanelBody, { title: data.strings.settings, initialOpen: true },
                            el(SelectControl, {
                                label: data.strings.showing,
                                value: props.attributes.variant,
                                options: definition.variants.map(function (v) {
                                    return { label: data.strings[v] || v, value: v };
                                }),
                                onChange: function (value) {
                                    props.setAttributes({ variant: value });
                                }
                            })
                        )
                    ));
                }

                if (ServerSideRender) {
                    /* The real thing, rendered by the server, so what somebody
                       lays out is what a visitor will see. */
                    children.push(el(ServerSideRender, {
                        key: 'preview',
                        block: definition.name,
                        attributes: props.attributes
                    }));
                } else {
                    children.push(el('p', { key: 'fallback' }, definition.description));
                }

                var wrapper = useBlockProps ? useBlockProps() : {};
                return el('div', wrapper, children);
            },
            /* Nothing is saved: the block is rendered by PHP every time. */
            save: function () {
                return null;
            }
        });
    });
})(
    window.wp && window.wp.blocks,
    window.wp && window.wp.element,
    window.wp && window.wp.blockEditor,
    window.wp && window.wp.components,
    window.wp && window.wp.i18n,
    window.jsstBlocks
);
