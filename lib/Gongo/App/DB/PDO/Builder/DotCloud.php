<?php
class Gongo_App_DB_PDO_Builder_DotCloud extends Gongo_App_DB_PDO_Builder
{
	public function get($env)
	{
		$filepath = $_SERVER['HOME'].'/environment.json';
		$dotoCloudEnv = json_decode(file_get_contents($filepath), true);
		$server = $dotoCloudEnv['DOTCLOUD_DATA_MYSQL_HOST'];
		$user = $dotoCloudEnv['DOTCLOUD_DATA_MYSQL_LOGIN'];
		$password = $dotoCloudEnv['DOTCLOUD_DATA_MYSQL_PASSWORD'];
		$dbname = $env->config->dotCloud ? $env->config->dotCloud->db_name : '' ;
		$port = $dotoCloudEnv['DOTCLOUD_DATA_MYSQL_PORT'];
		$dsn = strtr($env->config->Database->dsn, array('DB_HOST' => $server, 'DB_NAME' => $dbname, 'DB_PORT' => $port));
		$options = $this->getOptions($env, $dsn);
		$pdo = new PDO($dsn, $user, $password , $options);
		return $pdo;
	}
}
