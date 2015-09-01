<?php
/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2015  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of regularizacion_stock
 *
 * @author carlos
 */
class regularizacion_stock extends fs_model
{
   /**
    * Clave primaria.
    * @var type 
    */
   public $id;
   
   /**
    * ID del stock, en el modelo stock.
    * @var type 
    */
   public $idstock;
   public $cantidadini;
   public $cantidadfin;
   
   /**
    * Código del almacén destino.
    * @var type 
    */
   public $codalmacendest;
   public $fecha;
   public $hora;
   public $motivo;
   
   /**
    * Nick del usuario que ha realizado la regularización.
    * @var type 
    */
   public $nick;
   
   public function __construct($r = FALSE, $o = 's')
   {
      parent::__construct('lineasregstocks', 'plugins/facturacion_base/');
	  if($o == 's') {
		  if($r)
		  {
			 $this->id = $this->intval($r['id']);
			 $this->idstock = $this->intval($r['idstock']);
			 $this->cantidadini = floatval($r['cantidadini']);
			 $this->cantidadfin = floatval($r['cantidadfin']);
			 $this->codalmacendest = $r['codalmacendest'];
			 $this->fecha = date('d-m-Y', strtotime($r['fecha']));
			 $this->hora = $r['hora'];
			 $this->motivo = $r['motivo'];
			 $this->nick = $r['nick'];
		  }
		  else
		  {
			 $this->id = NULL;
			 $this->idstock = NULL;
			 $this->cantidadini = 0;
			 $this->cantidadfin = 0;
			 $this->codalmacendest = NULL;
			 $this->fecha = date('d-m-Y');
			 $this->hora = date('H:i:s');
			 $this->motivo = '';
			 $this->nick = NULL;
		  }
	  } else {
	      if($r)
		  {
			 //$this->id = $this->intval($r['id']);
			 $this->kfecha = date('Y-m-d', strtotime($r['kfecha']));
			 $this->khora = $r['khora'];
			 $this->kdetalle = $r['kdetalle'];
			 $this->kmovimiento = $o;
			 $this->kcantidad = $r['kcantidad'];			 
		  }
		  else
		  {
			 //$this->id = NULL;
			 $this->kfecha = date('d-m-Y');
			 $this->khora = date('H:i:s');
			 $this->kdetalle = '';
			 $this->kmovimiento = $o;
			 $this->kcantidad = 0;
		  }
	  }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function get($id)
   {
      $data = $this->db->select("SELECT * FROM lineasregstocks WHERE id = ".$this->var2str($id).";");
      if($data)
      {
         return new regularizacion_stock($data[0],'s');
      }
      else
         return FALSE;
   }
   
   public function exists()
   {
      if( is_null($this->id) )
      {
         return FALSE;
      }
      else
         return $this->db->select("SELECT * FROM lineasregstocks WHERE id = ".$this->var2str($this->id).";");
   }
   
   public function save()
   {
      if( $this->exists() )
      {
         $sql = "UPDATE lineasregstocks SET idstock = ".$this->var2str($this->idstock).",
            cantidadini = ".$this->var2str($this->cantidadini).", cantidadfin = ".$this->var2str($this->cantidadfin).",
            codalmacendest = ".$this->var2str($this->codalmacendest).",
            fecha = ".$this->var2str($this->fecha).", hora = ".$this->var2str($this->hora).",
            motivo = ".$this->var2str($this->motivo).", nick = ".$this->var2str($this->nick)."
            WHERE id = ".$this->var2str($this->id).";";
         
         return $this->db->exec($sql);
      }
      else
      {
         $sql = "INSERT INTO lineasregstocks (idstock,cantidadini,cantidadfin,codalmacendest,fecha,hora,motivo,nick)
            VALUES (".$this->var2str($this->idstock).",".$this->var2str($this->cantidadini).",".$this->var2str($this->cantidadfin).",
             ".$this->var2str($this->codalmacendest).",".$this->var2str($this->fecha).",
             ".$this->var2str($this->hora).",".$this->var2str($this->motivo).",".$this->var2str($this->nick).");";
         
         if( $this->db->exec($sql) )
         {
            $this->id = $this->db->lastval();
            return TRUE;
         }
         else
            return FALSE;
      }
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM lineasregstocks WHERE id = ".$this->var2str($this->id).";");
   }
   
   public function all_from_articulo($ref)
   {
      $rlist = array();
      
      $data = $this->db->select("SELECT * FROM lineasregstocks WHERE idstock IN
         (SELECT idstock FROM stocks WHERE referencia = ".$this->var2str($ref).") ORDER BY fecha DESC, hora DESC;");
      if($data)
      {
         foreach($data as $d)
            $rlist[] = new regularizacion_stock($d);
      }
      
      return $rlist;
   }
   
   public function array_kardex($array, $on, $order=SORT_ASC)
	  {
		$new_array = array();
		$sortable_array = array();
	
		if (count($array) > 0) {
			foreach ($array as $k => $v) {
				if (is_array($v)) {
					foreach ($v as $k2 => $v2) {
						if ($k2 == $on) {
							$sortable_array[$k] = $v2;
						}
					}
				} else {
					$sortable_array[$k] = $v;
				}
			}
	
			switch ($order) {
				case SORT_ASC:
					asort($sortable_array);
				break;
				case SORT_DESC:
					arsort($sortable_array);
				break;
			}
	
			foreach ($sortable_array as $k => $v) {
				$new_array[$k] = $array[$k];
			}
		}
		
		return $new_array;
	}
  
   public function all_from_kardex($ref)
   {
	  $rlist = array();
      
      /*FHF Kardex - Regularizaciones*/
	  $data = $this->db->select("
		  SELECT 
			lineasregstocks.fecha AS kfecha, 
			lineasregstocks.hora AS khora, 
			lineasregstocks.motivo AS kdetalle, 
			lineasregstocks.cantidadfin AS kcantidad 
		  FROM lineasregstocks
		  INNER JOIN stocks ON lineasregstocks.idstock = stocks.idstock
		  WHERE stocks.referencia = ".$this->var2str($ref)."
		  ORDER BY lineasregstocks.fecha DESC, lineasregstocks.hora DESC"
	  );	  		 		
      if($data)
      {
         foreach($data as $d)
            $rlist[] = new regularizacion_stock($d,'i'); // i = input / entrada
      }
	  
	  /*FHF Kardex - Compras - Facturas de Proveedor*/
	  $data = $this->db->select("
		  SELECT 
			facturasprov.fecha AS kfecha, 
			facturasprov.hora AS khora, 
			facturasprov.codigo AS kdetalle, 
			lineasfacturasprov.cantidad AS kcantidad 
		  FROM lineasfacturasprov
		  INNER JOIN facturasprov ON lineasfacturasprov.idfactura = facturasprov.idfactura
		  WHERE lineasfacturasprov.referencia = ".$this->var2str($ref)."
		  ORDER BY facturasprov.fecha DESC, facturasprov.hora DESC"
	  ); 		
      if($data)
      {
         foreach($data as $d)
            $rlist[] = new regularizacion_stock($d,'i'); // o = output / salida
      }
	  /*FHF Kardex - Compras - Pedidos de Proveedor
	  $data = $this->db->select("SELECT * FROM lineasregstocks WHERE idstock IN
         (SELECT idstock FROM stocks WHERE referencia = ".$this->var2str($ref).") ORDER BY fecha DESC, hora DESC;");
	  		 		
      if($data)
      {
         foreach($data as $d)
            $rlist[] = new regularizacion_stock($d,'k');
      }
	  */
	  /*FHF Kardex - Ventas - Facturas de Cliente*/
	  $data = $this->db->select("
		  SELECT 
			facturascli.fecha AS kfecha, 
			facturascli.hora AS khora, 
			facturascli.codigo AS kdetalle, 
			lineasfacturascli.cantidad AS kcantidad 
		  FROM lineasfacturascli
		  INNER JOIN facturascli ON lineasfacturascli.idfactura = facturascli.idfactura
		  WHERE lineasfacturascli.referencia = ".$this->var2str($ref)."
		  ORDER BY facturascli.fecha DESC, facturascli.hora DESC"
	  ); 		
      if($data)
      {
         foreach($data as $d)
            $rlist[] = new regularizacion_stock($d,'o'); // o = output / salida
      }
	  /*FHF Kardex - Ventas - Pedidos de Cliente*/
	  $data = $this->db->select("
		  SELECT 
			pedidoscli.fecha AS kfecha, 
			pedidoscli.hora AS khora, 
			pedidoscli.codigo AS kdetalle, 
			lineaspedidoscli.cantidad AS kcantidad 
		  FROM lineaspedidoscli
		  INNER JOIN pedidoscli ON lineaspedidoscli.idpedido = pedidoscli.idpedido
		  WHERE lineaspedidoscli.referencia = ".$this->var2str($ref)."
		  ORDER BY pedidoscli.fecha DESC, pedidoscli.hora DESC"
	  ); 		
      if($data)
      {
         foreach($data as $d)
            $rlist[] = new regularizacion_stock($d,'o'); // o = output / salida
      }

	  return $this->array_kardex($rlist, 'kfecha'.'khora', SORT_DESC);
   }
}
