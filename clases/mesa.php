<?php
class Mesa
{
    public $id;
    public $codigo;
    public $estado;
    
    public function GetCodigo() {
        return $this->codigo;
    }
    public function GetEstado() {
        return ucwords($this->estado);
    }

    public function SetCodigo($value) {
        $this->codigo = $value;
    }
    public function SetEstado($value) {
        $this->estado = $value;
    }
    
    public function __construct(){}

    public function BorrarMesa() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("
            delete
            from mesas
            WHERE id=$this->id");
        $consulta->execute();
        return $consulta->rowCount();
    }

    public function ModificarMesa() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("
            update mesas 
            set codigo='$this->codigo',
            estado='$this->estado'
            WHERE id=$this->id");
        return $consulta->execute();
    }

    public function InsertarMesa() {
        $nuevoCodigo = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 5);
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("INSERT into mesas (codigo,estado)values('$nuevoCodigo','$this->estado')");
        $consulta->execute();
        return $nuevoCodigo;
    }

    public function GuardarMesa() {
        if ($this->id > 0) {
            $this->ModificarMesa();
        } else {
            $this->InsertarMesa();
        }
    }

    public static function TraerMesas() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("select * from mesas;");
        $consulta->execute();
        $mesas = $consulta->fetchAll(PDO::FETCH_CLASS, "Mesa");
        foreach($mesas as $mesa) {
            //echo $mesa->toString();
        }
        return $mesas;
    }

    public static function TraerMesa($id) {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("select * from mesas where codigo = '$id'");
        $consulta->execute();
        $mesaResultado= $consulta->fetchObject('Mesa');
        if ($mesaResultado) {
            //echo $mesaResultado->toString();
        }
        return $mesaResultado;
    }

    public function CerrarMesa() {
        $this->estado = "cerrada";
        $this->GuardarMesa();
    }

    public function toString() {
        return "\nMesa #$this->codigo -> ".$this->GetEstado();
    }
}