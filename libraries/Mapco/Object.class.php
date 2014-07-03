<?php
	/**************************************************
	***	Class: MObject				***
	***	Namespace: Mapco.Object			***
	*** Author: C.Haendler <chaendler(at)mapco.de> 	*** 
	***	Version: 1.0  		01/07/14/ 	***
	***	Last mod: 01/07/14			***
	***************************************************/

	class MObject 
		{
			
		/**
		 * Class constructor, overridden in descendant classes.
		 *
		 * @param   mixed  $properties  Either and associative array or another
		 *                              object to set the initial properties of the object.
		 *
		 */
		public function __construct($properties = null)
		{
			if ($properties !== null)
			{
				$this->setProperties($properties);
			}
		}
	
		/**
		 * Magic method to convert the object to a string gracefully.
		 *
		 * @return  string  The classname.
		 *
		 */
		public function __toString()
		{
			return get_class($this);
		}
	
		/**
		 * Sets a default value if not alreay assigned
		 *
		 * @param   string  $property  The name of the property.
		 * @param   mixed   $default   The default value.
		 *
		 * @return  mixed
		 *
		 */
		public function def($property, $default = null)
		{
			$value = $this->get($property, $default);
			return $this->set($property, $value);
		}
	
		/**
		 * Returns a property of the object or the default value if the property is not set.
		 *
		 * @param   string  $property  The name of the property.
		 * @param   mixed   $default   The default value.
		 *
		 * @return  mixed    The value of the property.
		 *
		 *
		 * @see     getProperties()
		 */
		public function get($property, $default = null)
		{
			if (isset($this->$property))
			{
				return $this->$property;
			}
			return $default;
		}
	
		/**
		 * Returns an associative array of object properties.
		 *
		 * @param   boolean  $public  If true, returns only the public properties.
		 *
		 * @return  array
		 *
		 * @see     get()
		 */
		public function getProperties($public = true)
		{
			$vars = get_object_vars($this);
			if ($public)
			{
				foreach ($vars as $key => $value)
				{
					if ('_' == substr($key, 0, 1))
					{
						unset($vars[$key]);
					}
				}
			}
	
			return $vars;
		}
	
		/**
		 * Modifies a property of the object, creating it if it does not already exist.
		 *
		 * @param   string  $property  The name of the property.
		 * @param   mixed   $value     The value of the property to set.
		 *
		 * @return  mixed  Previous value of the property.
		 *
		 */
		public function set($property, $value = null)
		{
	
			$previous = isset($this->$property) ? $this->$property : null;
			$this->$property = $value;
			return $previous;   
		}
	
		/**
		 * Set the object properties based on a named array/hash.
		 *
		 * @param   mixed  $properties  Either an associative array or another object.
		 *
		 * @return  boolean
		 *
		 *
		 * @see     set()
		 */
		public function setProperties($properties)
		{
			if (is_array($properties) || is_object($properties))
			{
				foreach ((array) $properties as $k => $v)
				{
					// Use the set function which might be overridden.
					$this->set($k, $v);
				}
				return true;
			}
	
			return false;
		}
			
		/**
		 * Converts the object to a string (the class name).
		 *
		 * @return  string
		 *
		 * @see         __toString()
		 */
		public function toString()
		{
			return $this->__toString();
		}
		
		public static function is_json($string) {
			json_decode($string);
			return (json_last_error() == JSON_ERROR_NONE);
		}
		
		public static function is_assoc($array) {
			return (bool)count(array_filter(array_keys($array), 'is_string'));
		}
		
		/*
		 *	Converts a MObject / JSON / ASSOC to Assoc
		 *	
		 *	@return mixed associative array
		*/
		
		public static function allToAssoc($data) {
	
			if (isset($data)) {
				if ($data instanceOf MObject) {
					$newdata = $data->getProperties(true);
				} else if (MObject::is_assoc($data)) {
					$newdata = $data;
				} else if (MObject::is_json($data)) {
					$newdata = json_decode($data, true);
				}
			}
			
			return $newdata;
	
		}
		
	}