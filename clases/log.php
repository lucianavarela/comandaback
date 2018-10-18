<?php
class Log
{
    public $id;
    public $idEmpleado;
    public $fecha;
    public $accion;
    
    public function GetIdEmpleado() {
        return $this->idEmpleado;
    }
    public function GetFecha() {
        return $this->fecha;
    }
    public function GetAccion() {
        return $this->accion;
    }

    public function SetIdEmpleado($value) {
        $this->idEmpleado = $value;
    }
    public function SetFecha($value) {
        $this->fecha = $value;
    }
    public function SetAccion($value) {
        $this->accion = $value;
    }
    
    public function BorrarLog() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("
            delete
            from logs
            WHERE id=$this->id");
        $consulta->execute();
        return $consulta->rowCount();
    }

    public function ModificarLog() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("
            update logs 
            set idEmpleado='$this->idEmpleado',
            fecha='$this->fecha',
            accion='$this->accion'
            WHERE id=$this->id");
        return $consulta->execute();
    }

    public function InsertarLog() {
        $datetime_now = date("Y-m-d H:i:s");
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("INSERT into logs (idEmpleado,fecha,accion)values($this->idEmpleado,'$datetime_now','$this->accion')");
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public function GuardarLog() {
        if ($this->id > 0) {
            $this->ModificarLog();
        } else {
            $this->InsertarLog();
        }
    }

    public static function TraerLogs() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("select l.id, e.usuario as idEmpleado, l.fecha,l.accion FROM logs l LEFT JOIN empleados e on l.idEmpleado = e.id");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Log");
    }

    public static function TraerLog($id) {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("select * FROM logs where id=$id");
        $consulta->execute();
        $logResultado= $consulta->fetchObject('Log');
        return $logResultado;
    }

    public function toString() {
        return "Metodo mostar:".$this->idEmpleado."  ".$this->fecha."  ".$this->accion;
    }
}