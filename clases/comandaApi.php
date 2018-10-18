<?php

class comandaApi extends Comanda implements IApiUsable
{
	public function TraerUno($request, $response, $args) {
		$codigoComanda=$args['codigoComanda'];
		$codigoMesa=$args['codigoMesa'];
		$comanda=Comanda::TraerComanda($codigoComanda);
		if ($comanda) {
			if ($comanda->GetIdMesa() == $codigoMesa) {
				$pedidos = Pedido::TraerPedidosPorComanda($comanda->codigo);
				foreach ($pedidos as $pedido) {
					if ($pedido->estimacion == NULL) {
						$pedido->estimacion = "-";
					} else {
						$diff = date_diff(date_create($pedido->estimacion), date_create ());
						$pedido->estimacion = $diff->format ('%i minutos');
					}
				}
				$newResponse = $response->withJson($pedidos, 200);  
				return $newResponse;
			} else {
				$objDelaRespuesta= new stdclass();
				$objDelaRespuesta->respuesta="Id de Mesa incorrecto para esta comanda.";
				return $response->withJson($objDelaRespuesta, 401);
			}
		} else {
			$objDelaRespuesta= new stdclass();
			$objDelaRespuesta->respuesta="Comanda inexistente.";
			return $response->withJson($objDelaRespuesta, 401);
		}
	}

	public function TraerTodos($request, $response, $args) {
		$comandas=Comanda::TraerComandas();
		$newResponse = $response->withJson($comandas, 200);  
		return $newResponse;
	}

	public function CargarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
		//Cargo la comanda
		$micomanda = new Comanda();
		$micomanda->SetNombreCliente($ArrayDeParametros['nombreCliente']);
		$micomanda->SetIdMesa($ArrayDeParametros['idMesa']);
		$micomanda->SetFoto(NULL);
		$codigo = $micomanda->InsertarComanda();
		if ($codigo) {
			if (Pedido::CargarPedidos($ArrayDeParametros, $codigo)) {
				$objDelaRespuesta = array(
					'respuesta'=>"Su comanda ha sido ingresada! Codigo de seguimiento: $codigo"
				);
				//Cargo el log
				if ($request->getAttribute('empleado')) {
					$new_log = new Log();
					$new_log->idEmpleado = $request->getAttribute('empleado')->id;
					$new_log->accion = "Cargar comanda";
					$new_log->GuardarLog();
				}
				//--
				return $response->withJson($objDelaRespuesta, 200);
			} else {
				$objDelaRespuesta = array(
					'respuesta'=>'Su comanda ha sido ingresada, pero no se han podido cargar los pedidos de esta comanda (faltan campos)'
				);
			}
		} else {
			$objDelaRespuesta = array(
				'respuesta'=>"Esta mesa no está cargada en el sistema o está ocupada."
			);
		}
		return $response->withJson($objDelaRespuesta, 401);
	}

	public function CargarFoto($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
		$comanda=Comanda::TraerComanda($ArrayDeParametros['codigoComanda']);
		if ($comanda) {
			$archivos = $request->getUploadedFiles();
			$destino="./fotos/";
			$nombreAnterior=$archivos['foto']->getClientFilename();
			$extension= explode(".", $nombreAnterior)  ;
			$extension=array_reverse($extension);
			$comanda->foto = $comanda->codigo.".".$extension[0];
			$comanda->GuardarComanda();
			$archivos['foto']->moveTo($destino.$comanda->codigo.".".$extension[0]);		
			$objDelaRespuesta= new stdclass();
			$objDelaRespuesta->respuesta="Foto cargada!";
			return $response->withJson($objDelaRespuesta, 200);
		} else {
			$objDelaRespuesta= new stdclass();
			$objDelaRespuesta->respuesta="No se pudo encontrar su comanda en el sistema";
			return $response->withJson($objDelaRespuesta, 401);
		}
	}

	public function BorrarUno($request, $response, $args) {
		$id=$args['id'];
		$comanda= new Comanda();
		$comanda->id=$id;
		$cantidadDeBorrados=$comanda->BorrarComanda();
		$objDelaRespuesta= new stdclass();
		if($cantidadDeBorrados>0) {
			//Cargo el log
			if ($request->getAttribute('empleado')) {
				$new_log = new Log();
				$new_log->idEmpleado = $request->getAttribute('empleado')->id;
				$new_log->accion = "Borrar comanda";
				$new_log->GuardarLog();
			}
			//--
			$objDelaRespuesta->respuesta="Comanda eliminada";
			return $response->withJson($objDelaRespuesta, 200);
		} else {
			$objDelaRespuesta->respuesta="Error eliminando la comanda";
			return $response->withJson($objDelaRespuesta, 400);
		}
	}

	public function ModificarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
		$micomanda = new Comanda();
		$micomanda->id=$args['id'];
		$micomanda->nombreCliente=$ArrayDeParametros['nombreCliente'];
		$micomanda->codigo=$ArrayDeParametros['codigo'];
		$micomanda->importe=$ArrayDeParametros['importe'];
		$micomanda->idMesa=$ArrayDeParametros['idMesa'];
		$micomanda->foto=$ArrayDeParametros['foto'];
		$micomanda->GuardarComanda();
		//Cargo el log
		if ($request->getAttribute('empleado')) {
			$new_log = new Log();
			$new_log->idEmpleado = $request->getAttribute('empleado')->id;
			$new_log->accion = "Modificar comanda";
			$new_log->GuardarLog();
		}
		//--
		return $response->withJson($micomanda, 200);		
	}

	public function CobrarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
		$comanda=Comanda::TraerComanda($ArrayDeParametros['codigoComanda']);
		if ($comanda) {
			$respuesta = $comanda->CobrarComanda($ArrayDeParametros['importe']);
			if ($respuesta == "OK") {
				//Cargo el log
				if ($request->getAttribute('empleado')) {
					$new_log = new Log();
					$new_log->idEmpleado = $request->getAttribute('empleado')->id;
					$new_log->accion = "Cobrar comanda";
					$new_log->GuardarLog();
				}
				//--
				$objDelaRespuesta= new stdclass();
				$objDelaRespuesta->respuesta="Clientes pagando";
				return $response->withJson($objDelaRespuesta, 200);
			} else {
				$objDelaRespuesta= new stdclass();
				$objDelaRespuesta->respuesta=$respuesta;
				return $response->withJson($objDelaRespuesta, 401);
			}
		}
		$objDelaRespuesta= new stdclass();
		$objDelaRespuesta->respuesta="Error encontrando la comanda seleccionada";
		return $response->withJson($objDelaRespuesta, 401);
	}
}