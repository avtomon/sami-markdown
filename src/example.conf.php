<?php

/** 
 * Example configuration file for Sami;
 * @see Sami website for details on this
 */

use Sami\Sami;
use Sami\RemoteRepository\GitHubRemoteRepository;
use Sami\Version\GitVersionCollection;
use Symfony\Component\Finder\Finder;

// Import markdown
require "SamiTwigExtension.php";

$iterator = Finder::create()
    ->files()
    /**
     * For those who clone this into their project,
     * we'll exclude sami-markdown to be rendered.
     */
    ->exclude("sami-markdown")
    ->name('*.php')
    ->in($dir = './')
;

/*$versions = GitVersionCollection::create($dir)
    ->add('master', 'master branch')
    ;
 */
$s =  new Sami($iterator, array(
    'theme'                => 'markdown',
    'template_dirs'        => array( __DIR__ . '/'),
    //'versions'             => $versions,
    'title'                => 'Sami Markdown Extension',
    'build_dir'            => __DIR__.'/build/%version%',
    'cache_dir'            => __DIR__.'/cache/%version%',
));

// Add extension
$s["twig"]->addExtension(new Markdown\SamiTwigExtension());

return $s;

?>
