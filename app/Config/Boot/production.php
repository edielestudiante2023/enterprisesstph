<?php

setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain');
date_default_timezone_set('America/Bogota');


/*
 |--------------------------------------------------------------------------
 | ERROR DISPLAY
 |--------------------------------------------------------------------------
 | Don't show ANY in production environments. Instead, let the system catch
 | it and display a generic error message.
 |
 | If you set 'display_errors' to '1', CI4's detailed error report will show.
 */
error_reporting(E_ALL & ~E_DEPRECATED);
// If you want to suppress more types of errors.
// error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
/* ini_set('display_errors', '0'); */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


/*
 |--------------------------------------------------------------------------
 | DEBUG MODE
 |--------------------------------------------------------------------------
 | Debug mode is an experimental flag that can allow changes throughout
 | the system. It's not widely used currently, and may not survive
 | release of the framework.
 */
defined('CI_DEBUG') || define('CI_DEBUG', false);
