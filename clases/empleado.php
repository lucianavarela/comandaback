<?php
class Empleado
{
    public $id;
    public $usuario;
    public $clave;
    public $sector;
    public $estado;
    public $sueldo;
    public $cantidad;
    
    public function GetUsuario() {
        return $this->usuario;
    }
    public function GetClave() {
        return $this->clave;
    }
    public function GetSector() {
        return $this->sector;
    }
    public function GetEstado() {
        return $this->estado;
    }
    public function GetSueldo() {
        return $this->sueldo;
    }

    public function SetUsuario($value) {
        $this->usuario = $value;
    }
    public function SetClave($value) {
        $this->clave = $value;
    }
    public function SetSector($value) {
        $this->sector = $value;
    }
    public function SetEstado($value) {
        $this->estado = $value;
    }
    public function SetSueldo($value) {
        $this->sueldo = $value;
    }
    
    public function BorrarEmpleado() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("
            delete
            from empleados
            WHERE id=$this->id");
        $consulta->execute();
        return $consulta->rowCount();
    }

    public function ModificarEmpleado() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("
            update empleados 
            set usuario='$this->usuario',
            clave='$this->clave',
            sector='$this->sector',
            estado='$this->estado',
            sueldo=$this->sueldo
            WHERE id=$this->id;");
        return $consulta->execute();
    }

    public function InsertarEmpleado() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("INSERT
        into empleados (usuario,clave,sector,estado,sueldo)
        values('$this->usuario','$this->clave','$this->sector','$this->estado',$this->sueldo)");
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public function GuardarEmpleado() {
        if ($this->id > 0) {
            $this->ModificarEmpleado();
        } else {
            $this->InsertarEmpleado();
        }
    }

    public function DeshabilitarEmpleado() {
        $this->estado = "deshabilitado";
        $this->GuardarEmpleado();
    }

    public function ActivarEmpleado() {
        $this->estado = "activo";
        $this->GuardarEmpleado();
    }

    public static function TraerEmpleados() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT e.id as id, e.usuario as usuario, e.clave as clave, COUNT(l.id) as cantidad, e.sector as sector, e.estado as estado, e.sueldo as sueldo 
            FROM logs l RIGHT JOIN empleados e ON l.idEmpleado=e.id GROUP BY e.id"
        );
        $consulta->execute();
        $empleados = $consulta->fetchAll(PDO::FETCH_CLASS, "Empleado");
        return $empleados;
    }

    public static function TraerEmpleado($id) {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("select * from empleados where id = $id");
        $consulta->execute();
        $empleadoResultado= $consulta->fetchObject('Empleado');
        return $empleadoResultado;
    }

    public static function Analytics() {
        $listaAnalytics= array();
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();

        //7b
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT COUNT(l.id) as cantidad, e.sector as sector FROM logs l INNER JOIN empleados e ON l.idEmpleado=e.id GROUP BY e.sector"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $rows = array();
            foreach($resultado as $row) {
                $rowObj = new stdclass();
                $rowObj->cantidad = $row['cantidad'];
                $rowObj->sector = $row['sector'];
                array_push($rows, $rowObj);
            }
            $listaAnalytics['7b-logs_por_sector'] = $rows;
        }

        //8a
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT COUNT(id) as cantidad, descripcion as pedido FROM pedidos GROUP BY pedido ORDER BY cantidad DESC LIMIT 1"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $result = new stdclass();
            $result->pedido = $resultado[0]['pedido'];
            $result->cantidad = $resultado[0]['cantidad'];
            $listaAnalytics['8a-más_pedido'] = $result;
        }

        //8b
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT COUNT(id) as cantidad, descripcion as pedido FROM pedidos GROUP BY pedido ORDER BY cantidad LIMIT 1"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $result = new stdclass();
            $result->pedido = $resultado[0]['pedido'];
            $result->cantidad = $resultado[0]['cantidad'];
            $listaAnalytics['8b-menos_pedido'] = $result;
        }

        //8c
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT id, idEmpleado, estimacion, fechaEntregado, TIMESTAMPDIFF(MINUTE, estimacion, fechaEntregado) AS diff FROM pedidos WHERE TIMESTAMPDIFF(MINUTE, estimacion, fechaEntregado) > 0"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $rows = array();
            foreach($resultado as $row) {
                $rowObj = new stdclass();
                $rowObj->id = $row['id'];
                $rowObj->empleado = $row['idEmpleado'];
                $rowObj->estimacion = $row['estimacion'];
                $rowObj->entrega = $row['fechaEntregado'];
                $rowObj->demora = $row['diff'] . " minutos";
                array_push($rows, $rowObj);
            }
            $listaAnalytics['8c-pedidos_demorados'] = $rows;
        }

        //8d
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT id, descripcion FROM pedidos WHERE estado = 'cancelado'"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $rows = array();
            foreach($resultado as $row) {
                $rowObj = new stdclass();
                $rowObj->id = $row['id'];
                $rowObj->descripcion = $row['descripcion'];
                array_push($rows, $rowObj);
            }
            $listaAnalytics['8d-pedidos_cancelados'] = $rows;
        }

        //9a
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT COUNT(id) as cantidad, idMesa as mesa FROM comandas GROUP BY idMesa ORDER BY cantidad DESC LIMIT 1"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $result = new stdclass();
            $result->mesa = $resultado[0]['mesa'];
            $result->cantidad = $resultado[0]['cantidad'];
            $listaAnalytics['9a-mesa_mas_usada'] = $result;
        }

        //9b
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT COUNT(id) as cantidad, idMesa as mesa FROM comandas GROUP BY idMesa ORDER BY cantidad LIMIT 1"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $result = new stdclass();
            $result->mesa = $resultado[0]['mesa'];
            $result->cantidad = $resultado[0]['cantidad'];
            $listaAnalytics['9b-mesa_menos_usada'] = $result;
        }

        //9c
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT SUM(importe) as importe, idMesa as mesa FROM comandas WHERE importe is not Null GROUP BY idMesa ORDER BY importe DESC LIMIT 1"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $result = new stdclass();
            $result->mesa = $resultado[0]['mesa'];
            $result->importe = $resultado[0]['importe'];
            $listaAnalytics['9c-mesa_mas_paga'] = $result;
        }

        //9d
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT SUM(importe) as importe, idMesa as mesa FROM comandas WHERE importe is not Null GROUP BY idMesa ORDER BY importe LIMIT 1"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $result = new stdclass();
            $result->mesa = $resultado[0]['mesa'];
            $result->importe = $resultado[0]['importe'];
            $listaAnalytics['9d-mesa_menos_paga'] = $result;
        }

        //9e
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT importe, idMesa as mesa FROM comandas ORDER BY importe DESC LIMIT 1"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $result = new stdclass();
            $result->mesa = $resultado[0]['mesa'];
            $result->importe = $resultado[0]['importe'];
            $listaAnalytics['9e-mesa_importe_mas_alto'] = $result;
        }

        //9f
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT importe, idMesa as mesa FROM comandas WHERE importe is not Null ORDER BY importe LIMIT 1"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $result = new stdclass();
            $result->mesa = $resultado[0]['mesa'];
            $result->importe = $resultado[0]['importe'];
            $listaAnalytics['9f-mesa_importe_mas_bajo'] = $result;
        }

        //9g
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT SUM(importe) as importe, idMesa as mesa
            FROM comandas WHERE importe is not Null
            AND codigo IN (SELECT idComanda FROM pedidos WHERE fechaIngresado >= '2018-06-25' AND fechaIngresado <= '2018-07-10')
            AND idMesa = 'g8sve' GROUP BY idMesa ORDER BY importe"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $result = new stdclass();
            $result->mesa = $resultado[0]['mesa'];
            $result->importe = $resultado[0]['importe'];
            $listaAnalytics['9g-recaudacion_mesa_entre_fechas'] = $result;
        }
        
        //9h
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT id,puntosMozo,puntosMesa,puntosRestaurante,puntosCocinero, (AVG(puntosMozo)+AVG(puntosMesa)+AVG(puntosRestaurante)+AVG(puntosCocinero))/4 as promedio, comentario
            FROM encuestas GROUP BY id HAVING promedio > 5"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $rows = array();
            foreach($resultado as $row) {
                $rowObj = new stdclass();
                $rowObj->promedio = $row['promedio'];
                $rowObj->Restaurante = $row['puntosRestaurante'];
                $rowObj->Mozo = $row['puntosMozo'];
                $rowObj->Mesa = $row['puntosMesa'];
                $rowObj->Cocinero = $row['puntosCocinero'];
                $rowObj->comentario = $row['comentario'];
                array_push($rows, $rowObj);
            }
            $listaAnalytics['9h-mejores_comentarios'] = $rows;
        }

        //9i
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT id,puntosMozo,puntosMesa,puntosRestaurante,puntosCocinero, (AVG(puntosMozo)+AVG(puntosMesa)+AVG(puntosRestaurante)+AVG(puntosCocinero))/4 as promedio, comentario
            FROM encuestas GROUP BY id HAVING promedio <= 5"
        );
        $consulta->execute();
        $resultado= $consulta->fetchAll();
        if ($resultado) {
            $rows = array();
            foreach($resultado as $row) {
                $rowObj = new stdclass();
                $rowObj->promedio = $row['promedio'];
                $rowObj->Restaurante = $row['puntosRestaurante'];
                $rowObj->Mozo = $row['puntosMozo'];
                $rowObj->Mesa = $row['puntosMesa'];
                $rowObj->Cocinero = $row['puntosCocinero'];
                $rowObj->comentario = $row['comentario'];
                array_push($rows, $rowObj);
            }
            $listaAnalytics['9i-peores_comentarios'] = $rows;
        }
        return $listaAnalytics;
    }

    public static function ValidarEmpleado($usuario, $clave) {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("select * from empleados where usuario='$usuario' and clave='$clave'");
        $consulta->execute();
        $empleadoResultado= $consulta->fetchObject('Empleado');
        return $empleadoResultado;
    }

    public function TomarPedido($pedido, $tiempo) {
        $this->estado = 'ocupado';
        $this->GuardarEmpleado();
        $pedido = Pedido::TraerPedido($pedido);
        $pedido->SetEstimacion($tiempo);
        $pedido->idEmpleado = $this->id;
        $pedido->estado = 'en preparación';
        $pedido->GuardarPedido();
        return "Se le ha asignado el pedido para la comanda #".$pedido->GetIdComanda().
        "\nDetalles del pedido: ".$pedido->GetDescripcion();
    }

    public static function PedidoPreparado($id) {
        $pedido = Pedido::TraerPedido($id);
        $pedido->estado = 'listo para servir';
        $pedido->fechaEntregado = date("Y-m-d H:i:s");
        $pedido->GuardarPedido();
        $empleado=Empleado::TraerEmpleado($pedido->idEmpleado);
        $empleado->estado = 'activo';
        $empleado->GuardarEmpleado();
        return "Pedido #$id entregado.";
    }

    public function toString() {
        return "Metodo mostar:".$this->usuario."  ".$this->clave."  ".$this->sector;
    }
}