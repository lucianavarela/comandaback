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
				$objDelaRespuesta = array(
					'pedidos'=>$pedidos,
					'status'=>'OK'
				);
			} else {
				$objDelaRespuesta = array(
					'mensaje'=>'Id de Mesa incorrecto para esta comanda.',
					'status'=>'ERROR'
				);
			}
		} else {
			$objDelaRespuesta = array(
				'mensaje'=>'Comanda inexistente.',
				'status'=>'ERROR'
			);
		}
		return $response->withJson($objDelaRespuesta, 200);
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
					'mensaje'=>"Su comanda ha sido ingresada! Codigo de seguimiento: $codigo",
					'status'=>'OK'
				);
				//Cargo el log
				if ($request->getAttribute('empleado')) {
					$new_log = new Log();
					$new_log->idEmpleado = $request->getAttribute('empleado')->id;
					$new_log->accion = "Cargar comanda";
					$new_log->GuardarLog();
				}
				//--
			} else {
				$objDelaRespuesta = array(
					'mensaje'=>'Su comanda ha sido ingresada, pero no se han podido cargar los pedidos de esta comanda (faltan campos)',
					'status'=>'ERROR'
				);
			}
		} else {
			$objDelaRespuesta = array(
				'mensaje'=>"Esta mesa no está cargada en el sistema o está ocupada.",
				'status'=>'ERROR'
			);
		}
		return $response->withJson($objDelaRespuesta, 200);
	}

	public function CargarFoto($request, $response, $args) {
		$codigo=$args['codigo'];
		$ArrayDeParametros = $request->getParsedBody();
		$comanda=Comanda::TraerComanda($codigo);
		if ($comanda) {
			$comanda->foto = $ArrayDeParametros['foto'];
			$comanda->tipoFoto = $ArrayDeParametros['tipo'];
			$comanda->GuardarComanda();
			$objDelaRespuesta = array(
				'mensaje'=>"Foto cargada.",
				'status'=>'OK'
			);
		} else {
			$objDelaRespuesta = array(
				'mensaje'=>"No se pudo encontrar su comanda en el sistema.",
				'status'=>'ERROR'
			);
		}
		return $response->withJson($objDelaRespuesta, 200);
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
			$objDelaRespuesta = array(
				'mensaje'=>"Comanda eliminada.",
				'status'=>'OK'
			);
		} else {
			$objDelaRespuesta = array(
				'mensaje'=>"Error eliminando la comanda.",
				'status'=>'ERROR'
			);
		}
		return $response->withJson($objDelaRespuesta, 200);
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
		$micomanda->tipoFoto=$ArrayDeParametros['tipoFoto'];
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
				$objDelaRespuesta = array(
					'mensaje'=>"Clientes pagando.",
					'status'=>'OK'
				);
			} else {
				$objDelaRespuesta = array(
					'mensaje'=>$respuesta,
					'status'=>'ERROR'
				);
			}
		} else {
			$objDelaRespuesta = array(
				'mensaje'=>'Error encontrando la comanda seleccionada.',
				'status'=>'ERROR'
			);
		}
		return $response->withJson($objDelaRespuesta, 200);
	}
}