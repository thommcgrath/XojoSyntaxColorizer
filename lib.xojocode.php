<?php
/*
Xojo Syntax Highlighter for PHP

Originally Written by Jonathan Johnson <nilobject.com>
Updated for Xojo by Thom McGrath <thezaz.com>

https://github.com/thommcgrath/xojo-syntax-coloring-php
MIT License
*/
     
class XojoSyntaxColorizer {
	const DEFINE_AS_ORIGINAL = '';
	const DEFINE_WITH_DIM = 'Dim';
	const DEFINE_WITH_VAR = 'Var';
	
	const COLORS_AUTOMATIC = 'auto';
	const COLORS_LIGHT = 'light';
	const COLORS_DARK = 'dark';
	
	private $color_text = '#000000';
	private $color_keyword = '#0000ff';
	private $color_integer = '#336698';
	private $color_real = '#006633';
	private $color_string = '#6600fe';
	private $color_comment = '#800000';
	private $color_red = '#ff0000';
	private $color_green = '#00bb00';
	private $color_blue = '#0000ff';
	
	private $color_text_dark = '#ffffff';
	private $color_keyword_dark = '#fc5fa2';
	private $color_integer_dark = '#8b87ff';
	private $color_real_dark = '#8b87ff';
	private $color_string_dark = '#e08140';
	private $color_comment_dark = '#3aa53f';
	private $color_red_dark = '#fe5e49';
	private $color_green_dark = '#36da5d';
	private $color_blue_dark = '#009aff';
	
	private $source = '';
	
	private $showLineNumbers = false;
	private $lineBreak = "\n";
	private $changeKeywordCase = true;
	private $colorMode = self::COLORS_AUTOMATIC;
	private $variableDefinitionStyle = self::DEFINE_AS_ORIGINAL;
	
	// IsNumerical returns 0 if the string isn't a number
	// It returns 1 if it's an integer
	// and returns 2 if it's a double
	private static function IsNumerical(string $theString) {
		// An empty string isn't a number :P
		$len = strlen($theString);
		if ($len == 0) {
			return 0;
		}
		
		// Few options, either it begins with &h, &ho, &b
		// Since any of the &_ combinations require at least
		// two characters, we'll check for that first
		if ($len >= 6) {
			// Next, check to see if it starts with an &
			if (substr($theString, 0, 5) == '&amp;') {
				// Finally, check for the 3 known numerical types
				$secondChar = substr($theString, 5, 1);
				if ($secondChar == 'h' || $secondChar == 'o' || $secondChar == 'b') {
					// All these are always considered integers
					return 1;
				}
				// If we started with an &, but weren't of any of the above types
				// we know we aren't numerical
				return 0;
			}
		}
		
		// Now, we start out assuming we're an integer
		$type = 1;
	
	
	
		for ($pos = 0; $pos < $len; $pos++) {
			$char = substr($theString, $pos, 1);
			
			// If we're between 0 and 9, we don't modify the type
			if ($char >= '0' && $char <= '9') {
				
			} else if ($char == '.') {
				// If we are a decimal, we now assume double
				$type = 2;
			} else {
				// We failed to be numerical. Return 0
				//echo "Failed at " . $char . "<br />";
				return 0;
			}
		}
		
		// TODO: Check the value with MAXINT and change type to a double if needed
		
		if ($type == 1) {
			if ($len == 10) { // 
				if (((float)$theString) > 2147483647.0) {
					$type = 2;
				}
			} else if ($len > 10) {
				$type = 2;
			}
		}
		
		// return the type
		return $type;
	}
	
	protected static function Generate(string $source, bool $showLineNumbers = false, string $lineBreak = "\n", bool $changeKeywordCase = false, string $variableDefinitionStyle = self::DEFINE_AS_ORIGINAL) {
		// Trim the source code
		$source = trim($source);
		
		// Since this is going to output xhtml compliant code, we need to take the xml entities
		// and convert them. So, if the REALbasic code contains &, <, >, ', " they can come in as
		// the entities. They *will* come back out as entities no matter what.
		$source = str_replace( "&lt;", "<", $source );
		$source = str_replace( "&gt;", ">", $source );
		$source = str_replace( "&quot;", "\"", $source );
		$source = str_replace( "&apos;", "'", $source );
		$source = str_replace( "&amp;", "&", $source );
		
		if ($variableDefinitionStyle !== self::DEFINE_AS_ORIGINAL) {
			if ($variableDefinitionStyle == self::DEFINE_WITH_VAR) {
				$search = 'dim';
				$replace = 'var';
			} else {
				$search = 'var';
				$replace = 'dim';
			}
			
			$source = preg_replace_callback('/^' . $search . '\b/im', function($matches) use ($replace) {
				$i=0;
				return join('', array_map(function($char) use ($matches, &$i) {
					return ctype_lower($matches[0][$i++]) ? strtolower($char) : strtoupper($char);
				}, str_split($replace)));
			}, $source);
		}
		
		// A list of keywords to highlight in blue.
		$keywords = array(
			"#elseif" => "#ElseIf",
			"#bad" => "#bad",
			"#else" => "#Else", 
			"#endif" => "#EndIf",
			"#if" => "#If", 
			"#pragma" => "#Pragma",
			"#tag" => "#tag",
			"addhandler" => "AddHandler",
			"addressof" => "AddressOf",
			"and" => "And",
			"array" => "Array",
			"as" => "As",
			"assigns" => "Assigns",
			"break" => "Break",
			"byref" => "ByRef",
			"byval" => "ByVal",
			"call" => "Call",
			"case" => "Case",
			"catch" => "Catch",
			"class" => "Class",
			"const" => "Const",
			"continue" => "Continue",
			"ctype" => "CType",
			"declare" => "Declare",
			"delegate" => "Delegate",
			"dim" => "Dim",
			"do" => "Do",
			"downto" => "DownTo",
			"each" => "Each",
			"else" => "Else",
			"elseif" => "ElseIf",
			"end" => "End",
			"enum" => "Enum",
			"event" => "Event",
			"exception" => "Exception",
			"exit" => "Exit",
			"extends" => "Extends",
			"false" => "False",
			"finally" => "Finally",
			"for" => "For",
			"function" => "Function",
			"gettypeinfo" => "GetTypeInfo",
			"global" => "Global",
			"goto" => "GoTo",
			"handles" => "Handles",
			"if" => "If",
			"implements" => "Implements",
			"in" => "In",
			"inherits" => "Inherits",
			"inline68k" => "Inline68k",
			"interface" => "Interface",
			"is" => "Is",
			"isa" => "IsA",
			"lib" => "Lib",
			"loop" => "Loop",
			"me" => "Me",
			"mod" => "Mod",
			"module" => "Module",
			"namespace" => "Namespace",
			"new" => "New",
			"next" => "Next",
			"nil" => "Nil",
			"not" => "Not",
			"object" => "Object",
			"of" => "Of",
			"optional" => "Optional",
			"or" => "Or",
			"paramarray" => "ParamArray",
			"private" => "Private",
			"property" => "Property",
			"protected" => "Protected",
			"public" => "Public",
			"raise" => "Raise",
			"raiseevent" => "RaiseEvent",
			"redim" => "Redim",
			"removehandler" => "RemoveHandler",
			"return" => "Return",
			"select" => "Select",
			"self" => "Self",
			"shared" => "Shared",
			"soft" => "Soft",
			"static" => "Static",
			"step" => "Step",
			"structure" => "Structure",
			"sub" => "Sub",
			"super" => "Super",
			"then" => "Then",
			"to" => "To",
			"true" => "True",
			"try" => "Try",
			"until" => "Until",
			"using" => "Using",
			"var" => "Var",
			"weakaddressof" => "WeakAddressOf",
			"wend" => "Wend",
			"while" => "While",
			"with" => "With",
			"xor" => "Xor",
			
			// Instrinsic datatypes
			"byte" => "Byte",
			"short" => "Short",
			"integer" => "Integer",
			"int8" => "Int8",
			"int16" => "Int16",
			"int32" => "Int32",
			"int64" => "Int64",
			"uint8" => "UInt8",
			"uint16" => "UInt16",
			"uint32" => "UInt32",
			"uint64" => "UInt64",
			"boolean" => "Boolean",
			"single" => "Single",
			"double" => "Double",
			"currency" => "Currency",
			"string" => "String",
			"color" => "Color",
			"variant" => "Variant",
			"ptr" => "Ptr",
			"cstring" => "CString",
			"pstring" => "pstring",
			"wstring" => "WString",
			"cfstringref" => "CFStringRef",
			"windowptr" => "WindowPtr",
			"ostype" => "OSType",
			"text" => "Text",
			
			// XML Utilites only
			"controlinstance" => "ControlInstance",
		);
		
		// Take the source, and split it into lines
		// First, replace all the line breaks of different platforms
		// TODO: This could be optimized to be a single loop that modifies
		// the string. However, this is easier for now.
		$source = str_replace( "\r\n", "/|\**__", $source );
		$source = str_replace( "\n", "\r", $source );
		$source = str_replace( "/|\**__", "\r", $source );
		
		// Break the lines by \r's
		$lines = explode( "\r", $source );
		
		// Initialize indent level and output, and linecontinuation character
		$indentLevel = 0;
		$output = "";
		$lastLineHadLineContinuationCharacter = false;
		$lineNumberLength = strlen( count( $lines ) );
		
		$output .= '<span class="xojo_code_text">';
		$isInInterface = false;
		// Iterate over each line
		foreach ($lines as $lineNumber => $line) {
			if (!$lastLineHadLineContinuationCharacter) {
				$isIfLine = false;
				$endedWithThen = false;
			}
			// Trim the line. We handle the indentation, so we'll just trim off the beginning
			// of the line
			if ($showLineNumbers) {
				$output .= str_pad( $lineNumber, $lineNumberLength, "0", STR_PAD_LEFT ) . "  ";
			}
			
			$line = trim($line);
			// We want to iterate over each "token". To do this, we need to split them up
			// Initialze the tokens array
			$tokens = array();
			
			$pos = 0;
			$lineLength = strlen($line);
			$currentToken = "";
			$inInStyle = false;
			$isInQuote = false;
			
			for ($pos = 0; $pos < $lineLength; $pos++) {
				$char = substr($line,$pos,1);
				
				// If we're inside a string, we need to add it to the current token
				// unless it's a quote, in which case we end the current token
				if ($isInQuote && $char !='"') {
					$currentToken .= $char;
				} else {
					// Basically, every character has the same effect if it's an
					// operator or special character.
					switch ($char) {
						case '"':
							// if we're a quote, we need to switch the state
							$isInQuote = !$isInQuote;
							// Intentional fall-through
						case '(':
						case ')':
						case ' ':
	
						case '+':
						case '-':
						case '/':
						case '\\':
						case '*':
						case ',':
						case '\'':
						case '^':
							// If we have a current token, add it to the array
							if ($currentToken != "") {
								array_push( $tokens, $currentToken );
							}
							// Add the current character as its own token
							array_push( $tokens, $char );
							// Reset the current token
							$currentToken = "";
							break;
						default:
							// Add the character to the current token
							$currentToken .= $char;
							break;
					}
				}
			}
			
			// If we have a token left over, we need to add it to the array
			if ($currentToken != "") {
				array_push( $tokens, $currentToken );
			}
			
			// Now, we want to iterate over each token
			$isInQuote = false;
			$isInStyle = false;
			$isOnEndLine = false;
			$tmp = 0;
			$isInComment = false;
			// Check for if, #if, etc.
			if (count($tokens) > 0) {
				$lcaseToken = strtolower($tokens[0]);
				if ($lcaseToken == 'if') {
					$tmp = 2;
					$isIfLine = true;
				} else if ($lcaseToken == '#if' || $lcaseToken == "for" || 
					$lcaseToken == "while" || $lcaseToken == "do" || $lcaseToken == "try" || 
					$lcaseToken == "sub" || $lcaseToken == "function" || $lcaseToken == "class" || 
					$lcaseToken == "module" || $lcaseToken == "window" ||
					$lcaseToken == "controlinstance" || $lcaseToken == "get" || $lcaseToken == "set" || $lcaseToken == "property" || $lcaseToken == "structure" || $lcaseToken == "enum" || $lcaseToken == "select" || $lcaseToken == "event") {
					// increase indentation level
					if (!$isInInterface) $tmp = 2;
				} else if ($lcaseToken == "interface") {
					$isInInterface = true;
					$tmp = 2;
				} else if ($lcaseToken == "end" || $lcaseToken == "#endif" || $lcaseToken == "next" || 
						   $lcaseToken == "wend" || $lcaseToken == "loop") {
					$indentLevel -= 2;
					$isInInterface = false;
					$isOnEndLine = true;
				} else if ($lcaseToken == "else" || $lcaseToken == "elseif" || $lcaseToken == "#else" || 
						   $lcaseToken == "#elseif" || $lcaseToken == "catch" || 
						   $lcaseToken == "implements" || $lcaseToken == "inherits" || $lcaseToken == "case") {
					$tmp = 2;
					$indentLevel -= 2;
				} else if (count($tokens) > 2 && $tokens[1] == " ") {
					// Check for protected sub, protected function, etc
					$lcaseSecondToken = strtolower($tokens[2]);
					
					if (($lcaseToken == "protected" || $lcaseToken == "private" || $lcaseToken == "global" ||
						 $lcaseToken == "public") && ($lcaseSecondToken == "function" || $lcaseSecondToken == "sub")) {
						 $tmp = 2;
					}
				}
			}
			
			// Output the indentation
			if ($indentLevel > 0)
				$output .= str_repeat( " ", $indentLevel );
			
			// If we had a line continuation character, output extra spaces
			if ($lastLineHadLineContinuationCharacter) {
				$output .= "  ";
			}
			$lastLineHadLineContinuationCharacter = false;
			
			// $tmp was used to delay the addition to the intentLevel. We add it now
			$indentLevel += $tmp;
			
			for ($i=0; $i < count($tokens); $i++) {  
				// Each token now needs to have the entities replaced. This is the perfect time
				// because anything past this will possibly have xhtml tags, and therefore is too
				// late to perform a replacement.
				$tokens[$i] = str_replace( "&", "&amp;", $tokens[$i] );
				$tokens[$i] = str_replace( "<", "&lt;", $tokens[$i] );
				$tokens[$i] = str_replace( ">", "&gt;", $tokens[$i] );
				$tokens[$i] = str_replace( "\"", "&quot;", $tokens[$i] );
				$tokens[$i] = str_replace( "'", "&apos;", $tokens[$i] );
				$shouldEndStyle = false;
				// Get the lowercase of the token. This is just cached for speed.
				$lcaseToken = trim(strtolower( $tokens[$i] ));
				
				// if we're not in a comment, we can colorize things
				if (!$isInComment) {
					// Check to see if we're a quote
					if ($lcaseToken == '&quot;') {  // Strings
						if ($isInQuote) {
							// If we're the ending quote, we need to end the style
							$shouldEndStyle = true;
						} else {
							// If we're beginning, we need to output the beginning style
							$output .= '<span class="xojo_code_string">';
						}
						$isInQuote = !$isInQuote;
						
					// Check for keywords
					} else if ($isInQuote) {
						// do nothing. Quotes superceed all.
					} else if (array_key_exists($lcaseToken, $keywords)) {
						// Keywords are only coloring the single word, so we output
						// a font color, and then end the style
						$output .= '<span class="xojo_code_keyword">';
						$shouldEndStyle = true;
						if ($changeKeywordCase) {
							$tokens[$i] = $keywords[$lcaseToken];
						}
					} else if ($i == 0 and ($lcaseToken=="get" or $lcaseToken=="set")) {
						// Keywords are only coloring the single word, so we output
						// a font color, and then end the style
						$output .= '<span class="xojo_code_keyword">';
						$shouldEndStyle = true;
						
					} else if ($isOnEndLine and ($lcaseToken=="get" or $lcaseToken=="set")) {
						// Keywords are only coloring the single word, so we output
						// a font color, and then end the style
						$output .= '<span class="xojo_code_keyword">';
						$shouldEndStyle = true;
					
					// This could be prettier, but we're checking for numericals
					// and storing the result.
					} else if ($tmp = self::IsNumerical($lcaseToken)) {
						// tmp is now the type of numerical token
						if ($tmp == 1) {
							// Integer
							$output .= '<span class="xojo_code_integer">';
						} else {
							// Real
							$output .= '<span class="xojo_code_real">';
						}
						// The style should only be for this token, so we need to end the style
						$shouldEndStyle = true;
						
					// Comments. First, check for ', next check for //, and finally check for
					// rem
					} else if (substr($lcaseToken,0,6) == "&apos;" || ($lcaseToken == '/' && $i + 1 < count($tokens) && $tokens[$i+1] == '/') || $lcaseToken == "rem") {
						// Turn comment on (which is reset at the beginning of each line
						$isInComment = true;
						// output our style
						$output .= '<span class="xojo_code_comment">';
					} else if (strlen($lcaseToken) == 12 && substr($lcaseToken,0,6) == "&amp;c") {
						// This is tricky!
						$color = substr($tokens[$i],6);
						$tokens[$i] = "";
						$output .= "&amp;c";
						$output .= '<span class="xojo_code_rgb_red">' . substr($color, 0, 2) . '</span>';
						$output .= '<span class="xojo_code_rgb_green">' . substr($color, 2, 2) . '</span>';
						$output .= '<span class="xojo_code_rgb_blue">' . substr($color, 4, 2) . '</span>';
					}
				}
				// If we're not in a comment, we do a cheap check for line continuation
				if (!$isInComment && $lcaseToken != "") {
					if ($lcaseToken == '_') {
						$lastLineHadLineContinuationCharacter = true;
					} else {
						$lastLineHadLineContinuationCharacter = false;
					}
	
					if ($lcaseToken == "then") {
						$endedWithThen = true;
					} else {
						$endedWithThen = false;
					}
				}
				
				// Output the token
				$output .= $tokens[$i];
				
				// And now, check to see if we need to end the style
				if ($shouldEndStyle) {
					$output .= '</span>';
					$shouldEndStyle = false;
				}
			}
			
			if ($isIfLine && !$endedWithThen && !$lastLineHadLineContinuationCharacter) {
				$indentLevel -= 2;
			}
			
			// If we're in a comment, we need to end that style
			if ($isInComment) {
				$output .= '</span>';
			}
			
			// break line
			if ($lineNumber < count($lines) - 1) {
				$output .= $lineBreak;
			}
		}
		
		$output .= '</span>';
		
		// Return the block of text. Works best if wrapped in <pre></pre>
		return $output;
	}
	
	function __construct(string $source, array $colors = array()) {
		$this->SetSource($source);
		$this->SetColors($colors);
	}
	
	// The Xojo source code to generate from.
	
	function GetSource() {
		return $this->source;
	}
	
	function SetSource(string $source) {
		$this->source = $source;
	}
	
	// Builds the html from the current source.
	
	public function GetHTML() {
		$source = self::Generate($this->source, $this->showLineNumbers, $this->lineBreak, $this->changeKeywordCase, $this->variableDefinitionStyle);
		
		if ($this->colorMode != self::COLORS_AUTOMATIC) {
			$dark = $this->colorMode == self::COLORS_DARK;
			
			$needles = array(
				'<span class="xojo_code_text">',
				'<span class="xojo_code_keyword">',
				'<span class="xojo_code_integer">',
				'<span class="xojo_code_real">',
				'<span class="xojo_code_string">',
				'<span class="xojo_code_comment">',
				'<span class="xojo_code_rgb_red">',
				'<span class="xojo_code_rgb_green">',
				'<span class="xojo_code_rgb_blue">'
			);
			
			$replacements = array(
				'<span style="font-family: \'source-code-pro\', \'menlo\', \'courier\', monospace; color: ' . $this->GetTextColor($dark) . ';">',
				'<span style="color: ' . $this->GetKeywordColor($dark) . ';">',
				'<span style="color: ' . $this->GetIntegerColor($dark) . ';">',
				'<span style="color: ' . $this->GetRealColor($dark) . ';">',
				'<span style="color: ' . $this->GetStringColor($dark) . ';">',
				'<span style="color: ' . $this->GetCommentColor($dark) . ';">',
				'<span style="color: ' . $this->GetRedColor($dark) . ';">',
				'<span style="color: ' . $this->GetGreenColor($dark) . ';">',
				'<span style="color: ' . $this->GetBlueColor($dark) . ';">'
			);
			
			$source = str_replace($needles, $replacements, $source);
		}
		
		return $source;
	}
	
	// Returns a stylesheet for the currently defined colors.
	
	public function GetStylesheet() {
		$lines = array(
			'<style type="text/css">',
			'	span.xojo_code_text { font-family: "source-code-pro", "menlo", "courier", monospace; color: ' . $this->color_text . '; }',
			'	span.xojo_code_keyword { color: ' . $this->color_keyword . '; }',
			'	span.xojo_code_integer { color: ' . $this->color_integer . '; }',
			'	span.xojo_code_real { color: ' . $this->color_real . '; }',
			'	span.xojo_code_string { color: ' . $this->color_string . '; }',
			'	span.xojo_code_comment { color: ' . $this->color_comment . '; }',
			'	span.xojo_code_rgb_red { color: ' . $this->color_red . '; }',
			'	span.xojo_code_rgb_green { color: ' . $this->color_green . '; }',
			'	span.xojo_code_rgb_blue { color: ' . $this->color_blue . '; }',
			'	@media (prefers-color-scheme: dark) {',
			'		span.xojo_code_keyword { color: ' . $this->color_keyword_dark . '; }',
			'		span.xojo_code_integer { color: ' . $this->color_integer_dark . '; }',
			'		span.xojo_code_real { color: ' . $this->color_real_dark . '; }',
			'		span.xojo_code_string { color: ' . $this->color_string_dark . '; }',
			'		span.xojo_code_comment { color: ' . $this->color_comment_dark . '; }',
			'		span.xojo_code_rgb_red { color: ' . $this->color_red_dark . '; }',
			'		span.xojo_code_rgb_green { color: ' . $this->color_green_dark . '; }',
			'		span.xojo_code_rgb_blue { color: ' . $this->color_blue_dark . '; }',
			'	}',
			'</style>'
		);
		
		return implode("\n", $lines);
	}
	
	// Get and set colors
	
	public function GetColors(bool $dark = false) {
		return array(
			'text' => $this->GetTextColor($dark),
			'keyword' => $this->GetKeywordColor($dark),
			'integer' => $this->GetIntegerColor($dark),
			'real' => $this->GetRealColor($dark),
			'string' => $this->GetStringColor($dark),
			'comment' => $this->GetCommentColor($dark),
			'rgb_red' => $this->GetRedColor($dark),
			'rgb_green' => $this->GetGreenColor($dark),
			'rgb_blue' => $this->GetBlueColor($dark)
		);
	}
	
	public function SetColors(array $colors, bool $dark = false) {
		if (array_key_exists('text', $colors)) {
			$this->SetTextColor($colors['text'], $dark);
		}
		if (array_key_exists('keyword', $colors)) {
			$this->SetKeywordColor($colors['keyword'], $dark);
		}
		if (array_key_exists('integer', $colors)) {
			$this->SetIntegerColor($colors['integer'], $dark);
		}
		if (array_key_exists('real', $colors)) {
			$this->SetRealColor($colors['real'], $dark);
		}
		if (array_key_exists('string', $colors)) {
			$this->SetStringColor($colors['string'], $dark);
		}
		if (array_key_exists('comment', $colors)) {
			$this->SetCommentColor($colors['comment'], $dark);
		}
		if (array_key_exists('rgb_red', $colors)) {
			$this->SetRedColor($colors['rgb_red'], $dark);
		}
		if (array_key_exists('rgb_green', $colors)) {
			$this->SetGreenColor($colors['rgb_green'], $dark);
		}
		if (array_key_exists('rgb_blue', $colors)) {
			$this->SetBlueColor($colors['rgb_blue'], $dark);
		}
	}
	
	public function GetTextColor(bool $dark = false) {
		return $dark ? $this->color_text_dark : $this->color_text;
	}
	
	public function SetTextColor(string $color, bool $dark = false) {
		if ($dark) {
			$this->color_text_dark = $color;
		} else {
			$this->color_text = $color;
		}
	}
	
	public function GetKeywordColor(bool $dark = false) {
		return $dark ? $this->color_keyword_dark : $this->color_keyword;
	}
	
	public function SetKeywordColor(string $color, bool $dark = false) {
		if ($dark) {
			$this->color_keyword_dark = $color;
		} else {
			$this->color_keyword = $color;
		}
	}
	
	public function GetIntegerColor(bool $dark = false) {
		return $dark ? $this->color_integer_dark : $this->color_integer;
	}
	
	public function SetIntegerColor(string $color, bool $dark = false) {
		if ($dark) {
			$this->color_integer_dark = $color;
		} else {
			$this->color_integer = $color;
		}
	}
	
	public function GetRealColor(bool $dark = false) {
		return $dark ? $this->color_real_dark : $this->color_real;
	}
	
	public function SetRealColor(string $color, bool $dark = false) {
		if ($dark) {
			$this->color_real_dark = $color;
		} else {
			$this->color_real = $color;
		}
	}
	
	public function GetStringColor(bool $dark = false) {
		return $dark ? $this->color_string_dark : $this->color_string;
	}
	
	public function SetStringColor(string $color, bool $dark = false) {
		if ($dark) {
			$this->color_string_dark = $color;
		} else {
			$this->color_string = $color;
		}
	}
	
	public function GetCommentColor(bool $dark = false) {
		return $dark ? $this->color_comment_dark : $this->color_comment;
	}
	
	public function SetCommentColor(string $color, bool $dark = false) {
		if ($dark) {
			$this->color_comment_dark = $color;
		} else {
			$this->color_comment = $color;
		}
	}
	
	public function GetRedColor(bool $dark = false) {
		return $dark ? $this->color_red_dark : $this->color_red;
	}
	
	public function SetRedColor(string $color, bool $dark = false) {
		if ($dark) {
			$this->color_red_dark = $color;
		} else {
			$this->color_red = $color;
		}
	}
	
	public function GetGreenColor(bool $dark = false) {
		return $dark ? $this->color_green_dark : $this->color_green;
	}
	
	public function SetGreenColor(string $color, bool $dark = false) {
		if ($dark) {
			$this->color_green_dark = $color;
		} else {
			$this->color_green = $color;
		}
	}
	
	public function GetBlueColor(bool $dark = false) {
		return $dark ? $this->color_blue_dark : $this->color_blue;
	}
	
	public function SetBlueColor(string $color, bool $dark = false) {
		if ($dark) {
			$this->color_blue_dark = $color;
		} else {
			$this->color_blue = $color;
		}
	}
	
	// InclueLineNumbers, when enabled, adds line numbers to be beginning of each line of output.
	
	public function GetIncludeLineNumbers() {
		return $this->showLineNumbers;
	}
	
	public function SetIncludeLineNumbers(bool $value) {
		$this->showLineNumbers = ($value == true);
	}
	
	// LineBreakCharacter is the character inserted between lines.
	
	public function GetLineBreakCharacter() {
		return $this->lineBreak;
	}
	
	public function SetLineBreakCharacter(string $character) {
		$this->lineBreak = $character;
	}
	
	// StandardKeywordCase, when enabled, changes all keywords to their titlecase equivalents.
	
	public function GetStandardizeKeywordCase() {
		return $this->changeKeywordCase;
	}
	
	public function SetStandardizeKeywordCase(bool $value) {
		$this->changeKeywordCase = ($value == true);
	}
	
	// UseStylesheet, when enabled, uses CSS classes instead of style attributes.
	// This option is deprecated. Use ColorMode instead.
	
	public function GetUseStylesheet() {
		return $this->colorMode == self::COLORS_AUTOMATIC;
	}
	
	public function SetUseStylesheet(bool $value) {
		$this->colorMode = $value ? self::COLORS_AUTOMATIC : self::COLORS_LIGHT;
	}
	
	// ColorMode: Use with the COLORS constants to define how colors should be used in the html
	
	public function GetColorMode() {
		return $this->colorMode;
	}
	
	public function SetColorMode(string $mode) {
		switch ($mode) {
		case self::COLORS_LIGHT:
			break;
		case self::COLORS_DARK:
			break;
		default:
			$mode = self::COLORS_AUTOMATIC;
			break;
		}
		$this->colorMode = $mode;
	}
	
	// DefinitionStyle: Determines are variables are defined
	
	public function GetDefinitionStyle() {
		return $this->variableDefinitionStyle;
	}
	
	public function SetDefinitionStlye(string $style) {
		switch ($style) {
		case self::DEFINE_WITH_DIM:
			break;
		case self::DEFINE_WITH_VAR:
			break;
		default:
			$style = self::DEFINE_AS_ORIGINAL;
			break;
		}
		$this->variableDefinitionStyle = $style;
	}
}

// Alias function for the old FormatRBCode. Colors are respected and a stylesheet is not used,
// just like the original FormatRBCode.
function FormatRBCode(string $source, bool $showLineNumbers = false, string $lineBreak = "<br />", array $colors = array(), bool $changeKeywordCase = false) {
	foreach ($colors as $key => $value) {
		$colors[$key] = substr($value, 0, 1) == '#' ? $value : '#' . $value;
	}
	
	$colorizer = new XojoSyntaxColorizer($source, $colors);
	$colorizer->SetIncludeLineNumbers($showLineNumbers);
	$colorizer->SetLineBreakCharacter($lineBreak);
	$colorizer->SetStandardizeKeywordCase($changeKeywordCase);
	$colorizer->SetColorMode(XojoSyntaxColorizer::COLORS_LIGHT);
	return $colorizer->GetHTML();
}

// Alias function for the newer FormatXojoCode, which does use a stylesheet.
function FormatXojoCode(string $source, bool $showLineNumbers = false, string $lineBreak = "\n", bool $changeKeywordCase = false) {
	$colorizer = new XojoSyntaxColorizer($source);
	$colorizer->SetIncludeLineNumbers($showLineNumbers);
	$colorizer->SetLineBreakCharacter($lineBreak);
	$colorizer->SetStandardizeKeywordCase($changeKeywordCase);
	return $colorizer->GetHTML();
}

// Alias function to retrieve a stylesheet from an array of color values.
function XojoCodeStylesheet(array $colors = array()) {
	foreach ($colors as $key => $value) {
		$colors[$key] = substr($value, 0, 1) == '#' ? $value : '#' . $value;
	}
	
	$colorizer = new XojoSyntaxColorizer('', $colors);
	return $colorizer->GetStylesheet();
}
?>
