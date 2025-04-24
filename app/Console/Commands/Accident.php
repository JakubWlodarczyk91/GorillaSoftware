<?php

namespace App\Console\Commands;

use App\Service\AccidentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class Accident extends Command
{
    public function __construct(private AccidentService $accidentService)
    {
        parent::__construct();
    }

    /**
     * @var string
     */
    protected $signature = 'app:accident {jsonFileName}';

    /**
     * @var string
     */
    protected $description = 'Process accidents from json file.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $jsonFileName = $this->argument('jsonFileName');

        $json = Storage::disk('public')->get($jsonFileName);

        $accidents = json_decode($json, true);

        $infoData = $this->accidentService->processAccidents($accidents);

        $this->info('Ogólna liczba przetworzonych wiadomości: '.$infoData['processedDataCount']);
        $this->info('Liczba utworzonych przeglądów: '.$infoData['servicesCount']);
        $this->info('Liczba utworzonych zgłoszeń: '.$infoData['reportAccidentsCount']);
        $this->info('Liczba nieprzetworzonych zgłoszeń: '.$infoData['unprocessedAccidentsCount']);
    }
}
