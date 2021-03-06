<!DOCTYPE html>
<html lang="en" dir="ltr">


<?php
	$lastExist;
?>
<?php require 'head.php'; ?>
<body class="layout-default">

	<?php
		if(__PAGES__[0] == 'anjungan') {
			require 'pages/anjungan/index.php';
		} else if(__PAGES__[0] == 'display') {
            require 'pages/display/index.php';
        } else if(__PAGES__[0] == 'display_jadwal_operasi') {
			require 'pages/display_jadwal_operasi/index.php';
		}
	?>
	<div class="mdk-header-layout js-mdk-header-layout">
		<?php require 'header.php'; ?>
		<div class="mdk-header-layout__content">

			<div class="mdk-drawer-layout js-mdk-drawer-layout">
				<div class="mdk-drawer-layout__content page" id="app-settings">
					<?php
						if(empty(__PAGES__[0])) {
							require 'pages/system/dashboard.php';
						} else {
							if(implode('/', __PAGES__) == 'system/logout') {
								require 'pages/system/logout.php';
							} else {
								/*echo '<pre>';
								print_r($_SESSION['akses_halaman_link']);
								echo '</pre>';*/
								if(is_dir('pages/' . implode('/', __PAGES__))) {
									$isInAccess = '';
									$allowAccess = false;
									foreach (__PAGES__ as $key => $value) {
										if($key == 0) {
											$isInAccess .= $value;
										} else {
											$isInAccess .= '/' . $value;
										}

										if (in_array($isInAccess, $_SESSION['akses_halaman_link'])) {
											$allowAccess = true;
											break;
										} else {
											if($allowAccess) {
												$allowAccess = false;
											}
										}
									}

									if($allowAccess) {
										require 'pages/' . implode('/', __PAGES__) . '/index.php';
									} else {
										if(!$allowAccess) {
											require 'pages/system/403.php';
										} else {
											require 'pages/system/404.php';
										}
									}
								} else {
									if(file_exists('pages/' . implode('/', __PAGES__) . '.php')) {
										require 'pages/' . implode('/', __PAGES__) . '.php';
									} else {
										$isFile = 'pages';
										$isInAccess = '';
										$allowAccess = false;

										foreach (__PAGES__ as $key => $value) {
											if(file_exists($isFile . '/' . $value . '.php')) {
												$lastExist = $isFile . '/' . $value . '.php';
											}
											$isFile .= '/' . $value;
										}

										foreach (__PAGES__ as $key => $value) {
											if($key == 0) {
												$isInAccess .= $value;
											} else {
												$isInAccess .= '/' . $value;
											}

											//echo $isInAccess . '<br />';

											if (in_array($isInAccess, $_SESSION['akses_halaman_link'])) {
												$allowAccess = true;
												break;
											} else {
												if($allowAccess) {
													$allowAccess = false;
												}
											}
										}

										if(isset($lastExist) && $allowAccess) {
											//echo $allowAccess;
											require $lastExist;
										} else {
											if(!$allowAccess) {
												require 'pages/system/403.php';
											} else {
												require 'pages/system/404.php';
											}
										}
									}
								}
							}
						}
					?>
				</div>
				<?php require 'sidemenu.php'; ?>
			</div>
		</div>
		<div class="preloader">
			<div class="sidemenu-shimmer">
				<?php
					/*for($sh = 1; $sh <= 10; $sh++) {
				?>
				<div class="shine"></div>
				<?php
					}*/
				?>
			</div>
			<div class="content-shimmer">
				<center>
					<img width="240" height="220" src="<?php echo __HOSTNAME__; ?>/template/assets/images/preloader.gif" />
					<br />
					Loading...
				</center>
			</div>
		</div>
	</div>
	<div class="global-sync-container blinker_dc">
		<h4 class="text-center" style="font-family: Courier"><i class="fa fa-signal"></i><br /><br /><small>reconnecting</small></h4>
	</div>
	<!-- <div id="app-settings">
		<app-settings layout-active="default" :layout-location="{
	  'default': 'index.html',
	  'fixed': 'fixed-dashboard.html',
	  'fluid': 'fluid-dashboard.html',
	  'mini': 'mini-dashboard.html'
	}"></app-settings>
	</div> -->
	<?php require 'script.php'; ?>
	<script type="text/javascript">
		$(function() {
			$(".txt_tanggal").datepicker({
				dateFormat: 'DD, dd MM yy',
				autoclose: true
			});

			moment.locale('id');
			var parentList = [];

			$(".sidebar-menu-item.active").each(function(){
				var activeMenu = $(this).attr("parent-child");
				$("a[href=\"#menu-" + activeMenu + "\"]").removeClass("collapsed").parent().addClass("open");
				$("ul#menu-" + activeMenu).addClass("show");
			});

			$("ul.sidebar-submenu").each(function() {
				var hasMaster = $(this).attr("master-child");
				if (typeof hasMaster !== typeof undefined && hasMaster !== false && hasMaster > 0) {

					//$("a[href=\"#menu-" + hasMaster + "\"]").removeClass("collapsed").parent().addClass("open");
					$("ul#menu-" + hasMaster).addClass("show");

				}
			});

			//$("ul[master-child=\"" + activeMenu + "\"").addClass("open");


			var idleCheck;
			function reloadSession() {
				window.clearTimeout(idleCheck);
				idleCheck = window.setTimeout(function(){
					location.href = __HOSTNAME__ + "/system/logout";
				},30 * 60 * 1000);
			}

			$("body").on("click", function() {
				reloadSession();
			});

			$("body").on("keyup", function() {
				reloadSession();
			});

			$("body").on("mousemove", function() {
				reloadSession();
			});

			refresh_notification();

			$("body").on("click", "#clear_notif", function() {
				$.ajax({
					async: false,
					url:__HOSTAPI__ + "/Notification",
					type: "POST",
					data: {
						request: "clear_notif"
					},
					beforeSend: function(request) {
						request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
					},
					success: function(response) {
						refresh_notification();
					},
					error: function(response) {
						console.log(response);
					}
				});
				return false;
			});

			$("body").on("click", "a[href=\"#notifications_menu\"]", function() {
				$.ajax({
					async: false,
					url:__HOSTAPI__ + "/Notification",
					type: "POST",
					data: {
						request: "read_notif"
					},
					beforeSend: function(request) {
						request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
					},
					success: function(response) {
						refresh_notification();
					},
					error: function(response) {
						console.log(response);
					}
				});
			});

			$("body").on("click", "#refresh_protocol", function() {

                push_socket(__ME__, "refresh", "*", "Refresh page", "info").then(function() {
                    notification ("info", "Refresh page", 3000, "notif_update");
                });
            });
		});

        function getDateRange(target) {
            var rangeItem = $(target).val().split(" to ");
            if(rangeItem.length > 1) {
                return rangeItem;
            } else {
                return [rangeItem, rangeItem];
            }
        }



		function refresh_notification() {
			$.ajax({
				async: false,
				url:__HOSTAPI__ + "/Notification",
				type: "GET",
				beforeSend: function(request) {
					request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
				},
				success: function(response){
					var newCounter = 0;
					$("#notification-container").html("");
					var notifData = response.response_package.response_data;
					for(var notifKey in notifData) {
						if(notifData[notifKey].status == "N") {
							newCounter++;
						}
						var notifContainer = document.createElement("DIV");
						var notifSenderContainer = document.createElement("DIV");
						var notifContentContainter = document.createElement("DIV");
						$(notifSenderContainer).html(	"<div class=\"avatar avatar-sm\" style=\"width: 32px; height: 32px;\">" +
															"<img src=\"" + __HOSTNAME__ + "/template/assets/images/avatar/queue.png\" alt=\"Avatar\" class=\"avatar-img rounded-circle\">" +
														"</div>").addClass("mr-3");
						if(notifData[notifKey].receiver_type == "group") {
							$(notifContentContainter).html(notifData[notifKey].notify_content).addClass("flex");
						} else {
							$(notifContentContainter).html("<a href=\"\">A.Demian</a> left a comment on <a href=\"\">Stack</a><br>" +
															"<small class=\"text-muted\">1 minute ago</small>").addClass("flex");
						}

						$(notifContainer).addClass("dropdown-item d-flex");
						$(notifContainer).append(notifSenderContainer);
						$(notifContainer).append(notifContentContainter);

						$("#notification-container").append(notifContainer);
					}
					if(newCounter > 0) {
						$("#counter-notif-identifier").addClass("navbar-notifications-indicator");
					} else {
						$("#counter-notif-identifier").removeClass("navbar-notifications-indicator");
					}
				},
				error: function(response) {
					console.log(response);
				}
			});
		}



        var serverTarget = "ws://" + __SYNC__ + ":" + __SYNC_PORT__;
		var Sync;
        var tm;
        var protocolLib = {
            akses_update: function(protocols, type, parameter, sender, receiver, time) {
                if(sender != receiver) {
                    $.ajax({
                        url:__HOSTAPI__ + "/Pegawai",
                        beforeSend: function(request) {
                            request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                        },
                        type:"POST",
                        data: {
                            "request": "refresh_pegawai_access",
                            "uid": __ME__
                        },
                        success:function(resp) {
                            notification ("info", "Hak modul Anda sudah diupdate. Refresh halaman untuk akses baru", 3000, "hasil_modul_update");
                        }
                    });
                } else {
                    //
                }
            },
            refresh: function(protocols, type, parameter, sender, receiver, time) {
                location.reload();
            }
        };

	</script>
	<?php
		if(empty(__PAGES__[0])) {
			require 'script/system/dashboard.php';
		} else {
			if(is_dir('script/' . implode('/', __PAGES__))) {
				include 'script/' . implode('/', __PAGES__) . '/index.php';
			} else {
				if(file_exists('script/' . implode('/', __PAGES__) . '.php')) {
					include 'script/' . implode('/', __PAGES__) . '.php';
				} else {
					if(isset($lastExist)) {
						$getScript = explode('/', $lastExist);
						$getScript[0] = 'script';
						include implode('/', $getScript);
					} else {
						include 'script/system/404.php';
					}
				}
			}
		}
	?>
	<script type="text/javascript">

        function resend_socket(requestList, callback) {
            var sendingStatus = 0;
            for(var reqKey in requestList) {
                push_socket(
                    requestList[reqKey].sender,
                    requestList[reqKey].protocol,
                    requestList[reqKey].receiver,
                    requestList[reqKey].message,
                    requestList[reqKey].type
                ).then(function() {
                    //alert(reqKey);
                    sendingStatus++;
                });
            }

            /*if(sendingStatus === requestList.length) {

            } else {
                resend_socket(requestList, callback);
            }*/

            callback();
        }
        async function push_socket(sender, protocols, receiver, parameter, type) {

            if(Sync.readyState === WebSocket.CLOSED) {
                Sync = SocketCheck(serverTarget, protocolLib, tm);
            }

            var msg = {
                protocols: protocols,
                sender: sender,
                receiver: receiver,
                parameter: parameter,
                type: type
            };

            return new Promise((resolve, reject) => {
                Sync.send(JSON.stringify(msg));
                resolve(msg);
            });
        }

        $(function() {
            if ("WebSocket" in window) {

                //var Sync = new WebSocket(serverTarget);
                //console.log(protocolLib);
                Sync = SocketCheck(serverTarget, protocolLib, tm);

            } else {
                console.log("WebSocket Not Supported");
            }

            $(".buttons-excel, .buttons-csv").css({
                "margin": "0 5px"
            }).removeClass("btn-secondary").addClass("btn-info").find("span").prepend("<i class=\"fa fa-dolly-flatbed\"></i>");

        });

        function SocketCheck(serverTarget, protocolLib, tm) {
            var audio;
            var Sync = new WebSocket(serverTarget);
            Sync.onopen = function() {
                clearInterval(tm);
                //console.log("connected");

                /*setInterval(function() {
                    //if (Sync.bufferedAmount == 0)

                }, 2000);*/

                $(".global-sync-container").fadeOut();
            }

            Sync.onmessage = function(evt) {
                var signalData = JSON.parse(evt.data);
                var command = signalData.protocols;
                var type = signalData.type;
                var sender = signalData.sender;
                var receiver = signalData.receiver;
                var time = signalData.time;
                var parameter = signalData.parameter;

                if(command !== undefined && command !== null && command !== "") {

                    if(protocolLib[command] !== undefined) {
                        if(command === "anjungan_kunjungan_panggil") {
                            if(audio !== undefined && audio.audio !== undefined) {
                                if(!audio.paused) {
                                    audio.audio.pause();
                                    audio.audio.currentTime = 0;
                                } else {
                                    //alert();
                                }
                            }
                            audio = protocolLib[command](command, type, parameter, sender, receiver, time);
                        } else {
                            if(receiver == __ME__ || sender == __ME__ || receiver == "*" || receiver == __MY_PRIVILEGES__.response_data[0]["uid"]) {
                                protocolLib[command](command, type, parameter, sender, receiver, time);
                                //console.log(__MY_PRIVILEGES__);
                            } else {
                                protocolLib[command](command, type, parameter, sender, receiver, time);
                                //alert("Tidak sesuai " + __MY_PRIVILEGES__.response_data[0]["uid"]);
                            }
                        }
                    }
                }
            }

            Sync.onclose = function() {
                $(".global-sync-container").fadeIn();
                var tryCount = 1;
                tm = setInterval(function() {
                    console.clear();
                    console.log("CPR..." + tryCount);
                    Sync = SocketCheck(serverTarget, protocolLib, tm);
                    tryCount++;
                }, 3000);
            }

            Sync.onerror = function() {
                /*$(".global-sync-container").fadeIn();
                var tryCount = 1;
                tm = setInterval(function() {
                    console.clear();
                    console.log("CPR..." + tryCount);
                    Sync = SocketCheck(serverTarget, protocolLib);
                    tryCount++;
                }, 3000);*/
            }

            return Sync;
        }

		function inArray(needle, haystack) {
			var length = haystack.length;
			for(var i = 0; i < length; i++) {
				if(haystack[i] == needle) return true;
			}
			return false;
		}

        var floatContainer = document.createElement("DIV");
        $(floatContainer).addClass("manual_container");
        $("body").append(floatContainer);

        function notify_manual(mode, title, time, identifier, setTo, pos = "left") {
            var alertContainer = document.createElement("DIV");
            var alertTitle = document.createElement("STRONG");
            var alertDismiss = document.createElement("BUTTON");
            var alertCloseButton = document.createElement("SPAN");

            $(alertContainer).addClass("alert alert-dismissible fade show alert-" + mode).attr({
                "role": "alert",
                "id": identifier
            });

            $(alertTitle).html(title);

            $(alertDismiss).attr({
                "type": "button",
                "data-dismiss": "alert",
                "aria-label": "Close"
            }).addClass("close");

            $(alertCloseButton).attr({
                "aria-hidden": true
            }).html("&times;");

            $(alertContainer).append(alertTitle);
            $(alertDismiss).append(alertCloseButton);
            $(alertContainer).append(alertDismiss);

            var parentPos = $(setTo).offset();
            if(parentPos !== undefined) {
                var topPos = parentPos.top;
                var leftPos = parentPos.left;

                var marginFrom = 30;

                var floatContainer = document.createElement("DIV");
                $(floatContainer).append(alertContainer);

                $(floatContainer).addClass("manual_container");

                $("body").append(floatContainer);

                if(pos === "left") {
                    $(".manual_container").css({
                        "top": topPos + "px",
                        "left": (leftPos - $(floatContainer).width() - marginFrom) + "px"
                    });
                } else if(pos === "bottom") {
                    $(".manual_container").css({
                        "top": (topPos - $(floatContainer).width() - marginFrom) + "px",
                        "left": leftPos + "px"
                    });
                }

                setTimeout(function() {
                    $(alertContainer).fadeOut();
                }, time);
            }
        }

		function notification (mode, title, time, identifier) {
			var alertContainer = document.createElement("DIV");
			var alertTitle = document.createElement("STRONG");
			var alertDismiss = document.createElement("BUTTON");
			var alertCloseButton = document.createElement("SPAN");

			$(alertContainer).addClass("alert alert-dismissible fade show alert-" + mode).attr({
				"role": "alert",
				"id": identifier
			});

			$(alertTitle).html(title);

			$(alertDismiss).attr({
				"type": "button",
				"data-dismiss": "alert",
				"aria-label": "Close"
			}).addClass("close");

			$(alertCloseButton).attr({
				"aria-hidden": true
			}).html("&times;");

			$(alertContainer).append(alertTitle);
			$(alertDismiss).append(alertCloseButton);
			$(alertContainer).append(alertDismiss);

			$(".notification-container").append(alertContainer);

			setTimeout(function() {
				$(alertContainer).fadeOut();
			}, time);
		}

		function number_format (number, decimals, dec_point, thousands_sep) {
			// Strip all characters but numerical ones.
			number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
			var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
			s = '',
			toFixedFix = function (n, prec) {
				var k = Math.pow(10, prec);
				return '' + Math.round(n * k) / k;
			};
			// Fix for IE parseFloat(0.55).toFixed(0) = 0;
			s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
			if (s[0].length > 3) {
				s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
			}
			if ((s[1] || '').length < prec) {
				s[1] = s[1] || '';
				s[1] += new Array(prec - s[1].length + 1).join('0');
			}
			return s.join(dec);
		}


		function bpjs_load_faskes() {
			var dataFaskes = [];
			$.ajax({
				async: false,
				url:__HOSTAPI__ + "/BPJS/get_faskes",
				type: "GET",
				beforeSend: function(request) {
					request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
				},
				success: function(response){
					var data = [];
					if(response == undefined || response.response_package == undefined || response.response_package.response_data == undefined) {
						dataFaskes = [];
					} else {
						dataFaskes = response.response_package.response_data;
					}
				},
				error: function(response) {
					console.log(response);
				}
			});

			return dataFaskes;
		}

		function str_pad(str_length, target, objectPad = "0") {
			target = "" + target;
			var pad = "";
			for(var a = 1; a <= str_length; a++) {
				pad += objectPad;
			}
			var ans = pad.substring(0, pad.length - target.length) + target;
			return ans;
		}

		$(function() {
			var sideMenu1 = <?php echo json_encode($sideMenu1); ?>;
			var sideMenu2 = <?php echo json_encode($sideMenu2); ?>;
			var sideMenu3 = <?php echo json_encode($sideMenu3); ?>;

			if(sideMenu1 > 0) {
				$("#sidemenu_1").show();
			} else {
				$("#sidemenu_1").hide();
			}

			if(sideMenu2 > 0) {
				$("#sidemenu_2").show();
			} else {
				$("#sidemenu_2").hide();
			}

			if(sideMenu3 > 0) {
				$("#sidemenu_3").show();
			} else {
				$("#sidemenu_3").hide();
			}


			$(".tooltip-custom").each(function() {
				var data = $(this).attr("data-toggle");
				$(this).tooltip({
					placement: "top",
					title: data
				});
			});

			$(".sidebar-menu").each(function(e) {
				$(this).find("li.sidebar-menu-item").each(function(f) {
					var shimmer = document.createElement("DIV");
					$(shimmer).addClass("shine");
					$(".sidemenu-shimmer").append(shimmer);
				});
			});

			var weekday=new Array(7);
			weekday[0]="Minggu";
			weekday[1]="Senin";
			weekday[2]="Selasa";
			weekday[3]="Rabu";
			weekday[4]="Kamis";
			weekday[5]="Jumat";
			weekday[6]="Sabtu";

			var monthName=new Array(7);
			monthName[0]="Januari";
			monthName[1]="Februari";
			monthName[2]="Maret";
			monthName[3]="April";
			monthName[4]="Mei";
			monthName[5]="Juni";
			monthName[6]="Juli";
			monthName[7]="Agustus";
			monthName[8]="September";
			monthName[9]="Oktober";
			monthName[10]="November";
			monthName[11]="Desember";
		});
	</script>
	<div class="notification-container"></div>
</body>

</html>