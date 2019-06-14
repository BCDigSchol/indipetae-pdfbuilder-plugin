<?php

use mikehaertl\wkhtmlto\Pdf as WKHTMLToPDF;
use Twig\Environment as Twig;

require_once __DIR__ . '/../../vendor/autoload.php';

class IndipetaePDFBuilder_Builder
{
    /**
     * @var IndipetaePDFBuilder_Logger
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

    public function __construct(IndipetaePDFBuilder_Logger $logger, Twig $twig, WKHTMLToPDF $wkhtmltopdf)
    {
        $this->logger = $logger;
        $this->twig = $twig;
        $this->wkhtmltopdf = $wkhtmltopdf;
    }

    public function build(IndipetaePDFBuilder_Model_Letter $letter): void
    {
        $path_to_pdf = $this->buildPathToPDF($letter);

        // Remove the old PDF
        $letter->removePDF($this->filename_prefix);

        // Build a temporary PDF.
        $html = $this->buildHTML($letter);
        $this->htmlToPDF($html, $path_to_pdf);

        // Add it to the letter.
        $letter->addFile($path_to_pdf);

        // Delete the temp PDF.
        unlink($path_to_pdf);
    }

    protected function buildHTML(IndipetaePDFBuilder_Model_Letter $letter): string
    {
        $css_file = __DIR__ . '/../../pdf-style.css';
        $context = [
            'letter' => $letter,
            'css' => file_get_contents($css_file)
        ];
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

    protected function buildPathToPDF(IndipetaePDFBuilder_Model_Letter $letter): string
    {
        $call_number = $letter->callNumber()->getValues()[0] ?? 'transcription';
        preg_replace('/[ ,.]+/', '-', $call_number);
        return __DIR__ . "/../../{$this->filename_prefix}-$call_number.pdf";
    }
}