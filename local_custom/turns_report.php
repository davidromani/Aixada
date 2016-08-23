<?php 
    require_once("../php/inc/header.inc.php");
    require_once("php/inc/turns_report.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
$from_date = null;
if (isset($_GET['from_date'])) {
    $from_date = $_GET['from_date'];
    if (strtotime($from_date) === false) {
        $from_date = null;
    }
}
if ($from_date === null) {
    $aux = new DateTime("-2 weeks");
    $from_date = $aux->format('Y-m-d');
}
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=$language;?>" lang="<?=$language;?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo 'Torns des de '.$from_date;?></title>
	<link rel="stylesheet" type="text/css"               href="css/reports_paper.css"/>
    <link rel="stylesheet" type="text/css"               href="css/reports_layout.css"/>
  	<link rel="stylesheet" type="text/css" media="print" href="css/reports-print.css"/>
</head>
<body>
    <div class="page-A4p">
    <h2 class="start"><?php echo 'Torns a partir de: '.$from_date; ?></h2>
    <?php echo write_turn_fromDate($from_date); ?>
    </div>
</body>
</html>
