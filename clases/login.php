<?php
class Login
{
    public static function UserLogin($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
        $empleado = Empleado::ValidarEmpleado($ArrayDeParametros['usuario'], $ArrayDeParametros['clave']);
        if($empleado) {
            $token = AutentificadorJWT::CrearToken($empleado);
            $objDelaRespuesta = array(
                'token'=>$token,
                'status'=>'OK'
            );
        } else {
			$objDelaRespuesta = array(
                'mensaje'=>'Usuario inexistente',
                'status'=>'ERROR'
            );
        }
        return $response->withJson($objDelaRespuesta, 200);
    }
}