<?php
require_once(dirname(__FILE__) . '/error_log.php');   // fnLogError() must exist everywhere
// 2026-11-11 the logged-in user is set on the connection (@bie_user_id) - the table triggers fill created_by / modify_by from it

// tell the database who is working, for the created / modified by triggers (db/post_upload_111126_audit.sql)
function fnDbSetUser($pdo)
{
	if ($pdo && session_status() == PHP_SESSION_ACTIVE && !empty($_SESSION['_user_id'])) {
		$pdo->exec("SET @bie_user_id = " . (int)$_SESSION['_user_id']);
		return true;
	}
	return false;
}

class dbconnect extends PDO
{
    public function __construct()
    {
		try
		{

			require_once dirname(__FILE__) . '/config.php';		

			parent::__construct("mysql:host=".DB_HOST.";dbname=".DB_NAME,DB_USERNAME ,DB_PASSWORD );

			$this->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$this->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

			// always disable emulated prepared statement when using the MySQL driver

			$this->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

			fnDbSetUser($this);

		}
		catch(PDOException  $e)
		{
			fnLogError($e);
			echo "Error Connecting Host :" .$e->getMessage();
		}
		catch(Exception  $e)
		{
			fnLogError($e);
			echo $e->getMessage();
		}
    }
}
?>

