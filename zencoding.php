<?php

$input = 'body>#page>div.logo.big+ul#navigation>li.item.item-%%*5>a[href="#"]:before(Click here!)+span.s%%*2';

if ( isset($_GET['zen']) ) {
	$input = $_GET['zen'];
}

?>
<!doctype html>
<html>

<head>
<style>
input:not([type="submit"]) { font-family: 'Courier new'; font-size: 100%; padding: 2px 3px; }
</style>
</head>

<body>

<pre>
<?php

function zen_element( $content, &$element, &$num, &$after = '' ) {
	list($content, $num) = explode('*', $content.'*1');
	$num = (int)$num;

	$classes = array();
	$id = '';
	$more = '';
	$before = '';

	$parts = preg_split('/(?=[\#\.\[:])/', $content);
	foreach ( $parts AS $i => &$part ) {
		if ( 0 < $i && 1 == substr_count($part, ']') && 1 == substr_count($parts[$i-1], '[') ) {
			$parts[$i-1] .= $part;
			unset($parts[$i]);
		}
		unset($part);
	}
//print_r($parts);
	$element = array_shift($parts) or $element = 'div';

	foreach ( $parts AS $part ) {
		$value = substr($part, 1);
		switch ( substr($part, 0, 1) ) {
			case '.':
				$classes[] = $value;
			break;
			case '#':
				$id = $value;
			break;
			case '[':
				if ( ']' == substr($part, -1) ) {
					$more .= ' '.substr($part, 1, -1);
				}
			break;
			case ':':
				if ( preg_match('/(before|after)\(([^\)]+)\)/', $value, $matches) ) {
					switch ( $matches[1] ) {
						case 'before':
							${$matches[1]} = $matches[2];
						break;
						case 'after':
							$after = $matches[2];
						break;
					}
				}
			break;
		}
	}

	$classes = $classes ? ' class="'.implode(' ', $classes).'"' : '';
	$id = $id ? ' id="'.$id.'"' : '';

	$html = '<'.$element.$id.$classes.$more.'>'.$before;
	return $html;
}

function zen_repeat_element( $element, $num ) {
	static $wildcard = '%%';

	$element = stripslashes($element);

	if ( !is_int(strpos($element, $wildcard)) ) {
		return str_repeat($element, $num);
	}

	$html = '';
	for ( $i=1; $i<=$num; $i++ ) {
		$html .= str_replace($wildcard, $i, $element);
	}
	
	return $html;
}

function zen_tree( $_tree ) {
	$tree = array();
	foreach ( $_tree AS $el ) {
		if ( !isset($el[1]) ) {
			$tree[$el[0]] = array();
		}
		else {
			$tree[$el[0]] = zen_tree($el[1]);
		}
	}
	return $tree;
}

function zen_parse( $input ) {
	if ( is_array($input) ) {
		$html = '';
		foreach ( $input AS $elements => $children ) {
//			var_dump($elements);
			$html .= zen_parse($elements);
			$html .= zen_parse($children);
		}
		return $html;
	}

	if ( is_int(strpos($input, '{')) ) {
		// build tree
		$tree = "array('".strtr($input, array(
			'}{' => "'), array('",
			'{' => "', array(array('",
			'}' => "'))",
		)).')';
		$tree = strtr($tree, array(
			")')" => '))',
		));
		eval('$_tree = '.$tree.';'); // yuck!
		$root = array_shift($_tree);
		$_tree = $_tree[0];
		$tree = array($root => zen_tree($_tree));
print_r($tree);
return '';
		return zen_parse($tree);

		// TEMP:
		$input = str_replace('{', '', str_replace('}', '', $input));
	}

	$parts = preg_split('/(?=[\+>])/', $input);

	$output = '';
	$stack = array();
	$maxdepth = $depth = -1;
	foreach ( $parts AS $i => $part ) {
		$first = !$i;
		$child = true;
		if ( $first ) {
			$content = $part;
		}
		else {
			$type = substr($part, 0, 1);
			$child = '>' == $type;
			$content = substr($part, 1);
		}

		if ( !$child ) {
			$tabs = str_repeat("\t", $depth);
			list($el, $n) = array_pop($stack);
			$output .= '</'.$el.'>';
		}

		$depth += (int)$child;
		$maxdepth = max($depth, $maxdepth);
		$tabs = str_repeat("\t", $depth);

		$element = '';
		$num = 1;
		$after = '';
		$content = zen_element($content, $element, $num, $after);

		if ( 1 < $num ) {
			$output .= '[['.$depth.':'.$num.'[[';
		}
		$output .= ( $first ? '' : "\n" ) . $tabs . $content;

		$stack[] = array($element, $num, $after);
	}

	$first = true;
	for ( $i=count($stack)-1; $i>=0; $i-- ) {
		list($el, $n, $aft) = array_pop($stack);
		$tabs = str_repeat("\t", $i);
		if ( $aft ) {
			$aft = "\t".$aft."\n".$tabs;
		}
		$output .= ($first ? '' : "\n" . $tabs ) . $aft . '</'.$el.'>';
		if ( 1 < $n ) {
			$output .= ']]'.$i.':'.$n.']]';
		}
		$first = false;
	}

	$output .= "\n";

	for ( $i=$maxdepth; $i>=0; $i-- ) {
		$key = $i.':(\d+)';
		if ( preg_match('#\[\[('.$key.')\[\[(.+)\]\]\1\]\]#s', $output, $matches) ) {
			$output = preg_replace('#\[\[('.$key.')\[\[(.+)\]\]\1\]\]#se', "zen_repeat_element('\\3', \\2)", $output);
		}
	}

	return $output;
}

$output = zen_parse($input);
echo htmlspecialchars($output);

?>

</pre>

<form>
	<p>Zen string<br><input name=zen style="width: 96%;" value="<?=htmlspecialchars($input)?>"></p>
	<p><input type=submit></p>
</form>

</body>

</html>


