<div class="instructions">

<h4>Finding Stop IDs</h4>

<p><strong>WMATA, ART, DC Circulator</strong>:

Use <a href="http://transitnearme.com/transitapis/">TransitAPIs</a>. Scroll and zoom the window to contain the desired stop.
Read the "Code" field from the Visible Stops text window. 

<p><strong>CaBi</strong>: Use <a href="http://cabitracker.com">CaBiTracker</a>. Click the station you want. 
	Click the more data link (you may have to scroll down inside the window). 
	The station id (looks like <code>cabi:198</code>) will appear in the URL address bar of your browser (NOT in the text of the webpage).
<p><strong>TheBus</strong>

Go to the <a href="http://www.nextbus.com/googleMap/?a=pgc&r=11&r=12&r=13&r=14&r=15x&r=16&r=17&r=18&r=20&r=21&r=21x&r=22&r=23&r=24&r=25&r=26&r=27&r=28&r=30&r=32&r=33&r=34&r=35&r=51&r=53">NextBus map</a>. Scroll and zoom the window to contain the desired stop. Click on the stop. Note the route name and copy the stop name. Go to <a href="http://webservices.nextbus.com/service/publicXMLFeed?command=routeConfig&a=pgc">NextBus API</a> and search for the stop name. The stop name and route will be in the same line of code.

<h4>Formatting Stop IDs</h4>

[agency id]:[stop id], e.g. <code>metrobus:6000123</code>.

<p>For TheBus the format is slightly different; pgc:r=[route name]&s=[stop name], e.g. <code>pgc:r=17&s=balthami </code>
  
<p>If several agencies serve a single stop, separate each agency-stop combination with semicolons, e.g.
  <code>cabi:198;cabi:112</code>. </p>

<p>Agency codes:</p>

<ul>
  <li>Metrorail: <code>metrorail</code></li>
  <li>Metrobus: <code>metrobus</code></li>
  <li>ART: <code>art</code></li>
  <li>Circulator: <code>dc-circulator</code></li>
  <li>Prince George's TheBus: <code>pgc</code></li>
  <li>Capital Bikeshare: <code>cabi</code></li>
  <li>Custom text block: <code>custom</code></li>
</ul> 

</div>