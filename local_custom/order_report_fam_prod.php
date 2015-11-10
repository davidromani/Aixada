<?php 
    require_once '../php/inc/header.inc.php';
    require_once 'order_report_common.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
$date_for_order=$_GET['date']; //'2014-12-18';
$detail = ((isset($_GET['detail'])?$_GET['detail']:'S') != 'N');
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=$language;?>" lang="<?=$language;?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo 'Fam-Prod '.$date_for_order.($detail?'':' summary');?></title>
	<link rel="stylesheet" type="text/css"               href="css/reports_paper.css"/>
    <link rel="stylesheet" type="text/css"               href="css/reports_layout.css"/>
  	<link rel="stylesheet" type="text/css" media="print" href="css/reports-print.css"/>
</head>
<body>
<div class="page-A4p">
<?php echo write_fam_prod($date_for_order, $detail); ?>
</div>
<?php
function write_fam_prod($date_for_order, $detail) {
    $db = DBWrap::get_instance();
    $strSQL = get_SQL($date_for_order).'uf_name, pv_name, order_id, p_name';
    $rs = $db->Execute($strSQL);
    $brk = array(
        'date_for_order' => $date_for_order,
        '2_id_break' => null,
		'2_uf_desc' => null,
        '3_provider_id' => null,
        '3_desc_order' => null,
        '3_order_id_n' => null
    );
	$totals = array();
	$total_amount_1 = 0;
	$total_amount_2 = 0;
    $total_amount_3 = 0;
    $total_costUF_3 = 0;
    $html = '';
	$html_2 = '';
	$html_3 = '';
    while ($row = $rs->fetch_array()) {
        if ($brk['2_id_break'] != $row['uf_id']) {
			$html_2 .= providerOrderHtml_end($html_3,$brk, 
				$total_amount_2, $total_amount_3, $total_costUF_3, $totals);				
			$total_amount_1 += $total_amount_2;
			
			$html .= ufHtml_end($row, $detail, $html_2, $brk, $total_amount_2);
			
			providerOrderHtml_start($row, $html_3,
				$brk, $total_amount_2, $total_amount_3, $total_costUF_3, $totals);
            
        }
        if ($brk['3_provider_id'] != $row['provider_id'] || 
                    $brk['3_order_id'] != $row['order_id']) {
            $html_2 .= providerOrderHtml_end($html_3, $brk, 
				$total_amount_2, $total_amount_3, $total_costUF_3, $totals);
			providerOrderHtml_start($row, $html_3, $brk, 
				$total_amount_3, $total_costUF_3, $totals);
        }
		// Rounded by UF
		$total_costUF_3 += round($row['cost_price']*$row['quantity'], 2);
        $amount = round($row['final_price']*$row['quantity'], 2);
		$total_amount_3 += $amount;
        if ($detail) {
            $html_3 .= '<div style="border-bottom: dotted 1px #777; margin-left:.5cm; padding:.05cm 0">';
            $html_3 .= 
                '<div class="cel8">'.$row['p_name'].'</div>'.
                '<div class="cel1h num">'.
                        clean_zeros($row['quantity']).'</div>'.
                '<div class="cel4 lite">'.$row['um_name'].'</div>'.
                '<div class="cel2 num lite">'.number_format($amount,2).'</div>';
            $html_3 .= '</div>'.chr(10);
        }
    }
	$html_2 .= providerOrderHtml_end($html_3, $brk, $total_amount_2, $total_amount_3, $total_costUF_3, $totals);
	$total_amount_1 += $total_amount_2;
    $html .= ufHtml_end(null, $detail, $html_2, $brk, $total_amount_2);
	
    //Totals finals per comanda
	if ($total_amount_1) {
		$html .= '<div class="block">'.chr(10);
		$html .= '<h2>Total comandes del dia: '.$date_for_order.'</h2>'.chr(10);
		foreach ($totals as $key => $total) {
			$html .= '<div style="border-bottom: dotted 1px #777; padding:.05cm 0">';
			$html .= 
				'<div class="cel12">'.$key.'</div>'.
				'<div class="cel3 num lite gray">Total final UFs</div>'.
				'<div class="cel2 num">'.number_format($total,2).'</div>';
			$html .= '</div>'.chr(10);
		}	
		$html .= '<div style="border-bottom: dotted 1px #777; padding:.05cm 0; margin-top:.2cm;">';
		$html .= 
			'<b class="cel12">Total dia: '.$date_for_order.'</b>'.
			'<div class="cel3 num lite gray">Total final UFs</div>'.
			'<b class="cel2 num">'.number_format($total_amount_1,2).'</b>';
		$html .= '</div>'.chr(10);
		$html .= '</div>'.chr(10);
	}
    return $html;
}
function ufHtml_end($row, $detail, &$html_2, &$brk, &$total_amount_2) {
	// end provider-order
	$html = '';
	if ($brk['2_id_break'] !== null) {
		$html .= '<div class="block">';
		$html .= 
			'<h3 style="border-bottom: dotted 1px #777">'.
				'<div class="cel10">'.$brk['2_uf_desc'].'</div>'.
				'<div class="cel1h">&nbsp;</div>'.
				'<div class="cel4 num lite"></div>'.
				'<div class="cel3 num"><span class="lite">Final: </span><b>'.
					number_format($total_amount_2, 2).
					'</b></div>'.
				'</h3>'.chr(10);
		if ($detail) {
			$html .= '<div class="block-body">'.chr(10).
					$html_2;
			$html .= '</div>'.chr(10);
		}
		$html .= '</div>'.chr(10);
	}
	// start uf
	if ($row) {
		$brk['2_id_break'] = $row['uf_id'];
		$brk['2_uf_desc'] = $row['uf_name'].'#'.$row['uf_id'];
	}
	// Clear
	$total_amount_2 = 0;
	$html_2 = '';
	return $html;
}
function providerOrderHtml_end(&$html_3, &$brk, &$total_amount_2, &$total_amount_3, &$total_costUF_3, &$totals) {
    $html = '';
    if ($brk['3_desc_order'] !== null) {
		$html .=	'<div style="border-bottom: dotted 1px #777; padding:.05cm 0">';
		$html .= 
			'<div class="cel12 gray">'.$brk['3_desc_order'].'</div>'.
			'<div class="cel1h num"></div>'.
			'<div class="cel2 lite"></div>'.
			'<div class="cel2 num">'.number_format($total_amount_3,2).'</div>';
		$html .= '</div>'.chr(10);
		$html .= $html_3;
		$total_amount_2 += $total_amount_3;
		if (!isset($totals[$brk['3_desc_order']])) {
			$totals[$brk['3_desc_order']] = 0;
		}
		$totals[$brk['3_desc_order']] +=$total_amount_3 ;
    }
	return $html;
}
function providerOrderHtml_start($row, &$html_3, &$brk, &$total_amount_3, &$total_costUF_3, &$totals) {
	// Start
	if ($row) {
		$brk['3_order_id'] = $row['order_id'];
		$brk['3_provider_id'] = $row['provider_id'];
		$brk['3_desc_order'] = $row['pv_name'].
			': '.$row['date_for_order'].
			' #'.($row['order_id']==null ? '??' : $row['order_id']);
	}
	// Clear
	$total_amount_3 = 0;
	$total_costUF_3 = 0;
	$html_3 = '';
}
?>
</body>
</html>
