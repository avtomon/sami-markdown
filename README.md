
Sami Markdown Extension
===

# Table of content    
1. [Markdown](#markdown "Markdown")
	1. [SamiTwigExtension](#samitwigextension-markdown "Markdown\SamiTwigExtension") An extension than builds a single ReadMe markdownfile out of your code.


---
Documentation
---

# Markdown

## Classes

### SamiTwigExtension `Markdown`


* Class extends [Twig_Extension](https://www.google.no/search?q=Twig_Extension)

An extension than builds a single ReadMe markdown
file out of your code.

By now, most is done "behind the scene" and the template
is very minimal. This means that it's not much you can do to
change any of the layout etc, without editing the actual code/class,
but you may want to change template structure by calling
the methods "render_classes", "render_class" etc, but be aware
that these methods does not remove whitespaces and takes care of
any format issues your template will get.


This is in lack of a a single-file-documentation, for a project,
and since the markdown language is very strict on format -
(indentation, signs etc.) - using the twig template was a hassle,
whitout ending with several "twig-files" that was unreadable (no
indentation etc).

Get [Sami](https://github.com/FriendsOfPHP/Sami/) and fork this
repo

```bash
git clone giaever@git.giaever.org:joachimmg/sami-markdown.git
```

Include the `SamiTwigExtension.php` into your Sami configuration-file
and add it to twig. See `example.conf.php` or

 * set template: `"template" => "markdown"`
 * add extension:

```php
$sami["twig"]->addExtension(new Markdown\SamiTwigExtension());
```

#### Methods

|Name|Return|Access|Description|
|:---|:---|:---|:---|
|[__construct](#__construct-markdownsamitwigextension)||public| |
|[getFunctions](#getfunctions-markdownsamitwigextension)||public| Set the functions available for the template engine.|
|[short_description](#short_description-markdownsamitwigextension)|string ***v*** null|public static| Returns the short description of a reflection.|
|[long_description](#long_description-markdownsamitwigextension)|string ***v*** null|public static| Returns a long description (both short and long) of a reflection.|
|[deprecated](#deprecated-markdownsamitwigextension)|string ***v*** null|public static| Returnes a deprecated label if class, method etc is.|
|[todo](#todo-markdownsamitwigextension)|array ***v*** null|public static| Returns todo-tag for Reflection.|
|[see](#see-markdownsamitwigextension)|array ***v*** null|public static| Returns see-tag for Reflection.|
|[href](#href-markdownsamitwigextension)|string|public static| Returnes a markdown link.|
|[toc](#toc-markdownsamitwigextension)|string|public static| Tabel of contents|
|[param_hint](#param_hint-markdownsamitwigextension)|string|public static| Get hints of a param.|
|[param_default](#param_default-markdownsamitwigextension)|string ***v*** null|public static| Get default for parameter.|
|[method_hint](#method_hint-markdownsamitwigextension)|string|public static| Get the methods hints.|
|[method_access](#method_access-markdownsamitwigextension)|string|public static| Get access to method.|
|[method_signature](#method_signature-markdownsamitwigextension)|string|public static| Get signature of a method.|
|[method_source_url](#method_source_url-markdownsamitwigextension)|string|public static| Return a link to method in source code.|
|[render_methods](#render_methods-markdownsamitwigextension)|string ***v*** null|public static| Render methods.|
|[render_class](#render_class-markdownsamitwigextension)|string|public static| Render class|
|[render_classes](#render_classes-markdownsamitwigextension)|string ***v*** null|public static| Render one or more classes.|
|[render_namespace](#render_namespace-markdownsamitwigextension)|string|public static| Render namespace.|
|[render](#render-markdownsamitwigextension)|string|public static| Render the whole ReadMe.|

#### Method details

##### __construct `Markdown\SamiTwigExtension`
```php
public function __construct(bool $pretty_print = false);
```

Parameters

| Type | Variable | Description |
|---|---|---|
|bool|$pretty_print|*None*|
---


##### getFunctions `Markdown\SamiTwigExtension`
```php
public function getFunctions();
```
 Set the functions available for the template engine.

---


##### short_description `Markdown\SamiTwigExtension`
```php
public static function short_description(Reflection $refl, bool $oneliner = true, int $max = -1);
```
 Returns the short description of a reflection.


Parameters

| Type | Variable | Description |
|---|---|---|
|Reflection|$refl|Reflection to return description of|
|bool|$oneliner|Removes every newline and tabulation.|
|int|$max|Maximum of letters|

Returns: string ***v*** null

---


##### long_description `Markdown\SamiTwigExtension`
```php
public static function long_description(Reflection $refl, bool $oneliner = false);
```
 Returns a long description (both short and long) of a reflection.


Parameters

| Type | Variable | Description |
|---|---|---|
|Reflection|$refl|Reflection to return description of|
|bool|$oneliner|Removes every newline and tabulation|

Returns: string ***v*** null

---


##### deprecated `Markdown\SamiTwigExtension`
```php
public static function deprecated(Reflection $refl, bool $notice = true);
```
 Returnes a deprecated label if class, method etc is.

If `$notice` is false, it will include the deprecated
note - if given in the documentation.


Parameters

| Type | Variable | Description |
|---|---|---|
|Reflection|$refl|Reflection|
|bool|$notice|Just as notice|

Returns: string ***v*** null

---


##### todo `Markdown\SamiTwigExtension`
```php
public static function todo(Reflection $refl);
```
 Returns todo-tag for Reflection.


Parameters

| Type | Variable | Description |
|---|---|---|
|Reflection|$refl|Reflection to get todo tag from.|

Returns: array ***v*** null

---


##### see `Markdown\SamiTwigExtension`
```php
public static function see(Reflection $refl);
```
 Returns see-tag for Reflection.


Parameters

| Type | Variable | Description |
|---|---|---|
|Reflection|$refl|Reflection to get see-tag from.|

Returns: array ***v*** null

---


##### href `Markdown\SamiTwigExtension`
```php
public static function href(string $ltxt, string $lurl, bool $namespace = false, string $desc = null);
```
 Returnes a markdown link.

To match the markdown template classes is linked to by
`#classname-namespace`, and methods `#method-namespace\classname`
and namespaces is linked to by `#namespace`, `$namespace` must be set
to true when linking to it.


Parameters

| Type | Variable | Description |
|---|---|---|
|string|$ltxt|The link text|
|string|$lurl|The link destination|
|bool|$namespace|True when linking to a namespace|
|string|$desc|Link title (like the html title/hover-tag)|

Returns: string

---


##### toc `Markdown\SamiTwigExtension`
```php
public static function toc(array $tree, int $depth);
```
 Tabel of contents

Generates a table of contentes out of the whole
project tree.


Parameters

| Type | Variable | Description |
|---|---|---|
|array|$tree|The tree array passed from twig|
|int|$depth|depth Initially this should be 0|

Returns: string

---


##### param_hint `Markdown\SamiTwigExtension`
```php
public static function param_hint(ParameterReflection $param, bool $link = false);
```
 Get hints of a param.

This could be `string`, `bool` etc or several if an parameter
can be `mixed`. If it's not stated in the functions signature,
the hint will automatically be `mixed`.

If the hint is a part of this package (root namespace), and
`link` is set to `true` it will return an internal link to the type,
but if link is set to true and the type is

 * [...](http://php.net/manual/en/functions.arguments.php#functions.variable-arg-list),
 * [iterable](http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration.types) or
 * something else

it will either return a link to this type or add a Google search query-link.


Parameters

| Type | Variable | Description |
|---|---|---|
|ParameterReflection|$param|The parameter|
|bool|$link|Set to true to link|

Returns: string

---


##### param_default `Markdown\SamiTwigExtension`
```php
public static function param_default(ParameterReflection $param);
```
 Get default for parameter.


Parameters

| Type | Variable | Description |
|---|---|---|
|ParameterReflection|$param|*None*|

Returns: string ***v*** null

---


##### method_hint `Markdown\SamiTwigExtension`
```php
public static function method_hint(MethodReflection $method, bool $link = false);
```
 Get the methods hints.

This hints is typically what a method returns, e.g `string`, `bool` etc.

Method works similar as `param_hint`.


Parameters

| Type | Variable | Description |
|---|---|---|
|MethodReflection|$method|*None*|
|bool|$link|= false|

Returns: string

---


##### method_access `Markdown\SamiTwigExtension`
```php
public static function method_access(MethodReflection $method);
```
 Get access to method.

Returns if a method is abstract, final, protected etc. Access
to a method can be a mix and this method will include every.


Parameters

| Type | Variable | Description |
|---|---|---|
|MethodReflection|$method|*None*|

Returns: string

---


##### method_signature `Markdown\SamiTwigExtension`
```php
public static function method_signature(MethodReflection $method, bool $incname = true);
```
 Get signature of a method.

Returns the function name, parameters and access. It
also includes default parameter values if `$incname` is
set to true.

The format will be
```php
access function name(paramterers [= "value"]);
```


Parameters

| Type | Variable | Description |
|---|---|---|
|MethodReflection|$method|Method reflection|
|bool|$incname|Adds default parameter values on true|

Returns: string

---


##### method_source_url `Markdown\SamiTwigExtension`
```php
public static function method_source_url(MethodReflection $method);
```
 Return a link to method in source code.


Parameters

| Type | Variable | Description |
|---|---|---|
|MethodReflection|$method|MethodReflection $method Method reflection|

Returns: string

---


##### render_methods `Markdown\SamiTwigExtension`
```php
public static function render_methods(array $methods);
```
 Render methods.

Returns a summary and detailed description of every
method in the method array.


Parameters

| Type | Variable | Description |
|---|---|---|
|array|$methods|*None*|

Returns: string ***v*** null

---


##### render_class `Markdown\SamiTwigExtension`
```php
public static function render_class(ClassReflection $class);
```
 Render class

Returns information about a class including it's methods.


Parameters

| Type | Variable | Description |
|---|---|---|
|ClassReflection|$class|Class reflection|

Returns: string

---


##### render_classes `Markdown\SamiTwigExtension`
```php
public static function render_classes(array $classes);
```
 Render one or more classes.

Should typically be used for a single namespace at the time.

Determines which kind of class (e.g trait, interface etc)
and returns them in the structure/order

Namespace
 * Normal classes,
 * Traits,
 * Interfaces,
 * Exceptions.


Parameters

| Type | Variable | Description |
|---|---|---|
|array|$classes|Array with ClassReflection|

Returns: string ***v*** null

---


##### render_namespace `Markdown\SamiTwigExtension`
```php
public static function render_namespace(string $namespace, array $namespaces, array $classes);
```
 Render namespace.

Returns information about the whole namespace, as long as
it's sub-namespaces and classes is passed along to the method.


Parameters

| Type | Variable | Description |
|---|---|---|
|string|$namespace|The name of the namespace|
|array|$namespaces|Array with names of sub-namespaces|
|array|$classes|Array with ClassReflections|

Returns: string

---


##### render `Markdown\SamiTwigExtension`
```php
public static function render(array $namespaces, array $classes);
```
 Render the whole ReadMe.

Will bind classes and it's sub-namespaces and render namespace
for namespace.


Parameters

| Type | Variable | Description |
|---|---|---|
|array|$namespaces|Array with names of namespaces|
|array|$classes|Array with ClassReflections|

Returns: string

---


 - Genetated using Sami and the [Sami/Twig Markdown Extension](https://git.giaever.org/joachimmg/sami-markdown)

