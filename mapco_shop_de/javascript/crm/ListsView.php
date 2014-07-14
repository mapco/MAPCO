<?php
	include("../../config.php");
	header('Content-type: text/javascript');
	
	//make dreamweaver highlight javascript
	if(true==false) { ?> 	<script type="text/javascript"> <?php }
?>

function load_lists(type_id)
{
	var where = '';
	
	if(type_id != 0)
	{
		where += 'WHERE type='+type_id;
		if (type_id == 1) 
		{
			where += ' AND firstmod_user='+<?php print $_SESSION['id_user'] ?>;	
		}
	}
	
	wait_dialog_show('Lade Listen');
	$.post("<?php echo PATH; ?>soa2/", { API:"cms", APIRequest:"TableDataSelect", where:where, db:'dbweb', table:'crm_costumer_lists' }, function($data){ 
		show_status2($data); return;
		try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
		if($xml.find('Ack').text()!='Success'){show_status2($data);return;}	
		
		var lists = '<ul id="ul_lists" class="orderlist ui-sortable" style="width:100%;">';
		lists += '	<li class="header" style="width:99%;">Listen</li>';
		if($xml.find('num_rows').text()>0)
		{		
			$xml.find('crm_costumer_lists').each(function(){
				
				lists += '<li class="customer_list" id="list_'+$(this).find('id_list').text()+'" style="width:100%;">'+$(this).find('title').text()+'</li>';
			});
		}
		$("#c_lists").empty().append(lists);
		
		$(".customer_list").click(function(){
			var id = $(this).attr('id');
			id = id.split('_');
			load_customers(id[1]);
		});
		
		wait_dialog_hide();
	});
}

function load_customer_list_types()
{
	wait_dialog_show('Lade Typenliste');
	$.post("<?php echo PATH; ?>soa2/", { API:"crm", APIRequest:"CustomerListTypesGet" }, function($data){ 
		//show_status2($data); return;
		try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
		if($xml.find('Ack').text()!='Success'){show_status2($data);return;}
		
		var type_title = '';
		var id = '';
		var listcount = 0;
		var types = '<ul id="ul_types" class="orderlist ui-sortable" style="width:100%;">';
		types += '	<li class="header" style="width:99%;">Listentypen</li>';
			
		$xml.find('list_type').each(function(){
			type_id = $(this).find('type_id').text();
			listcount = $(this).find('listcount').text();
			types += '<li class="customer_list_type" id="type_'+type_id+'" style="width:100%;">'+$(this).find('title').text()+' ('+listcount+')</li>';
		});
		types += '</ul>';
	
		$("#c_list_types").empty().append(types);
		
		$(".customer_list_type").click(function(){
			var id = $(this).attr('id');
			id = id.split('_');
			load_lists(id[1]);
		});
		
		wait_dialog_hide();
	});
}

function load_customers(id_list)
{
	wait_dialog_show('Lade Kunden');
	$.post("<?php echo PATH; ?>soa2/", { API:"crm", APIRequest:"GetCustomerFromList", id_list:id_list }, function($data){ 
		//show_status2($data); return;
		try{$xml = $($.parseXML($data))} catch($err){show_status2($err.message);return;}
		if($xml.find('Ack').text()!='Success'){show_status2($data);return;}	
		
		var customers = '<ul id="ul_customers" class="orderlist ui-sortable" style="width:100%;">';
		customers += '	<li class="header" style="width:99%;">Kunden</li>';		
		$xml.find('customer').each(function(){
			customers += '	<li style="width:99%;" value="'+$(this).find('customer_id').text()+'">'+$(this).find('name').text()+'</li>';
		});
		customers += '</ul>';

		$("#c_lists_customers").empty().append(customers);	
		
		wait_dialog_hide();
	});
}