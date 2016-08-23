<?php
define('DS', DIRECTORY_SEPARATOR);
define('__ROOT__', dirname(__FILE__).DS); 
require_once(__ROOT__ ."php/utilities/general.php");
require_once(__ROOT__ ."aixada_check-ctrl-BOM.php");

$x = 8001;
printXML('<rowset><![CDATA['.str_repeat('a', $x-71)."]]>\n</rowset>");
?>
