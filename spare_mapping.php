<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");
isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$dtm = date('Y-m-d H:i:s');
$by  = $_SESSION['_user_id'];

if (isset($_POST['SAVE']) || isset($_POST['UPDATE'])) {
	try {
		$item_id = $_REQUEST['item_id'];

		$delete_details = "DELETE FROM tbl_spare_mapping WHERE item_id = '" . $item_id . "'";
		$result = $conn->prepare($delete_details);
		$result->execute();

		if ($item_id > 0) {
			$stmt1 = null;
			$stmt1 = $conn->prepare("INSERT INTO tbl_spare_mapping
						(item_id, spare_item_id, created_by, created_dtm)
						VALUES
						(:item_id, :spare_item_id, :created_by, :created_dtm)");

			for ($x = 0; $x < count($_REQUEST['spare_item_id']); $x++) {
				if ($_REQUEST['spare_item_id'][$x] > 0) {
					$data1 = array(
						':item_id'       => $item_id,
						':spare_item_id' => $_REQUEST['spare_item_id'][$x],
						':created_by'    => $by,
						':created_dtm'   => $dtm
					);

					$stmt1->execute($data1);
				}
			}
		}

		if (isset($_POST['SAVE']))
			$_SESSION['_msg'] = "Spare Mapping Successfully Saved..!";
		else
			$_SESSION['_msg'] = "Spare Mapping Successfully Updated..!";
	} catch (Exception $e) {
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		$_SESSION['_msg_err'] = $str;
	}

	header("location:spare_mapping_list.php");
	die();
}

$item_id = '';
$item_desciption = '';

if (isset($_REQUEST['item_id']) && $_REQUEST['item_id'] != "") {
	$result = $conn->query("SELECT * FROM tbl_item_details WHERE item_id = " . $_REQUEST['item_id']);
	if ($result->rowCount() > 0) {
		$obj = $result->fetch(PDO::FETCH_OBJ);
		$item_id = $obj->item_id;
		$item_desciption = $obj->item_desciption;
	}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo PAGE_TITLE; ?> - Spare Mapping</title>

	<?php include_once("inc/common/css-js.php"); ?>
	<script type='text/javascript' src='js/auto/jquery.autocomplete.js'></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css" />

</head>

<body>

	<?php include("inc/common/header.php") ?>

	<div class="page-content">

		<?php include("inc/common/sidebar.php") ?>

		<div class="content-wrapper">

			<div class="page-header">
				<div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
					<div class="d-flex">
						<div class="breadcrumb">
							<a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
							<a href="#" class="breadcrumb-item">Item Master</a>
							<span class="breadcrumb-item active">Spare Mapping</span>
						</div>
					</div>
				</div>
			</div>

			<div class="content pt-0">
				<div class="row">

					<div class="col-md-12">
						<form name="spareForm" method="POST" action="" onsubmit="return fnValidate();">

							<div class="card">
								<div class="card-header bg-pgheader text-white header-elements-inline">
									<h6 class="card-title">Spare Mapping Details</h6>
								</div>

								<div class="card-body">

									<input type="hidden" name="item_id" id="item_id" value="<?php echo $item_id; ?>">

									<div class="row mb-3">
										<div class="col-md-12">
											<label><b>Parent Item</b></label><br>
											<span style="font-size:18px;color:blue;"><?php echo $item_desciption; ?></span>
										</div>
									</div>

									<hr>

									<div class="row">
										<div class="col-md-6">
											<label>Spare Item</label>
											<input type="text" class="form-control" name="item_name" id="item_name" placeholder="Search Spare Item">
											<input type="hidden" name="spare_item_id" id="spare_item_id">
										</div>

										<div class="col-md-2 pt-4">
											<button class="btn btn-warning" type="button" id="add_items">
												<i class="icon-plus2"></i> Add
											</button>
										</div>
									</div>

									<br>

									<div class="table-responsive">
										<table class="table table-bordered table-hover" id="table_po">
											<thead class="bg-table-header">
												<tr>
													<th width="5%">Sl.No</th>
													<th width="15%">Code</th>
													<th>Description</th>
													<th width="10%">Action</th>
												</tr>
											</thead>
											<tbody>
												<?php
												if ($item_id != "") {
													$dets_sql = "SELECT * FROM tbl_spare_mapping WHERE item_id = " . $item_id;
													$result_dets = $conn->query($dets_sql);

													if ($result_dets->rowCount() > 0) {
														$sno = 1;
														while ($itm = $result_dets->fetch()) {
															$item_details = $dbconn->GetSingleReconrd("tbl_item_details", "CONCAT(item_code,'~',item_desciption)", "item_id", $itm->spare_item_id);
															$itm_dets = explode("~", $item_details);

															$delete = '<a href="javascript:void(0);" class="delete"><i class="icon-bin"></i></a>';

															echo '<tr id="' . $itm->spare_item_id . '">';
															echo '<td class="slno text-center">' . $sno . '</td>';
															echo '<td class="text-center">' . $itm_dets[0] . '</td>';
															echo '<td>' . $itm_dets[1] . '<input type="hidden" name="spare_item_id[]" value="' . $itm->spare_item_id . '"></td>';
															echo '<td class="text-center">' . $delete . '</td>';
															echo '</tr>';
															$sno++;
														}
													}
												}
												?>
											</tbody>
										</table>
									</div>

								</div>

								<div class="card-footer text-center">
									<?php if ($item_id != '') { ?>
										<input type="submit" name="UPDATE" value="Update" class="btn btn-custom">
									<?php } else { ?>
										<input type="submit" name="SAVE" value="Save" class="btn btn-custom">
									<?php } ?>
									<input type="button" class="btn btn-light" value="Cancel" onclick="history.go(-1);">
								</div>

							</div>

						</form>
					</div>

				</div>
			</div>

			<?php include("inc/common/footer.php") ?>
		</div>
	</div>

	<script>
		$(document).ready(function() {

			<?php
			if (isset($_SESSION['_msg']) && $_SESSION['_msg'] != "") {
				echo "$.jGrowl('" . $_SESSION['_msg'] . "',{ theme:'alert-styled-left alert-arrow-left alert-success', position:'bottom-right'});";
				$_SESSION['_msg'] = "";
			}
			if (isset($_SESSION['_msg_err']) && $_SESSION['_msg_err'] != "") {
				echo "$.jGrowl('" . $_SESSION['_msg_err'] . "',{ theme:'alert-styled-left alert-arrow-left alert-danger', position:'top-right'});";
				$_SESSION['_msg_err'] = "";
			}
			?>

			$("#item_name").autocomplete({
				minLength: 1,
				source: function(request, response) {
					$.ajax({
						url: "inc/cis_ajax/select_spare_mapping_items.php",
						dataType: "text",
						data: {
							q: request.term
						},
						success: function(data) {

							var lines = data.split("\n");
							var result = [];

							$.each(lines, function(i, line) {
								if ($.trim(line) != '') {
									var parts = line.split("|");

									if (parts.length >= 2) {
										result.push({
											label: $.trim(parts[0]),
											value: $.trim(parts[0]),
											id: $.trim(parts[1])
										});
									}
								}
							});

							response(result);
						}
					});
				},
				select: function(event, ui) {
					$("#spare_item_id").val(ui.item.id);
				}
			});

			$('#add_items').click(function() {

				let spare_item_id = $("#spare_item_id").val();

				if (spare_item_id == '' || spare_item_id == 0) {
					alert("Please select spare item");
					return false;
				}

				var existingRow = $('#table_po tbody tr').filter(function() {
					return Number($(this).attr('id')) === Number(spare_item_id);
				});

				var isExistingItem = existingRow.length > 0;

				$.ajax({
					type: "POST",
					url: "inc/cis_ajax/jquery_item_spare_mapping.php",
					data: {
						"spare_item_id": spare_item_id,
						"mode": "SpareMapping"
					}
				}).done(function(msg) {

					if (isExistingItem) {
						existingRow.replaceWith(msg);
					} else {
						$("#table_po tbody").append(msg);
					}

					updateSerialNumbers();
					$("#spare_item_id").val('');
					$("#item_name").val('');
				});
			});

			$(document).on("click", ".delete", function() {
				$(this).closest("tr").remove();
				updateSerialNumbers();
			});

			function updateSerialNumbers() {
				$('#table_po tbody tr').each(function(index) {
					$(this).find('.slno').text(index + 1);
				});
			}

		});

		function fnValidate() {
			var item_id = $("#item_id").val();
			if (item_id == '' || item_id == 0) {
				alert("Parent item missing");
				return false;
			}

			var rowCount = $('#table_po tbody tr').length;
			if (rowCount <= 0) {
				alert("Please add atleast one spare item");
				return false;
			}

			return true;
		}
	</script>

</body>

</html>