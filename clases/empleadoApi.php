<?php
require_once 'empleado.php';
require_once 'IApiUsable.php';
class empleadoApi extends Empleado implements IApiUsable
{
	public function TraerUno($request, $response, $args) {
		$id=$args['id'];
		$empleadoObj = Empleado::TraerEmpleado($id);
		$newResponse = $response->withJson($empleadoObj, 200);  
		return $newResponse;
	}

	public function TraerTodos($request, $response, $args) {
		$empleados = Empleado::TraerEmpleados();
		$newResponse = $response->withJson($empleados, 200);  
		return $newResponse;
	}
	
	public function TraerMetricas($request, $response, $args) {
		$metricas = Empleado::Analytics();
		$newResponse = $response->withJson($metricas, 200);  
		return $newResponse;
	}
	
	public function DeshabilitarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
		$empleado = Empleado::TraerEmpleado($ArrayDeParametros['idEmpleado']);
		$idEmpleado = $request->getAttribute('empleado')->id;
		if ($empleado) {
			if ($empleado->id != $idEmpleado) {
				if ($empleado->estado == "activo") {
					$empleado->DeshabilitarEmpleado();
					//Cargo el log
					if ($request->getAttribute('empleado')) {
						$new_log = new Log();
						$new_log->idEmpleado = $request->getAttribute('empleado')->id;
						$new_log->accion = "Deshabilitar empleado $empleado->usuario";
						$new_log->GuardarLog();
					}
					//--
					$objDelaRespuesta = array(
						'mensaje'=>'Empleado deshabilitado',
						'status'=>'OK'
					);
				} else if ($empleado->estado == "ocupado") {
					$objDelaRespuesta = array(
						'mensaje'=>'Este empleado esta ocupado en este momento.',
						'status'=>'ERROR'
					);
				} else {
					$objDelaRespuesta = array(
						'mensaje'=>'Este empleado ya esta deshabilitado.',
						'status'=>'ERROR'
					);
				}
			} else {
				$objDelaRespuesta = array(
					'mensaje'=>'No puede deshabilitarse a usted mismo.',
					'status'=>'ERROR'
				);
			}
		} else {
			$objDelaRespuesta = array(
				'mensaje'=>'Error buscando al empleado.',
				'status'=>'ERROR'
			);
		}
		return $response->withJson($objDelaRespuesta, 200);
	}

	public function ActivarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
		$empleado = Empleado::TraerEmpleado($ArrayDeParametros['idEmpleado']);
		if ($empleado) {
			if ($empleado->GetEstado() == 'deshabilitado') {
				$empleado->ActivarEmpleado();
				//Cargo el log
				if ($request->getAttribute('empleado')) {
					$idEmpleado = $request->getAttribute('empleado')->id;
					$new_log = new Log();
					$new_log->idEmpleado = $request->getAttribute('empleado')->id;
					$new_log->accion = "Activar empleado $empleado->usuario";
					$new_log->GuardarLog();
				}
				//--
				$objDelaRespuesta = array(
					'mensaje'=>'Empleado activado.',
					'status'=>'OK'
				);
			} else {
				$objDelaRespuesta = array(
					'mensaje'=>'Este empleado ya esta activo.',
					'status'=>'ERROR'
				);
			}
		} else {
			$objDelaRespuesta = array(
				'mensaje'=>'Error buscando al empleado.',
				'status'=>'ERROR'
			);
		}
		return $response->withJson($objDelaRespuesta, 200);
	}

	public function CargarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
		$miempleado = new Empleado();
		$miempleado->usuario=$ArrayDeParametros['usuario'];
		$miempleado->clave=$ArrayDeParametros['clave'];
		$miempleado->sector=$ArrayDeParametros['sector'];
		$miempleado->sueldo=$ArrayDeParametros['sueldo'];
		$miempleado->estado=$ArrayDeParametros['estado'];
		$miempleado->InsertarEmpleado();
		//Cargo el log
		if ($request->getAttribute('empleado')) {
			$new_log = new Log();
			$new_log->idEmpleado = $request->getAttribute('empleado')->id;
			$new_log->accion = "Cargar empleado";
			$new_log->GuardarLog();
		}
		//--
		$objDelaRespuesta = array(
			'mensaje'=>"Se ha ingresado el empleado",
			'status'=>'OK'
		);
		return $response->withJson($objDelaRespuesta, 200);
	}

	public function BorrarUno($request, $response, $args) {
		$id=$args['id'];
		$empleado= new Empleado();
		$empleado->id=$id;
		$cantidadDeBorrados=$empleado->BorrarEmpleado();
		$objDelaRespuesta= new stdclass();
		if($cantidadDeBorrados>0) {
			//Cargo el log
			if ($request->getAttribute('empleado')) {
				$new_log = new Log();
				$new_log->idEmpleado = $request->getAttribute('empleado')->id;
				$new_log->accion = "Borrar empleado";
				$new_log->GuardarLog();
			}
			//--
			$objDelaRespuesta = array(
				'mensaje'=>"Empleado eliminado.",
				'status'=>'OK'
			);
		} else {
			$objDelaRespuesta = array(
				'mensaje'=>"Error eliminando el empleado.",
				'status'=>'ERROR'
			);
		}
		return $response->withJson($objDelaRespuesta, 200);
	}
		
	public function ModificarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
		$miempleado = new Empleado();
		$miempleado->id = $args['id'];
		$miempleado->usuario=$ArrayDeParametros['usuario'];
		$miempleado->clave=$ArrayDeParametros['clave'];
		$miempleado->sector=$ArrayDeParametros['sector'];
		$miempleado->sueldo=$ArrayDeParametros['sueldo'];
		$miempleado->estado=$ArrayDeParametros['estado'];
		$miempleado->GuardarEmpleado();
		//Cargo el log
		if ($request->getAttribute('empleado')) {
			$new_log = new Log();
			$new_log->idEmpleado = $request->getAttribute('empleado')->id;
			$new_log->accion = "Modificar empleados";
			$new_log->GuardarLog();
		}
		//--
		$objDelaRespuesta = array(
			'mensaje'=>"Empleado modificado.",
			'status'=>'OK'
		);
		return $response->withJson($objDelaRespuesta, 200);	
	}
}