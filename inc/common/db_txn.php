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
