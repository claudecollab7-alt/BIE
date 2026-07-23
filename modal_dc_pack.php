<div id="modalDCPack" class="modal fade" tabindex="-1">
	<div class="modal-dialog   modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header pb-2 pt-2 bg-modal">
				<h6 class="modal-title" style="width: 700px;">
					<span id="m_sales_rec" class="font-weight-bold">Dispatch Qty:</span>
				</h6>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="row p-3">
				<div class="col-lg-6" style=" text-align: center;">
					<span style="font-size: 14px; font-weight: bold;  ">Box No</span>
				</div>
				<div class="col-lg-6" style=" text-align: center;">
					<span style="font-size: 14px; font-weight: bold;">Quantity</span>
				</div>
			</div>
			<!-- <legend class="font-weight-semibold"></legend> -->
			<!-- <form name='thisForm' id="validate" action="dc_add.php" class="form-horizontal" method='POST' onSubmit="return fnValidate();" enctype="multipart/form-data"> -->
			<form name='thisForm' class="form-horizontal" method='post' action="temp_pack_box_save.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
				<div class="modal-body py-0" id="m_sales_code">
					<div class="col-md-6 pt-5 pb-5 text-center">
						<span id="spinner-light" class="text-loading">
							<i class="icon-spinner spinner mr-2 "></i>
							Loading ...
						</span>
					</div>
				</div>
				<br>
				<!-- <div class="col-lg-12" style="font-size: 14px; font-weight: bold; text-align: center;" >
					<INPUT class="btn btn-custom" type="submit" onclick="history.go(-1)" name="SAVE" id="SAVE" value="SAVE">
				</div> -->
			<!-- </form> -->
			<br>

			<!-- <legend class="font-weight-semibold"></legend> -->
			<div class="modal-footer pt-0 pb-2 bg-modal">
				<!--button type="button" class="btn btn-light" data-dismiss="modal">Close</button-->
			</div>
		</div>
	</div>
</div>
<!-- /basic modal -->

<script type="text/javascript">
	$('#SAVE').click(function() {
		var pack_box_no = $("#no_of_box").val();
		var pack_item_qty = $("#pack_item_qty").val();
		var total_qty = $("#total").val();
		var dispatch_qty = $("#dispatch_qty").val();
		// alert(dispatch_qty);
		for (i = 1; i <= pack_box_no; i++) {
			var boxno_empty = $("#pack_box_no" + i).val();
			var boxqty_empty = $("#pack_item_qty" + i).val();

			if (boxno_empty == '') {
				alert("1");
				$("#pack_box_no" + i).focus();
				return false;

			}

			if (pack_item_qty == '') {
				alert("One or more Box Number Missing..");
				$("#pack_item_qty").focus();
				return false;
			}
			
		}
		if(total_qty != dispatch_qty)
		{
			alert("Total qty and dispatch qty must be same");
			return false;
		}

	});
</script>