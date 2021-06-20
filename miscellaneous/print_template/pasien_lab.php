<html lang="en">
<head>
    <style type='text/css'>
        @media print {
            @page {
                size:8.2cm 3.40cm;
                margin:0;
                padding:0 !important;
                font-size:0.5rem;
                page-break-after: avoid !important;
                page-break-before: avoid !important;
            }
        }
        @page {
            size:8.2cm 3.4cm;
            margin:0;
            padding:0 !important;
            font-size:0.5rem;
            page-break-after: avoid !important;
            page-break-before: avoid !important;
        }
        html, body{
            size:8.2cm 3.4cm;
            margin: 0 !important;
            padding: 0 !important;
            color: #000;
            font-family: Times;
            font-size: 0.8rem;
            text-align:center;
        }
        .borders{
            display: block;
            position: relative;
            page-break-after: avoid !important;
            page-break-before: avoid !important;
            overflow: hidden;
            margin: 0 !important;
            padding: 0 !important;
            line-height: .4cm;
            width: 100%;
            height: 1.65cm;
            float: left;
            background: #fff !important;
        }
        .borders.odd {
            border-bottom: solid 1px #000;
        }
        .borders div {
            width: 50%;
            position: absolute;
            padding: 1px;
            min-height: .85cm !important;
            max-height: .85cm !important;
        }
        .borders div:nth-child(1) {
            left: 0%;
        }
        .borders div:nth-child(2) {
            left: 50%;
        }
    </style>
    <title></title>
</head>
<body>
<?php
for($i=0;$i<4;$i++){
    ?>
    <div class="borders <?php echo ($i % 2 > 0) ? 'even' : 'odd'; ?>">
        <div>
            <?php echo (isset($_POST['nama_panggilan'])) ? $_POST['nama_panggilan'] .". ".$_POST['nama'] : $_POST['nama']; ?>
            <br />
            <?php echo $_POST['tanggal'];?>
        </div>
        <div>
            <?php echo date('d F Y', strtotime($_POST['tanggal_lahir']));?>
            <br />
            <?php echo $_POST['usia'];?> tahun
        </div>
    </div>
    <?php
}
?>
</body>
</html>