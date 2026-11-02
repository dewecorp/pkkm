<?php
// Simple XLSX Reader tailored for importing PKKM sheets
class PkkmExcelReader {
    private $zip;
    private $strings = [];
    private $sheets = [];

    public function __construct($filename) {
        $this->zip = new ZipArchive();
        if ($this->zip->open($filename) !== TRUE) {
            throw new Exception("Cannot open $filename");
        }
        $this->loadStrings();
        $this->loadSheets();
    }

    private function loadStrings() {
        $xmlStr = $this->zip->getFromName('xl/sharedStrings.xml');
        if ($xmlStr) {
            $xml = simplexml_load_string($xmlStr);
            foreach ($xml->si as $si) {
                if (isset($si->t)) {
                    $this->strings[] = (string)$si->t;
                } elseif (isset($si->r)) {
                    $t = '';
                    foreach ($si->r as $r) {
                        $t .= (string)$r->t;
                    }
                    $this->strings[] = $t;
                } else {
                    $this->strings[] = '';
                }
            }
        }
    }

    private function loadSheets() {
        $wbXml = simplexml_load_string($this->zip->getFromName('xl/workbook.xml'));
        $relsXml = simplexml_load_string($this->zip->getFromName('xl/_rels/workbook.xml.rels'));
        $relMap = [];
        foreach ($relsXml->Relationship as $rel) {
            $relMap[(string)$rel['Id']] = (string)$rel['Target'];
        }
        foreach ($wbXml->sheets->sheet as $s) {
            $name = (string)$s['name'];
            $rId = (string)$s->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
            $target = $relMap[$rId] ?? '';
            $this->sheets[$name] = 'xl/' . ltrim($target, '/');
        }
    }

    public function getSheetData($sheetName) {
        if (!isset($this->sheets[$sheetName])) return null;
        $xmlStr = $this->zip->getFromName($this->sheets[$sheetName]);
        if (!$xmlStr) return null;
        $xml = simplexml_load_string($xmlStr);
        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $rIdx = (int)$row['r'];
            $rowData = [];
            foreach ($row->c as $c) {
                $ref = (string)$c['r'];
                $col = preg_replace('/[0-9]/', '', $ref);
                $type = (string)$c['t'];
                $val = '';
                if ($type === 's') {
                    $sIdx = (int)$c->v;
                    $val = $this->strings[$sIdx] ?? '';
                } else {
                    $val = isset($c->v) ? (string)$c->v : '';
                }
                $rowData[$col] = trim($val);
            }
            $rows[$rIdx] = $rowData;
        }
        return $rows;
    }
}
