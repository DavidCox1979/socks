It becomes inefficient to log every hit in your application. A "rollup" is a table that aggregates statistics for performance reasons.

The stats framework itself can be found in library/PhpStats

See HTML version of documentation in library/PhpStats/documentation

> <a></a>
<ol>
<blockquote><li>
<blockquote><a href='#logging'>Logging (PhpStats_Logger)</a>
<ul>
<blockquote><li><a href='#logging_realtime'>Real Time Logging</a></li>
<li><a href='#logging_segmenting'>Segment With Attributes</a></li>
<li><a href='#logging_backdating'>Back Dating</a></li>
</blockquote></ul>
</blockquote></li>
<li>
<blockquote><a href='#reporting'>Reporting (PhpStats_TimeInterval)</a>
<ul>
<blockquote><li><a href='#reporting_hour'>Hour</a></li>
<li><a href='#reporting_day'>Day</a></li>
<li><a href='#reporting_month'>Month</a></li>
<li><a href='#reporting_segments'>Segmented Report</a></li>
<li><a href='#reporting_describesegment'>Describe Segments (Attribute)</a></li>
<li><a href='#reporting_describetypes'>Describe Event Types</a></li>
</blockquote></ul>
</blockquote></li>
</ol></blockquote>


<a></a>
<h1>Logging (PhpStats_Logger)</h1>

<a></a>
<h2>Real Time Logging</h2>
<p>PhpStats_Logger is used to record events. In it's simplest usage you simply instantiate the logger, and call the log method. It has one required paramater, an event type. You should use any string name you want (Example "click", "view").</p>

<pre>
$logger = new PhpStats_Logger;<br>
$logger->log('click');<br>
</pre>

<a></a>
<h2>Segment With Attributes</h2>
<p>Attributes are key value pairs, represented by associative PHP arrays. Attributes are used to segment your data for reporting. When using the logger pass an optional associative array of attributes to the log method.</p>
<pre>
$logger->log( 'click', array(<br>
'attribute' => 'value'<br>
));<br>
</pre>

<a></a>
<h2>Back Dating</h2>
<p>So far we have covered real time events. The log method has a third optional paramater that can be used to record an event subsequent to it's happening. Pass a timestamp as the third paramater. If the post dated timestamp is omitted, the return value of time() is used to record a "real time" event.</p>

<pre>
$timestamp = time() - 9000;<br>
$logger->log( 'click', array(), $timestamp );<br>
</pre>
<a href='#top'>Back to Top</a>



&lt;hr /&gt;



<a></a>
<h1>Reporting (PhpStats_TimeInterval)</h1>
<p>There are "TimeInterval" classes for each discrete chunk of time that can be analyzed. The PhpStats_TimeInterval interface defines a constructor that all time intervals use. We need an associative array describing the time interval we are asking about in order to insantiate a TimeInterval. Each TimeInterval requires a "time parts" array that identifies the interval uniquely, as you can see in the following examples.</p>

<p>If you fail to provide all required time parts for a TimeInterval, a "Missing Time" Exception will be raised (PhpStats_TimeInterval_Exception_MissingTime).</p>

<a></a>
<h2>Hour Report (PhpStats_TimeInterval_Hour)</h2>
<p>For an Hour Interval, we must pass a year, month, day &amp; hour in the form of the "time parts" array.</p>
<pre>
$timeParts = array(<br>
'hour' => 4,<br>
'month' => 1,<br>
'day' => 23,<br>
'year' => 2010<br>
);<br>
$hour = new PhpStats_TimeInterval_Hour( $timeParts );<br>
</pre>
<p>We can then "ask" the hour how many times it has "seen" an event type.</p>
<pre>
echo $hour->getCount('click'); // "5"<br>
</pre>

<a></a>
<h2>Day Report (PhpStats_TimeInterval_Day)</h2>
<p>Pass all required time parts to the Day Interval to instantiate it.</p>
<pre>
$timeParts = array(<br>
'month' => 1,<br>
'day' => 23,<br>
'year' => 2010<br>
);<br>
$day = new PhpStats_TimeInterval_Day( $timeParts );<br>
echo $day->getCount('click'); // "50"<br>
</pre>
<p>You can get a total event count for an event type for the day.</p>

<p>You may also output an hourly report by querying the day for it's hour intervals and other informations.</p>

<pre>
&lt;strong&gt;Report For Day &lt;?=$this-&gt;escape( $this-&gt;day-&gt;dayLabel() )?&gt;&lt;/strong&gt;:&lt;br /&gt;<br>
&lt;table border=&quot;1&quot;&gt;<br>
&lt;tr&gt;<br>
&lt;th&gt;Hour&lt;/th&gt;<br>
&lt;th&gt;Clicks&lt;/th&gt;<br>
&lt;/tr&gt;<br>
&lt;?php<br>
foreach( $this-&gt;day-&gt;getHours() as $hour )<br>
{<br>
?&gt;<br>
&lt;tr&gt;<br>
&lt;td&gt;&lt;?=$this-&gt;escape( $hour-&gt;hourLabel() )?&gt;&lt;/td&gt;<br>
&lt;td&gt;&lt;?=$this-&gt;escape( number_format( $hour-&gt;getCount('click'), 0 ) )?&gt;&lt;/td&gt;<br>
&lt;/tr&gt;<br>
&lt;?php<br>
}<br>
?&gt;<br>
&lt;/table&gt;<br>
</pre>

<a></a>
<h2>Month Report (PhpStats_TimeInterval_Month)</h2>
<pre>
$timeParts = array(<br>
'month' => 1,<br>
'year' => 2010<br>
);<br>
$month = new PhpStats_TimeInterval_Month( $timeParts );<br>
echo $month->getCount('click'); // "500"<br>
</pre>

<p>You saw how we can output an hourly report for a day. Likewise we can use a Month Interval to get a daily report:</p>

<pre>
&lt;strong&gt;Report For Month &lt;?=$this-&gt;escape( $this-&gt;month-&gt;monthLabel() )?&gt;&lt;/strong&gt;:&lt;br /&gt;<br />&lt;table border=&quot;1&quot;&gt;  <br />    &lt;tr&gt;<br />        &lt;th&gt;Day&lt;/th&gt;<br />        &lt;th&gt;Click&lt;/th&gt;<br />    &lt;/tr&gt;  <br />    &lt;?php<br />    foreach( $this-&gt;month-&gt;getDays() as $day )<br />    {<br />        ?&gt;<br />        &lt;tr&gt;<br />            &lt;td&gt;&lt;?=$this-&gt;escape( $day-&gt;dayShortlabel() )?&gt;&lt;/td&gt;<br />            &lt;td&gt;&lt;?=$this-&gt;escape( number_format( $day-&gt;getCount('click'), 0 ) )?&gt;&lt;/td&gt;<br />        &lt;/tr&gt;<br />        &lt;?php<br />    }<br />    ?&gt;<br />&lt;/table&gt;</pre>

<a></a>
<h2>Segmenting Reports (Drill Down By Attribute)</h2>
<p>If you would like to segment a report you can pass optional attribute(s) array as the second argument of the constructor. Then when you count an event type for that interval, it will only count events that match all segment attributes supplied.</p>
<pre>
// no segmenting<br>
$hour = new PhpStats_TimeInterval_Hour( $timeParts );<br>
echo $hour->getCount('click'); // "4"<br>
<br>
// segmenting<br>
$hour = new PhpStats_TimeInterval_Hour( $timeParts, array( 'a' => 1 ) );<br>
echo $hour->getCount('click'); // "2"<br>
</pre>

<a></a>
<h2>Describe Segments (Attributes)</h2>

<p>An array of distinct attribute keys in use for an interval can be gotten with it's describeAttributeKeys() method.</p>
<pre>
print_r( $hour->describeAttributeKeys() ); // array( 'attribute1', 'attribute2' );<br>
</pre>

<p>An array of distinct attribute keys <b>AND their values</b> in use for an interval can be gotten with it's describeAttributesValues() method.</p>
<pre>
print_r( $hour->describeAttributesValues() ); //  array('attribute1' => array( 'value1', 'value2' )<br>
</pre>


<a></a>
<h2>Describe Event Types</h2>

<p>An array of event types in use for an interval can be gotten with it's describeEventTypes() method</p>
<pre>
print_r( $hour->describeEventTypes() ); // array( 'clicks', 'views' )<br>
</pre>



