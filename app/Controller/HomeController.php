<?php
namespace App\Controller;

use Illuminate\Database\Capsule\Manager as DB;

class HomeController extends Controller
{
 	public function index($request, $response, $args)
	{
	 	return $this->view->render($response, 'layout.twig');
	}
	
	public function registrar_usuario($request, $response, $args)
	{

		$datos = (object) $request->getParams();

		$uploadedFiles = $request->getUploadedFiles();
		$archivo='';
		$ruta = $this->view['dirarchivo'];

		//SUBIDA DE ARCHIVO
		$uploadedFile = $uploadedFiles['frmArchivo'];

		if ($uploadedFile->getSize()>5000000) {
			return json_encode( array("status" => false, "message" => 'Archivo supera el tamaño de 5MB'));
		}
		
		if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
				 $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
				 if ($this->valext($extension)) {
					 $basename = bin2hex(random_bytes(8)); 
					 $filename = sprintf('%s.%0.8s', $basename, $extension);					 
					 $uploadedFile->moveTo($ruta.DIRECTORY_SEPARATOR.$filename);
					 $archivo=$filename;
				 }
				 else{
				 	return json_encode( array("status" => false, "message" => 'formato de archivo no válido'));
				 }
  		  }

  		  $data = [
			"nombre" => $datos->nombre,
			"apellido" => $datos->apellido,
			"tp_documento" => $datos->tp_documento,
			"documento" => $datos->documento,
			"email" => $datos->email,
			"celular" => $datos->celular,
			"fechaNacimiento" => $datos->fechaNacimiento,
			"archivo" => $archivo			
		];

		try {

			$validar = DB::table('usuarios')->where('documento',$datos->documento)->first();			
			if ($validar) {
				return json_encode( array("status" => false, "message" => "El usuario ya existe."));				
			}
			
		
			//DB::beginTransaction();
			DB::table('usuarios')->insert($data);
			//DB::commit();			

			return json_encode( array("status" => true, "message" => "Usuario registrado exitosamente."));

		} catch (\Illuminate\Database\QueryException $ex) {
			return json_encode( array("status" => false, "message" => 'error '.$ex));
		}		

	}

	public function ver_usuarios($request, $response, $args)
	 {
	 	$usuarios = DB::table('usuarios')->get();
	 	if ($usuarios) {
	 		return json_encode( array("status" => true, "usuarios" => $usuarios));
	 	}	 
	 	else
	 	{
	 		return json_encode( array("status" => false, "message" => 'error'));	
	 	}		 		 	
	 }

	 public function editar_usuario($request, $response, $args)
	 {
	 	$datos = (object) $request->getParams();

	 	$uploadedFiles = $request->getUploadedFiles();
		$archivo='';
		$ruta = $this->view['dirarchivo'];

		//SUBIDA DE ARCHIVO
		$uploadedFile = $uploadedFiles['frmArchivo'];

		if ($uploadedFile->getSize()>5000000) {
			return json_encode( array("status" => false, "message" => 'Archivo supera el tamaño de 5MB'));
		}
		
		if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
				 $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
				 if ($this->valext($extension)) {
					 $basename = bin2hex(random_bytes(8)); 
					 $filename = sprintf('%s.%0.8s', $basename, $extension);
					 $uploadedFile->moveTo($ruta.DIRECTORY_SEPARATOR.$filename);
					 $archivo=$filename;
				 }
				 else{
				 	return json_encode( array("status" => false, "message" => 'formato de archivo no válido'));
				 }
    	}

	 	

	 	try{
			$cadena = [
			"nombre"=>$datos->nombre,
			"apellido"=>$datos->apellido,
			"tp_documento"=>$datos->tp_documento,
			"documento"=>$datos->documento,
			"email"=>$datos->email,
			"celular"=>$datos->celular,
			"fechaNacimiento"=>$datos->fechaNacimiento,
			"archivo"=>$archivo			
		];

	 	

			DB::beginTransaction();
			DB::table('usuarios')->where('documento', $datos->documento)->update($cadena);
			DB::commit();			

			return json_encode( array("status" => true, 'message' => 'El usuario ha sido modicado.') );
		} catch (\Illuminate\Database\QueryException $ex) {
			return json_encode( array("status" => false, "message" => "error"));
		}
	 }
	 public function borrar_usuario($request, $response, $args)
	{
		$data = (object) $request->getParams();
		try{
			$usuario = DB::table('usuarios')->where('id',$data->id)->first();

			DB::beginTransaction();
			DB::table('usuarios')->where('cedula',$data->cedula)->delete();
			DB::commit();			

			return json_encode( array("status" => true, 'message' => 'El usuario '.$usuario->nombre.' ha sido eliminado.') );
		} catch (\Illuminate\Database\QueryException $ex) {
			return json_encode( array("status" => false, "message" => "error"));
		}
	}

	private function valext($ext)
	{
		if ($ext=='jpg' || $ext=='png' || $ext=='JPG' || $ext=='PNG' || $ext=='jpeg' || $ext=='JPEG' || $ext=='svg' || $ext=='SVG' || $ext=='gif' || $ext=='pdf') {
			return true;
		}
		else {
			return false;
		}
	}
	
}
