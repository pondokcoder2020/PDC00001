<script type="text/javascript">
	$(function(){
		var no_urut_universal = 1;
        var dataInfo = {};
        var dataItems = {};

        loadGudang();
        loadPemasok();
		loadItem(1);
		loadSatuan(1);

		$("#table-item-do tbody").on('keyup','.itemInputan', function(){
			let id = $(this).attr("id").split("_");
			id = id[id.length - 1];

			let stats = checkItemColumn(id);
			if (stats == true){
				if ($("#barang_" + id).parent().parent().hasClass("last")) {
                    no_urut_universal++;
					newColumn(no_urut_universal);
					$("#barang_" + id).parent().parent().removeClass("last");
                    //setLastRow('item_' + no_urut_universal);
                    setNomorUrut("table-item-do","no_urut");
				}
			}
		});

		$("#table-item-do tbody").on('change','.itemInputanSelect', function(){
			let id = $(this).attr("id").split("_");
			id = id[id.length - 1];

			let stats = checkItemColumn(id);
			if (stats == true){
				if ($("#barang_" + id).parent().parent().hasClass("last")) {
					no_urut_universal++;
                    newColumn(no_urut_universal);
					$("#barang_" + id).parent().parent().removeClass("last");
                    //setLastRow('item_' + no_urut_universal);
                    setNomorUrut("table-item-do","no_urut");
				}
			}
		});

		$("#table-item-do tbody").on('click', '.btn-hapus-item', function(){
            $(this).closest("tr").remove();
            setNomorUrut("table-item-do","no_urut");
        });

        $("#btnSubmit").click(function(){

            $(".informasi").each(function(){
                let value = $(this).val();

                if (value != "" && value != null){
                    $this = $(this);
                    let name = $(this).attr("id");

                    dataInfo[name] = value;
                }
            });

            $(".items").each(function(){
                let value = $(this).val();

                if (value != "" && value != null && value != 0){
                    $this = $(this);
                    let row = $(this).attr("id").split("_");
                    let name = row.slice(0, row.length - 1).join("_");
                    //name.join("_");
                    row = row[row.length - 1];
                    
                    if (row in dataItems){
                        dataItems[row][name] = value;
                    } else {
                        dataItems[row] = {[name]: value};
                    }
                }
            });

            $.ajax({
                async: false,
                url: __HOSTAPI__ + "/DeliveryOrder",
                data: {
                    request : "tambah-do",
                    dataInfo : dataInfo,
                    dataItems : dataItems
                },
                beforeSend: function(request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                type: "POST",
                success: function(response){
                    console.log(response);
                    //location.href = __HOSTNAME__ + '/rawat_jalan/perawat';
                },
                error: function(response) {
                    console.log("Error : ");
                    console.log(response);
                }
            });

            return false;

        });

        $('.select2').select2({});
	});

    function loadGudang(){
        $.ajax({
            async: false,
            url:__HOSTAPI__ + "/Inventori/gudang",
            type: "GET",
             beforeSend: function(request) {
                request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
            },
            success: function(response){
                var dataGudang = response.response_package.response_data;

                for(i = 0; i < dataGudang.length; i++){
                    var selection = document.createElement("OPTION");

                    $(selection).attr("value", dataGudang[i].uid).html(dataGudang[i].nama);
                    $("#gudang").append(selection);
                }
            },
            error: function(response) {
                console.log(response);
            }
        });

    }

     function loadPemasok(){
        $.ajax({
            async: false,
            url:__HOSTAPI__ + "/Supplier",
            type: "GET",
             beforeSend: function(request) {
                request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
            },
            success: function(response){
                var dataPemasok = response.response_package.response_data;

                for(i = 0; i < dataPemasok.length; i++){
                    var selection = document.createElement("OPTION");

                    $(selection).attr("value", dataPemasok[i].uid).html(dataPemasok[i].nama);
                    $("#supplier").append(selection);
                }
            },
            error: function(response) {
                console.log(response);
            }
        });

    }

	function checkItemColumn(id){
		let stats = false;
		let item = $("#barang_" + id).val();
		let kode_batch = $("#kode_batch_" + id).val();
		let qty = $("#qty_" + id).val();
		let kedaluarsa = $("#kedaluarsa_" + id).val();

		if (item != "" && kode_batch != "" && (qty != 0 && qty != "") && kedaluarsa != ""){
			stats = true;
		}

		return stats;
	}

	function newColumn(no_urut){
        /*html = "<tr id='tindakan_" + uid_tindakan + "'>" + 
                    "<td class='no_urut'></td>" +
                    "<td><a href='#' class='linkTindakan'>"+ nama_tindakan +"</a></td>" +
                    "<td><button type='button' rel='tooltip' id='btn_tindakan_"+ uid_tindakan +"' class='btn btn-sm btn-danger btnHapusTindakan' data-toggle='tooltip' data-placement='top' title='' data-original-title='Hapus'><i class='fa fa-trash'></i></button></td>" +
                "</tr>";*/

        let html = '<tr>' +
					'<td class="no_urut"></td>' +
					'<td><select class="form-control itemInputanSelect select2 items" id="barang_'+ no_urut +'" nama="barang_'+ no_urut +'"><option value="">Pilih Item</option></select>' + 
                        '<div class="input-group">' +
                            '<div class="input-group-prepend">' +
                                '<span class="input-group-text" id="kedaluarsa_label_'+ no_urut +'">Kedaluarsa</span>' +
                            '</div>' +
                            '<input type="date" name="kedaluarsa_'+ no_urut +'" id="kedaluarsa_'+ no_urut +'" class="form-control itemInputan items" placeholder="Kedaluarsa" aria-describedby="kedaluarsa_label">' + 
                        '</div>' + 
                    '</td>' +
					'<td><input type="text" name="kode_batch_'+ no_urut +'" id="kode_batch_'+ no_urut +'" class="form-control itemInputan items" placeholder="Kode Batch"></td>' +
					'<td><input type="number" name="qty_'+ no_urut +'" id="qty_'+ no_urut +'" class="form-control itemInputan items" value="0"></td>' +
					'<td><span id="satuan_'+ no_urut +'">Satuan</span></td>' + 
					'<td><textarea class="form-control items" id="keterangan_'+ no_urut +'" nama="keterangan_'+ no_urut +'"></textarea></td>' + 
					'<td><button class="btn btn-sm btn-danger btn-hapus-item" data-toggle="tooltip" title="Hapus"><i class="fa fa-trash"></i></button></td>' +
				'</tr>';

        $("#table-item-do tbody").append(html);
        setNomorUrut("table-item-do","no_urut");
        loadItem(no_urut);
        loadSatuan(no_urut);
    }

    function loadItem(selector_id){
    	var dataItem;

        $.ajax({
            async: false,
            url:__HOSTAPI__ + "/Inventori",
            type: "GET",
             beforeSend: function(request) {
                request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
            },
            success: function(response){
                dataItem = response.response_package.response_data;

                var html = "";
                 for(i = 0; i < dataItem.length; i++){
                    var selection = document.createElement("OPTION");

                    $(selection).attr("value", dataItem[i].uid).html(dataItem[i].nama);
                    $("#barang_" + selector_id).append(selection);
                }
            },
            error: function(response) {
                console.log(response);
            }
        });

        return dataItem;
    }

    function loadSatuan(selector_id){
    	var dataSatuan;

        $.ajax({
            async: false,
            url:__HOSTAPI__ + "/Inventori/satuan",
            type: "GET",
             beforeSend: function(request) {
                request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
            },
            success: function(response){
                dataSatuan = response.response_package.response_data;

                var html = "";
                 for(i = 0; i < dataSatuan.length; i++){
                    var selection = document.createElement("OPTION");

                    $(selection).attr("value", dataSatuan[i].uid).html(dataSatuan[i].nama);
                    //$("#satuan_" + selector_id).append(selection);
                }
            },
            error: function(response) {
                console.log(response);
            }
        });

        return dataSatuan;
    }

    function loadFromPo(){

        $.ajax({
            async: false,
            url:__HOSTAPI__ + "/Inventori/satuan",
            type: "GET",
             beforeSend: function(request) {
                request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
            },
            success: function(response){
                dataSatuan = response.response_package.response_data;

                var html = "";
                 for(i = 0; i < dataSatuan.length; i++){
                    var selection = document.createElement("OPTION");

                    $(selection).attr("value", dataSatuan[i].uid).html(dataSatuan[i].nama);
                    //$("#satuan_" + selector_id).append(selection);
                }
            },
            error: function(response) {
                console.log(response);
            }
        });
    }

	function setNomorUrut(table_name, no_urut_class){
        /*set dynamic serial number*/
        let rowCount = $("#"+ table_name +" tr").length;
        let table = $("#"+ table_name);
        let rowNum = parseInt(rowCount) - 1;
        $("."+ no_urut_class).html("");
        table.find('tr:eq('+ rowNum +')').addClass("last");

        for (var i = 0, row; i < rowCount; i++) {
            //console.log()
            table.find('tr:eq('+ i +')').find('td:eq(0)').html(i);
        }
        /*--------*/
    }

   /* function setLastRow(selector){
    	$("#" + selector).parent().parent().addClass("last");
    }*/
</script>