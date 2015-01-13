<?
if ( !preg_match( '/^cli/i', php_sapi_name() ) ) {
	die("for command line use only");
}

require_once('wikirip.inc.php');

class gpTestBase extends PHPUnit_Framework_TestCase
{
	public function test_apply_image_cache_rewrite() {
		$txt = '<img src="/w/img_auth.php/2/28/Teaser_wohin-geht-spende.png" border="0" height="223" width="190">';
		$s = WikiRip::apply_image_cache_rewrite($txt, array($this, 'dummy_rewrite'));
		
		$this->assertEquals( '<img src="REWRITTEN:/w/img_auth.php/2/28/Teaser_wohin-geht-spende.png" border="0" height="223" width="190">', $s );
		
		$txt = <<<EOT
		<div class="infobox"> 
<a name="Frage_zu_Ihrer_Spende.3F" id="Frage_zu_Ihrer_Spende.3F"></a><h3> <span class="mw-headline">Frage zu Ihrer Spende?</span></h3>
<p>Till Mletzko von Wikimedia Deutschland antwortet Ihnen gerne.</p>
<a name="E-Mail_senden" id="E-Mail_senden"></a><h4> <span class="mw-headline"><a href="mailto:spenden@wikimedia.de" class="external text" title="mailto:spenden@wikimedia.de" rel="nofollow">E-Mail senden</a></span></h4>
<a name="Telefonat_vereinbaren" id="Telefonat_vereinbaren"></a><h4> <span class="mw-headline"><a href="mailto:spenden@wikimedia.de?subject=Ich%20wuerde%20gerne%20ein%20Telefonat%20vereinbaren&amp;amp" class="external text" title="mailto:spenden@wikimedia.de?subject=Ich%20wuerde%20gerne%20ein%20Telefonat%20vereinbaren&amp;amp" rel="nofollow">Telefonat vereinbaren</a> </span></h4>
</div>
<div class="infobox"> 
<p><a href="https://spenden.wikimedia.de/spenden//?rip=Ihre_Spende"><img  src="/w/img_auth.php/2/28/Teaser_wohin-geht-spende.png" width="190" height="223" border="0" /></a>
</p>

<a name="Weitere_Informationen" id="Weitere_Informationen"></a><h4> <span class="mw-headline"><a href="https://spenden.wikimedia.de/spenden/?rip=Ihre_Spende" class="external text" title="https://spenden.wikimedia.de/spenden/?rip=Ihre_Spende" rel="nofollow">Weitere Informationen</a></span></h4> 
</div>
<div class="infobox"> 
<a name="Spendenquittung" id="Spendenquittung"></a><h3> <span class="mw-headline">Spendenquittung</span></h3>
<p>Ab einer Spende von 25 Euro erhalten Sie automatisch eine Zuwendungsbestätigung Anfang nächsten Jahres.</p>
<a name="Weitere_Informationen_2" id="Weitere_Informationen_2"></a><h4> <span class="mw-headline"><a href="https://spenden.wikimedia.de/spenden/?rip=Fragen_und_Antworten" class="external text" title="https://spenden.wikimedia.de/spenden/?rip=Fragen_und_Antworten" rel="nofollow">Weitere Informationen</a></span></h4> 
</div>

<!-- 
NewPP limit report
Preprocessor node count: 22/1000000
Post-expand include size: 305/2097152 bytes
Template argument size: 73/2097152 bytes
Expensive parser function count: 0/100
-->

<!-- Saved in parser cache with key db177721_7:pcache:idhash:3067-1!1!0!!de!2 and timestamp 20110928173651 -->

EOT;

		$s = WikiRip::apply_image_cache_rewrite($txt, array($this, 'dummy_rewrite'));
		
		$this->assertTrue( strpos($s, 'REWRITTEN') !== false );

	}
	
	public function dummy_rewrite($m) {
		if ( preg_match('/^REWRITTEN:/', $m[2]) ) return $m[0]; 
		
		return $m[1] . "REWRITTEN:" . $m[2] . $m[3];
	}
	
	public function test_extract_template_params() {
		$txt = "{{SpendenLayout
				|hallo welt
				|show=Foo
				|banner=
				|form=einzug
				|stuff
				|tower=
				}}";
				
		$exp = array(
			1 => 'hallo welt',
			'show' => 'Foo',
			'banner' => '',
			'form' => 'einzug',
			2 => 'stuff',
			'tower' => '',
		);
		
		$params = WikiRip::extract_template_params($txt, 'SpendenLayout');
		
		$this->assertEquals( $exp, $params );
	}

}
