<?php

use IndipetaePdfBuilder_Logger as BuildLogger;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

class IndipetaePdfBuilder_Factory
{
    public static function getBuilder(): IndipetaePdfBuilder_Builder
    {
        _log("Building PDF builder", Zend_Log::INFO);

        return new IndipetaePdfBuilder_Builder(new BuildLogger(), self::buildTwig(), self::buildWKHTMLToPDF());
    }

    private static function buildWKHTMLToPDF(): \mikehaertl\wkhtmlto\Pdf
    {
        return new \mikehaertl\wkhtmlto\Pdf([
            'binary' => '/usr/local/bin/wkhtmltopdf',
            'encoding' => 'utf-8'
        ]);
    }

    /**
     * Build the Twig templating engine
     *
     * See https://twig.symfony.com/ for how to use Twig templates.
     *
     * @return Environment
     */
    private static function buildTwig(): Environment
    {
        $is_development = APPLICATION_ENV === 'development';

        $path_to_cache = $is_development ? false : __DIR__ . '/../../cache';
        $path_to_templates = __DIR__ . '/../../templates';

        $loader = new FilesystemLoader($path_to_templates);
        $twig = new Environment($loader, ['cache' => false, 'debug' => $is_development]);

        if ($is_development) {
            $twig->addExtension(new DebugExtension());
        }
        return $twig;
    }
}