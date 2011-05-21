
# A PHP Zencoding parser

## For fun

Because nobody cares.

Input:

	body>#page>div.logo.big+ul#navigation>li.item.item-%%*5>a[href="#"]:before(Click here!)+span.s%%*2

Output:

	<body>
		<div id="page">
			<div class="logo big"></div>
			<ul id="navigation">
				<li class="item item-1">
					<a href="#">Click here!</a>
					<span class="s1"></span>
					<span class="s2"></span>
				</li>
				<li class="item item-2">
					<a href="#">Click here!</a>
					<span class="s1"></span>
					<span class="s2"></span>
				</li>
			</ul>
		</div>
	</body>

## Why?

See <http://code.google.com/p/zen-coding/> to find out why. I just did it for fun...

There is a downside to this syntax: you can only go deeper (Javascript's `nextSibling`
and `firstChild`). There's no way to create sibling subtrees with different content.

## Syntax

Think CSS:

* `>`: direct child
* `+`: next sibling
* `#`: id following
* `.`: class name following
* `[attr]`: empty attribute with name "attr"
* `[attr=value]`: attribute "name" with value "value"
* `:before(X)`: inserts "X" at the start of this element
* `:after(X)`: inserts "X" at the end of this element
* `*N`: repeat this element `N` times
* `%%`: number of iteration (starting with `1`, not `0`) (only applicable with `*N`)

## Better syntax?

Better syntax for stacks might be:

	root{elements,elements,{elements, elements}}

instead of (also unimplemented):

	root{elements}{elements{elements}{elements}}
