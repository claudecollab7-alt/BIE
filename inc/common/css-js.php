	<link rel="icon" href="favicon.ico" type="image/x-icon" />
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />

	<!-- Global stylesheets -->
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
	<link href="css/icons/icomoon/styles.min.css" rel="stylesheet" type="text/css">
	<link href="css/icons/fontawesome/styles.min.css" rel="stylesheet" type="text/css">
	<link href="css/bootstrap.css" rel="stylesheet" type="text/css">
	<link href="css/bootstrap_limitless.css" rel="stylesheet" type="text/css">
	<link href="css/layout.css" rel="stylesheet" type="text/css">
	<link href="css/components.css" rel="stylesheet" type="text/css">
	<link href="css/colors.css" rel="stylesheet" type="text/css">	
	<link href="css/custom.css" rel="stylesheet" type="text/css">
	
	<!-- /global stylesheets -->

	<!-- Core JS files -->
	<script src="js/jquery.min.js"></script>
	<script src="js/bootstrap.bundle.min.js"></script>
	<script src="js/plugins/loaders/blockui.min.js"></script>
	<script src="js/plugins/editors/ckeditor/ckeditor.js"></script>
	 
  

	<!-- /core JS files -->

	<!-- Theme JS files -->	
	<script src="js/plugins/forms/styling/uniform.min.js"></script>	
	<script src="js/plugins/forms/styling/switch.min.js"></script>
	
	<script src="js/plugins/forms/inputs/autosize.min.js"></script>	
	<script src="js/plugins/forms/inputs/inputmask.js"></script>
			
	<script src="js/plugins/extensions/session_timeout.min.js"></script>
		
	<script src="js/app.js"></script>	
	<!--<script src="js/plugins/pagination/bs_pagination.min.js"></script-->
	<script src="js/plugins/tables/datatables/datatables.min.js"></script>
	<script src="js/plugins/tables/datatables/extensions/responsive.min.js"></script>
	
	<script src="js/plugins/tables/datatables/extensions/buttons.min.js"></script>	
	<script src="js/plugins/tables/datatables/extensions/jszip/jszip.min.js"></script>
	<script src="js/plugins/tables/datatables/extensions/pdfmake/pdfmake.min.js"></script>
	<script src="js/plugins/tables/datatables/extensions/pdfmake/vfs_fonts.min.js"></script>
	
	<script src="js/plugins/extensions/jquery_ui/interactions.min.js"></script>
	<script src="js/plugins/forms/selects/select2.min.js"></script>
	<script src="js/plugins/forms/selects/bootstrap_multiselect.js"></script>
	
	<script src="js/plugins/pickers/pickadate/picker.js"></script>
	<script src="js/plugins/pickers/pickadate/picker.date.js"></script>
	<script src="js/plugins/pickers/pickadate/picker.time.js"></script>
	
	<!-- /theme JS files -->
	
	<!-- Growl JS files -->
	<script src="js/plugins/notifications/jgrowl.min.js"></script>
	<!-- Growl JS files -->
	
	<!-- Uploader JS files -->
	<script src="js/plugins/uploaders/fileinput/plugins/purify.min.js"></script>
	<script src="js/plugins/uploaders/fileinput/plugins/sortable.min.js"></script>
	<script src="js/plugins/uploaders/fileinput/fileinput.min.js"></script>
	<script src="js/plugins/uploaders/fileinput/uploader_bootstrap.js"></script>
	<!-- Uploader JS files -->
	
	<!-- Image Popup -->	
	<script src="js/plugins/media/fancybox.min.js"></script>
	<script src="js/gallery_library.js"></script>
	<!-- Image Popup -->
	
	<script language="javascript" type="text/javascript" src="inc/common/validate.js"></script>
	<script language="javascript" type="text/javascript" src="js/tulips.js"></script>
	
	<script type="text/javascript">
	/*var session_time = 1440;	
	
		function sessiontick()
		{			 
		  document.getElementById('session_timer').innerHTML = "Session Expires in : " + session_time--;
		}*/
			
		$( function() 
		{	
			//var t = setInterval(sessiontick, 1000);
			
			// Initialize Controls		
			$('.form-input-styled').uniform();
						
			$('.form-check-input-styled').uniform();
			
			$('.form-control-select2').select2();	

			$('.select').select2({
				minimumResultsForSearch: Infinity
			});

			// Select with search
			$('.select-search').select2();
		
			//$('.pickadate').pickadate();
			
			$('.form-control-uniform').uniform();
			
			$.extend( $.fn.dataTable.defaults, {
				autoWidth: false,
				responsive: true,
				processing: true,					
				dom: '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
				language: {
					search: ' _INPUT_',
					searchPlaceholder: 'Type to Search...',
					lengthMenu: '<span>Show:</span> _MENU_',
					paginate: { 'first': 'First', 'last': 'Last', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
				}
			});
			
			// Basic datatable
			//$('.datatable-basic').DataTable();			
			
			$('.datatable-col3').DataTable({
				autoWidth: false,	
				responsive: true,
				processing: true,	
				stateSave: true,
				dom: '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
				language: {
					search: ' _INPUT_',
					searchPlaceholder: 'Type to Search...',
					lengthMenu: '<span>Show:</span> _MENU_',
					paginate: { 'first': 'First', 'last': 'Last', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
				},
				columnDefs: [{ 
					orderable: false,
					width: 90,
					targets: [ 2 ]
				}],
			});

			$('.datatable-col5').DataTable({
				autoWidth: false,
				responsive: true,
				processing: true,	
				stateSave: true,
				dom: '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
				language: {
					search: ' _INPUT_',
					searchPlaceholder: 'Type to Search...',
					lengthMenu: '<span>Show:</span> _MENU_',
					paginate: { 'first': 'First', 'last': 'Last', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
				},
				columnDefs: [{ 
					orderable: false,
					width: 90,
					targets: [ 4 ]
				},  {
                        targets: [4],
                        className: 'text-center'
                    },
					
				],
			});

			$('.datatable-col6').DataTable({
				autoWidth: false,
				responsive: true,
				processing: true,	
				stateSave: true,
				dom: '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
				language: {
					search: ' _INPUT_',
					searchPlaceholder: 'Type to Search...',
					lengthMenu: '<span>Show:</span> _MENU_',
					paginate: { 'first': 'First', 'last': 'Last', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
				},
				columnDefs: [{ 
					orderable: false,
					width: 90,
					targets: [ 5 ]
				},  {
                        targets: [5],
                        className: 'text-center'
                    },],
			});

			$('.datatable-col7').DataTable({
				autoWidth: false,
				responsive: true,
				processing: true,	
				stateSave: true,
				dom: '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
				language: {
					search: ' _INPUT_',
					searchPlaceholder: 'Type to Search...',
					lengthMenu: '<span>Show:</span> _MENU_',
					paginate: { 'first': 'First', 'last': 'Last', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
				},
				columnDefs: [{ 
					orderable: false,
					width: 90,
					targets: [0,6]
				},  {
                        targets: [6],
                        className: 'text-center'
                    },],
			});
			$('.datatable-col9').DataTable({
				autoWidth: false,
				responsive: true,
				processing: true,	
				stateSave: true,
				dom: '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
				language: {
					search: ' _INPUT_',
					searchPlaceholder: 'Type to Search...',
					lengthMenu: '<span>Show:</span> _MENU_',
					paginate: { 'first': 'First', 'last': 'Last', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
				},
				columnDefs: [{ 
					orderable: false,
					width: 90,
					targets: [ 6 ]
				},  {
                        targets: [6],
                        className: 'text-center'
                    },],
			});

			$('.datatable-col2').DataTable({
				autoWidth: false,
				responsive: true,
				processing: true,	
				stateSave: true,
				dom: '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
				language: {
					search: ' _INPUT_',
					searchPlaceholder: 'Type to Search...',
					lengthMenu: '<span>Show:</span> _MENU_',
					paginate: { 'first': 'First', 'last': 'Last', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
				},
				columnDefs: [{ 
					orderable: false,
					width: 90,
					targets: [ 1]
				}],
			});
			
			$('.dataTables_length select').select2({
				minimumResultsForSearch: Infinity,
				dropdownAutoWidth: true,
				width: 'auto'
			});
			$('.datatable-col8').DataTable({
				autoWidth: false,
				responsive: true,
				processing: true,	
				stateSave: true,
				dom: '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
				language: {
					search: ' _INPUT_',
					searchPlaceholder: 'Type to Search...',
					lengthMenu: '<span>Show:</span> _MENU_',
					paginate: { 'first': 'First', 'last': 'Last', 'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;' }
				},
				columnDefs: [{ 
					orderable: false,
					width: 90,
					targets: [ 6,8 ]
				},  {
                        targets: [0,1,4,8],
                        className: 'text-center'
                    },
					{
                    targets: [6],
                    className: 'text-left'
                },
				
				],
			});

		

			/**Check box / Radio box Color *
			
			
			$('.datatable-pagination').DataTable({
				pagingType: "simple",
				language: {
					paginate: {'next': $('html').attr('dir') == 'rtl' ? 'Next &larr;' : 'Next &rarr;', 'previous': $('html').attr('dir') == 'rtl' ? '&rarr; Prev' : '&larr; Prev'}
				}
			});
			//
			// Contextual colors
			//

			// Primary
			$('.form-check-input-styled-primary').uniform({
				wrapperClass: 'border-primary text-primary'
			});

			// Danger
			$('.form-check-input-styled-danger').uniform({
				wrapperClass: 'border-danger text-danger'
			});

			// Success
			$('.form-check-input-styled-success').uniform({
				wrapperClass: 'border-success text-success'
			});

			// Warning
			$('.form-check-input-styled-warning').uniform({
				wrapperClass: 'border-warning text-warning'
			});

			// Info
			$('.form-check-input-styled-info').uniform({
				wrapperClass: 'border-info text-info'
			});

			// Custom color
			$('.form-check-input-styled-custom').uniform({
				wrapperClass: 'border-indigo-400 text-indigo-400'
			});
			
			/**End of Check box / Radio Color */
		
		
			// Initialize Bootstrap Switch 
			$('.form-check-input-switch').bootstrapSwitch();
		
			
						
			/*
				File Upload control with default color change
			$('.form-input-styled').uniform({
				fileButtonClass: 'action btn bg-pink-400'
			});
			
			
			var ta = document.querySelector('textarea');
			ta.style.display = 'block';
			autosize.update(ta);
*/

		});
	</script>