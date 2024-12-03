<?php

namespace App\Libraries;

use TCPDF;
use app\Models\DocumentSetUpModel;

class pdfSellContractCash extends TCPDF
{

    //Page header
    public function Header()
    {
        $this->LpWebsiteModel = new \App\Models\LpWebsiteModel();
        $data['website'] = $this->LpWebsiteModel->getLpWebsiteByID(1);
        // Logo
        $image_file = getenv('CDN_IMG').'/uploads/img/' . $data['website']->logo;
        // $image_file = 'https://chomecarcenter.sgp1.cdn.digitaloceanspaces.com/uploads/img/1677675246_f1b73ff4c7a757364a77.jpeg';
        /**
         * width : 50
         */
        $this->Image($image_file, 10, 14, 30);
        // Set font
        // $this->SetFont('thsarabun', 'B', 11);
        // $this->SetX(70);
        // $this->Cell(0, 2, 'sobatcoding.com', 0, 1, '', 0, '', 0);
        // Title

        // QRCODE,H : QR-CODE Best error correction
        // $this->write2DBarcode('https://sobatcdoing.com', 'QRCODE,H', 0, 3, 20, 20, ['position' => 'R'], 'N');

        // $style = array('width' => 0.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
        // $this->Line(15, 25, 195, 25, $style);

    }

    // Page footer
    public function Footer()
    {
        // Position at 15 mm from bottom
        // $this->SetY(-15);
        // Set font
        // $this->SetFont('helvetica', 'I', 8);
        // Page number
        // $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
