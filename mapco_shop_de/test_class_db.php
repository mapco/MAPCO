<?php

include("config.php");

class db_table_dataobject
{
	public $db;
	public $table;
	
	public $res;
	
	private $datafield = array();
	private $structure = array();
	
	private $pointer = 0;
	
	public $column_count;
	
	
	public function __construct($database, $dbtable, $query = "")
	{
		if ($database != "" && $dbtable != "")
		{
			$this->db = $database;
			$this->table = $dbtable;
		}
		else return false;
		
		//CHECK IF TABLE EXISTS
		$res = q("SHOW TABLES LIKE '".$dbtable."';", $database, __FILE__, __LINE__);
		echo mysqli_error();
		if (mysqli_num_rows($res)==0) return false;
		
		if (!is_null($query))
		{
			$this->load_data($query);
		}
	}
	
	public function load_data ($query = "") 
	{
		
		// GET STRUCTURE OF TABEL
		$res_struct=q("SHOW COLUMNS FROM ".$this->table.";", $this->db, __FILE__, __LINE__);
		while($struct=mysqli_fetch_assoc($res_struct))
		{
			$this->structure[$struct["Field"]] = $struct;
		}
		//print_r($this->structure);
		if ($query == "")
		{
			$res = q("SELECT * FROM ".$this->table.";", $this->db, __FILE__, __LINE__);
		}
		else
		{
			$res = q("SELECT * FROM ".$this->table." WHERE ".$query.";", $this->db, __FILE__, __LINE__);
		}
	
		$this->column_count = mysqli_num_rows($res);
		
		while ($row = mysqli_fetch_assoc($res))
		{
			$this->datafield[]=$row;
		}
		
	}
	
	public function get_data_array($index = "", $fields = array() )
	{
	}
	
	public function get_data_assoc_num($fields = array())
	{
		$data=array();
		
		if (sizeof($fields)>0)
		{
			$fieldcheck = true;
		}
		else
		{
			$fieldcheck = false;
		}
		foreach($this->datafield as $dataset)
		{
			$prikey=sizeof($data);

			while (list ($key, $val)  = each ($dataset))
			{
				if (!$fieldcheck || ($fieldcheck && in_array($key, $fields)) )
				{	
					$data[$prikey][$key]=$val;
				}
			}
		}
		
		return $data;

	}
	
	public function get_data_assoc_key($index = "", $fields = array() )
	{
		$data=array();
		
		if (sizeof($fields)>0)
		{
			$fieldcheck = true;
		}
		else
		{
			$fieldcheck = false;
		}
		
		if ($index=="")
		{
			return $this->datafield;
		}
		else
		{
			//CHECK IF INDEX IS IN STRUCTFIELDS
			if (!isset($this->structure[$index]))
			{
				return false;
			}
			else
			{
				//CHECK IF INDEX IS PRIMARY KEY
				if ($this->structure[$index]["Key"]=="PRI")
				{
					foreach($this->datafield as $dataset)
					{
						$prikey=$dataset[$index];
						while (list ($key, $val)  = each ($dataset))
						{
							if (!$fieldcheck || ($fieldcheck && in_array($key, $fields)) )
							{	
								if ($key != $index)
								{
									$data[$prikey][$key]=$val;
								}
							}
						}
					}
				}
				else
				//INDEX IS NOT PRIMARY KEY
				{
					foreach($this->datafield as $dataset)
					{
						$prikey=$dataset[$index];
						while (list ($key, $val)  = each ($dataset))
						{
							if (!$fieldcheck || ($fieldcheck && in_array($key, $fields)) )
							{	
								if ($key != $index)
								{
									if (!isset($data[$prikey]))
									{
										$data[$prikey][0][$key]=$val;
									}
									else
									{
										$data[$prikey][sizeof($data[$prikey])][$key]=$val;
									}
								}
							}
						}
					}
					
				}
				
			}
			return $data;			
		}

	}
	
	public function get_data_rows($index = "", $fields = array() )
	{
	}
	
	public function fetch_array()
	{
	}

	public function fetch_assoc($fields = array() )
	{
		$i = $this->pointer;
		
		$data=array();
		
		if (sizeof($fields)>0)
		{
			$fieldcheck = true;
		}
		else
		{
			$fieldcheck = false;
		}

		if ($i < sizeof($this->datafield))
		{
			while (list ($key, $val) = each ($this->datafield[$i]))
			{
				if (!$fieldcheck || ($fieldcheck && in_array($key, $fields)) )
				{	
					$data[$key]=$val;
				}
			}
			
			$this->pointer++;
			
			return $data;
		}
		else
		{
			$this->pointer=0;
			return false;
		}
	}

	public function fetch_row()
	{
	}
	
	public function show_result_count()
	{
		return $this->column_count;
	}
	
	public function pointer_reset()
	{
		$this->pointer=0;
	}
	

}

$shop_orders = new db_table_dataobject($dbshop, "shop_shops");

//$shop_orders -> load_data ();
	echo "RESULTS: ".$shop_orders ->show_result_count();
//	echo "+".$shop_orders->column_count;
//print_r( $shop_orders->get_data_array("id_shop", array("shop_type")) );
//print_r($shop_orders->get_data_assoc_num());

while ($row = $shop_orders->fetch_assoc())
{
	
	echo print_r($row, true)."+++++++<br />";
}


/*

	get_data_assoc_num(]ARRAY tablefield[)
	get_data_assoc_key([STRING index], ]ARRAY tablefield[)
	
	fetch_assoc(]ARRAY tablefield[)
	
	reset_pointer();
*/

?>