<html>
    <head>
        <style type='text/css'>
            @media print {
                @page {
                    background: #fff;
                    size:8cm 2.2cm;
                    font-size: 0.8rem;
                    margin:0;
                    padding:0;
                    page-break-after: avoid !important;
                    page-break-before: avoid !important;
                }
            }
            @page  {
                page-break-after: avoid !important;
                page-break-before: avoid !important;
            }
            * {
                background: #fff !important;
            }
            html, body{
                page-break-after: avoid !important;
                page-break-before: avoid !important;
                overflow: hidden;
                background: #fff;
                color: #000;
                font-family: Times;
                font-size: 0.8rem;
                text-align:center;
            }

            hr {
                border:1px dashed #F2F2F2 !important;
            }

            table tr td {
                text-align: left !important;
            }
        </style>
    </head>
    <body>
        <b><?php echo $_POST['pc_customer']; ?></b>
        <table style='width:100%;border-collapse:collapse; border-top: solid 1px #f2f2f2; background:#fff; font-size:0.7rem !important;'>
            <tr>
                <td width="50%"><?php echo $_POST['no_rm'];?></td>
                <td><?php echo $_POST['nama'];?></td>
            </tr>
            <tr>
                <td><?php echo date('d F Y', strtotime($_POST['tanggal_lahir']));?></td>
                <td><?php echo $_POST['usia'];?> tahun</td>
            </tr>
            <tr>
                <td><?php echo $_POST['dokter'];?></td>
                <td>TGL : <?php echo $_POST['waktu_masuk']; ?></td>
            </tr>
        </table>
    </body>
</html>