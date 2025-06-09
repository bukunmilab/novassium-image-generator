(function (blocks, element, editor) {
    var el = element.createElement;
    var RichText = editor.RichText;

    blocks.registerBlockType('nig/image-generator', {
        title: 'Image Generator',
        icon: 'format-image',
        category: 'common',
        edit: function (props) {
            return el(
                'div',
                { className: props.className },
                el(
                    'p',
                    {},
                    'Generate images using your Novassium API key.'
                )
            );
        },
        save: function () {
            return null; // Server-side rendering
        },
    });
})(window.wp.blocks, window.wp.element, window.wp.editor);