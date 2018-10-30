<?php
class MWparaAutentificar
{
 /**
   * @api {any} /MWparaAutenticar/  Verificar Usuario
   * @apiVersion 0.1.0
   * @apiName VerificarUsuario
   * @apiGroup MIDDLEWARE
   * @apiDescription  Por medio de este MiddleWare verifico las credeciales antes de ingresar al correspondiente metodo 
   *
   * @apiParam {ServerRequestInterface} request  El objeto REQUEST.
 * @apiParam {ResponseInterface} response El objeto RESPONSE.
 * @apiParam {Callable} next  The next middleware callable.
   *
   * @apiExample Como usarlo:
   *    ->add(\MWparaAutenticar::class . ':VerificarUsuario')
   */
	public function VerificarUsuario($request, $response, $next) {
		$objDelaRespuesta= new stdclass();
		$objDelaRespuesta->esValido=false;
		if($request->isGet() || $request->isPost()) {
			$arrayConToken = $request->getHeader('HTTP_AUTHORIZATION');
			if($arrayConToken) {
				$token=$arrayConToken[0];
				try {
					AutentificadorJWT::VerificarToken($token);
					$objDelaRespuesta->esValido=true;
				} catch (Exception $e) {
					$objDelaRespuesta->excepcion=$e->getMessage();
					$objDelaRespuesta->esValido=false;
				}
			}
			if($objDelaRespuesta->esValido) {
				$payload=AutentificadorJWT::ObtenerData($token);
				var_dump($payload);
				$request = $request->withAttribute('empleado', $payload);
			}
            $response = $next($request, $response);
		} else {
            $arrayConToken = $request->getHeader('HTTP_AUTHORIZATION');
            $token=$arrayConToken[0];
            $objDelaRespuesta->esValido=true;
            
			try {
				AutentificadorJWT::VerificarToken($token);
				$objDelaRespuesta->esValido=true;
			} catch (Exception $e) {
				$objDelaRespuesta->excepcion=$e->getMessage();
				$objDelaRespuesta->esValido=false;
			}
			if($objDelaRespuesta->esValido) {
				$payload=AutentificadorJWT::ObtenerData($token);
				if($payload->sector=="management")
				{
					$response = $next($request, $response);
				}
				else
				{
					$objDelaRespuesta = array(
						'status'=>'ERROR',
						'mensaje'=>"Solo socios"
					);
					return $response->withJson($objDelaRespuesta, 200);
				}
			} else {
				$objDelaRespuesta = array(
					'status'=>'ERROR',
					'mensaje'=>"Solo usuarios registrados"
				);
				return $response->withJson($objDelaRespuesta, 200);
			}
		}

        return $response;
	}
	
	public function VerificarToken($request, $response, $next) {
        
		$objDelaRespuesta= new stdclass();
		$arrayConToken = $request->getHeader('HTTP_AUTHORIZATION');
		$token=$arrayConToken[0];
		$objDelaRespuesta->esValido=true;
		
		try {
			AutentificadorJWT::VerificarToken($token);
			$objDelaRespuesta->esValido=true;
		} catch (Exception $e) {
			$objDelaRespuesta->excepcion=$e->getMessage();
			$objDelaRespuesta->esValido=false;
		}
		if($objDelaRespuesta->esValido) {
			$payload=AutentificadorJWT::ObtenerData($token);
			$request = $request->withAttribute('empleado', $payload);
			$response = $next($request, $response);
		} else {
			$objDelaRespuesta = array(
				'status'=>'ERROR',
				'mensaje'=>"Por favor logueese para realizar esta accion."
			);
			return $response->withJson($objDelaRespuesta, 200);
		}

        return $response;
	}

	public function VerificarAdmin($request, $response, $next) {
		$objDelaRespuesta= new stdclass();
		$sector = $request->getAttribute('empleado')->sector;
		if($sector == "management") {
			$response = $next($request, $response);
		}
		else
		{
			$objDelaRespuesta = array(
				'status'=>'ERROR',
				'mensaje'=>"Solo socios."
			);
			return $response->withJson($objDelaRespuesta, 200);
		}
        
        return $response;
	}

	public function VerificarEmpleado($request, $response, $next) {
		$objDelaRespuesta= new stdclass();
		$sector = $request->getAttribute('empleado')->sector;
		if($sector == "barra" || $sector == "cerveza" || $sector == "cocina" || $sector == "candy") {
			$response = $next($request, $response);
		}
		else
		{
			$objDelaRespuesta = array(
				'status'=>'ERROR',
				'mensaje'=>"Solo empleados."
			);
			return $response->withJson($objDelaRespuesta, 200);
		}

        return $response;
	}

	public function VerificarMozo($request, $response, $next) {
		$objDelaRespuesta= new stdclass();
		$sector = $request->getAttribute('empleado')->sector;
		if($sector == "mozo") {
			$response = $next($request, $response);
		}
		else
		{
			$objDelaRespuesta = array(
				'status'=>'ERROR',
				'mensaje'=>"Solo mozos."
			);
			return $response->withJson($objDelaRespuesta, 200);
		}

        return $response;
	}
	
	public function FiltrarSueldos($request, $response, $next) {
		$objDelaRespuesta= new stdclass();
		$objDelaRespuesta->esValido=false;
		$arrayConToken = $request->getHeader('HTTP_AUTHORIZATION');
		if(sizeof($arrayConToken) > 0) {
			$token=$arrayConToken[0];
			try {
				AutentificadorJWT::VerificarToken($token);
				$objDelaRespuesta->esValido=true;
			} catch (Exception $e) {
				$objDelaRespuesta->excepcion=$e->getMessage();
				$objDelaRespuesta->esValido=false;
			}
		}
		if($objDelaRespuesta->esValido) {
			$payload=AutentificadorJWT::ObtenerData($token);
			$response = $next($request, $response);
			if($payload->sector != "management") {
				$empleados = json_decode($response->getBody()->__toString());
				if (is_array($empleados)) {
					foreach ($empleados as $empleado) {
						unset($empleado->sueldo);
					}
				} else {
					unset($empleados->sueldo);
				}
				$nueva=$response->withJson($empleados, 200);
				return $nueva;
			}
		} else {
			$response = $next($request, $response);
			$empleados = json_decode($response->getBody()->__toString());
			if (is_array($empleados)) {
				foreach ($empleados as $empleado) {
					unset($empleado->sueldo);
				}
			} else {
				unset($empleados->sueldo);
			}
			$nueva=$response->withJson($empleados, 200);
			return $nueva;
		}
		
        return $response;
	}
	
	public function FiltrarPedidos($request, $response, $next) {
		$objDelaRespuesta= new stdclass();
		$usuarioEmpleado = $request->getAttribute('empleado')->usuario;
		$sector = $request->getAttribute('empleado')->sector;
		if($sector == "barra" || $sector == "cerveza" || $sector == "cocina" || $sector == "candy") {
			$response = $next($request, $response);
			$pedidos = json_decode($response->getBody()->__toString());
			if (is_array($pedidos)) {
				foreach ($pedidos as $key => $pedido) {
					if (!($pedido->sector == $sector && ($pedido->estado == 'pendiente' || ($pedido->estado == 'en preparación' && $pedido->idEmpleado == $usuarioEmpleado)))) {
						unset($pedidos[$key]);
					}
				}
			} else {
				if ($pedidos->sector != $sector) {
					$pedidos = [];
				}
			}
			$nueva=$response->withJson($pedidos, 200);
			return $nueva;
		} else if($sector == "mozo") {
			$response = $next($request, $response);
			$pedidos = json_decode($response->getBody()->__toString());
			if (is_array($pedidos)) {
				foreach ($pedidos as $key => $pedido) {
					if ($pedido->estado != 'listo para servir') {
						unset($pedidos[$key]);
					}
				}
			} else {
				if ($pedidos->sector != $sector) {
					$pedidos = [];
				}
			}
			$nueva=$response->withJson($pedidos, 200);
			return $nueva;
			return $response;
		} else if($sector == "management") {
			$response = $next($request, $response);
			return $response;
		} else {
			$objDelaRespuesta = array(
				'status'=>'ERROR',
				'mensaje'=>"Solo usuarios."
			);
			return $response->withJson($objDelaRespuesta, 200);
		}

        return $response;
	}
}