<?php

function get_SQL($date_for_order) {
    return "select * from (
		select
			oi.date_for_order, oi.product_id,
			if(os.quantity is not null, os.quantity*os.arrived, oi.quantity) quantity,
			ifnull(os.unit_price_stamp, oi.unit_price_stamp) final_price, 
			if( os.unit_price_stamp is not null,
				round(
					os.unit_price_stamp / 
					(1 + os.iva_percent/100) / 
					(1 + os.rev_tax_percent/100), 2),
				p.unit_price
			) cost_price,
			oi.order_id,
			o.revision_status,
			uf.id uf_id, uf.name uf_name,
			p.name p_name, p.provider_id, pv.name pv_name,
			um.name um_name
		from aixada_order_item oi
		join (
			aixada_uf uf,
			aixada_product p,
            aixada_unit_measure um,
			aixada_provider pv )
		on 
			oi.uf_id = uf.id and
			oi.product_id = p.id and
			p.unit_measure_order_id = um.id and
			p.provider_id = pv.id
		left join (
			aixada_order o,
			aixada_order_to_shop os )
		on 
			oi.order_id=o.id and
			oi.id = os.order_item_id
		where oi.date_for_order='{$date_for_order}'
	) r
	order by ";
}
function formatOrderStatus($revision_status) {
    global $Text;
        switch ($revision_status){
            case null:
                $text_key = 'not_yet_sent';
                break;                
            case "1":
                $text_key = 'ostat_yet_received';
                break;
            case "2": 
               $text_key = 'ostat_is_complete';
                break;
            case "3": 
                $text_key = 'ostat_postponed';
                break;
            case "4": 
                $text_key = 'ostat_canceled';
                break;
            case "5": 
                $text_key = 'ostat_changes';
                break;
            default:
                return '<span class="DATA-not_yet_sent">??OrderStatus="'.
                    $revision_status.'"??</span>';
        
    }
    return '<span class="DATA-'.$text_key.'">'.$Text[$text_key].'</span>';
}

function break2Html_end(&$brk, $detail) {
    $html = '';
    if ($brk['2_id_break'] != null) {
        if ($detail) {
            $html .= '</div>';
        }
        $html .= '</div>'.chr(10);
        $brk['2_id_break'] = null;
    }
    return $html;
}
function get_sum($db, $date_for_order, $provider_id, $order_id, $whereSQL) {
    $sql = 
        "select 
			if(os.quantity is not null, os.quantity*os.arrived, oi.quantity) quantity,
			ifnull(os.unit_price_stamp, oi.unit_price_stamp) final_price, 
			if( os.unit_price_stamp is not null,
				round(
					os.unit_price_stamp / 
					(1 + os.iva_percent/100) / 
					(1 + os.rev_tax_percent/100), 2),
				p.unit_price
			) cost_price
		from aixada_order_item oi
		join (
			aixada_product p )
		on
			oi.product_id = p.id 
		left join (
			aixada_order_to_shop os )
		on
			oi.id = os.order_item_id
        where 
			{$whereSQL} and
			oi.date_for_order='{$date_for_order}' and 
			p.provider_id={$provider_id} and 
			oi.order_id ".($order_id == null ? 'is null' :'='.$order_id);
	$sql_sum = "
		select 
			sum(quantity) sum_quantity,
			sum(round(quantity * cost_price, 2)) sum_cost,
			sum(round(quantity * final_price, 2)) sum_amount 
		from ({$sql}) r;";
    return get_row_query($sql_sum);
}
?>
