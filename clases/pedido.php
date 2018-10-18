<?php
class Pedido
{
    public $id;
    public $idComanda;
    public $sector;
    public $idEmpleado;
    public $descripcion;
    public $estado;
    public $estimacion;
    public $fechaIngresado;
    public $fechaEntregado;
    
    
    public function GetIdComanda() {
        return $this->idComanda;
    }
    public function GetSector() {
        return $this->sector;
    }
    public function GetIdEmpleado() {
        return $this->idEmpleado;
    }
    public function GetDescripcion() {
        return $this->descripcion;
    }
    public function GetEstado() {
        return $this->estado;
    }
    public function GetFechaIngresado() {
        return $this->fechaIngresado;
    }
    public function GetEstimacion() {
        return $this->estimacion;
    }
    public function GetFechaEntregado() {
        return $this->fechaEntregado;
    }

    public function SetIdComanda($value) {
        $this->idComanda = $value;
    }
    public function SetSector($value) {
        $this->sector = $value;
    }
    public function SetIdEmpleado($value) {
        $this->idEmpleado = $value;
    }
    public function SetDescripcion($value) {
        $this->descripcion = $value;
    }
    public function SetEstado($value) {
        $this->estado = $value;
    }
    public function SetFechaIngresado($value) {
        $this->fechaIngresado = $value;
    }
    public function SetEstimacion($value) {
        $now = date("Y-m-d H:i:s");
        $time = date("Y-m-d H:i:s",strtotime("+$value minutes",strtotime($now)));
        $this->estimacion = $time;
    }
    public function SetFechaEntregado($value) {
        $this->fechaEntregado = $value;
    }
    
    public function BorrarPedido() {
        $idComanda = $this->idComanda;
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("
            delete
            from pedidos
            WHERE id=$this->id");
        $consulta->execute();

        if ($consulta->rowCount()>0) {
            $comanda=Comanda::TraerComanda($idComanda);
            $todos_pedidos_listos = true;
            $pedidos_pendientes_de_comanda = Pedido::TraerPedidosPorComanda($idComanda);
            if (sizeof($pedidos_pendientes_de_comanda) > 0) {
                foreach ($pedidos_pendientes_de_comanda as $pedido) {
                    if (!($pedido->estado == 'entregado')) {
                        $todos_pedidos_listos = false;
                        break;
                    }
                }
                if ($todos_pedidos_listos) {
                    $mesa=Mesa::TraerMesa($comanda->idMesa);
                    $mesa->estado = 'con clientes comiendo';
                    $mesa->GuardarMesa();
                }
            } else {
                $mesa=Mesa::TraerMesa($comanda->idMesa);
                $mesa->estado = 'cerrada';
                $mesa->GuardarMesa();
            }
        }

        return $consulta->rowCount();
    }

    public function ModificarPedido() {
        if ($this->estimacion) {
            $estimacion = "'$this->estimacion'";
        } else {
            $estimacion = "NULL";
        }
        if ($this->fechaEntregado) {
            $fechaEntregado = "'$this->fechaEntregado'";
        } else {
            $fechaEntregado = "NULL";
        }
        if ($this->idEmpleado) {
            $idEmpleado = "'$this->idEmpleado'";
        } else {
            $idEmpleado = "NULL";
        }
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("
            update pedidos 
            set sector='$this->sector',
            idComanda='$this->idComanda',
            descripcion='$this->descripcion',
            estado='$this->estado',
            idEmpleado=$idEmpleado,
            estimacion=$estimacion,
            fechaIngresado='$this->fechaIngresado',
            fechaEntregado=$fechaEntregado
            WHERE id=$this->id");
        return $consulta->execute();
    }

    public static function CargarPedidos($arrayComanda, $comanda) {
        if ($arrayComanda['barra'] != '') {
            $pedido_nuevo = new Pedido();
            $pedido_nuevo->sector = 'barra';
            $pedido_nuevo->estado = 'pendiente';
            $pedido_nuevo->idComanda = $comanda;
            $pedido_nuevo->descripcion = $arrayComanda['barra'];
            $pedido_nuevo->InsertarPedido();
        }
        if ($arrayComanda['cerveza'] != '') {
            $pedido_nuevo = new Pedido();
            $pedido_nuevo->sector = 'cerveza';
            $pedido_nuevo->estado = 'pendiente';
            $pedido_nuevo->idComanda = $comanda;
            $pedido_nuevo->descripcion = $arrayComanda['cerveza'];
            $pedido_nuevo->InsertarPedido();
        }
        if ($arrayComanda['cocina'] != '') {
            $pedido_nuevo = new Pedido();
            $pedido_nuevo->sector = 'cocina';
            $pedido_nuevo->estado = 'pendiente';
            $pedido_nuevo->idComanda = $comanda;
            $pedido_nuevo->descripcion = $arrayComanda['cocina'];
            $pedido_nuevo->InsertarPedido();
        }
        if ($arrayComanda['candy'] != '') {
            $pedido_nuevo = new Pedido();
            $pedido_nuevo->sector = 'candy';
            $pedido_nuevo->estado = 'pendiente';
            $pedido_nuevo->idComanda = $comanda;
            $pedido_nuevo->descripcion = $arrayComanda['candy'];
            $pedido_nuevo->InsertarPedido();
        }
        return true;
    }

    public function InsertarPedido() {
        $datetime_now = date("Y-m-d H:i:s");
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("INSERT into pedidos
        (sector,descripcion,idComanda,estado,fechaIngresado)values
        ('$this->sector','$this->descripcion','$this->idComanda','$this->estado','$datetime_now')"
        );
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public function GuardarPedido() {
        if ($this->id > 0) {
            $this->ModificarPedido();
        } else {
            $this->InsertarPedido();
        }
    }

    public static function TraerPedidos() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("SELECT p.idComanda, p.sector, e.usuario as idEmpleado, p.id, p.descripcion, p.estado, p.estimacion, p.fechaIngresado, p.fechaEntregado FROM pedidos p LEFT JOIN empleados e on p.idEmpleado = e.id");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Pedido");
    }

    public static function TraerPedido($id) {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("select * from pedidos where id = $id");
        $consulta->execute();
        $pedidoResultado= $consulta->fetchObject('Pedido');
        return $pedidoResultado;
    }

    public static function TraerPedidosPorComanda($codigoComanda) {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta(
            "SELECT * FROM pedidos WHERE idComanda = '$codigoComanda'"
        );
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Pedido");
    }

    public function Cancelar() {
        if ($this->estado == 'entregado') {
            return "El pedido ya fue entregado";
        } else if ($this->estado == 'cancelado') {
            return "El pedido ya fue cancelado";
        } else {
            if ($this->estado == 'en preparación') {
                $empleado=Empleado::TraerEmpleado($this->idEmpleado);
                $empleado->estado = 'activo';
                $empleado->GuardarEmpleado();
            }
            $this->estado = 'cancelado';
            $this->GuardarPedido();
            $comanda=Comanda::TraerComanda($this->idComanda);
            $todos_pedidos_listos = true;
            $pedidos_pendientes_de_comanda = Pedido::TraerPedidosPorComanda($comanda->codigo);
            foreach ($pedidos_pendientes_de_comanda as $pedido) {
                if (!($pedido->estado == 'entregado' || $pedido->estado == 'cancelado')) {
                    $todos_pedidos_listos = false;
                    break;
                }
            }
            if ($todos_pedidos_listos) {
                $mesa=Mesa::TraerMesa($comanda->idMesa);
                $mesa->estado = 'con clientes comiendo';
                $mesa->GuardarMesa();
            }
            return "Pedido #$this->id cancelado.";
        }
    }

    public function toString() {
        return "Metodo mostar:".$this->sector."  ".$this->idEmpleado."  ".$this->descripcion;
    }
    
    public static function EntregarPedido($id) {
        $pedido = Pedido::TraerPedido($id);
        $pedido->estado = 'entregado';
        $pedido->GuardarPedido();
        $comanda=Comanda::TraerComanda($pedido->idComanda);
        $todos_pedidos_listos = true;
        $pedidos_pendientes_de_comanda = Pedido::TraerPedidosPorComanda($comanda->codigo);
        foreach ($pedidos_pendientes_de_comanda as $pedido) {
            if (!($pedido->estado == 'entregado')) {
                $todos_pedidos_listos = false;
                break;
            }
        }
        if ($todos_pedidos_listos) {
            $mesa=Mesa::TraerMesa($comanda->idMesa);
            $mesa->estado = 'con clientes comiendo';
            $mesa->GuardarMesa();
        }
        return "Pedido #$id entregado.";
    }
}