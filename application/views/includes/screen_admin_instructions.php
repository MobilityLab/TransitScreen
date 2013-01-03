<div class="instructions">

<h4>Finding Stop IDs</h4>

<p><strong>WMATA, ART, DC Circulator</strong>:

Use <a href="http://transitnearme.com/transitapis/">TransitAPIs</a>. Scroll and zoom the window to contain the desired stop.
Read the "Code" field from the Visible Stops text window. 

<p><strong>CaBi</strong>: Use <a href="http://cabitracker.com">CaBiTracker</a>. Click the station you want. 
	Click the more data link (you may have to scroll down inside the window). 
	The station id (looks like <code>cabi:198</code>) will appear in the URL address bar of your browser (NOT in the text of the webpage).

<h4>Formatting Stop IDs</h4>

[agency id]:[stop id], e.g. <code>metrobus:6000123</code>.
  
<p>If several agencies serve a single stop, separate each agency-stop combination with semicolons, e.g.
  <code>cabi:198;cabi:112</code>. </p>

<p>Agency codes:</p>

<ul>
  <li>Metrorail: <code>metrorail</code></li>
  <li>Metrobus: <code>metrobus</code></li>
  <li>ART: <code>art</code></li>
  <li>Circulator: <code>dc-circulator</code></li>
  <li>Capital Bikeshare: <code>cabi</code></li>
  <li>Custom text block: <code>custom</code></li>
</ul> 

</div>