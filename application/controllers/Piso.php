<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'/libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class Piso extends REST_Controller {
	public function __construct()
	{
		header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
		header("Access-Control-Allow-Origins: *");

		parent::__construct();
		$this->load->database();
	}

	public function index(){
	}

	public function pisos_get($module_id){
		$query = $this->db->query("SELECT floor_id FROM aulaList WHERE module_id='".$module_id."'");
			$this->response($query->result());
	}
	public function allpisos_get(){
		$query = $this->db->query("SELECT * FROM floorList");
			$this->response($query->result());
	}
}