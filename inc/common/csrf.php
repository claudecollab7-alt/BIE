<?php
/* CSRF + single-use form tokens. Needs an active session. See docs/CHANGELOG.md */

define('FORM_TOKEN_KEEP', 5);   // tokens held per form, so the same form works in several tabs

// One CSRF token per session, reused across forms/tabs.
function generate_csrf_token()
{
	if (empty($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}
	return $_SESSION['csrf_token'];
}

function verify_csrf_token($token)
{
	if (!$token || empty($_SESSION['csrf_token'])) {
		return false;
	}
	return hash_equals($_SESSION['csrf_token'], $token);
}

// Single-use per-form tokens (duplicate-submission guard).
function generate_form_token($form_name = 'default')
{
	$token = bin2hex(random_bytes(32));

	if (!isset($_SESSION['form_tokens'][$form_name]) || !is_array($_SESSION['form_tokens'][$form_name])) {
		$_SESSION['form_tokens'][$form_name] = array();
	}
	$_SESSION['form_tokens'][$form_name][] = $token;

	// keep only the newest few
	if (count($_SESSION['form_tokens'][$form_name]) > FORM_TOKEN_KEEP) {
		$_SESSION['form_tokens'][$form_name] = array_slice($_SESSION['form_tokens'][$form_name], -FORM_TOKEN_KEEP);
	}
	return $token;
}

function validate_form_token($form_name, $token)
{
	if (!$token || empty($_SESSION['form_tokens'][$form_name])) {
		return false;
	}

	foreach ($_SESSION['form_tokens'][$form_name] as $i => $stored) {
		if (hash_equals($stored, $token)) {
			unset($_SESSION['form_tokens'][$form_name][$i]);   // single-use
			$_SESSION['form_tokens'][$form_name] = array_values($_SESSION['form_tokens'][$form_name]);
			return true;
		}
	}
	return false;
}

// Hidden inputs for a form. Call inside <form>.
function csrf_fields($form_name = 'default')
{
	echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generate_csrf_token()) . '">' . "\n";
	echo '<input type="hidden" name="form_token" value="' . htmlspecialchars(generate_form_token($form_name)) . '">' . "\n";
}

// Both checks in one. Call first thing in every POST handler.
function csrf_check($form_name = 'default')
{
	$csrf = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null;
	$form = isset($_POST['form_token']) ? $_POST['form_token'] : null;

	return verify_csrf_token($csrf) && validate_form_token($form_name, $form);
}

// Stale form / repeat submit: warn and send the user back.
function csrf_fail($redirect_to)
{
	$_SESSION['_msg_err'] = "This form was already submitted or your session expired. Please try again.";
	header("location:" . $redirect_to);
	die();
}

// CSRF-only check for ajax posts (no single-use token, ajax can repeat).
// Token arrives as a post field or as the X-CSRF-Token header.
function csrf_check_ajax()
{
	$csrf = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null;
	if (!$csrf && isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
		$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'];
	}
	return verify_csrf_token($csrf);
}

// <meta> tag so javascript can attach the token to ajax posts.
function csrf_meta()
{
	echo '<meta name="csrf-token" content="' . htmlspecialchars(generate_csrf_token()) . '">' . "\n";
}
