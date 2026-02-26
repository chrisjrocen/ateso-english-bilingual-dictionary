/**
 * Word of the Day — Gutenberg Editor Script.
 *
 * Uses ServerSideRender for live preview, InspectorControls for settings.
 */
( function () {
	'use strict';

	const { registerBlockType } = wp.blocks;
	const { useBlockProps, InspectorControls } = wp.blockEditor;
	const { PanelBody, ToggleControl, Button, Spinner } = wp.components;
	const { useState } = wp.element;
	const ServerSideRender = wp.serverSideRender;
	const el = wp.element.createElement;

	registerBlockType( 'ateso-dict/word-of-the-day', {

		edit: function ( props ) {
			const { attributes, setAttributes } = props;
			const blockProps = useBlockProps();
			const [ isRefreshing, setIsRefreshing ] = useState( false );
			const [ refreshKey, setRefreshKey ] = useState( 0 );

			function handleRefresh() {
				setIsRefreshing( true );

				wp.apiFetch( {
					path: '/dictionary/v1/word-of-the-day/refresh',
					method: 'POST',
				} )
					.then( function () {
						// Increment key to force ServerSideRender remount.
						setRefreshKey( function ( prev ) {
							return prev + 1;
						} );
					} )
					.catch( function ( err ) {
						// eslint-disable-next-line no-console
						console.error( 'WOTD refresh error:', err );
					} )
					.finally( function () {
						setIsRefreshing( false );
					} );
			}

			return el(
				'div',
				blockProps,

				// Inspector sidebar controls.
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: 'Word of the Day Settings', initialOpen: true },
						el( ToggleControl, {
							label: 'Show Plural Form',
							help: attributes.showPlural
								? 'Plural form is visible.'
								: 'Plural form is hidden.',
							checked: attributes.showPlural,
							onChange: function ( val ) {
								setAttributes( { showPlural: val } );
							},
						} ),
						el(
							'div',
							{ style: { marginTop: '12px' } },
							el(
								Button,
								{
									variant: 'secondary',
									onClick: handleRefresh,
									disabled: isRefreshing,
									icon: isRefreshing ? null : 'update',
								},
								isRefreshing
									? el( Spinner, null )
									: 'Pick New Word'
							)
						)
					)
				),

				// Live preview via server-side render.
				el( ServerSideRender, {
					key: 'wotd-ssr-' + refreshKey,
					block: 'ateso-dict/word-of-the-day',
					attributes: attributes,
				} )
			);
		},

		save: function () {
			// Server-side rendered — no save markup.
			return null;
		},
	} );
} )();
