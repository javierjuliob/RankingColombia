.GTTabs_divs{
	padding: 4px;	
}


.GTTabs_titles{
	display:none;	
}

ul.GTTabs
	{
	<?php if ($GTTabs_options["layout"]=="vertical") echo "float: left;"; ?>
	width: <?php if ($GTTabs_options["layout"]=="horizontal") echo $GTTabs_options["width"]; else echo "auto"; ?>;
	height: <?php echo $GTTabs_options["height"]; ?>;
	margin: <?php if ($GTTabs_options["layout"]=="horizontal") echo "0px 0px 1em"; else echo "0px 1em 1em 0px"; ?> !important;
	padding: <?php if ($GTTabs_options["layout"]=="horizontal") echo "0.2em 1em 0.2em ".$GTTabs_options["spacing"]; else echo "1em 0px ".$GTTabs_options["spacing"]." 0.2em"; ?> !important;
	border-<?php if ($GTTabs_options["layout"]=="horizontal") echo "bottom:"; else echo "right:"; ?> 1px solid <?php echo $GTTabs_options["line"] ?> !important;
	font-size: <?php echo $GTTabs_options["font-size"]; ?>;
	list-style-type: none !important;
	line-height:normal;
	text-align: <?php if ($GTTabs_options["layout"]=="horizontal") echo $GTTabs_options["align"]; else echo "right"; ?>;
	display: block !important;
	background: none;
	}

ul.GTTabs li
	{	
	<?php if ($GTTabs_options["layout"]=="horizontal") echo "display: inline !important;"; ?>
	font-size: <?php echo $GTTabs_options["font-size"]; ?>;
	line-height:normal;
	background: none;
	padding: 0px;
	margin:1em 0px 0px 0px;
	}
  
ul.GTTabs li:before{
content: none;	
}  
  	
ul.GTTabs li a
	{
	text-decoration: none;
	background: <?php echo $GTTabs_options["inactive_bg"]; ?>;
	border: 1px solid <?php echo $GTTabs_options["line"]; ?>  !important;
	padding: 0.2em 0.4em !important;
	color: <?php echo $GTTabs_options["inactive_font"]; ?> !important;
	outline:none;	
	cursor: pointer;
	
	}
	
ul.GTTabs li.GTTabs_curr a{
	border-<?php if ($GTTabs_options["layout"]=="horizontal") echo "bottom:"; else echo "right:"; ?>: 1px solid <?php echo $GTTabs_options["active_bg"] ?> !important;
	background: <?php echo $GTTabs_options["active_bg"]; ?>;
	color: <?php echo $GTTabs_options["active_font"]; ?> !important;
	text-decoration: none;
	
	}

ul.GTTabs li a:hover
	{
	color: <?php echo $GTTabs_options["over_font"]; ?> !important;
	background: <?php echo $GTTabs_options["over_bg"]; ?>;
	text-decoration: none;
	
	}

.GTTabsNavigation{
	display: block !important;
	overflow:hidden;
}

.GTTabs_nav_next{
	float:right;
}

.GTTabs_nav_prev{
	float:left;
}

@media print {
         .GTTabs {display:none;border:0 none;}
	.GTTabs li a {display:none;border:0 none;}
	.GTTabs li.GTTabs_curr a {display:none;border:0 none;}
	.GTTabs_titles{display:block !important;border-bottom:1px solid;}
	.GTTabs_divs {display:block !important;}
}
