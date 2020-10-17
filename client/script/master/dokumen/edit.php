<script src="<?php echo __HOSTNAME__; ?>/plugins/ckeditor5-build-decoupled/ckeditor.js"></script>
<script type="text/javascript">
    $(function () {
        var UID = __PAGES__[3];
        let templateDokumen;
        $.ajax({
            url:__HOSTAPI__ + "/Dokumen/detail/" + UID,
            beforeSend: function(request) {
                request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
            },
            type:"GET",
            success:function(response) {
                if(response.response_package !== undefined && response.response_package !== null) {
                    if(response.response_package.response_result > 0) {
                        $("#txt_nama").val(response.response_package.response_data[0].nama);
                        DecoupledEditor.create( document.querySelector( '#template-editor' ), {
                            extraPlugins: [ MyCustomUploadAdapterPlugin ],
                            placeholder: "Template Dokumen",
                            alignment: {
                                options: [ 'left', 'right', 'justify', 'center' ]
                            }
                        } )
                            .then( editor => {
                                const toolbarContainer = document.querySelector( '.document-editor__toolbar' );
                                if(response.response_package.response_data === undefined) {
                                    editor.setData("");
                                } else {
                                    editor.setData(response.response_package.response_data[0].template_iden);
                                }
                                toolbarContainer.appendChild( editor.ui.view.toolbar.element );
                                templateDokumen = editor;
                                window.editor = editor;
                            } )
                            .catch( err => {
                                console.error( err );
                            } );
                    } else {
                        console.log(response);
                    }
                } else {
                    console.log(response);
                }
            },
            error: function(response) {
                console.log(response);
            }
        });

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



        $("#btnSubmit").click(function () {
            var nama = $("#txt_nama").val();
            var editorDokumen = templateDokumen.getData();
            if(nama !== "") {
                Swal.fire({
                    title: 'Simpan template dokumen?',
                    showDenyButton: true,
                    confirmButtonText: `Ya`,
                    denyButtonText: `Belum`,
                }).then((result) => {
                    if (result.isConfirmed) {
                        //{{__+[A-Z]+__}}
                        $.ajax({
                            url:__HOSTAPI__ + "/Dokumen",
                            beforeSend: function(request) {
                                request.setRequestHeader("Authorization", "Bearer " + <?php echo json_encode($_SESSION["token"]); ?>);
                            },
                            data:{
                                request:'edit_dokumen',
                                uid:UID,
                                nama:nama,
                                template_iden:editorDokumen
                            },
                            type:"POST",
                            success:function(response) {
                                if(response.response_package.response_result > 0) {
                                    console.clear();
                                    console.log(response);
                                    /*Swal.fire(
                                        'Template berhasil disimpan!',
                                        'Dokumen sudah dapat digunakan',
                                        'success'
                                    ).then((result) => {
                                        location.href = __HOSTNAME__ + '/master/dokumen';
                                    });*/
                                } else {
                                    console.log(response);
                                }
                            },
                            error: function(response) {
                                console.log(response);
                            }
                        });

                    } else if (result.isDenied) {
                        //Swal.fire('Changes are not saved', '', 'info')
                    }
                });

            }
        });
    });
</script>