<?php
class Encuesta
{
    public $id;
    public $idComanda;
    public $puntosMozo;
    public $puntosMesa;
    public $puntosRestaurante;
    public $puntosCocinero;
    public $comentario;
    
    public function GetIdComanda() {
        return $this->idComanda;
    }
    public function GetPuntosMozo() {
        return $this->puntosMozo;
    }
    public function GetPuntosMesa() {
        return $this->puntosMesa;
    }
    public function GetPuntosRestaurante() {
        return $this->puntosRestaurante;
    }
    public function GetPuntosCocinero() {
        return $this->puntosCocinero;
    }
    public function GetComentario() {
        return $this->comentario;
    }

    public function SetIdComanda($value) {
        $this->idComanda = $value;
    }
    public function SetPuntosMozo($value) {
        $this->puntosMozo = $value;
    }
    public function SetPuntosMesa($value) {
        $this->puntosMesa = $value;
    }
    public function SetPuntosRestaurante($value) {
        $this->puntosRestaurante = $value;
    }
    public function SetPuntosCocinero($value) {
        $this->puntosCocinero = $value;
    }
    public function SetComentario($value) {
        $this->comentario = $value;
    }
    
    public function BorrarEncuesta() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("
            delete
            from encuestas
            WHERE id=$this->id");
        $consulta->execute();
        return $consulta->rowCount();
    }

    public function ModificarEncuesta() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("
            update encuestas 
            set idComanda='$this->idComanda',
            puntosMozo='$this->puntosMozo',
            puntosMesa='$this->puntosMesa',
            puntosRestaurante='$this->puntosRestaurante',
            puntosCocinero='$this->puntosCocinero',
            comentario='$this->comentario'
            WHERE id=$this->id");
        return $consulta->execute();
    }

    public function InsertarEncuesta() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("INSERT into encuestas (idComanda,puntosMozo,puntosMesa,puntosRestaurante,puntosCocinero,comentario)values
        ('$this->idComanda','$this->puntosMozo','$this->puntosMesa','$this->puntosRestaurante','$this->puntosCocinero','$this->comentario')");
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public function GuardarEncuesta() {
        if ($this->id > 0) {
            $this->ModificarEncuesta();
        } else {
            $this->InsertarEncuesta();
        }
    }

    public static function TraerEncuestas() {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("select * from encuestas");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Encuesta");
    }

    public static function TraerEncuesta($id) {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("select * from encuestas where id = $id");
        $consulta->execute();
        $encuestaResultado= $consulta->fetchObject('Encuesta');
        return $encuestaResultado;
    }

    public function toString() {
        return "Metodo mostar:".$this->idComanda."  ".$this->puntosMozo."  ".$this->puntosMesa;
    }
}