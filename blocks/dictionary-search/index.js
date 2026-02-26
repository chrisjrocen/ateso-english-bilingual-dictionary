const { registerBlockType } = wp.blocks;
const { useBlockProps } = wp.blockEditor;
const { ServerSideRender } = wp.serverSideRender || wp.components;
const el = wp.element.createElement;

registerBlockType('ateso-dict/dictionary-search', {
	edit: function (props) {
		const blockProps = useBlockProps();

		return el(
			'div',
			blockProps,
			el(
				'div',
				{ className: 'ateso-dict-editor-preview' },
				el(
					'div',
					{ className: 'ateso-dict-editor-icon' },
					el('span', { className: 'dashicons dashicons-book-alt' })
				),
				el('h3', null, 'Ateso-English Dictionary'),
				el('p', null, 'The dictionary search and browse interface will appear here on the frontend.'),
				el(ServerSideRender, {
					block: 'ateso-dict/dictionary-search',
					attributes: props.attributes,
				})
			)
		);
	},

	save: function () {
		return null; // Server-side rendered.
	},
});
