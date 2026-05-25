/* global ecfiCfwAdmin */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {

		// Confirm dialogs for destructive row action forms.
		document.querySelectorAll( '.ecfi-cfw-row-action-form[data-confirm]' ).forEach( function ( form ) {
			form.addEventListener( 'submit', function ( e ) {
				var message = form.getAttribute( 'data-confirm' );
				if ( message && ! window.confirm( message ) ) {
					e.preventDefault();
				}
			} );
		} );

		// Clipboard copy for "Copy Link" buttons.
		document.querySelectorAll( '.ecfi-copy-link' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var token   = btn.getAttribute( 'data-token' );
				var baseUrl = ( ecfiCfwAdmin && ecfiCfwAdmin.editBaseUrl )
					? ecfiCfwAdmin.editBaseUrl
					: window.location.origin + '/edit/';
				var url = baseUrl + '?token=' + encodeURIComponent( token );

				if ( navigator.clipboard && navigator.clipboard.writeText ) {
					navigator.clipboard.writeText( url ).then( function () {
						markCopied( btn );
					} ).catch( function () {
						fallbackCopy( url );
					} );
				} else {
					fallbackCopy( url );
				}
			} );
		} );

		function markCopied( btn ) {
			btn.classList.add( 'copied' );
			setTimeout( function () {
				btn.classList.remove( 'copied' );
			}, 2000 );
		}

		function fallbackCopy( text ) {
			var el = document.createElement( 'textarea' );
			el.value = text;
			el.style.position = 'fixed';
			el.style.left = '-9999px';
			document.body.appendChild( el );
			el.focus();
			el.select();
			try {
				document.execCommand( 'copy' );
			} catch ( err ) {
				// Silently fail — browser may not support execCommand.
			}
			document.body.removeChild( el );
		}

	} );
}() );
