<?php 
    require_once("../php/inc/header.inc.php");
    require_once("php/inc/turns_report.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=$language;?>" lang="<?=$language;?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php 
            $page_title = 'Repartiment de comandes';
      		echo $Text['global_title'] . " - " . $page_title;  ?></title>

 	<link rel="stylesheet" type="text/css"   media="screen" href="../css/aixada_main.css" />
  	<link rel="stylesheet" type="text/css"   media="screen" href="../js/fgmenu/fg.menu.css"   />
    <link rel="stylesheet" type="text/css"   media="screen" href="../css/ui-themes/<?=$default_theme;?>/jqueryui.css"/>
    <link rel="stylesheet" type="text/css"                  href="css/reports_layout.css" />
     
    <script type="text/javascript" src="../js/jquery/jquery.js"></script>
    <script type="text/javascript" src="../js/jqueryui/jqueryui.js"></script>
    <script type="text/javascript" src="../js/fgmenu/fg.menu.js"></script>
    <script type="text/javascript" src="../js/aixadautilities/jquery.aixadaMenu.js"></script>     	 
    <script type="text/javascript" src="../js/aixadautilities/jquery.aixadaXML2HTML.js" ></script>
    <script type="text/javascript" src="../js/aixadautilities/jquery.aixadaUtilities.js" ></script>
    <style>
        .aix-style-entry-widget {margin-bottom:0; margin-top: 20px;}
        .uf_turns {
            color:#555;
            font-size:11pt;
        }
        tr.uf_turns td {
            padding-top:0;
        }
    </style>
<?php
    $db = DBWrap::get_instance();
    $rs = $db->Execute(
        'SELECT min(date_for_order) for_date' .
        ' FROM aixada_product_orderable_for_date'.
        ' where date_for_order >= date(sysdate())'
    );
    $row = $rs->fetch_array();
    $for_date = null;
    if (isset($_GET['date'])) {
        $for_date = $_GET['date'];
        if (strtotime($for_date) === false) {
            $for_date = null;
        }
    } else {
        if ($row) {
            $for_date = $row['for_date'];
        } 
        if ($for_date == null) {
            $rs = $db->Execute(
                'SELECT max(date_for_order) for_date' .
                ' FROM aixada_product_orderable_for_date');
            $row = $rs->fetch_array();
            if ($row) {
                $for_date = $row['for_date'];
            }
        }
    }
    $order_null = false;
    $order_not_null = false;
    if ($for_date) {
        $order_null = (get_list_query(
            'select count(*) order_null from aixada_order_item'.
            " where order_id is null and date_for_order='".$for_date."'") != 0);
        $order_not_null = (get_list_query(
            'select count(*) order_null from aixada_order_item'.
            " where order_id is not null and date_for_order='".$for_date."'")
                                                                          != 0);
    }
?>
</head>
<body>
<div id="wrap">
	<div id="headwrap">
		<?php include "../php/inc/menu.inc.php" ?>
	</div>
    <script>
        // rebase menus to up
        $('a').prop('href', 
            function(i, val) {
            console.log(val);
                return val.replace('/local_custom/','/');
            }
        );
    </script>
	<div id="stagewrap">
		<div class="aix-layout-center60 ui-widget">
            <?php if ($for_date) { // START ?>
            <div class="aix-style-entry-widget">
				<table><tr>
                    <?php if ($order_null){ // START ?>
                    <td><button class="aix-layout-fixW150" 
                        onclick="window.open('../manage_orders.php?filter=nextWeek&lastPage=torn.php','_blank');"
                    >Enviar</button>
                    </td>
                    <td><p>
                        Tancar i <b>Enviar</b> la comanda al/s proveïdor/s.<br>
                        (la comanda s'envia al proveïdor i a la UF responsable)
                    </p></td>
                    <?php } else if($order_not_null) { // ELSE IF ?>
                    <td><p>La comanda als proveïdors per <?php echo $for_date; ?> ja estan tancades.</p></td>
                    <?php } else { // ELSE ?>
                    <td><p><b>No hi ha cap comanda</b> per <?php echo $for_date; ?>.</p></td>
                    <?php } // END ?>
				</tr></table>
            </div>
			<div class="aix-style-entry-widget">
                <h2>Informes per <?php echo $for_date; ?></h2>
				<table>
                <tr>
                    <td><button class="aix-layout-fixW150"
                            onclick="window.open('order_report_prod_fam.php?date=<?php echo $for_date; ?>','_blank');"
                            >
                        Prod-Fam
                    </button></td>
					<td><p>
                        Relació de Productes i detall de les Famílies que en demanen.<br>
                        <span style="color:#555">Useu aquest informe per fer el repartiment!</span>
                    </p></td>
				</tr>
                <tr style="color:#555">
                    <td><button class="aix-layout-fixW150" style="color:#555"
                            onclick="window.open('order_report_fam_prod.php?date=<?php echo $for_date; ?>','_blank');"
                            >Fam-Prod
                        </button>
                    </td>
					<td><p>
                        Relació de Famílies amb els Productes demanats.
                        <button class="aix-layout-fixW100" style="color:#555"
                            onclick="window.open('order_report_fam_prod.php?detail=N&date=<?php echo $for_date; ?>','_blank');"
                            >Resum
                        </button>
                    </p></td>
				</tr>
                </table>
			</div>
            <?php } else { // ELSE ?>
            <div class="aix-style-entry-widget">
                <h2>No hi ha cap data :-((</h2>
            <div>
            <?php } // END ?>
            <div class="aix-style-entry-widget">
                <h2>Informació d'interès</h2>
				<table>
                <tr>
                    <td style="padding-bottom:0" ><button class="aix-layout-fixW150"
                            onclick="window.open('turns_report.php','_blank');"
                            >
                        Torns
                    </button></td>
					<td style="padding-bottom:0" ><p>
                        Calendari de torns de UFs per fer repartiments.
                        <button class="aix-layout-fixW100"
                            onclick="window.location.href = '../manage_data.php?table=cistella_turn'";
                            >Dades</button>
                    </p></td>                    
				</tr>
                <tr class="uf_turns"><td colspan="2">
                    <?php
                        $text_turns = write_turn_uf(get_session_uf_id(), $for_date);
                        if ($text_turns !="") {
                            echo "Els teus pròxims torns:";
                            echo $text_turns;                            
                        }
                    ?></td></tr>
                <tr>
                    <td><button class="aix-layout-fixW150"
                            onclick="window.open('dir_report_prv_fam.php','_blank');"
                            >
                        Directori
                    </button></td>
					<td><p>
                        Emails i telèfons dels proveïdors i les famílies.
                    </p></td>
				</tr>
                </table>
			</div>
		</div>

	</div>
</div>
</body>
</html>