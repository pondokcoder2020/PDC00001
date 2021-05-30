<script type="text/javascript">
    $(function () {
        var listRI = $("#table-antrian-rawat-jalan").DataTable({
            processing: true,
            serverSide: true,
            sPaginationType: "full_numbers",
            bPaginate: true,
            lengthMenu: [[20, 50, -1], [20, 50, "All"]],
            serverMethod: "POST",
            "ajax":{
                url: __HOSTAPI__ + "/Inap",
                type: "POST",
                data: function(d) {
                    d.request = "get_rawat_inap";
                },
                headers:{
                    Authorization: "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>
                },
                dataSrc:function(response) {
                    var returnedData = [];
                    if(response == undefined || response.response_package == undefined) {
                        returnedData = [];
                    } else {
                        var data = response.response_package.response_data;
                        for(var key in data) {
                            if(data[key].pasien !== null && data[key].pasien !== undefined) {
                                returnedData.push(data[key]);
                            }
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
                searchPlaceholder: "Cari Nama Pasien / Nama Dokter / Ruangan"
            },
            "columns" : [
                {
                    "data" : null, render: function(data, type, row, meta) {
                        return "<span id=\"uid_" + row.uid + "\" keterangan=\"" + row.keterangan + "\">" + row.autonum + "</span>";
                    }
                },
                {
                    "data" : null, render: function(data, type, row, meta) {
                        return row.waktu_masuk_tanggal;
                    }
                },
                {
                    "data" : null, render: function(data, type, row, meta) {
                        return "<b kunjungan=\"" + row.kunjungan + "\" data=\"" + row.pasien.uid + "\" id=\"pasien_" + row.uid + "\" class=\"text-info\">" + row.pasien.no_rm + "</b><br />" + row.pasien.nama;
                    }
                },
                {
                    "data" : null, render: function(data, type, row, meta) {
                        return (row.kamar !== null) ? "<span bed=\"" + row.bed.uid + "\" kamar=\"" + row.kamar.uid + "\" id=\"kamar_" + row.uid + "\">" + row.kamar.nama + "</span><br />" + row.bed.nama  + "<br /><b class=\"text-info\">[" + row.nurse_station.kode_ns + "]</b> " +row.nurse_station.nama_ns: "";
                    }
                },
                {
                    "data" : null, render: function(data, type, row, meta) {
                        return "<span id=\"dokter_" + row.uid + "\" data=\"" + row.dokter.uid + "\">" + row.dokter.nama + "</span>"
                    }
                },
                {
                    "data" : null, render: function(data, type, row, meta) {
                        return "<span id=\"penjamin_" + row.uid + "\" data=\"" + row.penjamin.uid + "\">" + row.penjamin.nama + "</span>";
                    }
                },
                {
                    "data" : null, render: function(data, type, row, meta) {
                        return "<div class=\"btn-group wrap_content\" role=\"group\" aria-label=\"Basic example\">" +
                            /*"<a href=\"" + __HOSTNAME__ + "/rawat_inap/dokter/index/" + row.pasien.uid + "/" + row.kunjungan + "/" + row.penjamin.uid + "\" class=\"btn btn-sm btn-info\">" +
                            "<span><i class=\"fa fa-sign-out-alt\"></i> Proses</span>" +
                            "</a>" +*/
                            "<button class=\"btn btn-sm btn-info btnProsesInap\" id=\"btn_proses_" + row.uid + "\">" +
                            "<span><i class=\"fa fa-sign-out-alt\"></i> Proses</span>" +
                            "</<button>" +
                            "<button disabled class=\"btn btn-sm btn-success btn-pulangkan-pasien\" id=\"pulangkan_" + row.pasien.uid + "\">" +
                            "<i class=\"fa fa-check\"></i> Pulangkan Pasien" +
                            "</button>" +
                            "</div>";
                    }
                }
            ]
        });

        var selectedUID = "";
        var selectedPasien = "";
        var selectedKunjungan = "";

        $("body").on("click", ".btnProsesInap", function () {
            var id = $(this).attr("id").split("_");
            id = id[id.length - 1];

            selectedUID = id;
            selectedPasien = $("#pasien_" + id).attr("data");
            selectedKunjungan = $("#pasien_" + id).attr("kunjungan");

            $("#inap_penjamin").html("<option value=\"" + $("#penjamin_" + id).attr("data") + "\">" + $("#penjamin_" + id).html() + "</option>");
            $("#inap_dokter").html("<option>" + $("#dokter_" + id).html() + "</option>");
            $("#inap_keterangan").html($("#uid_" + id).attr("keterangan"));

            var kamar = $("#kamar_" + id).attr("kamar");
            var bed = $("#kamar_" + id).attr("bed");


            loadKamar("inap", kamar);
            loadBangsal("inap", $("#inap_kamar").val(), bed);


            $("#form-inap").modal("show");
        });

        $("body").on("click", ".btn-pulangkan-pasien", function () {
            var id = $(this).attr("id").split("_");
            selectedUID = id[id.length - 1];

            $("#form-pulang").modal("show");
        });

        $("#btnProsesInap").click(function () {
            Swal.fire({
                title: "Rawat Inap",
                text: "Proses Administrasi?",
                showDenyButton: true,
                confirmButtonText: "Ya",
                denyButtonText: "Tidak",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        async: false,
                        url:__HOSTAPI__ + "/Inap",
                        type: "POST",
                        data: {
                            request: "update_inap",
                            uid: selectedUID,
                            pasien: selectedPasien,
                            //waktu_masuk: $("#inap_tanggal_masuk").val(),
                            kamar: $("#inap_kamar").val(),
                            penjamin: $("#inap_penjamin").val(),
                            bed: $("#inap_bed").val(),
                            dokter: $("#inap_dokter").val(),
                            kunjungan: selectedKunjungan,
                            keterangan: $("#inap_keterangan").val()
                        },
                        beforeSend: function(request) {
                            request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                        },
                        success: function(response){
                            if(response.response_package.response_result > 0) {
                                Swal.fire(
                                    "Rawat Inap",
                                    "Administrasi berhasil diproses",
                                    "success"
                                ).then((result) => {
                                    listRI.ajax.reload();
                                    $("#form-inap").modal("hide");
                                });
                            } else {
                                console.log(response);
                            }
                            console.log(response);
                        },
                        error: function(response) {
                            console.log(response);
                        }
                    });
                } else {
                    $("#form-inap").modal("hide");
                }
            });
        });

        $("#btnSubmitPulang").click(function() {
            Swal.fire({
                title: "Rawat Inap",
                text: "Pulangkan pasien?",
                showDenyButton: true,
                confirmButtonText: "Ya",
                denyButtonText: "Tidak",
            }).then((result) => {
                if (result.isConfirmed) {

                    $.ajax({
                        async: false,
                        url:__HOSTAPI__ + "/Inap",
                        type: "POST",
                        data: {
                            request: "pulangkan_pasien",
                            uid: selectedUID,
                            jenis: $("input[name=\"txt_jenis_pulang\"]:checked").val(),
                            keterangan: $("#txt_keterangan_pulang").val()
                        },
                        beforeSend: function(request) {
                            request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                        },
                        success: function (response) {
                            if(response.response_package.response_result > 0) {
                                Swal.fire(
                                    "Rawat Inap",
                                    "Pasien dipulangkan!",
                                    "success"
                                ).then((result) => {
                                    $("#form-pulang").modal("hide");
                                    listRI.ajax.reload();
                                });
                            } else {
                                console.log(response);
                            }
                        },
                        error: function (response) {
                            //
                        }
                    });
                }
            });
        });

        loadKamar("inap");

        $(".inputan_inap").select2({
            dropdownParent:$("#form-inap")
        });

        $("#inap_kamar").change(function() {
            loadBangsal("inap", $("#inap_kamar").val());
        });

        function resetSelectBox(selector, name){
            $("#"+ selector +" option").remove();
            var opti_null = "<option value='' selected disabled>"+ name +" </option>";
            $("#" + selector).append(opti_null);
        }

        function loadKamar(target_ui, selected = ""){
            resetSelectBox(target_ui + "_kamar", "Ruangan");

            $.ajax({
                async: false,
                url:__HOSTAPI__ + "/Ruangan",
                type: "GET",
                beforeSend: function(request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                success: function(response){
                    var MetaData = dataPoli = response.response_package.response_data;

                    if (MetaData !== undefined) {
                        for(i = 0; i < MetaData.length; i++){
                            var selection = document.createElement("OPTION");

                            $(selection).attr("value", MetaData[i].uid).html(MetaData[i].nama);
                            if(MetaData[i].uid === selected) {
                                $(selection).attr({
                                    "selected": "selected"
                                });
                            }
                            $("#" + target_ui + "_kamar").append(selection);
                        }
                    }
                },
                error: function(response) {
                    console.log(response);
                }
            })
        }

        function loadBangsal(target_ui, kamar, selected = ""){
            resetSelectBox(target_ui + "_bed", "Pilih Ranjang");

            $.ajax({
                async: false,
                url:__HOSTAPI__ + "/Bed/bed-ruangan-avail/" + kamar,
                type: "GET",
                beforeSend: function(request) {
                    request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                },
                success: function(response){
                    var MetaData = dataPoli = response.response_package.response_data;

                    if (MetaData !== undefined){
                        for(i = 0; i < MetaData.length; i++){
                            var selection = document.createElement("OPTION");

                            $(selection).attr("value", MetaData[i].uid).html(MetaData[i].nama);
                            if(MetaData[i].uid === selected) {
                                $(selection).attr({
                                    "selected": "selected"
                                });
                            }
                            $("#" + target_ui + "_bed").append(selection);
                        }
                    }
                },
                error: function(response) {
                    console.log(response);
                }
            })
        }
    });
</script>

<div id="form-inap" class="modal fade" role="dialog" aria-labelledby="modal-large-title" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-large-title">Proses Rawat Inap</h5>
            </div>
            <div class="modal-body" id="inap-container">
                <div class="card card-form">
                    <div class="row no-gutters">
                        <div class="col-lg-12 card-body">
                            <div class="form-row">
                                <div class="col-12 col-md-6 mb-3">
                                    <label>Pembayaran <span class="red">*</span></label>
                                    <select id="inap_penjamin" class="form-control select2 inputan_inap" readonly disabled></select>
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label>Dokter <span class="red">*</span></label>
                                    <select id="inap_dokter" class="form-control select2 inputan_inap" readonly disabled></select>
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label>Kamar <span class="red">*</span></label>
                                    <select id="inap_kamar" class="form-control select2 inputan_inap" required>
                                        <option value="" disabled selected>Pilih Kamar</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label>Ranjang <span class="red">*</span></label>
                                    <select id="inap_bed" class="form-control select2 inputan_inap" required>
                                        <option value="" disabled selected>Pilih Ranjang</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-12 mb-12">
                                    <label>Keterangan <span class="red">*</span></label>
                                    <textarea type="text" id="inap_keterangan" class="form-control" placeholder="Keterangan"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="btnProsesInap">
                    <span>
                        <i class="fa fa-check"></i> Proses
                    </span>
                </button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <span>
                        <i class="fa fa-ban"></i> Kembali
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>




<div id="form-pulang" class="modal fade" role="dialog" aria-labelledby="modal-large-title" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-large-title">Pemulangan Pasien</h5>
            </div>
            <div class="modal-body">
                <div class="form-group col-md-12">
                    <h6>Jenis Pulang</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="txt_jenis_pulang" value="P" checked/>
                                <label class="form-check-label">
                                    PAPS
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="txt_jenis_pulang" value="D" />
                                <label class="form-check-label">
                                    Dokter
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <h6>Keterangan Pemulangan</h6>
                    <textarea style="min-height: 100px;" class="form-control" id="txt_keterangan_pulang"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Kembali</button>
                <button type="button" class="btn btn-primary" id="btnSubmitPulang">Proses</button>
            </div>
        </div>
    </div>
</div>