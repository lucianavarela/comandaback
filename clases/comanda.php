<?php
class Comanda
{
    public $id;
    public $nombreCliente;
    public $codigo;
    public $importe;
    public $idMesa;
    public $foto;
    
    public function GetNombreCliente() {
        return $this->nombreCliente;
    }
    public function GetCodigo() {
        return $this->codigo;
    }
    public function GetImporte() {
        return $this->importe;
    }
    public function GetIdMesa() {
        return $this->idMesa;
    }
    public function GetFoto() {
        return $this->foto;
    }
    public function SetNombreCliente($value) {
        $this->nombreCliente = $value;
    }
    public function SetCodigo($value) {
        $this->codigo = $value;
    }
    public function SetImporte($value) {
        $this->importe = (float)$value;
    }
    public function SetIdMesa($value) {
        $this->idMesa = $value;
    }
    public function SetFoto($value) {
        $this->foto = $value;
    }

    public function __construct(){}
    
    public function InsertarComanda() {
        $nuevoCodigo = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 5);
        $mesa = Mesa::TraerMesa($this->idMesa);
        if ($mesa && $mesa->GetEstado() == 'Cerrada') {
            $mesa->SetEstado('con clientes esperando pedido');
            $mesa->GuardarMesa();
            $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
            $consulta =$objetoAccesoDato->RetornarConsulta("INSERT into comandas (nombreCliente,codigo,idMesa,foto)
                values(
                '$this->nombreCliente',
                '$nuevoCodigo',
                '$this->idMesa',
                '$this->foto'
                );");
            $consulta->execute();
            return $nuevoCodigo;
        } else {
            return NULL;
        }
    }

    public function ModificarComanda() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("
            update comandas 
            set nombreCliente='$this->nombreCliente',
            codigo='$this->codigo',
            importe='$this->importe',
            idMesa='$this->idMesa',
            foto='$this->foto'
            WHERE id=$this->id;");
        return $consulta->execute();
    }

    public function GuardarComanda() {
        if ($this->id > 0) {
            $this->ModificarComanda();
        } else {
            $codigo = $this->InsertarComanda();
            return $codigo;
        }
    }

    public function BorrarComanda() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("
            delete
            from comandas
            WHERE id=$this->id;");
        $consulta->execute();
        return $consulta->rowCount();
    }

    public static function TraerComandas() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("select * from comandas;");
        $consulta->execute();
        $comandas = $consulta->fetchAll(PDO::FETCH_CLASS, "Comanda");
        return $comandas;
    }

    public static function TraerComanda($codigoComanda) {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("select * from comandas where codigo = '$codigoComanda';");
        $consulta->execute();
        $comandaResultado= $consulta->fetchObject('Comanda');
        return $comandaResultado;
    }

    public function CobrarComanda($importe) {
        $mesa = Mesa::TraerMesa($this->idMesa);
        if ($mesa) {
            if ($mesa->estado == 'con clientes comiendo') {
                $mesa->SetEstado('con clientes pagando');
                $mesa->GuardarMesa();
                $this->SetImporte($importe);
                $this->GuardarComanda();
                return 'OK';
            } else if ($mesa->estado == 'con clientes pagando') {
                return 'Estos clientes ya estan pagando';
            } else if ($mesa->estado == 'con clientes esperando pedido') {
                return 'Estos clientes aún están esperando pedido/s';
            } else {
                return 'Esta comanda ya ha sido cerrada';
            }
        }
        return 'Error encontrando la mesa de su comanda';
    }

    public function toString() {
        return "\nComanda #$this->codigo: $this->nombreCliente (Mesa #$this->idMesa)";
    }
}