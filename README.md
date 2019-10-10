# XojoSyntaxColorizer
PHP class for converting Xojo code into colored HTML.

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
span.xojo_code_text { font-family: "source-code-pro", "menlo", "courier", monospace; color: #000000; }
span.xojo_code_keyword { color: #0000FF; }
span.xojo_code_integer { color: #336698; }
span.xojo_code_real { color: #006633; }
span.xojo_code_string { color: #6600FE; }
span.xojo_code_comment { color: #800000; }
```

## Advanced Usage
The basic function above is just an alias for using the XojoSyntaxColorizer class with some pre-defined settings. For more options, create a new instance of XojoSyntaxColorizer.

```php
$colorizer = new XojoSyntaxColorizer('Dim Message As Text = "Hello World"');
```

The constructor optionally takes an array to customize coloring options:

```php
$colors = array(
	'text' => '#000000',
	'keyword' => '#0000FF',
	'integer' => '#336698',
	'real' => '#006633',
	'string' => '#6600FE',
	'comment' => '#800000'
);
$colorizer = new XojoSyntaxColorizer('Dim Message As Text = "Hello World"', $colors);
```

Only the colors being changed are required. Missing keys in the array will simply be skipped. The colors can also be changed after creation using SetColors.

```php
$colorizer->SetColors($colors);
```

Or with any of the Set*Color methods.

```php
$colorizer->SetTextColor('#000000');
$colorizer->SetKeywordColor('#0000FF');
$colorizer->SetIntegerColor('#336698');
$colorizer->SetRealColor('#006633');
$colorizer->SetStringColor('#6600FE');
$colorizer->SetCommentColor('#800000');
```

These colors can also be retrieved using Get variants of the methods.

After colors have been set, a stylesheet can be built using GetStylesheet.

All color functions support an additional boolean parameter to work on dark mode variations. Use true for dark mode, false for classic/light mode.

```php
$colorizer->SetTextColor('#FFFFFF', true);
$dark_colors = $colorizer->GetColors(true);
$colorizer->SetColors($dark_colors, true);
$text_color = $colorizer->GetTextColor(true);
```

A few options are available, which are controlled with their Get and Set methods.

- IncludeLineNumbers: Defaults to false. When enabled, line numbers are included in the output. Because these make the code difficult to copy and paste into the IDE, line numbers are not recommended.
- LineBreakCharacter: Defaults to \n.
- StandardizeKeywordCase: Defaults to true. When enabled, keywords will be titlecased.
- ColorMode: Uses the constants `COLORS_AUTOMATIC`, `COLORS_LIGHT`, or `COLORS_DARK` to determine how to generate the html colors. The default is `COLORS_AUTOMATIC`. When using `COLORS_AUTOMATIC`, the class will generate the html using `span` elements with CSS classes. Use `GetStylesheet` to generate the CSS with automatic activation of both light and dark variants depending on viewer preferences. Use `COLORS_LIGHT` or `COLORS_DARK` to force output of specific colors embedded into the `span` elements themselves.
- DefinitionStyle: Uses the constants `DEFINE_AS_ORIGINAL`, `DEFINE_WITH_DIM`, or `DEFINE_WITH_VAR` to determine how variable definitions will be formatted. The default is `DEFINE_AS_ORIGINAL`.

## Backwards Compatibility
Users of Jonathan Johnson's FormatRBCode PHP function can use this updated library safely. A FormatRBCode alias is included matching the original parameters. Colors will be respected if provided, and inline styles will be used, which is most similar to the original FormatRBCode output.

## Misc
This library is an updated version of Jonathan Johnson's FormatRBCode PHP function.
