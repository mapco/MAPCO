<?php
 
 	include("config.php");
	include("functions/cms_tl.php");
	
	$_GET['id_order'] = 0;
	
	if( isset( $_GET["getvars1"] ) )
	{
		$_GET["id_order"] = $_GET["getvars1"];
		
		if ( !isset( $_SESSION['id_user'] ) )
		{
			header("HTTP/1.1 301 Moved Permanently");
			header("location: ".PATHLANG.$_GET['static'] );
			exit;
		}
		else
		{
			$res = q( "SELECT * FROM shop_orders WHERE shop_id=" . $_SESSION['id_shop'] . " AND id_order=" . $_GET['id_order'] . " AND customer_id=" . $_SESSION['id_user'], $dbshop, __FILE__, __LINE__ );
			if ( mysqli_num_rows( $res ) != 1 )
			{
				header("HTTP/1.1 301 Moved Permanently");
				header("location: ".PATHLANG.$_GET['static'] );
				exit;
			}
			
			if ( $_GET['url'] != $_GET['static'] . $_GET['id_order'] . '/' )
			{
				header("HTTP/1.1 301 Moved Permanently");
				header("location: " . PATHLANG . $_GET['static'] . $_GET['id_order'] . '/' );
				exit;
			}
		}
	}
		
	$menu_hide = true; // Menü ausblenden

	include("templates/".TEMPLATE."/header.php");
	
?>

<script type="text/javascript">
	
	$( document ).ready(function()
	{
		shop_checkout_order_main();
	});
/*	
	function checkout_order_id_unset()
	{
		wait_dialog_show();
		
		$postdata = 				new Object();
		$postdata['API'] = 			'cms';
		$postdata['APIRequest'] = 	'VariableUnset';
		$postdata['key'] = 			'checkout_order_id';
		
		soa2( $postdata, 'checkout_order_id_unset_callback' );
	}
	
	function checkout_order_id_unset_callback()
	{
		alert( 'unsetted' );
	}
*/	
	function order_set()
	{
		wait_dialog_show();
		
		$postdata = 				new Object;
		$postdata['API'] = 			'shop';
		$postdata['APIRequest'] = 	'CheckoutOrderSet';
		$postdata['get_id_order'] = <?php echo $_GET['id_order'];?>;

		soa2( $postdata, 'order_set_callback', 'xml' );

	}
	
	function order_set_callback( $xml )
	{
		//show_status2( $xml + '<?php echo str_replace( "\n", "", print_r( $_SESSION, true )); ?>' );
		location.href = '<?php echo PATHLANG . tl(667, "alias");?>';
	}
	
/*
	function session_show()
	{
		show_status2( '<?php echo str_replace( "\n", "", print_r( $_SESSION, true )); ?>' );
	}
*/
	function shop_checkout_order_main()
	{	
//		alert( 'test');
//		show_status2( '<?php echo str_replace( "\n", "", print_r( $_SESSION, true )); ?>' );
/*		
		$( '#main_div' ).append( $( '<input type="button" id="unset_checkout_order_id_button" value="unset checkout_order_id">' ) );
		
		$( '#unset_checkout_order_id_button' ).click(function()
		{
			checkout_order_id_unset();
		});
		
		$( '#main_div' ).append( $( '<input type="button" id="session_show_button" value="session_show">' ) );
		
		$( '#session_show_button' ).click(function()
		{
			session_show();
		});
*/		
		order_set();
				
	}
	
</script>
		
<?php
	
	echo '<div id="main_div"></div>';
	include("templates/".TEMPLATE."/footer.php");
	
?>