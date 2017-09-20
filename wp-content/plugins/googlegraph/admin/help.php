<style>
#content {
	width: 95%;
}

#accordion .ui-accordion-header {
	background: silver;
	padding: 10px;
	box-shadow: 2px 2px 5px #707070;
}
#accordion .ui-accordion-header:hover {
	background: white;
}
#accordion .ui-accordion-content {
	background: #fafafa;
	padding: 14px;
}

#shortcode_table {
  border-collapse: collapse;
}
#shortcode_table td {
	margin: 0;
	border: solid 1px black;
}
</style>
<div id="content">
	<h1>GoogleGraph Help Page</h1>

    <div id="accordion">
      <h3>Introduction</h3>
      <div>
        <p>
    GoogleGraph is a wrapper plug-in for the Google Charts JS library. The plug-in provides shortcodes that the users can use to create stunning
    interactive charts.
    We had spend effort to create this plug-in and we are investing our time in improving it and incorporating your suggestions. We don't charge
    for the non-commercial use of the plug-in, all we ask is from our users to share thier comments for improvement of the plug-in and to
    attribute our work to us. If you want to make the text invisible, you are free to use CSS and set the paragrah's display attribute to "none".
        </p>
        <p>
    To open a section, click on it.
        </p>
      </div>
      <h3>Supported short codes</h3>
      <div>
        <p>
            <table id="shortcode_table" width="80%">
            <tr>
                <td width="20%"><em>Shortcode</em></td>
                <td><em>Description</em></td>
            </tr>
            <tr>
                <td>lineChart</td>
                <td>Gnerated a line-chart graph</td>
            </tr>
            <tr>
                <td>columnChart</td>
                <td>Generate a column-chart graph</td>
            </tr>
            <tr>
                <td>barChart</td>
                <td>Generate a bar-chart graph</td>
            </tr>
            <tr>
                <td>pieChart</td>
                <td>Generate a pie-chart graph</td>
            </tr>
            <tr>
                <td>geoChart</td>
                <td>Genearate a geo-chart graph</td>
            </tr>
            <tr>
                <td>bubbleChart</td>
                <td>Generate buble-chart graph</td>
            </tr>
            <tr>
                <td>scatterChart</td>
                <td>Generate scatter-chart graph</td>
            </tr>
            </table>
        </p>
      	<a href="https://wordpress.org/plugins/googlegraph/faq/" target="_blank">See examples in WordPress plug-in directory</a>
      </div>
      <h3>Additional shortcodes - Depending on a plug-in</h3>
      <div>
        <p>
            <table id="shortcode_table" width="80%">
            <tr>
                <td width="20%"><em>Shortcode</em></td>
                <td width="20%"><em>Required plugin</em></td>
                <td><em>Description</em></td>
            </tr>
            <tr>
                <td>phpLeagueGraphPerTeam</td>
                <td>phpLeague</td>
                <td>Generates a bar chart with the statistics for each teams in a ligue</td>
            </tr>
            <tr>
                <td>phpLeagueGraphPerCategory</td>
                <td>phpLeague</td>
                <td>Generates a bar chart with the statistics per category of the statistics</td>
            </tr>
            </table>
        </p>
      </div>
      <h3>Attributes</h3>
      <div>
        <p>
            <table id="shortcode_table" width="80%">
            <tr>
                <td width="20%"><em>Attribute</em></td>
                <td width="20%"><em>Supported shortcodes</em></td>
                <td><em>Description</em></td>
            </tr>
            <tr>
                <td>width</td>
                <td>all</td>
                <td>Determines the width of the chart. By default the value is 600px</td>
            </tr>
            <tr>
                <td>height</td>
                <td>all</td>
                <td>Determines the height of the chart. The default value is 400px</td>
            </tr>
            <tr>
                <td>title</td>
                <td>all</td>
                <td>Sets the title of the chart. The value is entered as a string representing the title of the chart.</td>
            </tr>
            <tr>
                <td>legend</td>
                <td>all</td>
                <td>Configuration object (json format) describing the attributes of the legent. Default value is "{position: 'bottom', maxLines: 3}"</td>
            </tr>
            <tr>
                <td>chartarea</td>
                <td>all</td>
                <td>Configuration object (json forma) describing the size and style of the chart area. Example "{width: '50%', height: '75%'}"</td>
            </tr>
            <tr>
                <td>vaxis</td>
                <td>all</td>
                <td>Configuration object describing the title of the vertical axes. The default value is "{title: 'Y',  titleTextStyle: {color: 'red'}}"</td>
            </tr>
            <tr>
                <td>haxis</td>
                <td>all</td>
                <td>Configuration object describing the title of the horisontal axes. The default value is "{title: 'X',  titleTextStyle: {color: 'red'}}"</td>
            </tr>
            <tr>
                <td>curvetype</td>
                <td>lineChart</td>
                <td>Type of the curve for the line. Values can be "function" and "none". </td>
            </tr>
            <tr>
                <td>interpolate</td>
                <td>lineChart</td>
                <td>Interpolates the missing values while drawing the line. The default value is "true" which means that the line will interpolate the missing values from the table</td>
            </tr>
            <tr>
                <td>displaymode</td>
                <td>geoChart</td>
                <td>Display mode of the chart. If set to "markers" the values will be shown on the chart as markers</td>
            </tr>
            <tr>
                <td>region</td>
                <td>geoChart</td>
                <td>Focueses on a regin on the chart.</td>
            </tr>
            <tr>
                <td>colorstart</td>
                <td>geoChart, bubbleChart</td>
                <td>Start value of the color for the shading</td>
            </tr>
            <tr>
                <td>colorend</td>
                <td>geoChart, bubbleChart</td>
                <td>End value of the color for the shading</td>
            </tr>
            <tr>
                <td>slices</td>
                <td>pieChart</td>
                <td>Configuration object describing the visual attributes of the slices of the pieChart</td>
            </tr>
            <tr>
                <td>bubble</td>
                <td>bubbleChart</td>
                <td>Configuration object describing the visual attributes of the bubbles rendered on the chart</td>
            </tr>
            <tr>
                <td>trendlines</td>
                <td>barChart, columnChart, scatterChart</td>
                <td>Configuration object describing the attributes of the trend lines for the series</td>
            </tr>
            </table>
        </p>
        <a href="https://google-developers.appspot.com/chart/interactive/docs/gallery" target="_blank">See the Google documentation for avanced information on the attributes</a>
      </div>
    </div>
    
</div> <!- #content -->

  <script>
  jQuery( document ).ready(function() {
    jQuery( "#accordion" ).accordion({heightStyle: "content"});
  });
  </script>
