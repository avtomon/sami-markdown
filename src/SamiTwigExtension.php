<?php

namespace Markdown;

use \Sami\Reflection\Reflection as Reflection;
use \Sami\Reflection\ClassReflection as ClassReflection;
use \Sami\Reflection\MethodReflection as MethodReflection;
use \Sami\Reflection\ParameterReflection as ParameterReflection;
use \Sami\Reflection\HintReflection as HintReflection;
use \Sami\Reflection\InterfaceReflection as InterfaceReflection;

/** 
 * An extension than builds a single ReadMe markdown 
 * file out of your code. 
 *
 * By now, most is done "behind the scene" and the template
 * is very minimal. This means that it's not much you can do to
 * change any of the layout etc, without editing the actual code/class,
 * but you may want to change template structure by calling 
 * the methods "render_classes", "render_class" etc, but be aware
 * that these methods does not remove whitespaces and takes care of
 * any format issues your template will get.
 * 
 *
 * This is in lack of a a single-file-documentation, for a project,
 * and since the markdown language is very strict on format -
 * (indentation, signs etc.) - using the twig template was a hassle,
 * whitout ending with several "twig-files" that was unreadable (no
 * indentation etc).
 *
 * Get [Sami](https://github.com/FriendsOfPHP/Sami/) and fork this
 * repo
 *
 * ```bash
 * git clone giaever@git.giaever.org:joachimmg/sami-markdown.git
 * ```
 *
 * Include the `SamiTwigExtension.php` into your Sami configuration-file
 * and add it to twig. See `example.conf.php` or 
 *
 *  * set template: `"template" => "markdown"`
 *  * add extension:
 *
 * ```php
 * $sami["twig"]->addExtension(new Markdown\SamiTwigExtension());
 * ```
 *
 * @see Twig_Extension
 * @see Sami
 * @see example.conf.php
 *
 * @author Joachim M. Giaever (joachim[]giaever.org)
 * @version 0.1
 */
class SamiTwigExtension extends \Twig_Extension {

    /**
     * Setting pretty-print to true will print
     * tables in the read me into a pretty printet format,
     * which makes it easier to read.
     *
     * @static
     * @access public
     */
    public static $pretty_print = false;

    /**
     * @see $pretty_print
     */
    public function __construct(bool $pretty_print = false) {
        self::$pretty_print = $pretty_print;
    }

    /** 
     * Set the functions available for the template engine.
     * @return 
     */
    public function getFunctions() {
        return array(
            new \Twig_Function("toc", array($this, "toc")),
            new \Twig_Function("render", array($this, "render"))
        );
    }

    /** 
     * Checks if var is a Sami-reflection type.
     *
     * Makes it easier to determine if the variable is
     * a string (namespace) or a Reflection-type.
     *
     * @param $var
     * @return bool
     */
    private static function isReflection($var) {
        return $var instanceof Reflection || $var instanceof HintReflection;
    }

    /**
     * Join an array into a string.
     *
     * @param array $arr The array to join
     * @param string $pad Padding letter (typically a asterisk etc)
     * @param string $sep Separator, before the padding
     * @param string $esep Ending separator
     * @param string $epad Ending pad
     * @return null On empty array
     * @return string
     */
    private static function join(array $arr, string $pad = "", string $sep = "\n", string $esep = "", string $epad = "") {
        if (empty($arr))
            return null;

        return $sep . $pad . join($sep . $pad, $arr) . $esep . $epad;
    }

    /** 
     * Returns x numbers of tabulations.
     *
     * @param int $depth 
     * @return string
     */
    private static function tab(int $depth) {
        return str_repeat("\t", $depth);
    }

    /**
     * Returns the short description of a reflection.
     *
     * @todo Implement $max
     * @param \Sami\Reflection\Reflection $refl Reflection to return description of
     * @param bool $oneliner Removes every newline and tabulation.
     * @param int $max Maximum of letters
     * @return string|null Null on empty
     */
    public static function short_description(Reflection $refl, bool $oneliner = true, int $max = -1) {
        return !empty($desc = $refl->getShortDesc()) ? (
            $oneliner ? str_replace(array("\n", "\r", "\t"), "", $desc) : $desc
        ) : null;
    }

    /**
     * Returns a long description (both short and long) of a reflection.
     *
     * @param \Sami\Reflection\Reflection $refl Reflection to return description of
     * @param bool $oneliner Removes every newline and tabulation
     * @return string|null Null on empty
     */
    public static function long_description(Reflection $refl, bool $oneliner = false) {
        return !empty($desc = self::short_description($refl, false) . (!empty($refl->getLongDesc()) ? "\n\n" . $refl->getLongDesc() : null)) ? (
            $oneliner ? str_replace(array("\n", "\r", "\t"), "", $desc) : $desc
        ) : null;
    }

    /**
     * Returnes a deprecated label if class, method etc is.
     *
     * If `$notice` is false, it will include the deprecated
     * note - if given in the documentation.
     *
     * @param \Sami\Reflection\Reflection Reflection
     * @param bool $notice Just as notice
     * @return string|null Null on none
     */
    public static function deprecated(Reflection $refl, $notice = true) {

        if (empty($refl->getDeprecated()))
            return null;

        if ($notice)
            return "`@deprecated`";

        return sprintf(
            "`@deprecated`: %s",
            join(" ", $refl->getDeprecated()[0])
        );
    }

    /** 
     * Returns todo-tag for Reflection. 
     * 
     * @param Reflection $refl Reflection to get todo tag from.
     * @return array|null Null on empty
     */
    public static function todo(Reflection $refl) {
        return empty($todo = $refl->getTodo()) ? $todo : null;
    }

    /**
     * Returns see-tag for Reflection.
     *
     * @param Reflection $refl Reflection to get see-tag from.
     * @return array|null Null on empty
     */
    public static function see(Reflection $refl) {
        return empty($see = $refl->getTags("see")) ? $see : null;
    }

    /**
     * Returnes a markdown link.
     *
     * To match the markdown template classes is linked to by 
     * `#classname-namespace`, and methods `#method-namespace\classname`
     * and namespaces is linked to by `#namespace`, `$namespace` must be set
     * to true when linking to it.
     *
     * @param string $ltxt The link text
     * @param string $lurl The link destination
     * @param bool $namespace True when linking to a namespace
     * @param string $desc Link title (like the html title/hover-tag)
     * @return string
     **/
    public static function href(string $ltxt, string $lurl, bool $namespace = false, string $desc = null) {
        $desc = $desc ? sprintf(' "%s"', $desc) : null;

        // Not linking a namespace directly
        if (!$namespace) {
            // Not within this package
            if (strpos($lurl, "\\") === false)
                return sprintf("[%s](https://www.google.no/search?q=%s%s)", $ltxt, rawurlencode($lurl), $desc);
            else // Withing package; set `name`-`namespace` order
                $lurl = sprintf(
                    "%s %s", 
                    substr($lurl, strrpos($lurl, "\\")), 
                    substr($lurl, 0, strrpos($lurl, "\\", 1))
                );
        }

        return sprintf(
            "[%s](#%s%s)", 
                $ltxt, 
                strtolower(
                    str_replace(
                        array("\\", " "), // Replace "\" = "" and " " = "-" 
                        array("", "-"), 
                        $lurl
                    )
                ), 
                $desc
            );
    }

    /**
     * Tabel of contents
     *
     * Generates a table of contentes out of the whole
     * project tree.
     *
     * @param array $tree The tree array passed from twig
     * @param int depth Initially this should be 0
     * @return string
     **/
    public static function toc(array $tree, int $depth = 0) {

        // Appending to given string
        $append = (function(string &$into, int $depth, int $idx, array $elem) {

            // Create a link to this entry
            $element = self::href($elem[0], $elem[1], is_string($elem[1]), $elem[1]);

            // Get deprecated notice and short description
            if (self::isReflection($elem[1])) {
                if (($dep = self::deprecated($elem[1])))
                    $element .= " " . $dep;
                if (($desc = self::short_description($elem[1])))
                    $element .= " " . $desc;
            }

            $into .= sprintf("%s%d. %s\n", self::tab($depth), $idx + 1, $element);
        });

        $str = "";

        foreach ($tree as $key => $elem) {
            $append($str, $depth, $key, $elem); 

            if (isset($elem[2]) && !empty($elem[2])) {
                
                usort($elem[2], function($a, $b) {
                    return strcmp("" . $a[1], "" . $b[1]);
                });

                foreach($elem[2] as $key2 => $elem2) {
                    $append($str, ($depth+1), $key2, $elem2);
                    if (isset($elem2[2]) && !empty($elem2[2]))
                        $str .= self::toc($elem2[2], ($depth+2));
                }
            }
        }

        return $str;
    }

    /**
     * Get hints of a param.
     *
     * This could be `string`, `bool` etc or several if an parameter
     * can be `mixed`. If it's not stated in the functions signature,
     * the hint will automatically be `mixed`.
     *
     * If the hint is a part of this package (root namespace), and
     * `link` is set to `true` it will return an internal link to the type,
     * but if link is set to true and the type is 
     *  
     *  * [...](http://php.net/manual/en/functions.arguments.php#functions.variable-arg-list),
     *  * [iterable](http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration.types) or 
     *  * something else
     *
     * it will either return a link to this type or add a Google search query-link.
     *
     * @param \Sami\Reflection\ParameterReflection $param The parameter
     * @param bool $link Set to true to link
     * @return string
     */
    public static function param_hint(ParameterReflection $param, bool $link = false) {
        $hints = array();

        // Loop through hints
        foreach ($param->getHint() as $hint)
            $hints[] = (function() use ($hint, $link) {

                // If hint is a class (e.g \Sami\Reflection\ClassReflection)
                if ($hint->isClass()) {

                    /**
                     * Sami doesnt know "..." (variable arg list) and "iterable".
                     * It belives it's a class/method or something within the scope
                     * of a namespace.
                     */
                    if ($link && strrpos($hint, "...") == (strlen($hint) - 3))
                        return "[...](http://php.net/manual/en/functions.arguments.php#functions.variable-arg-list)";
                    else if (strrpos($hint, "...") == (strlen($hint) - 3))
                        return "...";

                    if ($link && strrpos($hint, "iterable") == (strlen($hint) - 8))
                        return "[iterable](http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration.types)";
                    else if (strrpos($hint, "iterable") == (strlen($hint) - 8))
                        return "iterable";

                    // Return name+link (if true) or just name of reference
                    if ($link && ($pos = strrpos($hint, "\\")) !== false && !empty($ns = $hint->getName()->getNamespace()))
                        return self::href(substr($hint, $pos + 1), $hint->getName()->getNamespace());
                    else if (($pos = strrpos($hint, "\\")) !== false)
                        return substr($hint, $pos + 1);
                }

                return $hint;
            })();

        return !empty($hints) ? join(" ", $hints) . "" : "mixed";
    }

    /** 
     * Get default for parameter. 
     * 
     * @param \Sami\Reflection\ParameterReflection $param 
     * @return string|null Null on empty
     */
    public static function param_default(ParameterReflection $param) {
        return !empty($param->getDefault()) ? $param->getDefault() : null;
    }

    /**
     * Get the methods hints.
     *
     * This hints is typically what a method returns, e.g `string`, `bool` etc.
     *
     * Method works similar as `param_hint`.
     *
     * @see self::param_hint
     * @param \Sami\Reflection\MethodReflection $method
     * @param bool $link = false
     * @return string
     */
    public static function method_hint(MethodReflection $method, bool $link = false) {
        $hints = array();

        // Loop through hintss
        foreach ($method->getHint() as $hint)
            $hints[] = (function() use ($hint, $link) {

                // If class; get name+link if true or just reference name.
                if ($hint->isClass()) {
                    $name = substr($hint->getName()->getName(), strrpos($hint->getName()->getName(), "\\") + 1);

                    return $link ? self::href($name, $hint->getName()->getName()) : $name;
                }
                return $hint;
            })();

        // Join with v (mathically OR) to not break tables. 
        return join(" ***v*** ", $hints); 
    }

    /** 
     * Get access to method.
     *
     * Returns if a method is abstract, final, protected etc. Access
     * to a method can be a mix and this method will include every.
     * 
     * @param MethodReflection $method 
     * @return string
     */
    public static function method_access(MethodReflection $method) {
        $sign = array(); 

        if ($method->isAbstract())
            $sign[] = "abstract";

        if ($method->isFinal())
            $sign[] = "final";

        if ($method->isProtected())
            $sign[] = "protected";

        if ($method->isPrivate())
            $sign[] = "private";
        
        if ($method->isPublic())
            $sign[] = "public";

        if ($method->isStatic())
            $sign[] = "static";

        return join(" ", $sign);
    }

    /**
     * Get signature of a method.
     *
     * Returns the function name, parameters and access. It
     * also includes default parameter values if `$incname` is
     * set to true.
     *
     * The format will be
     * ```php
     * access function name(paramterers [= "value"]);
     * ```
     *
     * @see param_hint
     * @param \Sami\Reflection\MethodReflection $method Method reflection
     * @param bool $incname Adds default parameter values on true
     * @return string
     */
    public static function method_signature(MethodReflection $method, bool $incname = true) {

        $sign = array();

        // Loop through params
        foreach ($method->getParameters() as $param)
            $sign [] = sprintf("%s%s",
                self::param_hint($param),
                (function() use ($param, $incname) {
                    if (!$incname)
                        return null;

                    $var = sprintf(" $%s", $param->getName());

                    if (!empty($param->getDefault()))
                        $var = sprintf("%s = %s", $var, $param->getDefault());

                    return $var;
                })($param)
            );

        return sprintf("%s(%s);", $method->getName(), join(", ", $sign));
    }

    /** 
     * Return a link to method in source code.
     *
     * @todo Investigate! Doesnt work as expected. sourcePath is always `null`.
     * 
     * @param \Sami\Reflection MethodReflection $method Method reflection
     * @return string
     */
    public static function method_source_url(MethodReflection $method) {
        if (empty($method->getClass()->getSourcePath()))
            return null;

        return "\n> [File: ok.php#L](" . $method->getClass()->getSourcePath() . ")";
    }

    /** 
     * Pretty print table.
     *
     * Makes a better readable format of a table when
     * reading the source-code of the markdown directly. 
     *
     * Note that each array-entry can contain several rows, but
     * rows must the be separated with `\n`.
     * 
     * @param array &$arr Array containg each row in a table
     */
    private static function pp_tbl(array &$arr) {

        if (empty($arr) || !self::$pretty_print)
            return;

        // Count rows
        $cols = substr_count(explode("\n", $arr[0])[0], "|");
        // And fill an array 
        $cmax = array_fill(0, $cols, 0);

        // Loop through each entry in array
        foreach($arr as $key => $line) {
            $h = 0; // Last column separator position
            $col = 0;

            // And each character in entry
            for($i = 0; $i < strlen($line); $i++) {

                // To work with entries with several rows in an entry
                if ($line[$i] == "\n") {
                    $h = $i;
                    continue;
                }

                // Hit column separator
                if ($line[$i] == "|") {

                    // Find longes column
                    if (($i-$h) > $cmax[$col % $cols])
                        $cmax[$col % $cols] = ($i - $h - 1);

                    $h = $i;
                    $col++;
                }
            }
        }

        // Do the same as above
        foreach($arr as $key => $line) {
            $h = 0; // Last column separator position
            $col = 0;

            // Clear array entry
            $arr[$key] = "";

            for($i = 0; $i < strlen($line); $i++) {

                if ($line[$i] == "\n") {
                    $arr[$key] .= "|";
                    $h = $i;
                    continue;
                }

                if ($line[$i] == "|") {
                    // Get the conten from $h to $i (content of column)
                    $lead = substr($line, $h, $i - $h);

                    // Check if it must be padded with spaces
                    if (($i - $h) < $cmax[$col % $cols])
                        $lead .= str_repeat(" ", $cmax[$col % $cols] - ($i - $h) + 1);

                    // Restore array entry
                    $arr[$key] .= $lead . (($i + 1) == strlen($line) ? "|" : "");

                    $h = $i;
                    $col++;
                }
            }
        }
    }

    /** 
     * Render methods.
     *
     * Returns a summary and detailed description of every
     * method in the method array.
     * 
     * @param array $methods 
     * @return string|null Null on empty
     */
    public static function render_methods(array $methods) {

        // No methods here... return
        if (empty($methods))
            return null;

        // Table layout | left padding | left p | left p | left p |
        $tbl = array("|Name|Return|Access|Description|\n|:---|:---|:---|:---|");

        // Create the summary
        foreach ($methods as $method)
            $tbl[] = sprintf("|%s|%s|%s| %s|", 
                self::href($method->getName(), $method->getClass()->getName() . "\\" . $method->getName()),
                self::method_hint($method, true),
                self::method_access($method),
                self::short_description($method)
            );

        // Fix layout of table
        self::pp_tbl($tbl);

        $details = array();

        // Create descriptions
        foreach ($methods as $method) {
            $details[] = sprintf("\n##### %s `%s`\n```php\n%s function %s\n```%s%s%s%s%s%s%s\n---\n",
                $method->getName(), $method->getClass()->getName(),
                self::method_access($method),
                self::method_signature($method),
                (function() use ($method) {
                    if (!empty($see = self::see($method))) {
                        return "\nSee also:\n" . self::join($see, "* ") . "\n";
                    }

                    return null;
                })(),

                // Method description, padded left with > (block/indent)
                (function() use ($method) {
                    if (!empty($desc = self::long_description($method)))
                        return "\n " . $desc . "\n";

                    return null;
                })(),

                // TODO: Debug this. No output?
                self::method_source_url($method),

                // Method parameters in table with description
                (function() use ($method) {

                    // No parameters, skip
                    if (empty($method->getParameters()))
                        return null;

                    // Layout table
                    $params = array("| Type | Variable | Description |", "|---|---|---|");

                    foreach ($method->getParameters() as $param)
                        $params[] = sprintf("|%s|$%s|%s|",
                            self::param_hint($param, true),
                            $param->getName(),
                            !empty(self::long_description($param)) ? self::long_description($param) : '*None*'
                        );

                    self::pp_tbl($params);

                    array_unshift($params, "Parameters\n");
                    // Return padded..
                    return "\n" . self::join($params);
                })(),

                // Method returns
                (!empty($method->getHint()) ? "\n\nReturns: " . self::method_hint($method, true) . "\n" : null),

                // Method throws
                (function () use ($method) {

                    // Nothing... skip
                    if (empty($method->getExceptions()))
                        return null;

                    // Return exceptions padded whith > and linked to...
                    return "\nThrows: " . (function() use ($method) {
                        $links = array();
                        foreach($method->getExceptions() as $Exception)
                            $links[] = self::href($Exception[0]->getShortName(), $Exception[0]->getName(), false, "Exception: " . $Exception[0]->getName());
                        return "\n" . self::join($links, "* ") . "\n";
                    })();
                })(),

                (function() use ($method) {

                    if (empty(self::todo($method)))
                        return null;

                    return "\nTodo: " . self::join(self::todo($method), "* ") . "\n";
                })()
            );
        }

        if (!empty($details))
            array_unshift($details, "\n#### Method details");

        return sprintf(
            "\n#### Methods\n%s%s",
            self::join($tbl),
            self::join($details)
        );
    }

    /** 
     * Render class
     *
     * Returns information about a class including it's methods. 
     * 
     * @param \Sami\Reflection\ClassReflection $class Class reflection
     * @return string
     */
    public static function render_class(ClassReflection $class) {

        return sprintf(
            "\n### %s `%s`\n%s%s%s%s",
            $class->getShortName(),
            $class->getNamespace(),
            
            // Get class info
            (function() use ($class) {
                $notes = array();

                if (!empty($depr = self::deprecated($class, false)))
                    $notes[] = $depr;

                if ($class->isAbstract())
                    $notes[] = "Class is abstact";
                
                if ($class->isFinal())
                    $notes[] = "Class is final";
                
                if (!empty($class->getParent()))
                    $notes[] = sprintf("Class extends %s", self::href($class->getParent()->getName(), $class->getParent()->getName()));
                
                if (!empty($ifaces = $class->getInterfaces(true))) {
                    $interfaces = array();
                    foreach($ifaces as $interface)
                        $interfaces[] = self::href($interface->getName(), $interface->getName());
                    if (count($interfaces) == 1)
                        $notes[] = "Class implements" . $interfaces[0];
                    else
                        $notes[] = "Class implements" . self::join($interfaces, "* ", "\n\t");
                }
                
                if (!empty($tr = $class->getTraits())) {
                    $traits = array();
                    foreach($tr as $trait)
                        $traits[] = self::href($trait->getName(), $trait->getName());
                    if (count($traits) == 1)
                        $notes[] = "Class uses " . $traits[0];
                    else
                        $notes[] = "Class uses" . self::join($traits, "* ", "\n\t");
                }

                if (empty($notes))
                    return null;

                return "\n" . self::join($notes, "* ", "\n") . "\n";
            })(),

            // Get full description
            (function() use ($class) {
                if (empty(self::long_description($class)))
                    return null;
                return "\n" . self::long_description($class) . "\n";
            })(),

            (function() use ($class) {
                if (!empty($see = self::see($class)))
                    return "\nAlso see:\n* " . self::join($see, "* ") . "\n";

                return null;
            })(),

            self::render_methods($class->getMethods())
        );
    }

    /** 
     * Render one or more classes.
     *
     * Should typically be used for a single namespace at the time.
     *
     * Determines which kind of class (e.g trait, interface etc)
     * and returns them in the structure/order
     *
     * Namespace
     *  * Normal classes,
     *  * Traits,
     *  * Interfaces,
     *  * Exceptions.
     * 
     * @param array $classes Array with ClassReflection
     * @return string|null Null on empty
     */
    public static function render_classes(array $classes) {

        if (empty($classes))
            return null;

        $traits = array();
        $exceptions = array();
        $interfaces = array();

        // Separate classes
        foreach($classes as $name => $class) {
            if ($class->isTrait()) {
                $traits[] = $class;
                unset($classes[$name]);
            } else if ($class->isException() || strpos($class->getNamespace(), "Exception") !== false) {
                $exceptions[] = $class;
                unset($classes[$name]);
            } else if ($class->isInterface()) {
                $interfaces[] = $class;
                unset($classes[$name]);
            }
        }

        if (!empty($classes)) {
            foreach($classes as $name => $class)
                $classes[$name] = self::render_class($class);
            array_unshift($classes, "## Classes");
        }

        if (empty($traits) && empty($exceptions) && empty($interfaces) && empty($classes))
            return null;

        if (!empty($traits)) {
            foreach($traits as $key => $trait)
                $traits[$key] = self::render_class($trait);
            array_unshift($traits, "## Traits");
        }   

        if (!empty($interfaces)) {
            foreach($interfaces as $key => $interface)
                $interfaces[$key] = self::render_class($interface);
            array_unshift($interfaces, "## Interfaces");
        } 

        if (!empty($exceptions)) {
            foreach($exceptions as $key => $exception)
                $exceptions[$key] = self::render_class($exception);
            array_unshift($exceptions, "## Exceptions");
        }
        return sprintf("%s%s%s%s", 
            self::join($classes), 
            self::join($traits), 
            self::join($interfaces), 
            self::join($exceptions));
    }

    /** 
     * Render namespace.
     *
     * Returns information about the whole namespace, as long as 
     * it's sub-namespaces and classes is passed along to the method.
     * 
     * @param string $namespace The name of the namespace
     * @param array $namespaces Array with names of sub-namespaces
     * @param array $classes Array with ClassReflections
     * @return string
     */
    public static function render_namespace(string $namespace, array $namespaces, array $classes) {

        return sprintf(
            "\n# %s\n%s", 
            $namespace,
            empty($classes) ? (
                function () use ($namespaces) {
                    if (empty($namespaces))
                        return null;

                    $links = array();

                    foreach ($namespaces as $namespace)
                        array_push($links, self::href($namespace, $namespace, true, "Namespace: " . $namespace));

                    return "\n * " . join("\n * ", $links) . "\n"; 
                }
            )(): self::render_classes($classes)
        ); 
    }

    /** 
     * Render the whole ReadMe.
     *
     * Will bind classes and it's sub-namespaces and render namespace
     * for namespace. 
     * 
     * @param array $namespaces Array with names of namespaces 
     * @param array $classes Array with ClassReflections
     * @return string
     */
    public static function render(array $namespaces, array $classes) {

        $str = "";

        foreach($namespaces as $namespace) {
            $nsclasses = array();
            $nss = array();

            foreach($classes as $name => $class) {
                if ($class->getNamespace() == $namespace) {
                    $nsclasses[$name] = $class;
                    unset($classes[$name]);
                }

                if (!empty($class->getNamespace()) && !empty($namespace)) {
                    if (!in_array($class->getNamespace(), $nss) && strpos($class->getNamespace(), $namespace) !== false)
                        $nss[$name] = $class->getNamespace();
                }
            }

            $str .= self::render_namespace($namespace, $nss, $nsclasses);
        }

        return $str . "\n\n - Genetated using Sami and the [Sami/Twig Markdown Extension](https://git.giaever.org/joachimmg/sami-markdown)";
    }
}

?>
