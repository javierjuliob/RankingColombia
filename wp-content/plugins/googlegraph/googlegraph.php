<?php
/*
Plugin Name: GoogleGraph
Plugin URI: http://tsba.mobi/google-graph
Description: Generate Google Chart.
Version: 0.4.2.1
Author: Jordan Vrtanoski
Author Email: jordan.vrtanoski@tsba.mobi
License:

  Copyright 2014 Jordan Vrtanoski <jordan.vrtanoski@tsba.mobi>

  This program is free software; you can redistribute it and/or modify
  it under the terms of the Creative Commons Attribution-NonCommercial 4.0 
  International (CC BY-NC 4.0)

  You can use this script in your non-commercial work by you must give credits
  to us by placing the following <a> tag on a visable arrea on your web site:

  <a href="http://tsba.mobi" title="Premium WordPress Developers">GoogleGraphs by TSBA.mobi</a>

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  http://creativecommons.org/licenses/by-nc/4.0/

  The chart is supported by Google Visualisation API (Google Charts). The javascript
  is owned by Google.
  https://google-developers.appspot.com/chart/interactive/docs/gallery
*/

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

class GoogleGraph {

	/*--------------------------------------------*
	 * Constants
	 *--------------------------------------------*/
	const name = 'GoogleGraph';
	const slug = 'googlegraph';
	
	/**
	 * Constructor
	 */
	function __construct() {
		//register an activation hook for the plugin
		register_activation_hook( __FILE__, array( &$this, 'install_googlegraph' ) );

		//Hook up to the init action
		add_action( 'init', array( &$this, 'init_googlegraph' ) );
	}
  
	/**
	 * Runs when the plugin is activated
	 */  
	function install_googlegraph() {
		// do not generate any output here
	}
  
	/**
	 * Runs when the plugin is initialized
	 */
	function init_googlegraph() {
		// Setup localization
		load_plugin_textdomain( self::slug, false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
		// Load JavaScript and stylesheets
		$this->register_scripts_and_styles();

		// Register the shortcode [barGraph]
		add_shortcode( 'lineChart', array( &$this, 'render_linechart' ) );
		add_shortcode( 'columnChart', array( &$this, 'render_column' ) );
		add_shortcode( 'barChart', array( &$this, 'render_bargraph' ) );
		add_shortcode( 'pieChart', array( &$this, 'render_piechart' ) );
		add_shortcode( 'geoChart', array( &$this, 'render_geochart' ) );
		add_shortcode( 'bubbleChart', array( &$this, 'render_bubblechart' ) );
        add_shortcode( 'scatterChart', array( &$this, 'render_scatterchart' ) );
		
		/* PHP League bridge */
		add_shortcode( 'phpLeagueGraphPerTeam', array( &$this, 'render_phpleaguegraph' ) );
		add_shortcode( 'phpLeagueGraphPerCategory', array( &$this, 'render_phpleaguegraphtransposed' ) );

		/* Register filters */
		add_filter( 'no_texturize_shortcodes', array( &$this, 'shortcodes_to_exempt_from_wptexturize') );

		if ( is_admin() ) {
			add_action( 'admin_menu', array( &$this, 'register_googlegraph_menu_page') );
			wp_enqueue_script('jquery'); 
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-accordion');
		} else {
			//this will run when on the frontend
		}

		/*
		 * TODO: Define custom functionality for your plugin here
		 *
		 * For more information: 
		 * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		add_action( 'your_action_here', array( &$this, 'action_callback_method_name' ) );
		add_filter( 'your_filter_here', array( &$this, 'filter_callback_method_name' ) );    
	}


	function shortcodes_to_exempt_from_wptexturize($shortcodes){
		$shortcodes[] = 'lineChart';
		$shortcodes[] = 'columnChart';
		$shortcodes[] = 'barChart';
		$shortcodes[] = 'pieChart';
		$shortcodes[] = 'geoChart';
		$shortcodes[] = 'bubbleChart';
		$shortcodes[] = 'scatterChart';

		$shortcodes[] = 'phpLeagueGraphPerTeam';
		$shortcodes[] = 'phpLeagueGraphPerCategory';
		return $shortcodes;
	}
	/**
		Register the GoogleGraph admin page.
	*/
	function register_googlegraph_menu_page(){
		add_menu_page( 'GoogleGraph menu', 'GoogleGraph', 'manage_options', 'googlegraph/admin/help.php', '', 'dashicons-chart-area', 81 );
	}
	
	/**
		Transposed PHPLeague Chart 
	*/
	function render_phpleaguegraph($atts, $content = null) {
		extract(shortcode_atts(array(
			'league' => '1',
			'club_list' => NULL
		), $atts));
		
		if (is_plugin_active('phpleague/phpleague.php')) {
			global $wpdb;
			if (is_null($club_list)) {
				$query = "SELECT * FROM $wpdb->table_cache WHERE id_league = $league ORDER BY points DESC";
			} else {
				$query = "SELECT * FROM $wpdb->table_cache WHERE id_league = $league AND id_team IN ($club_list) ORDER BY points DESC";
			}
			
			$result = "['Club', 'Points', 'Games Played', 'Victory', 'Draw', 'Defeat', 'Goals For', 'Goals Against'],";
			foreach ($wpdb->get_results($wpdb->prepare($query, NULL)) as $row)
			{
				$result .= "[";
				$result .= "'".$row->club_name."',"; 
				$result .= "".$row->points.","; 
				$result .= "".$row->played.","; 
				$result .= "".$row->victory.","; 
				$result .= "".$row->draw.","; 
				$result .= "".$row->defeat.","; 
				$result .= "".$row->goal_for.","; 
				$result .= "".$row->goal_against.","; 
				$result .= "],";
			}
	
			if (!isset($atts))
			{
				$atts = array();
			}
			if (!isset($atts['vaxis'])) {
				$atts['vaxis'] = "{title: 'Clubs'}";
			}
			if (!isset($atts['haxis'])) {
				$atts['haxis'] = "{title: ''}";
			}
			
			return $this->render_chart('BarChart',$atts,$result );
		} else {
			return "PHPLeague plug-in should be installed for this shortcode to work";
		}
	}

	/**
	Transposed PHPLeague Chart 
	*/
	function render_phpleaguegraphtransposed($atts, $content = null) {
		extract(shortcode_atts(array(
			'league' => '1',
			'club_list' => NULL
		), $atts));
		
		if (is_plugin_active('phpleague/phpleague.php')) {
			global $wpdb;
			if (is_null($club_list)) {
				$query = "SELECT * FROM $wpdb->table_cache WHERE id_league = $league ORDER BY points DESC";
			} else {
				$query = "SELECT * FROM $wpdb->table_cache WHERE id_league = $league AND id_team IN ($club_list) ORDER BY points DESC";
			}

			/* Create the array */
			$resArr = array();
			$resArr[1] = array( "'Categories'" );
			$resArr[2] = array( "'Points'" );
			$resArr[3] = array( "'Games Played'" );
			$resArr[4] = array( "'Victory'" );
			$resArr[5] = array( "'Draw'" );
			$resArr[6] = array( "'Defeat'" );
			$resArr[7] = array( "'Goals For'" );
			$resArr[8] = array( "'Goals Against'" );

			foreach ($wpdb->get_results($wpdb->prepare($query, NULL)) as $row)
			{

				$resArr[1][] = "'".$row->club_name."'"; 
				$resArr[2][] = $row->points; 
				$resArr[3][] = $row->played; 
				$resArr[4][] = $row->victory; 
				$resArr[5][] = $row->draw; 
				$resArr[6][] = $row->defeat; 
				$resArr[7][] = $row->goal_for; 
				$resArr[8][] = $row->goal_against; 
			}
			
			/* Transpose the aray */
			$result = "";
			foreach ($resArr as $row) {
				$result .= "[" . implode(",",$row) . "],";
			}
	
			if (!isset($atts))
			{
				$atts = array();
			}
			if (!isset($atts['vaxis'])) {
				$atts['vaxis'] = "{title: 'Categories'}";
			}
			if (!isset($atts['haxis'])) {
				$atts['haxis'] = "{title: ''}";
			}
			
			return $this->render_chart('BarChart',$atts,$result );
		} else {
			return "PHPLeague plug-in should be installed for this shortcode to work";
		}
	}


    function render_linechart($atts, $content = null) {
         return $this->render_chart('LineChart', $atts, $content);
    }

    function render_column($atts, $content = null) {
         return $this->render_chart('ColumnChart', $atts, $content);
    }

    function render_bargraph($atts, $content = null) {
         return $this->render_chart('BarChart', $atts, $content);
    }

    function render_piechart($atts, $content = null) {
         return $this->render_chart('PieChart', $atts, $content);
    }

    function render_geochart($atts, $content = null) {
         return $this->render_chart('GeoChart', $atts, $content);
    }   

    function render_bubblechart($atts, $content = null) {
         return $this->render_chart('BubbleChart', $atts, $content);
    }   

    function render_scatterchart($atts, $content = null) {
         return $this->render_chart('ScatterChart', $atts, $content);
    }   

	function action_callback_method_name() {
		// TODO define your action method here
	}

	function filter_callback_method_name() {
		// TODO define your filter method here
	}

	function render_chart($type, $atts, $content) {
		// Extract the attributes
		extract(shortcode_atts(array(
			'width' => '600px', //foo is a default value
			'height' => '400px',
			'title' => '',
			'stacked' => 'false',
			'legend' => "{position: 'bottom', maxLines: 3}",
			'vaxis' => "{title: 'Y',  titleTextStyle: {color: 'red'}}",
			'haxis' => "{title: 'X',  titleTextStyle: {color: 'red'}}",
			'curvetype' => NULL,
			'displaymode' => NULL,
			'region' => NULL,
			'colorstart' => NULL,
			'colorend' => NULL,
			'slices' => NULL,
			'bubble' => NULL,
			'chartarea' => NULL,
            'interpolate' => 'true',
            'trendlines' => NULL,
			), $atts));
		// you can now access the attribute values using $attr1 and $attr2

    global $item_id;
    $item_id++;
    
    // Prepare the curve option for line chart types
    $otheroptions="";

    if (!is_null($chartarea)) {
       	    $otheroptions= $otheroptions."chartArea: $chartarea,";
    }

    if ($type === "LineChart") {
	if (!is_null($curvetype)) {
       	    $otheroptions= $otheroptions."curveType: '$curvetype',";
    	}
        if (!($interpolate ==="false")) {
            $interpolate = "true";
        }
        $otheroptions = $otheroptions."interpolateNulls: $interpolate,";
    }

    if ($type === "GeoChart") {
	if (!is_null($displaymode)) {
       		$otheroptions= $otheroptions."displayMode: '$displaymode',";
    	}
	if (!is_null($region)) {
       		$otheroptions= $otheroptions."region: '$region',";
    	}
	if (!is_null($colorstart) && !is_null($colorend)) {
       		$otheroptions= $otheroptions."colorAxis: {colors: ['$colorstart', '$colorend']} ,";
    	}
    }

    if ($type === "ColumnChart") {
        if (!is_null($trendlines)) {
       		$otheroptions= $otheroptions."trendlines: $trendlines , ";
        }
    }

    if ($type === "BarChart") {
        if (!is_null($trendlines)) {
       		$otheroptions= $otheroptions."trendlines: $trendlines , ";
        }
    }

    if ($type === "ScatterChart") {
        if (!is_null($trendlines)) {
       		$otheroptions= $otheroptions."trendlines: $trendlines , ";
        }
    }

    // Adding the option for "slices" as per the google documents
   //  Example:  slices="{ 0: {offset: 0.2, color: 'black'} }"
    if ($type === "PieChart") {
	if (!is_null($slices)) {
       		$otheroptions= $otheroptions."slices: $slices, ";
    	}
    }

    // Adding the option for "bubble" as per the google documents
   //  Example:  bubble ="{textStyle: {auraColor: 'none'}}"
    if ($type === "BubbleChart") {
        if (!is_null($bubble)) {
       		$otheroptions= $otheroptions."bubble: $bubble, ";
    	}
	if (!is_null($colorstart) && !is_null($colorend)) {
       		$otheroptions= $otheroptions."colorAxis: {colors: ['$colorstart', '$colorend']},";
        }
    }
       
	   
    // Remove HTML tags from the content of the shortcode
	$content = html_entity_decode($content, ENT_NOQUOTES, "UTF-8");
    $content = wp_strip_all_tags($content,true);

    $str = <<<EOT
    <div id="googlegraph_$item_id" class="tsba_googlegraph">	
    <div id="chart_div_$item_id" class="gc_$type" style="width: $width; height: $height;"></div>
	<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
			$content
        ]);

        var options = {
          $otheroptions
          title: '$title',
          vAxis: $vaxis,
          hAxis: $haxis,
          isStacked: $stacked,
          legend: $legend
        };

        var chart = new google.visualization.$type(document.getElementById('chart_div_$item_id'));
        chart.draw(data, options);
      }
    </script>
    <p style="font-size: .5em;">Powered by <a href="http://tsba.mobi" title="Powered by TSBA.mobi GoogleGraph Wordpress plugin">TSBA.mobi GoogleGraph Wordpress plugin</a></p>
    </div>
		
EOT;
		return $str;
	}
  
	/**
	 * Registers and enqueues stylesheets for the administration panel and the
	 * public facing site.
	 */
	private function register_scripts_and_styles() {
	    wp_register_script( self::slug . '-jsapi-script', 'https://www.google.com/jsapi');
	    wp_enqueue_script( self::slug . '-jsapi-script');
	    
	} // end register_scripts_and_styles
	
	/**
	 * Helper function for registering and enqueueing scripts and styles.
	 *
	 * @name	The 	ID to register with WordPress
	 * @file_path		The path to the actual file
	 * @is_script		Optional argument for if the incoming file_path is a JavaScript source file.
	 */
	private function load_file( $name, $file_path, $is_script = false ) {

		$url = plugins_url($file_path, __FILE__);
		$file = plugin_dir_path(__FILE__) . $file_path;

		if( file_exists( $file ) ) {
			if( $is_script ) {
				wp_register_script( $name, $url, array('jquery') ); //depends on jquery
				wp_enqueue_script( $name );
			} else {
				wp_register_style( $name, $url );
				wp_enqueue_style( $name );
			} // end if
		} // end if

	} // end load_file
  
} // end class
new GoogleGraph();


// add more buttons to the html editor
function googlegraph_add_quicktags() {
    if (wp_script_is('quicktags')){
?>
    <script type="text/javascript">
    QTags.addButton( 'eg_geoChart', 'geoChart', '[geoChart vaxis="{title: \'\'}" haxis="{title: \'\'}" title=""]', '[/geoChart]', 'g', 'Geo Chart', 200 );
    QTags.addButton( 'eg_lineChart', 'lineChart', '[lineChart vaxis="{title: \'\'}" haxis="{title: \'\'}" title=""]', '[/lineChart]', 'l', 'Line Chart', 201 );
    QTags.addButton( 'eg_columnChart', 'columnChart', '[columnChart vaxis="{title: \'\'}" haxis="{title: \'\'}" title=""]', '[/columnChart]', 'c', 'Line Chart', 202 );
    QTags.addButton( 'eg_barChart', 'barChart', '[barChart vaxis="{title: \'\'}" haxis="{title: \'\'}" title=""]', '[/barChart]', 'b', 'Line Chart', 203 );
    QTags.addButton( 'eg_pieChart', 'pieChart', '[pieChart title=""]', '[/pieChart]', 'p', 'Pie Chart', 204 );
    QTags.addButton( 'eg_bubbleChart', 'bubbleChart', '[bubbleChart vaxis="{title: \'\'}" haxis="{title: \'\'}" title="" bubble="{}"]', '[/bubbleChart]', 'p', 'Bubble Chart', 205 );
    QTags.addButton( 'eg_scatterChart', 'scatterChart', '[scatterChart vaxis="{title: \'\'}" haxis="{title: \'\'}" title="" ]', '[/scatterChart]', 'p', 'Scatter Chart', 206 );
    </script>
<?php
    }
}
add_action( 'admin_print_footer_scripts', 'googlegraph_add_quicktags' );


