<?php
	//Datos de la conexión
	define( "HOST", "localhost" );
	define( "USER", "root" );
	define( "PASSWORD", "root" );
	define( "DATABASE", "k9dispenser" );	//Nombre de tu Base de datos
	
	class Database 
	{
		private $_connection;
		private static $_instance;
			
		//Establecer la conexión o indicar errores
		private function __construct()
		{
			$this->_connection = new mysqli( HOST, USER, PASSWORD, DATABASE, 3307 );

		
			
			if(mysqli_connect_error())
			{
				
				trigger_error("Fallo la conexion a MySQL: " . mysqli_connect_error(), E_USER_ERROR);
			}else{
				
			
			
				if (!mysqli_set_charset($this->_connection, "utf8")) {
					printf("Error cargando el conjunto de caracteres utf8: %s\n", mysqli_error($this->_connection));
					exit();
				} else {
					//printf("Conjunto de caracteres actual: %s\n", mysqli_character_set_name($this->_connection));
				}
			
			}
		}

		//Función para la instancia de la Base de Datos	
		public static function getInstance()
		{
			if(!self::$_instance)
			{	
				self::$_instance = new self();
			}
			return self::$_instance;
		}

       //Función para retornar la conexión de la BD
		public function getConnection()
		{
			
			return $this->_connection;
			
		}

	}
  
?>



