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
