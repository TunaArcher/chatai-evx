<?php

namespace App\Libraries;

use TCPDF;
use app\Models\DocumentSetUpModel;

class pdfKleanXDay extends TCPDF
{

    //Page header
    public function Header()
    {
        $this->LpWebsiteModel = new \App\Models\LpWebsiteModel();
        $data['website'] = $this->LpWebsiteModel->getLpWebsiteByID(1);
        // Logo
        $image_file = getenv('CDN_IMG').'/uploads/img/' . $data['website']->logo;
        // $image_file = 'https://stock.psnkp.co/assets/img/up2cars_dark.jpg';
        /**
         * width : 50
         */
        $this->Image($image_file, 18, 10, 40);
        // Set font
        // $this->SetFont('thsarabun', 'B', 11);
        // $this->SetX(70);
        // $this->Cell(0, 2, 'sobatcoding.com', 0, 1, '', 0, '', 0);
        // Title
        $this->DocumentSetUpModel = new \App\Models\DocumentSetUpModel();
        $setup = $this->DocumentSetUpModel->getDocumentSetUpAll();

        try {
            $backup_number = '';
            if ($setup[0]->set_up_backup_number != '') {
                $backup_number = ' , ' . $setup[0]->set_up_backup_number;
            }

            $this->SetFont('boonlight', '', 12);
            $this->SetX(70);
            $this->Cell(0, 2, 'ชื่อบริษัท  ' . $setup[0]->set_up_name, 0, 1, '', 0, '', 0);
            $this->SetX(70);
            $this->Cell(0, 2, 'ที่อยู่  ' . $setup[0]->set_up_address, 0, 1, '', 0, '', 0);
            $this->SetX(70);
            $this->Cell(0, 2, 'เบอร์โทรศัพท์  ' . $setup[0]->set_up_phone_number . $backup_number . '  เบอร์โทรศัพท์มือถือ  ' . $setup[0]->set_up_taxpayer_number, 0, 1, '', 0, '', 0);
        } catch (\Exception $e) {
            echo $e->getMessage() . ' ' . $e->getLine();
        }
        $this->SetFont('boonlight', 'B', 18);
        $this->SetX(90);
        $this->Cell(0, 15, 'ใบเสร็จรับเงิน ', 0, 1, '', 0, '', 0);

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
