<!--
Copyright (c) 2008-2012 Simon Kågedal Reimer

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
-->
<html>
<head>
<title>PubMed ID to APA reference</title>
<style type="text/css">
body { text-align: center; background: #ccc; margin: 0px; padding: 0px; }
#maincontent { margin: 0px auto 0px auto; width: 600; text-align: left; background: white; padding: 1em; }
.error { color: red; }
</style>
</head>
<body>
<div id="maincontent">
<? 
function xml_url($pmid) {
	return 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id='.$pmid.'&retmode=xml';
}

function view_url($pmid) {
	return 'http://www.ncbi.nlm.nih.gov/pubmed/'.$pmid.'/';
}

function author_format($author) {
	return $author->LastName . ', ' . trim(chunk_split($author->Initials, 1, ". "));
}

function medlinepgn_format($pgn) {
	// Medline uses a strange pagination format: 354-7 means 354-357.
	// Return corrected version. If unable to parse, return original.
	$ps = explode("-", $pgn);
	if (count($ps) != 2) 
		return $pgn;
	$ps[0] = trim($ps[0]);
	$ps[1] = trim($ps[1]);
	if (!ctype_digit($ps[0]) or !ctype_digit($ps[1]))
		return $pgn;
	$i = strlen($ps[0]) - strlen($ps[1]);
	$ps[1] = substr($ps[0], 0, $i).$ps[1];
	return $ps[0]."&ndash;".$ps[1];
}

function ref_for_pmid($pmid) {
	$xml = simplexml_load_file(xml_url($pmid));
	$article = $xml->PubmedArticle->MedlineCitation->Article;

	echo "<blockquote>\n";

	// I wish I could just: echo implode(", ", array_map("author_format", $article->AuthorList->Author));
	foreach ($article->AuthorList->Author as $author)
		$authors[] = author_format($author);
	$c = count($authors);
	if ($c > 1) 
		$authors[$c-1] = "&amp; ".$authors[$c-1];
	echo implode(", ", $authors);

	$year = $article->ArticleDate->Year;
	if (!$year or trim($year)=="")
		$year = $article->Journal->JournalIssue->PubDate->Year;

	echo ' (' . $year . '). '; 

	echo $article->ArticleTitle." ";

	echo '<em>'.$article->Journal->Title.', '.$article->Journal->JournalIssue->Issue.',</em> '.medlinepgn_format($article->Pagination->MedlinePgn).'.';

	echo "</blockquote>\n";
}

function input_form() {
	?>
	<h1>PubMed to APA reference</h1>
	<p>Please type a PubMed ID, or PMID. (This is the number that is written after <code>/pubmed/</code> in the <acronym title="Uniform Resource Locator - webbsidans adress">URL</acronym>, such as <code>18784657</code> in <a href="http://www.ncbi.nlm.nih.gov/pubmed/18784657">this page</a> &ndash; the number is also displayed toward the bottom of the page, after the abstract)</p>
	<form action="index.php" method="get">
	PMID: <input type="text" name="pmid" value="" />
	<input type="submit" value="OK" />
	</form>
	<?
}

if ($_GET["pmid"]) {
	$pmid = trim($_GET["pmid"]);
	if (!ctype_digit($pmid)) {
		?> <p class="error">Invalid PMID: <? echo $pmid; ?> - A valid PMID consist of only digits!</p> <?
	} else {
		echo ('<p>Reference for <a href="'.view_url($pmid).'">'.$pmid.'</a> (<a href="'.xml_url($pmid).'">XML</a>):</p>');
		ref_for_pmid($_GET["pmid"]);
	}
}
input_form();


?>
<hr />
By <a href="http://helgo.net/simon/">Simon K&aring;gedal</a>, 2008 &ndash; 
<a href="https://github.com/skagedal/pubmed-to-apa">source code at github</a> (licensed with <a href="http://opensource.org/licenses/MIT">MIT License</a>)
</div> <!-- maincontent -->
</body>
</html>
