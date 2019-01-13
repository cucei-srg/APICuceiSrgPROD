<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH.'/libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class Reporte extends REST_Controller {
	public function __construct()
	{
		header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
		header("Access-Control-Allow-Origins: *");

		parent::__construct();
		$this->load->database();
	}

	public function index(){}

	public function nuevo_post(){
		//SI NO SE ENVIA TOKEN NI EL ID DEL USUARIO
		$token = $this->post('token');
		$idUsuario = $this->post('idUsuario');
		if($token === "" || $idUsuario === ""){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No Autorizado');
			$this->response($respuesta,REST_Controller::HTTP_UNAUTHORIZED);
			return;
		}
		//VALIDAR SI EL TOKEN ENVIADO CORRESPONDE AL ID DEL USUARIO QUE SOLICITA
		$condiciones = array('id' => $idUsuario,
							 'token' => $token );
		$this->db->where($condiciones);
		$query = $this->db->get('personal');
		$existe = $query->row();
		if (!$existe) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'Usuario y token incorrectos');
			$this->response($respuesta,REST_Controller::HTTP_UNAUTHORIZED);
			return;
		}
		//AQUI YA ESTA VALIDADO EL USUARIO
		$this->db->reset_query();
		$correo = $this->post('correo');
		//OBTENER ID Y NOMBRE A PARTIR DEL CORREO DEL USUARIO DADO
		//VALIDAR A SU VEZ EL CORREO SEA VALIDO
		$this->db->select('id,nombre,a_paterno,a_materno');
		$this->db->where('correo',$correo);
		$query = $this->db->get('usuario')->result_array();
		if (!$query) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'El correo dado no esta registrado');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}

		foreach ($query as $key) {
				   $id = $key['id'];
				   $nombre = $key['nombre'];
		           $aPaterno = $key['a_paterno'];
		           $aMaterno = $key['a_materno'];
		}

		$this->db->reset_query();

		//SE PREPARAN LOS DATOS A INSERTAR
		$ubicacionServicio = "".$this->post('modulo')." ".$this->post('piso')." ".$this->post('aula')."";
		$datos = array('recibe' => $this->post('recibe'),
					   'nombre' => $nombre,
					   'a_paterno' => $aPaterno,
					   'a_materno' => $aMaterno,
					   'telefono' => $this->post('telefono'),
					   'area_solicitante' => $this->post('area'),
					   'ubicacion_servicio' => $ubicacionServicio,
					   'anotacion_extra' => $this->post('anotacionExtra'),
					   'descripcion_servicio' => $this->post('option'),
					   'descripcion_problema' => $this->post('descripcionProblema'),
					   'idUsuario' => $id
					);
		$this->db->insert('reportemanten',$datos);
		//OBTENER EL ULTIMO FOLIO REGISTRADO
		$ultimoFolio = $this->db->insert_id();
		$this->db->reset_query();

		//PREPARAN LOS DATOS PARA INSERTAR EN LA TABLA STATUSREPORTE
		$idStatus = "1";
		$datosEstatusReporte = array('id' => null,
									 'idUsuario' => $id,
									 'idStatus' => $idStatus,
									 'folio' => $ultimoFolio);
		$this->db->insert('statusreporte',$datosEstatusReporte);

		//SE ENVIA LA RESPUESTA
		$respuesta = array('error' => FALSE,
							'mensaje' => 'Se ha realizado el reporte correctamente',
							'folio' => $ultimoFolio);

		$this->response($respuesta);
	}

	public function nuevor_post(){
		//SI NO SE ENVIA TOKEN NI EL ID DEL USUARIO
		$token = $this->post('token');
		$idUsuario = $this->post('idUsuario');
		if($token === "" || $idUsuario === ""){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No Autorizado');
			$this->response($respuesta,REST_Controller::HTTP_UNAUTHORIZED);
			return;
		}
		//VALIDAR SI EL TOKEN ENVIADO CORRESPONDE AL ID DEL USUARIO QUE SOLICITA
		$condiciones = array('id' => $idUsuario,
							 'token' => $token );
		$this->db->where($condiciones);
		$query = $this->db->get('usuario');
		$existe = $query->row();
		if (!$existe) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'Usuario y token incorrectos');
			$this->response($respuesta,REST_Controller::HTTP_UNAUTHORIZED);
			return;
		}
		//AQUI YA ESTA VALIDADO EL USUARIO
		$this->db->reset_query();
		//OBTENER ID Y NOMBRE A PARTIR DEL CORREO DEL USUARIO DADO
		$correo = $this->post('correo');
		$this->db->select('id,nombre,a_paterno,a_materno');
		$this->db->where('correo',$correo);
		$query = $this->db->get('usuario')->result_array();

		foreach ($query as $key) {
				   $id = $key['id'];
				   $nombre = $key['nombre'];
		           $aPaterno = $key['a_paterno'];
		           $aMaterno = $key['a_materno'];
		}

		$this->db->reset_query();

		//SE PREPARAN LOS DATOS A INSERTAR
		$ubicacionServicio = "".$this->post('modulo')." ".$this->post('piso')." ".$this->post('aula')."";
		$datos = array('recibe' => $this->post('recibe'),
					   'nombre' => $nombre,
					   'a_paterno' => $aPaterno,
					   'a_materno' => $aMaterno,
					   'telefono' => $this->post('telefono'),
					   'area_solicitante' => $this->post('area'),
					   'ubicacion_servicio' => $ubicacionServicio,
					   'descripcion_servicio' => 'Descripcion Problema',
					   'descripcion_problema' => $this->post('option'),
					   'idUsuario' => $id
					);
		$this->db->insert('reportemanten',$datos);
		//OBTENER EL ULTIMO FOLIO REGISTRADO
		$ultimoFolio = $this->db->insert_id();
		$this->response($ultimoFolio);
		$this->db->reset_query();
		//PREPARAN LOS DATOS PARA INSERTAR EN LA TABLA STATUSREPORTE
		$datosEstatusReporte = array('idUsuario' => $id,
									 'idStatus' => '1',
									 'folio' => $ultimoFolio);
		$this->db->insert('statusreporte',$datosEstatusReporte);

		//SE ENVIA LA RESPUESTA
		$respuesta = array('error' => FALSE,
							'mensaje' => 'Se ha realizado el reporte correctamente',
							'folio' => $ultimoFolio);

		$this->response($respuesta);
	}
	public function nuevos_get(){
		$query = $this->db->query('SELECT * FROM statusreporte WHERE idStatus = 1');
		$this->response($query->num_rows());
	}
	public function atender_get(){
		$query = $this->db->query('SELECT * FROM statusreporte WHERE idStatus = 2');
		$this->response($query->num_rows());
	}
	public function finalizados_get(){
		$query = $this->db->query('SELECT * FROM statusreporte WHERE idStatus = 3');
		$this->response($query->num_rows());
	}
	public function cancelados_get(){
		$query = $this->db->query('SELECT * FROM statusreporte WHERE idStatus = 4');
		$this->response($query->num_rows());
	}
	public function reportenpp_get($aPaterno,$aMaterno,$nombre,$folio){
		$this->db->select('*');
		$this->db->where('a_paterno',$aPaterno)->or_where('a_materno',$aMaterno)->or_where('nombre',$nombre)->or_where('folio',$folio);
		$query = $this->db->get('reportemanten')->result();
		if(empty($query)){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No hay resultados');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->response($query);
	}
	public function reporteindpp_get($folio){
		$this->db->select('*');
		$this->db->where('folio',$folio);
		$query = $this->db->get('reportemanten')->result();
		if(empty($query)){
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No hay resultados');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		$this->response($query);
	}
	public function modreporte_post(){
		$token = $this->post('token');
		$folio = $this->post('folio');
		$fechaRecepcion = $this->post('fecha-recepcion');
		$fechaAsignacion = $this->post('fecha-asignacion');
		$fechaReparacion = $this->post('fecha-reparacion');
		
		$this->db->select('idStatus');
		$this->db->where('folio',$folio);
		$query = $this->db->get('statusreporte')->result_array();
		foreach ($query as $key) {
			$id = $key['idStatus'];
		 }
		 if($id == '4'){
			$respuesta = array('error' => TRUE,
			'mensaje' => 'Ya fue cancelado, no se puede modificar');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		 }

		if (empty($token)) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'No Autorizado.');
			$this->response($respuesta,REST_Controller::HTTP_UNAUTHORIZED);
			return;
		}
		if (!empty($fechaRecepcion) && empty($fechaAsignacion) && !empty($fechaReparacion)) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'Debe haber fecha de Asignación antes.');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		if (empty($fechaRecepcion) && empty($fechaAsignacion) && empty($fechaReparacion)) {
			$respuesta = array('error' => TRUE,
												'mensaje' => 'No se realizó ningun cambio.' );
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		if (empty($fechaRecepcion) && !empty($fechaAsignacion)) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'Debe existir fecha de recepción antes de asignar una.' );
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		if (empty($fechaRecepcion) && !empty($fechaReparacion)) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'Debe haber fecha de recepcion antes de asignar.' );
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		if (empty($fechaAsignacion) && !empty($fechaReparacion)) {
			$respuesta = array('error' => TRUE,
								'mensaje' => 'Debe haber fecha de asignacion antes de asignar.' );
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		}
		 $condiciones = array('fecha_recepcion' => $fechaRecepcion,
	  								'fecha_asignacion' => $fechaAsignacion,
										'fecha_reparacion' => $fechaReparacion);
		$this->db->where('folio',$folio);
		$resultado = $this->db->update('reportemanten',$condiciones);
		$respuesta = array('error' => FALSE,
						   'mensaje' => 'Reporte Actualizado Correctamente');
		$this->response($respuesta);
	}

	public function cancelar_post(){
		$token = $this->post('token');
		$folio = $this->post('folio');

		$this->db->select('idStatus');
		$this->db->where('folio',$folio);
		$query = $this->db->get('statusreporte')->result_array();
		foreach ($query as $key) {
			$id = $key['idStatus'];
		 }
		 if($id == '4'){
			$respuesta = array('error' => TRUE,
			'mensaje' => 'Ya fue cancelado');
			$this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
			return;
		 }
		
		$condiciones = array('idStatus' => '4');
		$this->db->where('folio',$folio);
		$resultado = $this->db->update('statusreporte',$condiciones);
		$respuesta = array('error' => FALSE,
						   'mensaje' => 'Reporte Mandado a Cancelados');
		$this->response($respuesta);
	}
}
