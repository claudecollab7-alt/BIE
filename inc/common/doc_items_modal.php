<?php
// 2026-10-22 new - popup with a document's items and their stage, shared by the sales order and purchase order reports
// a link with class doc-items, data-url (the ajax page) and data-id opens it; pending items by default, Show all for every item
?>
<div id="modalDocItems" class="modal fade" tabindex="-1">
	<div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header pb-2 pt-2 bg-modal">
				<h6 class="modal-title font-weight-bold" id="docItemsTitle">Items</h6>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body py-2" id="docItemsBody"></div>
			<div class="modal-footer pt-2 pb-2 bg-modal">
				<label class="mr-auto mb-0"><input type="checkbox" id="doc_show_all"> Show all items</label>
				<button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<script>
	$(function() {
		function docFilter() {
			var all = $('#doc_show_all').is(':checked');
			$('#docItemTable tr.doc-done').toggle(all);
			$('#docItemTable tr.doc-none').toggle(!all && $('#docItemTable tr.doc-pending').length == 0);
		}
		$('#doc_show_all').on('change', docFilter);
		$(document).on('click', '.doc-items', function(e) {
			e.preventDefault();
			var a = $(this);
			fnPrice.busy(true);
			$.ajax({ type: 'POST', url: a.data('url'), data: { id: a.data('id') } })
				.done(function(html) {
					$('#docItemsTitle').text(a.data('title') || 'Items');
					$('#docItemsBody').html(html);
					$('#doc_show_all').prop('checked', false);
					docFilter();
					$('#modalDocItems').modal('show');
				})
				.fail(function() { alert('Loading the items failed..!'); })
				.always(function() { fnPrice.busy(false); });
		});
	});
</script>
