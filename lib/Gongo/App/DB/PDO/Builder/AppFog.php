<?php
class Gongo_App_DB_PDO_Builder_AppFog extends Gongo_App_DB_PDO_Builder
{
	public function get($env)
	{
		$services_json = json_decode(getenv("VCAP_SERVICES"),true);
		$dbkey = $env->config->AppFog ? $env->config->AppFog->database('mysql-5.1') : 'mysql-5.1' ;
		$db_config = $services_json[$dbkey][0]["credentials"];
		$server = $db_config["hostname"];
		$user = $db_config["username"];
		$password = $db_config["password"];
		$port = $db_config["port"];
		$dbname = $db_config["name"];
		$dsn = strtr($env->config->Database->dsn, array('DB_HOST' => $server, 'DB_NAME' => $dbname, 'DB_PORT' => $port));
		$options = $this->getOptions($env, $dsn);
		$pdo = new PDO($dsn, $user, $password , $options);
		return $pdo;
	}
}
