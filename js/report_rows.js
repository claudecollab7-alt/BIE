// 2026-10-26 big report tables: the rows wait in <template id="rpt_rows"> (not laid out, images not loaded) and are
// shown 100 a page with search; Excel / PDF / Print still get every row - they are put back for that click
var fnRptRows = (function () {
	var dt = null, tbody = null, bits = null;

	// columns of a row / header row, colspans counted
	function width(tr) {
		var n = 0;
		$(tr).children('td,th').each(function () { n += parseInt($(this).attr('colspan') || 1, 10); });
		return n;
	}

	function init() {
		var tpl = document.getElementById('rpt_rows');
		if (!tpl) return;
		var table = $(tpl).closest('table');
		tbody = $(tpl).parent();
		var rows = $(tpl.content || tpl).children('tr').toArray();
		$(tpl).remove();
		var cols = width(table.find('thead tr').first());
		// a "no records" row or a row of another width cannot be paged - shown as it is
		var plain = rows.length < 2 || rows.some(function (tr) { return width(tr) != cols; });
		if (plain || !$.fn.DataTable) {
			tbody.append(rows);
			return;
		}
		dt = table.DataTable({
			order: [],
			pageLength: 100,
			lengthMenu: [[100, 500, 1000, -1], [100, 500, 1000, 'All']],
			responsive: false,
			processing: false
		});
		dt.rows.add(rows).draw();
		bits = table.closest('.dataTables_wrapper').find('.datatable-header, .datatable-footer');

		// before the export / print handlers run: every row in its first order, the search / pager hidden
		document.addEventListener('click', function (e) {
			if (dt && $(e.target).closest('.rpt_export, .rpt_pdf, .rpt_print').length) {
				all();
				setTimeout(paged, 1500);
			}
		}, true);
	}

	function all() {
		bits.hide();
		tbody.empty().append(dt.rows({ order: 'index' }).nodes().toArray());
	}

	function paged() {
		bits.show();
		dt.draw(false);
	}

	$(init);
	return { all: all, paged: paged };
})();
