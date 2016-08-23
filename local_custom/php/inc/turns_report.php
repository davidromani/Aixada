<?php
function write_turn_fromDate($from_date) {
    return write_turn_where(
        "uf.active = m.active and date_turn >='{$from_date}'"
    );
}
function write_turn_uf($uf_id, $from_date) {
    return write_turn_where("t.date_turn in(
        select date_turn
        from cistella_turn tuf
        where tuf.uf_id={$uf_id} and date_turn >='{$from_date}'
        group by date_turn
    )");
}
function write_turn_where($where) {
    $db = DBWrap::get_instance();
    $strSQL = 'select date_turn,'.
        ' t.uf_id, uf.active uf_active, uf.name uf_name,'.
        ' min(m.name) min_m_name, max(m.name) max_m_name'.
        ' from cistella_turn t'.
        ' join (aixada_uf uf, aixada_member m)'.
        ' on (t.uf_id=uf.id and uf.id=m.uf_id)'.
        ' where '.$where.
        ' group by date_turn, t.uf_id, uf.name'.
        ' order by date_turn, uf.name';
    $rs = $db->Execute($strSQL);
    $brk = array(
        '1_brk' => null
    );
    $html = '';
    while ($row = $rs->fetch_array()) {
        if ($brk['1_brk'] != $row['date_turn']) {
            if ($brk['1_brk'] != null) {
                $html .= '</div>'.chr(10);
            }
            $brk['1_brk'] = $row['date_turn'];
            $html .= '<div class="block" style="margin: 0.3cm; border-top:1px solid #ccc;">';
            $html .= '<div>';
            $html .= '<div class="cel4">'.$row['date_turn'].'</div>';
        } else {
            $html .= '<div>';
            $html .= '<div class="cel4">&nbsp;</div>';
        }
        $html .= '<div class="cel12">';
            if ($row['uf_active'] == 0) {
                $html .= '<span style="color:red">*ÉS BAIXA*</span> ';
            }
            $html .= $row['uf_name'].'#'.$row['uf_id'];
            if ($row['min_m_name'] == $row['max_m_name']) {
                $html .= ' ('.$row['max_m_name'].')';
            } else {
                $html .= ' ('.$row['min_m_name'].' - '.$row['max_m_name'].')';
            }
            $html .= '</div>';
        $html .= '</div>'.chr(10);
    }
    if ($brk['1_brk'] != null) {
        $html .= '</div>'.chr(10);
    }
    return $html;
}
?>
