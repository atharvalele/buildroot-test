<?php
include("funcs.inc.php");
Header("Content-type: text/xml; charset=utf-8");
?>

<rdf:RDF
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns="http://purl.org/rss/1.0/"
  xmlns:dc="http://purl.org/dc/elements/1.1/"
  xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
  xmlns:admin="http://webns.net/mvcb/"
  xmlns:cc="http://web.resource.org/cc/"
  xmlns:content="http://purl.org/rss/1.0/modules/content/">

  <channel rdf:about="http://autobuild.buildroot.net">
  <title>Autobuild Buildroot results</title>
  <description>Autobuild Buildroot results</description>
  <link>http://autobuild.buildroot.net</link>
  <dc:language>en</dc:language>
  <dc:creator>buildroot.org</dc:creator>

<?php
echo " <items>\n";
echo "  <rdf:Seq>\n";

$results = bab_get_results(0, 50);

for ($i = 0; $i < count($results); $i++)
  {
    echo "<rdf:li rdf:resource=\"http://buildroot.humanoidz.org/results/" . $results[$i]['id'] . "\"/>\n";
  }

echo "  </rdf:Seq>\n";
echo " </items>\n";
echo "</channel>\n";

for ($i = 0; $i < count($results); $i++)
  {
    $current = $results[$i];
    echo " <item rdf:about=\"http://buildroot.humanoidz.org/results/" . $current['id'] . "\">\n";
    echo "  <title>Build " . ($status == "OK" ? "successful" : "failed") . " at " . $current['date'] . "</title>\n";
    echo "  <link>http://buildroot.humanoidz.org/results/" . $current['id'] . "</link>\n";
    echo "  <description>\n";
    if ($current['status'] == "OK") {
      echo "A Buildroot build result, submitted by " . $current['submitter'] . " was successful. The build used the Git commit id " . $current['gitid'] . " and was targetting the " . $current['arch'] . " architecture.";
    }
    else {
      echo "A Buildroot build result, submitted by " . $current['submitter'] . " failed. The reason of the failure is: " . $current['reason'] . ". The build used the Git commit id " . $current['gitid'] . " and was targetting the " . $current['arch'] . " architecture.";
    }
    echo "  </description>\n";
    echo " </item>\n\n";
  }
?>

</rdf:RDF>
