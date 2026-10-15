// 2026-10-15 revoke button - asks the reason, calls the revoke, shows why when it is refused
$(document).on('click', '.revoke-btn', function (e) {
	e.preventDefault();
	var btn = $(this);
	if (btn.data('busy')) {
		return;
	}
	var reason = prompt('Revoke - this undoes the approve / verify.\nReason for revoking:');
	if (reason === null) {
		return;
	}
	reason = $.trim(reason);
	if (reason === '') {
		alert('Please give the reason for revoking..!');
		return;
	}
	btn.data('busy', 1);
	$.ajax({
		type: 'POST',
		url: 'inc/cis_ajax/jquery_revoke.php',
		data: { doc: btn.data('doc'), id: btn.data('id'), reason: reason }
	}).done(function () {
		window.location.reload();
	}).fail(function (xhr) {
		btn.data('busy', 0);
		alert(xhr.status == 403 ? 'You are not allowed to revoke this..!' : (xhr.responseText || 'Revoke failed - please try again..!'));
	});
});
