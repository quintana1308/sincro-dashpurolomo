<?php 
	if (!defined('BASE_URL')) define('BASE_URL', 'https://sincro.data-purolomo.com');

	//Zona horaria
	date_default_timezone_set('America/Caracas');
	setlocale(LC_ALL, 'es_ES');

	// Datos Dinámicos (pueden ser actualizados)
	define('DB_HOST', 'p21adn.com:3306');
	define('DB_NAME', 'p21adn_supercarnes');
	define('DB_USER', 'p21adn_wapi');
	define('DB_PASSWORD', 'Maximus10Meridium*');
	define('DB_CHARSET', "utf8");

?>