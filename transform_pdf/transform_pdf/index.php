<?php

use setasign\Fpdi\Fpdi;

require_once('library/FPDF/fpdf.php');
require_once('library/FPDI/src/autoload.php');
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

function init($file, $approvers, $extraData) {
    // --- Логи -----
    file_put_contents(__DIR__ . '/log.json', " new  ", FILE_APPEND);
    file_put_contents(__DIR__ . '/log.json', json_encode($file, JSON_UNESCAPED_UNICODE), FILE_APPEND);
    file_put_contents(__DIR__ . '/log.json', json_encode($approvers, JSON_UNESCAPED_UNICODE), FILE_APPEND);
    file_put_contents(__DIR__ . '/log.json', json_encode($extraData, JSON_UNESCAPED_UNICODE), FILE_APPEND);

    $pdf = new Fpdi(); 
    // подключаем шрифты
    // define('FPDF_FONTPATH',"fpdf/font/");
    $pdf->AddFont('Arial','','arial.php'); 
    $pdf->SetFont('Arial');
    $pdf->SetFontSize(10);

    $pageCount = $pdf->setSourceFile($file);
    
    $HEIGHT = $pdf->GetPageHeight();
    $WIDTH = $pdf->GetPageWidth();
    
    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $tplIdx = $pdf->importPage($pageNo);
        $pdf->AddPage();
        $pdf->useTemplate($tplIdx, 0, 0);
    }

    // ----- Данные для построения таблицы ------
    $header = array('Дата', 'ФИО');
    $dataTableInfo = getDataTable($approvers, $extraData);
    $data = $dataTableInfo["dataTable"];

    // ----- Длина правой колонки
    $nmax = $dataTableInfo["nmax"];
    $wcol = $nmax + 16; // колонка с отступом

    // ----- Отступ ----
    $pdf->SetAutoPageBreak(true, 0);   
    $ncol = count($data) + count($extraData);
    $h = $HEIGHT - (2 + $ncol)*7;
    $pdf->SetY($h);
    $w = $WIDTH - ($wcol+25)-10;
    $pdf->SetLeftMargin($w);
    // -----------------------

    renderTable($pdf, $header, $data, $extraData, $wcol);
    // ----- Передать содержимое файла --
	return $pdf->Output('S');

}


// Предусмотреть задание ширины таблицы в зависимости от самого длинного значения правого столбца
function getDataTable($approvers, $extraData) {
    $tmp = array();
    $dataTable = array();
    foreach ($approvers[0] as $key => $value) {
        $fullName = getFullName($key);
        $dataTable[] = array($value, $fullName);
        $tmp[] = strlen($fullName);
    }

    foreach ($extraData as $key => $row) {
        foreach($row as $col) {
            $tmp[] = strlen($col);
        }
    }
    // Максимальная длина для правой колонки
    $nmax = max($tmp);
    $res = array("dataTable" => $dataTable, "nmax" => $nmax);

    return $res;
}


// --TABLE --
function renderTable($pdf, $header, $data, $extraData, $wcol)
    { 
        $pdf->SetDrawColor(0,0,255);
        $pdf->SetLineWidth(0.3);
        
        // ExtraData: object and organization
        foreach($extraData as $row) {
            foreach ($row as $col => $val) {
                $text = iconv('utf-8','windows-1251',$val);
                if ((int) $col === 0)
                $pdf->Cell(25,6, trim($text), 1, 0, 'L');
                else 
                $pdf->Cell($wcol,6, trim($text), 1, 0, 'L');
            }
            $pdf->Ln();
        }
        // Header Main
        $headerTitle = iconv('utf-8', 'windows-1251',"СОГЛАСОВАНО");
        $pdf->Cell(25+$wcol, 7, $headerTitle, 1, 0, 'C');
        $pdf->Ln();

        // Header date and fio
        foreach($header as $col => $val) {
            $text = iconv('utf-8','windows-1251',$val);
            if ((int) $col === 0)
                $pdf->Cell(25,6,$text,1, 0, 'C');
            else 
                $pdf->Cell($wcol,6,$text,1, 0, 'C');
        }
        $pdf->Ln();
        // Data date and fio
        foreach($data as $row)
        {
            $tmp = 1;
            foreach($row as $col => $val) {
                $text = iconv('utf-8','windows-1251',$val);
                if ((int) $col === 0)
                    $pdf->Cell(25,6, trim($text),1, 0, 'L');
                else 
                    $pdf->Cell($wcol,6, trim($text), 1, 0, 'L');
            }
            $pdf->Ln();
        }
    }


function delEmail($strFIO){
    $newStr = preg_replace('/\((.*?)\)/', "", $strFIO);
    return $newStr; 
}


function getDateFormat($date){
    $newDate = preg_replace('/00:00:00/', "", $date);
    $newDate = trim($newDate);
    return $newDate; 
}

function getFullName($id){
    $rsUser = CUser::GetByID($id);
    $arUser = $rsUser->Fetch();
    $fullName = $arUser["NAME"] . ' ' . $arUser["LAST_NAME"];
    return $fullName;
}
?>



