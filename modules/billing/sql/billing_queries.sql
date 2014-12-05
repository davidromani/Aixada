delimiter |

/**
 *	create a new bill. this does not create the connection to the 
 *  individual carts. this is done with add_cart. 
 */
drop procedure if exists create_bill|
create procedure create_bill(in the_ref_bill varchar(150), 
							 in the_uf_id int, 
							 in the_operator_id int, 
							 in the_description varchar(255))
begin

	insert into
		aixada_bill (ref_bill, uf_id, operator_id, description, date_for_bill, ts_validated)
	values
		(the_ref_bill, the_uf_id, the_operator_id, the_description, now(), now());

	select last_insert_id();


end| 



/**
 *	creates the relations between the bill and corresponding carts. 
 */
drop procedure if exists add_cart_to_bill|
create procedure add_cart_to_bill(in the_bill_id int, in the_cart_id int )
begin

	insert into
		aixada_bill_rel_cart (bill_id, cart_id)
	values
		(the_bill_id, the_cart_id);

end| 



/**
 *	retrieve row of given cart. check if validated, the uf it pertains to. 
 */
drop procedure if exists get_cart|
create procedure get_cart(in cart_id int)
begin
	select
		*
	from 
		aixada_cart
	where
		id=cart_id;

end|


/**
 *	retrieves bills either by ID, by uf_id and/or combination with date
 */
drop procedure if exists get_bills| 
create procedure get_bills(in the_uf_id int, in the_bill_id int, in from_date date, to_date date)
begin


	select 
		b.*,
		ifnull(mem.name, 'default') as operator,
		uf.name as uf_name,
		(select
				sum(get_purchase_total(c.id))
			from 
				aixada_cart c,
				aixada_bill_rel_cart bc
			where
				bc.cart_id = c.id
				and bc.bill_id = b.id
			) as total
		
	from 
		aixada_bill b,
		aixada_member mem,
		aixada_user u,
		aixada_uf uf 
	where
		b.uf_id = the_uf_id
		and b.uf_id = uf.id
		and b.operator_id = u.id
 		and u.member_id = mem.id; 
end|


drop procedure if exists get_tax_groups| 
create procedure get_tax_groups(in the_bill_id int)
begin

	select
		si.iva_percent,
		sum(si.quantity * (si.unit_price_stamp / (1+si.rev_tax_percent/100) / (1+si.iva_percent/100) )) as total_sale_netto,
		sum(si.quantity * (si.unit_price_stamp / (1+si.rev_tax_percent/100) / (1+si.iva_percent/100) ) * (si.iva_percent/100)) as iva_sale
	from 
		aixada_cart c, 
		aixada_shop_item si,
		aixada_bill_rel_cart bc
  	where
  		bc.bill_id = the_bill_id
  		and bc.cart_id = c.id
  		and c.id = si.cart_id
  	group by
  		si.iva_percent;



end|



/**
 *	retrieves product details of carts grouped in this bill
 */
drop procedure if exists get_bill_detail| 
create procedure get_bill_detail(in the_bill_id int)
begin
	
	
  select 
  	b.*,
    p.id as product_id,
    p.name as product_name,
    c.id as cart_id,
	c.date_for_shop,
    si.quantity as quantity,
    si.iva_percent,
    si.rev_tax_percent, 
    si.unit_price_stamp as unit_price,
    p.provider_id,  
    pv.name as provider_name,
    um.unit,
    (si.quantity * si.unit_price_stamp) as total
  from
  	aixada_cart c, 
  	aixada_shop_item si,
  	aixada_product p, 
  	aixada_provider pv, 
  	aixada_unit_measure um,
  	aixada_bill b,
  	aixada_bill_rel_cart bc
  where
  	b.id = the_bill_id
  	and bc.bill_id = b.id
  	and bc.cart_id = c.id
  	and si.cart_id = c.id
  	and si.product_id = p.id
  	and pv.id = p.provider_id
  	and um.id = p.unit_measure_shop_id
  order by c.id, p.provider_id;
end|



/**
 * returns listing of aixada_cart's for given uf and date range. 
 * including the name and uf of the validation operator. 
 * if uf_id is not set (0), then returns for all ufs 
 */
drop procedure if exists get_cart_listing|
create procedure get_cart_listing(in from_date date, in to_date date, in the_uf_id int, in the_limit varchar(255))
begin
	
	declare wherec varchar(255) default "";
	declare set_limit varchar(255) default ""; 
	
	-- filter by uf_id --
	if (the_uf_id > 0) then
		set wherec = concat(" and c.uf_id = ",the_uf_id," and uf.id = ",the_uf_id);
	else 
		set wherec = concat(" and c.uf_id = uf.id");
	end if; 
	
	-- set a limit?
    if (the_limit <> "") then
    	set set_limit = concat("limit ", the_limit);
    end if;
	
	
	
	set @q =  concat("select 
		c.*, 
		(select 
			bc.bill_id
		from 
			aixada_bill_rel_cart bc
		where
			bc.cart_id = c.id limit 1) as bill_id,
		uf.id as uf_id,
		uf.name as uf_name,
		m.name as operator_name,
		m.uf_id as operator_uf,
		get_purchase_total(c.id) as purchase_total
	from 
		aixada_uf uf,
		aixada_cart c,
		aixada_user u,
		aixada_member m
	where
		c.operator_id = u.id
		and u.member_id = m.id
		and c.date_for_shop between '",from_date,"' and '",to_date,"'
		",wherec,"
	order by 
		c.date_for_shop desc, uf.id desc
		",set_limit,";"); 
			
	prepare st from @q;
  	execute st;
  	deallocate prepare st;
end |


delimiter ; 