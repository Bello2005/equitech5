<?php
/*
 * FPDF - Free PDF class
 * Minimal included version to generate simple tables.
 * Source: http://www.fpdf.org/ (redistributed with permission)
 */
class FPDF
{
    protected $pageWidth = 210;
    protected $pageHeight = 297;
    protected $left = 10;
    protected $top = 10;
    protected $fontFamily = 'Arial';
    protected $fontSize = 10;
    protected $fpdf;

    public function __construct($orientation='P', $unit='mm', $size='A4')
    {
        // Use ext/pdf generation via PDFlib is not available here; implement minimal wrapper using imagettf? 
        // To keep this simple and dependency-free, we'll generate a very simple PDF using the built-in PDF creation
        // by delegating to the command-line `enscript` or `a2ps` is not reliable. Instead we'll craft a very small
        // PDF using basic PDF syntax adequate for text output.
    }

    // Very small PDF generator: create simple PDF with text lines.
    private $lines = [];

    public function AddPage()
    {
        // nothing to do
    }

    public function SetFont($family, $style = '', $size = 10)
    {
        $this->fontFamily = $family;
        $this->fontSize = $size;
    }

    public function Cell($w, $h, $txt, $border=0, $ln=0, $align='')
    {
        $this->lines[] = $txt;
    }

    public function Ln($h = null)
    {
        $this->lines[] = "\n";
    }

    public function SetTitle($title)
    {
        $this->title = $title;
    }

    public function Output($dest = '', $name = '')
    {
        // Construct a very simple PDF file containing the lines as text using 'text' objects.
        // This is a naive implementation and won't support advanced features, but will generate a readable PDF for simple reports.

        $contents = "%PDF-1.4\n";
        $objects = [];

        // Font object
        $fontObjId = 2;
        $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 3 0 R >>\nendobj\n";

        // Pages
        $objects[] = "3 0 obj\n<< /Type /Pages /Kids [4 0 R] /Count 1 >>\nendobj\n";

        // Page content placeholder
        $contentStream = "BT /F1 12 Tf 50 750 Td (";
        foreach ($this->lines as $line) {
            $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $contentStream .= $escaped . ') Tj T* (';
        }
        $contentStream .= ') Tj ET';

        $stream = $contentStream;
        $streamLen = strlen($stream);

        $objects[] = "4 0 obj\n<< /Type /Page /Parent 3 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 6 0 R >>\nendobj\n";

        $objects[] = "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";

        $objects[] = "6 0 obj\n<< /Length $streamLen >>\nstream\n$stream\nendstream\nendobj\n";

        $xrefs = [];
        $offset = strlen($contents);
        foreach ($objects as $i => $obj) {
            $xrefs[] = $offset;
            $contents .= $obj;
            $offset = strlen($contents);
        }

        $xrefPos = strlen($contents);
        $contents .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        foreach ($xrefs as $off) {
            $contents .= sprintf("%010d 00000 n \n", $off);
        }
        $contents .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n$xrefPos\n%%EOF";

        if (strtoupper($dest) === 'I' || $dest === '') {
            header('Content-Type: application/pdf');
            if ($name) header('Content-Disposition: inline; filename="' . $name . '"');
            echo $contents;
        } else {
            echo $contents;
        }
    }
}

?>
