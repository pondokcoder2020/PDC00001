<div class="container-fluid page__heading-container">
	<div class="page__heading d-flex align-items-center">
		<div class="flex">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb mb-0">
					<li class="breadcrumb-item"><a href="<?php echo __HOSTNAME__; ?>/">Home</a></li>
					<li class="breadcrumb-item"><a href="<?php echo __HOSTNAME__; ?>/master/kendaraan/jenis">Kendaraan Jenis</a></li>
					<li class="breadcrumb-item active" aria-current="page">Tambah</li>
				</ol>
			</nav>
		</div>
	</div>
</div>


<div class="container-fluid page__container">
	<div class="row card-group-row">
		<div class="col-lg-12 col-md-12 card-group-row__col">
			<div class="card card-body">
				<form>
					<div class="form-group">
						<label for="txt_no_ktp">Nama Kategori:</label>
						<input type="text" class="form-control" id="txt_nama_jenis" placeholder="Jenis Kendaraaan. Ex:R2, R4" required>
					</div>
					
					<button type="submit" class="btn btn-primary">Tambah Jenis</button>
					<a href="<?php echo __HOSTNAME__; ?>/master/kendaraan/jenis" class="btn btn-danger">Batal</a>
				</form>
			</div>
		</div>
	</div>
</div>
