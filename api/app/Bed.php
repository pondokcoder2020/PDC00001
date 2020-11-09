<?php 

namespace PondokCoder;

use PondokCoder\Query as Query;
use PondokCoder\QueryException as QueryException;
use PondokCoder\Utility as Utility;
use PondokCoder\Authorization as Authorization;
use PondokCoder\Lantai as Lantai;
use PondokCoder\Ruangan as Ruangan;

class Bed extends Utility {
	static $pdo;
	static $query;

	protected static function getConn(){
		return self::$pdo;
	}

	public function __construct($connection){
		self::$pdo = $connection;
		self::$query = new Query(self::$pdo);
	}


	public function __GET__($parameter = array()) {
		try {
			switch($parameter[1]) {
				case 'bed':
					return self::get_bed('master_unit_bed');
					break;

				case 'bed-detail':
					return self::get_ruangan_detail('master_unit_ruangan', $parameter[2]);
					break;

				/*case 'ruangan-lantai':
					return self::get_ruangan_lantai('master_unit_ruangan', $parameter[2]);
					break;*/
                
                case 'bed-ruangan':
                    return self::get_bed_ruangan('master_unit_bed', $parameter[2]);
                    break;
				default:
					# code...
					break;
			}
		} catch (QueryException $e) {
			return 'Error => '. $e;
		}
	}

	public function __POST__($parameter = array()) {
		switch ($parameter['request']) {
			case 'tambah_bed':
				return self::tambah_bed('master_unit_bed', $parameter);
				break;

			case 'edit_bed':
				return self::edit_bed('master_unit_bed', $parameter);
				break;

			default:
				# code...
				break;
		}
	}

	public function __DELETE__($parameter = array()) {
		return self::delete_bed('master_unit_bed', $parameter);
	}


	/*====================== GET FUNCTION =====================*/
	private function get_bed($table){
		$data = self::$query
					->select($table, 
						array(
							'uid',
							'nama',
							'uid_ruangan',
							'uid_lantai',
							'created_at',
							'updated_at'
						)
					)
					->where(array(
							$table . '.deleted_at' => 'IS NULL'
						)
					)
					->execute();

		$autonum = 1;
		foreach ($data['response_data'] as $key => $value) {
			$data['response_data'][$key]['autonum'] = $autonum;
			$autonum++;

			$uid_lantai = $data['response_data'][$key]['uid_lantai'];
			$arr_lantai = ['','lantai-detail', $uid_lantai];

			$lantai = new Lantai(self::$pdo);
			$get_lantai = $lantai::__GET__($arr_lantai);

			$lantai_res = $get_lantai['response_data'][0];
			$data['response_data'][$key]['lantai'] = $lantai_res['nama'];

			$uid_ruangan = $data['response_data'][$key]['uid_ruangan'];
			$arr_ruangan = ['','ruangan-detail', $uid_ruangan];

			$ruangan = new Ruangan(self::$pdo);
			$get_ruangan = $ruangan::__GET__($arr_ruangan);

			$ruangan_res = $get_ruangan['response_data'][0];
			$data['response_data'][$key]['ruangan'] = $ruangan_res['nama']; 
		}	

		return $data;
	}

	private function get_bed_detail($table, $parameter){
		$data = self::$query
					->select($table, 
						array(
							'uid',
							'nama',
							'uid_ruangan',
							'uid_lantai',
							'created_at',
							'updated_at'
						)
					)
					->where(array(
							$table . '.deleted_at' => 'IS NULL',
							'AND',
							$table . '.uid' => '= ?'
						),
						array($parameter)
					)
					->execute();

		$autonum = 1;
		foreach ($data['response_data'] as $key => $value) {
			$data['response_data'][$key]['autonum'] = $autonum;
			$autonum++;
		}

		return $data;
	}

	private function get_bed_ruangan($table, $parameter){
		$data = self::$query
					->select($table, 
						array(
							'uid',
							'nama',
							'uid_lantai',
							'uid_ruangan',
							'created_at',
							'updated_at'
						)
					)
					->where(array(
							$table . '.deleted_at' => 'IS NULL',
							'AND',
							$table . '.uid_ruangan' => '= ?'
						),
						array($parameter)
					)
					->execute();

		$autonum = 1;
		foreach ($data['response_data'] as $key => $value) {
			$data['response_data'][$key]['autonum'] = $autonum;
			$autonum++;
		}

		return $data;
	}
	/*=========================================================*/


	/*====================== CRUD ========================*/
	private function tambah_bed($table, $parameter){
		$Authorization = new Authorization();
		$UserData = $Authorization::readBearerToken($parameter['access_token']);

		$check = self::duplicate_check(array(
			'table'=>$table,
			'check'=>$parameter['nama']
		));

		if (count($check['response_data']) > 0){
			$check['response_message'] = 'Duplicate data detected';
			$check['response_result'] = 0;
			unset($check['response_data']);
			return $check;
		} else {
			$arr = ['','ruangan-detail', $parameter['ruangan']];

			$ruangan = new Ruangan(self::$pdo);
			$get_ruangan = $ruangan::__GET__($arr);

			$ruangan_res = $get_ruangan['response_data'][0];
			$uid_lantai = $ruangan_res['uid_lantai'];

			$uid = parent::gen_uuid();
			$bed = self::$query
						->insert($table, array(
							'uid'=>$uid,
							'nama'=>$parameter['nama'],
							'uid_ruangan'=>$parameter['ruangan'],
							'uid_lantai'=>$uid_lantai,
							'created_at'=>parent::format_date(),
							'updated_at'=>parent::format_date()
							)
						)
						->execute();

			if ($bed['response_result'] > 0){
				$log = parent::log(array(
							'type'=>'activity',
							'column'=>array(
								'unique_target',
								'user_uid',
								'table_name',
								'action',
								'logged_at',
								'status',
								'login_id'
							),
							'value'=>array(
								$uid,
								$UserData['data']->uid,
								$table,
								'I',
								parent::format_date(),
								'N',
								$UserData['data']->log_id
							),
							'class'=>__CLASS__
						)
					);
			}

			return $bed;
		}
	}

	private function edit_bed($table, $parameter){
		$Authorization = new Authorization();
		$UserData = $Authorization::readBearerToken($parameter['access_token']);

		$old = self::get_bed_detail('master_unit_bed', $parameter['uid']);

		$ruangan = new Ruangan(self::$pdo);
		$get_ruangan = $ruangan::get_ruangan_detail('master_unit_ruangan', $parameter['ruangan']);

		$ruangan_res = $get_ruangan['response_data'][0];
		$uid_lantai = $ruangan_res['lantai'];

		$bed = self::$query
				->update($table, array(
						'nama'=>$parameter['nama'],
						'uid_ruangan'=>$parameter['ruangan'],
						'uid_lantai'=>$uid_lantai,
						'updated_at'=>parent::format_date()
					)
				)
				->where(array(
					$table . '.deleted_at' => 'IS NULL',
					'AND',
					$table . '.uid' => '= ?'
					),
					array(
						$parameter['uid']
					)
				)
				->execute();

		if ($bed['response_result'] > 0){
			unset($parameter['access_token']);

			$log = parent::log(array(
					'type'=>'activity',
					'column'=>array(
						'unique_target',
						'user_uid',
						'table_name',
						'action',
						'old_value',
						'new_value',
						'logged_at',
						'status',
						'login_id'
					),
					'value'=>array(
						$parameter['uid'],
						$UserData['data']->uid,
						$table,
						'U',
						json_encode($old['response_data'][0]),
						json_encode($parameter),
						parent::format_date(),
						'N',
						$UserData['data']->log_id
					),
					'class'=>__CLASS__
				)
			);
		}

		return $bed;
	}

	private function delete_bed($table, $parameter){
		$Authorization = new Authorization();
		$UserData = $Authorization::readBearerToken($parameter['access_token']);

		$bed = self::$query
			->delete($table)
			->where(array(
					$table . '.uid' => '= ?'
				), array(
					$parameter[6]	
				)
			)
			->execute();

		if ($bed['response_result'] > 0){
			$log = parent::log(array(
					'type'=>'activity',
					'column'=>array(
						'unique_target',
						'user_uid',
						'table_name',
						'action',
						'logged_at',
						'status',
						'login_id'
					),
					'value'=>array(
						$parameter[6],
						$UserData['data']->uid,
						$table,
						'D',
						parent::format_date(),
						'N',
						$UserData['data']->log_id
					),
					'class'=>__CLASS__
				)
			);
		}

		return $bed;
	}

	private function duplicate_check($parameter) {
		return self::$query
		->select($parameter['table'], array(
			'uid',
			'nama'
		))
		->where(array(
			$parameter['table'] . '.deleted_at' => 'IS NULL',
			'AND',
			$parameter['table'] . '.nama' => '= ?'
		), array(
			$parameter['check']
		))
		->execute();
	}
}