/**
 * Persian Live Gold Price for WooCommerce
 * Frontend JavaScript Handler
 */

(function() {
	'use strict';

	var config = (typeof live_gold_price_data !== 'undefined') ? live_gold_price_data : ((typeof lgp_data !== 'undefined') ? lgp_data : null);
	if (!config || !config.rest_url) {
		return;
	}

	function fetchProductPrices(productIds, callback) {
		if (!productIds || productIds.length === 0) {
			return;
		}

		var separator = config.rest_url.indexOf('?') === -1 ? '?' : '&';
		var url = config.rest_url + separator + 'ids=' + productIds.join(',');

		fetch(url, {
			method: 'GET',
			headers: {
				'X-WP-Nonce': config.nonce,
				'Accept': 'application/json'
			}
		})
		.then(function(response) {
			if (!response.ok) {
				throw new Error('Network error fetching live gold prices');
			}
			return response.json();
		})
		.then(function(data) {
			if (typeof callback === 'function') {
				callback(data);
			}
		})
		.catch(function(err) {
			// Non-blocking console notice for troubleshooting
			if (window.console && console.warn) {
				console.warn('Live Gold Price sync notice:', err.message);
			}
		});
	}

	function updateWrappers(wrappers, data) {
		if (!wrappers || !data) {
			return;
		}
		for (var i = 0; i < wrappers.length; i++) {
			var wrapper = wrappers[i];
			var id = wrapper.getAttribute('data-product-id');
			if (id && Object.prototype.hasOwnProperty.call(data, id)) {
				wrapper.innerHTML = data[id];
			}
		}
	}

	function initLivePrices() {
		var priceWrappers = document.querySelectorAll('.live-gold-price-wrapper, .lgp-live-price-wrapper');
		if (priceWrappers.length === 0) {
			return;
		}

		var productIds = [];
		for (var i = 0; i < priceWrappers.length; i++) {
			var id = priceWrappers[i].getAttribute('data-product-id');
			if (id && productIds.indexOf(id) === -1) {
				productIds.push(id);
			}
		}

		if (productIds.length > 0) {
			fetchProductPrices(productIds, function(data) {
				updateWrappers(priceWrappers, data);
			});
		}
	}

	// Trigger immediately if DOM is already ready, or listen to DOMContentLoaded
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initLivePrices);
	} else {
		initLivePrices();
	}

	if (typeof jQuery !== 'undefined') {
		var $ = jQuery;

		function fetchAndReplaceLivePrices() {
			var newWrappers = document.querySelectorAll('.live-gold-price-wrapper:not(.live-gold-price-updated), .lgp-live-price-wrapper:not(.live-gold-price-updated)');
			if (newWrappers.length === 0) {
				return;
			}

			var newIds = [];
			for (var i = 0; i < newWrappers.length; i++) {
				var wrapper = newWrappers[i];
				var id = wrapper.getAttribute('data-product-id');
				if (id && newIds.indexOf(id) === -1) {
					newIds.push(id);
				}
				wrapper.classList.add('live-gold-price-updated');
				wrapper.classList.add('lgp-updated');
			}

			if (newIds.length > 0) {
				fetchProductPrices(newIds, function(data) {
					var allWrappers = document.querySelectorAll('.live-gold-price-wrapper, .lgp-live-price-wrapper');
					updateWrappers(allWrappers, data);
				});
			}
		}

		$(document).on('ajaxComplete', function(event, xhr, settings) {
			if (settings && settings.url && (settings.url.indexOf('admin-ajax.php') !== -1 || settings.url.indexOf('wc-ajax') !== -1)) {
				setTimeout(fetchAndReplaceLivePrices, 200);
			}
		});

		var originalTopPrice = null;

		$(document).on('show_variation', function(event, variation) {
			if (variation && variation.variation_id) {
				var vid = variation.variation_id;
				var container = document.querySelector('.woocommerce-variation-price');
				var topPriceContainer = document.querySelector('div.summary p.price');

				if (topPriceContainer && !originalTopPrice) {
					originalTopPrice = topPriceContainer.innerHTML;
				}

				var skeletonHTML = '<div class="live-gold-price-skeleton lgp-loading-skeleton"></div>';
				if (container) {
					container.innerHTML = skeletonHTML;
					container.style.display = 'block';
				}
				if (topPriceContainer) {
					topPriceContainer.innerHTML = skeletonHTML;
				}

				fetchProductPrices([vid], function(data) {
					if (data && data[vid]) {
						var livePriceHTML = '<span class="live-gold-price-wrapper lgp-live-price-wrapper live-gold-price-updated lgp-updated" data-product-id="' + vid + '">' + data[vid] + '</span>';
						if (container) {
							container.innerHTML = livePriceHTML;
						}
						if (topPriceContainer) {
							topPriceContainer.innerHTML = livePriceHTML;
						}
					}
				});
			}
		});

		$(document).on('hide_variation', function() {
			if (originalTopPrice) {
				var topPriceContainer = document.querySelector('div.summary p.price');
				if (topPriceContainer) {
					topPriceContainer.innerHTML = originalTopPrice;
				}
			}
		});

		// Auto refresh every 60 seconds
		setInterval(function() {
			var allWrappers = document.querySelectorAll('.live-gold-price-wrapper, .lgp-live-price-wrapper');
			if (allWrappers.length > 0) {
				for (var i = 0; i < allWrappers.length; i++) {
					allWrappers[i].classList.remove('live-gold-price-updated');
					allWrappers[i].classList.remove('lgp-updated');
				}
				fetchAndReplaceLivePrices();
			}

			if (document.querySelector('.woocommerce-cart-form')) {
				$(document.body).trigger('wc_update_cart');
			}
			if (document.querySelector('form.checkout')) {
				$(document.body).trigger('update_checkout');
			}
		}, 60000);
	}
})();
