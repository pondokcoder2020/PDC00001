<html>
<head>
    <style type='text/css'>
        @media print {
            @page {
                font-size: 10pt;
                size:15.24cm 1.9cm;
                margin:0;
                padding:0;
            }
        }
        * {
            background: #fff !important;
        }
        body{
            margin:auto 0px;
            color: #000;
            font-family: Times;
            font-size: 0.8rem;
            text-align:center;
            padding:5px;
        }

        hr{
            border:1px dashed #F2F2F2;
        }
    </style>
</head>
<body>
<b><?php echo $_POST['pc_customer'];?></b>
<center style="display: block;">
    <table style='width:50%;border-collapse:collapse; background:#fff; font-size:0.6rem;'>
        <tr>
            <td width="50%"><?php echo $_POST['no_rm'];?></td>
            <td width="100%"><?php echo (isset($_POST['panggilan_name']['nama'])) ? $_POST['panggilan_name']['nama'] . $_POST['nama'] : $_POST['nama']; ?></td>
        </tr>
        <tr>
            <td><?php echo date('d F Y', strtotime($_POST['tanggal_lahir']));?></td>
            <td><?php echo $_POST['usia'];?> tahun</td>
        </tr>
        <tr>
            <td><?php echo $_POST['nama_pegawai'];?></td>
            <td>TGL : <?php echo date('d F Y');?></td>
        </tr>
    </table>
</center>
</body>
</html>