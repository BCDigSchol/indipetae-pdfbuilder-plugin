<?php

use mikehaertl\wkhtmlto\Pdf as WKHTMLToPDF;
use Twig\Environment as Twig;

require_once __DIR__ . '/../../vendor/autoload.php';

class IndipetaePdfBuilder_Builder
{
    /**
     * @var IndipetaePdfBuilder_Logger
     */
    private $logger;

    /**
     * @var Twig
     */
    private $twig;

    /**
     * @var WKHTMLToPDF
     */
    private $wkhtmltopdf;

    private $filename_prefix = 'indipetae-transcription';

    public function __construct(IndipetaePdfBuilder_Logger $logger, Twig $twig, WKHTMLToPDF $wkhtmltopdf)
    {
        $this->logger = $logger;
        $this->twig = $twig;
        $this->wkhtmltopdf = $wkhtmltopdf;
    }

    public function build(IndipetaePdfBuilder_Model_Letter $letter): void
    {
        _log("Starting Builder::build() for {$letter->id()}");

        $path_to_pdf = $this->buildPathToPDF($letter);

        // Remove the old PDF
        $letter->removePDF('indipetae-transcript');

        _log("Removed old PDF");

        // Build a temporary PDF.
        $html = $this->buildHTML($letter);

        _log("Built PDF HTML");

        $this->htmlToPDF($html, $path_to_pdf);

        _log("Built new PDF");

        // Add it to the letter.
        $letter->addFile($path_to_pdf);

        _log("Added to letter");

        // Delete the temp PDF.
        unlink($path_to_pdf);
    }

    protected function buildHTML(IndipetaePdfBuilder_Model_Letter $letter): string
    {
        $css_file = __DIR__ . '/../../pdf-style.css';
        $context = [
            'letter' => $letter,
            'css' => file_get_contents($css_file)
        ];

        _log("Sending to template");

        return $this->twig->render('IndipetaePDFTemplate.html.twig', $context);
    }

    protected function htmlToPDF(string $html, string $path_to_pdf): string
    {
        $this->wkhtmltopdf->addPage($html);

        if (!$this->wkhtmltopdf->saveAs($path_to_pdf)) {
            $error = $this->wkhtmltopdf->getError();
            $this->logger->error("htmlToPDF error: $error");
        }

        return $path_to_pdf;
    }

    protected function buildPathToPDF(IndipetaePdfBuilder_Model_Letter $letter): string
    {
        $call_number = $letter->callNumber()->getValues()[0] ?? 'transcription';
        $call_number = str_replace([' ', ',', '/'], '-', $call_number);
        return __DIR__ . "/../../{$this->filename_prefix}-$call_number.pdf";
    }
}