<?php 
	if (!defined('BASE_URL')) define('BASE_URL', 'https://sincro.data-purolomo.com');

	//Zona horaria
	date_default_timezone_set('America/Caracas');
	setlocale(LC_ALL, 'es_ES');

	// Datos Dinámicos (pueden ser actualizados)
	define('DB_HOST', '198.251.71.50:3306');
	define('DB_NAME', 'sincro_purolomo');
	define('DB_USER', 'sincropurolomo');
	define('DB_PASSWORD', '66Axx4u_8');
	define('DB_CHARSET', "utf8");

?>