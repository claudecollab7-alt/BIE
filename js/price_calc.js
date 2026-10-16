// 2026-10-16 item price fields, the same on every screen:
// cost = item price less discount, margin % on cost price; selling price changes only when margin or selling is edited
var fnPrice = {
	num: function (el) {
		var v = parseFloat($(el).val());
		return isNaN(v) ? 0 : v;
	},
	cost: function (price, discount) {
		return Math.round((price - price * discount / 100) * 100) / 100;
	},
	margin: function (selling, cost) {
		return cost > 0 && selling > 0 ? (selling - cost) / cost * 100 : 0;
	},
	selling: function (cost, margin) {
		return cost + cost * margin / 100;
	},
	// small "please wait" over the page while a call or a long redraw runs; calls can overlap
	busyCount: 0,
	busy: function (on) {
		fnPrice.busyCount = Math.max(0, fnPrice.busyCount + (on ? 1 : -1));
		if (on && fnPrice.busyCount == 1) {
			$.blockUI({
				message: '<i class="icon-spinner4 spinner"></i><span class="ml-2">Please wait..</span>',
				fadeIn: 0, fadeOut: 0, baseZ: 2000,
				overlayCSS: { backgroundColor: '#1b2024', opacity: 0.25, cursor: 'wait' },
				css: { border: 0, padding: '10px 18px', color: '#fff', width: 'auto', left: '50%', marginLeft: '-60px', backgroundColor: '#333', borderRadius: '3px' }
			});
		} else if (!on && fnPrice.busyCount == 0) {
			$.unblockUI();
		}
	},
	// f = {price, discount, cost, margin, selling} as selectors or elements; returns the recalc used after values are loaded
	link: function (f) {
		var n = fnPrice.num;
		function fromPrice() {
			var c = fnPrice.cost(n(f.price), n(f.discount));
			$(f.cost).val(c.toFixed(2));
			if ($.trim($(f.selling).val()) !== '') {
				$(f.margin).val(fnPrice.margin(n(f.selling), c).toFixed(2));
			}
		}
		$(f.price).add(f.discount).on('input change', fromPrice);
		$(f.margin).on('input change', function () {
			$(f.selling).val(fnPrice.selling(n(f.cost), n(f.margin)).toFixed(2));
		});
		$(f.selling).on('input change', function () {
			$(f.margin).val(fnPrice.margin(n(f.selling), n(f.cost)).toFixed(2));
		});
		fromPrice();
		return fromPrice;
	}
};
