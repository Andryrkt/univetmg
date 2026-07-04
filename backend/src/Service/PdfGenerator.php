<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class PdfGenerator
{
    public function __construct(
        private Environment $twig
    ) {
    }

    /**
     * Génère un PDF à partir d'un template Twig
     */
    public function generatePdfFromTemplate(string $template, array $context = [], array $pdfOptions = []): string
    {
        // Render HTML from Twig template
        $html = $this->twig->render($template, $context);

        // Configure Dompdf
        $options = new Options();
        // Change default font to a lighter one like Helvetica to reduce file size
        $options->set('defaultFont', 'Helvetica');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        // Initialize Dompdf
        $dompdf = new Dompdf($options);
        
        // Load HTML content
        $dompdf->loadHtml($html);
        
        // Set paper size and orientation from options, with defaults
        $paperSize = $pdfOptions['paper_size'] ?? 'A4';
        $paperOrientation = $pdfOptions['paper_orientation'] ?? 'portrait';
        $dompdf->setPaper($paperSize, $paperOrientation);
        
        // Render PDF (first pass to get total pages)
        $dompdf->render();
        
        // Return PDF as string
        return $dompdf->output();
    }

    /**
     * Génère un PDF et le retourne pour téléchargement
     */
    public function generatePdfResponse(string $template, array $context = [], string $filename = 'document.pdf', array $pdfOptions = []): \Symfony\Component\HttpFoundation\Response
    {
        $pdfContent = $this->generatePdfFromTemplate($template, $context, $pdfOptions);

        $response = new \Symfony\Component\HttpFoundation\Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline; filename="' . $filename . '"');

        return $response;
    }
}
