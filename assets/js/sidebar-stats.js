/**
 * WPAMesh Sidebar Stats - AJAX Lazy Loading
 *
 * Fetches sidebar statistics via AJAX and updates stat boxes with fresh data.
 * This allows the page to load immediately with cached/placeholder values,
 * then updates asynchronously without blocking.
 *
 * @package wpamesh-theme
 */

( function() {
    'use strict';

    // Map stat types to their data keys
    var statKeyMap = {
        'total_nodes': 'total_nodes',
        'active_nodes': 'active_nodes',
        'routers': 'routers',
        'packets_24h': 'packets_24h',
        'channel_utilization': 'channel_utilization',
        'air_util_tx': 'air_util_tx'
    };

    /**
     * Fetch stats from AJAX endpoint and update stat boxes
     */
    function refreshStats() {
        // Check if we have the localized data
        if ( typeof wpameshStats === 'undefined' || ! wpameshStats.ajaxUrl ) {
            return;
        }

        // Find all stat boxes on the page
        var statBoxes = document.querySelectorAll( '.wpamesh-stat-box[data-stat]' );
        if ( statBoxes.length === 0 ) {
            return;
        }

        // Make AJAX request
        var xhr = new XMLHttpRequest();
        xhr.open( 'POST', wpameshStats.ajaxUrl, true );
        xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );

        xhr.onreadystatechange = function() {
            if ( xhr.readyState !== 4 ) {
                return;
            }

            if ( xhr.status !== 200 ) {
                console.error( 'WPAMesh: Failed to fetch stats', xhr.status );
                return;
            }

            try {
                var response = JSON.parse( xhr.responseText );
                if ( response.success && response.data ) {
                    updateStatBoxes( statBoxes, response.data );
                }
            } catch ( e ) {
                console.error( 'WPAMesh: Failed to parse stats response', e );
            }
        };

        xhr.send( 'action=' + encodeURIComponent( wpameshStats.action ) );
    }

    /**
     * Update stat box elements with new data
     *
     * @param {NodeList} statBoxes - Stat box elements to update
     * @param {Object} data - Stats data from AJAX response
     */
    function updateStatBoxes( statBoxes, data ) {
        statBoxes.forEach( function( box ) {
            var statType = box.getAttribute( 'data-stat' );
            var dataKey = statKeyMap[ statType ];

            if ( ! dataKey || ! data.hasOwnProperty( dataKey ) ) {
                return;
            }

            var valueEl = box.querySelector( '.number' );
            if ( valueEl ) {
                valueEl.textContent = data[ dataKey ];
                // Add a subtle animation to indicate update
                valueEl.classList.add( 'wpamesh-stat-updated' );
                setTimeout( function() {
                    valueEl.classList.remove( 'wpamesh-stat-updated' );
                }, 300 );
            }
        });
    }

    // Run on DOM ready
    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', refreshStats );
    } else {
        refreshStats();
    }

    // Optionally refresh periodically (every 5 minutes)
    setInterval( refreshStats, 5 * 60 * 1000 );

} )();
