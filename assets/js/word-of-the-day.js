/**
 * Word of the Day — Frontend Modal.
 *
 * Fetches the full word entry via REST API and displays it in
 * an accessible modal dialog (focus trap, ESC to close, ARIA).
 * Falls back to page link when JS is unavailable.
 */
( function () {
	'use strict';

	var config = window.atesoDictWotdConfig || {};
	var restUrl = config.restUrl || '/wp-json/dictionary/v1';
	var dictUrl = config.dictionaryUrl || '/dictionary/';

	/**
	 * Escape HTML to prevent XSS.
	 */
	function esc( str ) {
		if ( ! str ) return '';
		var div = document.createElement( 'div' );
		div.textContent = str;
		return div.innerHTML;
	}

	/**
	 * Create the modal overlay element (appended once to body).
	 */
	function createModalOverlay() {
		var overlay = document.createElement( 'div' );
		overlay.className = 'ateso-wotd-modal-overlay';
		overlay.setAttribute( 'role', 'presentation' );
		overlay.innerHTML =
			'<div class="ateso-wotd-modal" role="dialog" aria-modal="true" aria-labelledby="ateso-wotd-modal-title" tabindex="-1">' +
				'<button type="button" class="ateso-wotd-modal-close" aria-label="Close">&times;</button>' +
				'<div class="ateso-wotd-modal-body"></div>' +
			'</div>';

		document.body.appendChild( overlay );

		// Close on overlay click.
		overlay.addEventListener( 'click', function ( e ) {
			if ( e.target === overlay ) {
				closeModal( overlay );
			}
		} );

		// Close on button click.
		overlay.querySelector( '.ateso-wotd-modal-close' ).addEventListener( 'click', function () {
			closeModal( overlay );
		} );

		// Keyboard handling.
		overlay.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' ) {
				closeModal( overlay );
				return;
			}
			// Focus trap.
			if ( e.key === 'Tab' ) {
				trapFocus( overlay.querySelector( '.ateso-wotd-modal' ), e );
			}
		} );

		return overlay;
	}

	/**
	 * Open the modal with loading state, then fetch and render data.
	 */
	function openModal( overlay, slug, triggerEl ) {
		var modal = overlay.querySelector( '.ateso-wotd-modal' );
		var body = overlay.querySelector( '.ateso-wotd-modal-body' );

		// Store the trigger element to restore focus on close.
		overlay._triggerEl = triggerEl;

		// Show loading state.
		body.innerHTML = '<div class="ateso-wotd-modal-loading">Loading&hellip;</div>';

		// Open.
		overlay.classList.add( 'is-open' );
		document.body.style.overflow = 'hidden';
		modal.focus();

		// Fetch full word data.
		fetch( restUrl + '/word/' + encodeURIComponent( slug ), {
			credentials: 'same-origin',
			headers: config.nonce ? { 'X-WP-Nonce': config.nonce } : {},
		} )
			.then( function ( res ) {
				if ( ! res.ok ) throw new Error( 'Not found' );
				return res.json();
			} )
			.then( function ( data ) {
				body.innerHTML = renderModalContent( data );
			} )
			.catch( function () {
				// Fallback: redirect to the word page.
				window.location.href = dictUrl + slug + '/';
			} );
	}

	/**
	 * Close the modal and restore focus.
	 */
	function closeModal( overlay ) {
		overlay.classList.remove( 'is-open' );
		document.body.style.overflow = '';

		// Restore focus to the element that opened the modal.
		if ( overlay._triggerEl ) {
			overlay._triggerEl.focus();
			overlay._triggerEl = null;
		}
	}

	/**
	 * Build the modal body HTML from the REST response.
	 */
	function renderModalContent( data ) {
		var html = '';

		// Word title.
		var homonym = data.homonym_number ? '<sup>' + esc( String( data.homonym_number ) ) + '</sup>' : '';
		html += '<h2 class="ateso-wotd-modal-word" id="ateso-wotd-modal-title">' + esc( data.word ) + homonym + '</h2>';

		// POS.
		if ( data.pos ) {
			html += '<span class="ateso-wotd-modal-pos">' + esc( data.pos );
			if ( data.pos_detail && data.pos_detail !== data.pos ) {
				html += ' <small>(' + esc( data.pos_detail ) + ')</small>';
			}
			html += '</span>';
		}

		// Definitions (numbered list).
		if ( data.definitions && data.definitions.length > 0 ) {
			html += '<ol class="ateso-wotd-modal-defs">';
			for ( var i = 0; i < data.definitions.length; i++ ) {
				html += '<li>' + esc( data.definitions[ i ].text ) + '</li>';
			}
			html += '</ol>';
		}

		// Examples.
		if ( data.examples && data.examples.length > 0 ) {
			html += '<div class="ateso-wotd-modal-examples">';
			html += '<h4>Examples</h4>';
			for ( var j = 0; j < data.examples.length; j++ ) {
				var ex = data.examples[ j ];
				html += '<div class="ateso-wotd-modal-ex-item">';
				html += '<p class="ateso-wotd-modal-ex-ateso">&bull; ' + esc( ex.ateso ) + '</p>';
				html += '<p class="ateso-wotd-modal-ex-english">' + esc( ex.english ) + '</p>';
				html += '</div>';
			}
			html += '</div>';
		}

		// Cross-references.
		var cpRefs = ( data.relations || [] ).filter( function ( r ) {
			return r.type === 'cp';
		} );
		if ( cpRefs.length > 0 ) {
			html += '<div class="ateso-wotd-modal-refs"><strong>See also:</strong> ';
			var links = cpRefs.map( function ( r ) {
				if ( r.slug ) {
					return '<a href="' + esc( dictUrl + r.slug + '/' ) + '">' + esc( r.resolved_word || r.word ) + '</a>';
				}
				return esc( r.word );
			} );
			html += links.join( ', ' );
			html += '</div>';
		}

		// View full entry link.
		html += '<a href="' + esc( data.url ) + '" class="ateso-wotd-modal-link">View full entry &rarr;</a>';

		return html;
	}

	/**
	 * Trap focus within the modal for accessibility.
	 */
	function trapFocus( modal, e ) {
		var focusable = modal.querySelectorAll(
			'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])'
		);
		if ( focusable.length === 0 ) return;

		var first = focusable[ 0 ];
		var last = focusable[ focusable.length - 1 ];

		if ( e.shiftKey ) {
			// Shift+Tab: wrap from first to last.
			if ( document.activeElement === first ) {
				e.preventDefault();
				last.focus();
			}
		} else {
			// Tab: wrap from last to first.
			if ( document.activeElement === last ) {
				e.preventDefault();
				first.focus();
			}
		}
	}

	/**
	 * Initialize: bind click handlers on all WOTD cards.
	 */
	function init() {
		var cards = document.querySelectorAll( '.ateso-wotd-card' );
		if ( cards.length === 0 ) return;

		var overlay = createModalOverlay();

		cards.forEach( function ( card ) {
			var slug = card.dataset.slug;
			if ( ! slug ) return;

			// "See full entry" button.
			var moreBtn = card.querySelector( '.ateso-wotd-more' );
			if ( moreBtn ) {
				moreBtn.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					openModal( overlay, slug, moreBtn );
				} );
			}

			// Word link — open modal instead of navigating.
			var wordLink = card.querySelector( '.ateso-wotd-word-link' );
			if ( wordLink ) {
				wordLink.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					openModal( overlay, slug, wordLink );
				} );
			}
		} );
	}

	// Initialize on DOM ready.
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
