<?php
require_once(dirname(__FILE__) . '/error_log.php');   // fnLogError() must exist everywhere

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

