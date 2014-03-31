<?php
class Gongo_App_DB_PDO_Builder
{
	public function get($env)
	{
		if (!Gongo_App_Environment::read('GONGO_OVERRIDE_DB_CONFIG')) {
			$dsn = $env->config->Database->dsn;
			$user = $env->config->Database->user;
			$password = $env->config->Database->password;
		} else {
			if ($env->devMode) {
				$dsn = Gongo_App_Environment::read('GONGO_DEVELOPMENT_DB_DSN');
				$user = Gongo_App_Environment::read('GONGO_DEVELOPMENT_DB_USER');
				$password = Gongo_App_Environment::read('GONGO_DEVELOPMENT_DB_PASSWORD');
			} else {
				$dsn = Gongo_App_Environment::read('GONGO_PRODUCTION_DB_DSN');
				$user = Gongo_App_Environment::read('GONGO_PRODUCTION_DB_USER');
				$password = Gongo_App_Environment::read('GONGO_PRODUCTION_DB_PASSWORD');
			}
		}
		$options = $this->getOptions($env, $dsn);
		$pdo = new PDO($dsn, $user, $password, $options);
		return $pdo;
	}

	protected function getOptions($env, $dsn, $charset = null)
	{
		$options = array();
		if (strpos($dsn, 'mysql') === 0 && PHP_VERSION_ID < 50306) {
			if (is_null($charset)) {
				if (preg_match('/charset\s*=\s*(\w+)/i', $dsn, $m)) {
					$charset = $m[1];
				}
				$charset = is_null($charset) ? 'utf8' : $charset ;
			}
			$options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET CHARACTER SET `{$charset}`";
		}
		return $options;
	}
}
