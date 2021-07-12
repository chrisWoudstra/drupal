<?php

namespace Drupal\google_sheets_api\Service;

// Composer package: "google/apiclient"
use Google\Client;
use Google_Service_Sheets;

class GoogleSheetsApiService {

    private $googleSheetId;

    public function __construct() {
        // The id of the google spreadsheet, found in the web url.
        // Example: https://docs.google.com/spreadsheets/d/abc123xyz/edit#gid=0
        $this->googleSheetId = 'abc123xyz';
    }

    public function syncData() {
        $googleSheetData = $this->getGoogleSheetData();
        $organizedData = $this->organizeGoogleSheetData($googleSheetData);
        $this->storeGoogleSheetData($organizedData);
        \Drupal::logger('Google Sheet Data')->notice('Google Sheet Data Sync Complete');
    }

    /**
     * Fetches data from Google Sheets using Service Account authentication credentials.
     *
     * @return array
     * @throws \Google\Exception
     */
    public function getGoogleSheetData() {
        $client = new Client();
        $client->setAuthConfig(DRUPAL_ROOT . '/modules/custom/google_sheets_api/auth/service-account.json');
        $client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);

        $service = new Google_Service_Sheets($client);

        return $service->spreadsheets_values->get($this->googleSheetId, 'Data')['values'];
    }

    /**
     * Organizes Google Sheet data into a manageable array
     * that will be converted to a JSON and stored. Alternatively,
     * you could create Drupal entities here (Nodes, Taxonomy Terms, etc).
     *
    **/
    public function organizeGoogleSheetData($googleSheetData) {
        $organizedData = [];
        $key = 0;
        foreach ($googleSheetData as $i => $row) {
            if ($i === 0) { continue; } // skip headers
            $organizedData[$key]['a'] = $row[0];
            $organizedData[$key]['b'] = $row[1];
            $organizedData[$key]['c'] = $row[2];
            $key++;
        }
        return $organizedData;
    }

    /**
     * Saves the formatted array to a JSON file that
     * can be retrieved later from the front-end.
     *
     * @param $data
     */
    public function storeData($data) {
        $jsonFile = DRUPAL_ROOT . '/modules/custom/google_sheets_api/data/google_sheets_data.json';
        $fp = fopen($jsonFile, 'w');
        fwrite($fp, json_encode($data));
        fclose($fp);
    }
}
