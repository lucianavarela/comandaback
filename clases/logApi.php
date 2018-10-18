<?php
require_once 'log.php';
require_once 'IApiUsable.php';
class logApi extends Log implements IApiUsable
{
	public function TraerUno($request, $response, $args) {
		$id=$args['id'];
		$logObj=Log::TraerLog($id);
		$newResponse = $response->withJson($logObj, 200);  
		return $newResponse;
	}

	public function TraerTodos($request, $response, $args) {
		$logs=Log::TraerLogs();
		$newResponse = $response->withJson($logs, 200);  
		return $newResponse;
	}

	public function CargarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
		$idEmpleado= $ArrayDeParametros['idEmpleado'];
		$fecha= $ArrayDeParametros['fecha'];
		$accion= $ArrayDeParametros['accion'];
		$milog = new Log();
		$milog->idEmpleado=$idEmpleado;
		$milog->fecha=$fecha;
		$milog->accion=$accion;
		$milog->InsertarLog();
		$objDelaRespuesta= new stdclass();
		$objDelaRespuesta->respuesta="Se guardo el log";
		return $response->withJson($objDelaRespuesta, 200);
	}

	public function BorrarUno($request, $response, $args) {
		$id=$args['id'];
		$log= new Log();
		$log->id=$id;
		$cantidadDeBorrados=$log->BorrarLog();
		
		$objDelaRespuesta= new stdclass();
		if($cantidadDeBorrados>0) {
			$objDelaRespuesta->respuesta="Log eliminado";
			return $response->withJson($objDelaRespuesta, 200);
		} else {
			$objDelaRespuesta->respuesta="Error eliminando el log";
			return $response->withJson($objDelaRespuesta, 400);
		}
	}

	public function ModificarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
		$milog = new Log();
		$milog->id=$args['id'];
		$milog->idEmpleado=$ArrayDeParametros['idEmpleado'];
		$milog->fecha=$ArrayDeParametros['fecha'];
		$milog->accion=$ArrayDeParametros['accion'];
		$milog->GuardarLog();
		return $response->withJson($milog, 200);		
	}
}