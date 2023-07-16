<?php

namespace App\Http\Controllers;

use App\Models\XmlFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class XmlFileController extends Controller

{
    public function index(Request $request)
    {
        $xmlFiles = XmlFile::query();

        if ($request->has('sort_by') && !empty($request->get('sort_by'))) {
            $sortColumn = $request->get('sort_by');
            $sortDirection = $request->get('order', 'asc');
            $xmlFiles->orderBy($sortColumn, $sortDirection);
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $xmlFiles->where('shop_name', 'like', "%{$search}%");
        }

        $perPage = $request->get('per_page', 30);
        $xmlFiles = $xmlFiles->paginate($perPage);

        return inertia('list', compact('xmlFiles'));
    }

    public function show($id)
    {
        $xmlFile = XmlFile::findOrFail($id);
        $xmlFilePath = Storage::disk('public')->path('uploads/' . $xmlFile->filename);

        if (File::exists($xmlFilePath)) {
            $content = File::get($xmlFilePath);
            return Response::make($content, 200, [
                'Content-Type' => 'application/xml',
            ]);
        } else {
            abort(404);
        }
    }

    public function home(Request $request)
    {
        $xmlFiles = XmlFile::query();

        if ($request->has('sort_by')) {
            $xmlFiles->orderBy($request->get('sort_by'), $request->get('order', 'asc'));
        }

        $xmlFiles = $xmlFiles->paginate(10); // paginate with 10 items per page

        return inertia('home', compact('xmlFiles'));
    }

    public function upload(Request $request)
    {
        $uploadType = $request->input('uploadType');

        $file = $request->file('file');
        $shopName = $request->input('shopName');
        $shopLink = $request->input('shopLink');
        $fileName = $file->getClientOriginalName();

        // $currentDateTime = now()->format('d_m H:i');
        $currentDateTime = time();
        $newFileName = $shopName . ' ' . $currentDateTime . '.xml';

        if ($uploadType === "xlsx") {

            // Handle xlsx file upload and conversion to XML

            $updatedFileName = $currentDateTime . ' ' . $fileName;

            // Save the uploaded file
            $file->storeAs('uploads/originals/', $updatedFileName, 'public');

            // Convert xlsx to XML
            $convertedFilePath = $this->convertXlsxToXml($updatedFileName, $newFileName);

            if ($convertedFilePath) {
                // Store the converted XML file
                $convertedFileContent = File::get($convertedFilePath);
                Storage::disk('public')->put('uploads/' . $newFileName, $convertedFileContent);

                // Remove the temporary converted file
                // File::delete($updatedFileName);

                // Create a database record for the uploaded XML file
                XmlFile::create([
                    'filename' => $newFileName,
                    'shop_name' => $shopName,
                    'shop_link' => $shopLink,
                    'uploadDateTime' => now(),
                    'lastCheckDateTime' => now(),
                ]);
                return redirect()->back()->with('success', 'File uploaded and converted successfully.');
            } else {
                return redirect()->back()->with('error', 'Failed to convert the file to XML.');
            }
        } else {
            $file = $request->file('file');
            $shopName = $request->input('shopName'); // Получаем значение shopName из запроса
            $shopLink = $request->input('shopLink'); // Получаем значение shopLink из запроса
            $fileName = $file->getClientOriginalName();
            // $currentDateTime = now()->format('d_m H:i');
            $currentDateTime = time();
            $NewFileName = $shopName . ' ' . $currentDateTime . '.xml'; // Формируем новое имя файла

            $file->storeAs('uploads', $NewFileName, 'public');
            // Создаем запись в базе данных для загруженного файла
            XmlFile::create([
                'filename' => $NewFileName,
                'shop_name' => $shopName,
                'shop_link' => $shopLink,
                'uploadDateTime' => now(),
                'lastCheckDateTime' => now(),
            ]);
            return redirect()->back()->with('success', 'File uploaded successfully.');
        }
    }


    private function convertXlsxToXml($xlsxFileName, $xmlFileName)
    {
        $tempXlsxFilePath = Storage::disk('public')->path('uploads/originals/' . $xlsxFileName);
        $tempXmlFilePath = Storage::disk('public')->path('uploads/' . $xmlFileName);

        try {
            // Open the xlsx file
            $spreadsheet = IOFactory::load($tempXlsxFilePath);

            // Extract data from the xlsx file and convert to XML
            $xmlData = $this->extractXmlDataFromXlsx($spreadsheet);

            // Write the XML data to a file
            file_put_contents($tempXmlFilePath, $xmlData);

            return $tempXmlFilePath;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function extractXmlDataFromXlsx($spreadsheet)
    {

        $data = [];
        $sheets = $spreadsheet->getAllSheets();

        foreach ($sheets as $sheet) {
            $rows = $sheet->toArray();
            $data = array_merge($data, $rows);
        }

        $shop = [
            'Name' => 'Allegro *UA*',
            'Company' => 'Allegro *UA*',
            'URL' => 'Allegro *UA*',
            'Currencies' => [
                ['ID' => 'USD', 'Rate' => 'CB'],
                ['ID' => 'PLN', 'Rate' => '1'],
                ['ID' => 'BYN', 'Rate' => 'CB'],
                ['ID' => 'KZT', 'Rate' => 'CB'],
                ['ID' => 'EUR', 'Rate' => 'CB'],
            ],
            'Categories' => [],
            'Offers' => [],
        ];

        try {

            $shop['Offers'] = [];

            foreach ($data as $row) {

                $offer = [
                    'ID' => $row[0],
                    'Available' => true,
                    'Name' => html_entity_decode($row[3]),
                    'Price' => $row[6],
                    'CurrencyID' => 'PLN',
                    'CategoryID' => $row[17] ?? null,
                    'Pictures' => [],
                    'Pickup' => false,
                    'Delivery' => true,
                    'Description' => ['Value' => $row[5]],
                ];

                if (!empty($row[13])) {
                    $imageUrls = explode(',', $row[13]);
                    foreach ($imageUrls as $imageUrl) {
                        $imageUrl = trim($imageUrl);
                        if (!empty($imageUrl)) {
                            $offer['Pictures'][] = ['Value' => $imageUrl];
                        }
                    }
                }

                if (!empty($row[1]) && $row[1] !== "NULL") {
                    $offer['Barcode'] = $row[1];
                }

                if (!empty($row[7]) && $row[7] !== "NULL") {
                    $offer['Oldprice'] = $row[7];
                }

                if (!empty($row[18]) && $row[18] !== "NULL") {
                    $offer['Vendor'] = $row[18];
                }

                $offer['Param'] = [];
                if (!empty($row[29]) && $row[29] !== "NULL") {
                    $offer['Param'][] = [
                        'Name' => $row[27],
                        'Unit' => '',
                        'Value' => $row[29],
                    ];
                }

                if (!empty($row[32]) && $row[32] !== "NULL") {
                    $offer['Param'][] = [
                        'Name' => $row[30],
                        'Unit' => '',
                        'Value' => $row[32],
                    ];
                }

                $shop['Offers'][] = ['offer' => $offer];
            }
        } catch (\Exception $e) {
            // Обработка и вывод ошибки
            echo "Произошла ошибка: " . $e->getMessage();
        }


        // Чтение данных из второй страницы Excel

        $worksheet = $spreadsheet->getSheetByName('Export Groups Sheet');
        $rows = $worksheet->toArray();
        $rows = array_slice($rows, 1); // Пропускаем заголовки

        // Проход по строкам Excel
        foreach ($rows as $row) {

            if (empty($row[2])) {
                continue;
            }

            // Создание категории
            $category = [
                'ID' => $row[2],
                'Name' => $row[1],
            ];

            // Добавление родительской категории, если есть
            if (!empty($row[3])) {
                $category['ParentID'] = $row[3];
            }

            // Добавление категории в список категорий
            $shop['Categories'][] = ['category' => $category];
        }


        $xmlData = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        $xmlData .= '<root>' . "\n";
        $xmlData .= $this->arrayToXml($shop, 1);
        $xmlData .= '</root>' . "\n";

        return $xmlData;
    }

    private function arrayToXml($array, $level)
    {

        $xml = '';
        $indentation = str_repeat('  ', $level);

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    if($key == "offer"){
                        $xml .= $indentation . "<$key id=\"".$value['ID']."\"> \n";
                    }
                    else{
                        $xml .= $indentation . "<$key>\n";
                    }
                }
                $xml .= $this->arrayToXml($value, $level + 1);
                if (!is_numeric($key)) {
                    $xml .= $indentation . "</$key>\n";
                }


            } else {
                $xml .= $indentation . "<$key>" . htmlspecialchars($value) . "</$key>\n";
            }
        }


        return $xml;
    }


    private function extractIDFromString($input)
    {
        $digits = [];
        for ($i = 0; $i < strlen($input); $i++) {
            $char = $input[$i];
            if (ctype_digit($char)) {
                $digits[] = $char;
            }
        }

        if (count($digits) > 11) {
            $digits = array_slice($digits, 0, 11);
        }

        return implode('', $digits);
    }

}
