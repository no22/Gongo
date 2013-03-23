<?php
class Gongo_App_DB_PDO_Builder
{
	public function get($env)
	{
		$options = $this->getOptions($env, $env->config->Database->dsn);
		$pdo = new PDO(
			$env->config->Database->dsn, 
			$env->config->Database->user, $env->config->Database->password ,
			$options
		);
		return $pdo;
	}

	protected function getOptions($env, $dsn, $charset = null)
	{
		$options = array();
		if (strpos($dsn, 'mysql') === 0 && PHP_VERSION_ID < 50306) {
			if (is_null($charset)) {
				if (preg_match('/charset\s*=\s*([\w+])/i', $dsn, $m)) {
					$charset = $m[1];
				}
				$charset = is_null($charset) ? 'utf8' : $charset ;
			}
			$options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET CHARACTER SET `{$charset}`";
		}
		return $options;
	}
}
