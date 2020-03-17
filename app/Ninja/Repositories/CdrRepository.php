<?php

namespace App\Ninja\Repositories;

use Illuminate\Support\Facades\Response;
use App\Libraries\CurlUtils;
use App\Models\Cdr;
use App\Models\ClientColtDid;
use App\Models\Clients;
use App\Models\Document;

use DB;
use Carbon;
use Utils;

class CdrRepository extends BaseRepository
{
    protected $clientRepo;

    public function __construct(ClientRepository $clientRepo)
    {
        $this->clientRepo = $clientRepo;
    }

    public function getClassName()
    {
        return 'App\Models\Cdr';
    }

    public function save($data, $cdr = null)
    {
        $entity = $cdr ?: Cdr::createNew();

        $entity->fill($data);
        $entity->save();

        return $entity;
    }

    public function insertArray($data) 
    {
        if (\Auth::check()) {
            $user = \Auth::user();
            $account = \Auth::user()->account;
        } else {
            Utils::fatalError();
        }
        foreach ($data as $index => $item) {
            $data[$index]['user_id'] = $user->id;
            $data[$index]['account_id'] = $account->id;
        }

        Cdr::insert($data);
    }

    public function updateClient()
    {
        $cdrs = Cdr::scope()
            ->whereNotNull('import_colt_id')
            ->whereNull('client_id')
            ->get();
        $numberUpdatedRows = 0;
        foreach($cdrs as $cdr) {
            $clientId = $this->clientRepo->getClientIdByDid($cdr->did);
            if ($clientId) {
                $cdr->client_id = $clientId;
                $cdr->save();
                $numberUpdatedRows++;
            }
        }
        return $numberUpdatedRows;
    }

    public function findByAstppId($uniqueId) {
        return Cdr::where('astpp_cdr_uniqueid', $uniqueId)->first();
    }

    public function findRateNotFoundImportColt($importColtId) {
        return Cdr
            ::where('import_colt_id', $importColtId)
            ->where('status', 'RATE_NOT_FOUND')
            ->get();
    }

    public function findRateNotFoundAstppClient($clientId, $startDate, $endDate) {
        return Cdr
        ::whereNotNull('astpp_cdr_uniqueid')
        ->where('client_id', $clientId)
        ->whereBetween('datetime', [$startDate, $endDate])
        ->where('status', 'RATE_NOT_FOUND')
        ->get();
    }

    public function invoiceDestinationReport($invoice) {
        return Cdr
            ::selectRaw('destination_name, count(*) as cnt, sum(dur) as duration, sum(cost) as cost') 
            ->where('invoice_id', $invoice->id)
            ->groupBy('destination_name')
            ->orderBy('destination_name')
            ->get();
    }

    public function getCdrPeriodForInvoice($invoice) {
        $sumCdr = Cdr
            ::selectRaw('min(datetime) as date_from, max(datetime) as date_to')
            ->where('invoice_id', $invoice->id)
            ->first();
        if (!$sumCdr) {
            return null;
        }
        $period_from = Carbon::parse($sumCdr->date_from)
            ->startOfMonth()
            ->toDateString();
        $period_to = Carbon::parse($sumCdr->date_to)
            ->endOfMonth()
            ->toDateString();
        return [
            'period_from' => $period_from,
            'period_to' => $period_to
        ];
    }

    public function exportCdr($invoice) {
        // TODO: create spearte method to build cdr data
        $cdrTable = [
            ['DID', 'Datetime', 'Destination', 'Duration', 'Cost']
        ];
        $cdrs = \App\Models\Cdr::select('did', 'datetime', 'dst', 'dur', 'cost')
            ->where('invoice_id', $invoice->id)
            ->orderBy('datetime')
            ->get();

        foreach ($cdrs as $cdr) {
            $cdrTable[] = [$cdr->did, $cdr->datetime, $cdr->dst, $cdr->dur, $cdr->cost];
        }
        $headers = [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=cdrs_' . $invoice->invoice_number . '.csv',
            'Expires'             => '0',
            'Pragma'              => 'public'
        ];
        return Response::stream(function () use ($cdrTable) {
                $file = fopen('php://output', 'w');
                foreach ($cdrTable as $row) {
                    // Change comma or dot to CSV_EXPORT_DECIMAL_DELIMITER in Cost field
                    $row[4] = str_replace(".", CSV_EXPORT_DECIMAL_DELIMITER, $row[4]);
                    $row[4] = str_replace(",", CSV_EXPORT_DECIMAL_DELIMITER, $row[4]);
                    
                    fputcsv($file, $row, CSV_EXPORT_DELIMITER, CSV_EXPORT_ENCLOSURE);
                }
                fclose($file);
            }, 200, $headers);
    }

    public function attachCdrToInvoice($invoice) {
        if (!$invoice->client->is_cdr_attach_invoice) {
            return false;
        }

        $cdrTable = [
            ['DID', 'Datetime', 'Destination', 'Duration', 'Cost']
        ];
        $cdrs = \App\Models\Cdr::select('did', 'datetime', 'dst', 'dur', 'cost')
            ->where('invoice_id', $invoice->id)
            ->orderBy('datetime')
            ->get();
        if ($cdrs->count() === 0) {
            return null;
        }

        foreach ($cdrs as $cdr) {
            $cdrTable[] = [$cdr->did, $cdr->datetime, $cdr->dst, $cdr->dur, $cdr->cost];
        }

        $document = Document::createNew();
        $disk = $document->getDisk();
        $putStream = tmpfile();
        foreach ($cdrTable as $fields) {
            // Change comma or dot to CSV_EXPORT_DECIMAL_DELIMITER in Cost field
            $row[4] = str_replace(".", CSV_EXPORT_DECIMAL_DELIMITER, $row[4]);
            $row[4] = str_replace(",", CSV_EXPORT_DECIMAL_DELIMITER, $row[4]);
            
            fputcsv($file, $row, CSV_EXPORT_DELIMITER, CSV_EXPORT_ENCLOSURE);
        }

        $fstatStream = fstat($putStream);
        $streamMetaData = stream_get_meta_data($putStream);

        rewind($putStream);

        $documentType = 'csv';
        $documentTypeData = Document::$types[$documentType];
        $name = "CDR for invoice {$invoice->invoice_number}.{$documentType}";
        $hash = sha1_file($streamMetaData['uri']);
        $filename = \Auth::user()->account->account_key.'/'.$hash.'.csv';
        
        $disk->getDriver()->putStream($filename, $putStream, ['mimetype' => $documentTypeData['mime']]);
        if (is_resource($putStream)) {
            fclose($putStream);
        }

        $size = $fstatStream['size'];
        $document->invoice_id = $invoice->id;
        $document->path = $filename;
        $document->type = $documentType;
        $document->size = $size;
        $document->hash = $hash;
        $document->name = substr($name, -255);       
        $document->save();

        return $document;
    }

    public function attachDestinationReportToInvoice($invoice)
    {
        $cdrCount = \App\Models\Cdr::where('invoice_id', $invoice->id)
            ->count();
        if ($cdrCount === 0) {
            return null;
        }

        $pdfString = $this->getPDFStringDestinationReport($invoice);
        if (!$pdfString) {
            return null;
        }
        $document = Document::createNew();
        $disk = $document->getDisk();
        $putStream = tmpfile();
        fwrite($putStream, $pdfString);

        $fstatStream = fstat($putStream);
        $streamMetaData = stream_get_meta_data($putStream);

        rewind($putStream);

        $documentType = 'pdf';
        $documentTypeData = Document::$types[$documentType];
        $name = "CDR destination report {$invoice->invoice_number}.{$documentType}";
        $hash = sha1_file($streamMetaData['uri']);
        $filename = \Auth::user()->account->account_key.'/'.$hash.'.pdf';
        
        $disk->getDriver()->putStream($filename, $putStream, ['mimetype' => $documentTypeData['mime']]);
        if (is_resource($putStream)) {
            fclose($putStream);
        }

        $size = $fstatStream['size'];
        $document->invoice_id = $invoice->id;
        $document->path = $filename;
        $document->type = $documentType;
        $document->size = $size;
        $document->hash = $hash;
        $document->name = substr($name, -255);       
        $document->save();

        return $document;        
    }

    /**
     * @return bool|string
     */
    public function getPDFStringDestinationReport($invoice, $decode = true)
    {
        if (! env('PHANTOMJS_CLOUD_KEY') && ! env('PHANTOMJS_BIN_PATH')) {
            return false;
        }

        if (Utils::isTravis()) {
            return false;
        }

        $invitation = $invoice->invitations[0];
        $link = $invitation->getLink('view', true, true) . "/cdr-destination-report";
        $pdfString = false;
        $phantomjsSecret = env('PHANTOMJS_SECRET');
        $phantomjsLink = $link . "?phantomjs=true&phantomjs_secret={$phantomjsSecret}";
        try {
            if (env('PHANTOMJS_BIN_PATH')) {
                // we see occasional 408 errors
                for ($i=1; $i<=5; $i++) {
                    $pdfString = CurlUtils::phantom('GET', $phantomjsLink);
                    $pdfString = strip_tags($pdfString);                   
                    if (strpos($pdfString, 'data') === 0) {
                        break;
                    } else {
                        if (Utils::isNinjaDev() || Utils::isTravis()) {
                            Utils::logError('Failed to generate: ' . $i);
                        }
                        $pdfString = false;
                        sleep(2);
                    }
                }
            }

            if (! $pdfString && ($key = env('PHANTOMJS_CLOUD_KEY'))) {
                $url = "http://api.phantomjscloud.com/api/browser/v2/{$key}/?request=%7Burl:%22{$link}?phantomjs=true%26phantomjs_secret={$phantomjsSecret}%22,renderType:%22html%22%7D";
                $pdfString = CurlUtils::get($url);
                $pdfString = strip_tags($pdfString);
            }
        } catch (\Exception $exception) {
            Utils::logError("PhantomJS - Failed to load {$phantomjsLink}: {$exception->getMessage()}");
            return false;
        }

        if (! $pdfString || strlen($pdfString) < 200) {
            Utils::logError("PhantomJS - Invalid response {$phantomjsLink}: {$pdfString}");
            return false;
        }

        if ($decode) {
            if ($pdf = Utils::decodePDF($pdfString)) {
                return $pdf;
            } else {
                Utils::logError("PhantomJS - Unable to decode {$phantomjsLink}");
                return false;
            }
        } else {
            return $pdfString;
        }
    }    
}
