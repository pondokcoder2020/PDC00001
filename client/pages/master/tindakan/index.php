<div class="container-fluid page__heading-container">
	<div class="page__heading d-flex align-items-center">
		<div class="flex">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb mb-0">
					<li class="breadcrumb-item"><a href="<?php echo __HOSTNAME__; ?>/">Home</a></li>
					<li class="breadcrumb-item" aria-current="page">Master Poli</li>
					<li class="breadcrumb-item active" aria-current="page">Tindakan</li>
				</ol>
			</nav>
		</div>
		<button class="btn btn-sm btn-info" id="tambah-tindakan">
			<i class="fa fa-plus"></i> Tambah Tindakan
		</button>
	</div>
</div>


<div class="container-fluid page__container">
	<div class="row card-group-row">
		<div class="col-lg-12 col-md-12">
            <div class="card">
                <div class="card-header card-header-large bg-white d-flex align-items-center">
                    <h5 class="card-header__title flex m-0">Tindakan</h5>
                    <button class="btn btn-sm btn-info pull-right" id="importData">
                        <i class="fa fa-upload"></i> Import
                    </button>
                </div>
                <div class="card-body tab-content">
                    <div class="tab-pane active show fade" id="resep-biasa">
                        <table class="table table-bordered" id="table-tindakan">
                            <thead class="thead-dark">
                            <tr>
                                <th class="wrap_content">No</th>
                                <th>Nama Tindakan</th>
                                <th class="wrap_content">Aksi</th>
                            </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
		</div>
	</div>
</div>