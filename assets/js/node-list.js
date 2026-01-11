/**
 * WPAMesh Node List - AJAX Lazy Loading
 *
 * Fetches node status data via AJAX and updates:
 * - Node list items (.wpamesh-node-item) - uses bulk endpoint
 * - Node page headers (.wpamesh-node-page-header) - uses single-node endpoint
 *
 * This allows the page to load immediately with basic node info,
 * then hydrates with live status asynchronously.
 *
 * @package wpamesh-theme
 */

( function() {
    'use strict';

    /**
     * Fetch node data from AJAX endpoint and update node elements
     */
    function refreshNodeData() {
        // Check if we have the localized data
        if ( typeof wpameshNodes === 'undefined' || ! wpameshNodes.ajaxUrl ) {
            return;
        }

        // Find all node elements with data-node-id attribute
        var nodeItems = document.querySelectorAll( '.wpamesh-node-item[data-node-id]' );
        var nodeHeaders = document.querySelectorAll( '.wpamesh-node-page-header[data-node-id]' );

        // Update node list items using bulk endpoint (if any exist)
        if ( nodeItems.length > 0 ) {
            refreshNodeListItems( nodeItems );
        }

        // Update node headers using single-node endpoint (more efficient)
        if ( nodeHeaders.length > 0 ) {
            refreshNodeHeaders( nodeHeaders );
        }
    }

    /**
     * Refresh node list items using bulk endpoint
     *
     * @param {NodeList} nodeItems - Node item elements to update
     */
    function refreshNodeListItems( nodeItems ) {
        var xhr = new XMLHttpRequest();
        xhr.open( 'POST', wpameshNodes.ajaxUrl, true );
        xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );

        xhr.onreadystatechange = function() {
            if ( xhr.readyState !== 4 ) {
                return;
            }

            if ( xhr.status !== 200 ) {
                console.error( 'WPAMesh: Failed to fetch node list data', xhr.status );
                return;
            }

            try {
                var response = JSON.parse( xhr.responseText );
                if ( response.success && response.data ) {
                    updateNodeItems( nodeItems, response.data );
                }
            } catch ( e ) {
                console.error( 'WPAMesh: Failed to parse node list response', e );
            }
        };

        xhr.send( 'action=' + encodeURIComponent( wpameshNodes.action ) );
    }

    /**
     * Refresh node headers using single-node endpoint (O(1) lookup per node)
     *
     * @param {NodeList} nodeHeaders - Node header elements to update
     */
    function refreshNodeHeaders( nodeHeaders ) {
        nodeHeaders.forEach( function( header ) {
            var nodeId = header.getAttribute( 'data-node-id' );
            if ( ! nodeId ) {
                return;
            }

            var xhr = new XMLHttpRequest();
            xhr.open( 'POST', wpameshNodes.ajaxUrl, true );
            xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );

            xhr.onreadystatechange = function() {
                if ( xhr.readyState !== 4 ) {
                    return;
                }

                if ( xhr.status !== 200 ) {
                    console.error( 'WPAMesh: Failed to fetch single node data', xhr.status );
                    return;
                }

                try {
                    var response = JSON.parse( xhr.responseText );
                    if ( response.success && response.data ) {
                        updateSingleNodeHeader( header, response.data );
                    }
                } catch ( e ) {
                    console.error( 'WPAMesh: Failed to parse single node response', e );
                }
            };

            xhr.send(
                'action=' + encodeURIComponent( wpameshNodes.singleAction ) +
                '&node_id=' + encodeURIComponent( nodeId )
            );
        });
    }

    /**
     * Update node item elements with new data
     *
     * @param {NodeList} nodeItems - Node item elements to update
     * @param {Object} data - Node data from AJAX response, keyed by node_id
     */
    function updateNodeItems( nodeItems, data ) {
        nodeItems.forEach( function( item ) {
            var nodeId = item.getAttribute( 'data-node-id' );
            var nodeData = data[ nodeId ];

            if ( ! nodeData ) {
                return;
            }

            // Update status dot
            var statusDot = item.querySelector( '.wpamesh-status-dot' );
            if ( statusDot ) {
                // Update class
                statusDot.classList.remove( 'online', 'offline', 'unknown' );
                if ( nodeData.is_online === true ) {
                    statusDot.classList.add( 'online' );
                    statusDot.setAttribute( 'title', 'Online' );
                } else if ( nodeData.is_online === false ) {
                    statusDot.classList.add( 'offline' );
                    statusDot.setAttribute( 'title', 'Offline' );
                } else {
                    statusDot.classList.add( 'unknown' );
                    statusDot.setAttribute( 'title', 'Unknown' );
                }
            } else if ( nodeData.is_online !== null ) {
                // Create status dot if it doesn't exist but we have data
                var h4 = item.querySelector( 'h4' );
                if ( h4 ) {
                    var newDot = document.createElement( 'span' );
                    newDot.className = 'wpamesh-status-dot ' + ( nodeData.is_online ? 'online' : 'offline' );
                    newDot.setAttribute( 'title', nodeData.is_online ? 'Online' : 'Offline' );
                    h4.insertBefore( newDot, h4.firstChild );
                }
            }

            // Update or create metrics section
            var metricsDiv = item.querySelector( '.wpamesh-node-metrics' );
            if ( nodeData.channel_util || nodeData.air_util ) {
                if ( ! metricsDiv ) {
                    // Create metrics div if it doesn't exist
                    metricsDiv = document.createElement( 'div' );
                    metricsDiv.className = 'wpamesh-node-metrics';
                    item.appendChild( metricsDiv );
                }

                // Update metrics content
                var metricsHtml = '';
                if ( nodeData.channel_util ) {
                    metricsHtml += '<span class="metric" title="Channel Utilization">' + nodeData.channel_util + ' <span class="label">Ch</span></span>';
                }
                if ( nodeData.air_util ) {
                    metricsHtml += '<span class="metric" title="Airtime TX">' + nodeData.air_util + ' <span class="label">Air</span></span>';
                }
                metricsDiv.innerHTML = metricsHtml;
            }

            // Add subtle animation to indicate update
            item.classList.add( 'wpamesh-node-updated' );
            setTimeout( function() {
                item.classList.remove( 'wpamesh-node-updated' );
            }, 300 );
        });
    }

    /**
     * Update a single node page header with new data
     *
     * @param {Element} header - Node header element to update
     * @param {Object} nodeData - Node data from single-node AJAX response
     */
    function updateSingleNodeHeader( header, nodeData ) {
        // Update online/offline class on the header
        header.classList.remove( 'wpamesh-node-online', 'wpamesh-node-offline' );
        if ( nodeData.is_online === true ) {
            header.classList.add( 'wpamesh-node-online' );
        } else if ( nodeData.is_online === false ) {
            header.classList.add( 'wpamesh-node-offline' );
        }

        // Update status badge
        var statusBadge = header.querySelector( '.wpamesh-node-status' );
        if ( statusBadge ) {
            statusBadge.classList.remove( 'online', 'offline' );
            if ( nodeData.is_online === true ) {
                statusBadge.classList.add( 'online' );
                statusBadge.textContent = 'Online';
            } else if ( nodeData.is_online === false ) {
                statusBadge.classList.add( 'offline' );
                statusBadge.textContent = 'Offline';
            }
        } else if ( nodeData.is_online !== null ) {
            // Create status badge if it doesn't exist
            var metaDiv = header.querySelector( '.wpamesh-node-meta' );
            if ( metaDiv ) {
                var newBadge = document.createElement( 'span' );
                newBadge.className = 'wpamesh-badge wpamesh-node-status ' + ( nodeData.is_online ? 'online' : 'offline' );
                newBadge.textContent = nodeData.is_online ? 'Online' : 'Offline';
                metaDiv.appendChild( newBadge );
            }
        }

        // Update last seen text
        var lastSeenSpan = header.querySelector( '.wpamesh-node-last-seen' );
        if ( nodeData.is_online === false && nodeData.last_seen ) {
            if ( lastSeenSpan ) {
                lastSeenSpan.textContent = nodeData.last_seen;
            } else {
                var metaDiv = header.querySelector( '.wpamesh-node-meta' );
                if ( metaDiv ) {
                    var newLastSeen = document.createElement( 'span' );
                    newLastSeen.className = 'wpamesh-node-last-seen';
                    newLastSeen.textContent = nodeData.last_seen;
                    metaDiv.appendChild( newLastSeen );
                }
            }
        } else if ( lastSeenSpan ) {
            // Remove last seen if node is online
            lastSeenSpan.remove();
        }

        // Update channel utilization metrics (right side of header)
        var metricsDiv = header.querySelector( '.wpamesh-node-page-metrics' );
        var channelUtil = header.querySelector( '.wpamesh-channel-util' );

        if ( nodeData.channel_util && nodeData.load_level ) {
            var loadTitle = 'Channel Utilization: ' + nodeData.channel_util + ' (' + nodeData.load_label + ' load)';

            if ( channelUtil ) {
                // Update existing element
                channelUtil.classList.remove( 'wpamesh-load-low', 'wpamesh-load-elevated', 'wpamesh-load-high' );
                channelUtil.classList.add( 'wpamesh-load-' + nodeData.load_level );
                channelUtil.setAttribute( 'title', loadTitle );

                var valueSpan = channelUtil.querySelector( '.value' );
                var labelSpan = channelUtil.querySelector( '.label' );
                if ( valueSpan ) valueSpan.textContent = nodeData.channel_util;
                if ( labelSpan ) labelSpan.textContent = nodeData.load_label;
            } else {
                // Create metrics section if it doesn't exist
                if ( ! metricsDiv ) {
                    metricsDiv = document.createElement( 'div' );
                    metricsDiv.className = 'wpamesh-node-page-metrics';
                    header.appendChild( metricsDiv );
                }

                var newChannelUtil = document.createElement( 'span' );
                newChannelUtil.className = 'wpamesh-channel-util wpamesh-load-' + nodeData.load_level;
                newChannelUtil.setAttribute( 'title', loadTitle );
                newChannelUtil.innerHTML = '<span class="value">' + nodeData.channel_util + '</span>' +
                    '<span class="label">' + nodeData.load_label + '</span>';
                metricsDiv.appendChild( newChannelUtil );
            }
        } else if ( metricsDiv && ! nodeData.channel_util ) {
            // Remove metrics section if no data
            metricsDiv.remove();
        }
    }

    // Run on DOM ready
    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', refreshNodeData );
    } else {
        refreshNodeData();
    }

    // Optionally refresh periodically (every 5 minutes)
    setInterval( refreshNodeData, 5 * 60 * 1000 );

} )();
