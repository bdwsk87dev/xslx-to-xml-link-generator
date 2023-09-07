<?php

namespace App\Http\Controllers;

use App\Models\XmlFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use SimpleXMLElement;

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

    public function edit(Request $request)
    {
        $fileName = "";
        $productId = $request->input('productId');
        $shopName = $request->input('shop_name');
        $shopLink = $request->input('shop_link');
        $uploadNewXLS = $request->input('uploadNewXLS');
        $deleteProducts = $request->input('deleteProducts');
        $allowNewProducts = $request->input('allowNewProducts');

        if ($request->hasFile('filename')) {
            $file = $request->file('filename');
            $fileName = $file->getClientOriginalName();

            // Сохраняем файл во временной директории
            $tempXlsxFilePath = tempnam(sys_get_temp_dir(), 'xlsx_');
            $file->move(sys_get_temp_dir(), $tempXlsxFilePath);

            // Теперь вы можете сохранить файл в нужное место
            // $file->storeAs('your_directory', $fileName); // Замените 'your_directory' на путь к папке, куда нужно сохранить файл
        }

        print_r($productId." | ");
        print_r($shopName." | ");
        print_r($shopLink." | ");
        print_r($uploadNewXLS." | ");
        print_r($deleteProducts." | ");
        print_r($allowNewProducts." | ");
        print_r($fileName." | ");


        // Найдем запись по id
        $xmlFile = XmlFile::find($productId);

        // Обновим нужные поля
        $xmlFile->shop_name = $shopName;
        $xmlFile->shop_link = $shopLink;

        // Сохраняем изменения
        $xmlFile->save();

        /*
         * Получим ссылку на файл (файл был загружен ранее!)
         */

        $fileUrl = null;
        if (!empty($xmlFile->filename)) {
            $xmlFilePath = Storage::disk('public')->path('uploads/' . $xmlFile->filename);
            $categories = $this->readCategoriesFromXmlFile($xmlFilePath);
            print_r("Categories : ");
            print_r($categories);
            $this->updateXmlFileFromXlsx($xmlFilePath, $tempXlsxFilePath, $categories, $deleteProducts, $allowNewProducts);
        }

    }


    public function readCategoriesFromXmlFile($xmlFilePath)
    {
        $categories = [];

        // Проверяем, существует ли файл по указанному URL
        if (file_exists($xmlFilePath)) {
            $xmlData = file_get_contents($xmlFilePath);

            // Создаем объект SimpleXMLElement из данных XML
            $xml = new SimpleXMLElement($xmlData);

            // Получаем корневой элемент <shop>
            $shopElement = $xml->shop;

            // Получаем элемент <categories> внутри корневого элемента <shop>
            $categoriesElement = $shopElement->categories;

            // Перебираем категории внутри элемента <categories>
            foreach ($categoriesElement->category as $category) {
                $categoryId = (int)$category['id'];
                $categoryName = (string)$category;

                // Добавляем категорию в массив
                $categories[$categoryId] = [
                    'ID' => $categoryId,
                    'Name' => $categoryName,
                ];
            }
        }

        return $categories;
    }


    public function updateXmlFileFromXlsx($xmlFilePath, $xlsxFilePath, $categories, $deleteProducts, $allowNewProducts)
    {

        // Шаг 1: Прочитать данные из xlsx файла
        $data = [];
        $spreadsheet = IOFactory::load($xlsxFilePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $firstRow = true; // Флаг для определения первой строки
        foreach ($worksheet->getRowIterator() as $row) {
            if ($firstRow) {
                $firstRow = false; // Пропустить первую строку
                continue;
            }

            $productId = (int)$worksheet->getCell('A' . $row->getRowIndex())->getValue();
            $productPrice = $worksheet->getCell('G' . $row->getRowIndex())->getValue();
            $productCategory = (int)$worksheet->getCell('R' . $row->getRowIndex())->getValue();

            $data[$productId] = [
                'price' => $productPrice,
                'category' => $productCategory,
            ];
        }


        // Шаг 2
        $xmlData = file_get_contents($xmlFilePath);
        $xml = new SimpleXMLElement($xmlData);
        $totalProducts = count($xml->shop->offers->offer);


        // Шаг 3: Обновить цену товаров в XML файле, только если они принадлежат к категориям из XLSX
        $interval = 10; // Интервал для записи процента выполнения

        $processedProducts = 0; // Переменная для отслеживания обработанных товаров

        $offers = $xml->shop->offers->offer; // Получаем коллекцию элементов
        // Цикл идет в обратном порядке, чтобы правильно удалять элементы
        for ($i = count($offers) - 1; $i >= 0; $i--) {
            $offer = $offers[$i];
            $productId = (int)$offer->attributes()['id'];
            $categoryId = (int)$offer->categoryId;

            // Проверка на принадлежность товара к нужной категории
            if (isset($categories[$categoryId])) {
                // если товар существует в новом файле
                if (isset($data[$productId])) {
                    $productPrice = $data[$productId]['price'];
                    $offer->price = $productPrice;
                } elseif ($deleteProducts) {
                    // Если товар не найден и $deleteProducts равен true, удаляем его из старого XML
                    unset($xml->shop->offers->offer[$i]);
                }
            }

            $processedProducts++; // Увеличиваем счетчик обработанных товаров

            // Вычисляем процент выполнения
            $completionPercentage = ($processedProducts / $totalProducts) * 100;

            if ($completionPercentage >= $interval) {
                // Записываем процент выполнения в файл
                $file = fopen('percent.txt', 'w');
                if ($file) {
                    fwrite($file, $completionPercentage);
                    fclose($file);
                } else {
                    // Обработчик ошибки
                }

                $interval += 10;
            }
        }




        // Шаг 4: Добавление товаров, которых нет

        $existingProducts = [];
        foreach ($xml->shop->offers->offer as $offer) {
            $productId = (int)$offer->attributes()['id'];
            $existingProducts[$productId] = true;
        }

        $rowArray = $worksheet->toArray();

        $firstRowSe = true;
        foreach ($rowArray as $rowArray) {
            $offer = [];
            if ($firstRowSe) {
                $firstRowSe = false; // Пропустить первую строку
                continue;
            }
            $productId = $rowArray[0];

            if (!isset($existingProducts[$productId]) && $rowArray[0] !="") {

                $offer = $xml->shop->offers->addChild('offer', '');

                $offer->addChild('ID', $rowArray[0]);
                $offer->addChild('name', htmlspecialchars($rowArray[3]));
                $offer->addChild('price', $rowArray[6]);

                // Добавьте остальные данные о товаре
                $offer->addChild('currencyId', 'PLN');
                $offer->addChild('categoryId', $rowArray[17] ?? null);
                $offer->addChild('pictures', "");
                $offer->addChild('pickup', "false");
                $offer->addChild('delivery', "true");
                $offer->addChild('description', '<![CDATA[ ' . $rowArray[5]. ']]>');

                // Добавление изображений
                if (!empty($rowArray[13])) {
                    $imageUrls = explode(',', $rowArray[13]);
                    foreach ($imageUrls as $imageUrl) {
                        $imageUrl = trim($imageUrl);
                        if (!empty($imageUrl)) {
                            $picture = $offer->addChild('picture', $imageUrl);
                        }
                    }
                }

                // Добавление остальных полей
                if (!empty($rowArray[1]) && $rowArray[1] !== "NULL") {
                    $offer->addChild('barcode', $rowArray[1]);
                }

                if (!empty($rowArray[7]) && $rowArray[7] !== "NULL") {
                    $offer->addChild('oldprice', $rowArray[7]);
                }

                if (!empty($rowArray[18]) && $rowArray[18] !== "NULL") {
                    $offer->addChild('vendor', $rowArray[18]);
                }

                if (!empty($rowArray[29]) && $rowArray[29] !== "NULL") {
                    $param = $offer->addChild('Param', '');
                    $param->addChild('Name', $rowArray[27]);
                    $param->addChild('Unit', '');
                    $param->addChild('Value', $rowArray[29]);
                }
            }
        }



        // Переименование старого файла
        if (file_exists($xmlFilePath)) {
            rename($xmlFilePath, $xmlFilePath . date('Y-m-d_H-i-s') . '.xml');
        }

        // Шаг 4: Сохранить результаты в редактируемый XML файл и переименовать старый файл
        $newXmlString = $xml->asXML();
        file_put_contents($xmlFilePath, $newXmlString);
    }

    public function getCompletionPercentage()
    {
        // Читаем процент выполнения из файла
        $filePath = 'percent.txt';
        if (file_exists($filePath)) {
            $completionPercentage = file_get_contents($filePath);
        } else {
            // Если файл не существует, установите значение по умолчанию (например, 0)
            $completionPercentage = 0;
        }

        // Возвращаем процент выполнения в виде JSON
        return response()->json(['percentage' => $completionPercentage]);
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
 //       $sheets = $spreadsheet->getAllSheets();

//        foreach ($sheets as $sheet) {
//            $rows = $sheet->toArray();
//            $data = array_merge($data, $rows);
//        }

        $worksheet = $spreadsheet->getSheetByName('Export Products Sheet');
        $rows = $worksheet->toArray();
        $data = array_slice($rows, 1); // Пропускаем заголовки

        $shop = [
            'name' => 'Allegro *UA*',
            'company' => 'Allegro *UA*',
            'url' => 'Allegro *UA*',
            'currencies' => [
                ['currency' => ['ID' => 'USD', 'Rate' => 'CB']],
                ['currency' => ['ID' => 'PLN', 'Rate' => '1']],
                ['currency' => ['ID' => 'BYN', 'Rate' => 'CB']],
                ['currency' => ['ID' => 'KZT', 'Rate' => 'CB']],
                ['currency' => ['ID' => 'EUR', 'Rate' => 'CB']],
            ],
            'categories' => [],
            'offers' => [],
        ];

        try {

            $shop['offers'] = [];

            foreach ($data as $row) {

                $offer = [
                    'ID' => $row[0],
                    //'Available' => true,
                    'name' => htmlspecialchars($row[3]),
                    'price' => $row[6],
                    'currencyId' => 'PLN',
                    'categoryId' => $row[17] ?? null,
                    'pictures' => [],
                    'pickup' => "false",
                    'delivery' => "true",
                    'description' => '<![CDATA[ ' . $row[5]. ']]>',
                ];

                if (!empty($row[13])) {
                    $imageUrls = explode(',', $row[13]);
                    foreach ($imageUrls as $imageUrl) {
                        $imageUrl = trim($imageUrl);
                        if (!empty($imageUrl)) {
                            $offer['pictures'][] = ['picture' => $imageUrl];
                        }
                    }
                }

                if (!empty($row[1]) && $row[1] !== "NULL") {
                    $offer['barcode'] = $row[1];
                }

                if (!empty($row[7]) && $row[7] !== "NULL") {
                    $offer['oldprice'] = $row[7];
                }

                if (!empty($row[18]) && $row[18] !== "NULL") {
                    $offer['vendor'] = $row[18];
                }

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


                if (!empty($row[35]) && $row[35] !== "NULL") {
                    $offer['Param'][] = [
                        'Name' => $row[33],
                        'Unit' => '',
                        'Value' => $row[35],
                    ];
                }

                if (!empty($row[38]) && $row[38] !== "NULL") {
                    $offer['Param'][] = [
                        'Name' => $row[36],
                        'Unit' => '',
                        'Value' => $row[38],
                    ];
                }


                if (!empty($row[41]) && $row[41] !== "NULL") {
                    $offer['Param'][] = [
                        'Name' => $row[39],
                        'Unit' => '',
                        'Value' => $row[41],
                    ];
                }

                if (!empty($row[44]) && $row[44] !== "NULL") {
                    $offer['Param'][] = [
                        'Name' => $row[42],
                        'Unit' => '',
                        'Value' => $row[44],
                    ];
                }

                if (!empty($row[47]) && $row[47] !== "NULL") {
                    $offer['Param'][] = [
                        'Name' => $row[45],
                        'Unit' => '',
                        'Value' => $row[47],
                    ];
                }

                if (!empty($row[50]) && $row[50] !== "NULL") {
                    $offer['Param'][] = [
                        'Name' => $row[48],
                        'Unit' => '',
                        'Value' => $row[50],
                    ];
                }

                if (!empty($row[53]) && $row[53] !== "NULL") {
                    $offer['Param'][] = [
                        'Name' => $row[51],
                        'Unit' => '',
                        'Value' => $row[53],
                    ];
                }

                if (!empty($row[56]) && $row[56] !== "NULL") {
                    $offer['Param'][] = [
                        'Name' => $row[54],
                        'Unit' => '',
                        'Value' => $row[56],
                    ];
                }

                $shop['offers'][] = ['offer' => $offer];
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
            $shop['categories'][] = ['category' => $category];
        }

        $currentDateTime = date('Y-m-d H:i');
        $xmlData = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        $xmlData .= '<yml_catalog date="'.$currentDateTime.'"><shop>' . "\n";
        $xmlData .= $this->arrayToXml($shop, 1);
        $xmlData .= '</shop></yml_catalog>' . "\n";

        return $xmlData;
    }

    private function arrayToXml($array, $level)
    {

        $xml = '';
        $indentation = str_repeat('  ', $level);

        foreach ($array as $key => $value) {
            if (is_array($value)) {

                // Up
                if (!is_numeric($key)) {

                    // Pictures
                    if($key == "picture"){
                        $xml .= $indentation . "<$key>" . $value . "</$key>\n";
                        continue;
                    }

                    // Если категория, тогда все делаем в одной строке! Без middle и down
                    if($key == "category"){
                        $xml .= $indentation . "<$key id=\"".$value['ID']."\"";
                        if (isset($value['ParentID'])){
                        $xml .= " parentId=\"".$value['ParentID']."\"";
                        }

                        $xml .=">".$value['Name']."</$key> \n";
                        continue;
                    }

                    // Если валюта, тогда все делаем в одной строке! Без middle и down
                    if($key == "currency"){
                        $xml .= $indentation . "<$key id=\"".$value['ID']."\" rate=\"".$value['Rate']."\"></$key> \n";
                        continue;
                    }

                    // Если оффер, тогда добавляем параметр в тег
                    if($key == "offer"){
                        $xml .= $indentation . "<$key id=\"".$value['ID']."\" available=\"true\"> \n";
                    }

                    else{
                        if($key!="pictures") {
                            $xml .= $indentation . "<$key>\n";
                        }
                    }
                }

                $xml .= $this->arrayToXml($value, $level + 1);

                if (!is_numeric($key)) {
                    if($key!="pictures") {
                        $xml .= $indentation . "</$key>\n";
                    }
                }

            } else {

                // Pictures
                if($key == "ID"){
                   continue;
                }

                // htmlspecialchars for name
                if($key === "Name"){
                    $xml .= $indentation . "<$key>" . htmlspecialchars($value) . "</$key>\n";
                }

                // other
                else{
                    $xml .= $indentation . "<$key>" . $value . "</$key>\n";
                }

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

    public function delete($id)
    {
        $xmlFile = XmlFile::findOrFail($id);

        // Удалите файл с сервера
        Storage::disk('public')->delete('uploads/' . $xmlFile->filename);

        // Удалите запись из базы данных
        $xmlFile->delete();

        return redirect()->back()->with('success', 'File and record deleted successfully.');
    }

}
