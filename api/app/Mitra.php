<?php

namespace PondokCoder;

use PondokCoder\Query as Query;
use PondokCoder\QueryException as QueryException;
use PondokCoder\Utility as Utility;


class Mitra extends Utility {
	static $pdo;
	static $query;
	
	protected static function getConn(){
		return self::$pdo;
	}

	public function __construct($connection) {
		self::$pdo = $connection;
		self::$query = new Query(self::$pdo);
	}

	public function __GET__($parameter = array()) {
		try {

			switch($parameter[1]) {
				case 'detail':
					return self::get_mitra_detail($parameter[2]);
					break;
				default:
					return self::get_mitra();
			}
		} catch (QueryException $e) {
			return 'Error => ' . $e;
		}
	}

	public function __POST__($parameter = array()) {
		try {
			switch($parameter['request']) {
				case 'tambah_mitra':
					return self::tambah_mitra($parameter);
					break;
				case 'edit_mitra':
					return self::edit_mitra($parameter);
					break;
				default:
					return self::get_mitra();
			}
		} catch (QueryException $e) {
			return 'Error => ' . $e;
		}
	}

	public function __DELETE__($parameter = array()) {
		return self::delete($parameter);
	}

	public function get_mitra() {
		$data = self::$query->select('master_mitra', array(
			'uid',
			'nama',
			'jenis',
			'kontak',
			'alamat',
			'created_at',
			'updated_at'
		))
		->where(array(
			'master_mitra.deleted_at' => 'IS NULL'
		), array())
		->execute();
		$autonum = 1;
		foreach ($data['response_data'] as $key => $value) {
			$data['response_data'][$key]['autonum'] = $autonum;
			$autonum++;
		}

		return $data;
	}

	private function tambah_mitra($parameter) {
		$Authorization = new Authorization();
		$UserData = $Authorization::readBearerToken($parameter['access_token']);
		$uid = parent::gen_uuid();
		$worker = self::$query->insert('master_mitra', array(
			'uid'=> $uid,
			'nama' => $parameter['nama'],
			'jenis' => $parameter['jenis'],
			'kontak' => $parameter['kontak'],
			'alamat' => $parameter['alamat'],
			'created_at' => parent::format_date(),
			'updated_at' => parent::format_date()
		))
		->execute();

		if($worker['response_result'] > 0) {
			$log = parent::log(array(
				'type' => 'activity',
				'column' => array(
					'unique_target',
					'user_uid',
					'table_name',
					'action',
					'logged_at',
					'status',
					'login_id'
				),
				'value' => array(
					$uid,
					$UserData['data']->uid,
					'master_mitra',
					'I',
					parent::format_date(),
					'N',
					$UserData['data']->log_id
				),
				'class' => __CLASS__
			));
		}

		return $worker;
	}


	public function get_mitra_detail($parameter) {
		$data = self::$query->select('master_mitra', array(
			'nama',
			'jenis',
			'kontak',
			'alamat',
			'created_at',
			'updated_at'
		))
		->where(array(
			'master_mitra.deleted_at' => 'IS NULL',
			'AND',
			'master_mitra.uid' => '= ?'
		), array(
			$parameter
		))
		->execute();

		return $data;
	}


	private function edit_mitra($parameter) {
		$Authorization = new Authorization();
		$UserData = $Authorization::readBearerToken($parameter['access_token']);
		$old = self::get_mitra_detail($parameter['uid']);
		$worker = self::$query->update('master_mitra', array(
			'nama' => $parameter['nama'],
			'jenis' => $parameter['jenis'],
			'kontak' => $parameter['kontak'],
			'alamat' => $parameter['alamat'],
			'updated_at' => parent::format_date()
		))
		->where(array(
			'master_mitra.deleted_at' => 'IS NULL',
			'AND',
			'master_mitra.uid' => '= ?'
		), array(
			$parameter['uid']
		))
		->execute();

		if($worker['response_result'] > 0) {
			$log = parent::log(array(
				'type' => 'activity',
				'column' => array(
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
				'value' => array(
					$parameter['uid'],
					$UserData['data']->uid,
					'master_mitra',
					'U',
					json_encode($old['response_data'][0]),
					json_encode($parameter),
					parent::format_date(),
					'N',
					$UserData['data']->log_id
				),
				'class' => __CLASS__
			));
		}

		return $worker;
	}


	private function delete($parameter) {
		$Authorization = new Authorization();
		$UserData = $Authorization::readBearerToken($parameter['access_token']);

		$worker = self::$query
		->delete($parameter[6])
		->where(array(
			$parameter[6] . '.uid' => '= ?'
		), array(
			$parameter[7]
		))
		->execute();
		if($worker['response_result'] > 0) {
			$log = parent::log(array(
				'type' => 'activity',
				'column' => array(
					'unique_target',
					'user_uid',
					'table_name',
					'action',
					'logged_at',
					'status',
					'login_id'
				),
				'value' => array(
					$parameter[7],
					$UserData['data']->uid,
					$parameter[6],
					'D',
					parent::format_date(),
					'N',
					$UserData['data']->log_id
				),
				'class' => __CLASS__
			));
		}
		return $worker;
	}
}