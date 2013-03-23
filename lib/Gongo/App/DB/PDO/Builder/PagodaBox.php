<?php
class Gongo_App_DB_PDO_Builder_PagodaBox extends Gongo_App_DB_PDO_Builder
{
	public function get($env)
	{
		$dbkey = $env->config->PagodaBox->database('DB1');
		$server = getenv($dbkey . "_HOST");
		$user = getenv($dbkey . "_USER");
		$password = getenv($dbkey . "_PASS");
		$dbname = getenv($dbkey . "_NAME");
		$port = getenv($dbkey . "_PORT");
		$dsn = strtr($env->config->Database->dsn, array('DB_HOST' => $server, 'DB_NAME' => $dbname, 'DB_PORT' => $port));
		$options = $this->getOptions($env, $dsn);
		$pdo = new PDO($dsn, $user, $password , $options);
		return $pdo;
	}
}
