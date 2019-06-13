<?php

use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

require_once __DIR__ . '/vendor/autoload.php';

class IndipetaePDFBuilderPlugin extends Omeka_Plugin_AbstractPlugin
{
    public $_hooks = ['after_save_item'];

    protected $filename_prefix = 'indipetae-transcript';

    public function hookAfterSaveItem(array $args): void
    {
        _log('Building PDF: ' . var_export(array_keys($args), true), ZEND_LOG::DEBUG);
        $this->buildPDF($args['record'], $args['post']['Elements']);
    }

    protected function buildPDF(Item $letter, $post): void
    {
        $this->removeOldPDF($letter->getFiles());
        $html = $this->buildHTML($letter);
        $path_to_pdf = $this->htmlToPDF($html, $post);
        insert_files_for_item($letter, 'Filesystem', [$path_to_pdf]);
        _log("PDF created at $path_to_pdf", ZEND_LOG::INFO);
        unlink($path_to_pdf);
    }

    /**
     * @param File[] $files
     * @throws Omeka_Record_Exception
     */
    protected function removeOldPDF(array $files): void
    {
        _log('Removing old PDFs', Zend_Log::DEBUG);
        foreach ($files as $file) {
            if (strpos($file->original_filename, $this->filename_prefix) === 0) {
                $file->delete();
                _log("Removed {$file->original_filename}", Zend_Log::DEBUG);
            }
        }
    }

    protected function buildHTML(Item $letter): string
    {
        $css_file = __DIR__ . '/pdf-style.css';

        $builder = new IndipetaePDFBuilder_MetadataBuilder($letter);

        $metadata = [
            $builder->getField('Contributor', 'Transcribed by'),
            $builder->getField('Identifier', 'Call number'),
            $builder->getField('Date', 'Date'),
            $builder->getField('Coverage', 'From'),
            $builder->getField('Spatial Coverage', 'To'),
            $builder->getField('Creator', 'Sender'),
            $builder->getField('Replaces', 'Grade'),
            $builder->getField('Audience', 'Recipient'),
            $builder->getField('Publisher', 'Destination'),
            $builder->getField('Subject', 'Models/Saints/ Missionaries'),
            $builder->getField('Relation', 'Other names'),
            $builder->getField('Date Issued', 'Left for mission lands'),
            $builder->getField('Medium', 'Anterior desire'),
        ];

        $context = [
            'metadata' => $metadata,
            'title' => $builder->getField('Title', 'Title')->getValues()[0],
            'transcription' => $builder->getField('Description', 'Transcription')->getValues()[0],
            'transcription_back' => $builder->getField('Extent', 'Transcription — back')->getValues()[0],
            'css' => file_get_contents($css_file)
        ];

        $twig = $this->loadTwig();
        $html = $twig->render('IndipetaePDFTemplate.html.twig', $context);

        return $html;
    }

    protected function htmlToPDF(string $html, $post): string
    {
        _log("buildHTML: $html", Zend_Log::DEBUG);

        $full_path_to_pdf = $this->buildPathToPDF($post);

        $pdf = new \mikehaertl\wkhtmlto\Pdf($html);
        $pdf->setOptions([
            'binary' => '/usr/local/bin/wkhtmltopdf',
            'encoding' => 'utf-8'
        ]);

        if (!$pdf->saveAs($full_path_to_pdf)) {
            $error = $pdf->getError();
            _log("htmlToPDF error: $error", ZEND_LOG::ERR);
        }
        return $full_path_to_pdf;
    }

    protected function buildPathToPDF(array $post_args): string
    {
        $base_path = __DIR__;

        $call_number = $post_args[43][0]['text'] ?? 'transcript';
        $call_number = str_replace([' ', ',', '.'], '-', $call_number);
        $call_number = str_replace(['--'], '-', $call_number);

        return "$base_path/{$this->filename_prefix}-$call_number.pdf";
    }

    /**
     * @return Environment
     */
    protected function loadTwig(): Environment
    {
        $is_development = APPLICATION_ENV === 'development';

        $path_to_cache = $is_development ? false : __DIR__ . '/cache';
        $path_to_templates = __DIR__ . '/templates';
        $loader = new FilesystemLoader($path_to_templates);
        $twig = new Environment($loader, ['cache' => $path_to_cache, 'debug' => $is_development]);

        if ($is_development) {
            $twig->addExtension(new DebugExtension());
        }
        return $twig;
    }

    protected function metadata(Item $letter, string $field)
    {
        return metadata($letter, ['Dublin Core', $field], ['no_filter' => true]);
    }
}