<?php

namespace PHPygments;
/**
 * Class Pygmentizer
 * @package PHPygments
 */
class Pygmentizer {

  //Available languages
  private $shortcuts = array(
    //General
    "code" => "text",
    "apacheconf" => "apacheconf",
    "bash" => "bash",
    "ini" => "ini",
    "cfg" => "cfg",
    "makefile" => "makefile",
    "nginx" => "nginx",
    "yaml" => "yaml",
    "perl" => "perl",
    "vbnet" => "vb.net",
    "console" => "console",
    //JS
    "javascript" => "javascript",
    "coffee_script" => "coffee-script",
    "json" => "json",
    //PHP
    "php" => "php",
    "cssphp" => "css+php",
    "htmlphp" => "html+php",
    "jsphp" => "js+php",
    "xmlphp" => "xml+php",
    //Ruby
    "ruby" => "ruby",
    "duby" => "duby",
    "csserb" => "css+erb",
    "cssruby" => "css+ruby",
    "xmlerb" => "xml+erb",
    "xmlruby" => "xml+ruby",
    //CSS
    "css" => "css",
    "sass" => "sass",
    "scss" => "scss",
    //HTML
    "html" => "html",
    "haml" => "haml",
    "jade" => "jade",
    //SQL
    "sql" => "sql",
    "sqlite3 " => "sqlite3",
    "mysql" => "mysql",
    //Python
    "python" => "python",
    "python3" => "python3",
    "xmldjango" => "xml+django",
    "xmljinja" => "xml+jinja",
    "cssdjango" => "css+django",
    "django" => "django",
    "jinja" => "jinja",
    "htmldjango" => "html+django",
    "htmljinja" => "html+jinja",
    "jsdjango" => "js+django",
    "jsjinja" => "js+jinja",
    //Java
    "clojure" => "clojure",
    "java" => "java",
    "groovy" => "groovy",
    "jsp" => "jsp",
    //C
    "cobjdump" => "c-objdump",
    "c" => "c",
    "cpp" => "cpp",
    "csharp" => "csharp",
    "objectivec" => "objective-c",
    //XML
    "xml" => "xml",
    "xslt" => "xslt",
    "rest" => "rest",
  );

  private $styles = array(
    "github",
    "monokai",
    "manni",
    "rrt",
    "perldoc",
    "borland",
    "colorful",
    "default",
    "murphy",
    "vs",
    "trac",
    "tango",
    "fruity",
    "autumn",
    "bw",
    "emacs",
    "vim",
    "pastie",
    "friendly",
    "native",
    "solarized-light",
    "solarized-dark"
  );

  /** @var $pygmentParams array Pygments default params */
  public $pygmentParams = array(
    'lang' => "text",
    'style' => "default",
    'linenumbers' => FALSE,
  );

  function __construct($defaultStyle = "default", $defaultLinenumbers = FALSE) {
    $this->pygmentParams["style"] = $defaultStyle;
    $this->pygmentParams["linenumbers"] = $defaultLinenumbers;
  }

  /**
   * Convert all shortcodes like [javascript][/javascript]
   * into the pygments shortcode [pyg lang="javascript"][/pyg]
   *
   * @param string $text
   *   text where are shortcodes
   * @return string
   *   Modified $text
   */
  protected function shortcuts2shortcodes(&$text) {

    foreach ($this->shortcuts as $shortcut => $lang) {

      $pattern = $this->getShortcodeRegex(array($shortcut));

      //workarround for closures
      $self = $this;

      $text = preg_replace_callback("/$pattern/s",
        function ($matches) use (&$self, $lang) {

          //Extract and set defaults
          $attrs = $self->extractAtts($matches[3]) + $self->pygmentParams;
          $attrs['lang'] = $lang;

          $atributtes = "";
          foreach ($attrs as $attribute => $data) {
            $atributtes .= $attribute . '="' . $data . '" ';
          }

          return '[pyg ' . rtrim($atributtes) . ']' . $matches[5] . '[/pyg]';
        },
        $text);
    }
  }

  /**
   * hightlights all valid tokens found un given text
   * @param $text
   * @param $return
   * @return mixed
   */
  protected function renderShortcodes(&$text, &$return) {

    $pattern = $this->getShortcodeRegex(array("pyg"));
    $text = preg_replace_callback("/$pattern/s", function ($matches) use (&$return) {

      $attr = $this->extractAtts($matches[3]) + $this->pygmentParams;

      $rendered = PHPygments::render($matches[5], $attr['lang'], $attr['style'], $attr['linenumbers']);
      //vamos haciendo push para sobreescribir los style que ya se hayan agregado
      $return["styles"][] = $rendered["styles"];
      $return["styles"] = array_unique($return["styles"]);

      return $rendered["code"];
    }, $text);
  }

  /**
   * hightlights all valid tokens found un given text.
   *
   * @param string $text
   *   String where find shortcodes to render
   * @param bool $concatenated default TRUE
   *   Determines if the return is an array with style
   *   and rendered contents separated or all together.
   *
   * @return array|string
   */
  public function hightlight($text, $concatenated = TRUE) {

    $return = array('styles' => array());
    $this->shortcuts2shortcodes($text);
    $this->renderShortcodes($text, $return);

    if ($concatenated) {
      // Useful when this method is called only once and nobody is
      // taking care of css's loading.
      $styles = '<style type="text/css">' . "\n";
      foreach ($return["styles"] as $style) {
        $styles .= file_get_contents(dirname(__FILE__) . '/' . $style) . "\n";
      }
      $styles .= '</style>';

      return $styles . $text;
    }
    else {
      return array(
        'styles' => $return["styles"],
        'code' => $text
      );
    }


  }


  /**
   * Generates the right pattern to find shortcodes.
   *
   * @param array $tagnames
   *   A list of shortcode tagnames you want find.
   *
   * @return string pattern
   */
  protected function getShortcodeRegex($tagnames) {
    $tagregexp = join('|', array_map('preg_quote', $tagnames));
    return
      '\\[' // Opening bracket
      . '(\\[?)' // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
      . "($tagregexp)" // 2: Shortcode name
      . '(?![\\w-])' // Not followed by word character or hyphen
      . '(' // 3: Unroll the loop: Inside the opening shortcode tag
      . '[^\\]\\/]*' // Not a closing bracket or forward slash
      . '(?:'
      . '\\/(?!\\])' // A forward slash not followed by a closing bracket
      . '[^\\]\\/]*' // Not a closing bracket or forward slash
      . ')*?'
      . ')'
      . '(?:'
      . '(\\/)' // 4: Self closing tag ...
      . '\\]' // ... and closing bracket
      . '|'
      . '\\]' // Closing bracket
      . '(?:'
      . '(' // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
      . '[^\\[]*+' // Not an opening bracket
      . '(?:'
      . '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
      . '[^\\[]*+' // Not an opening bracket
      . ')*+'
      . ')'
      . '\\[\\/\\2\\]' // Closing shortcode tag
      . ')?'
      . ')'
      . '(\\]?)'; // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
  }

  /**
   * Extract all attributes of an shortcode block.
   *
   * @param string $string a string containing attributes like 'some="attribute"  another="attribute"'
   *
   * @return array
   *  array list of found attrs
   */
  public function extractAtts($string) {
    $atts = array();
    $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
    $string = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $string);

    if (preg_match_all($pattern, $string, $match, PREG_SET_ORDER)) {
      foreach ($match as $m) {
        if (!empty($m[1])) {
          $atts[strtolower($m[1])] = stripcslashes($m[2]);
        }
        elseif (!empty($m[3])) {
          $atts[strtolower($m[3])] = stripcslashes($m[4]);
        }
        elseif (!empty($m[5])) {
          $atts[strtolower($m[5])] = stripcslashes($m[6]);
        }
        elseif (isset($m[7]) and strlen($m[7])) {
          $atts[] = stripcslashes($m[7]);
        }
        elseif (isset($m[8])) {
          $atts[] = stripcslashes($m[8]);
        }
      }
    }
    else {
      $atts = array();
    }
    return $atts;
  }

  public function getStyles() {
    return $this->styles;
  }

  public function getShortcuts() {
    return $this->shortcuts;
  }


}


