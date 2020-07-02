<div class="container-fluid page__heading-container">
	<div class="page__heading d-flex align-items-center">
		<div class="flex">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb mb-0">
					<li class="breadcrumb-item"><a href="<?php echo __HOSTNAME__; ?>/">Home</a></li>
					<li class="breadcrumb-item"><a href="<?php echo __HOSTNAME__; ?>/master/supplier">Master Supplier</a></li>
					<li class="breadcrumb-item active" aria-current="page" id="mode_item">Edit</li>
				</ol>
			</nav>
		</div>
	</div>
</div>


<div class="container-fluid page__container">
	<div class="row card-group-row">
		<div class="col-lg-12 col-md-12">
			<div class="z-0">
				<ul class="nav nav-tabs nav-tabs-custom" role="tablist">
					<li class="nav-item">
						<a href="#tab-informasi" class="nav-link active" data-toggle="tab" role="tab" aria-selected="true" aria-controls="tab-informasi" >
							<span class="nav-link__count"><i class="fa fa-info-circle"></i></span>
							Informasi Dasar
						</a>
					</li>
					<li class="nav-item">
						<a href="#tab-satuan" class="nav-link" data-toggle="tab" role="tab" aria-selected="false">
							<span class="nav-link__count"><i class="fa fa-cubes"></i></span>
							Satuan
						</a>
					</li>
					<li class="nav-item">
						<a href="#tab-penjamin" class="nav-link" data-toggle="tab" role="tab" aria-selected="false">
							<span class="nav-link__count"><i class="fa fa-cash-register"></i></span>
							Harga Penjamin
						</a>
					</li>
					<li class="nav-item">
						<a href="#tab-lokasi" class="nav-link" data-toggle="tab" role="tab" aria-selected="false">
							<span class="nav-link__count"><i class="fa fa-clipboard-list"></i></span>
							Lokasi Simpan
						</a>
					</li>
					<li class="nav-item">
						<a href="#tab-monitoring" class="nav-link" data-toggle="tab" role="tab" aria-selected="false">
							<span class="nav-link__count"><i class="fa fa-eye"></i></span>
							Monitoring
						</a>
					</li>
				</ul>
				<div class="card card-body tab-content">
					<div class="tab-pane active show fade" id="tab-informasi">
						<div class="row">
							<div class="col-md-4">
								<div id="image-uploader"></div>
								<span class="custom-upload btn btn-info">
									<input type="file" name="" id="upload-image" />
									<i class="fa fa-upload"></i> Upload
								</span>
							</div>
							<div class="col-lg-8">
								<div class="row">
									<div class="col-md-8">
										<div class="form-group">
											<label for="txt_no_ktp">Nama Item:</label>
											<input type="text" class="form-control" id="txt_nama" placeholder="Nama Item" required>
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label for="txt_kategori">Kategori Item:</label>
											<select class="form-control" id="txt_kategori"></select>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<div class="form-group">
											<label for="txt_kode">Kode Item:</label>
											<input type="text" class="form-control uppercase" id="txt_kode" placeholder="Kode Item" required>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label for="txt_manufacture">Manufacture:</label>
											<select class="form-control" id="txt_manufacture"></select>
										</div>
									</div>
									<div class="col-md-12">
										<div class="form-group">
											<label for="txt_keterangan">Keterangan:</label>
											<textarea id="txt_keterangan" class="form-control" placeholder="Keterangan Item"></textarea>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row" style="margin-top: 10px;">
							<div class="col-md-12">
								<div class="form-group">
									<label>
										Kombinasi Produk:
										<i class="fa fa-info-circle text-info tooltip-custom" data-toggle="Produk merupakan kombinasi produk lain (set) produk. Dapat digunakan juga untuk definisi obat racikan"></i>
									</label>
									<table class="table table-bordered" id="table-kombinasi">
										<thead>
											<tr>
												<th>No</th>
												<th>Nama</th>
												<th>Jumlah</th>
												<th>Satuan</th>
												<th>Varian</th>
												<th>Aksi</th>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
					<div class="tab-pane show fade" id="tab-satuan">
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<label>Satuan Terkecil:</label>
									<select class="form-control" id="txt_satuan_terkecil"></select>
									<table class="table table-bordered table-data" id="table-konversi-satuan">
										<thead>
											<tr>
												<th>No</th>
												<th>Dari</th>
												<th>Ke</th>
												<th>Rasio</th>
												<th>Aksi</th>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</div>
							</div>
							<div class="col-md-12" style="margin-top: 50px;">
								<div class="form-group">
									<label>Varian Kemasan:</label>
									<table class="table table-bordered table-data" id="table-varian">
										<thead>
											<tr>
												<th style="width: 50px;">No</th>
												<th style="width: 50%;">Satuan</th>
												<th>Kemasan</th>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
					<div class="tab-pane show fade" id="tab-penjamin">
						<table class="table table-bordered">
							<thead>
								<tr>
									<th>No</th>
									<th>Penjamin</th>
									<th>Satuan - Varian</th>
									<th>Harga Jual</th>
									<th>Aksi</th>
								</tr>
							</thead>
						</table>
					</div>
					<div class="tab-pane show fade" id="tab-lokasi">
						<table class="table table-bordered">
							<thead>
								<tr>
									<th>No</th>
									<th>Gudang</th>
									<th>No Rak</th>
									<th>Aksi</th>
								</tr>
							</thead>
						</table>
					</div>
					<div class="tab-pane show fade" id="tab-monitoring">
						<table class="table table-bordered">
							<thead>
								<tr>
									<th rowspan="2">No</th>
									<th class="col-md-4" rowspan="2">Gudang</th>
									<th colspan="4">Jumlah</th>
								</tr>
								<tr>
									<th>Minimum</th>
									<th></th>
									<th>Maksimum</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>1</td>
									<td>Gudang Dinkes</td>
									<td>
										<input type="number"  class="form-control" />
									</td>
									<td>
										<select class="form-control"></select>
									</td>
									<td>
										<input type="number"  class="form-control" />
									</td>
									<td>
										<select class="form-control"></select>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-12 text-right">
			<button type="submit" id="btn_save_data" class="btn btn-success">Simpan & Keluar</button>
			<button type="submit" id="btn_save_data_stay" class="btn btn-info">Simpan & Tetap Disini</button>
			<a href="<?php echo __HOSTNAME__; ?>/master/inventori" class="btn btn-danger">Batal</a>
		</div>
	</div>
</div>