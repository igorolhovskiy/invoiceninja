<?php

namespace App\Ninja\Repositories;

use App\Models\Cdr;
use App\Models\ClientColtDid;
use App\Models\Clients;
use App\Models\Document;

use DB;
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
            fputcsv($putStream, $fields);
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
}
