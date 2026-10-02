<?php
// Error logging. Call fnLogError($e) as the first line of every catch block.
// Writes logs/error_YYYY-MM.log - one file per month.

if (!defined('BIE_ERROR_LOG_LOADED')) {
define('BIE_ERROR_LOG_LOADED', 1);

define('BIE_LOG_DIR', dirname(dirname(dirname(__FILE__))) . '/logs');

// Keeps the log directory present and not readable over the web.
function fnLogDir()
{
	$dir = BIE_LOG_DIR;
	if (!is_dir($dir)) {
		@mkdir($dir, 0775, true);
	}
	$deny = $dir . '/.htaccess';
	if (is_dir($dir) && !file_exists($deny)) {
		@file_put_contents($deny, "Require all denied\nDeny from all\n");
	}
	return $dir;
}

/**
 * Writes one line per error: date | file:line | page | user | message.
 * $e may be an Exception or a plain message string.
 * Never throws - a failed log must not break the page.
 */
function fnLogError($e, $note = '')
{
	try {
		if (is_object($e) && method_exists($e, 'getMessage')) {
			$msg  = $e->getMessage();
			$file = basename($e->getFile());
			$line = $e->getLine();
		} else {
			$msg  = (string)$e;
			$bt   = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
			$file = isset($bt[0]['file']) ? basename($bt[0]['file']) : '-';
			$line = isset($bt[0]['line']) ? $bt[0]['line'] : 0;
		}

		$page = isset($_SERVER['SCRIPT_NAME']) ? basename($_SERVER['SCRIPT_NAME']) : 'cli';
		$user = isset($_SESSION['_user_id']) ? $_SESSION['_user_id'] : '-';
		$name = isset($_SESSION['_user_name']) ? $_SESSION['_user_name'] : '';

		// keep it to one line so the log stays greppable
		$msg = trim(preg_replace('/\s+/', ' ', $msg));
		if ($note != '') {
			$msg = $note . ' - ' . $msg;
		}

		$row = sprintf("%s | %s:%s | %s | user %s %s | %s" . PHP_EOL,
			date('Y-m-d H:i:s'), $file, $line, $page, $user, $name, $msg);

		@file_put_contents(fnLogDir() . '/error_' . date('Y-m') . '.log', $row, FILE_APPEND | LOCK_EX);
	} catch (Exception $ignored) {
		// logging must never break the page
	}
}

}
