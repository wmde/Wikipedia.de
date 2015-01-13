<?php
$dir=dirname(__FILE__);
$cache_url = "http://wikipedia.de/cache/";
$self = $_SERVER['php_self'];

// read cache list
$list = file("$dir/../wpde-image-cache.list");
$urls = array();

if ( $list ) {
	foreach ( $list as $s ) {
		$a = explode("\t", $s);
		if ( $a[0] == 'url' ) {
			$n = "wpde-" . $a[1];
			$urls[ $n ] = $a[ 2 ];
		}
	}
}

// list files ---------------------------------
function list_stats_files($dir) {
	global $self;

	$ff=glob("$dir/stats-*.txt");

	foreach ( $ff as $f ) {
		$n = basename($f);
	
		print "\n\t\t<li><a href=\"".htmlspecialchars("$self?f=$n")."\">".htmlspecialchars($n)."</a></li>";
	}
}

// show img stats
function print_stats($f) {
	global $urls, $cache_url;

        $list = file("$f");

        foreach ( $list as $s ) {
			if ( preg_match ('/(\d+)\s+([^\s]+)/', $s, $m) ) {
				$c = (int)$m[1];
				$n = basename($m[2]);
				$u = @$urls[ $n ];
				
				if (!$u) continue;

				print "\n\t<tr>";
				print "\n\t\t<td align='right'>".htmlspecialchars($c)."</td>";
				print "\n\t\t<td align='left'><a href=\"".htmlspecialchars("$cache_url/$n")."\">".htmlspecialchars($n)."</a></td>";
				
				if ($u) {
					$w = basename($u);
					$w = preg_replace('/^\d+px-/', '', $w);
					print "\n\t\t<td align='left'><a href=\"".htmlspecialchars($u)."\">".htmlspecialchars($w)."</a></td>";
				}
				
				print "\n\t</tr>";
			}
        }
}


$show = @$_SERVER['PATH_INFO'];
if ( !$show) $show = @$_GET['f'];

$show = preg_replace('@[;:/\\!]@', '', $show);

?>

<html>
<head>
	<title>Image Cache Stats</title>
	<style type="text/css">
		.stats td { padding:0.5ex 1ex; }
	</style>
</head>
<body>
	<?php if ( $show ) { ?>
		<h1>Image Cache Stats: <?php print htmlspecialchars($show); ?></h1>

		<table class="stats">
		<?php print_stats("$dir/$show")  ?>
		</table>
	<?php } else { ?>
		<h1>Image Cache Stats</h1>

		<ul class="files">
		<?php list_stats_files("$dir")  ?>
		</ul>
	<?php } ?>
</body>
</html>

