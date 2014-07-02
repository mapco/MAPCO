<?php
	/**************************************************
	***	Class: MLoader								***
	*** Author: C.Haendler <chaendler(at)mapco.de> 	*** 
	***	Version: 1.0  		01/07/14/ 				***
	***	Last mod: 01/07/14							***
	***************************************************/
		
	/*
	*	the class prefix - not the filename prefix
	*/
	define('LOADER_CLASS_PREFIX', 'M'); 
	
	/*
	*	the filename extensions of a class
	*/
	define('LOADER_CLASS_EXTENSION', '.class');
		
	/*
	*	LOADER EXTENSIONS
	*/
	
	/*
	*	const LOADER_EXTENSIONS 
	*		0 = do not use extension namespace
	*		0 = use extension namespace
	*/
	define('LOADER_EXTENSIONS', 0); 

/*	
	define('M_PATH_COMPONENTS', 'MCOM');
	define('LOADER_COM_PREFIX', 'MCOM');
	
	define('M_PATH_PLUGINS','');
	define('LOADER_PLUG_PREFIX','MPLUG');
		
	define('M_PATH_WIDGETS','');
	define('LOADER_WID_PREFIX', 'MWID');
	
	define('M_PATH_MODULES','MMOD');
	define('LOADER_MOD_PREFIX', 'MMOD');
*/	
	


	class MLoader {
	
		/**
		 * Container for already imported library paths.
		 *
		 * @var    array
		 * @since   1.0
		 */
		protected static $classes = array();
	
		/**
		 * Container for already imported library paths.
		 *
		 * @var    array
		 * @since   1.0
		 */
		protected static $imported = array();
	
		/**
		 * Container for registered library class prefixes and path lookups.
		 *
		 * @var    array
		 * @since   1.0
		 */
		protected static $prefixes = array();		
		/**
		 * Method to discover classes of a given type in a given path.
		 *
		 * @param   string   $classPrefix  The class name prefix to use for discovery.
		 * @param   string   $parentPath   Full path to the parent folder for the classes to discover.
		 * @param   boolean  $force        True to overwrite the autoload path value for the class if it already exists.
		 * @param   boolean  $recurse      Recurse through all child directories as well as the parent path.
		 *
		 * @return  void
		 *
		 * @since   1.0
		 */
		public static function discover($classPrefix, $parentPath, $force = true, $recurse = false)
		{
			try
			{
				if ($recurse)
				{
					$iterator = new RecursiveIteratorIterator(
						new RecursiveDirectoryIterator($parentPath),
						RecursiveIteratorIterator::SELF_FIRST
					);
				}
				else
				{
					$iterator = new DirectoryIterator($parentPath);
				}
	
				foreach ($iterator as $file)
				{
					$fileName = $file->getFilename();
	
					// Only load for php files.
					if ($file->isFile() && substr($fileName, strrpos($fileName, '.') + 1) == 'php')
					{
						// Get the class name and full path for each file.
						$class = strtolower($classPrefix . preg_replace('#\.php$#', '', $fileName));
	
						// Register the class with the autoloader if not already registered or the force flag is set.
						if (empty(static::$classes[$class]) || $force)
						{
							static::register($class, $file->getPath() . '/' . $fileName);
						}
					}
				}
			}
			catch (UnexpectedValueException $e)
			{
				// Exception will be thrown if the path is not a directory. Ignore it.
			}
		}
	
		/**
		 * Method to get the list of registered classes and their respective file paths for the autoloader.
		 *
		 * @return  array  The array of class => path values for the autoloader.
		 *
		 * @since   1.0
		 */
		public static function getClassList()
		{
			return static::$classes;
		}
	
		/**
		 * Loads a class from specified directories.
		 *
		 * @param   string  $key   The class name to look for (dot notation).
		 * @param   string  $base  Search this directory for the class.
		 *
		 * @return  boolean  True on success.
		 *
		 * @since   1.0
		 */
		public static function import($key, $base = null)
		{
			// Only import the library if not already attempted.
			if (!isset(static::$imported[$key]))
			{
				// Setup some variables.
				$success = false;
				$parts = explode('.', $key);
				$class = array_pop($parts);
				$base = (!empty($base)) ? $base : dirname(__FILE__);
				$path = str_replace('.', DIRECTORY_SEPARATOR, $key);
				$extension = false;
							
				$extParts = explode('.', $key);
				// check extension shortcuts
				switch($extParts[0]) 
				{
					/*
					case LOADER_COM_PREFIX:
						$extPath = PATH_COMPONENTS;
						$extExt = ".component";
						$extension = true;                                    
					break;
									
					case LOADER_PLUG_PREFIX:
						$extPath = PATH_PLUGINS;
						$extExt = ".plugin";
						$extension = true;    
					break;
									
					case LOADER_MOD_PREFIX:
						$extPath = PATH_MODULES;
						$extExt = ".modul";
						$extension = true;    
					break;
									
					case LOADER_WID_PREFIX:
						$extPath = PATH_WIDGETS;
						$extExt = ".widget";
						$extension = true;   
					break;
					*/
				} 
							
				if ($extension) 
				{			
					// import extension main class
					if (count($extParts) == 2) 
					{
						//$ext = strtolower($extParts[1]);
						$ext = $extParts[1];
						//$class = strtolower($class);
						$file = $extPath.DS.$ext.DS.$class.$extExt.''.LOADER_CLASS_EXTENSION.'.php';
						if (is_file($file)) 
						{
							$success = (bool) include_once $file;
							static::$imported[$key] = $success;
						} 
					}
	
					// import extension sub class
					if (count($extParts) > 2) 
					{
						$subParts = explode('.',$key);
						array_shift($subParts);
						//$class = strtolower(array_pop($subParts));
						$class = array_pop($subParts);
						$subPath = implode(DS,$subParts);
						$file = $extPath.DS.$subPath.DS.$class.''.LOADER_CLASS_EXTENSION.'.php';
						if (is_file($file)) 
						{
							$success = (bool) include_once $file;
							static::$imported[$key] = $success;
						} 
					}
				} 
				else 
				{
									
					// Handle special case for helper classes.
					if ($class == 'helper')
					{
						$class = ucfirst(array_pop($parts)) . ucfirst($class);
					}
					// Standard class.
					else
					{
						$class = ucfirst($class);
					}
	
					// If we are importing a library from the Mapco namespace set the class to autoload.
					if (strpos($path, 'Mapco') === 0)
					{
						// Since we are in the namespace prepend the classname with XJ.
						$class = LOADER_CLASS_PREFIX.'' . $class;
	
						// Only register the class for autoloading if the file exists.
						if (is_file($base . '/' . $path . ''.LOADER_CLASS_EXTENSION.'.php'))
						{
							static::$classes[strtolower($class)] = $base . '/' . $path . ''.LOADER_CLASS_EXTENSION.'.php';
							$success = true;
						}
					}
					/*
					* If we are not importing a library from the Mapco namespace directly include the
					* file since we cannot assert the file/folder naming conventions.
					*/
					else
					{
						// If the file exists attempt to include it.
						if (is_file($base . '/' . $path . ''.LOADER_CLASS_EXTENSION.'.php'))
						{
							$success = (bool) include_once $base . '/' . $path . ''.LOADER_CLASS_EXTENSION.'.php';
						}
					}
	
					// Add the import key to the memory cache container.
					static::$imported[$key] = $success;
				}
			}
	
			return static::$imported[$key];
		}
	
		/**
		 * Load the file for a class.
		 *
		 * @param   string  $class  The class to be loaded.
		 *
		 * @return  boolean  True on success
		 *
		 * @since   1.0
		 */
		public static function load($class)
		{
			// Sanitize class name.
			$class = strtolower($class);
	
			// If the class already exists do nothing.
			if (class_exists($class))
			{
				return true;
			}
	
			// If the class is registered include the file.
			if (isset(self::$classes[$class]))
			{
				include_once self::$classes[$class];
				return true;
			}
	
			return false;
		}
	
		/**
		 * Directly register a class to the autoload list.
		 *
		 * @param   string   $class  The class name to register.
		 * @param   string   $path   Full path to the file that holds the class to register.
		 * @param   boolean  $force  True to overwrite the autoload path value for the class if it already exists.
		 *
		 * @return  void
		 *
		 * @since   1.0
		 */
		public static function register($class, $path, $force = true)
		{
			// Sanitize class name.
			$class = strtolower($class);
	
			// Only attempt to register the class if the name and file exist.
			if (!empty($class) && is_file($path))
			{
				// Register the class with the autoloader if not already registered or the force flag is set.
				if (empty(self::$classes[$class]) || $force)
				{
					self::$classes[$class] = $path;
				}
			}
		}
	
		/**
		 * Register a class prefix with lookup path.  This will allow developers to register library
		 * packages with different class prefixes to the system autoloader.  More than one lookup path
		 * may be registered for the same class prefix, but if this method is called with the reset flag
		 * set to true then any registered lookups for the given prefix will be overwritten with the current
		 * lookup path.
		 *
		 * @param   string   $prefix  The class prefix to register.
		 * @param   string   $path    Absolute file path to the library root where classes with the given prefix can be found.
		 * @param   boolean  $reset   True to reset the prefix with only the given lookup path.
		 *
		 * @return  void
		 *
		 * @since   1.0
		 */
		public static function registerPrefix($prefix, $path, $reset = false)
		{
			// Verify the library path exists.
			if (!file_exists($path))
			{
				throw new RuntimeException('Library path ' . $path . ' cannot be found.', 500);
			}
	
			// If the prefix is not yet registered or we have an explicit reset flag then set set the path.
			if (!isset(self::$prefixes[$prefix]) || $reset)
			{
				self::$prefixes[$prefix] = array($path);
			}
			// Otherwise we want to simply add the path to the prefix.
			else
			{
				self::$prefixes[$prefix][] = $path;
			}
		}
	
		/**
		 * Method to setup the autoloaders for the Mapco Platform.  Since the SPL autoloaders are
		 * called in a queue we will add our explicit, class-registration based loader first, then
		 * fall back on the autoloader based on conventions.  This will allow people to register a
		 * class in a specific location and override platform libraries as was previously possible.
		 *
		 * @return  void
		 *
		 * @since   1.0
		 */
		public static function setup()
		{
			// Register the base path for Mapco platform libraries.
			self::registerPrefix(LOADER_CLASS_PREFIX, M_PATH_LIBRARIES . '/Mapco');
	
			// Register the autoloader functions.
			spl_autoload_register(array('MLoader', 'load'));
			spl_autoload_register(array('MLoader', '_autoload'));
		}
	
		/**
		 * Autoload a class based on name.
		 *
		 * @param   string  $class  The class to be loaded.
		 *
		 * @return  void
		 *
		 * @since   1.0
		 */
		private static function _autoload($class)
		{
			foreach (self::$prefixes as $prefix => $lookup)
			{
				if (strpos($class, $prefix) === 0)
				{
					return self::_load(substr($class, strlen($prefix)), $lookup);
				}
			}
		}
	
		/**
		 * Load a class based on name and lookup array.
		 *
		 * @param   string  $class   The class to be loaded (wihtout prefix).
		 * @param   array   $lookup  The array of base paths to use for finding the class file.
		 *
		 * @return  void
		 *
		 * @since   1.0
		 */
		private static function _load($class, $lookup)
		{
			// Split the class name into parts separated by camelCase.
			$parts = preg_split('/(?<=[a-z0-9])(?=[A-Z])/x', $class);
	
			// If there is only one part we want to duplicate that part for generating the path.
			$parts = (count($parts) === 1) ? array($parts[0], $parts[0]) : $parts;
	
			foreach ($lookup as $base)
			{
				// Generate the path based on the class name parts.
				$path = $base . '/' . implode('/', array_map('strtolower', $parts)) . '.php';
	
				// Load the file if it exists.
				if (file_exists($path))
				{
					return include $path;
				}
			}
		}
	
	} 
	
	function i($namespace) 
	{
		MLoader::import($namespace);
	}
				
?>