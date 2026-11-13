/**
 * ABEX Management Services — front-end motion.
 *
 * Adds `is-in` to `.abex-reveal` / `.abex-wipe` elements as they enter the
 * viewport, which drives the scroll-linked reveals from the design files.
 * Purely additive: with JS off, or reduced-motion on, everything is visible.
 */
( function () {
	'use strict';

	var SELECTOR = '.abex-reveal, .abex-wipe, .abex-meter, .abex-ring';

	function showAll( nodes ) {
		for ( var i = 0; i < nodes.length; i++ ) {
			nodes[ i ].classList.add( 'is-in' );
		}
	}

	function init() {
		var nodes = document.querySelectorAll( SELECTOR );
		if ( ! nodes.length ) {
			return;
		}

		var reduced = window.matchMedia &&
			window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

		if ( reduced || ! ( 'IntersectionObserver' in window ) ) {
			showAll( nodes );
			return;
		}

		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( ! entry.isIntersecting ) {
					return;
				}
				entry.target.classList.add( 'is-in' );
				io.unobserve( entry.target );
			} );
		}, { rootMargin: '0px 0px -12% 0px', threshold: 0.15 } );

		for ( var i = 0; i < nodes.length; i++ ) {
			io.observe( nodes[ i ] );
		}

		// Safety net: full-page screenshot tools and some slow devices never
		// scroll, so the observer may never fire. Reveal anything still hidden
		// rather than leave content invisible.
		window.setTimeout( function () {
			showAll( document.querySelectorAll( SELECTOR + ':not(.is-in)' ) );
		}, 2500 );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

	// Elementor re-renders widgets in the editor; re-scan so previews animate.
	window.addEventListener( 'elementor/frontend/init', function () {
		if ( window.elementorFrontend && window.elementorFrontend.isEditMode() ) {
			showAll( document.querySelectorAll( SELECTOR ) );
		}
	} );
}() );

/**
 * ABEX Management Services — the "How We Work" panel stack.
 *
 * The three panels pin one above the next as you scroll. The one underneath
 * scales down and dims as the next covers it, which is what gives the stack
 * its sense of depth. Scale and dim are published as custom properties so the
 * stylesheet owns the look and this file only owns the arithmetic.
 *
 * Ported from the scroll maths in ABEX_Home_v3.dc.html. Below 1025px, on short
 * viewports, or with reduced motion on, the panels stay in normal flow.
 */
( function () {
	'use strict';

	var BASE_TOP = [ 100, 116, 132 ];

	function clamp( v ) {
		return Math.max( 0, Math.min( 1, v ) );
	}

	function init( stack ) {
		var panels = Array.prototype.slice.call(
			stack.querySelectorAll( '.abex-panel' )
		);
		if ( panels.length < 2 ) {
			return;
		}

		var raf = 0;

		// The list items animate from JS, so they carry their easing from here.
		panels.forEach( function ( p ) {
			var items = p.querySelectorAll( 'li' );
			for ( var i = 0; i < items.length; i++ ) {
				items[ i ].style.transition = 'opacity .4s ease, transform .4s ease';
			}
		} );

		/**
		 * Draw a panel's contents at its own scroll progress.
		 *
		 * Each panel holds one kind of indicator, so one pass covers all three:
		 * the activities list fades in item by item, the numbered rings draw their
		 * arcs in turn, and the scope meters fill from the left. The thresholds and
		 * easing are the ones in ABEX_Home_v3.dc.html.
		 *
		 * @param {Element} panel The panel being drawn.
		 * @param {number}  r     0 to 1, how far it has travelled into its pin.
		 */
		function paint( panel, r ) {
			var items = panel.querySelectorAll( 'li' );
			for ( var i = 0; i < items.length; i++ ) {
				var on = r > 0.25 + i * 0.07;
				items[ i ].style.opacity = on ? '1' : '0.15';
				items[ i ].style.transform = on ? 'none' : 'translateY(10px)';
			}

			var arcs = panel.querySelectorAll( '.abex-ring__arc' );
			for ( var a = 0; a < arcs.length; a++ ) {
				// 170 is the dasharray set on the arc in assets/abex.css.
				arcs[ a ].style.strokeDashoffset = Math.round(
					170 * ( 1 - clamp( ( r - 0.3 - a * 0.12 ) / 0.4 ) )
				);
			}

			var fills = panel.querySelectorAll( '.abex-meter__fill' );
			for ( var f = 0; f < fills.length; f++ ) {
				// Each bar carries its own full width as --w.
				var full = parseFloat(
					fills[ f ].style.getPropertyValue( '--w' )
				);
				if ( ! isFinite( full ) ) { full = 100; }
				fills[ f ].style.width =
					( full * clamp( ( r - 0.3 ) / 0.5 ) ).toFixed( 1 ) + '%';
			}
		}

		function clear() {
			panels.forEach( function ( p ) {
				p.style.removeProperty( '--abex-s' );
				p.style.removeProperty( '--abex-dim' );
				p.style.removeProperty( 'top' );

				// Hand the contents back to the plain reveal in assets/abex.css.
				var reset = p.querySelectorAll(
					'li, .abex-ring__arc, .abex-meter__fill'
				);
				for ( var n = 0; n < reset.length; n++ ) {
					reset[ n ].style.removeProperty( 'opacity' );
					reset[ n ].style.removeProperty( 'transform' );
					reset[ n ].style.removeProperty( 'stroke-dashoffset' );
					reset[ n ].style.removeProperty( 'width' );
				}
			} );
		}

		function measure() {
			raf = 0;

			var vh = window.innerHeight;
			if ( window.innerWidth <= 1024 || vh < 480 ) {
				clear();
				return;
			}

			var tops = [];
			var stick = [];

			panels.forEach( function ( p, i ) {
				tops.push( p.getBoundingClientRect().top );
				// A panel taller than the viewport pins at a negative offset so
				// its foot still reaches the bottom of the screen.
				stick.push( Math.round( Math.min(
					BASE_TOP[ i ] === undefined ? 132 : BASE_TOP[ i ],
					vh - p.offsetHeight - 16
				) ) );
				p.style.top = stick[ i ] + 'px';
			} );

			panels.forEach( function ( p, i ) {
				// How far this panel has travelled into its pinned position.
				var r = clamp(
					( vh - tops[ i ] ) / Math.max( 1, vh - Math.max( stick[ i ], 0 ) )
				);
				paint( p, r );
				// How far the next panel has covered it.
				var shade = i < panels.length - 1
					? clamp( 1 - ( tops[ i + 1 ] - stick[ i + 1 ] ) / ( vh * 0.6 ) )
					: 0;

				p.style.setProperty(
					'--abex-s',
					( ( 0.9 + 0.1 * r ) * ( 1 - shade * 0.05 ) ).toFixed( 4 )
				);
				p.style.setProperty(
					'--abex-dim',
					( Math.round( shade * 45 ) / 100 ).toFixed( 2 )
				);
			} );
		}

		function onScroll() {
			if ( ! raf ) {
				raf = window.requestAnimationFrame( measure );
			}
		}

		window.addEventListener( 'scroll', onScroll, { passive: true } );
		window.addEventListener( 'resize', onScroll );
		measure();
	}

	function boot() {
		if ( window.matchMedia &&
			window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			return;
		}
		var stacks = document.querySelectorAll( '.abex-stack' );
		for ( var i = 0; i < stacks.length; i++ ) {
			init( stacks[ i ] );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
}() );
