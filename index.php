<?php include "inc/header.inc.php" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=$language;?>" lang="<?=$language;?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $Text['global_title']; ?></title>

 	<link rel="stylesheet" type="text/css"   media="screen" href="css/aixada_main.css" />
  	<link rel="stylesheet" type="text/css"   media="print"  href="css/print.css" />
  	<link rel="stylesheet" type="text/css"   media="screen" href="js/aixadacart/aixadacart.css" />
  	<link rel="stylesheet" type="text/css"   media="screen" href="js/fgmenu/fg.menu.css"   />
    <link rel="stylesheet" type="text/css"   media="screen" href="css/ui-themes/<?=$default_theme;?>/jqueryui.css"/>
     
    
    <?php if (isset($_SESSION['dev']) && $_SESSION['dev'] == true ) { ?> 
	    <script type="text/javascript" src="js/jquery/jquery.js"></script>
		<script type="text/javascript" src="js/jqueryui/jqueryui.js"></script>
		<script type="text/javascript" src="js/fgmenu/fg.menu.js"></script>
		<script type="text/javascript" src="js/aixadautilities/jquery.aixadaMenu.js"></script>     	 
	   	<script type="text/javascript" src="js/aixadautilities/jquery.aixadaXML2HTML.js" ></script>
	   	<script type="text/javascript" src="js/aixadautilities/jquery.aixadaUtilities.js" ></script>
	   	<script type="text/javascript" src="js/aixadacart/jquery.aixadacart.js" ></script>   	    
   	<?php  } else { ?>
	   	<script type="text/javascript" src="js/js_for_index.min.js"></script>
    <?php }?>
     
    <script type="text/javascript" src="js/aixadacart/i18n/cart.locale-<?=$language;?>.js" ></script>
	   
	<script type="text/javascript">
	$(function(){

			$('#loadingMsg').hide();

			$( "#rightSummaryCol" ).tabs({
				select : function (e, ui){
					if ($(this).tabs( "option", "selected" ) == 0){
						$('#tbl_Shop tbody').xml2html('reload'); //load purchase list when switching tabs
					}
				}
	
			});

			$('#tmp').hide();



			/********************************************************
			 *      My ORDERS
			 ********************************************************/
			 
			var lastDate = '';
			$('#tbl_Orders tbody').xml2html('init',{
				url : 'ctrlOrders.php',
				params : 'oper=getOrdersListingForUf&uf_id=-1&filter=pastMonths2Future', 
				loadOnInit : true, 
				rowComplete : function(rowIndex, row){
					var orderId = $(row).attr('orderId');
					var timeLeft = $(row).children().eq(2).text();
					
					//var revisionStatus = $(row).attr('revision_status');
					
					if (orderId > 0){ //order has been send
						$(row).children().eq(3).text('expected');
					} else {
						 $(row).children().eq(3).text('not yet send');		
					}

					if (timeLeft <= 0){
						$(row).children().eq(2).html('<span class="ui-icon ui-icon-locked tdIconCenter" title="order is closed"></span>');
					} 
					
					var date = $(row).attr('dateForOrder');
					if (date != lastDate) $(row).before('<tr><td colspan="6">&nbsp;</td></tr><tr><td colspan="5" class="dateRow ui-corner-all"><p>Ordered for <span class="boldStuff">'+date+'</span></p></td><td><p class="ui-corner-all iconContainer ui-state-default printOrderIcon" dateForOrder="'+date+'"><span class="ui-icon ui-icon-print" title="Print order"></span></p></td></tr>');
					lastDate=date; 	

				},
				complete : function(rowCount){
					globalRowIndex = 0; 
					loadOrderDetails();
				}
			});


			
			var globalRowIndex = 0; 
		
			function loadOrderDetails(){

				//get either order_id OR date and provider for those orders that are still open/not finalized
				var orderId = $('#tbl_Orders tbody tr').eq(globalRowIndex).attr('orderId');
				var dateForOrder = $('#tbl_Orders tbody tr').eq(globalRowIndex).attr('dateForOrder');
				var providerId = $('#tbl_Orders tbody tr').eq(globalRowIndex).attr('providerId');
				
				if (orderId > 0) {
					$('#tbl_diffOrderShop').attr('currentOrderId',orderId);
					$('#tbl_diffOrderShop tbody').xml2html('reload', {
						params : 'oper=getDiffOrderShop&order_id='+orderId, 
					});	

				} else if (providerId > 0){
					$('#tbl_diffOrderShop').attr('currentDateForOrder',dateForOrder);
					$('#tbl_diffOrderShop').attr('currentProviderId',providerId);
					$('#tbl_diffOrderShop tbody').xml2html('reload', {
						params : 'oper=getProductQuantiesForUfs&uf_id=-1&provider_id='+providerId + '&date_for_order='+dateForOrder, 
					});	
					
				} else {
					$('#tbl_diffOrderShop').attr('currentOrderId','');
					$('#tbl_diffOrderShop').attr('currentDateForOrder','');
					$('#tbl_diffOrderShop').attr('currentProviderId','');
					
					globalRowIndex++;
					if (globalRowIndex <= $('#tbl_Orders tbody').children().length) loadOrderDetails();
				
				}


			}

			
			//tmp table to load the order - shop comparison
			$('#tbl_diffOrderShop tbody').xml2html('init',{
				url : 'ctrlOrders.php',
				params : 'oper=getDiffOrderShop', 
				loadOnInit : false, 
				rowComplete : function (rowIndex, row){
					var qu = $(row).children().eq(3).text();
					if (isNaN(qu)) $(row).children().eq(3).text("-");
				},
				complete : function(rowCount){
					if (rowCount >0){
						
						var orderId = $('#tbl_diffOrderShop').attr('currentOrderId');
						var dateForOrder = $('#tbl_diffOrderShop').attr('currentDateForOrder');
						var providerId = $('#tbl_diffOrderShop').attr('currentProviderId');
						
						var header = $('#tbl_diffOrderShop thead tr').clone();
						var itemRows = $('#tbl_diffOrderShop tbody tr').clone();

						if (orderId > 0){
							var revision = $('#order_'+orderId).attr('revisionStatus');
							$('#order_'+orderId).after(itemRows).after(header);
							$('.detail_'+orderId).hide().prev().hide();

							
							var modClass = '';
							var modTxt = ''; 

							
							if (revision == 1){ 		//default state; send off and awaited for delivery
								modTxt = 'not yet received';
								
							} else if (revision == 2){	//arrived and has been revised

								var modifications = false; 
								
								//check if ordered quantities and delivered are the same
								$('#tbl_diffOrderShop tbody').children('tr').each(function(){
									//each row
									var id = $(this).children().eq(1).text();
									var orderedQu = $(this).children().eq(2).text();
									var deliveredQu = $(this).children().eq(3).text();

									if (orderedQu != deliveredQu) modifications = true; 
									
								});

								if (modifications){
									modClass = "withChanges";
									modTxt = "with changes";
								} else {
									modClass = "asOrdered";
									modTxt = "is complete"; 
								}
								
							} else if (revision == 3){ //postponed
								modTxt = "postponed";	
								
							} else if (revision == 4){ //canceled. Will never arrive
								modClass="orderCanceled";
								modTxt = "canceled";
							}


							$('#order_'+orderId).children().eq(3).addClass(modClass).text(modTxt);
							

						} else if (providerId > 0){  //not yet send / closed order
							$('.Date_'+dateForOrder+'.Provider_'+providerId).after(itemRows).after(header);
							$('.detail_date_'+dateForOrder+'.detail_provider_'+providerId).hide().prev().hide();
							//$('.Date_'+dateForOrder+'.Provider_'+providerId).children().eq(4).text("-");

						}

						globalRowIndex++;
						if (globalRowIndex <= $('#tbl_Orders tbody').children().length) {
							loadOrderDetails();
						}
						
					} 

					
					
				}
			});
			
			
			$('.expandOrderIcon').live('click', function(){

				var orderId = $(this).parents('tr').attr('orderId');
				var dateForOrder =  $(this).parents('tr').attr('dateForOrder');
				var providerId =  $(this).parents('tr').attr('providerId');

				var selector = (orderId > 0)? '.detail_'+orderId:'.detail_date_'+dateForOrder+'.detail_provider_'+providerId; 
							
				if ($('span',this).hasClass('ui-icon-plus')){
					$('span',this).removeClass('ui-icon-plus').addClass('ui-icon-minus');
					$(selector).show().prev().show();
					$(this).parents('tr').children().addClass('ui-state-highlight ui-corner-all');
				} else {
					$('span',this).removeClass('ui-icon-minus').addClass('ui-icon-plus');
					$(selector).hide().prev().hide();
					$(this).parents('tr').children().removeClass('ui-state-highlight');
				}
			})
			
			
			var printWin = null;
			$('.printOrderIcon').live('click', function(){

				var dateForOrder = $(this).attr('dateForOrder');
				
				printWin = window.open('tpl/<?=$tpl_print_myorders;?>?date='+dateForOrder);
				printWin.focus();
			});

			//show older orders dates
			var orderDateSteps = 2;
			var orange = 'month';
			$('#btn_prevOrders').button({
				icons : {
					primary:"ui-icon-seek-prev"
					}
			})
			.click( function(e){
					orderDateSteps++;	
					$('#tbl_Orders tbody').xml2html('reload',{
						url : 'ctrlOrders.php',
						params : 'oper=getOrdersListingForUf&uf_id=-1&filter=steps&steps='+orderDateSteps+'&range='+orange
					});

			});

			//show more recent order dates
			$('#btn_nextOrders').button({
				icons : {
					secondary:"ui-icon-seek-next"
					}
			})
			.click( function(e){
				orderDateSteps--;	
				$('#tbl_Orders tbody').xml2html('reload',{
					url : 'ctrlOrders.php',
					params : 'oper=getOrdersListingForUf&uf_id=-1&filter=steps&steps='+orderDateSteps+'&range='+orange
				});

			});
			
			
			
			/********************************************************
			 *      My PURCHASE
			 ********************************************************/
			var shopDateSteps = 1;
			var srange = 'month';

			//load purchase listing
			$('#tbl_Shop tbody').xml2html('init',{
					url : 'ctrlShop.php',
					params : 'oper=getShopListingForUf&uf_id=-1&filter=steps&steps='+shopDateSteps+'&range='+srange, 
					loadOnInit : false, 
					rowComplete : function(rowIndex, row){
						var validated = $(row).children().eq(2).text();

						if (validated == '0000-00-00 00:00:00'){
							$(row).children().eq(2).html("-");	
						} else {
							$(row).children().eq(2).html('<span class="ui-icon ui-icon-check tdIconCenter" title="Validated at: '+validated+'"></span>');
						}

						
					}
			});

			//load purchase detail (products and quantities)
			$('#tbl_purchaseDetail tbody').xml2html('init',{
				url : 'ctrlShop.php',
				params : 'oper=getShopDetail', 
				loadOnInit : false, 
				rowComplete : function (rowIndex, row){
					var price = new Number($(row).children().eq(5).text());
					var qu = new Number($(row).children().eq(3).text());
					var totalPrice = price * qu;
					totalPrice = totalPrice.toFixed(2);
					$(row).children().eq(5).text(totalPrice);
					
				},
				complete : function(rowCount){

					var shopId = $('#tbl_purchaseDetail').attr('currentShopId');
					var header = $('#tbl_purchaseDetail thead tr').clone();
					var itemRows = $('#tbl_purchaseDetail tbody tr').clone();

					$('#shop_'+shopId).after(itemRows).after(header);
					
				}
			});
			

			$('.expandShopIcon').live('click', function(){

				var shopId = $(this).parents('tr').attr('shopId');
				var dateForShop = $(this).parents('tr').attr('dateForShop');

				$('#tbl_purchaseDetail').attr('currentShopId', shopId);
				$('#tbl_purchaseDetail').attr('currentDateForShop', dateForShop);
				
				
							
				if ($('span',this).hasClass('ui-icon-plus')){
					$('span',this).removeClass('ui-icon-plus').addClass('ui-icon-minus');
					$(this).parents('tr').children().addClass('ui-state-highlight ui-corner-all');

					$('#tbl_purchaseDetail tbody').xml2html('reload',{
						params : 'oper=getShopDetail&shop_id='+shopId
					});

					
				} else {
					$('span',this).removeClass('ui-icon-minus').addClass('ui-icon-plus');
					$(this).parents('tr').children().removeClass('ui-state-highlight');
					$('#shop_'+shopId).next().hide();
					$('.detail_shop_'+shopId).hide();
				}
			})
			
			//print purchase / order
			$('.printShopIcon').live('click', function(){

				var shopId = $(this).parents('tr').prev().attr('shopId');
				var date = $(this).parents('tr').prev().attr('dateForShop');
				var op_name = $(this).parents('tr').prev().attr('operatorName');
				var op_uf = $(this).parents('tr').prev().attr('operatorUf');
				

				
				printWin = window.open('tpl/<?=$tpl_print_bill;?>?shopId='+shopId+'&date='+date+'&operatorName='+op_name+'&operatorUf='+op_uf);
				printWin.focus();
			});

			//show older purchase dates
			$('#btn_prevPurchase').button({
				icons : {
					primary:"ui-icon-seek-prev"
					}
			})
			.click( function(e){
					shopDateSteps++;	
					$('#tbl_Shop tbody').xml2html('reload',{
						url : 'ctrlShop.php',
						params : 'oper=getShopListingForUf&uf_id=-1&filter=steps&steps='+shopDateSteps+'&range='+srange
					});

			});

			//show older purchase dates
			$('#btn_nextPurchase').button({
				icons : {
					secondary:"ui-icon-seek-next"
					}
			})
			.click( function(e){
				shopDateSteps--;	
				$('#tbl_Shop tbody').xml2html('reload',{
					url : 'ctrlShop.php',
					params : 'oper=getShopListingForUf&uf_id=-1&filter=steps&steps='+shopDateSteps+'&range='+srange
				});

			});
			


			
			
			$('.iconContainer')
			.live('mouseover', function(e){
				$(this).addClass('ui-state-hover');
			})
			.live('mouseout', function (e){
				$(this).removeClass('ui-state-hover');
			});
			
	});  //close document ready
</script>


</head>
<body>
<div id="wrap">
	<div id="headwrap">
		<?php include "inc/menu2.inc.php" ?>
	</div>
	<!-- end of headwrap -->
	<div id="stagewrap">
		<div id="homeWrap">
			<div id="leftIconCol">
				<div class="homeIcon">
					<a href="shop_and_order.php?what=Shop"><img src="img/cesta.png"/></a>
					<p><a href="shop_and_order.php?what=Shop"><?php echo $Text['icon_purchase'];?></a></p>
				</div>
				<div class="homeIcon">
					<a href="shop_and_order.php?what=Order"><img src="img/pedido.png"/></a>
					<p><a href="shop_and_order.php?what=Order"><?php echo $Text['icon_order'];?></a></p>
				</div>
				<div class="homeIcon">
					<a href="incidents.php"><img src="img/incidencias.png"/></a>
					<p><a href="incidents.php"><?php echo $Text['icon_incidents'];?></a></p>
				</div>
			</div>
			<div id="rightSummaryCol">
				<ul>
					<li><a href="#tabs-1"><h2>My Order(s)</h2></a></li>
					<li><a href="#tabs-2"><h2>My Purchase(s)</h2></a></li>	
				</ul>
			
				<div id="tabs-1">
					<table id="tbl_Orders">
						<tbody>
							<tr id="order_{id}" orderId="{id}" dateForOrder="{date_for_order}" providerId="{provider_id}" class="Date_{date_for_order} Provider_{provider_id}" revisionStatus="{revision_status}">
								<td><p class="iconContainer ui-corner-all ui-state-default expandOrderIcon"><span class="ui-icon ui-icon-plus"></span></p></td>
								<td>{provider_name}</td>
								<td class="textAlignCenter">{time_left}</td>
								<td class="textAlignCenter">Loading status info...</td>
								<td class="textAlignRight">{order_total}€</td>
								<td>&nbsp;</td>
							</tr>
						</tbody>
						<tfoot>
						<tr>
								<td colspan="6">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="6">
									<p class="textAlignCenter">
										<button id="btn_prevOrders">Previous</button>&nbsp;&nbsp;Dates&nbsp;&nbsp;
										<button id="btn_nextOrders">Next</button></p>
									</td>
								
								
							</tr>
						
						</tfoot>
					</table>
				</div>
				
				<div id="tabs-2">
					<table id="tbl_Shop" class="table_overviewShop">
						<thead>
							<tr >
								<th></th>
								<th class="textAlignCenter">Date of purchase</th>
								<th class="textAlignCenter" colspan="3">Validated</th>
								<th class="textAlignRight">Total</th>
							</tr>
						</thead>
						<tbody>
							<tr id="shop_{id}" shopId="{id}" dateForShop="{date_for_shop}" operatorName="{operator_name}" operatorUf="{operator_uf}">
								<td><p class="iconContainer ui-corner-all ui-state-default expandShopIcon"><span class="ui-icon ui-icon-plus"></span></p></td>
								<td class="textAlignCenter">{date_for_shop}</td>
								<td class="textAlignCenter" colspan="3">{ts_validated}</td>
								<td class="textAlignRight">{purchase_total}€</td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="6">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="6">
									<p class="textAlignCenter">
										<button id="btn_prevPurchase">Previous</button>&nbsp;&nbsp;Dates&nbsp;&nbsp;
										<button id="btn_nextPurchase">Next</button></p>
									</td>
								
								
							</tr>
						</tfoot>
					</table>
				</div>
			</div>			
		</div>
	</div>
	
	<!-- end of stage wrap -->
</div>

<div id="tmp">
<table id="tbl_diffOrderShop" currentOrderId="" currentDateForOrder="" currentProviderId="">
	<thead>
		<tr>
			<td class="tdMyOrder">id</td>
			<td class="tdMyOrder" colspan="2">Product</td>
			<td class="tdMyOrder">Ordered</td>
			<td class="tdMyOrder">Delivered</td>
			<!-- td class="tdMyOrder">Price</td-->		
		</tr>
	</thead>
	<tbody>
		<tr class="detail_{order_id} detail_date_{date_for_order} detail_provider_{provider_id}">
			<td class="MyOrderItem">{product_id}</td>
			<td class="MyOrderItem" colspan="2">{name}</td>
			<td class="MyOrderItem">{quantity}</td>
			<td class="MyOrderItem">{shop_quantity}</td>
			<!-- td class="MyOrderItem">{unit_price}</td-->
		</tr>
	</tbody>
</table>

<table id="tbl_purchaseDetail" currentShopId="" currenShopDate="">
	<thead>
		<tr>
			<td><p class="ui-corner-all iconContainer ui-state-default printShopIcon"><span class="ui-icon ui-icon-print" title="Print bill"></span></p></td>
			<th><?php echo $Text['name_item'];?></th>	
			<th><?php echo $Text['provider_name'];?></th>					
			<th class="textAlignCenter">Qu</th>
			<th><?php echo $Text['unit'];?></th>
			<th class="textAlignRight">Price</th>
			
			
			
		</tr>
	</thead>
	<tbody>
		<tr class="detail_shop_{cart_id}">
			<td></td>
			<td class="MyShopItem">{name}</td>
			<td class="MyShopItem">{provider_name}</td>
			<td class="MyShopItem textAlignCenter">{quantity}</td>
			<td class="MyShopItem">{unit}</td>
			<td class="MyShopItem textAlignRight">{unit_price}</td>	
			
		</tr>						
	</tbody>
	<tfoot>
		<tr>
			<td>&nbsp;</td>
			<td colspan="5">			
		</tr>
	</tfoot>
</table>

				
</div>



<!-- end of wrap -->
<div id="dialog-message" title="">
		 <p id="loadingMsg" class="ui-state-highlight"><?php echo $Text['loading'];?></p>
		 <div id="cartLayer"></div>
</div>

<!-- / END -->
</body>
</html>