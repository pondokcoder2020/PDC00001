<script src="<?php echo __HOSTNAME__; ?>/plugins/printThis/printThis.js"></script>
<script type="text/javascript">
    $(function() {
        var selectedKunjungan = "", selectedPenjamin = "", selected_waktu_masuk = "", targettedDataResep = {};
        var tableResep = $("#table-resep-inap").DataTable({
            processing: true,
            serverSide: true,
            sPaginationType: "full_numbers",
            bPaginate: true,
            lengthMenu: [[20, 50, -1], [20, 50, "All"]],
            serverMethod: "POST",
            "ajax":{
                url: __HOSTAPI__ + "/Apotek",
                type: "POST",
                data: function(d) {
                    d.request = "resep_inap";
                    d.pasien = __PAGES__[3];
                    d.kunjungan = __PAGES__[4];
                },
                headers:{
                    Authorization: "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>
                },
                dataSrc:function(response) {
                    var returnedData = [];
                    if(response.response_package === undefined || response.response_package.response_data === undefined) {
                        returnedData = [];
                    } else {
                        returnedData = response.response_package.response_data;
                    }


                    response.draw = parseInt(response.response_package.response_draw);
                    response.recordsTotal = response.response_package.recordsTotal;
                    response.recordsFiltered = returnedData.length;

                    return returnedData;
                }
            },
            autoWidth: false,
            language: {
                search: "",
                searchPlaceholder: "Cari Resep"
            },
            aaSorting: [[0, "asc"]],
            "columnDefs":[
                {"targets":0, "className":"dt-body-left"}
            ],
            "columns" : [
                {
                    "data" : null, render: function(data, type, row, meta) {
                        return row.autonum;
                    }
                },
                {
                    "data" : null, render: function(data, type, row, meta) {
                        return "<span class=\"wrap_content\">" + row.created_at_parsed + "</span>";
                    }
                },
                {
                    "data" : null, render: function(data, type, row, meta) {
                        var detail = row.detail;
                        var parsedDetail = "<span class=\"text-danger\"><i class=\"fa fa-times-circle\"></i> Tidak ada resep</span>";
                        if(detail.length > 0) {
                            parsedDetail = "<div class=\"row\">";
                            for(var a in detail) {
                                if(detail[a].detail.nama !== "") {
                                    parsedDetail += "<div class=\"col-md-12\">" +
                                        "<span class=\"badge badge-info badge-custom-caption\"><i class=\"fa fa-tablets\"></i> " + detail[a].detail.nama + "</span><br />" +
                                        "<div style=\"padding-left: 20px;\">" + detail[a].signa_qty + " &times; " + detail[a].signa_pakai + " <label class=\"text-info\">[" + detail[a].qty + "]</label></div>" +
                                        "</div>";
                                }
                            }
                            parsedDetail += "</div>";
                        }

                        return parsedDetail;
                    }
                },
                {
                    "data" : null, render: function(data, type, row, meta) {
                        var racikan = row.racikan;
                        var parsedDetail = "<span class=\"text-danger\"><i class=\"fa fa-times-circle\"></i> Tidak ada racikan</span>";
                        if(racikan.length > 0) {
                            parsedDetail = "<div class=\"row\">";
                            for(var a in racikan) {
                                var detailRacikan = racikan[a].detail;
                                parsedDetail += "<div class=\"col-md-12\">" +
                                    "<span class=\"badge badge-success badge-custom-caption\">" + racikan[a].kode + "</span><br />" +
                                    "<div style=\"padding-left: 20px;\">" + racikan[a].signa_qty + " &times; " + racikan[a].signa_pakai + " <label class=\"text-info\">[" + racikan[a].qty + "]</label></div>" +
                                    "<ol>";
                                for(var b in detailRacikan) {
                                    parsedDetail += "<span style=\"margin-bottom: 5px;\" class=\"badge badge-info badge-custom-caption\"><i class=\"fa fa-tablets\"></i> " + detailRacikan[b].detail.nama + "</span>";
                                }
                                parsedDetail += "</ul></div>";
                            }
                            parsedDetail += "</div>";
                        }
                        return parsedDetail;
                    }
                },
                {
                    "data" : null, render: function(data, type, row, meta) {
                        if(row.status_resep === "N") {
                            return "<span class=\"badge badge-warning badge-custom-caption\"><i class=\"fa fa-info-circle\"></i> Menunggu Verifikasi</span>";
                        } else {
                            return "<div class=\"btn-group wrap_content\" role=\"group\" aria-label=\"Basic example\">" +
                                "<button class=\"btn btn-success btn-sm berikanObat\" id=\"resep_" + row.uid + "\">" +
                                "<span><i class=\"fa fa-eye\"></i>Berikan Obat</span>" +
                                "</button>" +
                                "</div>";
                        }
                    }
                }
            ]
        });



        var tableAntrian= $("#table-antrian-rawat-jalan").DataTable({
            "ajax":{
                url: __HOSTAPI__ + "/Asesmen/antrian-asesmen-medis/inap",
                type: "GET",
                headers:{
                    Authorization: "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>
                },
                dataSrc:function(response) {
                    var filteredData = [];
                    var data = response.response_package.response_data;

                    for(var a = 0; a < data.length; a++) {
                        if(
                            data[a].uid_pasien === __PAGES__[3] &&
                            data[a].uid_kunjungan === __PAGES__[4] &&
                            data[a].uid_poli === __POLI_INAP__
                        ) {
                            filteredData.push(data[a]);
                        }
                    }

                    if(filteredData.length > 0) {
                        selectedKunjungan = filteredData[0].uid_kunjungan;
                        selectedPenjamin = filteredData[0].uid_penjamin;
                        selected_waktu_masuk = filteredData[0].waktu_masuk;
                        //console.log(filteredData[0].pasien_detail);
                        $("#target_pasien").html(filteredData[0].pasien);
                        $("#rm_pasien").html(filteredData[0].no_rm);
                        $("#nama_pasien").html((filteredData[0].pasien_detail.panggilan_name === null) ? filteredData[0].pasien_detail.nama : filteredData[0].pasien_detail.panggilan_name.nama + " " +  filteredData[0].pasien_detail.nama);
                        $("#jenkel_pasien").html(filteredData[0].pasien_detail.jenkel_detail.nama);
                        $("#tempat_lahir_pasien").html(filteredData[0].pasien_detail.tempat_lahir);
                        $("#alamat_pasien").html(filteredData[0].pasien_detail.alamat);
                        $("#usia_pasien").html(filteredData[0].pasien_detail.usia);
                        $("#tanggal_lahir_pasien").html(filteredData[0].pasien_detail.tanggal_lahir_parsed);
                    } else {
                        //Pasien Detail
                        $.ajax({
                            url: __HOSTAPI__ + "/Pasien/pasien-detail/" + __PAGES__[3],
                            async:false,
                            beforeSend: function(request) {
                                request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                            },
                            type:"GET",
                            success:function(response) {
                                var pasienData = response.response_package.response_data;
                                $("#target_pasien").html(pasienData[0].nama);
                                $("#rm_pasien").html(pasienData[0].no_rm);
                                $("#nama_pasien").html((pasienData[0].panggilan_name === null) ? pasienData[0].nama : pasienData[0].panggilan_name.nama + " " +  pasienData[0].nama);
                                $("#usia_pasien").html(pasienData[0].usia);
                                $("#jenkel_pasien").html(pasienData[0].jenkel_detail.nama);
                                $("#tanggal_lahir_pasien").html(pasienData[0].tanggal_lahir_parsed);
                                $("#tempat_lahir_pasien").html(pasienData[0].tempat_lahir);
                                $("#alamat_pasien").html(pasienData[0].alamat);
                            },
                            error: function(response) {
                                console.log(response);
                            }
                        });
                    }

                    return filteredData;
                }
            },
            autoWidth: false,
            "bInfo" : false,
            aaSorting: [[0, "asc"]],
            "columnDefs":[
                {"targets":0, "className":"dt-body-left"}
            ],
            "columns" : [
                {
                    "data" : null, render: function(data, type, row, meta) {
                        return row.autonum;
                    }
                },
                {
                    "data" : null, render: function(data, type, row, meta) {
                        return row.waktu_masuk;
                    }
                },
                {
                    "data" : null, render: function(data, type, row, meta) {
                        /*return "<div class=\"btn-group wrap_content\" role=\"group\" aria-label=\"Basic example\">" +
                            "<a href=\"" + __HOSTNAME__ + "/rawat_inap/dokter/antrian/" + row.uid + "/" + row.uid_pasien + "/" + row.uid_kunjungan + "\" class=\"btn btn-success btn-sm\">" +
                            "<span><i class=\"fa fa-eye\"></i>Detail</span>" +
                            "</a>" +
                            "</div>";*/
                        return "";
                    }
                }
            ]
        });

        $("body").on("click", ".berikanObat", function () {
            var id = $(this).attr("id").split("_");
            id = id[id.length - 1];

            $.ajax({
                url:__HOSTAPI__ + "/Apotek/detail_resep_verifikator/" + id,
                async:false,
                beforeSend: function(request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                type:"GET",
                success:function(response) {
                    targettedDataResep = response.response_package.response_data[0];
                    $("#form-berikan-resep").modal("show");

                    $("#resep-nama-pasien").attr({
                        "set-penjamin": targettedDataResep.antrian.penjamin_data.uid
                    }).html(((targettedDataResep.antrian.pasien_info.panggilan_name !== undefined && targettedDataResep.antrian.pasien_info.panggilan_name !== null) ? targettedDataResep.antrian.pasien_info.panggilan_name.nama : "") + " " + targettedDataResep.antrian.pasien_info.nama + "<b class=\"text-success\"> [" + targettedDataResep.antrian.penjamin_data.nama + "]</b>");
                    $("#jk-pasien").html(targettedDataResep.antrian.pasien_info.jenkel_nama);
                    $("#tanggal-lahir-pasien").html(targettedDataResep.antrian.pasien_info.tanggal_lahir + " (" + targettedDataResep.antrian.pasien_info.usia + " tahun)");
                    loadDetailResep(targettedDataResep);

                },
                error: function(response) {
                    console.log(response);
                }
            });
        });

        $("#btnSubmitBerikanObat").click(function () {
            var autonum = 1;
            $("#list-konfirmasi-berikan-obat tbody").html("");
            for(var a in targettedDataResep.detail) {
                var newResepConfRow = document.createElement("TR");
                var newResepNo = document.createElement("TD");
                var newResepItem = document.createElement("TD");
                var newResepQty = document.createElement("TD");

                $(newResepNo).html(autonum);
                $(newResepItem).html(targettedDataResep.detail[a].detail.nama);
                $(newResepQty).html("<h6 class=\"number_style\">" + parseFloat(targettedDataResep.detail[a].signa_pakai) + "</h6>");

                $(newResepConfRow).append(newResepNo);
                $(newResepConfRow).append(newResepItem);
                $(newResepConfRow).append(newResepQty);

                $("#list-konfirmasi-berikan-obat").append(newResepConfRow);
                autonum += 1;
            }

            for(var a in targettedDataResep.racikan) {
                var newResepConfRow = document.createElement("TR");
                var newResepNo = document.createElement("TD");
                var newResepItem = document.createElement("TD");
                var newResepQty = document.createElement("TD");

                $(newResepNo).html(autonum);
                $(newResepItem).html(targettedDataResep.racikan[a].kode);
                $(newResepQty).html("<h6 class=\"number_style\">" + parseFloat(targettedDataResep.racikan[a].signa_pakai) + "</h6>");

                $(newResepConfRow).append(newResepNo);
                $(newResepConfRow).append(newResepItem);
                $(newResepConfRow).append(newResepQty);

                $("#list-konfirmasi-berikan-obat").append(newResepConfRow);
                autonum += 1;
            }

            $("#form-konfirmasi-berikan-resep").modal("show");
        });

        function load_product_resep(target, selectedData = "", appendData = true) {
            var selected = [];
            var productData;
            $.ajax({
                url:__HOSTAPI__ + "/Inventori/item_detail/" + selectedData,
                async:false,
                beforeSend: function(request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                type:"GET",
                success:function(response) {
                    productData = response.response_package.response_data;
                    for (var a = 0; a < productData.length; a++) {
                        var penjaminList = [];
                        var penjaminListData = productData[a].penjamin;
                        for(var penjaminKey in penjaminListData) {
                            if(penjaminList.indexOf(penjaminListData[penjaminKey].penjamin.uid) < 0) {
                                penjaminList.push(penjaminListData[penjaminKey].penjamin.uid);
                            }
                        }

                        if(selected.indexOf(productData[a].uid) < 0 && appendData) {
                            $(target).append("<option penjamin-list=\"" + penjaminList.join(",") + "\" satuan-caption=\"" + productData[a].satuan_terkecil.nama + "\" satuan-terkecil=\"" + productData[a].satuan_terkecil.uid + "\" " + ((productData[a].uid == selectedData) ? "selected=\"selected\"" : "") + " value=\"" + productData[a].uid + "\">" + productData[a].nama.toUpperCase() + "</option>");
                        }
                    }
                },
                error: function(response) {
                    console.log(response);
                }
            });
            return {
                allow: (productData.length == selected.length),
                data: productData
            };
        }

        function refreshBatch(item) {
            var batchData;
            $.ajax({
                url:__HOSTAPI__ + "/Inventori/item_batch/" + item,
                async:false,
                beforeSend: function(request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                type:"GET",
                success:function(response) {
                    batchData = response.response_package.response_data;
                },
                error: function(response) {
                    console.log(response);
                }
            });
            return batchData;
        }


        function loadDetailResep(data) {
            $("#load-detail-resep tbody tr").remove();
            if(data.detail.length > 0) {
                for(var a = 0; a < data.detail.length; a++) {
                    if(data.detail[a].detail !== null) {
                        var ObatData = load_product_resep(newObat, data.detail[a].detail.uid, false);
                        var selectedBatchResep = refreshBatch(data.detail[a].detail.uid);
                        var selectedBatchList = [];

                        var harga_tertinggi = 0;
                        var kebutuhan = parseFloat(data.detail[a].qty);
                        var jlh_sedia = 0;
                        var butuh_amprah = 0;
                        for(bKey in selectedBatchResep)
                        {
                            if(selectedBatchResep[bKey].harga > harga_tertinggi)    //Selalu ambil harga tertinggi
                            {
                                harga_tertinggi = parseFloat(selectedBatchResep[bKey].harga);
                            }

                            if(kebutuhan > 0)
                            {

                                if(kebutuhan > selectedBatchResep[bKey].stok_terkini)
                                {
                                    selectedBatchResep[bKey].used = selectedBatchResep[bKey].stok_terkini;
                                } else {
                                    selectedBatchResep[bKey].used = kebutuhan;
                                }
                                kebutuhan = kebutuhan - selectedBatchResep[bKey].stok_terkini;
                                if(selectedBatchResep[bKey].used > 0)
                                {
                                    selectedBatchList.push(selectedBatchResep[bKey]);
                                }
                            }

                            if(selectedBatchResep[bKey].gudang.uid === __UNIT__.gudang) {
                                jlh_sedia += selectedBatchResep[bKey].stok_terkini;
                            } else {
                                butuh_amprah += selectedBatchResep[bKey].stok_terkini;
                            }
                        }

                        if(selectedBatchResep.length > 0)
                        {
                            var profit = 0;
                            var profit_type = "N";

                            for(var batchDetail in selectedBatchResep[0].profit)
                            {
                                if(selectedBatchResep[0].profit[batchDetail].penjamin === $("#nama-pasien").attr("set-penjamin"))
                                {
                                    profit = parseFloat(selectedBatchResep[0].profit[batchDetail].profit);
                                    profit_type = selectedBatchResep[0].profit[batchDetail].profit_type;
                                }
                            }

                            var newDetailRow = document.createElement("TR");
                            $(newDetailRow).attr({
                                "id": "row_resep_" + a,
                                "profit": profit,
                                "profit_type": profit_type
                            });

                            var newDetailCellID = document.createElement("TD");
                            $(newDetailCellID).addClass("text-center").html((a + 1));

                            var newDetailCellObat = document.createElement("TD");
                            var newObat = document.createElement("SELECT");
                            $(newDetailCellObat).append("<h5 class=\"text-info\">" + data.detail[a].detail.nama + "</h5>");
                            /*$(newObat).attr({
                                "id": "obat_selector_" + a
                            }).addClass("obatSelector resep-obat form-control").select2();
                            $(newObat).append("<option value=\"" + data.detail[a].detail.uid + "\">" + data.detail[a].detail.nama + "</option>").val(data.detail[a].detail.uid).trigger("change");*/



                            $(newDetailCellObat).append("<b style=\"padding-top: 10px; display: block\">Batch Terpakai:</b>");
                            $(newDetailCellObat).append("<span id=\"batch_resep_" + a + "\" class=\"selected_batch\"><ol></ol></span>");
                            for(var batchSelKey in selectedBatchList)
                            {
                                $(newDetailCellObat).find("span ol").append("<li batch=\"" + selectedBatchList[batchSelKey].batch + "\"><b>[" + selectedBatchList[batchSelKey].kode + "]</b> " + selectedBatchList[batchSelKey].expired + " (" + selectedBatchList[batchSelKey].used + ")</li>");
                            }

                            $(newDetailCellObat).attr({
                                harga: harga_tertinggi
                            });


                            var newDetailCellSigna = document.createElement("TD");
                            $(newDetailCellSigna).html("<h5 class=\"text_center\">" + data.detail[a].signa_qty + " &times; " + data.detail[a].signa_pakai + "</h5>");

                            $(newDetailCellSigna).find("input").inputmask({
                                alias: 'decimal',
                                rightAlign: true,
                                placeholder: "0.00",
                                prefix: "",
                                autoGroup: false,
                                digitsOptional: true
                            });

                            var newDetailCellQty = document.createElement("TD");
                            var newQty = document.createElement("INPUT");
                            var statusSedia = "";

                            /*if(parseFloat(data.detail[a].qty) <= parseFloat(jlh_sedia))
                            {
                                statusSedia = "<b class=\"text-success text-right\"><i class=\"fa fa-check-circle\"></i> Tersedia <br />" + number_format(parseFloat(jlh_sedia), 2, ".", ",") + "</b>";
                            } else {
                                statusSedia = "<b class=\"text-danger\"><i class=\"fa fa-ban\"></i> Tersedia <br />" + number_format(parseFloat(jlh_sedia), 2, ".", ",") + "</b>";
                            }*/

                            /*if((parseFloat(data.detail[a].qty) - parseFloat(jlh_sedia)) > 0) {
                                statusSedia += "<br /><b class=\"text-warning\"><i class=\"fa fa-exclamation-circle\"></i>Butuh Amprah : " + number_format(parseFloat(data.detail[a].qty) - parseFloat(jlh_sedia), 2, ".", ",") + "</b>";
                                $("#btnSelesai").attr({
                                    "disabled": "disabled"
                                }).removeClass("btn-success").addClass("btn-danger").html("<i class=\"fa fa-ban\"></i> Selesai");
                            } else {
                                var disabledStatus = $("#btnSelesai").attr('name');
                                if (typeof attr !== typeof undefined && attr !== false) {
                                    // ...
                                } else {
                                    $("#btnSelesai").removeAttr("disabled").removeClass("btn-danger").addClass("btn-success").html("<i class=\"fa fa-check\"></i> Selesai");
                                }
                            }*/

                            $(newDetailCellQty).addClass("text_center").append("<h5 class=\"text_center\">" + parseFloat(data.detail[a].qty) + "</h5>").append(statusSedia);
                            /*$(newQty).inputmask({
                                alias: "decimal",
                                rightAlign: true,
                                placeholder: "0.00",
                                prefix: "",
                                autoGroup: false,
                                digitsOptional: true
                            }).addClass("form-control qty_resep").attr({
                                "id": "qty_resep_" + a
                            }).val(parseFloat(data.detail[a].qty));*/

                            var totalObatRaw = parseFloat(harga_tertinggi);
                            var totalObat = 0;
                            if(profit_type === "N")
                            {
                                totalObat = totalObatRaw
                            } else if(profit_type === "P")
                            {
                                totalObat = totalObatRaw + (profit / 100  * totalObatRaw);
                            } else if(profit_type === "A")
                            {
                                totalObat = totalObatRaw + profit;
                            }

                            var newDetailCellKeterangan = document.createElement("TD");
                            $(newDetailCellKeterangan).html(data.detail[a].keterangan);
                            //=======================================
                            $(newDetailRow).append(newDetailCellID);
                            $(newDetailRow).append(newDetailCellObat);
                            $(newDetailRow).append(newDetailCellSigna);
                            $(newDetailRow).append(newDetailCellQty);
                            $(newDetailRow).append(newDetailCellKeterangan);

                            $("#load-detail-resep tbody").append(newDetailRow);
                        }
                    }
                }
            } else {
                $("#resep tbody").append("<tr><td colspan=\"5\" class=\"text-center text-info\"><i class=\"fa fa-info-circle\"></i> Tidak ada resep</td></tr>");
            }









            //==================================================================================== RACIKAN
            $("#load-detail-racikan tbody").html("");
            if(data.racikan.length > 0) {
                for(var b = 0; b < data.racikan.length; b++) {
                    var racikanDetail = data.racikan[b].detail;
                    for(var racDetailKey = 0; racDetailKey < racikanDetail.length; racDetailKey++) {
                        var selectedBatchRacikan = refreshBatch(racikanDetail[racDetailKey].obat);
                        var selectedBatchListRacikan = [];
                        var harga_tertinggi_racikan = 0;
                        var kebutuhan_racikan = parseFloat(data.racikan[b].qty);
                        var jlh_sedia = 0;
                        var butuh_amprah = 0;
                        for(bKey in selectedBatchRacikan)
                        {
                            if(selectedBatchRacikan[bKey].harga > harga_tertinggi_racikan)    //Selalu ambil harga tertinggi
                            {
                                harga_tertinggi_racikan = selectedBatchRacikan[bKey].harga;
                            }

                            if(kebutuhan_racikan > 0)
                            {

                                if(kebutuhan_racikan > selectedBatchRacikan[bKey].stok_terkini)
                                {
                                    selectedBatchRacikan[bKey].used = selectedBatchRacikan[bKey].stok_terkini;
                                } else {
                                    selectedBatchRacikan[bKey].used = kebutuhan_racikan;
                                }
                                kebutuhan_racikan -= selectedBatchRacikan[bKey].stok_terkini;

                                selectedBatchListRacikan.push(selectedBatchRacikan[bKey]);
                            }

                            if(selectedBatchRacikan[bKey].gudang.uid === __UNIT__.gudang) {
                                jlh_sedia += selectedBatchRacikan[bKey].stok_terkini;
                            } else {
                                butuh_amprah += selectedBatchRacikan[bKey].stok_terkini;
                            }

                        }


                        if(selectedBatchListRacikan.length > 0)
                        {
                            var profit_racikan = 0;
                            var profit_type_racikan = "N";

                            for(var batchDetail in selectedBatchRacikan[0].profit)
                            {
                                if(selectedBatchRacikan[0].profit[batchDetail].penjamin === $("#nama-pasien").attr("set-penjamin"))
                                {
                                    profit_racikan = parseFloat(selectedBatchRacikan[0].profit[batchDetail].profit);
                                    profit_type_racikan = selectedBatchRacikan[0].profit[batchDetail].profit_type;
                                }
                            }

                            var newRacikanRow = document.createElement("TR");


                            $(newRacikanRow).addClass("racikan_row").attr({
                                "id": "racikan_group_" + data.racikan[b].uid + "_" + racDetailKey,
                                "group_racikan": data.racikan[b].uid
                            });

                            var newCellRacikanID = document.createElement("TD");
                            var newCellRacikanNama = document.createElement("TD");
                            var newCellRacikanSigna = document.createElement("TD");
                            var newCellRacikanObat = document.createElement("TD");
                            var newCellRacikanJlh = document.createElement("TD");
                            var newCellRacikanKeterangan = document.createElement("TD");

                            $(newCellRacikanID).attr("rowspan", racikanDetail.length).html((b + 1));
                            $(newCellRacikanNama).attr("rowspan", racikanDetail.length).html("<h5 style=\"margin-bottom: 20px;\">" + data.racikan[b].kode + "</h5>");
                            $(newCellRacikanSigna).addClass("text-center").attr("rowspan", racikanDetail.length).html("<h5>" + data.racikan[b].signa_qty + " &times " + data.racikan[b].signa_pakai + "</h5>");
                            $(newCellRacikanJlh).addClass("text-center").attr("rowspan", racikanDetail.length);

                            var RacikanObatData = load_product_resep(newRacikanObat, racikanDetail[racDetailKey].obat, false);
                            var newRacikanObat = document.createElement("SELECT");
                            var statusSediaRacikan = "";
                            /*if(parseFloat(data.racikan[b].qty) <= parseFloat(racikanDetail[racDetailKey].sedia))
                            {
                                statusSediaRacikan = "<b class=\"text-success text-right\"><i class=\"fa fa-check-circle\"></i> Tersedia " + racikanDetail[racDetailKey].sedia + "</b>";
                            } else {
                                statusSediaRacikan = "<b class=\"text-danger\"><i class=\"fa fa-ban\"></i> Tersedia " + racikanDetail[racDetailKey].sedia + "</b>";
                            }*/

                            if(parseFloat(data.racikan[b].qty) <= parseFloat(jlh_sedia))
                            {
                                statusSediaRacikan = "<b class=\"text-success text-right\"><i class=\"fa fa-check-circle\"></i> Tersedia <br />" + number_format(parseFloat(jlh_sedia), 2, ".", ",") + "</b>";
                            } else {
                                statusSediaRacikan = "<b class=\"text-danger\"><i class=\"fa fa-ban\"></i> Tersedia <br />" + number_format(parseFloat(jlh_sedia), 2, ".", ",") + "</b>";
                            }

                            if((parseFloat(data.racikan[b].qty) - parseFloat(jlh_sedia)) > 0) {
                                statusSediaRacikan += "<br /><b class=\"text-info\"><i class=\"fa fa-exclamation-circle\"> Stok : " + number_format(parseFloat(data.racikan[b].qty) -parseFloat(jlh_sedia), 2, ".", ",") + "</i></b>";
                                $("#btnSelesai").attr({
                                    "disabled": "disabled"
                                }).removeClass("btn-success").addClass("btn-danger").html("<i class=\"fa fa-ban\"></i> Selesai");
                            } else {
                                var disabledStatus = $("#btnSelesai").attr('name');
                                if (typeof attr !== typeof undefined && attr !== false) {
                                    $("#btnSelesai").attr({
                                        "disabled": "disabled"
                                    }).removeClass("btn-success").addClass("btn-danger").html("<i class=\"fa fa-ban\"></i> Selesai");
                                } else {
                                    $("#btnSelesai").removeAttr("disabled").removeClass("btn-danger").addClass("btn-success").html("<i class=\"fa fa-check\"></i> Selesai");
                                }
                            }

                            $(newCellRacikanObat).append("<h5 class=\"text-info\">" + RacikanObatData.data[0].nama + " <b class=\"text-danger text-right\">[" + racikanDetail[racDetailKey].kekuatan + "]</b></h5>").append(statusSediaRacikan);

                            $(newRacikanObat).attr({
                                "id": "racikan_obat_" + data.racikan[b].uid + "_" + racDetailKey,
                                "group_racikan": data.racikan[b].uid
                            }).addClass("obatSelector racikan-obat form-control").select2();
                            $(newRacikanObat).append("<option value=\"" + RacikanObatData.data[0].uid + "\">" + RacikanObatData.data[0].nama + "</option>").val(RacikanObatData.data[0].uid).trigger("change");


                            $(newCellRacikanObat).append("<b style=\"padding-top: 10px; display: block\">Batch Terpakai:</b>");
                            $(newCellRacikanObat).append("<span id=\"racikan_batch_" + data.racikan[b].uid + "_" + racDetailKey + "\" class=\"selected_batch\"><ol></ol></span>");
                            for(var batchSelKey in selectedBatchListRacikan)
                            {
                                $(newCellRacikanObat).find("span ol").append("<li batch=\"" + selectedBatchListRacikan[batchSelKey].batch + "\"><b>[" + selectedBatchListRacikan[batchSelKey].kode + "]</b> " + selectedBatchListRacikan[batchSelKey].expired + " (" + selectedBatchListRacikan[batchSelKey].used + ")</li>");
                            }

                            $(newCellRacikanObat).attr({
                                harga: harga_tertinggi_racikan
                            });

                            $(newCellRacikanJlh).html("<h5>" + data.racikan[b].qty + "<h5>");
                            $(newCellRacikanKeterangan).html(data.racikan[b].keterangan);
                            //alert(b + " - " + racDetailKey);
                            if(racDetailKey === 0) {
                                $(newRacikanRow).append(newCellRacikanID);
                                $(newRacikanRow).append(newCellRacikanNama);
                                $(newRacikanRow).append(newCellRacikanSigna);
                                $(newRacikanRow).append(newCellRacikanJlh);

                                $(newRacikanRow).append(newCellRacikanObat);
                                $(newRacikanRow).append(newCellRacikanKeterangan);
                            } else {
                                $(newRacikanRow).append(newCellRacikanObat);
                            }

                            $(newCellRacikanKeterangan).attr("rowspan", racikanDetail.length);
                            $("#load-detail-racikan tbody").append(newRacikanRow);
                        } else {
                            console.log("No Batch");
                        }
                    }
                }
            } else {
                $("#load-detail-racikan tbody").append("<tr><td colspan=\"6\" class=\"text-center text-info\"><i class=\"fa fa-info-circle\"></i> Tidak ada racikan</td></tr>");
            }
        }

        $("#btnTambahAsesmen").click(function() {
            $(this).attr({
                "disabled": "disabled"
            }).removeClass("btn-info").addClass("btn-warning").html("<i class=\"fa fa-sync\"></i> Menambahkan Asesmen");

            var formData = {
                request: "tambah_asesmen",
                penjamin: __PAGES__[5],
                kunjungan: __PAGES__[4],
                pasien: __PAGES__[3],
                poli: __POLI_INAP__
            };

            $.ajax({
                url: __HOSTAPI__ + "/Inap",
                async:false,
                beforeSend: function(request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                type:"POST",
                data: formData,
                success:function(response) {
                    location.href = __HOSTNAME__ + "/rawat_inap/dokter/antrian/" + response.response_package.response_values[0] + "/" + __PAGES__[3] + "/" + __PAGES__[4];
                },
                error: function(response) {
                    console.log(response);
                }
            });
        });



        $(".print_manager").click(function() {
            var targetSurat = $(this).attr("id");
            $("#target-judul-cetak").html("CETAK " + targetSurat.toUpperCase() + " PASIEN");
            $.ajax({
                async: false,
                url: __HOST__ + "miscellaneous/print_template/pasien_" + targetSurat + ".php",
                beforeSend: function (request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                type: "POST",
                data: {
                    pc_customer: __PC_CUSTOMER__,
                    no_rm:$("#rm_pasien").html(),
                    pasien: "An. " + $("#nama_pasien").html(),
                    tanggal_lahir: $("#tanggal_lahir_pasien").html(),
                    usia: $("#usia_pasien").html() + " tahun",
                    dokter: __MY_NAME__,
                    waktu_masuk: selected_waktu_masuk,
                    alamat: $("#alamat_pasien").html(),
                    tempat_lahir: $("#tempat_lahir_pasien").html()
                },
                success: function (response) {
                    //$("#dokumen-viewer").html(response);
                    var containerItem = document.createElement("DIV");
                    $(containerItem).html(response);
                    $(containerItem).printThis({
                        importCSS: true,
                        base: false,
                        pageTitle: "igd",
                        afterPrint: function() {
                            $("#cetak").modal("hide");
                            $("#dokumen-viewer").html("");
                        }
                    });
                }
            });
        });
    });
</script>



<div id="form-berikan-resep" class="modal fade" role="dialog" aria-labelledby="modal-large-title" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-large-title">Berikan Resep</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card-group">
                            <div class="card card-body">
                                <div class="d-flex flex-row">
                                    <div class="col-md-12">
                                        <b class="nama_pasien" id="resep-nama-pasien"></b>
                                        <br />
                                        <span class="jk_pasien" id="jk-pasien"></span>
                                        <br />
                                        <span class="tanggal_lahir_pasien" id="tanggal-lahir-pasien"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="card card-body">
                                <div class="d-flex flex-row">
                                    <div class="col-md-12">
                                        <b>Detail Info</b>
                                        <hr />
                                        <table class="form-mode">
                                            <tr>
                                                <td>Diresep tanggal</td>
                                                <td class="wrap_content">:</td>
                                                <td id="resep_tanggal"></td>
                                                <td>Oleh</td>
                                                <td class="wrap_content">:</td>
                                                <td id="resep_dokter"></td>
                                            </tr>
                                            <tr>
                                                <td>Diverifikasi Oleh</td>
                                                <td class="wrap_content">:</td>
                                                <td id="resep_verifikator"></td>
                                                <td>Nomor Mutasi</td>
                                                <td class="wrap_content">:</td>
                                                <td id="resep_mutasi"></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header card-header-large bg-white d-flex align-items-center">
                                <h5 class="card-header__title flex m-0">Resep</h5>
                            </div>
                            <div class="card-body tab-content">
                                <div class="tab-pane active show fade" id="resep-biasa">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <table id="load-detail-resep" class="table table-bordered largeDataType">
                                                <thead class="thead-dark">
                                                <tr>
                                                    <th class="wrap_content"><i class="fa fa-hashtag"></i></th>
                                                    <th style="width: 40%;">Obat</th>
                                                    <th width="15%">Signa</th>
                                                    <th width="15%">Jumlah</th>
                                                    <th>Keterangan</th>
                                                </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header card-header-large bg-white d-flex align-items-center">
                                <h5 class="card-header__title flex m-0">Racikan</h5>
                            </div>
                            <div class="card-body tab-content">
                                <div class="tab-pane active show fade" id="resep-racikan">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <table id="load-detail-racikan" class="table table-bordered largeDataType">
                                                <thead class="thead-dark">
                                                <tr>
                                                    <th class="wrap_content"><i class="fa fa-hashtag"></i></th>
                                                    <th width="20%;">Racikan</th>
                                                    <th style="width: 15%;">Signa</th>
                                                    <th class="wrap_content">Jumlah</th>
                                                    <th width="30%;">Obat</th>
                                                    <th>Keterangan</th>
                                                </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Kembali</button>
                <button type="button" class="btn btn-primary" id="btnSubmitBerikanObat">Berikan</button>
            </div>
        </div>
    </div>
</div>

<div id="form-konfirmasi-berikan-resep" class="modal fade" role="dialog" aria-labelledby="modal-large-title" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-large-title">Konfirmasi Jumlah Obat</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered largeDataType" id="list-konfirmasi-berikan-obat">
                            <thead class="thead-dark">
                                <tr>
                                    <th class="wrap_content">No</th>
                                    <th>Obat/Racikan</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Kembali</button>
                <button type="button" class="btn btn-primary" id="btnKonfirmasiBerikanObat">Berikan</button>
            </div>
        </div>
    </div>
</div>