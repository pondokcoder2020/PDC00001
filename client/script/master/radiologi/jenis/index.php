<script type="text/javascript">
	$(function(){
		var MODE = "tambah", selectedUID;

		//var groupColumn = 3;
		var tableJenis = $("#table-jenis-layanan").DataTable({
			"ajax":{
				url: __HOSTAPI__ + "/Radiologi/jenis",
				type: "GET",
				headers:{
					Authorization: "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>
				},
				dataSrc:function(response) {
					return response.response_package.response_data;
				}
			},
			autoWidth: false,
			aaSorting: [[0, "asc"]],
			"columnDefs":[
				{"targets":0, "className":"dt-body-left"}
			],
			"columns" : [
				{
					"data" : null, render: function(data, type, row, meta) {
						return row["autonum"];
					}
				},
				{
					"data" : null, render: function(data, type, row, meta) {
						return "<span id=\"nama_" + row["uid"] + "\">" + row["nama"] + "</span>";
					}
				},
				{
					"data" : null, render: function(data, type, row, meta) {
						return "<div class=\"btn-group\" role=\"group\" aria-label=\"Basic example\">" +
									"<button class=\"btn btn-warning btn-sm btn-edit-jenis\" id=\"jenis_edit_" + row["uid"] + "\" data-toggle='tooltip' title='Edit'>" +
										"<i class=\"fa fa-edit\"></i>" +
									"</button>" +
									"<button id=\"bed_delete_" + row['uid'] + "\" class=\"btn btn-danger btn-sm btn-delete-jenis\" data-toggle='tooltip' title='Hapus'>" +
										"<i class=\"fa fa-trash\"></i>" +
									"</button>" +
								"</div>";
					}
				}
			],
		});

		$("body").on("click", ".btn-delete-jenis", function(){
			var uid = $(this).attr("id").split("_");
			uid = uid[uid.length - 1];

			var conf = confirm("Hapus jenis item?");
			if(conf) {
				$.ajax({
					url:__HOSTAPI__ + "/Radiologi/master_radiologi_jenis/" + uid,
					beforeSend: function(request) {
						request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
					},
					type:"DELETE",
					success:function(response) {
						console.log(response);
						tableJenis.ajax.reload();
					},
					error: function(response) {
						console.log(response);
					}
				});
			}
		});
		
		$("body").on("click", ".btn-edit-jenis", function() {
			var uid = $(this).attr("id").split("_");
			uid = uid[uid.length - 1];
			selectedUID = uid;

			MODE = "edit";
			$("#title-form").html("Edit");
			$("#txt_nama").val($("#nama_" + uid).html());
			$("#form-tambah").modal("show");
			return false;
		});
		
		$("#btnTambah").click(function() {
			$("#txt_nama").val("");
			MODE = "tambah";
			$("#title-form").html("Tambah");
			$("#form-tambah").modal("show");
		});

		$("#btnSubmit").click(function() {
			var nama = $("#txt_nama").val();

			if(nama != "") {
				var form_data = {};
				if(MODE == "tambah") {
					form_data = {
						"request": "tambah-jenis",
						"nama": nama
					};
				} else {
					form_data = {
						"request": "edit-jenis",
						"uid": selectedUID,
						"nama": nama
					};
				}

				$.ajax({
					async: false,
					url: __HOSTAPI__ + "/Radiologi",
					data: form_data,
					beforeSend: function(request) {
						request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
					},
					type: "POST",
					success: function(response){
						$("#txt_nama").val("");
						$("#form-tambah").modal("hide");
						console.log(response);
						tableJenis.ajax.reload();
					},
					error: function(response) {
						console.log(response);
					}
				});
			}
		});
	});
</script>

<div id="form-tambah" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal-large-title" aria-hidden="true" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog modal-md bg-danger" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modal-large-title"><span id="title-form"></span> Jenis Layanan Radiologi</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="form-group col-md-12">
					<label for="txt_nama">Nama Jenis Layanan:</label>
					<input type="text" class="form-control" id="txt_nama" />
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" id="btnSubmit">Simpan</button>
				<button type="button" class="btn btn-danger" data-dismiss="modal">Kembali</button>
			</div>
		</div>
	</div>
</div>