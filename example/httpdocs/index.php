<?php
define('PATH_TO_SRC', '/path/to/src');
require PATH_TO_SRC . '/vendor/autoload.php';
if (!include(PATH_TO_SRC . '/apps/skelton/app.php')) {
	trigger_error("Gongo application could not be found.", E_USER_ERROR);
}
