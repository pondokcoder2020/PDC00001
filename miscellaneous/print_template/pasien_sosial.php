<html>
<head>
    <style type='text/css'>
        @media print {
            @page {
                background: #fff;
                size:A4;
                padding:0.3cm;
            }
        }
        html, body{
            background: #fff;
            margin:auto 0px;
            color: #000;
            font-family: Courier;
        }

        .header{
            background: #fff;
            margin-bottom:1rem;
            border-bottom:1px dashed #F2F2F2;
            text-align:left;
            margin-bottom:1rem;
        }

        img.logo{
            float:left;
            padding-right: 1rem;
            max-height:100px;
        }

        span{
            display:block;
        }

        span.title{
            font-weight: bold;
            font-size:1.2rem;
        }
        span.alamat, span.telepon{
            font-size:0.8rem;
        }

        .width-50{
            width:50%;
            float:left;
        }

        .both{
            clear:both;
        }

        h4.cetak{
            text-decoration:underline;
        }

        table.head tr td{
            font-size:1.1rem;
        }

        table{
            border-collapse: collapse;
            width:100%;
        }

        tr td.gray{
            background-color:#F2F2F2;
        }

        table.border-style tr td, table.border-style tr th{
            border: 1px solid #e6ecf5;
            padding:0.3rem;
            font-size:0.8rem;
        }

        .text-left{
            text-align:left;
        }
        .text-center{
            text-align:center;
        }
        .text-right{
            text-align:right;
        }

        .isian {
            font-size: 1.2rem;
        }
        .isian table tbody tr td{
            vertical-align: top !important;
        }
        .isian table tr td:nth-child(1) {
            text-align: right;
        }

        .isian table tr td:nth-child(3) {
            text-align: left !important;
        }

        /* .terbilang{
            margin-top:1rem;
            padding:0.6rem;
            border:1px dashed #F2F2F2;
            font-size:0.8rem;
        } */

        td.noborder-right{
            border-right:0 !important;
        }
        td.noborder-left{
            border-left:0 !important;
        }

        div.footer{
            page-break-after: always;
        }
    </style>

</head>
<body>
<div class="header" style="border-bottom: solid 1px #000;">
    <table style="background: #fff">
        <tr>
            <td style="text-align:center; width:5%">
                <img src="http://<?php echo $_SERVER['SERVER_ADDR'] ?>/simrsv2/client/template/assets/images/logo-icon.png" class="logo" />
            </td>
            <td style="width:45%;">
                <span class="title"><b>PEMERINTAH KABUPATEN BINTAN</b></span>
                <span class="title"><?php echo $_POST['__PC_CUSTOMER__']; ?></span>
                <span class="alamat"><?php echo $_POST['__PC_CUSTOMER_ADDRESS__'];?></span>
                <span class="telepon">Telp. <?php echo $_POST['__PC_CUSTOMER_CONTACT__'];?></span>
            </td>
            <td style="width:2%;"></td>
            <td style="border: 1px solid #000000; padding : 1% 1% 1% 2%">
                <table class="head">
                    <tr><td>No. RM</td><td>:</td><td><?php echo $_POST['no_rm'];?></td></tr>
                    <tr><td>Nama Pasien</td><td>:</td><td><?php echo (isset($_POST['nama_panggilan'])) ? $_POST['nama_panggilan'] . ". " . $_POST['nama_pasien'] : $_POST['nama_pasien'];?></td></tr>
                    <tr><td>Tanggal Lahir</td><td>:</td><td><?php echo $_POST['tanggal_lahir'];?></td></tr>
                </table>
            </td>
        </tr>
    </table>
</div>
<span class="text-center" style="background: #fff; padding: 1% 0 1% 0; font-size: 1.5rem;"><b>DATA SOSIAL PASIEN</b></span>
<div class="middle" style="background: #fff; border-top: solid 1px #000">
    <table class="isian" cellpadding="8" style="/*border: 1px solid #000000; */padding-left: 10%;">
        <thead>
        <th style="width:25%;"></th>
        <th></th>
        <th></th>
        </thead>
        <tbody>
        <tr>
            <td>Nama</td>
            <td>:</td>
            <td><b><?= $_POST['nama']; ?></b></td>
        </tr>
        <tr>
            <td>Nama Ayah</td>
            <td>:</td>
            <td width="20"><b><?= $_POST['nama_ayah']; ?></b></td>
        </tr>
        <tr>
            <td>Nama Ibu</td>
            <td>:</td>
            <td width="20"><b><?= $_POST['nama_ibu']; ?></b></td>
        </tr>
        <tr>
            <td>Nama Suami / Istri</td>
            <td>:</td>
            <td width="20"><b><?= $_POST['nama_suami_istri']; ?></b></td>
        </tr>
        <tr>
            <td>Alamat Lengkap</td>
            <td>:</td>
            <td rowspan=""><b>
                    <?php
                    echo $_POST['alamat'] . '<br />' . $_POST['alamat_kelurahan_parse'] . ', ' . $_POST['alamat_kecamatan_parse'] . ', ' . $_POST['alamat_kabupaten_parse'] . ', ' . $_POST['alamat_provinsi_parse'];
                    ?></b></td>
        </tr>
        <!-- <tr>
            <td>&nbsp;</td>
        </tr> -->
        <tr>
            <td>Tempat / Tanggal Lahir</td>
            <td>:</td>
            <td><b><?= $_POST['tempat_lahir'] . " / " . $_POST['tanggal_lahir']; ?></b></td>
        </tr>
        <tr>
            <td>Jenis Kelamin</td>
            <td>:</td>
            <td><b><?= (isset($_POST['jenkel_detail']['nama'])) ? $_POST['jenkel_detail']['nama'] : '-'; ?></b></td>
        </tr>
        <tr>
            <td>Status Perkawinan</td>
            <td>:</td>
            <td><b><?= (isset($_POST['nikah_detail']['nama'])) ? $_POST['status_pernikahan']['nama'] : '-'; ?></b></td>
        </tr>
        <tr>
            <td>Agama</td>
            <td>:</td>
            <td><b><?= $_POST['agama_detail']['nama'];?></b></td>
        </tr>
        <tr>
            <td>Pekerjaan</td>
            <td>:</td>
            <td><b><?= (isset($_POST['pekerjaan_detail']['nama'])) ? $_POST['pekerjaan_detail']['nama'] : '-'; ?></b></td>
        </tr>
        <tr>
            <td>Kewarganegaraan</td>
            <td>:</td>
            <td><b><?= (isset($_POST['wn_detail']['nama'])) ? $_POST['wn_detail']['nama'] : '-'; ?></b></td>
        </tr>
        <tr>
            <td>Suku</td>
            <td>:</td>
            <td><b><?= (isset($_POST['suku_detail']['nama'])) ? $_POST['suku_detail']['nama'] : '-'; ?></b></td>
        </tr>
        <tr>
            <td>No. Telp / HP</td>
            <td>:</td>
            <td><b><?= $_POST['no_telp']; ?></b></td>
        </tr>
        <tr>
            <td>Kartu Identitas</td>
            <td>:</td>
            <td><b><?= $_POST['nik']; ?></b></td>
        </tr>
        <tr>
            <td>Riwayat Alergi Obat</td>
            <td>:</td>
            <td rowspan="2"><span style="border: 1px solid #000000"><textarea cols="100" rows="5"></textarea></span></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>Pembiayaan</td>
            <td>:</td>
            <td rowspan="2">
                <div style="inline-block">
                    <div style="col-sm-4">
                        [ ] Pribadi
                    </div>
                    <div style="col-sm-4">
                        [ ] Jaminan Asuransi ________________
                    </div>
                    <div style="col-sm-4">
                        [ ] Jaminan KK / KTP
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        </tbody>
    </table>
    <br />
    <span style="font-size:11pt; text-align:left; color: red;"><b>* Jika ada perubahan data sosial akan saya informasikan kepada pihak RS pada kunjungan berikutnya.</b></span>
    <span style="text-align:center;">------------------------------------------------------------------------------------------</span>
    <span style="font-size:15pt; text-align:right; padding-top: 1%;"><b><?php echo $_POST['pc_customer_address_short'] ?>, <?php echo date('d F Y'); ?> </b></span>
    <br />
    <br />
    <table class="isian">
        <tbody>
        <tr>
            <td>
                <b>Petugas Pendaftaran, <br/> RSUD Kab. Bintan</b>
            </td>
            <td>
            </td>
            <td>
                <b>Pasien / Penanggung Jawab</b>
            </td>
        </tr>
        <tr>
            <td>
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
                <br />
            </td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>
                <b>(..................................)</b>
            </td>
            <td>
            </td>
            <td>
                <b>(..................................)</b>
            </td>
        </tr>
        <tr>
            <td>
                <b>Nama dan Tanda Tangan</b>
            </td>
            <td>
            </td>
            <td>
                <b>Nama dan Tanda Tangan</b>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="footer"></div>
</body>
</html>