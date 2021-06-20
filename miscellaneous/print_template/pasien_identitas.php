<html>
<head>
    <style type='text/css'>
        @media print {
            @page {
                size:7.5cm 4cm;
                margin:0;
                padding:0;
                font-size: .8rem;
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
        table tr td {
            vertical-align: top;
        }

        table tr td:nth-child(1) {
            text-align: right;
        }

        table tr td:nth-child(2) {
            font-weight: bold !important;
        }

        hr{
            border:1px dashed #F2F2F2;
        }
    </style>
</head>
<body>
<div align="center">
    <table style='width:100%;border-collapse:collapse; background:#fff; font-size:1.1rem; margin-top: 10%;'>
        <tr>
            <td width="25%">No. RM</td>
            <td> : </td>
            <td width="70%"><span style="padding-left: 5%;"><?php echo $_POST['no_rm'];?></span></td>
        </tr>
        <tr>
            <td width="25%">Nama</td>
            <td> : </td>
            <td width="70%"><span style="margin-left: 5%;"><?php echo (isset($_POST['nama_panggilan'])) ? $_POST['nama_panggilan'] . $_POST['nama'] : $_POST['nama']; ?></span></td>
        </tr>
        <tr>
            <td width="25%">Tanggal Lahir</td>
            <td> : </td>
            <td width="70%"><span style="padding-left: 5%;"><?php echo $_POST['tanggal_lahir'];?></span></td>
        </tr>
    </table>
</div>
</body>
</html>