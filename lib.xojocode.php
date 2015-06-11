<?php
    /*
     *  Xojo Syntax Highlighter for PHP
     *
     *  Originally Written by Jonathan Johnson <nilobject.com>
     *  Updated for Xojo by Thom McGrath <thezaz.com>
     *  
     *  Public domain. Feel free to submit any changes back to me at the above
     *  address.
     */
     
     
// IsNumerical returns 0 if the string isn't a number
// It returns 1 if it's an integer
// and returns 2 if it's a double
function IsNumerical( $theString ) {
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


// FormatXojoCode takes a string and returns a new string
// The new string has indentation and coloring just like
// the defaults in REALbasic. It's best wrapped in <pre>
// </pre> tags.
function FormatRBCode ($source, $showLineNumbers = false, $lineBreak = "<br />", $colors = array(), $changeKeywordCase = false) {
	return FormatXojoCode($source, $showLineNumbers, $lineBreak, $changeKeywordCase);
}

function FormatXojoCode($source, $showLineNumbers = false, $lineBreak = "\n", $changeKeywordCase = false) {
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
    
    // A list of keywords to highlight in blue.
    $keywords = array(
		"#elseif" => "#ElseIf",
		"#bad" => "#bad",
		"#else" => "#Else", 
		"#endif" => "#EndIf",
		"#if" => "#If", 
		"#pragma" => "#pragma",
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
		"auto" => "Auto",
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
    while (list($lineNumber,$line) = each($lines))
    {
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
                } else if ($tmp = IsNumerical($lcaseToken)) {
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
                    $output .= '<span style="color: #FF0000;">' . substr($color, 0, 2) . '</span>';
                    $output .= '<span style="color: #00BB00;">' . substr($color, 2, 2) . '</span>';
                    $output .= '<span style="color: #0000FF;">' . substr($color, 4, 2) . '</span>';
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

function XojoCodeStylesheet ($colors = array()) {
	if (!isset( $colors['text'] )) $colors['text'] = '000000';
	if (!isset( $colors['keyword'] )) $colors['keyword'] = '0000FF';
	if (!isset( $colors['integer'] )) $colors['integer'] = '336698';
	if (!isset( $colors['real'] )) $colors['real'] = '006633';
	if (!isset( $colors['string'] )) $colors['string'] = '6600FE';
	if (!isset( $colors['comment'] )) $colors['comment'] = '800000';
	
	$lines[] = '<style type="text/css">';
	$lines[] = '	span.xojo_code_text { font-family: "Source Code Pro", "Menlo", "Courier", monospace; color: #' . $colors['text'] . '; }';
	$lines[] = '	span.xojo_code_keyword { color: #' . $colors['keyword'] . '; }';
	$lines[] = '	span.xojo_code_integer { color: #' . $colors['integer'] . '; }';
	$lines[] = '	span.xojo_code_real { color: #' . $colors['real'] . '; }';
	$lines[] = '	span.xojo_code_string { color: #' . $colors['string'] . '; }';
	$lines[] = '	span.xojo_code_comment { color: #' . $colors['comment'] . '; }';
	$lines[] = '</style>';
	
	return implode("\n", $lines);
}
?>
