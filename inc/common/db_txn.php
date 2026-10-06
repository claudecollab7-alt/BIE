<?php
/* Transaction helpers. Safe to call more than once - each is a no-op if there is nothing to do. */

function db_begin($conn)
{
	if (!$conn->inTransaction()) {
		$conn->beginTransaction();
	}
}

function db_commit($conn)
{
	if ($conn->inTransaction()) {
		$conn->commit();
	}
}

function db_rollback($conn)
{
	if ($conn->inTransaction()) {
		$conn->rollBack();
	}
}

// one value, read on the connection doing the work - $dbconn is a second connection and
// cannot see rows this transaction has written until it commits
function db_value($conn, $sql, $params = array())
{
	$stmt = $conn->prepare($sql);
	$stmt->execute($params);
	$v = $stmt->fetchColumn();
	return ($v === false) ? '' : $v;
}
