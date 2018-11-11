<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once './composer/vendor/autoload.php';
require_once './clases/AccesoDatos.php';
require_once './clases/MWparaAutentificar.php';
require_once './clases/AutentificadorJWT.php';
require_once './clases/login.php';
require_once './clases/IApiUsable.php';
require_once './clases/mesa.php';
require_once './clases/mesaApi.php';
require_once './clases/pedido.php';
require_once './clases/pedidoApi.php';
require_once './clases/comanda.php';
require_once './clases/comandaApi.php';
require_once './clases/empleado.php';
require_once './clases/empleadoApi.php';
require_once './clases/encuesta.php';
require_once './clases/encuestaApi.php';
require_once './clases/log.php';
require_once './clases/logApi.php';
$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$app = new \Slim\App(["settings" => $config]);

$app->post('/login/', \login::class . ':UserLogin');
$app->group('/api', function () use ($app) {
  $this->group('/comanda', function () use ($app) {
    $this->get('/', \comandaApi::class . ':TraerTodos');
    $this->get('/{codigoMesa}/{codigoComanda}', \comandaApi::class . ':TraerUno');
    $this->post('/', \comandaApi::class . ':CargarUno')->add(\MWparaAutentificar::class . ':VerificarMozo')->add(\MWparaAutentificar::class . ':VerificarToken');
    $this->post('/foto/{codigo}', \comandaApi::class . ':CargarFoto')->add(\MWparaAutentificar::class . ':VerificarMozo')->add(\MWparaAutentificar::class . ':VerificarToken');
    $this->post('/cobrar', \comandaApi::class . ':CobrarUno')->add(\MWparaAutentificar::class . ':VerificarMozo')->add(\MWparaAutentificar::class . ':VerificarToken');
    $this->delete('/{id}', \comandaApi::class . ':BorrarUno');
    $this->put('/{id}', \comandaApi::class . ':ModificarUno');
  });
  $app->group('/empleado', function () use ($app) {
    $this->get('/', \empleadoApi::class . ':TraerTodos')->add(\MWparaAutentificar::class . ':FiltrarSueldos');
    $this->get('/{id}', \empleadoApi::class . ':TraerUno')->add(\MWparaAutentificar::class . ':FiltrarSueldos')->add(\MWparaAutentificar::class . ':VerificarAdmin')->add(\MWparaAutentificar::class . ':VerificarToken');
    $this->get('/metricas/', \empleadoApi::class . ':TraerMetricas')->add(\MWparaAutentificar::class . ':VerificarAdmin')->add(\MWparaAutentificar::class . ':VerificarToken');
    $this->post('/', \empleadoApi::class . ':CargarUno');
    $this->post('/deshabilitar', \empleadoApi::class . ':DeshabilitarUno')->add(\MWparaAutentificar::class . ':VerificarAdmin')->add(\MWparaAutentificar::class . ':VerificarToken');
    $this->post('/activar', \empleadoApi::class . ':ActivarUno')->add(\MWparaAutentificar::class . ':VerificarAdmin')->add(\MWparaAutentificar::class . ':VerificarToken');
    $this->delete('/{id}', \empleadoApi::class . ':BorrarUno');
    $this->put('/{id}', \empleadoApi::class . ':ModificarUno');
  });
  $app->group('/mesa', function () use ($app) {
    $this->get('/', \mesaApi::class . ':TraerTodos');
    $this->get('/{id}', \mesaApi::class . ':TraerUno');
    $this->post('/', \mesaApi::class . ':CargarUno')->add(\MWparaAutentificar::class . ':VerificarAdmin')->add(\MWparaAutentificar::class . ':VerificarToken');
    $this->post('/cerrar', \mesaApi::class . ':CerrarUno')->add(\MWparaAutentificar::class . ':VerificarAdmin')->add(\MWparaAutentificar::class . ':VerificarToken');
    $this->delete('/{id}', \mesaApi::class . ':BorrarUno');
    $this->put('/{id}', \mesaApi::class . ':ModificarUno');
  });
  $app->group('/pedido', function () use ($app) {
    $this->get('/', \pedidoApi::class . ':TraerTodos')->add(\MWparaAutentificar::class . ':FiltrarPedidos')->add(\MWparaAutentificar::class . ':VerificarToken');
    $this->get('/{id}', \pedidoApi::class . ':TraerUno')->add(\MWparaAutentificar::class . ':FiltrarPedidos')->add(\MWparaAutentificar::class . ':VerificarToken');
    $this->post('/', \pedidoApi::class . ':CargarUno')->add(\MWparaAutentificar::class . ':VerificarMozo')->add(\MWparaAutentificar::class . ':VerificarToken');
    $this->post('/servir', \pedidoApi::class . ':EntregarACliente')->add(\MWparaAutentificar::class . ':VerificarMozo')->add(\MWparaAutentificar::class . ':VerificarToken');
    $this->post('/cancelar/{id}', \pedidoApi::class . ':CancelarUno')->add(\MWparaAutentificar::class . ':VerificarAdmin')->add(\MWparaAutentificar::class . ':VerificarToken');
    $this->post('/tomar', \pedidoApi::class . ':TomarUnPedido')->add(\MWparaAutentificar::class . ':VerificarEmpleado')->add(\MWparaAutentificar::class . ':VerificarToken');
    $this->post('/entregar', \pedidoApi::class . ':EntregarUnPedido')->add(\MWparaAutentificar::class . ':VerificarEmpleado')->add(\MWparaAutentificar::class . ':VerificarToken');
    $this->delete('/{id}', \pedidoApi::class . ':BorrarUno');
    $this->put('/{id}', \pedidoApi::class . ':ModificarUno');
  });
  $app->group('/encuesta', function () use ($app) {
    $this->get('/', \encuestaApi::class . ':TraerTodos');
    $this->get('/{id}', \encuestaApi::class . ':TraerUno');
    $this->post('/', \encuestaApi::class . ':CargarUno');
    $this->delete('/{id}', \encuestaApi::class . ':BorrarUno');
    $this->put('/{id}', \encuestaApi::class . ':ModificarUno');
  });
  $app->group('/log', function () use ($app) {
    $this->get('/', \logApi::class . ':TraerTodos');
    $this->get('/{id}', \logApi::class . ':TraerUno');
    $this->post('/', \logApi::class . ':CargarUno');
    $this->delete('/{id}', \logApi::class . ':BorrarUno');
    $this->put('/{id}', \logApi::class . ':ModificarUno');
  });
})->add(\MWparaAutentificar::class . ':VerificarUsuario');

$app->options('/{routes:.+}', function ($request, $response, $args) {
  return $response;
});

$app->add(function ($req, $res, $next) {
  $response = $next($req, $res);
  return $response
          ->withHeader('Access-Control-Allow-Origin', '*')
          ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
          ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
  $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
  return $handler($req, $res);
});

$app->run();