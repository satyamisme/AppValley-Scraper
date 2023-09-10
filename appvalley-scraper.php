<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $url = filter_var($_POST['url'], FILTER_SANITIZE_URL); //Never trust user input. Man fuck the user...
    
    if ($url === false) {
        die('Bruh... Invalid URL provided.');
    }

    $doc = new DOMDocument();
    $success = @$doc->loadHTMLFile($url);

    if (!$success) {
        die('Failed to load the webpage.');
    }

    $xpath = new DOMXPath($doc);
    $appNames = $xpath->query('//p[@class="app-preview__name truncate"]');
    $pngLinks = $xpath->query('//img[@class="app-preview__icon"]/@data-src');
    $appLinks = $xpath->query('//a[@class="app-preview__link"]/@href');

    if ($appNames->length === $pngLinks->length && $pngLinks->length === $appLinks->length) {
        $data = [];

        for ($i = 0; $i < $appNames->length; $i++) {
            $appName = htmlspecialchars($appNames[$i]->textContent, ENT_QUOTES, 'UTF-8');
            $pngLink = htmlspecialchars($pngLinks[$i]->nodeValue, ENT_QUOTES, 'UTF-8');
            $appLink = 'https://www.app-valley.vip/' . str_replace('app', 'install', htmlspecialchars($appLinks[$i]->nodeValue, ENT_QUOTES, 'UTF-8'));

            $data[] = [
                'App Name' => $appName,
                'PNG Link' => $pngLink,
                'App Link' => $appLink,
            ];
        }

        $AppValleyAppsCSV = 'appvalley_apps.csv';

        $AppValleyCSVFile = fopen($AppValleyAppsCSV, 'w');

        if (!$AppValleyCSVFile) {
            die('Failed to open the CSV file for writing.');
        }
    
        fputcsv($AppValleyCSVFile, ['title', 'logo', 'url']); // As per Colton's existing scheme.
        foreach ($data as $row) {
            fputcsv($AppValleyCSVFile, array_map('htmlspecialchars', $row, array_fill(0, count($row), ENT_QUOTES)));
        }
        
        fclose($AppValleyCSVFile);
        
        echo "<B>[+] Ayyy! Data has been scraped and saved to $AppValleyAppsCSV</B>";
    } else {
        die('<B style="color: red;">[!] FAIL: XPath queries returned different numbers of results.</B>');
    }
}
?>
