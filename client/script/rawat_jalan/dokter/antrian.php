<script type="text/javascript">
	$(function() {
		var poliListRaw = <?php echo json_encode($_SESSION['poli']['response_data'][0]['poli']['response_data']); ?>;
		console.log(poliListRaw);
		var poliList = poliListRaw;
		var allICD10 = load_icd_10();
		var selectedICD10Kerja = [], selectedICD10Banding = [];
		poliList.tindakan = [];
		//Filter Rawat Jalan
		for(var z in poliListRaw.tindakan) {
			if(poliListRaw.tindakan[z].kelas == __UID_KELAS_GENERAL_RJ__) {
				poliList.tindakan.push(poliListRaw.tindakan);
			}
		}

		//Init
		let editorKeluhanUtamaData, editorKeluhanTambahanData, editorPeriksaFisikData, editorKerja, editorBanding, editorKeteranganResep, editorKeteranganResepRacikan, editorPlanning;
		var antrianData, asesmen_detail;
		var tindakanMeta = [];
		var usedTindakan = [];
		var pasien_penjamin, pasien_penjamin_uid;
		var pasien_uid;
		var UID = __PAGES__[3];
		$("#info-pasien-perawat").remove();
		$.ajax({
			url:__HOSTAPI__ + "/Antrian/antrian-detail/" + UID,
			async:false,
			beforeSend: function(request) {
				request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
			},
			type:"GET",
			success:function(response) {
				antrianData = response.response_package.response_data[0];
				pasien_uid = antrianData.pasien_info.uid;
				var pasien_nama = antrianData.pasien_info.nama;
				var pasien_rm = antrianData.pasien_info.no_rm;
				var pasien_jenkel = antrianData.pasien_info.jenkel_nama;
				var pasien_tanggal_lahir = antrianData.pasien_info.tanggal_lahir;
				var pasien_penjamin = antrianData.penjamin_data.nama;
				pasien_penjamin_uid = antrianData.penjamin_data.uid;

				$(".nama_pasien").html(pasien_nama + " <span class=\"text-info\">[" + pasien_rm + "]</span>");
				$(".jk_pasien").html(pasien_jenkel);
				$(".tanggal_lahir_pasien").html(pasien_tanggal_lahir);
				$(".penjamin_pasien").html(pasien_penjamin);

				$.ajax({
					url:__HOSTAPI__ + "/Asesmen/antrian-detail/" + UID,
					async:false,
					beforeSend: function(request) {
						request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
					},
					type:"GET",
					success:function(response) {
						
						if(response.response_package.response_data[0].asesmen_rawat != undefined) {
							//loadAssesmen(response.response_package.response_data[0].asesmen_rawat);
							loadPasien(UID);
						} else {
							//console.log(response.response_package.response_data[0]);
							/*loadAssesmen(response.response_package.response_data[0].asesmen_rawat);
							loadPasien(UID);*/
						}

						if(response.response_package.response_data[0] === undefined) {
							asesmen_detail = {};
							tindakanMeta = generateTindakan(poliList[0].tindakan, antrianData, usedTindakan);
						} else {
							asesmen_detail = response.response_package.response_data[0];
							if(asesmen_detail.tindakan !== undefined) {
								if(asesmen_detail.tindakan.length > 0) {
									for(var tindakanKey in asesmen_detail.tindakan) {
										if(usedTindakan.indexOf(asesmen_detail.tindakan[tindakanKey].uid) < 0) {
											usedTindakan.push(asesmen_detail.tindakan[tindakanKey].uid);
											tindakanMeta = generateTindakan(poliList[0].tindakan, antrianData, usedTindakan);

											autoTindakan(tindakanMeta, {
												uid: asesmen_detail.tindakan[tindakanKey].uid,
												nama: asesmen_detail.tindakan[tindakanKey].nama
											}, antrianData);
										}
									}
								} else {
									tindakanMeta = generateTindakan(poliList[0].tindakan, antrianData, usedTindakan);
								}
							} else {
								tindakanMeta = generateTindakan(poliList[0].tindakan, antrianData, usedTindakan);
							}

							var keterangan_resep = "";
							var keterangan_racikan = "";

							if(response.response_package.response_data[0].resep !== undefined) {
								if(response.response_package.response_data[0].resep.length > 0) {

									var resep_uid = response.response_package.response_data[0].resep[0].uid;
									var resep_obat_detail = response.response_package.response_data[0].resep[0].resep_detail;
									
									keterangan_resep = response.response_package.response_data[0].resep[0].keterangan;
									keterangan_racikan = response.response_package.response_data[0].resep[0].keterangan_racikan;

									for(var resepKey in resep_obat_detail) {
										autoResep({
											"obat": resep_obat_detail[resepKey].obat,
											"aturan_pakai": resep_obat_detail[resepKey].aturan_pakai,
											"keterangan": resep_obat_detail[resepKey].keterangan,
											"signaKonsumsi": resep_obat_detail[resepKey].signa_qty,
											"signaTakar": resep_obat_detail[resepKey].signa_pakai,
											"signaHari": resep_obat_detail[resepKey].qty,
											"pasien_penjamin_uid": pasien_penjamin_uid
										});
									}

									if(resep_obat_detail.length > 0) {
										autoResep();
									}
								}

								var racikan_detail = response.response_package.response_data[0].racikan;
								for(var racikanKey in racikan_detail) {
									autoRacikan({
										nama: racikan_detail[racikanKey].kode,
										keterangan: racikan_detail[racikanKey].keterangan,
										"signaKonsumsi": racikan_detail[racikanKey].signa_qty,
										"signaTakar": racikan_detail[racikanKey].signa_pakai,
										"signaHari": racikan_detail[racikanKey].qty,
										"item":racikan_detail[racikanKey].item,
										"aturan_pakai": racikan_detail[racikanKey].aturan_pakai
									});
									var itemKomposisi = racikan_detail[racikanKey].item;
									for(var komposisiKey in itemKomposisi) {
										var penjaminObatRacikanListUID = [];
										var penjaminObatRacikanList = itemKomposisi[komposisiKey].obat_detail.penjamin;
										for(var penjaminObatKey in penjaminObatRacikanList) {
											if(penjaminObatRacikanListUID.indexOf(penjaminObatRacikanList[penjaminObatKey].penjamin) < 0) {
												penjaminObatRacikanListUID.push(penjaminObatRacikanList[penjaminObatKey].penjamin);
											}
										}

										itemKomposisi[komposisiKey].satuan = "<b>" + itemKomposisi[komposisiKey].takar_bulat + "</b><sub nilaiExact=\"" + itemKomposisi[komposisiKey].ratio + "\">" + itemKomposisi[komposisiKey].takar_decimal + "</sub>";

										if(penjaminObatRacikanListUID.indexOf(pasien_penjamin_uid) > 0) {
											infoPenjamin = "<b class=\"badge badge-success\"><i class=\"fa fa-check-circle\" style=\"margin-right: 5px;\"></i> Ditanggung Penjamin</b>";
										} else {
											infoPenjamin = "<b class=\"badge badge-danger\"><i class=\"fa fa-ban\" style=\"margin-right: 5px;\"></i> Tidak Ditanggung Penjamin</b>";
										}

										itemKomposisi[komposisiKey].obat_detail.nama += infoPenjamin;
										autoKomposisi((parseInt(racikanKey) + 1), itemKomposisi[komposisiKey]);
									}
								}
								if(racikan_detail.length > 0) {
									autoRacikan();	
								}
							}
							checkGenerateRacikan();
						}
						
						/*load_icd_10("#txt_icd_10_kerja", asesmen_detail.icd10_kerja);
						load_icd_10("#txt_icd_10_banding", asesmen_detail.icd10_banding);*/
						selectedICD10Kerja = asesmen_detail.icd10_kerja;
						selectedICD10Banding = asesmen_detail.icd10_banding;
						var rawSelectedKerja = [];
						var rawSelectedBanding = [];

						var icd10KerjaDataParse = asesmen_detail.icd10_kerja;
						for(var icd10KerjaKey in icd10KerjaDataParse) {
							if(rawSelectedKerja.indexOf(parseInt(icd10KerjaDataParse[icd10KerjaKey].id)) < 0) {
								rawSelectedKerja.push(parseInt(icd10KerjaDataParse[icd10KerjaKey].id));
								$("#txt_diagnosa_kerja_list tbody").append(
									"<tr targetICD=\"" + parseInt(icd10KerjaDataParse[icd10KerjaKey].id) + "\">" +
										"<td>" + ($("#txt_diagnosa_kerja_list tbody tr").length + 1) + "</td>" +
										"<td>" + icd10KerjaDataParse[icd10KerjaKey].nama + "</td>" +
										"<td><button class=\"btn btn-sm btn-danger btn_delete_icd_kerja\" targetICD=\"" + parseInt(icd10KerjaDataParse[icd10KerjaKey].id) + "\"><i class=\"fa fa-trash\"></i></button></td>" +
									"</tr>"
								);
							}
						}

						var icd10BandingDataParse = asesmen_detail.icd10_banding;
						for(var icd10BandingKey in icd10BandingDataParse) {
							if(rawSelectedBanding.indexOf(parseInt(icd10BandingDataParse[icd10BandingKey].id)) < 0) {
								rawSelectedBanding.push(parseInt(icd10BandingDataParse[icd10BandingKey].id));
								$("#txt_diagnosa_banding_list tbody").append(
									"<tr targetICD=\"" + parseInt(icd10BandingDataParse[icd10BandingKey].id) + "\">" +
										"<td>" + ($("#txt_diagnosa_banding_list tbody tr").length + 1) + "</td>" +
										"<td>" + icd10BandingDataParse[icd10BandingKey].nama + "</td>" +
										"<td><button class=\"btn btn-sm btn-danger btn_delete_icd_banding\" targetICD=\"" + parseInt(icd10BandingDataParse[icd10BandingKey].id) + "\"><i class=\"fa fa-trash\"></i></button></td>" +
									"</tr>"
								);
							}
						}
						
						parse_icd_10("#txt_icd_10_kerja", allICD10, rawSelectedKerja);
						parse_icd_10("#txt_icd_10_banding", allICD10, rawSelectedBanding);

						$("#txt_icd_10_kerja").select2();
						$("#txt_icd_10_banding").select2();
						
						ClassicEditor
							.create( document.querySelector( '#txt_keluhan_utama' ), {
								extraPlugins: [ MyCustomUploadAdapterPlugin ],
								placeholder: "Keluhan Utama..."
							} )
							.then( editor => {
								if(asesmen_detail.keluhan_utama === undefined) {
									editor.setData("");	
								} else {
									editor.setData(asesmen_detail.keluhan_utama);
								}
								editorKeluhanUtamaData = editor;
								window.editor = editor;
							} )
							.catch( err => {
								//console.error( err.stack );
							} );

						ClassicEditor
							.create( document.querySelector( '#txt_keluhan_tambahan' ), {
								extraPlugins: [ MyCustomUploadAdapterPlugin ],
								placeholder: "Keluhan Tambahan..."
							} )
							.then( editor => {
								if(asesmen_detail.keluhan_tambahan === undefined) {
									editor.setData("");	
								} else {
									editor.setData(asesmen_detail.keluhan_tambahan);
								}
								editorKeluhanTambahanData = editor;
								window.editor = editor;
							} )
							.catch( err => {
								//console.error( err.stack );
							} );

						/*$("#txt_tanda_vital_td").val(asesmen_detail.tekanan_darah);
						$("#txt_tanda_vital_s").val(asesmen_detail.suhu);
						$("#txt_tanda_vital_n").val(asesmen_detail.nadi);
						$("#txt_tanda_vital_rr").val(asesmen_detail.pernafasan);
						$("#txt_berat_badan").val(asesmen_detail.berat_badan);*/
						$("#txt_tinggi_badan").val(asesmen_detail.tinggi_badan);
						$("#txt_lingkar_lengan").val(asesmen_detail.lingkar_lengan_atas);

						ClassicEditor
							.create( document.querySelector( '#txt_pemeriksaan_fisik' ), {
								extraPlugins: [ MyCustomUploadAdapterPlugin ],
								placeholder: "Pemeriksaan Fisik..."
							} )
							.then( editor => {
								if(asesmen_detail.pemeriksaan_fisik === undefined) {
									editor.setData("");	
								} else {
									editor.setData(asesmen_detail.pemeriksaan_fisik);
								}
								editorPeriksaFisikData = editor;
								window.editor = editor;
							} )
							.catch( err => {
								//console.error( err.stack );
							} );

						ClassicEditor
							.create( document.querySelector( '#txt_diagnosa_kerja' ), {
								extraPlugins: [ MyCustomUploadAdapterPlugin ],
								placeholder: "Diagnosa Kerja..."
							} )
							.then( editor => {
								if(asesmen_detail.diagnosa_kerja === undefined) {
									editor.setData("");	
								} else {
									editor.setData(asesmen_detail.diagnosa_kerja);
								}
								editorKerja = editor;
								window.editor = editor;
							} )
							.catch( err => {
								//console.error( err.stack );
							} );

						ClassicEditor
							.create( document.querySelector( '#txt_diagnosa_banding' ), {
								extraPlugins: [ MyCustomUploadAdapterPlugin ],
							} )
							.then( editor => {
								if(asesmen_detail.diagnosa_banding === undefined) {
									editor.setData("");	
								} else {
									editor.setData(asesmen_detail.diagnosa_banding);
								}
								editorBanding = editor;
								window.editor = editor;
							} )
							.catch( err => {
								//console.error( err.stack );
							} );


						ClassicEditor
							.create( document.querySelector( '#txt_keterangan_resep' ), {
								extraPlugins: [ MyCustomUploadAdapterPlugin ],
								placeholder: "Keterangan resep..."
							} )
							.then( editor => {
								editor.setData(keterangan_resep);
								editorKeteranganResep = editor;
								window.editor = editor;
							} )
							.catch( err => {
								//console.error( err.stack );
							} );

						ClassicEditor
							.create( document.querySelector( '#txt_keterangan_resep_racikan' ), {
								extraPlugins: [ MyCustomUploadAdapterPlugin ],
								placeholder: "Keterangan resep..."
							} )
							.then( editor => {
								editor.setData(keterangan_racikan);
								editorKeteranganResepRacikan = editor;
								window.editor = editor;
							} )
							.catch( err => {
								//console.error( err.stack );
							} );

						ClassicEditor
							.create( document.querySelector( '#txt_planning' ), {
								extraPlugins: [ MyCustomUploadAdapterPlugin ],
								placeholder: "Planning Tindakan"
							} )
							.then( editor => {
								if(asesmen_detail.planning === undefined) {
									editor.setData("");	
								} else {
									editor.setData(asesmen_detail.planning);
								}
								editorPlanning = editor;
								window.editor = editor;
							} )
							.catch( err => {
								//console.error( err.stack );
							} );
					},
					error: function(response) {
						console.log(response);
					}
				});
			},
			error: function(response) {
				console.log(response);
			}
		});
				

		if(poliList.length > 1) {
			$("#change-poli").show();
			$("#current-poli").addClass("handy");
		} else {
			$("#change-poli").hide();
			$("#current-poli").removeClass("handy");
		}

		$("#btn_tambah_icd10_kerja").click(function() {
			var allowAdd = false;
			if(selectedICD10Kerja.length > 0) {
				for(var selectedKeyKerja in selectedICD10Kerja) {
					if(selectedICD10Kerja[selectedKeyKerja].id != parseInt($("#txt_icd_10_kerja").val())) {
						allowAdd = true;
					} else {
						allowAdd = false;
						break;
					}
				}
			} else {
				allowAdd = true;
			}

			if(allowAdd) {
				$("#txt_diagnosa_kerja_list tbody").append(
					"<tr targetICD=\"" + parseInt($("#txt_icd_10_kerja").val()) + "\">" +
						"<td>" + ($("#txt_diagnosa_kerja_list tbody tr").length + 1) + "</td>" +
						"<td>" + $("#txt_icd_10_kerja option:selected").text() + "</td>" +
						"<td><button class=\"btn btn-sm btn-danger btn_delete_icd_kerja\" targetICD=\"" + parseInt($("#txt_icd_10_kerja").val()) + "\"><i class=\"fa fa-trash\"></i></button></td>" +
					"</tr>"
				);
				
				selectedICD10Kerja.push({
					id: parseInt($("#txt_icd_10_kerja").val()),
					nama: $("#txt_icd_10_kerja option[value=\"" + parseInt($("#txt_icd_10_kerja").val()) + "\"]").text()
				});

				$("#txt_icd_10_kerja option[value=\"" + parseInt($("#txt_icd_10_kerja").val()) + "\"]").remove();
				rebaseICD("#txt_diagnosa_kerja_list");
			}
		});

		$("body").on("click", ".btn_delete_icd_kerja", function() {
			var id = $(this).attr("targetICD");
			for(var selectedKeyKerja in selectedICD10Kerja) {
				if(selectedICD10Kerja[selectedKeyKerja].id == id) {
					$("#txt_diagnosa_kerja_list tbody tr[targetICD=\"" + selectedICD10Kerja[selectedKeyKerja].id +"\"]").remove();
					$("#txt_icd_10_kerja").prepend("<option value=\"" + selectedICD10Kerja[selectedKeyKerja].id + "\">" + selectedICD10Kerja[selectedKeyKerja].nama + "</option>");
					selectedICD10Kerja.splice(selectedKeyKerja, 1);
				}
			}
			rebaseICD("#txt_diagnosa_kerja_list");
		});

		function rebaseICD(target) {
			$(target + " tbody tr").each(function(e) {
				$(this).find("td:eq(0)").html((e + 1));
			});
		}


		$("#btn_tambah_icd10_banding").click(function() {
			var allowAdd = false;
			if(selectedICD10Banding.length > 0) {
				for(var selectedKeyBanding in selectedICD10Banding) {
					if(selectedICD10Banding[selectedKeyBanding].id != parseInt($("#txt_icd_10_banding").val())) {
						allowAdd = true;
					} else {
						allowAdd = false;
						break;
					}
				}
			} else {
				allowAdd = true;
			}

			if(allowAdd) {
				$("#txt_diagnosa_banding_list tbody").append(
					"<tr targetICD=\"" + parseInt($("#txt_icd_10_banding").val()) + "\">" +
						"<td>" + ($("#txt_diagnosa_banding_list tbody tr").length + 1) + "</td>" +
						"<td>" + $("#txt_icd_10_banding option:selected").text() + "</td>" +
						"<td><button class=\"btn btn-sm btn-danger btn_delete_icd_banding\" targetICD=\"" + parseInt($("#txt_icd_10_banding").val()) + "\"><i class=\"fa fa-trash\"></i></button></td>" +
					"</tr>"
				);
				
				selectedICD10Banding.push({
					id: parseInt($("#txt_icd_10_banding").val()),
					nama: $("#txt_icd_10_banding option[value=\"" + parseInt($("#txt_icd_10_banding").val()) + "\"]").text()
				});

				$("#txt_icd_10_banding option[value=\"" + parseInt($("#txt_icd_10_banding").val()) + "\"]").remove();
				rebaseICD("#txt_diagnosa_banding_list");
			}
		});

		$("body").on("click", ".btn_delete_icd_banding", function() {
			var id = $(this).attr("targetICD");
			for(var selectedKeyBanding in selectedICD10Banding) {
				if(selectedICD10Banding[selectedKeyBanding].id == id) {
					$("#txt_diagnosa_banding_list tbody tr[targetICD=\"" + selectedICD10Banding[selectedKeyBanding].id +"\"]").remove();
					$("#txt_icd_10_banding").prepend("<option value=\"" + selectedICD10Banding[selectedKeyBanding].id + "\">" + selectedICD10Banding[selectedKeyBanding].nama + "</option>");
					selectedICD10Banding.splice(selectedKeyBanding, 1);
				}
			}
			rebaseICD("#txt_diagnosa_banding_list");
		});

		

		$("#current-poli").prepend(poliList[0]['nama']);

		function generateTindakan(poliList, antrianData, selected = []) {

			var tindakanMeta = {};
			$("#txt_tindakan option").remove();
			var __UID_KONSULTASI__ = <?php echo json_encode(__UID_KONSULTASI__); ?>;
			var __UID_KARTU__ = <?php echo json_encode(__UID_KARTU__); ?>;
			for(var key in poliList) {
				if(poliList[key].tindakan != null) {
					if(tindakanMeta[poliList[key].uid_tindakan] === undefined) {
						tindakanMeta[poliList[key].uid_tindakan] = [];
						tindakanMeta[poliList[key].uid_tindakan].kelas = poliList[key].kelas;
						tindakanMeta[poliList[key].uid_tindakan].nama = poliList[key].tindakan.nama;
					}

					if(poliList[key].penjamin != undefined){
						if(antrianData.penjamin == poliList[key].uid_penjamin) {
							tindakanMeta[poliList[key].uid_tindakan].push({
								uid: poliList[key].uid_penjamin,
								nama: poliList[key].penjamin.nama
							});
						}
					}
				}
			}

			for(var key in tindakanMeta) {
				if(selected.indexOf(key) < 0 && tindakanMeta[key].nama != undefined && key != __UID_KONSULTASI__ && key != __UID_KARTU__) {
					$("#txt_tindakan").append(
						"<option value=\"" + key + "\" kelas=\"" + tindakanMeta[key].kelas + "\">" + tindakanMeta[key].nama + "</option>"
					);
				}
			}
			return tindakanMeta;
		}

		$("#txt_tindakan").select2();

		$("#btnTambahTindakan").click(function(){
			autoTindakan(tindakanMeta, {
				uid: $("#txt_tindakan").val(),
				nama: $("#txt_tindakan option:selected").text(),
				kelas: $("#txt_tindakan option:selected").attr("kelas"),
			}, antrianData);
			
			if(usedTindakan.indexOf($("#txt_tindakan").val()) < 0) {
				usedTindakan.push($("#txt_tindakan").val());
				tindakanMeta = generateTindakan(poliList[0].tindakan, antrianData, usedTindakan);
			}
			
			return false;
		});

		$("body").on("click", ".btnDeleteTindakan", function(){
			var id = $(this).attr("id").split("_");
			id = id[id.length - 1];
			$("#row_tindakan_" + id).remove();
			usedTindakan.splice(usedTindakan.indexOf($(this).val()), 1);
			tindakanMeta = generateTindakan(poliList[0].tindakan, antrianData, usedTindakan);
			return false;
		});

		function autoTindakan(penjaminMeta, setTindakan, selectedPenjamin) {
			var newRowTindakan = document.createElement("TR");
			var newCellTindakanID = document.createElement("TD");
			var newCellTindakanTindakan = document.createElement("TD");
			var newCellTindakanPenjamin = document.createElement("TD");
			var newCellTindakanAksi = document.createElement("TD");

			$(newCellTindakanTindakan).html(setTindakan.nama).attr({
				"set-tindakan": setTindakan.uid
			}).attr("kelas", setTindakan.kelas);
			var newPenjamin = document.createElement("SELECT");
			
			for(var a = 0; a < penjaminMeta[setTindakan.uid].length; a++) {
				if(penjaminMeta[setTindakan.uid][a].uid == antrianData.penjamin) {
					$(newPenjamin).append("<option " + ((penjaminMeta[setTindakan.uid][a].uid == selectedPenjamin.penjamin) ? "selected=\"selected\"" : "") + " value=\"" + penjaminMeta[setTindakan.uid][a].uid + "\">" + penjaminMeta[setTindakan.uid][a].nama + "</option>");
				}
			}
			
			$(newCellTindakanPenjamin).append(newPenjamin);
			$(newPenjamin).addClass("form-control").select2();
			

			var newPenjaminDelete = document.createElement("BUTTON");
			$(newPenjaminDelete).addClass("btn btn-sm btn-danger btnDeleteTindakan").html("<i class=\"fa fa-ban\"></i>");
			$(newCellTindakanAksi).append(newPenjaminDelete);

			$(newRowTindakan).append(newCellTindakanID);
			$(newRowTindakan).append(newCellTindakanTindakan);
			$(newRowTindakan).append(newCellTindakanPenjamin);
			$(newRowTindakan).append(newCellTindakanAksi);

			$("#table-tindakan").append(newRowTindakan);
			rebaseTindakan();
		}

		function rebaseTindakan() {
			$("#table-tindakan tbody tr").each(function(e) {
				var id = (e + 1);
				$(this).attr({
					"id": "row_tindakan_" + id
				});

				$(this).find("td:eq(0)").html(id);
				$(this).find("td:eq(3) button").attr({
					"id": "delete_tindakan_" + id
				});
			});
		}

		function load_icd_10() {
			var icd10Data;
			$.ajax({
				url:__HOSTAPI__ + "/Icd/icd10",
				async:false,
				beforeSend: function(request) {
					request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
				},
				type:"GET",
				success:function(response) {
					icd10Data = response.response_package.response_data;
				},
				error: function(response) {
					console.log(response);
				}
			});
			return icd10Data;
		}

		function parse_icd_10(target, icd10Data, selectedData = []) {
			$(target + " option").remove();

			for(var a = 0; a < icd10Data.length; a++) {
				if(selectedData.indexOf(parseInt(icd10Data[a].id)) < 0) {
					$(target).append("<option value=\"" + icd10Data[a].id + "\">" + icd10Data[a].kode + " - " + icd10Data[a].nama + "</option>");
				}
			}
		}

		class MyUploadAdapter {
			static loader;
		    constructor( loader ) {
		        // CKEditor 5's FileLoader instance.
		        this.loader = loader;

		        // URL where to send files.
		        this.url = __HOSTAPI__ + "/Upload";

		        this.imageList = [];
		    }

		    // Starts the upload process.
		    upload() {
		        return new Promise( ( resolve, reject ) => {
		            this._initRequest();
		            this._initListeners( resolve, reject );
		            this._sendRequest();
		        } );
		    }

		    // Aborts the upload process.
		    abort() {
		        if ( this.xhr ) {
		            this.xhr.abort();
		        }
		    }

		    // Example implementation using XMLHttpRequest.
		    _initRequest() {
		        const xhr = this.xhr = new XMLHttpRequest();

		        xhr.open( 'POST', this.url, true );
		        xhr.setRequestHeader("Authorization", 'Bearer ' + <?php echo json_encode($_SESSION["admin_ciscard"]); ?>);
		        xhr.responseType = 'json';
		    }

		    // Initializes XMLHttpRequest listeners.
		    _initListeners( resolve, reject ) {
		        const xhr = this.xhr;
		        const loader = this.loader;
		        const genericErrorText = 'Couldn\'t upload file:' + ` ${ loader.file.name }.`;

		        xhr.addEventListener( 'error', () => reject( genericErrorText ) );
		        xhr.addEventListener( 'abort', () => reject() );
		        xhr.addEventListener( 'load', () => {
		            const response = xhr.response;

		            if ( !response || response.error ) {
		                return reject( response && response.error ? response.error.message : genericErrorText );
		            }

		            // If the upload is successful, resolve the upload promise with an object containing
		            // at least the "default" URL, pointing to the image on the server.
		            resolve( {
		                default: response.url
		            } );
		        } );

		        if ( xhr.upload ) {
		            xhr.upload.addEventListener( 'progress', evt => {
		                if ( evt.lengthComputable ) {
		                    loader.uploadTotal = evt.total;
		                    loader.uploaded = evt.loaded;
		                }
		            } );
		        }
		    }


		    // Prepares the data and sends the request.
		    _sendRequest() {
		    	const toBase64 = file => new Promise((resolve, reject) => {
				    const reader = new FileReader();
				    reader.readAsDataURL(file);
				    reader.onload = () => resolve(reader.result);
				    reader.onerror = error => reject(error);
				});
				var Axhr = this.xhr;
				
				async function doSomething(fileTarget) {
					fileTarget.then(function(result) {
						var ImageName = result.name;

						toBase64(result).then(function(renderRes) {
							const data = new FormData();
							data.append( 'upload', renderRes);
							data.append( 'name', ImageName);
							Axhr.send( data );
						});
					});
				}

				var ImageList = this.imageList;

				this.loader.file.then(function(toAddImage) {

					ImageList.push(toAddImage.name);

				});
				
				this.imageList = ImageList;

				doSomething(this.loader.file);
		    }
		}


		function MyCustomUploadAdapterPlugin( editor ) {
		    editor.plugins.get( 'FileRepository' ).createUploadAdapter = ( loader ) => {
		        var MyCust = new MyUploadAdapter( loader );
		        var dataToPush = MyCust.imageList;
		        hiJackImage(dataToPush);
		        return MyCust;
		    };
		}

		var imageResultPopulator = [];

		function hiJackImage(toHi) {
			imageResultPopulator.push(toHi);
		}
			

		function load_product_penjamin(target, obat, selectedData = "") {
			var productData;
			$.ajax({
				/*url:__HOSTAPI__ + "/Penjamin/get_penjamin_obat/" + obat,*/
				url:__HOSTAPI__ + "/Penjamin/get_penjamin_obat/" + obat,
				async:false,
				beforeSend: function(request) {
					request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
				},
				type:"GET",
				success:function(response) {
					$(target).find("option").remove();
					productData = response.response_package.response_data;
					for (var a = 0; a < productData.length; a++) {
						$(target).append("<option " + ((productData[a].penjamin.uid == selectedData) ? "selected=\"selected\"" : "") + " value=\"" + penjaminData[a].penjamin.uid + "\">" + penjaminData[a].penjamin.nama + "</option>");
					}
				},
				error: function(response) {
					console.log(response);
				}
			});
			return productData;
		}

		function load_product_resep(target, selectedData = "", appendData = true) {
			var selected = [];
			var productData;
			$.ajax({
				url:__HOSTAPI__ + "/Inventori",
				async:false,
				beforeSend: function(request) {
					request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
				},
				type:"GET",
				success:function(response) {
					$(target).find("option").remove();
					$(target).append("<option value=\"none\">Pilih Obat</option>");
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
			//return (productData.length == selected.length);
			return {
				allow: (productData.length == selected.length),
				data: productData
			};
		}

		checkGenerateResep();

		function checkGenerateResep(id = 0) {
			if($(".last-resep").length == 0) {
				autoResep();
			} else {
				var obat = $("#resep_obat_" + id).val();
				var jlh_hari = $("#resep_jlh_hari_" + id).inputmask("unmaskedvalue");
				var signa_konsumsi = $("#resep_signa_konsumsi_" + id).inputmask("unmaskedvalue");
				var signa_hari = $("#resep_signa_takar_" + id).inputmask("unmaskedvalue");

				if(
					parseFloat(jlh_hari) > 0 &&
					parseFloat(signa_konsumsi) > 0 &&
					parseFloat(signa_hari) > 0 &&
					obat != null &&
					$("#resep_row_" + id).hasClass("last-resep")
				) {
					autoResep();
				}
			}
		}

		function autoAturanPakai() {
			var dataAturanPakai;
			$.ajax({
				url:__HOSTAPI__ + "/Terminologi/terminologi-items/15",
				async:false,
				beforeSend: function(request) {
					request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
				},
				type:"GET",
				success:function(response) {
					dataAturanPakai = response.response_package.response_data;
				},
				error: function(response) {
					console.log(response);
				}
			});
			return dataAturanPakai;

		}

		function autoKategoriObat(obat) {
			var kategoriObat;
			$.ajax({
				url:__HOSTAPI__ + "/Inventori/kategori_per_obat/" + obat,
				async:false,
				beforeSend: function(request) {
					request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
				},
				type:"GET",
				success:function(response) {
					kategoriObat = response.response_package;
				},
				error: function(response) {
					console.log(response);
				}
			});
			return kategoriObat;
		}

		function checkPenjaminAvail(currentPenjamin, penjaminList, targetRow) {
			if(penjaminList.length > 0) {
				if(penjaminList.indexOf(currentPenjamin) > 0) {
					$("#resep_obat_" + targetRow).parent().find("div.penjamin-container").html("<b class=\"badge badge-success obat-penjamin-notifier\"><i class=\"fa fa-check-circle\" style=\"margin-right: 5px;\"></i> Ditanggung Penjamin</b>");
				} else {
					$("#resep_obat_" + targetRow).parent().find("div.penjamin-container").html("<b class=\"badge badge-danger obat-penjamin-notifier\"><i class=\"fa fa-ban\" style=\"margin-right: 5px;\"></i> Tidak Ditanggung Penjamin</b>");
				}
			} else {
				$("#resep_obat_" + targetRow).parent().find("div.penjamin-container").html("<b class=\"badge badge-danger obat-penjamin-notifier\"><i class=\"fa fa-ban\" style=\"margin-right: 5px;\"></i> Tidak Ditanggung Penjamin</b>");			
			}
		}

		$("#txt_racikan_takar_bulat").inputmask({
			alias: 'decimal',
			rightAlign: true,
			placeholder: "0.00",
			prefix: "",
			autoGroup: false,
			digitsOptional: true
		});

		function autoResep(setter = {
			"obat": "",
			"aturan_pakai": 0,
			"keterangan": "",
			"signaKonsumsi": 0,
			"signaTakar": 0,
			"signaHari": 0,
			"pasien_penjamin_uid": ""
		}) {
			$("#table-resep tbody tr").removeClass("last-resep");
			var newRowResep = document.createElement("TR");
			$(newRowResep).addClass("last-resep");
			var newCellResepID = document.createElement("TD");
			var newCellResepObat = document.createElement("TD");
			var newCellResepJlh = document.createElement("TD");
			var newCellResepSatuan = document.createElement("TD");
			var newCellResepSigna1 = document.createElement("TD");
			var newCellResepSigna2 = document.createElement("TD");
			var newCellResepSigna3 = document.createElement("TD");
			var newCellResepPenjamin = document.createElement("TD");
			var newCellResepAksi = document.createElement("TD");

			var newObat = document.createElement("SELECT");
			$(newCellResepObat).append(newObat);

			var addAnother = load_product_resep(newObat, setter.obat, false);
			
			if(!addAnother.allow) {
				$(newCellResepObat).append(
					"<div class=\"row\" style=\"padding-top: 5px;\">" +
						"<div style=\"position: relative\" class=\"col-md-12 penjamin-container text-right\"></div>" +
						"<div class=\"col-md-7 aturan-pakai-container\"><span>Aturan Pakai</span></div>" +
						"<div class=\"col-md-5 kategori-obat-container\"><span>Kategori Obat</span><br /></div>" +
						"<div style=\"position: relative; padding-top: 5px;\" class=\"col-md-12 keterangan-container\"></div>" +
					"</div>");
				var newAturanPakai = document.createElement("SELECT");
				var dataAturanPakai = autoAturanPakai();

				$(newCellResepObat).find("div.aturan-pakai-container").append(newAturanPakai);
				$(newAturanPakai).addClass("form-control aturan-pakai");
				$(newAturanPakai).append("<option value=\"none\">Pilih Aturan Pakai</option>").select2();
				for(var aturanPakaiKey in dataAturanPakai) {
					$(newAturanPakai).append("<option " + ((dataAturanPakai[aturanPakaiKey].id == setter.aturan_pakai) ? "selected=\"selected\"" : "") + " value=\"" + dataAturanPakai[aturanPakaiKey].id + "\">" + dataAturanPakai[aturanPakaiKey].nama + "</option>")
				}

				var keteranganPerObat = document.createElement("TEXTAREA");
				$(newCellResepObat).find("div.keterangan-container").append("<span>Keterangan</span>").append(keteranganPerObat);
				$(keteranganPerObat).addClass("form-control").attr({
					"placeholder": "Keterangan per Obat"
				}).val(setter.keterangan);

				var itemData = addAnother.data;
				var parsedItemData = [];
				var obatNavigator = [];
				for(var dataKey in itemData) {
					var penjaminList = [];
					var penjaminListData = itemData[dataKey].penjamin;
					for(var penjaminKey in penjaminListData) {
						if(penjaminList.indexOf(penjaminListData[penjaminKey].penjamin.uid) < 0) {
							penjaminList.push(penjaminListData[penjaminKey].penjamin.uid);
						}
					}
					
					obatNavigator.push(itemData[dataKey].uid);
					parsedItemData.push({
						id: itemData[dataKey].uid,
						"penjamin-list": penjaminList,
						"satuan-caption": (itemData[dataKey].satuan_terkecil !== null) ? itemData[dataKey].satuan_terkecil.nama : "",
						"satuan-terkecil": (itemData[dataKey].satuan_terkecil !== null) ? itemData[dataKey].satuan_terkecil.uid : "",
						text: "<div style=\"color:" + ((itemData[dataKey].stok > 0) ? "#12a500" : "#cf0000") + ";\">" + itemData[dataKey].nama.toUpperCase() + "</div>",
						html: 	"<div class=\"select2_item_stock\">" +
									"<div style=\"color:" + ((itemData[dataKey].stok > 0) ? "#12a500" : "#cf0000") + "\">" + itemData[dataKey].nama.toUpperCase() + "</div>" +
									"<div>" + itemData[dataKey].stok + "</div>" +
								"</div>",
						title: itemData[dataKey].nama
					});
				}

				$(newObat).addClass("form-control resep-obat").select2({
					data: parsedItemData,
					placeholder: "Pilih Obat",
					selectOnClose: true,
					val: setter.obat,
					escapeMarkup: function(markup) {
						return markup;
					},
					templateResult: function(data) {
						return data.html;
					},
					templateSelection: function(data) {
						return data.text;
					}
				}).on("select2:select", function(e) {
					var data = e.params.data;
					$(this).children("[value=\""+ data['id'] + "\"]").attr({
						"data-value": data["data-value"],
						"penjamin-list": data["penjamin-list"],
						"satuan-caption": data["satuan-caption"],
						"satuan-terkecil": data["satuan-terkecil"]
					});

					//============KATEGORI OBAT
					
					if(setter.obat != "") {
						if($(newObat).val() != "none") {
							var dataKategoriPerObat = autoKategoriObat(setter.obat);
							var kategoriObatDOM = "";
							for(var kategoriObatKey in dataKategoriPerObat) {
								kategoriObatDOM += "<span class=\"badge badge-info resep-kategori-obat\">" + dataKategoriPerObat[kategoriObatKey].kategori.nama + "</span>";
							}
							$(newCellResepObat).find("div.kategori-obat-container").append(kategoriObatDOM);
						} else {
							//
						}

						var penjaminAvailable = parsedItemData[obatNavigator.indexOf(setter.obat)]['penjamin-list'];
						if(penjaminAvailable.length > 0) {
							if(penjaminAvailable.indexOf(setter.pasien_penjamin_uid) > 0) {
								$(newCellResepObat).find("div.penjamin-container").html("<b class=\"badge badge-success obat-penjamin-notifier\"><i class=\"fa fa-check-circle\" style=\"margin-right: 5px;\"></i> Ditanggung Penjamin</b>");
							} else {
								$(newCellResepObat).find("div.penjamin-container").html("<b class=\"badge badge-danger obat-penjamin-notifier\"><i class=\"fa fa-ban\" style=\"margin-right: 5px;\"></i> Tidak Ditanggung Penjamin</b>");
							}
						} else {
							$(newCellResepObat).find("div.penjamin-container").html("<b class=\"badge badge-danger obat-penjamin-notifier\"><i class=\"fa fa-ban\" style=\"margin-right: 5px;\"></i> Tidak Ditanggung Penjamin</b>");			
						}
						$(newCellResepSatuan).html(parsedItemData[obatNavigator.indexOf(setter.obat)]["satuan-caption"]);
					}
				});

				if(setter.obat != "") {
					$(newObat).val([setter.obat]).trigger("change").trigger({
						type:"select2:select",
						params: {
							data: parsedItemData
						}
					});

					$(newObat).find("option:selected").attr({
						"data-value": parsedItemData[obatNavigator.indexOf(setter.obat)]["data-value"],
						"penjamin-list": parsedItemData[obatNavigator.indexOf(setter.obat)]['penjamin-list'],
						"satuan-caption": parsedItemData[obatNavigator.indexOf(setter.obat)]["satuan-caption"],
						"satuan-terkecil": parsedItemData[obatNavigator.indexOf(setter.obat)]["satuan-terkecil"]
					});
				} else {
					$(newCellResepSatuan).html($(newObat).find("option:selected").attr("satuan-caption"));
				}

				var newJumlah = document.createElement("INPUT");
				$(newCellResepJlh).append(newJumlah);
				$(newJumlah).addClass("form-control resep_jlh_hari").inputmask({
					alias: 'decimal',
					rightAlign: true,
					placeholder: "0.00",
					prefix: "",
					autoGroup: false,
					digitsOptional: true
				}).attr({
					"placeholder": "0"
				}).val((setter.signaHari == 0) ? "" : setter.signaHari);

				var newKonsumsi = document.createElement("INPUT");
				$(newCellResepSigna1).append(newKonsumsi);
				$(newKonsumsi).addClass("form-control resep_konsumsi").attr({
					"placeholder": "0"
				}).inputmask({
					alias: 'decimal',
					rightAlign: true,
					placeholder: "0.00",
					prefix: "",
					autoGroup: false,
					digitsOptional: true
				}).val((setter.signaKonsumsi == 0) ? "" : setter.signaKonsumsi);

				$(newCellResepSigna2).html("<i class=\"fa fa-times signa-sign\"></i>");

				var newTakar = document.createElement("INPUT");
				$(newCellResepSigna3).append(newTakar);
				$(newTakar).addClass("form-control resep_takar").attr({
					"placeholder": "0"
				}).inputmask({
					alias: 'decimal',
					rightAlign: true,
					placeholder: "0.00",
					prefix: "",
					autoGroup: false,
					digitsOptional: true
				}).val((setter.signaTakar == 0) ? "" : setter.signaTakar);

				
				var newDeleteResep = document.createElement("BUTTON");
				$(newCellResepAksi).append(newDeleteResep);
				$(newDeleteResep).addClass("btn btn-sm btn-danger resep_delete").html("<i class=\"fa fa-ban\"></i>");

				$(newRowResep).append(newCellResepID);
				$(newRowResep).append(newCellResepObat);
				$(newRowResep).append(newCellResepSigna1);
				$(newRowResep).append(newCellResepSigna2);
				$(newRowResep).append(newCellResepSigna3);
				$(newRowResep).append(newCellResepJlh);
				$(newRowResep).append(newCellResepSatuan);
				$(newRowResep).append(newCellResepAksi);
				$("#table-resep").append(newRowResep);	
				
				rebaseResep();
			}
		}

		function rebaseResep() {
			$("#table-resep tbody tr").each(function(e) {
				var id = (e + 1);

				$(this).attr({
					"id": "resep_row_" + id
				});
				$(this).find("td:eq(0)").html(id);
				$(this).find("td:eq(1) select.resep-obat").attr({
					"id": "resep_obat_" + id
				});

				//load_product_resep($(this).find("td:eq(1) select.resep-obat"), "");
				if($(this).find("td:eq(1) select.resep-obat").val() != "none") {
					var penjaminAvailable = $(this).find("td:eq(1) select option:selected").attr("penjamin-list").split(",");
					checkPenjaminAvail(pasien_penjamin_uid, penjaminAvailable, id);
				}

				$(this).find("td:eq(2) input:eq(0)").attr({
					"id": "resep_signa_konsumsi_" + id
				});
				$(this).find("td:eq(4) input:eq(0)").attr({
					"id": "resep_signa_takar_" + id
				});
				$(this).find("td:eq(5) input").attr({
					"id": "resep_jlh_hari_" + id
				});
				$(this).find("td:eq(6)").attr({
					"id": "resep_satuan_" + id
				});
				$(this).find("td:eq(7) button").attr({
					"id": "resep_delete_" + id
				});
			});
		}








		
		function checkGenerateRacikan(id = 0) {
			if($(".last-racikan").length == 0) {
				autoRacikan();
			} else {
				var obat = $("#racikan_nama_" + id).val();
				var jlh_obat = $("#racikan_jumlah_" + id).inputmask("unmaskedvalue");
				var signa_konsumsi = $("#racikan_signaA_" + id).inputmask("unmaskedvalue");
				var signa_hari = $("#racikan_signaB_" + id).inputmask("unmaskedvalue");

				if(
					parseFloat(jlh_obat) > 0 &&
					parseFloat(signa_konsumsi) > 0 &&
					parseFloat(signa_hari) > 0 &&
					obat != null &&
					$("#row_racikan_" + id).hasClass("last-racikan")
				) {
					autoRacikan();
				}
			}
		}


		function autoRacikan(setter = {
			"nama": "",
			"keterangan": "",
			"signaKonsumsi": "",
			"signaTakar": "",
			"signaHari": "",
			"aturan_pakai": "",
			"item":[]
		}) {
			$("#table-resep-racikan tbody.racikan tr").removeClass("last-racikan");
			var newRacikanRow = document.createElement("TR");
			$(newRacikanRow).addClass("last-racikan racikan-master");

			var newRacikanCellID = document.createElement("TD");
			var newRacikanCellNama = document.createElement("TD");
			var newRacikanCellSignaA = document.createElement("TD");
			var newRacikanCellSignaX = document.createElement("TD");
			var newRacikanCellSignaB = document.createElement("TD");
			var newRacikanCellJlh = document.createElement("TD");
			var newRacikanCellAksi = document.createElement("TD");

			$(newRacikanCellID).addClass("master-racikan-cell");
			$(newRacikanCellNama).addClass("master-racikan-cell");
			$(newRacikanCellSignaA).addClass("master-racikan-cell");
			$(newRacikanCellSignaX).addClass("master-racikan-cell");
			$(newRacikanCellSignaB).addClass("master-racikan-cell");
			$(newRacikanCellJlh).addClass("master-racikan-cell");
			$(newRacikanCellAksi).addClass("master-racikan-cell");

			var newRacikanNama = document.createElement("INPUT");
			$(newRacikanCellNama).append(newRacikanNama);
			$(newRacikanNama).addClass("form-control").css({
				"margin-bottom": "20px"
			}).attr({
				"placeholder": "Nama Racikan"
			}).val(setter.nama);

			$(newRacikanCellNama).append(
				"<h6 style=\"padding-bottom: 10px;\">" +
					"Komposisi:" +
					"<button style=\"margin-left: 20px;\" class=\"btn btn-sm btn-info tambahKomposisi\"" +
						"<i class=\"fa fa-plus\"></i> Tambah" +
					"</button>" +
				"</h6>" +
				"<table class=\"table table-bordered komposisi-racikan largeDataType\" style=\"margin-top: 10px;\">" +
					"<thead class=\"thead-dark\">" +
						"<tr>" +
							"<th class=\"wrap_content\">No</th>" +
							"<th>Obat</th>" +
							/*"<th class=\"wrap_content\">@</th>" +*/
							/*"<th>Takaran</th>" +*/
							"<th>Kekuatan</th>" +
							"<th class=\"wrap_content\">Aksi</th>" +
						"<tr>" +
					"</thead>" +
					"<tbody class=\"komposisi-item\"></tbody>" +
				"</table>"
			);

			var newAturanPakaiRacikan = document.createElement("SELECT");
			
			var dataAturanPakai = autoAturanPakai();
			
			$(newAturanPakaiRacikan).addClass("form-control aturan-pakai");
			var newKeteranganRacikan = document.createElement("TEXTAREA");
			$(newRacikanCellNama).append("<span>Aturan Pakai</span>").append(newAturanPakaiRacikan).append("<span>Keterangan</span>").append(newKeteranganRacikan);
			$(newAturanPakaiRacikan).append("<option value=\"none\">Pilih Aturan Pakai</option>").select2();
			for(var aturanPakaiKey in dataAturanPakai) {
				$(newAturanPakaiRacikan).append("<option " + ((dataAturanPakai[aturanPakaiKey].id == setter.aturan_pakai) ? "selected=\"selected\"" : "") + " value=\"" + dataAturanPakai[aturanPakaiKey].id + "\">" + dataAturanPakai[aturanPakaiKey].nama + "</option>")
			}
			$(newKeteranganRacikan).addClass("form-control").attr({
				"placeholder": "Keterangan racikan"
			}).val(setter.keterangan);

			/*var newRacikanObat = document.createElement("SELECT");
			var newObatTakar = document.createElement("INPUT");
			$(newRacikanCellObat).append(newRacikanObat);
			var addAnother = load_product_resep(newRacikanObat, "");
			$(newRacikanCellObat).append("<br /><b>Takaran</b>");
			$(newRacikanCellObat).append(newObatTakar);
			$(newRacikanObat).addClass("form-control").select2();
			$(newObatTakar).addClass("form-control");*/

			var newRacikanSignaA = document.createElement("INPUT");
			$(newRacikanCellSignaA).append(newRacikanSignaA);
			$(newRacikanSignaA).addClass("form-control racikan_signa_a").attr({
				"placeholder": "0"
			}).val(setter.signaKonsumsi).inputmask({
				alias: 'decimal',
				rightAlign: true,
				placeholder: "0.00",
				prefix: "",
				autoGroup: false,
				digitsOptional: true
			});

			$(newRacikanCellSignaX).html("<i class=\"fa fa-times signa-sign\"></i>");

			var newRacikanSignaB = document.createElement("INPUT");
			$(newRacikanCellSignaB).append(newRacikanSignaB);
			$(newRacikanSignaB).addClass("form-control racikan_signa_b").attr({
				"placeholder": "0"
			}).val(setter.signaTakar).inputmask({
				alias: 'decimal',
				rightAlign: true,
				placeholder: "0.00",
				prefix: "",
				autoGroup: false,
				digitsOptional: true
			});

			var newRacikanJlh = document.createElement("INPUT");
			$(newRacikanCellJlh).append(newRacikanJlh);
			$(newRacikanJlh).addClass("form-control racikan_signa_jlh").attr({
				"placeholder": "0"
			}).val(setter.signaHari).inputmask({
				alias: 'decimal',
				rightAlign: true,
				placeholder: "0.00",
				prefix: "",
				autoGroup: false,
				digitsOptional: true
			});

			var newRacikanDelete = document.createElement("BUTTON");
			$(newRacikanCellAksi).append(newRacikanDelete);
			$(newRacikanDelete).addClass("btn btn-danger btn-sm btn-delete-racikan").html("<i class=\"fa fa-ban\"></i>");

			$(newRacikanRow).append(newRacikanCellID);
			$(newRacikanRow).append(newRacikanCellNama);
			$(newRacikanRow).append(newRacikanCellSignaA);
			$(newRacikanRow).append(newRacikanCellSignaX);
			$(newRacikanRow).append(newRacikanCellSignaB);
			$(newRacikanRow).append(newRacikanCellJlh);
			$(newRacikanRow).append(newRacikanCellAksi);

			$("#table-resep-racikan tbody.racikan").append(newRacikanRow);
			rebaseRacikan();
		}

		function rebaseRacikan() {
			$("#table-resep-racikan > tbody.racikan > tr").each(function(e) {
				var id = (e + 1);

				$(this).attr({
					"id": "row_racikan_" + id
				});

				$(this).find("td:eq(0)").html(id);

				$(this).find("td:eq(1) input").attr({
					"id": "racikan_nama_" + id
				});
				if($(this).find("td:eq(1) input") == "") {
					$(this).find("td:eq(1) input").val("RACIKAN " + id);
				}

				$(this).find("td:eq(1) table").attr({
					"id": "komposisi_" + id
				});

				$(this).find("td:eq(1) button.tambahKomposisi").attr({
					"id": "tambah_komposisi_" + id
				});

				$(this).find("td:eq(2) input").attr({
					"id": "racikan_signaA_" + id
				});

				$(this).find("td:eq(4) input").attr({
					"id": "racikan_signaB_" + id
				});

				$(this).find("td:eq(5) input").attr({
					"id": "racikan_jumlah_" + id
				});

				$(this).find("td:eq(6) button").attr({
					"id": "racikan_delete_" + id
				});
			});
		}


		function autoKomposisi(id, setter = {}) {
			if(setter.obat != undefined || $("#komposisi_" + id + " tbody tr").length == 0 || $("#komposisi_" + id + " tbody tr:last-child td:eq(1)").html() != "") {
				var newKomposisiRow = document.createElement("TR");
				$(newKomposisiRow).addClass("komposisi-row");

				var newKomposisiCellID = document.createElement("TD");
				var newKomposisiCellObat = document.createElement("TD");
				//\var newKomposisiCellJumlah = document.createElement("TD");
				var newKomposisiCellSatuan = document.createElement("TD");
				var newKomposisiCellAksi = document.createElement("TD");



				var newKomposisiEdit = document.createElement("BUTTON");
				$(newKomposisiEdit).addClass("btn btn-sm btn-info btn_edit_komposisi").html("<i class=\"fa fa-pencil-alt\"></i>");

				var newKomposisiDelete = document.createElement("BUTTON");
				$(newKomposisiDelete).addClass("btn btn-sm btn-danger btn_delete_komposisi").html("<i class=\"fa fa-ban\"></i>");

				$(newKomposisiCellAksi).append("<div class=\"btn-group\" role=\"group\" aria-label=\"Basic example\"></div>");
				$(newKomposisiCellAksi).find("div").append(newKomposisiEdit);
				$(newKomposisiCellAksi).find("div").append(newKomposisiDelete);

				$(newKomposisiRow).append(newKomposisiCellID);
				$(newKomposisiRow).append(newKomposisiCellObat);
				//$(newKomposisiRow).append(newKomposisiCellJumlah);
				$(newKomposisiRow).append(newKomposisiCellSatuan);
				$(newKomposisiRow).append(newKomposisiCellAksi);

				$("#komposisi_" + id + " tbody").append(newKomposisiRow);
				
				/*if($("#komposisi_" + id + " tbody tr").length == 1) {
					//autoModal
					prepareModal(id);
				}*/
				if(setter.obat != undefined) {
					$(newKomposisiCellObat).attr({
						"uid-obat" : setter.obat
					}).html(setter.obat_detail.nama.toUpperCase());

					//$(newKomposisiCellJumlah).html(setter.ratio);
					$(newKomposisiCellSatuan).html(setter.satuan);
				} else {
					prepareModal(id);
				}

				rebaseKomposisi(id);
			}
		}

		function rebaseKomposisi(id) {
			$("#komposisi_" + id + " tbody tr").each(function(e) {
				var cid = (e + 1);

				$(this).attr({
					"id": "single_komposisi_" + cid
				});

				$(this).find("td:eq(0)").html(cid);
				$(this).find("td:eq(1)").attr({
					"id": "obat_komposisi_" + id + "_" + cid
				});
				/*$(this).find("td:eq(2)").attr({
					"id": "jlh_komposisi_" + id + "_" + cid
				});*/
				$(this).find("td:eq(2)").attr({
					"id": "takar_komposisi_" + id + "_" + cid
				});
				$(this).find("td:eq(3) button:eq(0)").attr({
					"id": "button_edit_komposisi_" + id + "_" + cid
				});

				$(this).find("td:eq(3) button:eq(1)").attr({
					"id": "button_delete_komposisi_" + id + "_" + cid
				});
			});
		}

		function prepareModal(id, setData = {
			obat: "",
			jlh: "",
			takarBulat: 1,
			takarDesimal: "",
			kekuatan: ""
		}) {
			$("#form-editor-racikan").modal("show");
			$("#modal-large-title").html($("#racikan_nama_" + id).val());

			//$("#txt_racikan_jlh").val(setData.jlh);
			//$("#txt_racikan_takar").val(setData.takar);
			$("#txt_racikan_takar").val(setData.takarDesimal);
			$("#txt_racikan_takar_bulat").val(setData.takarBulat);
			$("#txt_racikan_kekuatan").val(setData.kekuatan);

			var modalProduct = load_product_resep($("#txt_racikan_obat"), setData.obat, false);
			var itemData = modalProduct.data;
				var parsedItemData = [];
				for(var dataKey in itemData) {
					var penjaminList = [];
					var penjaminListData = itemData[dataKey].penjamin;
					for(var penjaminKey in penjaminListData) {
						if(penjaminList.indexOf(penjaminListData[penjaminKey].penjamin.uid) < 0) {
							penjaminList.push(penjaminListData[penjaminKey].penjamin.uid);
						}
					}

					console.log(itemData[dataKey]);

					parsedItemData.push({
						id: itemData[dataKey].uid,
						"penjamin-list": penjaminList,
						"satuan-caption": (itemData[dataKey].satuan_terkecil != undefined) ? itemData[dataKey].satuan_terkecil.nama : "",
						"satuan-terkecil": (itemData[dataKey].satuan_terkecil != undefined) ? itemData[dataKey].satuan_terkecil.uid : "",
						text: "<div style=\"color:" + ((itemData[dataKey].stok > 0) ? "#12a500" : "#cf0000") + ";\">" + itemData[dataKey].nama.toUpperCase() + "</div>",
						html: 	"<div class=\"select2_item_stock\">" +
									"<div style=\"color:" + ((itemData[dataKey].stok > 0) ? "#12a500" : "#cf0000") + "\">" + itemData[dataKey].nama.toUpperCase() + "</div>" +
									"<div>" + itemData[dataKey].stok + "</div>" +
								"</div>",
						title: itemData[dataKey].nama
					});
				}

				$("#txt_racikan_obat").addClass("form-control resep-obat").select2({
					data: parsedItemData,
					placeholder: "Pilih Obat",
					selectOnClose: true,
					escapeMarkup: function(markup) {
						return markup;
					},
					templateResult: function(data) {
						return data.html;
					},
					templateSelection: function(data) {
						return data.text;
					}
				}).val(setData.obat).trigger("change").on("select2:select", function(e) {
					var data = e.params.data;
					$(this).children("[value=\""+ data['id'] + "\"]").attr({
						"data-value": data["data-value"],
						"penjamin-list": data["penjamin-list"],
						"satuan-caption": data["satuan-caption"],
						"satuan-terkecil": data["satuan-terkecil"]
					});
				});
		}

		$("#txt_racikan_obat").select2();
		/*$("#txt_racikan_jlh").inputmask({
			alias: 'decimal',
			rightAlign: true,
			placeholder: "0.00",
			prefix: "",
			autoGroup: false,
			digitsOptional: true
		});*/

		var currentRacikID = 1;
		var currentKomposisiID = $("#komposisi_" + currentRacikID + " tbody tr").length;
		var komposisiMode = "add";

		$("body").on("click", ".btn_edit_komposisi", function() {
			var id = $(this).attr("id").split("_");
			var thisID = id[id.length - 1];
			var Pid = id[id.length - 2];
			

			prepareModal(Pid, {
				obat: $("#obat_komposisi_" + Pid + "_" + thisID).attr("uid-obat"),
				//jlh: $("#jlh_komposisi_" + Pid + "_" + thisID).html(),
				takarBulat: $("#takar_komposisi_" + Pid + "_" + thisID).find("b").html(),
				takarDesimal: $("#takar_komposisi_" + Pid + "_" + thisID).find("sub").html(),
				kekuatan: $("#takar_komposisi_" + Pid + "_" + thisID).find("h6").html()
			});

			currentKomposisiID = thisID;
			currentRacikID = Pid;
		});

		$("body").on("click", ".btn-delete-racikan", function() {
			var id = $(this).attr("id").split("_");
			var thisID = id[id.length - 1];
			$("#row_racikan_" + thisID).remove();
			rebaseRacikan();
		});

		$("body").on("click", ".btn_delete_komposisi", function(){
			var id = $(this).attr("id").split("_");
			var thisID = id[id.length - 1];
			var Pid = id[id.length - 2];

			$("#single_komposisi_" + thisID).remove();
			rebaseKomposisi(Pid);
			return false;
		});

		$("body").on("click", ".tambahKomposisi", function() {
			var id = $(this).attr("id").split("_");
			id = id[id.length - 1];
			currentRacikID = id;
			currentKomposisiID = $("#komposisi_" + currentRacikID + " tbody tr").length + 1;

			autoKomposisi(id);
		});

		$("body").on("click", "#btnSubmitKomposisi", function() {
			var infoPenjamin = "";
			if($("#txt_racikan_obat").find("option:selected").attr("penjamin-list") !== undefined) {
				var penjaminCheck = $("#txt_racikan_obat").find("option:selected").attr("penjamin-list").split(",");
				if(penjaminCheck.length > 0) {
					if(penjaminCheck.indexOf(pasien_penjamin_uid) > 0) {
						infoPenjamin = "<b class=\"badge badge-success\"><i class=\"fa fa-check-circle\" style=\"margin-right: 5px;\"></i> Ditanggung Penjamin</b>";
					} else {
						infoPenjamin = "<b class=\"badge badge-danger\"><i class=\"fa fa-ban\" style=\"margin-right: 5px;\"></i> Tidak Ditanggung Penjamin</b>";
					}
				} else {
					infoPenjamin = "<b class=\"badge badge-danger\"><i class=\"fa fa-ban\" style=\"margin-right: 5px;\"></i> Tidak Ditanggung Penjamin</b>";
				}

				$("#obat_komposisi_" + currentRacikID + "_" + currentKomposisiID)
					.html($("#txt_racikan_obat").find("option:selected").text() + infoPenjamin)
					.attr({
						"uid-obat": $("#txt_racikan_obat").val()
					});
			}

			//$("#jlh_komposisi_" + currentRacikID + "_" + currentKomposisiID).html($("#txt_racikan_jlh").val());
			$("#takar_komposisi_" + currentRacikID + "_" + currentKomposisiID).html("<b style=\"font-size: 15pt; display: none\">" + $("#txt_racikan_takar_bulat").val() + "</b><sub nilaiExact=\"" + eval($("#txt_racikan_takar").val()) + "\">" + $("#txt_racikan_takar").val() + "</sub><h6>" + $("#txt_racikan_kekuatan").val() + "</h6>");
			//if($("#txt_racikan_jlh").val() != "" && $("#txt_racikan_takar").val()) {
				$("#form-editor-racikan").modal("hide");	
			//}
		});

		$("body").on("keyup", ".racikan_signa_a", function() {
			var id = $(this).attr("id").split("_");
			id = id[id.length - 1];
			checkGenerateRacikan(id);
		});

		$("body").on("keyup", ".racikan_signa_b", function() {
			var id = $(this).attr("id").split("_");
			id = id[id.length - 1];
			checkGenerateRacikan(id);
		});

		$("body").on("keyup", ".racikan_signa_jlh", function() {
			var id = $(this).attr("id").split("_");
			id = id[id.length - 1];
			checkGenerateRacikan(id);
		});
		//===========================================================================
		$("body").on("keyup", ".resep_konsumsi", function() {
			var id = $(this).attr("id").split("_");
			id = id[id.length - 1];
			checkGenerateResep(id);
		});

		$("body").on("keyup", ".resep_takar", function() {
			var id = $(this).attr("id").split("_");
			id = id[id.length - 1];
			checkGenerateResep(id);
		});

		$("body").on("keyup", ".resep_jlh_hari", function() {
			var id = $(this).attr("id").split("_");
			id = id[id.length - 1];
			checkGenerateResep(id);
		});
		
		$("body").on("select2:select", ".resep-obat", function(e) {
			var data = e.params.data;
			$(this).children("[value=\""+ data['id'] + "\"]").attr({
				"data-value": data["data-value"],
				"penjamin-list": data["penjamin-list"],
				"satuan-caption": data["satuan-caption"],
				"satuan-terkecil": data["satuan-terkecil"]
			});

			var id = $(this).attr("id").split("_");
			id = id[id.length - 1];

			if($(this).val() != "none") {
				var dataKategoriPerObat = autoKategoriObat($(this).val());
				var kategoriObatDOM = "";
				for(var kategoriObatKey in dataKategoriPerObat) {
					kategoriObatDOM += "<span class=\"badge badge-info resep-kategori-obat\">" + dataKategoriPerObat[kategoriObatKey].kategori.nama + "</span>";
				}
				$("#resep_row_" + id).find("td:eq(1) div.kategori-obat-container").html("<span>Kategori Obat</span><br />" + kategoriObatDOM);

				var penjaminAvailable = $(this).find("option:selected").attr("penjamin-list").split(",");
				checkPenjaminAvail(pasien_penjamin_uid, penjaminAvailable, id);

				var satuanCaption = $(this).find("option:selected").attr("satuan-caption");
				$("#resep_satuan_" + id).html(satuanCaption);
				rebaseResep();
			} else {
				$("#resep_obat_" + id).parent().find("div.penjamin-container").html("");
				$("#resep_satuan_" + id).html("");
				$("#resep_row_" + id).find("td:eq(1) div.kategori-obat-container").html("<span>Kategori Obat</span><br />");
			}
		});

		$("body").on("click", ".resep_delete", function() {
			var id = $(this).attr("id").split("_");
			id = id[id.length - 1];

			if(!$("#resep_row_" + id).hasClass("last-resep")) {
				$("#resep_row_" + id).remove();
			}

			rebaseResep();
			//$("#table-resep tbody tr").each(function(e));
		});

		function populateAllData() {
			//PREPARE FOR SAVE DATA
			var keluhanUtamaData = editorKeluhanUtamaData.getData();
			var keluhanTambahanData = editorKeluhanTambahanData.getData();
			var tekananDarah = $("#txt_tanda_vital_td").val();
			var nadi = $("#txt_tanda_vital_n").val();
			var suhu = $("#txt_tanda_vital_s").val();
			var pernafasan = $("#txt_tanda_vital_rr").val();
			var beratBadan = $("#txt_berat_badan").val();
			var tinggiBadan = $("#txt_tinggi_badan").val();
			var lingkarLengan = $("#txt_lingkar_lengan").val();
			var pemeriksaanFisikData = editorPeriksaFisikData.getData();
			var icd10kerja = $("#txt_icd_10_kerja").val();
			var icd10Banding = $("#txt_icd_10_banding").val();
			var icd10KerjaData = editorKerja.getData();
			var icd10BandingData = editorBanding.getData();
			var planningData = editorPlanning.getData();

			var tindakan = [];
			$("#table-tindakan tbody tr").each(function() {
				var tindakanItem = $(this).find("td:eq(1)").attr("set-tindakan");
				var pilihanPenjamin = $(this).find("td:eq(2) select").val();
				tindakan.push({
					"item": tindakanItem,
					"itemName": $(this).find("td:eq(1)").html(),
					"penjamin": pilihanPenjamin,
					"penjaminName": $(this).find("td:eq(2) select option:selected").text()
				});
			});

			var resep = [];
			$("#table-resep tbody tr").each(function() {
				var obat = $(this).find("td:eq(1) select").val();
				var signaKonsumsi = $(this).find("td:eq(2) input").inputmask("unmaskedvalue");
				var signaTakar = $(this).find("td:eq(4) input").inputmask("unmaskedvalue");
				var signaHari = $(this).find("td:eq(5) input").inputmask("unmaskedvalue");
				var penjamin = $(this).find("td:eq(6) select").val();

				resep.push({
					"obat": obat,
					"signaKonsumsi": signaKonsumsi,
					"signaTakar": signaTakar,
					"signaHari": signaHari,
					"penjamin": penjamin
				});
			});

			var keteranganResep = editorKeteranganResep.getData();
		}

		$("#txt_tanda_vital_td").inputmask({
			alias: 'decimal',
			rightAlign: true,
			placeholder: "0.00",
			prefix: "",
			autoGroup: false,
			digitsOptional: true
		});

		$("#txt_tanda_vital_n").inputmask({
			alias: 'decimal',
			rightAlign: true,
			placeholder: "0.00",
			prefix: "",
			autoGroup: false,
			digitsOptional: true
		});

		$("#txt_tanda_vital_s").inputmask({
			alias: 'decimal',
			rightAlign: true,
			placeholder: "0.00",
			prefix: "",
			autoGroup: false,
			digitsOptional: true
		});

		$("#txt_tanda_vital_rr").inputmask({
			alias: 'decimal',
			rightAlign: true,
			placeholder: "0.00",
			prefix: "",
			autoGroup: false,
			digitsOptional: true
		});

		$("#txt_berat_badan").inputmask({
			alias: 'decimal',
			rightAlign: true,
			placeholder: "0.00",
			prefix: "",
			autoGroup: false,
			digitsOptional: true
		});

		$("#txt_tinggi_badan").inputmask({
			alias: 'decimal',
			rightAlign: true,
			placeholder: "0.00",
			prefix: "",
			autoGroup: false,
			digitsOptional: true
		});

		$("#txt_lingkar_lengan").inputmask({
			alias: 'decimal',
			rightAlign: true,
			placeholder: "0.00",
			prefix: "",
			autoGroup: false,
			digitsOptional: true
		});

		$("body").on("click", "#btnSelesai", function() {
			var kunjungan = antrianData.kunjungan;
			var antrian = UID;
			var penjamin = antrianData.penjamin;
			var pasien = antrianData.pasien;
			var poli = antrianData.departemen;

			//POLI FORM
			var keluhanUtamaData = editorKeluhanUtamaData.getData();
			var keluhanTambahanData = editorKeluhanTambahanData.getData();
			var tekananDarah = $("#txt_tanda_vital_td").inputmask("unmaskedvalue");
			var nadi = $("#txt_tanda_vital_n").inputmask("unmaskedvalue");
			var suhu = $("#txt_tanda_vital_s").inputmask("unmaskedvalue");
			var pernafasan = $("#txt_tanda_vital_rr").inputmask("unmaskedvalue");
			var beratBadan = $("#txt_berat_badan").inputmask("unmaskedvalue");
			var tinggiBadan = $("#txt_tinggi_badan").inputmask("unmaskedvalue");
			var lingkarLengan = $("#txt_lingkar_lengan").inputmask("unmaskedvalue");
			var pemeriksaanFisikData = editorPeriksaFisikData.getData();
			
			/*var icd10Kerja = $("#txt_icd_10_kerja").val();
			var icd10Banding = $("#txt_icd_10_banding").val();*/
			
			var diagnosaKerjaData = editorKerja.getData();
			var diagnosaBandingData = editorBanding.getData();
			var planningData = editorPlanning.getData();

			var tindakan = [];
			$("#table-tindakan tbody tr").each(function() {
				var tindakanItem = $(this).find("td:eq(1)").attr("set-tindakan");
				var pilihanPenjamin = $(this).find("td:eq(2) select").val();
				tindakan.push({
					"kunjungan": kunjungan,
					"antrian": antrian,
					"pasien": pasien,
					"kelas": $(this).find("td:eq(1)").attr("kelas"),
					"poli": poli,
					"item": tindakanItem,
					"itemName": $(this).find("td:eq(1)").html(),
					"penjamin": pilihanPenjamin,
					"penjaminName": $(this).find("td:eq(2) select option:selected").text()
				});
			});

			var resep = [];
			$("#table-resep tbody tr").each(function() {
				var obat = $(this).find("td:eq(1) select.resep-obat").val();
				var aturanPakai = $(this).find("td:eq(1) select.aturan-pakai").val();
				var keteranganPerObat = $(this).find("td:eq(1) textarea").val();
				var signaKonsumsi = $(this).find("td:eq(2) input").inputmask("unmaskedvalue");
				var signaTakar = $(this).find("td:eq(4) input").inputmask("unmaskedvalue");
				var signaHari = $(this).find("td:eq(5) input").inputmask("unmaskedvalue");
				//var penjamin = $(this).find("td:eq(6) select").val();
				if(
					obat != undefined &&
					obat != "none" &&
					obat != "" &&

					parseFloat(signaKonsumsi) > 0 &&
					parseFloat(signaTakar) > 0 &&
					parseFloat(signaHari) > 0

				) {
					resep.push({
						"obat": obat,
						"aturanPakai": aturanPakai,
						"keteranganPerObat": keteranganPerObat,
						"signaKonsumsi": signaKonsumsi,
						"signaTakar": signaTakar,
						"signaHari": signaHari
					});
				}
			});

			var keteranganResep = editorKeteranganResep.getData();
			var keteranganRacikan = editorKeteranganResepRacikan.getData();
			
			var racikan = [];
			$("#resep-racikan tbody.racikan tr.racikan-master").each(function() {
				var masterRacikanRow = $(this);
				var dataRacikan = {
					"nama": "",
					"item": [],
					"keterangan": "",
					"signaKonsumsi": 0,
					"signaTakar": 0,
					"signaHari": 0,
					"aturanPakai": 0
				};

				dataRacikan.nama = masterRacikanRow.find("td.master-racikan-cell:eq(1) input").val();
				dataRacikan.aturanPakai = masterRacikanRow.find("td.master-racikan-cell:eq(1) select").val();
				dataRacikan.keterangan = masterRacikanRow.find("td.master-racikan-cell:eq(1) textarea").val();
				dataRacikan.signaKonsumsi = parseInt(masterRacikanRow.find("td.master-racikan-cell:eq(2) input").inputmask("unmaskedvalue"));
				dataRacikan.signaTakar = parseInt(masterRacikanRow.find("td.master-racikan-cell:eq(4) input").inputmask("unmaskedvalue"));
				dataRacikan.signaHari = parseInt(masterRacikanRow.find("td.master-racikan-cell:eq(5) input").inputmask("unmaskedvalue"));




				$(this).find("td:eq(1) table.komposisi-racikan tbody.komposisi-item tr.komposisi-row").each(function() {
					var obat = $(this).find("td:eq(1)").attr("uid-obat");
					//var qty = $(this).find("td:eq(2)").html();
					var takaranBulat = $(this).find("td:eq(2) b").html();
					var takaranDecimal = $(this).find("td:eq(2) sub").attr("nilaiExact");
					var takaranDecimalText = $(this).find("td:eq(2) sub").html();
					var takaranKekuatan = $(this).find("td:eq(2) h6").html();
					var takaran = parseFloat(takaranBulat) + parseFloat(takaranDecimal);
					
					if(obat != undefined) {
						dataRacikan.item.push({
							"obat": obat,
							//"qty": qty,
							"takaranBulat": takaranBulat,
							"takaranDecimal": takaranDecimal,
							"takaranDecimalText": takaranDecimalText,
							"takaran": (isNaN(takaran) ? 1 : takaran),
							"kekuatan": takaranKekuatan
						});
					}
				});

				if(
					dataRacikan.nama != "" &&
					dataRacikan.item.length > 0 &&

					dataRacikan.signaKonsumsi > 0 &&
					dataRacikan.signaTakar > 0 &&
					dataRacikan.signaHari > 0
				) {
					racikan.push(dataRacikan);
				}
			});

			var formData = {
				request: "update_asesmen_medis",
				kunjungan: kunjungan,
				antrian: antrian,
				penjamin: penjamin,
				pasien: pasien,
				poli: poli,
				//==============================
				keluhan_utama: keluhanUtamaData,
				keluhan_tambahan: keluhanTambahanData,
				tekanan_darah: parseFloat(tekananDarah),
				nadi: parseFloat(nadi),
				suhu: parseFloat(suhu),
				pernafasan: parseFloat(pernafasan),
				berat_badan: parseFloat(beratBadan),
				tinggi_badan: parseFloat(tinggiBadan),
				lingkar_lengan_atas: parseFloat(lingkarLengan),
				pemeriksaan_fisik: pemeriksaanFisikData,
				//icd10_kerja: parseInt(icd10Kerja),
				icd10_kerja: selectedICD10Kerja,
				diagnosa_kerja: diagnosaKerjaData,
				//icd10_banding: parseInt(icd10Banding),
				icd10_banding: selectedICD10Banding,
				diagnosa_banding: diagnosaBandingData,
				planning: planningData,
				//==============================
				tindakan:tindakan,
				resep: resep,
				keteranganResep: keteranganResep,
				keteranganRacikan: keteranganRacikan,
				racikan: racikan
			};

			Swal.fire({
				title: 'Selesai isi asesmen rawat?',
				showDenyButton: true,
				//showCancelButton: true,
				confirmButtonText: `Ya`,
				denyButtonText: `Belum`,
			}).then((result) => {
				if (result.isConfirmed) {
					//Validation
					$.ajax({
						async: false,
						url: __HOSTAPI__ + "/Asesmen",
						data: formData,
						beforeSend: function(request) {
							request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
						},
						type: "POST",
						success: function(response) {
							
							//console.log(response);

							if(response.response_package.response_result > 0) {
								orderRadiologi(UID, listTindakanRadiologiTerpilih, listTindakanRadiologiDihapus);
								listTindakanRadiologiDihapus = [];		//set back to empty
								
								notification ("success", "Asesmen Berhasil Disimpan", 3000, "hasil_tambah_dev");
								//location.href = __HOSTNAME__ + "/rawat_jalan/dokter";
							} else {
								notification ("danger", response.response_package, 3000, "hasil_tambah_dev");
							}
						},
						error: function(response) {
							console.clear();
							console.log(response);
						}
					});
					
					//orderRadiologi(UID, listTindakanRadiologiTerpilih, listTindakanRadiologiDihapus);
					//listTindakanRadiologiDihapus = [];		//set back to empty
				} else if (result.isDenied) {
					//Swal.fire('Changes are not saved', '', 'info')
				}
			});
			return false;
		});

		
		loadRadiologiTindakan('tindakan-radiologi');
		
		$("#tindakan-radiologi").select2({});
		
		function loadRadiologiTindakan(selector){
			var radiologiTindakan;
			$.ajax({
				url: __HOSTAPI__ + "/Radiologi/tindakan",
				async:false,
				beforeSend: function(request) {
					request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
				},
				type:"GET",
				success:function(response) {
					if(response.response_package != null) {
						radiologiTindakan = response.response_package.response_data;
						if (radiologiTindakan.length > 0){
							for(i = 0; i < radiologiTindakan.length; i++){
			                    var selection = document.createElement("OPTION");
			                    $(selection).attr("value", radiologiTindakan[i].uid).html(radiologiTindakan[i].nama);
			                    $("#" + selector).append(selection);
			                }
						}
					}
				},
				error: function(response) {
					console.log(response);
				}
			});
			return radiologiTindakan;
		}

		function loadPasien(params){
			var MetaData = null;

			if (params != ""){
				$.ajax({
					async: false,
		            url:__HOSTAPI__ + "/Asesmen/asesmen-rawat-detail/" + params,
		            type: "GET",
		            beforeSend: function(request) {
		                request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
		            },
		            success: function(response){
		            	if (response.response_package != ""){
		            		MetaData = response.response_package;

		            		$.each(MetaData.pasien, function(key, item){
			                	$("#" + key).html(item);
			                });

			                $.each(MetaData.antrian, function(key, item){
			                	$("#" + key).val(item);
			                });

							if (MetaData.pasien.id_jenkel == 2){
								$(".wanita").attr("hidden",true);
							} else {
								$(".pria").attr("hidden",true);
							}

							if (MetaData.asesmen_rawat != ""){
			                	$.each(MetaData.asesmen_rawat, function(key, item){
				                	$("#" + key).val(item);
				                	/*alert("#txt_" + key);
				                	alert(item);*/
				                	$("#txt_" + key).val(item);
				                	checkedRadio(key, item);
				                	checkedCheckbox(key, item);
				                });
			                }
		            	}
		            },
		            error: function(response) {
		                console.log(response);
		            }
		        });
			}

			return MetaData;
		}

		function checkedRadio(name, value){
			var $radios = $('input:radio[name='+ name +']');

			if ($radios != ""){
				if($radios.is(':checked') === false) {
					if (value != null && value != ""){
		       	 		$radios.filter('[value="'+ value +'"]').prop('checked', true);
		    		}
		    	}
			}
		}

		function checkedCheckbox(name, value){
			var $check = $('input:checkbox[name='+ name +']');

		    if ($check != ""){
			    if($check.is(':checked') === false) {
			    	if (value != null && value != ""){
			    		$check.filter('[value="'+ value +'"]').prop('checked', true);
			    	}
			    }
			}		 
		}

		function loadDataPenjamin(){
			let dataPenjamin;

			$.ajax({
				url: __HOSTAPI__ + "/Penjamin/penjamin",
				async:false,
				beforeSend: function(request) {
					request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
				},
				type:"GET",
				success:function(response) {
					if(response.response_package != null) {
						dataPenjamin = response.response_package.response_data;
					}
				},
				error: function(response) {
					console.log(response);
				}
			});

			return dataPenjamin;
		}









		/*========================= CPPT ==========================*/

		load_cppt(pasien_uid);

		function load_cppt(pasien, dari = "", sampai = "") {
			$.ajax({
				url: __HOSTAPI__ + "/CPPT/semua/" + pasien,
				async:false,
				beforeSend: function(request) {
					request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
				},
				type:"GET",
				success:function(response) {
					$("#cppt_loader").html("");
					var data = response.response_package.response_data;
					for(var a = 0; a < data.length; a++) {
						$.ajax({
							url: __HOSTNAME__ + "/pages/rawat_jalan/dokter/cppt-single.php",
							async:false,
							data:{
								setter:data[a]
							},
							beforeSend: function(request) {
								request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
							},
							type:"POST",
							success:function(response_html) {
								$("#cppt_loader").append(response_html);
							},
							error: function(response_html) {
								console.log(response_html);
							}
						});
					}
				},
				error: function(response) {
					console.log(response);
				}
			});
		}



		/*=========================================================*/





















		
		/*========================= RADIOLOGI SCRIPT AREA START ==========================*/
		//load order with returning selectedTindakan
		function loadRadiologiOrder(uid_antrian){
			let dataOrder;
			let selectedTindakan = {};

			$.ajax({
				url: __HOSTAPI__ + "/Radiologi/get-radiologi-order/" + uid_antrian,
				async:false,
				beforeSend: function(request) {
					request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
				},
				type:"GET",
				success:function(response) {
					if(response.response_package != null) {
						dataOrder = response.response_package.detail_order;

						let no_urut = 1;
						$.each(dataOrder, function(key_order, item_order){
							let status_disabled = "disabled";

							//check if order data has inserted, the delete button will disabled
							if (item_order.keterangan == null && item_order.kesimpulan == null){
								status_disabled = "";
							}

							let html = "<tr>\
									<td class='no_urut_rad'>"+ no_urut +"</td>\
									<td>"+ item_order.tindakan +"</td>\
									<td>"+ item_order.penjamin +"</td>\
									<td><button class='btn btn-danger btn-sm btnHapusTindakanRad' 					data-uid='"+ item_order.uid_tindakan +"' \
										data-nama='" + item_order.tindakan +"' "+ status_disabled +">\
										<i class='fa fa-trash'></button></td>\
								</tr>";

							$("#table_tindakan_radiologi tbody").append(html);
							no_urut++;

							$('#tindakan_radiologi').val('').trigger('change');
							selectedTindakan[item_order.uid_tindakan] = item_order.uid_penjamin;
							$("#tindakan_radiologi option[value='"+ item_order.uid_tindakan +"']").remove();
						});

					}

				},
				error: function(response) {
					console.log(response);
				}
			});
			
			return selectedTindakan;
		}

		function loadRadiologiTindakan(){
			var radiologiTindakan;

			$("#tindakan_radiologi").empty();
			$("#tindakan_radiologi").append("<option disabled selected value=''>Pilih Tindakan Radiologi</option>");

			$.ajax({
				url: __HOSTAPI__ + "/Radiologi/get_tindakan_for_dokter",
				async:false,
				beforeSend: function(request) {
					request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
				},
				type:"GET",
				success:function(response) {
					if(response.response_package != null) {
						radiologiTindakan = response.response_package.response_data;
						if (radiologiTindakan.length > 0){
							for(i = 0; i < radiologiTindakan.length; i++){
								
			                    var selection = document.createElement("OPTION");
			                    $(selection).attr("value", radiologiTindakan[i].uid).html(radiologiTindakan[i].nama);
			                    $("#tindakan_radiologi").append(selection);
			                }
						}
					}

					$("#tindakan_radiologi").select2({});
				},
				error: function(response) {
					console.log(response);
				}
			});

			return radiologiTindakan;
		}

		
		//initiate radiologi tindakan data
		var listRadiologiTindakan = loadRadiologiTindakan();

		//variable for collect selected Tindakan
		var listTindakanRadiologiTerpilih = loadRadiologiOrder(UID);

		//variable for collect deleted Tindakan
		var listTindakanRadiologiDihapus = [];

		//variable for load penjamin
		var listPenjamin = loadDataPenjamin();
		
		//this variable will be used in action tambahTindakan; default is uid penjamin umum
		var uid_penjamin_tindakan_rad = __UIDPENJAMINUMUM__;

		$("#tindakan_radiologi").on('select2:select', function(){
			let uidTindakanRad = $(this).val();

			$("#radiologi_tindakan_notifier").html("");
			if (pasien_penjamin_uid !== __UIDPENJAMINUMUM__){
				uid_penjamin_tindakan_rad = __UIDPENJAMINUMUM__;

				let html = '<p><b class="badge badge-warning"><i class="fa fa-exclamation-circle" style="margin-right: 5px;"></i>Akan ditanggung Penjamin Umum</b></p>';

				$.each(listRadiologiTindakan, function(key_tindakan, item_tindakan){
					let statusLoop = true;
					
					if (item_tindakan.uid === uidTindakanRad){

						$.each(item_tindakan.harga, function(key_harga, item_harga){

							if (pasien_penjamin_uid == item_harga.penjamin){
								html = '<p><b class="badge badge-success"><i class="fa fa-check-circle" style="margin-right: 5px;"></i> Ditanggung Penjamin</b></p>';
								
								//setter jika dijamin
								uid_penjamin_tindakan_rad = pasien_penjamin_uid; 
								statusLoop = false;	
								return false;
							}

							});

							if (statusLoop === false){
							return false;
						}
						
					}

				});

				$("#radiologi_tindakan_notifier").html(html);
			}

		});
		
		$("#btnTambahTindakanRadiologi").click(function(){
			let uidTindakanRad = $("#tindakan_radiologi").val();
			let dataTindakan = $("#tindakan_radiologi").select2('data');
			let namaPenjamin;

			$.each(listPenjamin, function(key, item){
				if (item.uid == uid_penjamin_tindakan_rad){
					namaPenjamin = item.nama;

					return false;
				}
			});

			let html = "<tr>\
					<td class='no_urut_rad'></td>\
					<td>"+ dataTindakan[0].text +"</td>\
					<td>"+ namaPenjamin +"</td>\
					<td><button class='btn btn-danger btn-sm btnHapusTindakanRad'><i class='fa fa-trash'></button></td>\
				</tr>";

			$("#table_tindakan_radiologi tbody").append(html);

			$('#tindakan_radiologi').val('').trigger('change');
			listTindakanRadiologiTerpilih[uidTindakanRad] = uid_penjamin_tindakan_rad;
			$("#tindakan_radiologi option[value='"+ uidTindakanRad +"']").remove();

			setNomorUrut('table_tindakan_radiologi', 'no_urut_rad');
		});
		
		$("#table_tindakan_radiologi").on('click', '.btnHapusTindakanRad', function(){
			let uid_tindakan = $(this).data("uid");
			let nama_tindakan = $(this).data("nama");

			delete listTindakanRadiologiTerpilih[uid_tindakan];
			listTindakanRadiologiDihapus.push(uid_tindakan);
			$(this).parent().parent().remove();

			//set back to list
			$("#tindakan_radiologi").append("<option value='"+ uid_tindakan +"'>"+ nama_tindakan +"</option>");

			setNomorUrut('table_tindakan_radiologi', 'no_urut_rad');
		});

		function orderRadiologi(uid_antrian, listTindakan, listTindakanDihapus){
			let formData = {
				'request' : 'add-order-radiologi',
				'uid_antrian' : uid_antrian,
				'listTindakan' : listTindakan,
				'listTindakanDihapus': listTindakanDihapus
			}

			$.ajax({
				async: false,
				url: __HOSTAPI__ + "/Radiologi",
				data: formData,
				beforeSend: function(request) {
					request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
				},
				type: "POST",
				success: function(response) {
					console.log(response);
					// if(response.response_package.response_result > 0) {
					// 	notification ("success", "Asesmen Berhasil Disimpan", 3000, "hasil_tambah_dev");
					// } else {
					// 	notification ("danger", response.response_package, 3000, "hasil_tambah_dev");
					// }
				},
				error: function(response) {
					console.clear();
					console.log(response);
				}
			});
		}
		/*======================= RADIOLOGI SCRIPT AREA STOP ==========================*/

		/*======================= LABORATORIUM SCRIPT AREA START ========================*/
		//load order with returning selectedTindakan
		/*function loadLabOrder(uid_antrian){
			let dataOrder;
			let selectedTindakan = {};

			$.ajax({
				url: __HOSTAPI__ + "/Laboratorium/get-radiologi-order/" + uid_antrian,
				async:false,
				beforeSend: function(request) {
					request.setRequestHeader("Authorization", "Bearer " + <?php //echo json_encode($_SESSION["token"]); ?>);
				},
				type:"GET",
				success:function(response) {
					if(response.response_package != null) {
						dataOrder = response.response_package.detail_order;

						let no_urut = 1;
						$.each(dataOrder, function(key_order, item_order){
							let status_disabled = "disabled";

							//check if order data has inserted, the delete button will disabled
							// if (item_order.keterangan == null && item_order.kesimpulan == null){
							// 	status_disabled = "";
							// }

							let html = "<tr>\
									<td class='no_urut_lab'>"+ no_urut +"</td>\
									<td>"+ item_order.tindakan +"</td>\
									<td>"+ item_order.penjamin +"</td>\
									<td><button class='btn btn-danger btn-sm btnHapusTindakanLab' 					data-uid='"+ item_order.uid_tindakan +"' \
										data-nama='" + item_order.tindakan +"' "+ status_disabled +">\
										<i class='fa fa-trash'></button></td>\
								</tr>";

							$("#table_tindakan_lab tbody").append(html);
							no_urut++;

							$('#tindakan_lab').val('').trigger('change');
							selectedTindakan[item_order.uid_tindakan] = item_order.uid_penjamin;
							$("#tindakan_lab option[value='"+ item_order.uid_tindakan +"']").remove();
						});

					}

				},
				error: function(response) {
					console.log(response);
				}
			});
			
			return selectedTindakan;
		}*/
		
		function loadLabOrder(uid_antrian){

			$.ajax({
				url: __HOSTAPI__ + "/Laboratorium/get-laboratorium-order/" + uid_antrian,
				async:false,
				beforeSend: function(request) {
					request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
				},
				type:"GET",
				success:function(response) {

					if(response.response_package != null) {

						let no_urut = 1;
						$.each(response.response_package.response_data, function(key, item){
							let status_disabled = "disabled";

							let html = "<tr>\
									<td class='no_urut_lab'>"+ no_urut +"</td>\
									<td>"+ item.no_order +"</td>\
									<td>"+ item.waktu_order +"</td>\
									<td>"+ item.nama_dr_penanggung_jawab +"</td>\
									<td>\
										<button class='btn btn-warning btn-sm btnViewDetailOrder' 					data-uid='"+ item.uid +"' data-dokterpj='"+ item.uid_dr_penanggung_jawab +"'>\
										<i class='fa fa-list'></i></button> \
										<button class='btn btn-danger btn-sm btnHapusOrderLab' 					data-uid='"+ item.uid +"' data-order='" + item.no_order + "' " + status_disabled +">\
										<i class='fa fa-trash'></i></button></td>\
								</tr>";

							$("#table_order_lab tbody").append(html);
							no_urut++;
						});

					}

				},
				error: function(response) {
					console.log(response);
				}
			});
			
		}

		var dataTableLabOrder = $("#table_order_lab").DataTable({
			"ajax":{
				"url" : __HOSTAPI__ + "/Laboratorium/get-laboratorium-order/" + UID,
				"async" : false,
				"beforeSend" : function(request) {
					request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
				},
				"type" : "GET",
				"dataSrc": function(response){
					if (response.response_package != null){
						return response.response_package.response_data;
					}
				}
			},
			"columnDefs":[
				{"targets":0, "className":"dt-body-left"}
			],
			"columns" : [
				{ 
					"data": null,"sortable": false, 
					render: function (data, type, row, meta) {
						return meta.row + meta.settings._iDisplayStart + 1;
					}  
				},
				{
					"data" : null, render: function(data, type, row, meta) {
						return row["no_order"];
					}
				},
				{
					"data" : null, render: function(data, type, row, meta) {
						return row["waktu_order"];
					}
				},
				{
					"data" : null, render: function(data, type, row, meta) {
						return row["nama_dr_penanggung_jawab"];
					}
				},
				{
					"data" : null, render: function(data, type, row, meta) {

						let button = "<button class='btn btn-warning btn-sm btnViewDetailOrder' data-uid='"
									+ row['uid'] +"' data-dokterpj='"+ row['uid_dr_penanggung_jawab'] +"' data-editable='" + row['editable'] + "'  >\
										<i class='fa fa-list'></i></button>";
						
						if (row['editable'] == 'true') {
							button += "<button class='btn btn-danger btn-sm btnHapusOrderLab' 					data-uid='"+ row['uid'] +"' data-order='" + row['no_order'] + "' " + ">\
										<i class='fa fa-trash'></i></button>";
						}

						return button;
					}
				}
			]
		});

		function loadLabDetailOrder(uid_lab_order, status_disabled){
			let dataDetail;
			let tindakanTerpilih = {};

			$("#table_tindakan_lab tbody").html("");

			$.ajax({
				url: __HOSTAPI__ + "/Laboratorium/get-laboratorium-order-detail/" + uid_lab_order,
				async:false,
				beforeSend: function(request) {
					request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
				},
				type:"GET",
				success:function(response) {
					if (response.response_package != null) {
						dataDetail = response.response_package.response_data;

						let no_urut = 1;
						$.each(dataDetail, function(key, item){
							let html = "<tr>" + 
									"<td class='no_urut_lab'>" + no_urut + "</td>" +
									"<td>" + item.tindakan + "</td>" + 
									"<td>" + item.penjamin + "</td>" +
									"<td><button " + status_disabled + " class='btn btn-sm btn-danger btnHapusTindakanLab' data-uid='" + item.uid_tindakan + "' data-nama='" + item.tindakan + "'><i class='fa fa-trash'></i></button></td>" +
								"</tr>";

							$("#table_tindakan_lab tbody").append(html);

							tindakanTerpilih[item.uid_tindakan] = item.uid_penjamin;
							$("#tindakan_lab option[value='"+ item.uid_tindakan +"']").remove();
							no_urut++;
						});	
					}

				},
				error: function(response) {
					console.log(response);
				}
			});

			return tindakanTerpilih;
		}

		function setLabTindakan(listLabTindakan){
			$("#tindakan_lab").empty();
			$("#tindakan_lab").append("<option disabled selected value=''>Pilih Tindakan Laboratorium</option>");

			if (listLabTindakan.length > 0){
				for(i = 0; i < listLabTindakan.length; i++){
					
					var selection = document.createElement("OPTION");
					$(selection).attr("value", listLabTindakan[i].uid).html(listLabTindakan[i].nama);
					$("#tindakan_lab").append(selection);
				}

				$("#tindakan_lab").select2({
					dropdownParent: $("#form-tambah-order-lab")
				});
			}
		}

		function loadLabTindakan(){
			let labTindakan;

			$.ajax({
				url: __HOSTAPI__ + "/Laboratorium/get_tindakan_for_dokter",
				async:false,
				beforeSend: function(request) {
					request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
				},
				type:"GET",
				success:function(response) {
					if(response.response_package != null) {
						labTindakan = response.response_package.response_data;
					}

				},
				error: function(response) {
					console.log(response);
				}
			});

			return labTindakan;
		}

		//loadLabOrder(UID);
		
		//initiate laboratorium tindakan data
		var listLabTindakan = loadLabTindakan();
		setLabTindakan(listLabTindakan);

		//variable for collect selected Tindakan
		var listTindakanLabTerpilih = {};

		//variable for collect deleted Tindakan
		var listTindakanLabDihapus = [];

		//variable for load penjamin
		var listPenjamin = loadDataPenjamin();

		//order lab action mode (new or edit)
		var LabMode;

		//for edit order lab
		var uid_lab_order;

		//this variable will be used in action tambahTindakan; default is uid penjamin umum
		var uid_penjamin_tindakan_lab = __UIDPENJAMINUMUM__;

		$("#tindakan_lab").on('select2:select', function(){
			let uidTindakanLab = $(this).val();

			$("#lab_tindakan_notifier").html("");
			if (pasien_penjamin_uid !== __UIDPENJAMINUMUM__){
				uid_penjamin_tindakan_lab = __UIDPENJAMINUMUM__;

				let html = '<p><b class="badge badge-warning"><i class="fa fa-exclamation-circle" style="margin-right: 5px;"></i>Tindakan akan ditanggung Penjamin Umum</b></p>';

				$.each(listLabTindakan, function(key_tindakan, item_tindakan){
					let statusLoop = true;
					
					if (item_tindakan.uid === uidTindakanLab){

						$.each(item_tindakan.harga, function(key_harga, item_harga){

							if (pasien_penjamin_uid == item_harga.penjamin){
								html = '<p><b class="badge badge-success"><i class="fa fa-check-circle" style="margin-right: 5px;"></i>Tindakan ditanggung Penjamin</b></p>';
								
								//setter jika dijamin
								uid_penjamin_tindakan_lab = pasien_penjamin_uid; 
								statusLoop = false;	
								return false;
							}

							});

							if (statusLoop === false){
							return false;
						}
						
					}

				});

				$("#lab_tindakan_notifier").html(html);
			}

		});

		$("#btnTambahOrderLab").click(function(){
			$("#btnTambahTindakanLab").removeAttr("disabled");
			$("#btnSubmitOrderLab").removeAttr("hidden");
			
			LabMode = "new";
			uid_lab_order = "";

			$("#table_tindakan_lab tbody").html("");
			$("#dr_penanggung_jawab_lab").val("").trigger('change');
			setLabTindakan(listLabTindakan);

			$("#form-tambah-order-lab").modal("show");

			listTindakanLabTerpilih = {};
		});

		$("#table_order_lab tbody").on('click', '.btnViewDetailOrder', function(){
			let uidLabOrder = $(this).data('uid');
			let uidDokterPj = $(this).data('dokterpj');
			let statusEditable = $(this).data('editable');
			let status_disabled = "";
			setLabTindakan(listLabTindakan);

			if (statusEditable == false) {
				$("#btnTambahTindakanLab").prop("disabled", true);
				$("#btnSubmitOrderLab").prop("hidden", true);
				status_disabled = "disabled";
			} else {
				$("#btnTambahTindakanLab").prop('disabled', false);
				$("#btnSubmitOrderLab").prop('hidden', false);
			}

			LabMode = "edit";
			uid_lab_order = uidLabOrder;

			listTindakanLabTerpilih = loadLabDetailOrder(uidLabOrder, status_disabled);
			
			$("#dr_penanggung_jawab_lab").val(uidDokterPj).trigger('change');
			$("#form-tambah-order-lab").modal("show");

			//listTindakanLabTerpilih = loadLabOrder(uidLabOrder);
		});

		$("#table_order_lab tbody").on('click', '.btnHapusOrderLab', function(){
			let uidLabOrder = $(this).data('uid');
			let noOrder = $(this).data('order');

			Swal.fire({
				title: 'Hapus order laboratorium ' + noOrder + '?',
				showDenyButton: true,
				type: 'warning',
				//showCancelButton: true,
				confirmButtonText: `Ya`,
  				confirmButtonColor: `#ff2a2a`,
				denyButtonText: `Batal`,
				denyButtonColor: `#1297fb`
			}).then((result) => {
				if (result.isConfirmed) {
					//Validation
					$.ajax({
						async: false,
						url: __HOSTAPI__ + "/Laboratorium/lab_order/" + uidLabOrder,
						beforeSend: function(request) {
							request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
						},
						type: "DELETE",
						success: function(response) {
							console.log(response);
							if(response.response_package.response_result > 0) {
								notification ("success", "Order Berhasil Dihapus", 3000, "hasil_tambah_dev");
								dataTableLabOrder.ajax.reload();
							} else {
								notification ("danger", response.response_package, 3000, "hasil_tambah_dev");
							}
						},
						error: function(response) {
							console.clear();
							console.log(response);
						}
					});
					
				} else if (result.isDenied) {
					//Swal.fire('Changes are not saved', '', 'info')
				}
			});
		});

		$("#btnTambahTindakanLab").click(function(){
			let uidTindakanLab = $("#tindakan_lab").val();
			
			if (uidTindakanLab != null){
				let dataTindakan = $("#tindakan_lab").select2('data');
				let namaPenjamin;

				$.each(listPenjamin, function(key, item){
					if (item.uid == uid_penjamin_tindakan_lab){
						namaPenjamin = item.nama;

						return false;
					}
				});

				let html = "<tr>\
						<td class='no_urut_lab'></td>\
						<td>"+ dataTindakan[0].text +"</td>\
						<td>"+ namaPenjamin +"</td>\
						<td>\
							<button class='btn btn-danger btn-sm btnHapusTindakanLab' data-uid='"+ uidTindakanLab + "' data-nama='" + dataTindakan[0].text + "'><i class='fa fa-trash'></i></button>\
						</td>\
					</tr>";

				$("#table_tindakan_lab tbody").append(html);

				$('#tindakan_lab').val('').trigger('change');
				listTindakanLabTerpilih[uidTindakanLab] = uid_penjamin_tindakan_lab;
				$("#tindakan_lab option[value='"+ uidTindakanLab +"']").remove();
				$("#lab_tindakan_notifier").html("");

				setNomorUrut('table_tindakan_lab', 'no_urut_lab');
			}

		});
		
		$("#table_tindakan_lab tbody").on('click', '.btnHapusTindakanLab', function(){
			let uid_tindakan = $(this).data("uid");
			let nama_tindakan = $(this).data("nama");

			delete listTindakanLabTerpilih[uid_tindakan];
			listTindakanLabDihapus.push(uid_tindakan);
			$(this).parent().parent().remove();

			//set back to list
			$("#tindakan_lab").append("<option value='"+ uid_tindakan +"'>"+ nama_tindakan +"</option>");
			$("#lab_tindakan_notifier").html("");

			setNomorUrut('table_tindakan_lab', 'no_urut_lab');
		});


		$("#btnSubmitOrderLab").click(function(){
			let dokterPJLabOrder = $("#dr_penanggung_jawab_lab").val();

			if (dokterPJLabOrder != "" && Object.keys(listTindakanLabTerpilih).length > 0){
				let formData = {
					'request' : LabMode + '-order-lab',
					'uid_antrian' : UID,
					'listTindakan' : listTindakanLabTerpilih,
					'dokterPJ' : dokterPJLabOrder,
					'uid_lab_order': uid_lab_order
				}
				
				$.ajax({
					async: false,
					url: __HOSTAPI__ + "/Laboratorium",
					data: formData,
					beforeSend: function(request) {
						request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
					},
					type: "POST",
					success: function(response) {
						console.log(response);
						if(response.response_package.response_result > 0) {
							notification ("success", "Laboratorium Berhasil Diorder", 3000, "hasil_tambah_dev");
						} else {
							notification ("danger", response.response_package, 3000, "hasil_tambah_dev");
						}

						dataTableLabOrder.ajax.reload();

						// reset form
						// $("#table_tindakan_lab tbody").empty();
						// $("#dr_penanggung_jawab_lab").val('').trigger('change');

						// $("#tindakan_lab").empty();
						// setLabTindakan(listLabTindakan);
						$("#form-tambah-order-lab").modal("hide");
					},
					error: function(response) {
						console.clear();
						console.log(response);
					}
				});
			}

		});


		/*==================== UNIVERSAL FUNCTION =====================*/
		function loadDokterPJ(){
			let dokterPJ;

			$(".dr_penanggung_jawab").empty();
			$(".dr_penanggung_jawab").append("<option disabled selected value=''>Pilih Dokter Penanggung Jawab</option>");

			$.ajax({
				url: __HOSTAPI__ + "/Pegawai/get_all_dokter",
				async:false,
				beforeSend: function(request) {
					request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
				},
				type:"GET",
				success:function(response) {
					if(response.response_package != null) {
						dokterPJ = response.response_package.response_data;
						if (dokterPJ.length > 0){
							for(i = 0; i < dokterPJ.length; i++){
								
			                    var selection = document.createElement("OPTION");
			                    $(selection).attr("value", dokterPJ[i].uid).html(dokterPJ[i].nama_dokter);
			                    $(".dr_penanggung_jawab").append(selection);
			                }
						}
					}

					$("#dr_penanggung_jawab_lab").select2({
						dropdownParent: $("#form-tambah-order-lab")
					});
				},
				error: function(response) {
					console.log(response);
				}
			});

			return dokterPJ;
		}

		loadDokterPJ();

		function setNomorUrut(table_name, no_urut_class){
			/*set dynamic serial number*/
			var rowCount = $("#"+ table_name +" tr").length;
			var table = $("#"+ table_name);
			$("."+ no_urut_class).html("");

			for (var i = 0, row; i < rowCount; i++) {
				//console.log()
				table.find('tr:eq('+ i +')').find('td:eq(0)').html(i);
			}
			/*--------*/
		}
	});

</script>


<div id="form-editor-racikan" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal-large-title" aria-hidden="true" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog modal-md" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modal-large-title"></h5>
				<!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button> -->
			</div>
			<div class="modal-body">
				<div class="form-group col-md-12">
					<label for="txt_racikan_obat">Obat:</label>
					<select class="form-control" id="txt_racikan_obat"></select>
				</div>
				<!-- <div class="form-group col-md-6">
					<label for="txt_racikan_jlh">Jumlah:</label>
					<input type="text" class="form-control" id="txt_racikan_jlh" />
				</div> -->
				<div class="form-group col-md-12">
					<div class="kolom_kekuatan">
						<label for="txt_racikan_kekuatan">Kekuatan:</label>
						<div class="row">
							<div class="col-md-12">
								<input type="text" class="form-control" id="txt_racikan_kekuatan" placeholder="0" />
							</div>
						</div>
					</div>
					<hr />
					<div class="kolom_takar" style="display: none">
						<label for="txt_racikan_takar">Takar:</label>
						<div class="row">
							<div class="col-md-4">
								<input type="text" value="1" class="form-control" id="txt_racikan_takar_bulat" placeholder="0" />
							</div>
							<div class="col-md-1">
								<i class="fa fa-plus" style="margin-top: 10px;"></i>
							</div>
							<div class="col-md-4">
								<input type="text" class="form-control" id="txt_racikan_takar" placeholder="a/b" />
							</div>
							<div class="col-md-3">
								<small>Cth:<br />2 + 1/2</small>
							</div>
						</div>
					</div>
				</div>
				<!-- <div class="form-group col-md-12">
					<label for="txt_racikan_satuan">Satuan:</label>
					<select class="form-control" id="txt_racikan_satuan"></select>
				</div> -->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">Kembali</button>
				<button type="button" class="btn btn-primary" id="btnSubmitKomposisi">Submit</button>
			</div>
		</div>
	</div>
</div>


<div id="form-tambah-order-lab" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal-large-title" aria-hidden="true" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modal-large-title"></h5>
			</div>
			<div class="modal-body">
				<div class="col-md-12 row form-group">
					<div class="col-md-3">
						<label for="dr_penanggung_jawab_lab">Dokter Penanggung Jawab</label>
					</div>
					<div class="col-md-6">
						<select class="form-control dr_penanggung_jawab" id="dr_penanggung_jawab_lab">
							
						</select>	
					</div>
				</div>
				<div class="col-md-12 row form-group">
					<div class="col-md-3">
						<label for="tindakan_lab">Tindakan</label>
					</div>
					<div class="col-md-6">
						<select class="form-control" id="tindakan_lab">
						
						</select>	
					</div>
					<div class="col-md-3">
						<button class="btn btn-info" id="btnTambahTindakanLab">
							<i class="fa fa-plus"></i> Tambah Tindakan Laboratorium
						</button>
					</div>
				</div>
				<div class="col-md-12 row">
					<!-- <div class="col-md-3"></div> -->
					<div class="offset-md-3 col-md-2" style="padding-top: 8px;" id="lab_tindakan_notifier"></div>
				</div>
				<div class="col-md-12 form-group" style="margin-top: 10px;">
					<table class="table table-bordered" id="table_tindakan_lab">
						<thead class="thead-dark">
							<tr>
								<th width='2%' class="wrap_content">No</th>
								<th width='25%'>Tindakan Laboratorium</th>
								<th width='25%'>Penjamin</th>
								<th width='8%' class="wrap_content">Aksi</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">Kembali</button>
				<button type="button" class="btn btn-primary" id="btnSubmitOrderLab">Submit</button>
			</div>
		</div>
	</div>
</div>