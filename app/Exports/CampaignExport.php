<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Campaign;

class CampaignExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Campaign::all();
    }
}