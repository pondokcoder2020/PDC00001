<script src="<?php echo __HOSTNAME__; ?>/plugins/printThis/printThis.js"></script>
<script type="text/javascript">
    $(function () {
        /*var queryDate = <?php echo json_encode(date('Y-m-d')); ?>, dateParts = queryDate.match(/(\d+)/g), realDate = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);

        $("#txt_bpjs_tanggal_rujukan").datepicker("setDate", realDate);*/
        $("#txt_bpjs_laka_tanggal").attr({
            "disabled": "disabled"
        });


        var selectedSEP = "", selectedSEPNo = "";
        var selectedLakaPenjamin = [];
        var isRujukan;
        $("#txt_bpjs_kelas_rawat").attr({
            "disabled": "disabled"
        });
        $("#range_sep").change(function() {
            if(
                !Array.isArray(getDateRange("#range_sep")[0]) &&
                !Array.isArray(getDateRange("#range_sep")[1])
            ) {
                SEPList.ajax.reload();
            }
        });

        $("#jenis_pelayanan").select2().on("select2:select", function(e) {
            SEPList.ajax.reload();
        });

        var refreshData = 'N';

        $("#btn_sync_bpjs").click(function() {
            refreshData = 'Y';
            SEPList.ajax.reload(function () {
                refreshData = 'N';
            });
        });

        function switchSEPParam(refreshData = false) {
            return {

            }
        }

        var SEPList = $("#table-sep").DataTable({
            processing: true,
            serverSide: true,
            sPaginationType: "full_numbers",
            bPaginate: true,
            serverMethod: "POST",
            "ajax": {
                url: __HOSTAPI__ + "/BPJS",
                type: "POST",
                headers: {
                    Authorization: "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>
                },
                data: function (d) {
                    d.request = "get_history_sep_local";
                    d.dari = getDateRange("#range_sep")[0];
                    d.sampai = getDateRange("#range_sep")[1];
                    d.pelayanan_jenis = $("#jenis_pelayanan").val();
                    d.sync_bpjs = refreshData;
                },
                dataSrc: function (response) {
                    var data = response.response_package.response_data;
                    console.clear();
                    console.log(data);

                    if (data === undefined || data === null) {
                        return [];
                    } else {
                        return data;
                    }

                }
            },
            autoWidth: false,
            "bInfo": false,
            lengthMenu: [[-1], ["All"]],
            aaSorting: [[0, "asc"]],
            "columnDefs": [{
                "targets": 0,
                "className": "dt-body-left"
            }],
            "columns": [
                {
                    "data": null, render: function (data, type, row, meta) {
                        return row.autonum;
                    }
                },
                {
                    "data": null, render: function (data, type, row, meta) {
                        return "<b id=\"sep_no_" + row.uid + "\">" + row.sep_no + "</b>";
                    }
                },
                {
                    "data": null, render: function (data, type, row, meta) {
                        return "<b class=\"text-info\">" + row.pasien.no_rm + "</b><br />" + ((row.pasien.panggilan_name !== undefined) ? row.pasien.panggilan_name.nama : "") + " " + row.pasien.nama;
                    }
                },
                {
                    "data": null, render: function (data, type, row, meta) {
                        var allowClaim = "";
                        if(row.claim.length > 0) {
                            allowClaim = "";
                        } else {
                            allowClaim = "<button class=\"btn btn-purple btn-sm btn-buat-claim\" no_sep=\"" + row.sep_no + "\" id=\"sep_buat_claim_" + row.uid + "\">" +
                                "<i class=\"fa fa-search\"></i> Claim" +
                                "</button>";
                        }
                        if(row.claim !== undefined && row.claim !== null) {
                            if(row.claim.length > 0) {
                                return "<div class=\"btn-group wrap_content\" role=\"group\" aria-label=\"Basic example\">" +
                                    "<button class=\"btn btn-info btn-sm btn-edit-sep\" no_sep=\"" + row.sep_no + "\" id=\"sep_edit_" + row.uid + "\">" +
                                    "<i class=\"fa fa-pencil-alt\"></i> Edit" +
                                    "</button>" +
                                    "<button class=\"btn btn-success btn-sm btn-cetak-sep\" no_sep=\"" + row.sep_no + "\" id=\"sep_cetak_" + row.uid + "\">" +
                                    "<i class=\"fa fa-print\"></i> Cetak" +
                                    "</button>" +
                                    allowClaim +
                                    "<button disabled class=\"btn btn-danger btnHapusSEP\" id=\"hapus_" + row.sep_no + "\"><i class=\"fa fa-ban\"></i> Hapus</button>" +
                                    "</div>";
                            } else {
                                return "<div class=\"btn-group wrap_content\" role=\"group\" aria-label=\"Basic example\">" +
                                    "<button class=\"btn btn-info btn-sm btn-edit-sep\" no_sep=\"" + row.sep_no + "\" id=\"sep_edit_" + row.uid + "\">" +
                                    "<i class=\"fa fa-pencil-alt\"></i> Edit" +
                                    "</button>" +
                                    "<button class=\"btn btn-success btn-sm btn-cetak-sep\" no_sep=\"" + row.sep_no + "\" id=\"sep_cetak_" + row.uid + "\">" +
                                    "<i class=\"fa fa-print\"></i> Cetak" +
                                    "</button>" +
                                    allowClaim +
                                    "<button class=\"btn btn-danger btnHapusSEP\" id=\"hapus_" + row.sep_no + "\"><i class=\"fa fa-ban\"></i> Hapus</button>" +
                                    "</div>";
                            }
                        } else {
                            return "<div class=\"btn-group wrap_content\" role=\"group\" aria-label=\"Basic example\">" +
                                "<button class=\"btn btn-info btn-sm btn-edit-sep\" no_sep=\"" + row.sep_no + "\" id=\"sep_edit_" + row.uid + "\">" +
                                "<i class=\"fa fa-pencil-alt\"></i> Edit" +
                                "</button>" +
                                "<button class=\"btn btn-success btn-sm btn-cetak-sep\" no_sep=\"" + row.sep_no + "\" id=\"sep_cetak_" + row.uid + "\">" +
                                "<i class=\"fa fa-print\"></i> Cetak" +
                                "</button>" +
                                allowClaim +
                                "<button class=\"btn btn-danger btnHapusSEP\" id=\"hapus_" + row.sep_no + "\"><i class=\"fa fa-ban\"></i> Hapus</button>" +
                                "</div>";
                        }


                    }
                }
            ]
        });

        $("body").on("click", ".btn-buat-claim", function() {
            var id = $(this).attr("id").split("_");
            id = id[id.length - 1];
            selectedSEP = $(this).attr("no_sep");
            $("#modal-sep-claim").modal("show");
        });

        $("body").on("click", ".btn-cetak-sep", function() {
            var id = $(this).attr("id").split("_");
            id = id[id.length - 1];

            var SEPButton = $(this);
            SEPButton.html("Memuat SEP...").removeClass("btn-success").addClass("btn-warning");

            $.ajax({
                async: false,
                url: __HOSTAPI__ + "/BPJS/get_sep_detail/" + id,
                beforeSend: function (request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                type: "GET",
                success: function (response) {

                    var dataSEP = response.response_package.response_data[0];
                    $("#sep_nomor").html(dataSEP.sep_no);
                    $("#sep_tanggal").html(dataSEP.sep_tanggal);
                    $("#sep_spesialis").html(dataSEP.poli_tujuan_detail.kode + " - " + dataSEP.poli_tujuan_detail.nama);
                    $("#sep_faskes_asal").html(dataSEP.asal_rujukan_ppk + " - " + ((dataSEP.asal_rujukan_nama !== undefined && dataSEP.asal_rujukan_nama !== null && dataSEP.asal_rujukan_nama !== "null") ? dataSEP.asal_rujukan_nama : "[TIDAK DITEMUKAN]") + "<b class=\"text-info\">[No. Rujuk: " + dataSEP.asal_rujukan_nomor + "]");
                    $("#sep_diagnosa_awal").html(dataSEP.diagnosa_nama);
                    $("#sep_catatan").html(dataSEP.catatan);
                    $("#sep_kelas_rawat").html(dataSEP.kelas_rawat.nama);
                    $("#sep_jenis_rawat").html((parseInt(dataSEP.pelayanan_jenis) === 1) ? "Rawat Inap" : "Rawat Jalan");


                    var penjaminList = dataSEP.pasien.history_penjamin;
                    for(var pKey in penjaminList) {
                        if(penjaminList[pKey].penjamin === __UIDPENJAMINBPJS__) {
                            var metaData = JSON.parse(penjaminList[pKey].rest_meta);
                            $("#sep_nomor_kartu").html(metaData.response.peserta.noKartu);
                            $("#sep_nama_peserta").html(metaData.response.peserta.nama + "<b class=\"text-info\">[" + metaData.response.peserta.mr.noMR + "]</b>");
                            $("#sep_tanggal_lahir").html(metaData.response.peserta.tglLahir);
                            $("#sep_nomor_telepon").html(metaData.response.peserta.mr.noTelepon);
                            $("#sep_peserta").html(metaData.response.peserta.jenisPeserta.keterangan);
                            if(
                                metaData.response.peserta.cob.noAsuransi !== undefined &&
                                metaData.response.peserta.cob.nmAsuransi !== undefined &&
                                metaData.response.peserta.cob.noAsuransi !== "" &&
                                metaData.response.peserta.cob.nmAsuransi !== "" &&
                                metaData.response.peserta.cob.noAsuransi !== null &&
                                metaData.response.peserta.cob.nmAsuransi !== null
                            ) {
                                $("#sep_cob").html(metaData.response.peserta.cob.noAsuransi + " - " + metaData.response.peserta.cob.nmAsuransi);
                            } else {
                                $("#sep_cob").html("-");
                            }
                        }
                    }
                    $("#modal-sep-cetak").modal("show");
                    SEPButton.html("<i class=\"fa fa-print\"></i> Cetak").removeClass("btn-warning").addClass("btn-success");
                },
                error: function (response) {
                    //
                }
            });
        });

        $("#btnCetakSEP").click(function() {
            $.ajax({
                async: false,
                url: __HOST__ + "miscellaneous/print_template/bpjs_sep.php",
                beforeSend: function (request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                type: "POST",
                data: {
                    __PC_CUSTOMER__: __PC_CUSTOMER__,
                    html_data_kiri: $("#data_sep_cetak_kiri").html(),
                    html_data_kanan: $("#data_sep_cetak_kanan").html(),
                    html_data_bawah: $("#data_sep_cetak_bawah").html()
                },
                success: function (response) {
                    //$("#dokumen-viewer").html(response);
                    var containerItem = document.createElement("DIV");
                    $(containerItem).html(response);
                    $(containerItem).printThis({
                        importCSS: true,
                        base: false,
                        pageTitle: "Cetak SEP",
                        afterPrint: function() {
                            //
                        }
                    });
                }
            });
        });

        $("body").on("click", ".btn-edit-sep", function() {
            var id = $(this).attr("id").split("_");
            id = id[id.length - 1];

            selectedSEP = id;
            selectedSEPNo = $("#sep_no_" + id).html().trim();

            var SEPButton = $(this);
            SEPButton.html("Memuat SEP...").removeClass("btn-info").addClass("btn-warning");

            $.ajax({
                async: false,
                url: __HOSTAPI__ + "/BPJS/get_sep_detail/" + id,
                beforeSend: function (request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                type: "GET",
                success: function (response) {
                    var data = {};
                    if(
                        response.response_package.response_data !== undefined &&
                        response.response_package.response_data.length > 0
                    ) {
                        data = response.response_package.response_data[0];

                        console.clear();
                        console.log(data);

                        //Pasien Info
                        var Pasien = data.pasien;
                        var pasien_penjamin = Pasien.history_penjamin;
                        var bpjs_no = "";

                        for(var pKey in pasien_penjamin) {
                            if(pasien_penjamin[pKey].penjamin === __UIDPENJAMINBPJS__) {
                                var rest_meta = JSON.parse(pasien_penjamin[pKey].rest_meta);
                                bpjs_no = rest_meta.response.peserta.noKartu;
                            }
                        }

                        $("#txt_bpjs_nomor").val(bpjs_no);
                        $("#txt_bpjs_nik").val(Pasien.nik);
                        $("#txt_bpjs_nama").val(Pasien.nama);
                        $("#txt_bpjs_telepon").val(Pasien.no_telp);
                        $("#txt_bpjs_rm").val(Pasien.no_rm);

                        $("#txt_bpjs_faskes").select2();
                        $("#txt_bpjs_jenis_layanan").select2();
                        $("#txt_bpjs_kelas_rawat").select2();







                        $.ajax({
                            async: false,
                            url:__HOSTAPI__ + "/BPJS/get_rujukan_list/" + bpjs_no,
                            type: "GET",
                            beforeSend: function(request) {
                                request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                            },
                            success: function(response) {

                                $("#txt_bpjs_nomor_rujukan " + " option").remove();

                                if(
                                    response.response_package.content !== undefined &&
                                    response.response_package.content.response !== null
                                ) {
                                    if(parseInt(response.response_package.content.metaData.code) === 200) {
                                        $("#panel-rujukan").show();
                                        var data = response.response_package.content.response.rujukan;
                                        selectedListRujukan = data;



                                        if(data.length > 0) {
                                            isRujukan = true;
                                            for(var a = 0; a < data.length; a++) {
                                                if(parseInt(data[a].pelayanan.kode) === 2) {
                                                    var selection = document.createElement("OPTION");

                                                    $(selection).attr("value", data[a].noKunjungan.toUpperCase()).html(data[a].noKunjungan.toUpperCase());
                                                    $("#txt_bpjs_nomor_rujukan").append(selection);
                                                }
                                            }

                                            $(".informasi_rujukan").show();
                                            $("#btnProsesSEP").show();
                                            loadInformasiRujukan(selectedListRujukan[0]);
                                            loadDPJP("#txt_bpjs_dpjp", $("#txt_bpjs_jenis_asal_rujukan").val(), $("#txt_bpjs_dpjp_spesialistik").val());
                                        } else {
                                            isRujukan = false;
                                            $(".informasi_rujukan").hide();
                                            $("#btnProsesSEP").hide();
                                        }
                                    } else {
                                        isRujukan = false
                                        $(".informasi_rujukan").hide();
                                        $("#panel-rujukan").hide();
                                        $("#btnProsesSEP").hide();
                                    }
                                } else {
                                    isRujukan = false
                                    $(".informasi_rujukan").hide();
                                    $("#panel-rujukan").hide();
                                    $("#btnProsesSEP").hide();
                                }

                                if(!isRujukan) {
                                    $("#btnProsesSEP").show();
                                    $(".informasi_rujukan").show();
                                    //$("#panel-rujukan").show();
                                }
                            },
                            error: function(response) {
                                console.log(response);
                            }
                        });

                        loadKelasRawat("#txt_bpjs_kelas_rawat", data.kelas_rawat.nama);

                        $("#txt_bpjs_poli_tujuan").select2({
                            minimumInputLength: 2,
                            "language": {
                                "noResults": function(){
                                    return "Faskes tidak ditemukan";
                                }
                            },
                            dropdownParent: $("#group_poli"),
                            ajax: {
                                dataType: "json",
                                headers:{
                                    "Authorization" : "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>,
                                    "Content-Type" : "application/json",
                                },
                                url:__HOSTAPI__ + "/BPJS/get_poli",
                                type: "GET",
                                data: function (term) {
                                    return {
                                        search:term.term
                                    };
                                },
                                cache: true,
                                processResults: function (response) {
                                    var data = response.response_package.content.response.poli;
                                    return {
                                        results: $.map(data, function (item) {
                                            return {
                                                text: item.nama,
                                                id: item.kode
                                            }
                                        })
                                    };
                                }
                            }
                        }).addClass("form-control").on("select2:select", function(e) {
                            //
                        });


                        $("#txt_bpjs_poli_tujuan").append("<option title=\"" + data.poli_tujuan_detail.kode + "\" value=\"" + data.poli_tujuan_detail.kode + "\">" + data.poli_tujuan_detail.nama + "</option>");
                        $("#txt_bpjs_poli_tujuan").select2("data", {id: data.poli_tujuan_detail.kode, text: data.poli_tujuan_detail.nama});
                        $("#txt_bpjs_poli_tujuan").trigger("change");


                        $("#txt_bpjs_diagnosa_awal").select2({
                            minimumInputLength: 2,
                            "language": {
                                "noResults": function(){
                                    return "Diagnosa tidak ditemukan";
                                }
                            },
                            dropdownParent: $("#group_diagnosa"),
                            ajax: {
                                dataType: "json",
                                headers:{
                                    "Authorization" : "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>,
                                    "Content-Type" : "application/json",
                                },
                                url:__HOSTAPI__ + "/BPJS/get_diagnosa",
                                type: "GET",
                                data: function (term) {
                                    return {
                                        search:term.term
                                    };
                                },
                                cache: true,
                                processResults: function (response) {
                                    var data = response.response_package.content.response.diagnosa;
                                    return {
                                        results: $.map(data, function (item) {
                                            return {
                                                text: item.nama,
                                                id: item.kode
                                            }
                                        })
                                    };
                                }
                            }
                        }).addClass("form-control").on("select2:select", function(e) {
                            //
                        });


                        $("#txt_bpjs_diagnosa_awal").append("<option title=\"" + data.diagnosa_kode + "\" value=\"" + data.diagnosa_kode + "\">" + data.diagnosa_nama + "</option>");
                        $("#txt_bpjs_diagnosa_awal").select2("data", {id: data.diagnosa_kode, text: data.diagnosa_nama});
                        $("#txt_bpjs_diagnosa_awal").trigger("change");

                        $("#txt_bpjs_catatan").val(data.catatan);
                        $("#txt_bpjs_skdp").val(data.skdp_no_surat);
                        loadSpesialistik("#txt_bpjs_dpjp_spesialistik", {
                            kode: data.spesialistik_kode,
                            nama: data.spesialistik_nama
                        }, {
                            kode: data.skdp_dpjp,
                            nama: data.skdp_dpjp_nama
                        });
                        $("#txt_bpjs_dpjp_spesialistik").select2();
                        $("#txt_bpjs_dpjp").select2();

                        var prov = loadProvinsi("#txt_bpjs_laka_suplesi_provinsi", data.laka_lantas_provinsi);
                        var kab = loadKabupaten("#txt_bpjs_laka_suplesi_kabupaten", $("#txt_bpjs_laka_suplesi_provinsi").val(), data.laka_lantas_kabupaten);
                        var kec = loadKecamatan("#txt_bpjs_laka_suplesi_kecamatan", $("#txt_bpjs_laka_suplesi_kabupaten").val(), data.laka_lantas_kecamatan);






                        $("#txt_bpjs_laka_suplesi_provinsi").select2({
                            dropdownParent: $("#group_provinsi"),
                            data: {id: data.laka_lantas_provinsi, text: prov}
                        });

                        $("#txt_bpjs_dpjp_spesialistik").select2({
                            dropdownParent: $("#group_spesialistik")
                        });

                        $("#txt_bpjs_laka_suplesi_kabupaten").select2({
                            dropdownParent: $("#group_kabupaten"),
                            data: {id: data.laka_lantas_kabupaten, text: kab}
                        });

                        $("#txt_bpjs_laka_suplesi_kecamatan").select2({
                            dropdownParent: $("#group_kecamatan"),
                            data: {id: data.laka_lantas_kecamatan, text: kec}
                        });

                        $("#txt_bpjs_nomor_rujukan").select2({
                            autoclose: true,
                            dropdownParent: $("#group_nomor_rujukan")
                        });

                        $("#txt_bpjs_dpjp").select2({
                            dropdownParent: $("#group_dpjp")
                        });

                        $("#txt_bpjs_kelas_rawat").select2({
                            dropdownParent: $("#group_kelas_rawat")
                        });

                        $("#txt_bpjs_asal_rujukan").select2({disabled:"readonly"});

                        $("#txt_bpjs_jenis_asal_rujukan").select2({disabled:"readonly"});





                        $("#txt_bpjs_jenis_asal_rujukan").change(function() {
                            loadDPJP("#txt_bpjs_dpjp", $("#txt_bpjs_jenis_asal_rujukan").val(), $("#txt_bpjs_dpjp_spesialistik").val());
                        });

                        $("#txt_bpjs_dpjp_spesialistik").change(function() {
                            loadDPJP("#txt_bpjs_dpjp", $("#txt_bpjs_jenis_asal_rujukan").val(), $("#txt_bpjs_dpjp_spesialistik").val());
                        });

                        $("#txt_bpjs_nomor_rujukan").change(function() {
                            loadInformasiRujukan(selectedListRujukan[$(this).find("option:selected").index()]);
                        });

                        $("input[name=\"txt_bpjs_cob\"][value=\"" + data.pasien_cob + "\"]").prop("checked", true);
                        $("input[name=\"txt_bpjs_katarak\"][value=\"" + data.pasien_katarak + "\"]").prop("checked", true);
                        $("input[name=\"txt_bpjs_laka\"][value=\"" + data.laka_lantas + "\"]").prop("checked", true);

                        if(parseInt(data.laka_lantas) > 0) {
                            $(".laka_lantas_container").show();
                        } else {
                            $(".laka_lantas_container").hide();
                        }
                        if(parseInt(data.laka_lantas_suplesi) > 0) {
                            $(".laka_lantas_suplesi_container").show();
                        } else {
                            $(".laka_lantas_suplesi_container").hide();
                        }

                        var laka_penjamin = data.laka_lantas_penjamin.split(",");
                        for(var lakaKey in laka_penjamin) {
                            $("input[name=\"txt_bpjs_laka_penjamin\"][value=\"" + laka_penjamin[lakaKey] + "\"]").prop("checked", true);
                            if(selectedLakaPenjamin.indexOf(laka_penjamin[lakaKey]) < 0) {
                                if(parseInt(laka_penjamin[lakaKey]) > 0) {
                                    selectedLakaPenjamin.push(laka_penjamin[lakaKey]);
                                }
                            }
                        }

                        $("#txt_bpjs_laka_tanggal").datepicker({
                            dateFormat: "DD, dd MM yy",
                            autoclose: true
                        }).datepicker("setDate", new Date(data.laka_lantas_tanggal));

                        //$("#txt_bpjs_laka_tanggal").val(data.laka_lantas_tanggal);
                        $("#txt_bpjs_laka_keterangan").val(data.laka_lantas_keterangan);
                        $("input[name=\"txt_bpjs_laka_suplesi\"][value=\"" + data.laka_lantas_suplesi + "\"]").prop("checked", true);
                        $("#txt_bpjs_laka_suplesi_nomor").val(data.laka_lantas_suplesi_sep);






                        SEPButton.html("<i class=\"fa fa-pencil-alt\"></i> Edit").removeClass("btn-warning").addClass("btn-info");

                        //$("#txt_bpjs_nomor").val(data.sep_no);
                        //$("#txt_bpjs_faskes").val();
                        $("#txt_bpjs_rm").val().replace(new RegExp(/-/g), data.pasien.no_rm);


                        /*
                        no_kartu: ,
                        ppk_pelayanan: ,
                        kelas_rawat: $("#txt_bpjs_kelas_rawat").val(),
                        no_mr: ,
                        asal_rujukan: $("#txt_bpjs_jenis_asal_rujukan").val(),
                        ppk_rujukan: $("#txt_bpjs_asal_rujukan").val(),
                        tgl_rujukan: parse_tanggal_rujukan,
                        no_rujukan: $("#txt_bpjs_nomor_rujukan").val(),
                        catatan: $("#txt_bpjs_catatan").val(),
                        diagnosa_awal: $("#txt_bpjs_diagnosa_awal").val(),
                        diagnosa_kode: $("#txt_bpjs_diagnosa_awal option:selected").text(),
                        poli: $("#txt_bpjs_poli_tujuan").val(),
                        eksekutif: $("input[type=\"radio\"][name=\"txt_bpjs_poli_eksekutif\"]:checked").val(),
                        cob: $("input[type=\"radio\"][name=\"txt_bpjs_cob\"]:checked").val(),
                        katarak: $("input[type=\"radio\"][name=\"txt_bpjs_katarak\"]:checked").val(),

                        laka_lantas: $("input[type=\"radio\"][name=\"txt_bpjs_laka\"]:checked").val(),
                        laka_lantas_penjamin: selectedLakaPenjamin.join(","),
                        laka_lantas_tanggal_kejadian: parse_tanggal_laka,
                        laka_lantas_keterangan: $("#txt_bpjs_laka_keterangan").val(),
                        laka_lantas_suplesi: $("input[type=\"radio\"][name=\"txt_bpjs_laka_suplesi\"]:checked").val(),
                        laka_lantas_suplesi_nomor: $("#txt_bpjs_laka_suplesi_nomor").val(),
                        laka_lantas_suplesi_provinsi: $("#txt_bpjs_laka_suplesi_provinsi").val(),
                        laka_lantas_suplesi_kabupaten: $("#txt_bpjs_laka_suplesi_kabupaten").val(),
                        laka_lantas_suplesi_kecamatan: $("#txt_bpjs_laka_suplesi_kecamatan").val(),

                        skdp: $("#txt_bpjs_skdp").val(),
                        dpjp: $("#txt_bpjs_dpjp").val(),
                        telepon: $("#txt_bpjs_telepon").val()
                        * */

                        var dataSEP = response.response_package.response_data[0];

                        var penjaminList = dataSEP.pasien.history_penjamin;
                        for(var pKey in penjaminList) {
                            if(penjaminList[pKey].penjamin === __UIDPENJAMINBPJS__) {
                                //var metaData = JSON.parse(penjaminList[pKey].penjamin_detail.rest_meta);


                            }
                        }
                        $("#modal-sep").modal("show");

                    } else {

                    }
                },
                error: function (response) {
                    //
                }
            });
        });

        $("input[type=\"checkbox\"][name=\"txt_bpjs_laka_penjamin\"]").change(function() {
            var selectedvalue = $(this).val();
            if($(this).is(":checked")) {
                if(selectedLakaPenjamin.indexOf(selectedvalue) < 0)
                {
                    if(parseInt(selectedvalue) > 0) {
                        selectedLakaPenjamin.push(selectedvalue);
                    }
                }
            } else {
                selectedLakaPenjamin.splice(selectedLakaPenjamin.indexOf(selectedvalue), 1);
            }
        });

        $("input[type=\"radio\"][name=\"txt_bpjs_laka\"]").change(function() {
            if(parseInt($(this).val()) === 1) {
                $(".laka_lantas_container").fadeIn();
            } else {
                $(".laka_lantas_container").fadeOut();
            }
        });

        $("input[type=\"radio\"][name=\"txt_bpjs_laka_suplesi\"]").change(function() {
            if(parseInt($(this).val()) === 1) {
                $(".laka_lantas_suplesi_container").fadeIn();
            } else {
                $(".laka_lantas_suplesi_container").fadeOut();
            }
        });

        $("#btnProsesSEP").click(function () {
            Swal.fire({
                title: 'Data sudah benar?',
                showDenyButton: true,
                confirmButtonText: `Sudah`,
                denyButtonText: `Belum`,
            }).then((result) => {
                if (result.isConfirmed) {
                    var tanggal_rujukan = new Date($("#txt_bpjs_tanggal_rujukan").datepicker("getDate"));
                    var parse_tanggal_rujukan =  tanggal_rujukan.getFullYear() + "-" + str_pad(2, tanggal_rujukan.getMonth()+1) + "-" + str_pad(2, tanggal_rujukan.getDate());


                    var tanggal_laka = new Date($("#txt_bpjs_laka_tanggal").datepicker("getDate"));
                    var parse_tanggal_laka =  tanggal_laka.getFullYear() + "-" + str_pad(2, tanggal_laka.getMonth()+1) + "-" + str_pad(2, tanggal_laka.getDate());

                    if(isRujukan)
                    {
                        dataSetSEP = {
                            request: "sep_edit",
                            uid : selectedSEP,
                            sep: selectedSEPNo,
                            no_kartu: $("#txt_bpjs_nomor").val(),
                            spesialistik_kode: $("#txt_bpjs_dpjp_spesialistik").val(),
                            spesialistik_nama: $("#txt_bpjs_dpjp_spesialistik option:selected").text(),
                            ppk_pelayanan: $("#txt_bpjs_faskes").val(),
                            kelas_rawat: $("#txt_bpjs_kelas_rawat").val(),
                            no_mr: $("#txt_bpjs_rm").val().replace(new RegExp(/-/g),""),
                            asal_rujukan: $("#txt_bpjs_jenis_asal_rujukan").val(),
                            ppk_rujukan: $("#txt_bpjs_asal_rujukan").val(),
                            tgl_rujukan: parse_tanggal_rujukan,
                            no_rujukan: $("#txt_bpjs_nomor_rujukan").val(),
                            catatan: $("#txt_bpjs_catatan").val(),
                            diagnosa_awal: $("#txt_bpjs_diagnosa_awal").val(),
                            diagnosa_kode: $("#txt_bpjs_diagnosa_awal option:selected").text(),
                            poli: $("#txt_bpjs_poli_tujuan").val(),
                            eksekutif: $("input[type=\"radio\"][name=\"txt_bpjs_poli_eksekutif\"]:checked").val(),
                            cob: $("input[type=\"radio\"][name=\"txt_bpjs_cob\"]:checked").val(),
                            katarak: $("input[type=\"radio\"][name=\"txt_bpjs_katarak\"]:checked").val(),

                            laka_lantas: $("input[type=\"radio\"][name=\"txt_bpjs_laka\"]:checked").val(),
                            laka_lantas_penjamin: selectedLakaPenjamin.join(","),
                            laka_lantas_tanggal_kejadian: parse_tanggal_laka,
                            laka_lantas_keterangan: $("#txt_bpjs_laka_keterangan").val(),
                            laka_lantas_suplesi: $("input[type=\"radio\"][name=\"txt_bpjs_laka_suplesi\"]:checked").val(),
                            laka_lantas_suplesi_nomor: $("#txt_bpjs_laka_suplesi_nomor").val(),
                            laka_lantas_suplesi_provinsi: $("#txt_bpjs_laka_suplesi_provinsi").val(),
                            laka_lantas_suplesi_kabupaten: $("#txt_bpjs_laka_suplesi_kabupaten").val(),
                            laka_lantas_suplesi_kecamatan: $("#txt_bpjs_laka_suplesi_kecamatan").val(),

                            skdp: $("#txt_bpjs_skdp").val(),
                            dpjp: $("#txt_bpjs_dpjp").val(),
                            dpjp_nama: $("#txt_bpjs_dpjp option:selected").text(),
                            telepon: $("#txt_bpjs_telepon").val()
                        };
                    } else {
                        dataSetSEP = {
                            request: "sep_edit",
                            uid : selectedSEP,
                            sep: selectedSEPNo,
                            spesialistik_kode: $("#txt_bpjs_dpjp_spesialistik").val(),
                            spesialistik_nama: $("#txt_bpjs_dpjp_spesialistik option:selected").text(),
                            no_kartu: $("#txt_bpjs_nomor").val(),
                            ppk_pelayanan: $("#txt_bpjs_faskes").val(),
                            kelas_rawat: $("#txt_bpjs_kelas_rawat").val(),
                            no_mr: $("#txt_bpjs_rm").val().replace(new RegExp(/-/g),""),
                            asal_rujukan: $("#txt_bpjs_jenis_asal_rujukan").val(),
                            ppk_rujukan: /*$("#txt_bpjs_asal_rujukan").val()*/"00010001",
                            tgl_rujukan: <?php echo json_encode(date('Y-m-d', strtotime("-1 days"))); ?>,
                            no_rujukan: "1234567",
                            catatan: $("#txt_bpjs_catatan").val(),
                            diagnosa_awal: $("#txt_bpjs_diagnosa_awal").val(),
                            diagnosa_kode: $("#txt_bpjs_diagnosa_awal option:selected").text(),
                            poli: $("#txt_bpjs_poli_tujuan").val(),
                            eksekutif: $("input[type=\"radio\"][name=\"txt_bpjs_poli_eksekutif\"]:checked").val(),
                            cob: $("input[type=\"radio\"][name=\"txt_bpjs_cob\"]:checked").val(),
                            katarak: $("input[type=\"radio\"][name=\"txt_bpjs_katarak\"]:checked").val(),

                            laka_lantas: $("input[type=\"radio\"][name=\"txt_bpjs_laka\"]:checked").val(),
                            laka_lantas_penjamin: selectedLakaPenjamin.join(","),
                            laka_lantas_tanggal_kejadian: parse_tanggal_laka,
                            laka_lantas_keterangan: $("#txt_bpjs_laka_keterangan").val(),
                            laka_lantas_suplesi: $("input[type=\"radio\"][name=\"txt_bpjs_laka_suplesi\"]:checked").val(),
                            laka_lantas_suplesi_nomor: $("#txt_bpjs_laka_suplesi_nomor").val(),
                            laka_lantas_suplesi_provinsi: $("#txt_bpjs_laka_suplesi_provinsi").val(),
                            laka_lantas_suplesi_kabupaten: $("#txt_bpjs_laka_suplesi_kabupaten").val(),
                            laka_lantas_suplesi_kecamatan: $("#txt_bpjs_laka_suplesi_kecamatan").val(),

                            skdp: $("#txt_bpjs_skdp").val(),
                            dpjp: $("#txt_bpjs_dpjp").val(),
                            dpjp_nama: $("#txt_bpjs_dpjp option:selected").text(),
                            telepon: $("#txt_bpjs_telepon").val()
                        };
                    }

                    $.ajax({
                        async: false,
                        url:__HOSTAPI__ + "/BPJS",
                        type: "POST",
                        data: dataSetSEP,
                        beforeSend: function(request) {
                            request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                        },
                        success: function(response){
                            SEPList.ajax.reload();
                            if(parseInt(response.response_package.bpjs.content.metaData.code) === 200) {
                                Swal.fire(
                                    "Edit SEP Berhasil!",
                                    "SEP telah diedit",
                                    "success"
                                ).then((result) => {
                                    $("#modal-sep").modal("hide");
                                });
                            } else {
                                Swal.fire(
                                    "Gagal buat SEP",
                                    response.response_package.bpjs.content.metaData.message,
                                    "warning"
                                ).then((result) => {
                                    console.log(response);
                                });
                            }
                        },
                        error: function(response) {
                            console.log(response);
                        }
                    });
                } else if (result.isDenied) {
                }
            });
        });

        $("body").on("click", ".btnHapusSEP", function () {
            var id = $(this).attr("id").split("_");
            id = id[id.length - 1];

            Swal.fire({
                title: "Hapus SEP?",
                showDenyButton: true,
                confirmButtonText: "Ya. Hapus",
                denyButtonText: "Tidak",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        async: false,
                        url: __HOSTAPI__ + "/BPJS/SEP/" + id,
                        beforeSend: function (request) {
                            request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                        },
                        type: "DELETE",
                        success: function (response) {
                            if(parseInt(response.response_package.bpjs.content.metaData.code) === 200) {
                                Swal.fire(
                                    'BPJS',
                                    'SEP Berhasil dihapus',
                                    'success'
                                ).then((result) => {
                                    SEPList.ajax.reload();
                                });
                            } else {
                                Swal.fire(
                                    'BPJS',
                                    response.response_package.bpjs.content.metaData.message,
                                    'error'
                                ).then((result) => {
                                    SEPList.ajax.reload();
                                });
                            }
                        },
                        error: function (response) {
                            console.clear();
                            console.log(response);
                        }
                    });
                }
            });
        });

        $("#claim_kelas_rawat").select2({
            dropdownParent: $("#modal-sep-claim")
        });

        $("#claim_dpjp").select2({
            dropdownParent: $("#modal-sep-claim")
        });

        $("#claim_rencana_tl").select2({
            dropdownParent: $("#modal-sep-claim")
        });

        loadSpesialistik("#claim_spesialistik", {
            kode: "",
            nama: ""
        }, {
            kode: "",
            nama: ""
        });

        loadKelasRawat("#claim_kelas_rawat");

        $("#claim_tanggal_masuk").datepicker({
            dateFormat: 'DD, dd MM yy',
            autoclose: true
        }).datepicker("setDate", new Date());

        $("#claim_tanggal_kontrol").datepicker({
            dateFormat: 'DD, dd MM yy',
            autoclose: true
        }).datepicker("setDate", new Date());

        $("#claim_tanggal_keluar").datepicker({
            dateFormat: 'DD, dd MM yy',
            autoclose: true
        }).datepicker("setDate", new Date());

        $("#claim_jaminan").select2();

        $("#claim_poli").select2({
            minimumInputLength: 2,
            "language": {
                "noResults": function() {
                    return "Poli tidak ditemukan";
                }
            },
            dropdownParent: $("#modal-sep-claim"),
            ajax: {
                dataType: "json",
                headers: {
                    "Authorization" : "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>,
                    "Content-Type" : "application/json",
                },
                url:__HOSTAPI__ + "/BPJS/get_poli",
                type: "GET",
                data: function (term) {
                    return {
                        search:term.term
                    };
                },
                cache: true,
                processResults: function (response) {
                    var data = response.response_package.content.response.poli;
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.nama,
                                id: item.kode
                            }
                        })
                    };
                }
            }
        }).addClass("form-control").on("select2:select", function(e) {
            //
        });

        $("#claim_poli_kontrol").select2({
            minimumInputLength: 2,
            "language": {
                "noResults": function(){
                    return "Poli tidak ditemukan";
                }
            },
            dropdownParent: $("#modal-sep-claim"),
            ajax: {
                dataType: "json",
                headers:{
                    "Authorization" : "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>,
                    "Content-Type" : "application/json",
                },
                url:__HOSTAPI__ + "/BPJS/get_poli",
                type: "GET",
                data: function (term) {
                    return {
                        search:term.term
                    };
                },
                cache: true,
                processResults: function (response) {
                    var data = response.response_package.content.response.poli;
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.nama,
                                id: item.kode
                            }
                        })
                    };
                }
            }
        }).addClass("form-control").on("select2:select", function(e) {
            //
        });

        $("#claim_dirujuk_ke_jenis").select2();

        $("#claim_dirujuk_ke").select2({
            minimumInputLength: 1,
            "language": {
                "noResults": function(){
                    return "SEP tidak ditemukan";
                }
            },
            dropdownParent: $("#modal-sep-claim"),
            ajax: {
                dataType: "json",
                headers:{
                    "Authorization" : "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>,
                    "Content-Type" : "application/json",
                },
                url:__HOSTAPI__ + "/BPJS/get_faskes_select2",
                type: "GET",
                data: function (term) {
                    return {
                        jenis:$("#claim_dirujuk_ke_jenis").val(),
                        search:term.term
                    };
                },
                cache: true,
                processResults: function (response) {
                    var data = response.response_package.content;
                    if(data.metaData.message === "Sukses") {
                        return {
                            results: $.map(data.response.faskes, function (item) {
                                return {
                                    text: item.kode + " - " + item.nama,
                                    id: item.kode
                                }
                            })
                        };
                    } else {
                        /*Swal.fire(
                            "Faskes tidak ditemukan",
                            data.metaData.message,
                            "warning"
                        ).then((result) => {
                            //
                        });*/
                    }
                }
            }
        }).addClass("form-control").on("select2:select", function(e) {
            //
        });










        $("#claim_ruang_rawat").select2({
            minimumInputLength: 2,
            "language": {
                "noResults": function() {
                    return "Ruang Rawat tidak ditemukan";
                }
            },
            dropdownParent: $("#modal-sep-claim"),
            ajax: {
                dataType: "json",
                headers: {
                    "Authorization" : "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>,
                    "Content-Type" : "application/json",
                },
                url:__HOSTAPI__ + "/BPJS/get_ruang_rawat",
                type: "GET",
                data: function (term) {
                    return {
                        search:term.term
                    };
                },
                cache: true,
                processResults: function (response) {
                    if(parseInt(response.response_package.content.metaData.code) === 200) {
                        var data = response.response_package.content.response.list;
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.nama,
                                    id: item.kode
                                }
                            })
                        };
                    } else {
                        return [];
                    }
                }
            }
        }).addClass("form-control").on("select2:select", function(e) {
            //
        });




        $("#claim_kondisi_pulang").select2({
            minimumInputLength: 2,
            "language": {
                "noResults": function() {
                    return "Kondisi Pulang tidak ditemukan";
                }
            },
            dropdownParent: $("#modal-sep-claim"),
            ajax: {
                dataType: "json",
                headers: {
                    "Authorization" : "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>,
                    "Content-Type" : "application/json",
                },
                url:__HOSTAPI__ + "/BPJS/get_referensi_cara_keluar",
                type: "GET",
                data: function (term) {
                    return {
                        search:term.term
                    };
                },
                cache: true,
                processResults: function (response) {
                    console.log(response);
                    var data = response.response_package.data;
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.nama,
                                id: item.kode
                            }
                        })
                    };
                }
            }
        }).addClass("form-control").on("select2:select", function(e) {
            //
        });




        $("#claim_spesialistik").select2({
            minimumInputLength: 2,
            "language": {
                "noResults": function() {
                    return "Faskes tidak ditemukan";
                }
            },
            dropdownParent: $("#modal-sep-claim"),
            ajax: {
                dataType: "json",
                headers: {
                    "Authorization" : "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>,
                    "Content-Type" : "application/json",
                },
                url:__HOSTAPI__ + "/BPJS/get_spesialistik",
                type: "GET",
                data: function (term) {
                    return {
                        search:term.term
                    };
                },
                cache: true,
                processResults: function (response) {
                    var data = response.response_package.content.response.list;
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.nama,
                                id: item.kode
                            }
                        })
                    };
                }
            }
        }).addClass("form-control").on("select2:select", function(e) {
            //
        });

        /*$("#claim_cara_keluar").select2({
            minimumInputLength: 2,
            "language": {
                "noResults": function() {
                    return "Cara keluar tidak ditemukan";
                }
            },
            dropdownParent: $("#modal-sep-claim"),
            ajax: {
                dataType: "json",
                headers: {
                    "Authorization" : "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>,
                    "Content-Type" : "application/json",
                },
                url:__HOSTAPI__ + "/BPJS/get_cara_keluar_select2",
                type: "GET",
                data: function (term) {
                    return {
                        search:term.term
                    };
                },
                cache: true,
                processResults: function (response) {
                    var data = response.response_package.content.response.list;
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.nama,
                                id: item.kode
                            }
                        })
                    };
                }
            }
        }).addClass("form-control").on("select2:select", function(e) {
            //
        });*/

        loadCaraKeluar("#claim_cara_keluar");
        $("#claim_cara_keluar").select2();

        loadKondisiPulang("#claim_kondisi_pulang");
        $("#claim_kondisi_pulang").select2();

        $("#btnClaimSEP").click(function() {
            var tglMasuk = $("#claim_tanggal_masuk").datepicker("getDate");
            var parse_tglMasuk =  tglMasuk.getFullYear() + "-" + str_pad(2, tglMasuk.getMonth()+1) + "-" + str_pad(2, tglMasuk.getDate());
            var tglKeluar = $("#claim_tanggal_keluar").datepicker("getDate");
            var parse_tglKeluar =  tglKeluar.getFullYear() + "-" + str_pad(2, tglKeluar.getMonth()+1) + "-" + str_pad(2, tglKeluar.getDate());
            var jaminan = $("#claim_jaminan").val();
            var poli = $("#claim_poli").val();
            var perawatan_ruang_rawat = ($("#claim_ruang_rawat").val() === null || $("#claim_ruang_rawat").val() === undefined) ? 1 : $("#claim_ruang_rawat").val();
            var perawatan_kelas_rawat = $("#claim_kelas_rawat").val();
            var perawatan_spesialistik = $("#claim_spesialistik").val();
            var perawatan_cara_keluar = $("#claim_cara_keluar").val();
            var perawatan_kondisi_pulang = $("#claim_kondisi_pulang").val();
            var diagnosa_kode = [];
            var procedure = [];
            var rencana_tl_tindak_lanjut = $("#claim_rencana_tl").val();
            var rencana_tl_dirujuk_ke = $("#claim_dirujuk_ke").val();
            var rencana_tl_kontrol_kembali_tanggal = $("#claim_tanggal_kontrol").datepicker("getDate");
            var parse_rencana_tl_kontrol_kembali_tanggal =  rencana_tl_kontrol_kembali_tanggal.getFullYear() + "-" + str_pad(2, rencana_tl_kontrol_kembali_tanggal.getMonth()+1) + "-" + str_pad(2, rencana_tl_kontrol_kembali_tanggal.getDate());
            var rencana_tl_kontrol_kembali_poli = $("#claim_poli_kontrol").val();
            var dpjp = $("#claim_dpjp").val();

            $("#claim_diagnosa tbody tr").each(function (e) {
                var kode = $(this).find("td:eq(1) select").val();
                var level = $(this).find("td:eq(2) select").val();

                if(kode !== undefined && kode !== null) {
                    diagnosa_kode.push({
                        kode: kode,
                        level: level
                    });
                }
            });

            $("#claim_procedure tbody tr").each(function (e) {
                var proc = $(this).find("td:eq(1) select").val();

                if(proc !== undefined && proc !== null) {
                    procedure.push({
                        kode: proc
                    });
                }
            });

            alert(dpjp);

            if(diagnosa_kode.length > 0 && procedure.length > 0) {
                Swal.fire({
                    title: "Proses Claim Baru ?",
                    showDenyButton: true,
                    confirmButtonText: "Ya",
                    denyButtonText: "Belum",
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            async: false,
                            url:__HOSTAPI__ + "/BPJS",
                            type: "POST",
                            data: {
                                request: "tambah_claim",
                                sep: selectedSEP,
                                tgl_masuk: parse_tglMasuk,
                                tgl_keluar: parse_tglKeluar,
                                jaminan: jaminan,
                                poli: poli,
                                perawatan_ruang_rawat: perawatan_ruang_rawat,
                                perawatan_kelas_rawat: perawatan_kelas_rawat,
                                perawatan_spesialistik: perawatan_spesialistik,
                                perawatan_cara_keluar: perawatan_cara_keluar,
                                perawatan_kondisi_pulang: perawatan_kondisi_pulang,
                                diagnosa_kode: diagnosa_kode,
                                procedure: procedure,
                                rencana_tl_tindak_lanjut: rencana_tl_tindak_lanjut,
                                rencana_tl_dirujuk_ke: rencana_tl_dirujuk_ke,
                                rencana_tl_kontrol_kembali_tanggal: parse_rencana_tl_kontrol_kembali_tanggal,
                                rencana_tl_kontrol_kembali_poli: rencana_tl_kontrol_kembali_poli,
                                dpjp: dpjp
                            },
                            beforeSend: function(request) {
                                request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                            },
                            success: function(response) {
                                if(parseInt(response.response_package.bpjs.content.metaData.code) === 200) {
                                    Swal.fire(
                                        "BPJS Claim",
                                        "Claim berhasil dibuat",
                                        "success"
                                    ).then((result) => {
                                        //
                                    });
                                } else {
                                    Swal.fire(
                                        "BPJS Claim",
                                        response.response_package.bpjs.content.metaData.message,
                                        "error"
                                    ).then((result) => {
                                        //
                                    });
                                }
                            },
                            error: function(response) {
                                console.log(response);
                            }
                        });
                    }
                });
            } else {
                var error_claim = [];
                var error_message = "<ol>";
                if(diagnosa_kode.length === 0) {
                    error_claim.push("Isi data icd10");
                }

                if(procedure.length === 0) {
                    error_claim.push("Isi data icd9");
                }

                for(var errKey in error_claim) {
                    error_message += "<li>" + error_claim[errKey] + "</li>";
                }

                error_message += "</ol>";

                Swal.fire(
                    "BPJS Claim",
                    error_message,
                    "error"
                ).then((result) => {
                    //
                });
            }

        });


        var dataKondisiPulang = load_bpjs("get_spesialistik");


        function load_bpjs(targetURL) {
            var bpjsData;
            $.ajax({
                url:__HOSTAPI__ + "/BPJS/" + targetURL,
                async:false,
                beforeSend: function(request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                type:"GET",
                success:function(response) {
                    bpjsData = response.response_package;
                },
                error: function(response) {
                    console.log(response);
                }
            });
            return bpjsData;
        }

        function autoICD(targetTable) {
            var newRow = document.createElement("TR");
            var newID = document.createElement("TD");
            var newDiagnosa = document.createElement("TD");
            var newAksi = document.createElement("TD");


            $(newRow).append(newID);
            $(newRow).append(newDiagnosa);
            $(newRow).append(newAksi);

            $(targetTable).append(newRow);
        }

        function loadKelasRawat(target, selected = ""){
            $.ajax({
                async: false,
                url:__HOSTAPI__ + "/BPJS/get_kelas_rawat_select2",
                type: "GET",
                beforeSend: function(request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                success: function(response){
                    var data = response.response_package.content.response.list;

                    $(target + " option").remove();
                    var targetParse = ["0", "I", "II", "III"];
                    var targetParse2 = ["0", "1", "2", "3"];
                    for(var a = 0; a < data.length; a++) {

                        var selection = document.createElement("OPTION");
                        var checkKelasNama = data[a].nama.toUpperCase().split("KELAS");
                        var checkSelectedKelas = selected.toUpperCase().split("KELAS");

                        if(checkKelasNama.length > 1) {

                            if(
                                targetParse.indexOf(checkKelasNama[1].trim()) > -1 ||
                                targetParse2.indexOf(checkKelasNama[1].trim()) > -1
                            ) {
                                if(targetParse.indexOf(checkKelasNama[1].trim()) > -1) {
                                    $(selection).attr("value", targetParse.indexOf(checkKelasNama[1].trim())).html(data[a].nama);
                                } else {
                                    $(selection).attr("value", targetParse2.indexOf(checkKelasNama[1].trim())).html(data[a].nama);
                                }

                            }

                            if(selected !== "") {
                                if(
                                    data[a].nama.toUpperCase() === "KELAS " + targetParse.indexOf(checkSelectedKelas[1].trim()) ||
                                    data[a].nama.toUpperCase() === "KELAS " + targetParse2.indexOf(checkSelectedKelas[1].trim())
                                ) {
                                    if(data[a].nama.toUpperCase() === "KELAS " + targetParse.indexOf(checkSelectedKelas[1].trim())) {
                                        $(selection).attr("value", targetParse.indexOf(checkSelectedKelas[1].trim())).html(data[a].nama);
                                    } else {
                                        $(selection).attr("value", targetParse2.indexOf(checkSelectedKelas[1].trim())).html(data[a].nama);
                                    }
                                } else {
                                    //console.log(data[a].nama.toUpperCase() + " >>> " + selected.toUpperCase());
                                }
                                $(selection).attr("selected", "selected");
                            }
                            $(target).append(selection);
                        } else {
                            if(data[a].nama.toUpperCase() === selected.toUpperCase()) {
                                $(selection).attr("selected", "selected");
                            }
                        }


                    }
                },
                error: function(response) {
                    console.log(response);
                }
            });
        }

        function loadCaraKeluar(target, selected = ""){
            $.ajax({
                async: false,
                url:__HOSTAPI__ + "/BPJS",
                type: "POST",
                data: {
                    request: "get_referensi_cara_keluar",
                    search: {
                        value: ""
                    },
                    start: 0,
                    length: 100,
                    draw: 1
                },
                beforeSend: function(request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                success: function(response){
                    var data = response.response_package.data;

                    $(target + " option").remove();
                    for(var a = 0; a < data.length; a++) {

                        var selection = document.createElement("OPTION");
                        $(selection).attr("value", data[a].kode).html(data[a].kode + " - " + data[a].nama);
                        if(data[a].nama.toUpperCase() === selected.toUpperCase()) {
                            $(selection).attr("selected", "selected");
                        }
                        $(target).append(selection);

                    }
                },
                error: function(response) {
                    console.log(response);
                }
            });
        }






        function loadKondisiPulang(target, selected = ""){
            $.ajax({
                async: false,
                url:__HOSTAPI__ + "/BPJS",
                type: "POST",
                data: {
                    request: "get_referensi_pasca_pulang",
                    search: {
                        value: ""
                    },
                    start: 0,
                    length: 100,
                    draw: 1
                },
                beforeSend: function(request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                success: function(response){
                    var data = response.response_package.data;

                    $(target + " option").remove();
                    for(var a = 0; a < data.length; a++) {

                        var selection = document.createElement("OPTION");
                        $(selection).attr("value", data[a].kode).html(data[a].kode + " - " + data[a].nama);
                        if(data[a].nama.toUpperCase() === selected.toUpperCase()) {
                            $(selection).attr("selected", "selected");
                        }
                        $(target).append(selection);

                    }
                },
                error: function(response) {
                    console.log(response);
                }
            });
        }


        function loadSpesialistik(target, selected = {
            kode: "",
            nama: ""
        }, dpjp = {
            kode: "",
            nama: ""
        }) {
            $.ajax({
                async: false,
                url:__HOSTAPI__ + "/BPJS/get_spesialistik",
                type: "GET",
                beforeSend: function(request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                success: function(response){
                    if(response.response_package.content === null) {
                        loadSpesialistik(target);
                    } else {
                        var data = response.response_package.content.response.list;

                        $(target + " option").remove();
                        for(var a = 0; a < data.length; a++) {
                            var selection = document.createElement("OPTION");
                            if(data[a].kode === selected.kode) {
                                $(selection).attr({
                                    "selected": "selected"
                                });
                            }
                            $(selection).attr("value", data[a].kode).html(data[a].nama);
                            $(target).append(selection);
                        }
                        loadDPJP((target === "txt_bpjs_dpjp_spesialistik") ? "#txt_bpjs_dpjp" : "#claim_dpjpz", (target === "txt_bpjs_dpjp_spesialistik") ? $("#txt_bpjs_jenis_asal_rujukan").val() : $("#claim_dirujuk_ke_jenis").val(), $(target).val(), dpjp);
                        $("#claim_dpjp").select2({
                            minimumInputLength: 1,
                            "language": {
                                "noResults": function(){
                                    return "Dokter tidak ditemukan";
                                }
                            },
                            dropdownParent: $("#modal-sep-claim"),
                            ajax: {
                                dataType: "json",
                                headers:{
                                    "Authorization" : "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>,
                                    "Content-Type" : "application/json",
                                },
                                url:__HOSTAPI__ + "/BPJS/get_dpjp_claim",
                                type: "GET",
                                data: function (term) {
                                    return {
                                        search:term.term
                                    };
                                },
                                cache: true,
                                processResults: function (response) {
                                    if(parseInt(response.response_package.content.metaData.code) === 200) {
                                        var data = response.response_package.content.response.list;
                                        return {
                                            results: $.map(data, function (item) {
                                                return {
                                                    text: item.nama,
                                                    id: item.kode
                                                }
                                            })
                                        };
                                    } else {
                                        return [];
                                    }
                                }
                            }
                        }).addClass("form-control").on("select2:select", function(e) {
                            var id = $(this).attr("id").split("_");
                            id = id[id.length - 1];

                            autoProcedure();
                        });
                    }
                },
                error: function(response) {
                    console.log(response);
                }
            });
        }

        function loadDPJP(target, jenis, spesialistik, selected = {
            kode: "",
            nama: ""
        }) {
            $.ajax({
                async: false,
                url:__HOSTAPI__ + "/BPJS/get_dpjp/" + jenis + "/" + spesialistik,
                type: "GET",
                beforeSend: function(request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                success: function(response){
                    if(response.response_package.content === null) {
                        loadDPJP(target, jenis, spesialistik);
                    } else {
                        var data = response.response_package.content.response.list;

                        $(target + " option").remove();
                        //$(target).select2('data', null);
                        for(var a = 0; a < data.length; a++) {
                            var selection = document.createElement("OPTION");

                            if(data[a].kode === selected.kode) {
                                $(selection).attr({
                                    "selected": "selected"
                                });
                            }

                            $(selection).attr("value", data[a].kode).html(data[a].kode + " - " + data[a].nama);
                            $(target).append(selection);
                        }

                    }
                },
                error: function(response) {
                    console.log(response);
                }
            });
        }

        function loadProvinsi(target, selected = "") {
            var selectedNama = "";
            $.ajax({
                async: false,
                url:__HOSTAPI__ + "/BPJS/get_provinsi",
                type: "GET",
                beforeSend: function(request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                success: function(response){
                    var data = response.response_package.content.response.list;

                    $(target + " option").remove();

                    for(var a = 0; a < data.length; a++) {
                        var selection = document.createElement("OPTION");

                        if(parseInt(data[a].kode) === parseInt(selected)) {
                            selectedNama = data[a].nama;
                            $(selection).attr({
                                "selected": "selected"
                            });
                        }
                        $(selection).attr("value", data[a].kode).html(data[a].nama);
                        $(target).append(selection);
                    }
                },
                error: function(response) {
                    console.log(response);
                }
            });
            return selectedNama;
        }

        function loadKabupaten(target, provinsi, selected = "") {
            var selectedNama = "";
            $.ajax({
                async: false,
                url:__HOSTAPI__ + "/BPJS/get_kabupaten/" + provinsi,
                type: "GET",
                beforeSend: function(request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                success: function(response){
                    var data = response.response_package.content.response.list;

                    $(target + " option").remove();
                    for(var a = 0; a < data.length; a++) {
                        var selection = document.createElement("OPTION");
                        if(parseInt(data[a].kode) === parseInt(selected)) {
                            selectedNama = data[a].nama;
                            $(selection).attr({
                                "selected": "selected"
                            });
                        }
                        $(selection).attr("value", data[a].kode).html(data[a].nama);
                        $(target).append(selection);
                    }
                },
                error: function(response) {
                    console.log(response);
                }
            });
            return selectedNama;
        }

        function loadKecamatan(target, kabupaten, selected = "") {
            var selectedNama = "";
            $.ajax({
                async: false,
                url:__HOSTAPI__ + "/BPJS/get_kecamatan/" + kabupaten,
                type: "GET",
                beforeSend: function(request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                success: function(response){
                    var data = response.response_package.content.response.list;

                    $(target + " option").remove();
                    for(var a = 0; a < data.length; a++) {
                        var selection = document.createElement("OPTION");
                        if(parseInt(data[a].kode) === parseInt(selected)) {
                            selectedNama = data[a].nama;
                            $(selection).attr({
                                "selected": "selected"
                            });
                        }
                        $(selection).attr("value", data[a].kode).html(data[a].nama);
                        $(target).append(selection);
                    }
                },
                error: function(response) {
                    console.log(response);
                }
            });
            return selectedNama;
        }

        autoDiagnosa();
        autoProcedure();

        function autoDiagnosa() {
            $("#claim_diagnosa tbody tr").removeClass("last-diagnosa");
            var newRow = document.createElement("TR");
            var newCellID = document.createElement("TD");
            var newCellDiagnosa = document.createElement("TD");
            var newCellLevel = document.createElement("TD");
            var newCellAksi = document.createElement("TD");

            var newDiagnosa = document.createElement("SELECT");
            var newLevel = document.createElement("SELECT");
            var newAksi = document.createElement("BUTTON");

            $(newCellDiagnosa).append(newDiagnosa);
            $(newCellLevel).append(newLevel);
            $(newCellAksi).append(newAksi);


            var level = [
                {
                    value: 1,
                    caption: "Primer"
                },
                {
                    value: 2,
                    caption: "Sekunder"
                }
            ];

            for(var key in level) {
                $(newLevel).append("<option value=\"" + level[key].value + "\">" + level[key].caption + "</option>");
            }



            $(newDiagnosa).addClass("form-control diagnosa_claim").select2({
                minimumInputLength: 2,
                "language": {
                    "noResults": function(){
                        return "Diagnosa tidak ditemukan";
                    }
                },
                dropdownParent: $("#modal-sep-claim"),
                ajax: {
                    dataType: "json",
                    headers:{
                        "Authorization" : "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>,
                        "Content-Type" : "application/json",
                    },
                    url:__HOSTAPI__ + "/BPJS/get_diagnosa",
                    type: "GET",
                    data: function (term) {
                        return {
                            search:term.term
                        };
                    },
                    cache: true,
                    processResults: function (response) {
                        var data = response.response_package.content.response.diagnosa;
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.nama,
                                    id: item.kode
                                }
                            })
                        };
                    }
                }
            }).addClass("form-control").on("select2:select", function(e) {
                var id = $(this).attr("id").split("_");
                id = id[id.length - 1];

                autoDiagnosa();
            });

            $(newLevel).select2();
            $(newAksi).addClass("btn btn-sm btn-danger deleteDiagnosa").html("<i class=\"fa fa-trash-alt\"></i>");

            $(newRow).append(newCellID);
            $(newRow).append(newCellDiagnosa);
            $(newRow).append(newCellLevel);
            $(newRow).append(newCellAksi);

            $("#claim_diagnosa tbody").append(newRow);

            $(newRow).addClass("last-diagnosa");
            rebaseDiagnosa();
        }

        $("body").on("click", ".deleteDiagnosa", function () {
            var id = $(this).attr("id").split("_");
            id = id[id.length - 1];

            if(!$("#row_diagnosa_" + id).hasClass("last-diagnosa")) {
                $("#row_diagnosa_" + id).remove();
                rebaseDiagnosa();
            }
        });

        function rebaseDiagnosa() {
            $("#claim_diagnosa tbody tr").each(function(e) {
                var id = (e + 1);

                $(this).attr({
                    "id": "row_diagnosa_" + id
                });

                $(this).find("td:eq(0)").html(id);
                $(this).find("td:eq(1) select").attr({
                    "id": "diagnosa_" + id
                });

                $(this).find("td:eq(2) select").attr({
                    "id": "level_diagnosa_" + id
                });

                $(this).find("td:eq(3) button").attr({
                    "id": "delete_diagnosa_" + id
                });
            });
        }

        function autoProcedure() {
            $("#claim_procedure tbody tr").removeClass("last-procedure");
            var newRow = document.createElement("TR");
            var newCellID = document.createElement("TD");
            var newCellProcedure = document.createElement("TD");
            var newCellAksi = document.createElement("TD");

            var newProcedure = document.createElement("SELECT");
            var newAksi = document.createElement("BUTTON");

            $(newCellProcedure).append(newProcedure);
            $(newCellAksi).append(newAksi);

            $(newProcedure).addClass("form-control diagnosa_claim").select2({
                minimumInputLength: 2,
                "language": {
                    "noResults": function(){
                        return "Procedure tidak ditemukan";
                    }
                },
                dropdownParent: $("#modal-sep-claim"),
                ajax: {
                    dataType: "json",
                    headers:{
                        "Authorization" : "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>,
                        "Content-Type" : "application/json",
                    },
                    url:__HOSTAPI__ + "/BPJS/get_procedure",
                    type: "GET",
                    data: function (term) {
                        return {
                            search:term.term
                        };
                    },
                    cache: true,
                    processResults: function (response) {
                        var data = response.response_package.content.response.procedure;
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.nama,
                                    id: item.kode
                                }
                            })
                        };
                    }
                }
            }).addClass("form-control").on("select2:select", function(e) {
                var id = $(this).attr("id").split("_");
                id = id[id.length - 1];

                autoProcedure();
            });

            $(newAksi).addClass("btn btn-sm btn-danger deleteProcedure").html("<i class=\"fa fa-trash-alt\"></i>");

            $(newRow).append(newCellID);
            $(newRow).append(newCellProcedure);
            $(newRow).append(newCellAksi);

            $("#claim_procedure tbody").append(newRow);

            $(newRow).addClass("last-procedure");
            rebaseProcedure();
        }

        $("body").on("click", ".deleteProcedure", function () {
            var id = $(this).attr("id").split("_");
            id = id[id.length - 1];

            if(!$("#row_procedure_" + id).hasClass("last-procedure")) {
                $("#row_procedure_" + id).remove();
                rebaseProcedure();
            }
        });

        function rebaseProcedure() {
            $("#claim_procedure tbody tr").each(function(e) {
                var id = (e + 1);

                $(this).attr({
                    "id": "row_procedure_" + id
                });

                $(this).find("td:eq(0)").html(id);
                $(this).find("td:eq(1) select").attr({
                    "id": "procedure_" + id
                });

                $(this).find("td:eq(2) button").attr({
                    "id": "delete_procedure_" + id
                });
            });
        }
    });
</script>

<div id="modal-sep" class="modal fade" role="dialog" aria-labelledby="modal-large-title" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-large-title">
                    <img src="<?php echo __HOSTNAME__;  ?>/template/assets/images/bpjs.png" class="img-responsive" width="275" height="45" style="margin-right: 50px" /> Surat Eligibilitas Peserta
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-lg-12">
                    <div class="form-row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header card-header-large bg-white d-flex align-items-center">
                                    <h5 class="card-header__title flex m-0 text-info"><i class="fa fa-hashtag"></i> Informasi Pasien</h5>
                                </div>
                                <div class="card-body row">
                                    <div class="col-12 col-md-2 mb-2 form-group">
                                        <label for="">No Kartu</label>
                                        <input type="text" autocomplete="off" class="form-control uppercase" id="txt_bpjs_nomor" readonly>
                                    </div>
                                    <div class="col-12 col-md-2 mb-2 form-group">
                                        <label for="">NIK Pasien</label>
                                        <input type="text" autocomplete="off" class="form-control uppercase" id="txt_bpjs_nik" readonly>
                                    </div>
                                    <div class="col-12 col-md-5 mb-5 form-group">
                                        <label for="">Nama Pasien</label>
                                        <input type="text" autocomplete="off" class="form-control uppercase" id="txt_bpjs_nama" readonly>
                                    </div>
                                    <div class="col-12 col-md-3 mb-3 form-group">
                                        <label for="">Kontak</label>
                                        <input type="text" autocomplete="off" class="form-control uppercase" id="txt_bpjs_telepon" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card">
                                <div class="card-header card-header-large bg-white d-flex align-items-center">
                                    <h5 class="card-header__title flex m-0 text-info"><i class="fa fa-hashtag"></i> Informasi Rujukan</h5>
                                </div>
                                <div class="card-body row">
                                    <div class="col-6">
                                        <div class="col-12 col-md-8 mb-4 form-group">
                                            <label for="">Nomor Medical Rahecord (MR)</label>
                                            <input type="text" autocomplete="off" class="form-control uppercase" id="txt_bpjs_rm" readonly>
                                        </div>

                                        <div class="col-12 col-md-7 form-group">
                                            <label for="">Tanggal SEP</label>
                                            <input type="text" autocomplete="off" class="form-control uppercase" id="txt_bpjs_tgl_sep" readonly value="<?php echo date('d F Y'); ?>">
                                        </div>
                                        <div class="col-12 col-md-9 form-group">
                                            <label for="">Faskes</label>
                                            <select class="form-control sep" id="txt_bpjs_faskes" disabled>
                                                <option value="<?php echo __KODE_PPK__; ?>">RSUD PETALA BUMI - KOTA PEKAN BARU</option>
                                            </select>
                                        </div>


                                        <div class="col-12 col-md-8 form-group">
                                            <label for="">Jenis Pelayanan</label>
                                            <select class="form-control sep" id="txt_bpjs_jenis_layanan">
                                                <option value="1">Rawat Inap</option>
                                                <option value="2">Rawat Jalan</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-9 mb-9 form-group" id="group_kelas_rawat">
                                            <label for="">Kelas Rawat</label>
                                            <select class="form-control" id="txt_bpjs_kelas_rawat"></select>
                                        </div>
                                    </div>

                                    <div class="col-6" id="panel-rujukan">
                                        <div class="col-12 col-md-6 mb-4 form-group" id="group_nomor_rujukan">
                                            <label for="">Nomor Rujukan</label>
                                            <select data-width="100%" class="form-control uppercase" id="txt_bpjs_nomor_rujukan"></select>
                                            <!--<input type="text" class="form-control uppercase" id="txt_bpjs_nomor_rujukan" />-->
                                        </div>
                                        <div class="col-12 col-md-4 mb-4 form-group">
                                            <label for="">Jenis Asal Rujukan</label>
                                            <select class="form-control uppercase sep" id="txt_bpjs_jenis_asal_rujukan">
                                                <option value="1">Puskesmas</option>
                                                <option value="2">Rumah Sakit</option>
                                            </select>
                                        </div>
                                        <div class="col-12 form-group">
                                            <label for="">Asal Rujukan</label>
                                            <select data-width="100%" class="form-control uppercase sep" id="txt_bpjs_asal_rujukan"></select>
                                        </div>
                                        <div class="col-12 col-md-5 mb-4 form-group">
                                            <label for="">Tanggal Rujukan</label>
                                            <input type="text" autocomplete="off" class="form-control uppercase" id="txt_bpjs_tanggal_rujukan">
                                        </div>

                                        <div class="informasi_rujukan">
                                            <table class="table form-mode">
                                                <tr>
                                                    <td>Perujuk</td>
                                                    <td>:</td>
                                                    <td id="txt_bpjs_rujuk_perujuk"></td>
                                                </tr>
                                                <tr>
                                                    <td>Tanggal Kunjungan</td>
                                                    <td>:</td>
                                                    <td id="txt_bpjs_rujuk_tanggal"></td>
                                                </tr>
                                                <tr>
                                                    <td>Poli</td>
                                                    <td>:</td>
                                                    <td id="txt_bpjs_rujuk_poli"></td>
                                                </tr>
                                                <tr>
                                                    <td>Diagnosa</td>
                                                    <td>:</td>
                                                    <td id="txt_bpjs_rujuk_diagnosa"></td>
                                                </tr>
                                                <tr>
                                                    <td>Keluhan</td>
                                                    <td>:</td>
                                                    <td id="txt_bpjs_rujuk_keluhan"></td>
                                                </tr>
                                                <tr>
                                                    <td>Hak Kelas</td>
                                                    <td>:</td>
                                                    <td id="txt_bpjs_rujuk_hak_kelas"></td>
                                                </tr>
                                                <tr>
                                                    <td>Jenis Peserta</td>
                                                    <td>:</td>
                                                    <td id="txt_bpjs_rujuk_jenis_peserta"></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <div class="col-12">
                            <div class="card">
                                <div class="card-header card-header-large bg-white d-flex align-items-center">
                                    <h5 class="card-header__title flex m-0 text-info"><i class="fa fa-hashtag"></i> Perobatan</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-6 mb-6">
                                            <div class="col-12 col-md-8 mb-4 form-group" id="group_poli">
                                                <label for="">Poli Tujuan</label>
                                                <select class="form-control" id="txt_bpjs_poli_tujuan"></select>
                                            </div>
                                            <div class="col-12 col-md-8 mb-4 form-group" id="group_poli">
                                                <label for="">Poli Eksekutif</label>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="txt_bpjs_poli_eksekutif" value="0" checked/>
                                                            <label class="form-check-label">
                                                                Tidak
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="txt_bpjs_poli_eksekutif" value="1" />
                                                            <label class="form-check-label">
                                                                Ya
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-12 form-group" id="group_diagnosa">
                                                <label for="">Diagnosa Awal</label>
                                                <select class="form-control sep" id="txt_bpjs_diagnosa_awal"></select>
                                            </div>
                                            <div class="col-12 col-md-12 form-group">
                                                <label for="">Catatan</label>
                                                <textarea class="form-control" placeholder="Catatan Peserta" id="txt_bpjs_catatan" style="min-height: 200px"></textarea>
                                            </div>
                                            <div class="col-12 col-md-6 mb-4 form-group">
                                                <label for="">Nomor SKDP</label>
                                                <input type="text" autocomplete="off" class="form-control uppercase" id="txt_bpjs_skdp" />
                                            </div>
                                            <div class="col-12 col-md-8 mb-8 form-group" id="group_spesialistik">
                                                <label for="">Spesialistik DPJP</label>
                                                <select class="form-control" id="txt_bpjs_dpjp_spesialistik"></select>
                                            </div>
                                            <div class="col-12 col-md-9 mb-9 form-group" id="group_dpjp">
                                                <label for="">Kode DPJP</label>
                                                <select class="form-control sep" id="txt_bpjs_dpjp"></select>
                                            </div>
                                            <div class="col-12 col-md-12 form-group">
                                                <label for="">COB</label>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="txt_bpjs_cob" value="0" checked/>
                                                            <label class="form-check-label">
                                                                Tidak
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="txt_bpjs_cob" value="1" />
                                                            <label class="form-check-label">
                                                                Ya
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-12 form-group">
                                                <label for="">Katarak</label>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="txt_bpjs_katarak" value="0" checked/>
                                                            <label class="form-check-label">
                                                                Tidak
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="txt_bpjs_katarak" value="1" />
                                                            <label class="form-check-label">
                                                                Ya
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 mb-6">
                                            <!--div class="alert alert-info">
                                                <div class="col-12 col-md-8 mb-4 form-group">
                                                    <b for="">Poli Tujuan</b>
                                                    <blockquote style="padding-left: 25px;">
                                                        <h6 id="txt_bpjs_internal_poli"></h6>
                                                    </blockquote>
                                                </div>
                                                <div class="col-12 col-md-12 form-group">
                                                    <h6 for="">Diagnosa Kerja</h6>
                                                    <ol type="1" id="txt_bpjs_internal_icdk"></ol>
                                                    <blockquote style="padding-left: 25px;">
                                                        <p id="txt_bpjs_internal_dk"></p>
                                                    </blockquote>
                                                </div>
                                                <div class="col-12 col-md-12 form-group">
                                                    <h6 for="">Diagnosa Banding</h6>
                                                    <ol type="1" id="txt_bpjs_internal_icdb"></ol>
                                                    <blockquote style="padding-left: 25px;">
                                                        <p id="txt_bpjs_internal_db"></p>
                                                    </blockquote>
                                                </div>
                                            </div-->
                                            <div class="col-12 col-md-12 form-group">
                                                <label for="">Jaminan Laka Lantas</label>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="txt_bpjs_laka" value="0" checked/>
                                                            <label class="form-check-label">
                                                                Tidak
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="txt_bpjs_laka" value="1" />
                                                            <label class="form-check-label">
                                                                Ya
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="laka_lantas_container">
                                                <div class="col-12 col-md-12 form-group" id="group_diagnosa">
                                                    <label for="">Penjamin Laka Lantas</label>
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="txt_bpjs_laka_penjamin" value="1" />
                                                                <label class="form-check-label">
                                                                    Jasa Raharja
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="txt_bpjs_laka_penjamin" value="2" />
                                                                <label class="form-check-label">
                                                                    BPJS Ketenagakerjaan
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="txt_bpjs_laka_penjamin" value="3" />
                                                                <label class="form-check-label">
                                                                    TASPEN PT
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="txt_bpjs_laka_penjamin" value="4" />
                                                                <label class="form-check-label">
                                                                    ASABRI PT
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-5 mb-4 form-group">
                                                    <label for="">Tanggal Kejadian</label>
                                                    <input type="text" autocomplete="off" class="form-control uppercase" id="txt_bpjs_laka_tanggal">
                                                </div>
                                                <div class="col-12 col-md-12 form-group">
                                                    <label for="">Keterangan</label>
                                                    <textarea class="form-control" placeholder="Catatan Peserta" id="txt_bpjs_laka_keterangan" style="min-height: 200px"></textarea>
                                                </div>
                                                <div class="col-12 col-md-12 form-group">
                                                    <label for="">Suplesi</label>
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="txt_bpjs_laka_suplesi" value="0" checked/>
                                                                <label class="form-check-label">
                                                                    Tidak
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="txt_bpjs_laka_suplesi" value="1" />
                                                                <label class="form-check-label">
                                                                    Ya
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="laka_lantas_suplesi_container">
                                                    <div class="col-12 col-md-6 mb-4 form-group">
                                                        <label for="">Nomor SEP Suplesi</label>
                                                        <input type="text" autocomplete="off" class="form-control uppercase" id="txt_bpjs_laka_suplesi_nomor" />
                                                    </div>
                                                    <div class="col-12 col-md-8 mb-4 form-group" id="group_provinsi">
                                                        <label for="">Provinsi Kejadian</label>
                                                        <select class="form-control" id="txt_bpjs_laka_suplesi_provinsi"></select>
                                                    </div>
                                                    <div class="col-12 col-md-8 mb-4 form-group" id="group_kabupaten">
                                                        <label for="">Kabupaten Kejadian</label>
                                                        <select class="form-control" id="txt_bpjs_laka_suplesi_kabupaten"></select>
                                                    </div>
                                                    <div class="col-12 col-md-8 mb-4 form-group" id="group_kecamatan">
                                                        <label for="">Kecamatan Kejadian</label>
                                                        <select class="form-control" id="txt_bpjs_laka_suplesi_kecamatan"></select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="btnProsesSEP">
                    <i class="fa fa-check"></i> Proses
                </button>

                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>




<div id="modal-sep-claim" class="modal fade" role="dialog" aria-labelledby="modal-large-title" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-large-title">
                    <img src="<?php echo __HOSTNAME__;  ?>/template/assets/images/bpjs.png" class="img-responsive" width="275" height="45" style="margin-right: 50px" /> <span>Lembar Pengajuan Klaim</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group col-md-12">
                    <div class="row card-group-row">
                        <div class="col-lg-12 col-md-12">
                            <div class="z-0">
                                <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                                    <li class="nav-item">
                                        <a href="#tab-lpk" class="nav-link active" data-toggle="tab" role="tab" aria-selected="true" aria-controls="tab-lpk" >
                                            <span class="nav-link__count">
                                                <i class="fa fa-address-book"></i>
                                            </span>
                                            LPK
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#tab-pemeriksaan" class="nav-link" data-toggle="tab" role="tab" aria-selected="true" aria-controls="tab-pemeriksaan" >
                                            <span class="nav-link__count">
                                                <i class="fa fa-flask"></i>
                                            </span>
                                            Hasil Pemeriksaan
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card card-body tab-content">
                                <div class="tab-pane show fade active" id="tab-lpk">
                                    <div class="row">
                                        <div class="col-6 col-md-6 form-group">
                                            <label for="">Tanggal Masuk</label>
                                            <input type="text" autocomplete="off" class="form-control uppercase" id="claim_tanggal_masuk" />
                                        </div>
                                        <div class="col-6 col-md-6 form-group">
                                            <label for="">Tanggal Keluar</label>
                                            <input type="text" autocomplete="off" class="form-control uppercase" id="claim_tanggal_keluar" />
                                        </div>
                                        <div class="col-4 col-md-6 form-group">
                                            <label for="">Jaminan</label>
                                            <select class="form-control" id="claim_jaminan">
                                                <option value="1">JKN</option>
                                            </select>
                                        </div>
                                        <div class="col-8 col-md-6 form-group">
                                            <label for="">Poli</label>
                                            <select class="form-control" id="claim_poli"></select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-4 col-md-4 form-group">
                                            <label for="">Ruang Rawat</label>
                                            <select class="form-control" id="claim_ruang_rawat"></select>
                                        </div>
                                        <div class="col-4 col-md-4 form-group">
                                            <label for="">Kelas Rawat</label>
                                            <select class="form-control" id="claim_kelas_rawat"></select>
                                        </div>
                                        <div class="col-4 col-md-4 form-group">
                                            <label for="">Spesialistik</label>
                                            <select class="form-control" id="claim_spesialistik"></select>
                                        </div>
                                        <div class="col-6 col-md-4 form-group">
                                            <label for="">Cara Keluar</label>
                                            <select class="form-control" id="claim_cara_keluar"></select>
                                        </div>
                                        <div class="col-6 col-md-4 form-group">
                                            <label for="">Kondisi Pulang</label>
                                            <select class="form-control" id="claim_kondisi_pulang"></select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 col-md-6">
                                            <h5>Diagnosa <b>[ICD10]</b></h5>
                                            <table class="table table-bordered largeDataType" id="claim_diagnosa">
                                                <thead class="thead-dark">
                                                <tr>
                                                    <th class="wrap_content">No</th>
                                                    <th>Diagnosa</th>
                                                    <th style="width: 100px;">Level</th>
                                                    <th class="wrap_content"></th>
                                                </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                        <div class="col-6 col-md-6">
                                            <h5>Prosedur <b>[ICD9]</b></h5>
                                            <table class="table table-bordered largeDataType" id="claim_procedure">
                                                <thead class="thead-dark">
                                                <tr>
                                                    <th class="wrap_content">No</th>
                                                    <th>Procedure</th>
                                                    <th class="wrap_content"></th>
                                                </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-4 col-md-4 form-group">
                                            <label for="">Rencana Tindak Lanjut</label>
                                            <select class="form-control" id="claim_rencana_tl">
                                                <option value="1">Diperbolehkan Pulang</option>
                                                <option value="2">Pemeriksaan Penunjang</option>
                                                <option value="3">Dirujuk Ke</option>
                                                <option value="4">Kontrol Kembali</option>
                                            </select>
                                        </div>

                                        <div class="col-4 col-md-4 form-group">
                                            <label for="">Dirujuk Ke</label>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label for="">Jenis Faskes</label>
                                                    <select class="form-control uppercase" id="claim_dirujuk_ke_jenis">
                                                        <option value="1">Puskesmas</option>
                                                        <option value="2">Rumah Sakit</option>
                                                    </select>
                                                    <hr />
                                                </div>
                                                <div class="col-md-12">
                                                    <label for="">Faskes</label>
                                                    <select class="form-control" id="claim_dirujuk_ke"></select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-4">
                                            <div class="form-group">
                                                <label for="">Kontrol Kembali</label>
                                                <input type="text" id="claim_tanggal_kontrol" class="form-control uppercase" />
                                            </div>
                                            <div class="form-group">
                                                <label for="">Kontrol Poli</label>
                                                <select class="form-control" id="claim_poli_kontrol"></select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-5 form-group">
                                            <label for="">DPJP</label>
                                            <select class="form-control" id="claim_dpjp"></select>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane show fade" id="tab-pemeriksaan">
                                    <div class="form-group col-md-12">
                                        <div class="row card-group-row">
                                            <div class="col-lg-12 col-md-12">
                                                <div class="z-0">
                                                    <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                                                        <li class="nav-item">
                                                            <a href="#tab-poli-1" class="nav-link active" data-toggle="tab" role="tab" aria-selected="true" aria-controls="tab-poli-1" >
                                            <span class="nav-link__count">
                                                <i class="fa fa-address-book"></i>
                                            </span>
                                                                CPPT
                                                            </a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a href="#tab-poli-2" class="nav-link" data-toggle="tab" role="tab" aria-selected="true" aria-controls="tab-poli-1" >
                                            <span class="nav-link__count">
                                                <i class="fa fa-flask"></i>
                                            </span>
                                                                Hasil Laboratorium
                                                            </a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a href="#tab-poli-3" class="nav-link" data-toggle="tab" role="tab" aria-selected="true" aria-controls="tab-poli-1" >
                                            <span class="nav-link__count">
                                                <i class="fa fa-life-ring"></i>
                                            </span>
                                                                Hasil Radiologi
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="card card-body tab-content">
                                                    <div class="tab-pane show fade active" id="tab-poli-1">
                                                        <div class="card-body">
                                                            <p class="text-dark-gray d-flex align-items-center mt-3">
                                                                <i class="material-icons icon-muted mr-2">event</i>
                                                                <strong id="tanggal_periksa"></strong>
                                                            </p>
                                                            <div class="row projects-item mb-1">
                                                                <div class="col-1">
                                                                    <br />
                                                                    <div class="text-dark-gray">Subjective</div>
                                                                </div>
                                                                <div class="col-11">
                                                                    <div class="card">
                                                                        <div class="card-header card-header-large bg-white">
                                                                            <div class="row">
                                                                                <div class="col-6">
                                                                                    <div class="segmen_keluhan_utama">
                                                                                        <div class="d-flex align-items-center">
                                                                                            <a href="#" class="text-body"><strong class="text-15pt mr-2"><i class="fa fa-hashtag"></i> Keluhan Utama</strong></a>
                                                                                        </div>
                                                                                        <div class="card-body">
                                                                                            <p class="txt_keluhan_utama">

                                                                                            </p>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-6">
                                                                                    <div class="segmen_keluhan_tambahan">
                                                                                        <div class="d-flex align-items-center">
                                                                                            <a href="#" class="text-body"><strong class="text-15pt mr-2"><i class="fa fa-hashtag"></i> Keluhan Tambahan</strong></a>
                                                                                        </div>
                                                                                        <div class="card-body">
                                                                                            <p class="txt_keluhan_tambahan">

                                                                                            </p>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row projects-item mb-1">
                                                                <div class="col-1">
                                                                    <br />
                                                                    <div class="text-dark-gray">Objective</div>
                                                                </div>
                                                                <div class="col-11">
                                                                    <div class="card">
                                                                        <div class="card-header card-header-large bg-white">
                                                                            <div class="row">
                                                                                <div class="col-6">
                                                                                    <div class="segmen_keluhan_utama">
                                                                                        <div class="d-flex align-items-center">
                                                                                            <a href="#" class="text-body"><strong class="text-15pt mr-2"><i class="fa fa-hashtag"></i> Pemeriksaan Fisik</strong></a>
                                                                                        </div>
                                                                                        <div class="card-body">
                                                                                            <p class="txt_pemeriksaan_fisik">

                                                                                            </p>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row projects-item mb-1">
                                                                <div class="col-1">
                                                                    <br />
                                                                    <div class="text-dark-gray">Asesmen</div>
                                                                </div>
                                                                <div class="col-11">
                                                                    <div class="card">
                                                                        <div class="card-header card-header-large bg-white">
                                                                            <div class="row">
                                                                                <div class="col-6">
                                                                                    <div class="segmen_diagnosa_utama">
                                                                                        <div class="d-flex align-items-center">
                                                                                            <a href="#" class="text-body"><strong class="text-15pt mr-2"><i class="fa fa-hashtag"></i> Diagnosa Kerja</strong></a>
                                                                                        </div>
                                                                                        <div class="card-body">
                                                                                            <p class="txt_diagnosa_kerja">

                                                                                            </p>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-6">
                                                                                    <div class="segmen_diagnosa_banding">
                                                                                        <div class="d-flex align-items-center">
                                                                                            <a href="#" class="text-body"><strong class="text-15pt mr-2"><i class="fa fa-hashtag"></i> Diagnosa Banding</strong></a>
                                                                                        </div>
                                                                                        <div class="card-body">
                                                                                            <p class="txt_diagnosa_banding">

                                                                                            </p>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row projects-item mb-1">
                                                                <div class="col-1">
                                                                    <br />
                                                                    <div class="text-dark-gray">Planning</div>
                                                                </div>
                                                                <div class="col-11">
                                                                    <div class="card">
                                                                        <div class="card-header card-header-large bg-white">
                                                                            <div class="row">
                                                                                <div class="col-6">
                                                                                    <div class="segmen_keluhan_utama">
                                                                                        <div class="d-flex align-items-center">
                                                                                            <a href="#" class="text-body"><strong class="text-15pt mr-2"><i class="fa fa-hashtag"></i> Planning</strong></a>
                                                                                        </div>
                                                                                        <div class="card-body">
                                                                                            <p class="txt_planning">

                                                                                            </p>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row projects-item mb-1">
                                                                <div class="col-1">
                                                                    <br />
                                                                    <div class="text-dark-gray">Resep & Racikan</div>
                                                                </div>
                                                                <div class="col-11">
                                                                    <div class="card">
                                                                        <div class="card-header card-header-large bg-white">
                                                                            <div class="row">
                                                                                <div class="col-6">
                                                                                    <div class="segmen_resep">
                                                                                        <div class="d-flex align-items-center">
                                                                                            <a href="#" class="text-body"><strong class="text-15pt mr-2"><i class="fa fa-hashtag"></i> Resep Dokter</strong></a>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="card-body">
                                                                                        <table class="table table-bordered resepDokterCPPT largeDataType">
                                                                                            <thead class="thead-dark">
                                                                                            <tr>
                                                                                                <th class="wrap_content">No</th>
                                                                                                <th>Obat</th>
                                                                                                <th>Signa</th>
                                                                                                <th>Jlh</th>
                                                                                            </tr>
                                                                                            </thead>
                                                                                            <tbody></tbody>
                                                                                        </table>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-6">
                                                                                    <div class="segmen_resep">
                                                                                        <div class="d-flex align-items-center">
                                                                                            <a href="#" class="text-body"><strong class="text-15pt mr-2"><i class="fa fa-hashtag"></i> Resep Apotek</strong></a>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="card-body">
                                                                                        <table class="table table-bordered resepApotekCPPT largeDataType">
                                                                                            <thead class="thead-dark">
                                                                                            <tr>
                                                                                                <th class="wrap_content">No</th>
                                                                                                <th>Obat</th>
                                                                                                <th>Signa</th>
                                                                                                <th>Jlh</th>
                                                                                            </tr>
                                                                                            </thead>
                                                                                            <tbody></tbody>
                                                                                        </table>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="card-header card-header-large bg-white">
                                                                            <div class="row">
                                                                                <div class="col-6">
                                                                                    <div class="segmen_racikan">
                                                                                        <div class="d-flex align-items-center">
                                                                                            <a href="#" class="text-body"><strong class="text-15pt mr-2"><i class="fa fa-hashtag"></i> Racikan Dokter</strong></a>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="card-body">
                                                                                        <table class="table table-bordered racikanDokterCPPT largeDataType">
                                                                                            <thead class="thead-dark">
                                                                                            <tr>
                                                                                                <th class="wrap_content">No</th>
                                                                                                <th>Racikan</th>
                                                                                                <th>Komposisi</th>
                                                                                                <th>Signa</th>
                                                                                                <th>Jlh</th>
                                                                                            </tr>
                                                                                            </thead>
                                                                                            <tbody></tbody>
                                                                                        </table>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-6">
                                                                                    <div class="segmen_racikan">
                                                                                        <div class="d-flex align-items-center">
                                                                                            <a href="#" class="text-body"><strong class="text-15pt mr-2"><i class="fa fa-hashtag"></i> Racikan Apotek</strong></a>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="card-body">
                                                                                        <table class="table table-bordered racikanApotekCPPT largeDataType">
                                                                                            <thead class="thead-dark">
                                                                                            <tr>
                                                                                                <th class="wrap_content">No</th>
                                                                                                <th>Racikan</th>
                                                                                                <th>Komposisi</th>
                                                                                                <th>Signa</th>
                                                                                                <th>Jlh</th>
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
                                                    </div>
                                                    <div class="tab-pane show fade" id="tab-poli-2">
                                                        <div class="row lab_loader"></div>
                                                    </div>
                                                    <div class="tab-pane show fade" id="tab-poli-3">
                                                        <div class="row rad_loader"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="btnClaimSEP">
                    <i class="fa fa-check-circle"></i> Buat Claim
                </button>

                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>







<div id="modal-sep-cetak" class="modal fade" role="dialog" aria-labelledby="modal-large-title" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-large-title">
                    <img src="<?php echo __HOSTNAME__;  ?>/template/assets/images/bpjs.png" class="img-responsive" width="275" height="45" style="margin-right: 50px" /> <span>Surat Eligibilitas Peserta</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6" id="data_sep_cetak_kiri">
                        <table class="table form-mode">
                            <tr>
                                <td>No. SEP</td>
                                <td class="wrap_content">:</td>
                                <td id="sep_nomor"></td>
                            </tr>
                            <tr>
                                <td>Tgl. SEP</td>
                                <td class="wrap_content">:</td>
                                <td id="sep_tanggal"></td>
                            </tr>
                            <tr>
                                <td>No. Kartu</td>
                                <td class="wrap_content">:</td>
                                <td id="sep_nomor_kartu"></td>
                            </tr>
                            <tr>
                                <td>Nama Peserta</td>
                                <td class="wrap_content">:</td>
                                <td id="sep_nama_peserta"></td>
                            </tr>
                            <tr>
                                <td>Tgl. Lahir</td>
                                <td class="wrap_content">:</td>
                                <td id="sep_tanggal_lahir"></td>
                            </tr>
                            <tr>
                                <td>No. Telp</td>
                                <td class="wrap_content">:</td>
                                <td id="sep_nomor_telepon"></td>
                            </tr>
                            <tr>
                                <td>Sub/Spesialis</td>
                                <td class="wrap_content">:</td>
                                <td id="sep_spesialis"></td>
                            </tr>
                            <tr>
                                <td>Faskes Penunjuk</td>
                                <td class="wrap_content">:</td>
                                <td id="sep_faskes_asal"></td>
                            </tr>
                            <tr>
                                <td>Diagnosa Awal</td>
                                <td class="wrap_content">:</td>
                                <td id="sep_diagnosa_awal"></td>
                            </tr>
                            <tr>
                                <td>Catatan</td>
                                <td class="wrap_content">:</td>
                                <td id="sep_catatan"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-6" id="data_sep_cetak_kanan">
                        <table class="table form-mode">
                            <tr>
                                <td>Peserta</td>
                                <td class="wrap_content">:</td>
                                <td id="sep_peserta"></td>
                            </tr>
                            <tr>
                                <td>COB</td>
                                <td class="wrap_content">:</td>
                                <td id="sep_cob"></td>
                            </tr>
                            <tr>
                                <td>Jenis Rawat</td>
                                <td class="wrap_content">:</td>
                                <td id="sep_jenis_rawat"></td>
                            </tr>
                            <tr>
                                <td>Kelas Rawat</td>
                                <td class="wrap_content">:</td>
                                <td id="sep_kelas_rawat"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-12" id="data_sep_cetak_bawah">
                        <small>
                            <i>
                                <ul type="*" style="margin: 0; padding: 10px;">
                                    <li>
                                        Saya menyetujui BPJS Kesehatan menggunakan informasi medis pasien jika diperlukan
                                    </li>
                                    <li>
                                        SEP bukan sebagai bukti penjaminan peserta
                                    </li>
                                </ul>
                            </i>
                        </small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="btnCetakSEP">
                    <i class="fa fa-print"></i> Cetak
                </button>

                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>