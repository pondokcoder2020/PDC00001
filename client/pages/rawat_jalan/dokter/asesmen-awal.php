<div class="row">
	<div class="col-lg">
		<div class="card">
			<div class="card-header card-header-large bg-white d-flex align-items-center">
				<h5 class="card-header__title flex m-0">Subjective</h5>
			</div>
			<div class="card-header card-header-tabs-basic nav" role="tablist">
				<a href="#keluhan-utama" class="active" data-toggle="tab" role="tab" aria-controls="keluhan-utama" aria-selected="true">Keluhan Utama</a>
				<a href="#keluhan-tambahan" data-toggle="tab" role="tab" aria-selected="false">Keluhan Tambahan</a>
			</div>
			<div class="card-body tab-content">
				<div class="tab-pane active show fade" id="keluhan-utama">
					<div id="txt_keluhan_utama"></div>
				</div>
				<div class="tab-pane show fade" id="keluhan-tambahan">
					<div id="txt_keluhan_tambahan"></div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row" style="margin-top: 20px;">
	<div class="col-lg">
		<div class="card">
			<div class="card-header card-header-large bg-white d-flex align-items-center">
				<h5 class="card-header__title flex m-0">Objective</h5>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-lg-6">
						<div class="row">
							<div class="form-group col-lg-6">
								<label for="txt_tekanan_darah">Tekanan Darah</label>
								<div class="input-group input-group-merge">
									<input type="text" id="txt_tekanan_darah" class="form-control form-control-appended" required="" placeholder="Tekanan Darah">
									<div class="input-group-append">
										<div class="input-group-text">
											<span>mmHg</span>
										</div>
									</div>
								</div>
							</div>
							<div class="form-group col-lg-6">
								<label for="txt_tekanan_darah">Nadi</label>
								<div class="input-group input-group-merge">
									<input type="text" id="txt_tekanan_darah" class="form-control form-control-appended" required="" placeholder="Nadi">
									<div class="input-group-append">
										<div class="input-group-text">
											<span>x/menit</span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="form-group col-lg-6">
								<label for="txt_tekanan_darah">Suhu</label>
								<div class="input-group input-group-merge">
									<input type="text" id="txt_tekanan_darah" class="form-control form-control-appended" required="" placeholder="Suhu">
									<div class="input-group-append">
										<div class="input-group-text">
											<span><sup>o</sup>C</span>
										</div>
									</div>
								</div>
							</div>
							<div class="form-group col-lg-6">
								<label for="txt_tekanan_darah">Pernafasan</label>
								<div class="input-group input-group-merge">
									<input type="text" id="txt_tekanan_darah" class="form-control form-control-appended" required="" placeholder="Pernafasan">
									<div class="input-group-append">
										<div class="input-group-text">
											<span>x/menit</span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="form-group col-lg-6">
								<label for="txt_tekanan_darah">Berat Badan</label>
								<div class="input-group input-group-merge">
									<input type="text" id="txt_tekanan_darah" class="form-control form-control-appended" required="" placeholder="Berat Badan">
									<div class="input-group-append">
										<div class="input-group-text">
											<span>kg</span>
										</div>
									</div>
								</div>
							</div>
							<div class="form-group col-lg-6">
								<label for="txt_tekanan_darah">Tinggi Badan</label>
								<div class="input-group input-group-merge">
									<input type="text" id="txt_tekanan_darah" class="form-control form-control-appended" required="" placeholder="Tinggi Badan">
									<div class="input-group-append">
										<div class="input-group-text">
											<span>cm</span>
										</div>
									</div>
								</div>
							</div>
							<div class="form-group col-lg-8">
								<label for="txt_tekanan_darah">Lingkar Lengan Atas</label>
								<div class="input-group input-group-merge">
									<input type="text" id="txt_tekanan_darah" class="form-control form-control-appended" required="" placeholder="Lingkar Lengan Atas">
									<div class="input-group-append">
										<div class="input-group-text">
											<span>cm</span>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-6">
						Pemeriksaan Fisik
						<div id="txt_pemeriksaan_fisik"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg">
		<div class="card">
			<div class="card-header card-header-large bg-white d-flex align-items-center">
				<h5 class="card-header__title flex m-0">Assesment</h5>
			</div>
			<div class="card-header card-header-tabs-basic nav" role="tablist">
				<a href="#asesmen-kerja" class="active" data-toggle="tab" role="tab" aria-controls="asesmen-kerja" aria-selected="true">Diagnosa Kerja</a>
				<a href="#asesmen-banding" data-toggle="tab" role="tab" aria-selected="false">Diagnosa Banding</a>
			</div>
			<div class="card-body tab-content">
				<div class="tab-pane active show fade" id="asesmen-kerja">
					<div class="form-group col-lg-12">
						<label for="txt_tekanan_darah">ICD 10</label>
						<div class="input-group input-group-merge">
							<select id="txt_icd_10_kerja" class="form-control"></select>
						</div>
						<br />
						<div id="txt_diagnosa_kerja"></div>
					</div>
				</div>
				<div class="tab-pane show fade" id="asesmen-banding">
					<div class="form-group col-lg-12">
						<label for="txt_tekanan_darah">ICD 10</label>
						<div class="input-group input-group-merge">
							<select id="txt_icd_10_banding" class="form-control"></select>
						</div>
						<br />
						<div id="txt_diagnosa_banding"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>