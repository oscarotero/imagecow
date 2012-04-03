<?php
namespace Fol;

class Loader {
	static $libraries_path;

	static $classes = array();
	static $prefixes = array();
	static $namespaces = array();


	/**
	 * static public function setLibrariesPath (string $libraries_path)
	 *
	 * Sets the base path for load the libraries
	 */
	static public function setLibrariesPath ($libraries_path) {
		if (is_dir($libraries_path)) {
			if (substr($libraries_path, -1) != '/') {
				$libraries_path .= '/';
			}

			self::$libraries_path = $libraries_path;
		} else {
			throw new \ErrorException("The folder '$libraries_path' does not exists");
		}
	}



	/**
	 * static public function register ()
	 *
	 * Installs this class loader on the SPL autoload stack.
	 */
	static public function register () {
		spl_autoload_register(__NAMESPACE__.'\\Loader::autoload');
	}


	/**
	 * static public function unregister ()
	 *
	 * Uninstalls this class loader from the SPL autoloader stack.
	 */
	static public function unregister () {
		spl_autoload_unregister(__NAMESPACE__.'\\Loader::autoload');
	}



	/**
	 * static public function autoload ($class_name)
	 *
	 * Basic autoload function
	 * Returns boolean
	 */
	static public function autoload ($class_name) {
		if ($file = self::getFile($class_name)) {
			include_once($file);
		}
	}



	/**
	 * static public function getFile ($class_name)
	 *
	 * Find a class file
	 * Returns string/false
	 */
	static public function getFile ($class_name) {
		$class_name = ltrim($class_name, '\\');

		if (isset(self::$classes[$class_name])) {
			$file = self::$classes[$class_name];

			if (is_file($file)) {
				return $file;
			}

			return false;
		}

		$namespace = '';

		if ($last_pos = strripos($class_name, '\\')) {
			$namespace = substr($class_name, 0, $last_pos);
			$class_name = substr($class_name, $last_pos + 1);
		}

		foreach (self::$namespaces as $ns => $options) {
			if (strpos($namespace, $ns) === 0) {
				if ($file = self::filePath(preg_replace('#^'.$ns.'#', '', $namespace), $class_name, $options)) {
					return $file;
				}

				break;
			}
		}

		foreach (self::$prefixes as $prefix => $options) {
			if (strpos($class_name, $prefix) === 0) {
				if ($file = self::filePath($namespace, $class_name, $options)) {
					return $file;
				}

				break;
			}
		}

		return self::filePath($namespace, $class_name);
	}



	/**
	 * static private function filePath (string $namespace, string $class_name, [array $options])
	 *
	 * Generate the filename and check if it exists
	 * Returns string/boolean
	 */
	static private function filePath ($namespace, $class_name, array $options = array()) {
		$file = isset($options['path']) ? $options['path'] : self::$libraries_path;

		if ($namespace && (!isset($options['namespace_directories']) || $options['namespace_directories'] !== false)) {
			$file .= str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
		}

		if (!isset($options['class_directories']) || $options['class_directories'] !== false) {
			$file .= str_replace('_', DIRECTORY_SEPARATOR, $class_name).'.php';
		} else {
			$file .= $class_name.'.php';
		}

		if (is_file($file)) {
			return $file;
		}

		return false;
	}



	/**
	 * static public function registerClass (array $classes)
	 * static public function registerClass (string $class, string $path)
	 *
	 * Sets a new path for an specific class
	 * Returns none
	 */
	static public function registerClass ($class, $path = null) {
		if (is_array($class)) {
			foreach ($class as $class => $path) {
				self::registerClass($class, $path);
			}

			return;
		}

		self::$classes[$class] = $path;
	}



	/**
	 * static public function registerPrefix (array $prefixes)
	 * static public function registerPrefix (string $prefix, string $path)
	 * static public function registerPrefix (string $prefix, array $options)
	 *
	 * Sets a new path for an specific prefix in class name
	 * Returns none
	 */
	static public function registerPrefix ($prefix, $options = null) {
		if (is_array($prefix)) {
			foreach ($prefix as $key => $value) {
				self::registerPrefix($key, $value);
			}

			return;
		}

		if (!is_array($options)) {
			$options = array('path' => $options);
		}

		self::$prefixes[$prefix] = $options;
	}



	/**
	 * static public function registerNamespace (array $namespaces)
	 * static public function registerNamespace (string $namespace, string $path)
	 * static public function registerNamespace (string $namespace, array $options)
	 *
	 * Sets a new base path for an specific namespace
	 * Returns none
	 */
	static public function registerNamespace ($namespace, $options = null) {
		if (is_array($namespace)) {
			foreach ($namespace as $namespace => $options) {
				self::registerNamespace($namespace, $options);
			}

			return;
		}

		if (!is_array($options)) {
			$options = array('path' => $options);
		}

		self::$namespaces[$namespace] = $options;
	}
}
?>