<?php
namespace App\Services;

use RuntimeException;
use SimpleXMLElement;

class SpreadsheetImportService
{
    private const MAX_FILE_SIZE = 10485760;
    private const MAX_ENTRY_SIZE = 26214400;
    private const MAX_ROWS = 50000;
    private const MAX_ZIP_ENTRIES = 5000;
    private const XLSX_NAMESPACE = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    public static function readFile(string $path, string $extension): array
    {
        $size = filesize($path);
        if ($size === false || $size === 0 || $size > self::MAX_FILE_SIZE) {
            throw new RuntimeException('Tệp trống hoặc vượt quá dung lượng tối đa 10 MB.');
        }

        if (strtolower($extension) === 'xlsx') {
            return self::readXlsx($path);
        }

        if (!in_array(strtolower($extension), ['csv', 'txt'], true)) {
            throw new RuntimeException('Chỉ hỗ trợ tệp .xlsx, .csv hoặc .txt.');
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException('Không thể đọc nội dung tệp đã tải lên.');
        }

        return self::readDelimitedText($contents);
    }

    public static function readDelimitedText(string $contents): array
    {
        if (strncmp($contents, "\xFF\xFE", 2) === 0) {
            $contents = iconv('UTF-16LE', 'UTF-8//IGNORE', substr($contents, 2));
        } elseif (strncmp($contents, "\xFE\xFF", 2) === 0) {
            $contents = iconv('UTF-16BE', 'UTF-8//IGNORE', substr($contents, 2));
        }
        if ($contents === false) {
            throw new RuntimeException('Không thể chuyển đổi bảng mã của tệp dữ liệu.');
        }
        $contents = preg_replace('/^\xEF\xBB\xBF/', '', $contents);

        $delimiter = self::detectDelimiter($contents);
        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw new RuntimeException('Không thể chuẩn bị dữ liệu để đọc tệp.');
        }

        fwrite($stream, $contents);
        rewind($stream);
        $rows = [];
        while (($row = fgetcsv($stream, 0, $delimiter, '"', '')) !== false) {
            $row = array_map(static function ($value): string {
                return trim((string) $value);
            }, $row);
            if (count(array_filter($row, static function ($value): bool {
                return $value !== '';
            })) > 0) {
                $rows[] = $row;
                if (count($rows) > self::MAX_ROWS) {
                    fclose($stream);
                    throw new RuntimeException('Tệp vượt quá giới hạn 50.000 dòng.');
                }
            }
        }
        fclose($stream);

        return $rows;
    }

    private static function detectDelimiter(string $contents): string
    {
        foreach (preg_split('/\r\n|\n|\r/', $contents) as $line) {
            if (trim($line) === '') {
                continue;
            }

            $scores = [
                ',' => substr_count($line, ','),
                ';' => substr_count($line, ';'),
                "\t" => substr_count($line, "\t"),
            ];
            arsort($scores);
            if (reset($scores) === 0) {
                continue;
            }
            return (string) array_key_first($scores);
        }

        return ',';
    }

    private static function readXlsx(string $path): array
    {
        $zip = file_get_contents($path);
        if ($zip === false) {
            throw new RuntimeException('Không thể đọc tệp Excel.');
        }

        $sharedStringsXml = self::readZipEntry($zip, 'xl/sharedStrings.xml');
        $sharedStrings = $sharedStringsXml === null ? [] : self::parseSharedStrings($sharedStringsXml);
        $sheetPath = self::firstWorksheetPath($zip);
        $sheetXml = self::readZipEntry($zip, $sheetPath);
        if ($sheetXml === null) {
            throw new RuntimeException('Không tìm thấy trang tính đầu tiên trong tệp Excel.');
        }

        return self::parseWorksheet($sheetXml, $sharedStrings);
    }

    private static function firstWorksheetPath(string $zip): string
    {
        $workbookXml = self::readZipEntry($zip, 'xl/workbook.xml');
        $relationshipsXml = self::readZipEntry($zip, 'xl/_rels/workbook.xml.rels');
        if ($workbookXml === null || $relationshipsXml === null) {
            return 'xl/worksheets/sheet1.xml';
        }

        $workbook = self::parseXml($workbookXml);
        $workbook->registerXPathNamespace('x', self::XLSX_NAMESPACE);
        $workbook->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $sheets = $workbook->xpath('//x:sheets/x:sheet');
        if (!$sheets) {
            return 'xl/worksheets/sheet1.xml';
        }

        $relationshipId = (string) ($sheets[0]->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'] ?? '');
        $relationships = self::parseXml($relationshipsXml);
        $relationships->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/package/2006/relationships');
        foreach ($relationships->xpath('//r:Relationship') as $relationship) {
            $attributes = $relationship->attributes();
            if ((string) ($attributes['Id'] ?? '') !== $relationshipId) {
                continue;
            }

            $target = str_replace('\\', '/', (string) ($attributes['Target'] ?? ''));
            if (str_starts_with($target, '/')) {
                return ltrim($target, '/');
            }

            return 'xl/' . ltrim($target, '/');
        }

        return 'xl/worksheets/sheet1.xml';
    }

    private static function readZipEntry(string $zip, string $wantedName): ?string
    {
        $endOffset = strrpos($zip, "PK\x05\x06");
        if ($endOffset === false || strlen($zip) < $endOffset + 22) {
            throw new RuntimeException('Tệp Excel không đúng định dạng .xlsx.');
        }

        $end = unpack('vdisk/vcentralDisk/ventriesOnDisk/ventriesTotal/VcentralSize/VcentralOffset/vcommentLength', substr($zip, $endOffset + 4, 18));
        if ($end === false) {
            throw new RuntimeException('Không đọc được cấu trúc tệp Excel.');
        }
        if ($end['entriesTotal'] === 0xFFFF || $end['centralOffset'] === 0xFFFFFFFF) {
            throw new RuntimeException('Tệp Excel quá lớn, không hỗ trợ định dạng ZIP64.');
        }
        if ($end['entriesTotal'] > self::MAX_ZIP_ENTRIES) {
            throw new RuntimeException('Tệp Excel có quá nhiều thành phần.');
        }
        if (
            $end['disk'] !== 0 ||
            $end['centralDisk'] !== 0 ||
            $end['entriesOnDisk'] !== $end['entriesTotal'] ||
            $end['centralOffset'] + $end['centralSize'] > $endOffset
        ) {
            throw new RuntimeException('Danh mục bên trong tệp Excel không hợp lệ.');
        }

        $offset = $end['centralOffset'];
        for ($entryNumber = 0; $entryNumber < $end['entriesTotal']; $entryNumber++) {
            if ($offset + 46 > $endOffset || substr($zip, $offset, 4) !== "PK\x01\x02") {
                throw new RuntimeException('Danh mục bên trong tệp Excel không hợp lệ.');
            }

            $entry = unpack(
                'vversionMade/vversionNeeded/vflags/vmethod/vtime/vdate/Vcrc/Vcompressed/Vuncompressed/vnameLength/vextraLength/vcommentLength/vdisk/vinternal/Vexternal/VlocalOffset',
                substr($zip, $offset + 4, 42)
            );
            $nameOffset = $offset + 46;
            $name = substr($zip, $nameOffset, $entry['nameLength']);
            $offset = $nameOffset + $entry['nameLength'] + $entry['extraLength'] + $entry['commentLength'];

            if ($name !== $wantedName) {
                continue;
            }
            if (($entry['flags'] & 1) !== 0) {
                throw new RuntimeException('Tệp Excel được mã hóa bằng mật khẩu và không thể nhập.');
            }
            if ($entry['uncompressed'] > self::MAX_ENTRY_SIZE) {
                throw new RuntimeException('Dữ liệu trong tệp Excel vượt quá giới hạn cho phép.');
            }

            $localOffset = $entry['localOffset'];
            if ($localOffset + 30 > strlen($zip) || substr($zip, $localOffset, 4) !== "PK\x03\x04") {
                throw new RuntimeException('Dữ liệu trang tính Excel không hợp lệ.');
            }
            $local = unpack('vversion/vflags/vmethod/vtime/vdate/Vcrc/Vcompressed/Vuncompressed/vnameLength/vextraLength', substr($zip, $localOffset + 4, 26));
            $dataOffset = $localOffset + 30 + $local['nameLength'] + $local['extraLength'];
            if ($dataOffset + $entry['compressed'] > strlen($zip)) {
                throw new RuntimeException('Dữ liệu trang tính Excel không đầy đủ.');
            }
            $compressed = substr($zip, $dataOffset, $entry['compressed']);

            if ($entry['method'] === 0) {
                $contents = $compressed;
            } elseif ($entry['method'] === 8) {
                $contents = gzinflate($compressed, self::MAX_ENTRY_SIZE);
            } else {
                throw new RuntimeException('Kiểu nén của tệp Excel không được hỗ trợ.');
            }

            if ($contents === false || strlen($contents) !== $entry['uncompressed']) {
                throw new RuntimeException('Không thể giải nén nội dung trang tính Excel.');
            }
            if (sprintf('%u', crc32($contents)) !== sprintf('%u', $entry['crc'])) {
                throw new RuntimeException('Dữ liệu trang tính Excel bị lỗi, vui lòng lưu lại tệp và thử lại.');
            }

            return $contents;
        }

        return null;
    }

    private static function parseSharedStrings(string $xml): array
    {
        $document = self::parseXml($xml);
        $document->registerXPathNamespace('x', self::XLSX_NAMESPACE);
        $values = [];
        foreach ($document->xpath('//x:si') as $item) {
            $item->registerXPathNamespace('x', self::XLSX_NAMESPACE);
            $text = '';
            foreach ($item->xpath('.//x:t') as $part) {
                $text .= (string) $part;
            }
            $values[] = $text;
        }

        return $values;
    }

    private static function parseWorksheet(string $xml, array $sharedStrings): array
    {
        $document = self::parseXml($xml);
        $document->registerXPathNamespace('x', self::XLSX_NAMESPACE);
        $rows = [];
        foreach ($document->xpath('//x:sheetData/x:row') as $rowElement) {
            if (count($rows) >= self::MAX_ROWS) {
                throw new RuntimeException('Tệp vượt quá giới hạn 50.000 dòng.');
            }
            $rowElement->registerXPathNamespace('x', self::XLSX_NAMESPACE);
            $row = [];
            foreach ($rowElement->xpath('./x:c') as $cell) {
                $cell->registerXPathNamespace('x', self::XLSX_NAMESPACE);
                $attributes = $cell->attributes();
                $column = self::columnIndex((string) ($attributes['r'] ?? ''));
                $type = (string) ($attributes['t'] ?? '');
                $children = $cell->children(self::XLSX_NAMESPACE);
                $value = isset($children->v) ? (string) $children->v : '';

                if ($type === 's') {
                    $value = $sharedStrings[(int) $value] ?? '';
                } elseif ($type === 'inlineStr' && isset($children->is)) {
                    $inline = $children->is;
                    $inline->registerXPathNamespace('x', self::XLSX_NAMESPACE);
                    $value = '';
                    foreach ($inline->xpath('.//x:t') as $part) {
                        $value .= (string) $part;
                    }
                } elseif ($type === 'b') {
                    $value = $value === '1' ? '1' : '0';
                }

                $row[$column] = trim($value);
            }

            if ($row !== []) {
                ksort($row);
                $values = array_fill(0, max(array_keys($row)) + 1, '');
                foreach ($row as $column => $value) {
                    $values[$column] = $value;
                }
                $rows[] = $values;
            }
        }

        return $rows;
    }

    private static function columnIndex(string $cellReference): int
    {
        if (!preg_match('/^([A-Z]+)/i', $cellReference, $matches)) {
            throw new RuntimeException('Tệp Excel có địa chỉ ô không hợp lệ.');
        }

        $index = 0;
        foreach (str_split(strtoupper($matches[1])) as $letter) {
            $index = $index * 26 + ord($letter) - ord('A') + 1;
            if ($index > 16384) {
                throw new RuntimeException('Tệp Excel có quá nhiều cột.');
            }
        }

        return $index - 1;
    }

    private static function parseXml(string $xml): SimpleXMLElement
    {
        $document = simplexml_load_string($xml, SimpleXMLElement::class, LIBXML_NONET | LIBXML_COMPACT);
        if ($document === false) {
            throw new RuntimeException('Cấu trúc dữ liệu Excel không hợp lệ.');
        }

        return $document;
    }
}
