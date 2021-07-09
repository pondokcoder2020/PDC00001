<script src="<?php echo __HOSTNAME__; ?>/plugins/printThis/printThis.js"></script>
<script type="text/javascript">
    $(function () {
        var tableRiwayatRadiologi = $("#table-riwayat-radiologi").DataTable({
            processing: true,
            serverSide: true,
            sPaginationType: "full_numbers",
            bPaginate: true,
            lengthMenu: [[5, 10, 15, -1], [5, 10, 15, "All"]],
            serverMethod: "POST",
            "ajax":{
                url: __HOSTAPI__ + "/Radiologi",
                type: "POST",
                data: function(d){
                    d.request = "riwayat_radiologi";
                    d.from = getDateRange("#range_history")[0];
                    d.to = getDateRange("#range_history")[1];
                },
                headers:{
                    Authorization: "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>
                },
                dataSrc:function(response) {
                    console.clear();
                    console.log(response);
                    var dataSet = response.response_package.response_data;
                    if(response.response_package === undefined || dataSet === undefined) {
                        dataSet = [];
                    }

                    var returnedData = [];
                    for(var a in dataSet) {
                        if(dataSet[a].asesmen !== undefined && dataSet[a].asesmen !== null) {
                            returnedData.push(dataSet[a]);
                        }
                    }


                    response.draw = parseInt(response.response_package.response_draw);
                    response.recordsTotal = response.response_package.recordsTotal;
                    response.recordsFiltered = response.response_package.recordsFiltered;


                    return returnedData;
                }
            },
            autoWidth: false,
            language: {
                search: "",
                searchPlaceholder: "Cari Kode Amprah"
            },
            "columns" : [
                {
                    "data" : null, render: function(data, type, row, meta) {
                        return row.autonum;
                    }
                },
                {
                    "data" : null, render: function(data, type, row, meta) {
                        return "<span class=\"wrap_content\">" + row.tanggal + "</span>";
                    }
                },
                {
                    "data" : null, render: function(data, type, row, meta) {
                        return "<h6 class=\"text-info\">" + row.no_rm + "</h6>" + row.nama_pasien;
                    }
                },
                {
                    "data" : null, render: function(data, type, row, meta) {
                        return (row.asesmen.poli !== null && row.asesmen.poli !== undefined) ? row.asesmen.poli.nama + "<br /><b>" + row.asesmen.dokter.nama + "</b>" : "";
                    }
                },
                {
                    "data" : null, render: function(data, type, row, meta) {
                        return (row.petugas !== undefined) ? row.petugas.nama : "";
                    }
                },
                {
                    "data" : null, render: function(data, type, row, meta) {
                        return (row.dokter_radio.nama !== undefined && row.dokter_radio.nama !== null) ? row.dokter_radio.nama : "";
                    }
                },
                {
                    "data" : null, render: function(data, type, row, meta) {
                        return "<div class=\"btn-group wrap_content\" role=\"group\" aria-label=\"Basic example\">" +
                            "<button id=\"cetak_" + row.uid + "\" class=\"btn btn-primary btn-sm btnCetak\">" +
                            "<span><i class=\"fa fa-print\"></i>Cetak</span>" +
                            "</button>" +
                            "</div>";
                    }
                }
            ]
        });

        $("body").on("click", ".btnCetak", function() {
            var uid = $(this).attr("id").split("_");
            uid = uid[uid.length - 1];

            var radItem = loadRadOrderDetail(uid);
            var radLampiran = loadRadOrderLampiran(uid);
            var radPasien = loadRadOrderPasien(uid);


            $.ajax({
                async: false,
                url: __HOST__ + "miscellaneous/print_template/rad_hasil.php",
                beforeSend: function (request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                type: "POST",
                data: {
                    __PC_CUSTOMER__: __PC_CUSTOMER__,
                    __PC_CUSTOMER_ADDRESS__: __PC_CUSTOMER_ADDRESS__,
                    __PC_CUSTOMER_CONTACT__: __PC_CUSTOMER_CONTACT__,
                    rad_pasien: radPasien,
                    rad_item: radItem,
                    rad_lampiran: radLampiran
                },
                success: function (response) {
                    var containerItem = document.createElement("DIV");
                    $(containerItem).html(response);
                    $(containerItem).printThis({
                        importCSS: true,
                        base: false,
                        pageTitle: "Laporan Radiologi " + radPasien.pasien.no_rm,
                        afterPrint: function() {
                            //
                        }
                    });
                },
                error: function (response) {
                    //
                }
            });

            return false;
        });

        $("#range_history").change(function() {
            if(
                !Array.isArray(getDateRange("#range_history")[0]) &&
                !Array.isArray(getDateRange("#range_history")[1])
            ) {
                tableRiwayatRadiologi.ajax.reload();
            }
        });



        function loadRadOrderDetail(uid){
            var html;
            $.ajax({
                async: false,
                url:__HOSTAPI__ + "/Radiologi/get-order-detail/" + uid,
                type: "GET",
                beforeSend: function(request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                success: function(response){
                    if (response.response_package.response_result > 0){
                        console.clear();
                        html = "<ol>";
                        dataItem = response.response_package.response_data;
                        $.each(dataItem, function(key, item){
                            html += "<li style=\"border-bottom: dashed 1px #808080; padding: 10px 0;\">" +
                                "<div style=\"margin-left: 10px\">" +
                                "<h4>" + item.tindakan + "</h4>" +
                                "<b>Keterangan:</b><br />" + item.keterangan +
                                "<br />" +
                                "<b>Kesimpulan:</b><br />" + item.kesimpulan +
                                "</div>" +
                                "</li>";
                        });
                        html += "</ol>";
                    }
                },
                error: function(response) {
                    console.log(response);
                }
            });
            return html;
        }

        function loadRadOrderLampiran(uid) {
            var MetaData;
            var html = "";
            $.ajax({
                async: false,
                url:__HOSTAPI__ + "/Radiologi/get-radiologi-lampiran/" + uid,
                type: "GET",
                beforeSend: function(request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                success: function(response){
                    MetaData = response.response_package.response_data;
                    for(LampKey in MetaData) {
                        html += "<div class=\"pagebreak\">" +
                            "<embed type=\"application/pdf\" src=\"" + __HOST__ + "document/radiologi/" + MetaData[LampKey].radiologi_order + "/" + MetaData[LampKey].lampiran + "\" width=\"100%\" height=\"100%\" />" +
                            "</div>";
                    }
                },
                error: function(response) {
                    console.log(response);
                }
            });
            return html;
        }

        function loadRadOrderPasien(uid) {
            var MetaData;
            $.ajax({
                async: false,
                url:__HOSTAPI__ + "/Radiologi/get-data-pasien-antrian/" + uid,
                type: "GET",
                beforeSend: function(request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                success: function(response){
                    MetaData = response.response_package;
                },
                error: function(response) {
                    console.log(response);
                }
            });
            return MetaData;
        }

        function loadHarga(mitra, asesmen, tindakan, target) {
            $("#harga_" + target + "_" + tindakan).html("<b>0.00</b>").attr({
                "harga": 0
            });
            $.ajax({
                async: false,
                url:__HOSTAPI__ + "/Mitra",
                type: "POST",
                data: {
                    request: "check_target",
                    mitra: mitra,
                    asesmen: asesmen,
                    tindakan: tindakan
                },
                beforeSend: function(request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                success: function(response){
                    if(response.response_package.response_data !== undefined && response.response_package.response_data[0] !== undefined) {
                        var harga = response.response_package.response_data[0].harga;
                        if(parseFloat(harga) > 0) {
                            $("#harga_" + target + "_" + tindakan).html("<b>" + number_format(harga, 2, ".", ",") + "</b>").attr({
                                "harga": harga
                            });
                        } else {
                            $("#harga_" + target + "_" + tindakan).html("<b>0.00</b><br /><span class=\"text-warning\"><i class=\"fa fa-info-circle\"></i> Harga bernilai 0. Pastikan harga tindakan sudah benar</span>").attr({
                                "harga": 0
                            });
                        }
                    } else {
                        $("#harga_" + target + "_" + tindakan).html("<b>0.00</b><br /><span class=\"text-warning\"><i class=\"fa fa-info-circle\"></i> Harga bernilai 0. Pastikan harga tindakan sudah benar</span>").attr({
                            "harga": 0
                        });
                    }

                    var totalBiaya = 0;
                    $("#item-verif-radio tbody tr").each(function() {
                        totalBiaya += parseFloat($(this).find("td:eq(3)").attr("harga"));
                    });
                    $("#item-verif-radio tfoot tr td:eq(1)").html("<h4 class=\"text-danger\">" + number_format(totalBiaya, 2, ".", ",") + "</h4>");
                },
                error: function(response) {
                    $("#harga_" + target + "_" + tindakan).html("<b>0.00</b><br /><span class=\"text-warning\"><i class=\"fa fa-info-circle\"></i> Harga bernilai 0. Pastikan harga tindakan sudah benar</span>").attr({
                        "harga": 0
                    });
                }
            });
        }
    });
</script>