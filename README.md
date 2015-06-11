# xojo-syntax-coloring-php
PHP library for converting Xojo code into colored HTML

## Basic Usage
Simply include lib.xojocode.php and use
```php
echo FormatXojoCode('Dim Message As Text = "Hello World"');
```

Which outputs
```html
<span class="xojo_code_text"><span class="xojo_code_keyword">Dim</span> Message <span class="xojo_code_keyword">As</span> Text = <span class="xojo_code_string">&quot;Hello World&quot;</span></span>
```

## Styles
Changing the color of the output is done with CSS. This sample stylesheet will match the default colors of Xojo.

```css
span.xojo_code_text { font-family: "Source Code Pro", "Menlo", "Courier", monospace; color: #000000; }
span.xojo_code_keyword { color: #0000FF; }
span.xojo_code_integer { color: #336698; }
span.xojo_code_real { color: #006633; }
span.xojo_code_string { color: #6600FE; }
span.xojo_code_comment { color: #800000; }
```

## Method Signature
The full method signature is
```php
function FormatXojoCode($source, $showLineNumbers = false, $lineBreak = "\n", $changeKeywordCase = false)
```

## Backwards Compatibility
Users of Jonathan Johnson's FormatRBCode PHP function can use this updated library safely. A FormatRBCode alias is included matching the original parameters, however custom colors will be ignored.

## Misc
This library is an updated version of Jonathan Johnson's FormatRBCode PHP function.
