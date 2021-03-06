<?php

if (isset($_POST['submit_GTTabs'])) {

	$options["active_font"] = $_POST['active_font'];
	$options["active_bg"] = $_POST['active_bg'];
	$options["inactive_font"] = $_POST['inactive_font'];
	$options["inactive_bg"] = $_POST['inactive_bg'];
	$options["over_font"] = $_POST['over_font'];
	$options["over_bg"] = $_POST['over_bg'];
	$options["line"] = $_POST['line'];
	$options["align"] = $_POST['align'];
	$options["layout"] = $_POST['layout'];
	$options["width"] = $_POST['width'];
	$options["height"] = $_POST['height'];
	$options["spacing"] = $_POST['spacing'];
	$options["font-size"] = $_POST['font-size'];
	$options["list_link"] = $_POST['list_link'];
	$options["single_link"] = $_POST['single_link'];
	$options["show_perma"] = $_POST['show_perma'];
	$options["cookies"] = ($_POST['cookies']=="1") ? "1" : "0";
	$options["TOC"] = $_POST['TOC'];
	$options["TOC_title"] = $_POST['TOC_title'];
	$options["layout"] = $_POST['layout'];
	update_option("GTTabs", $options);
				
	echo "<div class=\"updated\"><p><strong> Wordpress Tabs Options Updated!</strong></p></div>";
}

$options=get_option("GTTabs");
?>
<script>
function GTTabs_preview(){
	
	tabs = new Array("active","inactive","over");
	
	document.getElementById("GTTabs_admin").style.borderBottom="1px solid "+document.GTTabsOptions.line.value;	
	document.getElementById("GTTabs_admin_active").style.border="1px solid "+document.GTTabsOptions.line.value;	
	document.getElementById("GTTabs_admin_inactive").style.border="1px solid "+document.GTTabsOptions.line.value;	
	document.getElementById("GTTabs_admin_over").style.border="1px solid "+document.GTTabsOptions.line.value;

	for(y=0;y<tabs.length;y++){
		document.getElementById("GTTabs_admin_"+tabs[y]).style.backgroundColor=document.GTTabsOptions.elements[tabs[y]+"_bg"].value;
	}
	for(y=0;y<tabs.length;y++){
		document.getElementById("GTTabs_admin_"+tabs[y]).style.color=document.GTTabsOptions.elements[tabs[y]+"_font"].value;
	}
	
	document.getElementById("GTTabs_admin_preview").style.backgroundColor=document.GTTabsOptions.active_bg.value;	
	document.getElementById("GTTabs_admin_active").style.borderBottom="1px solid "+document.GTTabsOptions.active_bg.value;		
}

function GTTabs_preview_align(dir){

	document.getElementById("GTTabs_admin").style.textAlign=dir;
	if(dir=="center") document.getElementById("GTTabs_admin").style.paddingLeft="0px";
	else document.getElementById("GTTabs_admin").style.paddingLeft="20px";

}

function GTTabs_preview_layout(dir){

	document.getElementById("GTTabs_admin").style.textAlign=dir;
	if(dir=="center") document.getElementById("GTTabs_admin").style.paddingLeft="0px";
	else document.getElementById("GTTabs_admin").style.paddingLeft="20px";

}

</script>

<div class="wrap">

	


	<form name="GTTabsOptions" method="post">
		<h1>WordPress Tabs Plugin Setup</h1>
		<!--<h2><img src="<?php echo ( GTGTTabs_URLPATH . 'gt-tabs.jpg' ) ; ?>"></h2>-->


		<div id="GTTabs_admin_preview">
		
		
			<ul id='GTTabs_admin' >
			<li ><a id='GTTabs_admin_active' href='#'>Active Tab</a></li>
			<li ><a id='GTTabs_admin_inactive' href='#'>Inactive Tab</a></li>
			<li ><a id='GTTabs_admin_over' href='#'>Mouse Over</a></li>
			
			</ul>
	
		</div>

		<div id="colorpicker301" class="colorpicker301" onClick="setTimeout('GTTabs_preview()',100)"></div>
		
<BR />
		Enter the color in the fields bellow, or use the buttons to pick it. 
<BR />
		<b>Dont forget to Save the changes after you are done.</b>
				
		<h3>Line Color</h3>
		<input type="button" onclick="showColorGrid3('line','none');" value="..." >&nbsp;
		<input type="text" id="line" name="line" value="<?php echo $options["line"] ?>" onKeyUp="GTTabs_preview()"> 
		<BR /><BR />

		<h3>Active Tab</h3>
		Text color:<BR />
		<input type="button" onclick="showColorGrid3('active_font','none');" value="..." >&nbsp;
		<input type="text" id="active_font" name="active_font" value="<?php echo $options["active_font"] ?>" onKeyUp="GTTabs_preview()"> <BR />
		Background color:<BR />
		<input type="button" onclick="showColorGrid3('active_bg','none');" value="..." >&nbsp;
		<input type="text" id="active_bg" name="active_bg" value="<?php echo $options["active_bg"] ?>" onKeyUp="GTTabs_preview()"> <BR /><BR />

		<h3>Mouse Over Tab</h3>
		Text color:<BR />
		<input type="button" onclick="showColorGrid3('over_font','none');" value="..." >&nbsp;
		<input type="text" id="over_font" name="over_font" value="<?php echo $options["over_font"] ?>" onKeyUp="GTTabs_preview()"> <BR />
		Background color:<BR />
		<input type="button" onclick="showColorGrid3('over_bg','none');" value="..." >&nbsp;
		<input type="text" id="over_bg" name="over_bg" value="<?php echo $options["over_bg"] ?>" onKeyUp="GTTabs_preview()"> <BR /><BR />

		<h3>Inactive Tab</h3>
		Text color:<BR />
		<input type="button" onclick="showColorGrid3('inactive_font','none');" value="..." >&nbsp;
		<input type="text" id="inactive_font" name="inactive_font" value="<?php echo $options["inactive_font"] ?>" onKeyUp="GTTabs_preview()"> <BR />
		Background color:<BR />
		<input type="button" onclick="showColorGrid3('inactive_bg','none');" value="..." >&nbsp;
		<input type="text" id="inactive_bg" name="inactive_bg" value="<?php echo $options["inactive_bg"] ?>" onKeyUp="GTTabs_preview()"> <BR /><BR />
		
		<h3>Tabs alignment</h3>
		<input onClick="GTTabs_preview_align('left');" type="radio" value="left" name="align" id="align" <?php if ("left" == $options["align"]) echo "checked"; ?> > Left <BR />
		<input onClick="GTTabs_preview_align('center');" type="radio" value="center" name="align" id="align" <?php if ("center" == $options["align"]) echo "checked"; ?>> Center <BR />
		<input onClick="GTTabs_preview_align('right');" type="radio" value="right" name="align" id="align" <?php if ("right" == $options["align"]) echo "checked"; ?>> Right <BR /><BR />
		
		<h3>Tabs Layout</h3>
		<input onClick="GTTabs_preview_layout('vertical');" type="radio" value="vertical" name="layout" id="layout" <?php if ("vertical" == $options["layout"]) echo "checked"; ?>> Vertical <BR />
		<input onClick="GTTabs_preview_layout('horizontal');" type="radio" value="horizontal" name="layout" id="layout" <?php if ("horizontal" == $options["layout"]) echo "checked"; ?>> Horizontal <BR /><BR />
		<b>Width</b> (defaults to 100%): <input type="text" name="width" id="width" value="<?php echo $options["width"] ?>"><BR />
		<b>Height</b> (defaults to 'auto'): <input type="text" name="height" id="height" value="<?php echo $options["height"] ?>"><BR />
		<b>Spacing</b> (defaults to 20px): <input type="text" name="spacing" id="spacing" value="<?php echo $options["spacing"] ?>"><BR /><BR />
		
		<h3>Font Settings</h3>
		<b>Size</b> (defaults to Small): 	
		<select name="font-size" id="font-size">
			<OPTION value="9px" <?php if ("9px" == $options["font-size"]) echo "selected"; ?>>X-Small</OPTION>
			<OPTION value="11px" <?php if ("11px" == $options["font-size"]) echo "selected"; ?>>Small</OPTION>
			<OPTION value="12px" <?php if ("12px" == $options["font-size"]) echo "selected"; ?>>Medium</OPTION>
			<OPTION value="14px" <?php if ("14px" == $options["font-size"]) echo "selected"; ?>>Large</OPTION>
			<OPTION value="16px" <?php if ("16px" == $options["font-size"]) echo "selected"; ?>>X-Large</OPTION>
		</select><BR /><BR />
		
		<h3>Display TOC</h3>
		Displays a Table of Contents with links to all tabs at the end of the post. (Note it will use your theme's default layout for unordered lists.)
		<BR>
		<b>TOC Title</b> (optional): <input type="text" name="TOC_title" id="TOC_title" value="<?php echo $options["TOC_title"] ?>"><br>
		<input type="radio" value="0" name="TOC" id="TOC" <?php if (0 == $options["TOC"]) echo "checked"; ?> > Never <BR>
		<input type="radio" value="END" name="TOC" id="TOC" <?php if ("END" == $options["TOC"]) echo "checked"; ?> > At the end of the post, after everything  <BR>
		<input type="radio" value="rightAfter" name="TOC" id="TOC" <?php if ("rightAfter" == $options["TOC"]) echo "checked"; ?>> Right after the [tab:END] tag, before the remaining post content<BR>
		<input type="radio" value="navigation" name="TOC" id="TOC" <?php if ("navigation" == $options["TOC"]) echo "checked"; ?>> Inside each tab - navigation style ( &lt;&lt;previous tab ... next tab&gt;&gt; )<BR>
		
		
		
		
		<BR /><BR />
		<h3>Links behavior</h3>
		
		<b>permalink:</b> This option set a permalink for each tab. <BR />
		Pros: You can have a direct link to your post with a specific tab opened. <BR />
		Cons: The page is reloaded every time you click on a tab.
		<BR /><BR />
		<b>Hide-Show Tabs:</b> This option display and hide the tab content as you click.<BR />
		Pros: It does not reload the entire page each time you click on a tab, it quickly displays each tab content<BR />
		Cons: You dont have a permalink for a tab. 
		<BR /><BR />
		
		You can have different link behavior on a page where there is a list of many posts, or on a page where there is a single post:
		<BR /><BR />
		<b>List:</b><BR />
		<input type="radio" value="hideshow" name="list_link" id="list_link" <?php if ("hideshow" == $options["list_link"]) echo "checked"; ?> > Hide-Show Tabs <BR />		
		<input type="radio" value="permalink" name="list_link" id="list_link" <?php if ("permalink" == $options["list_link"]) echo "checked"; ?> > Permalink <BR />		
		<BR /><BR />
		<b>Single Post or page:</b><BR />
		<input type="radio" value="hideshow" name="single_link" id="single_link" <?php if ("hideshow" == $options["single_link"]) echo "checked"; ?> > Hide-Show Tabs <BR />		
		<input type="radio" value="permalink" name="single_link" id="single_link" <?php if ("permalink" == $options["single_link"]) echo "checked"; ?> > Permalink <BR />		
		
		<BR /><BR />
		<input type="checkbox" name="cookies" value="1" <?php if ("1" == $options["cookies"]) echo "checked"; ?>> <b>Remember last opened tab:</b> 
		When using 'Hide-Show', makes the browser remember in wich tab the user was when the page is reloaded. Requires cookies to be enable.
		<BR /><BR /><BR />
		The permalinks also work even if you have selected the option Hide-show tabs. The thing is the address is never shown so you will never know it. Mark the option bellow if you want to display each tab's permalink:
		<BR /><BR />
		<b>Display tab permalink inside tab body:</b><BR />
		<input type="radio" value="never" name="show_perma" id="show_perma" <?php if ("never" == $options["show_perma"]) echo "checked"; ?> > Never <BR />		
		<input type="radio" value="registered" name="show_perma" id="show_perma" <?php if ("registered" == $options["show_perma"]) echo "checked"; ?> > Only to registered users <BR />		
		<input type="radio" value="all" name="show_perma" id="show_perma" <?php if ("all" == $options["show_perma"]) echo "checked"; ?> > Allways <BR />	
		
		<div class="submit">
		<input type="submit" name="submit_GTTabs" value="<?php _e('Save', '') ?>">
		
		<h2>Wordpress Tabs Usage</h2>
		Edit your post (or) page and add the following quick code where ever you want to start your Tab list: <b>[tab:Tab Title]</b><BR />
		All the content bellow this code will be inserted inside this tab, untill another tab is declared or the tab list is ended.<br><BR />
		To end the tab list, you would use the end code: <b>[tab:END]</b>.  <font color="red">NOTE: The end code is case sensitive!</font>
		
		<h3>Example</h3>
		<code>[tab:Your First Tab] Your first tab content goes here, this can include pictures, slide shows, other block codes, etc.<BR />
		[tab:Your Second Tab] Your second tab content goes here, this can also include pictures, slide shows, etc.<BR />
		[tab:END] The remaining page content goes here and will appear BELOW the tabbed content container.</code>
		
		</div>
		
	</form>	
	
	<?php if (isset($_POST['submit_GTTabs'])) echo '<script>GTTabs_preview();</script>'; ?>
</div>
