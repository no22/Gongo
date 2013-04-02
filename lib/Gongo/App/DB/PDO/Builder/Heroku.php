<?php
class Gongo_App_DB_PDO_Builder_Heroku extends Gongo_App_DB_PDO_Builder
{
	public function get($env)
	{
		$url = parse_url(getenv("CLEARDB_DATABASE_URL"));
		$server = $url["host"];
		$user = $url["user"];
		$password = $url["pass"];
		$dbname = substr($url["path"], 1);
		$port = $env->config->Heroku ? $env->config->Heroku->db_port('3306') : '3306' ;
		$dsn = strtr($env->config->Database->dsn, array('DB_HOST' => $server, 'DB_NAME' => $dbname, 'DB_PORT' => $port));
		$options = $this->getOptions($env, $dsn);
		$pdo = new PDO($dsn, $user, $password , $options);
		return $pdo;
	}
}
