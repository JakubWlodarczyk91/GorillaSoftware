<?php

namespace Tests\Unit\Service;

use App\Enum\AccidentEnum;
use App\Enum\PriorityEnum;
use App\Enum\StatusEnum;
use App\Service\AccidentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccidentServiceTest extends TestCase
{
    private AccidentService $accidentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->accidentService = new AccidentService;

        // Mock Storage facade
        Storage::fake('public');
    }

    public function test_process_accidents_empty()
    {
        $result = $this->accidentService->processAccidents([]);

        $this->assertEquals([
            'processedDataCount' => 0,
            'servicesCount' => 0,
            'reportAccidentsCount' => 0,
            'unprocessedAccidentsCount' => 0,
        ], $result);
    }

    public function test_process_accidents_single_service()
    {
        $accidents = [
            [
                'description' => 'Test serwis',
                'dueDate' => '2025-04-25 10:00:00',
                'phone' => '123456789',
            ],
        ];

        $result = $this->accidentService->processAccidents($accidents);

        $this->assertEquals([
            'processedDataCount' => 1,
            'servicesCount' => 0,
            'reportAccidentsCount' => 1,
            'unprocessedAccidentsCount' => 0,
        ], $result);

        Storage::disk('public')->assertExists('reportAccidents.json');
        $reportAccidentsCountJson = Storage::disk('public')->get('reportAccidents.json');
        $reportAccidents = json_decode($reportAccidentsCountJson, true);

        $this->assertCount(1, $reportAccidents);
        $this->assertEquals('Test serwis', $reportAccidents[0]['description']);
        $this->assertEquals(StatusEnum::DEADLINE->value, $reportAccidents[0]['status']);
    }

    public function test_process_accidents_single_report_accident()
    {
        $accidents = [
            [
                'description' => 'Test awarii',
                'dueDate' => '2025-04-25 10:00:00',
                'phone' => '123456789',
            ],
        ];

        $result = $this->accidentService->processAccidents($accidents);

        $this->assertEquals([
            'processedDataCount' => 1,
            'servicesCount' => 0,
            'reportAccidentsCount' => 1,
            'unprocessedAccidentsCount' => 0,
        ], $result);

        Storage::disk('public')->assertExists('reportAccidents.json');
        $reportAccidentsJson = Storage::disk('public')->get('reportAccidents.json');
        $reportAccidents = json_decode($reportAccidentsJson, true);

        $this->assertCount(1, $reportAccidents);
        $this->assertEquals('Test awarii', $reportAccidents[0]['description']);
        $this->assertEquals(StatusEnum::DEADLINE->value, $reportAccidents[0]['status']);
    }

    public function test_process_accidents_duplicated_description()
    {
        $accidents = [
            [
                'description' => 'Test awarii',
                'dueDate' => '2025-04-25 10:00:00',
                'phone' => '123456789',
            ],
            [
                'description' => 'Test awarii', // Duplicate
                'dueDate' => '2025-04-26 10:00:00',
                'phone' => '987654321',
            ],
        ];

        $result = $this->accidentService->processAccidents($accidents);

        $this->assertEquals([
            'processedDataCount' => 2,
            'servicesCount' => 0,
            'reportAccidentsCount' => 1,
            'unprocessedAccidentsCount' => 1,
        ], $result);

        Storage::disk('public')->assertExists('unprocessedAccidents.json');
        $unprocessedAccidentsJson = Storage::disk('public')->get('unprocessedAccidents.json');
        $unprocessedAccidents = json_decode($unprocessedAccidentsJson, true);

        $this->assertCount(1, $unprocessedAccidents);
        $this->assertEquals('Test awarii', $unprocessedAccidents[0]['description']);
        $this->assertEquals('Duplicated Accident', $unprocessedAccidents[0]['comment']);
    }

    public function test_process_accidents_mixed_types()
    {
        $accidents = [
            [
                'description' => 'Test serwis',
                'dueDate' => '2025-04-25 10:00:00',
                'phone' => '123456789',
            ],
            [
                'description' => 'Test przegląd',
                'dueDate' => '2025-04-26 10:00:00',
                'phone' => '987654321',
            ],
        ];

        $result = $this->accidentService->processAccidents($accidents);

        $this->assertEquals([
            'processedDataCount' => 2,
            'servicesCount' => 1,
            'reportAccidentsCount' => 1,
            'unprocessedAccidentsCount' => 0,
        ], $result);
    }

    public function test_process_accidents_with_priorities()
    {
        $accidents = [
            [
                'description' => 'Test bardzo pilne',
                'dueDate' => '2025-04-25 10:00:00',
                'phone' => '123456789',
            ],
            [
                'description' => 'Test pilne',
                'dueDate' => '2025-04-26 10:00:00',
                'phone' => '987654321',
            ],
            [
                'description' => 'Test normalny',
                'dueDate' => '2025-04-27 10:00:00',
                'phone' => '555555555',
            ],
        ];

        $result = $this->accidentService->processAccidents($accidents);

        Storage::disk('public')->assertExists('reportAccidents.json');
        $reportAccidentsJson = Storage::disk('public')->get('reportAccidents.json');
        $reportAccidents = json_decode($reportAccidentsJson, true);

        $this->assertCount(3, $reportAccidents);

        // Check priorities assigned correctly
        $priorities = array_column($reportAccidents, 'priority');
        $this->assertContains(PriorityEnum::CRITICAL->value, $priorities);
        $this->assertContains(PriorityEnum::HIGH->value, $priorities);
        $this->assertContains(PriorityEnum::NORMAL->value, $priorities);
    }

    public function test_process_accidents_without_due_date()
    {
        $accidents = [
            [
                'description' => 'Test przegląd',
                'dueDate' => null,
                'phone' => '123456789',
            ],
            [
                'description' => 'Test awarii',
                'dueDate' => null,
                'phone' => '987654321',
            ],
        ];

        $result = $this->accidentService->processAccidents($accidents);

        Storage::disk('public')->assertExists('services.json');
        Storage::disk('public')->assertExists('reportAccidents.json');

        $servicesJson = Storage::disk('public')->get('services.json');
        $reportAccidentsJson = Storage::disk('public')->get('reportAccidents.json');

        $services = json_decode($servicesJson, true);
        $reportAccidents = json_decode($reportAccidentsJson, true);

        // Check that status is NEW when there's no due date
        $this->assertEquals(StatusEnum::NEW->value, $services[0]['status']);
        $this->assertEquals(StatusEnum::NEW->value, $reportAccidents[0]['status']);
    }

    public function test_get_type_method()
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->accidentService);
        $method = $reflection->getMethod('getType');
        $method->setAccessible(true);

        $serviceAccident = ['description' => 'Test przegląd'];
        $reportAccident = ['description' => 'Test awarii'];

        $this->assertEquals(AccidentEnum::SERVICE, $method->invoke($this->accidentService, $serviceAccident));
        $this->assertEquals(AccidentEnum::REPORT_ACCIDENT, $method->invoke($this->accidentService, $reportAccident));
    }

    public function test_get_date_method()
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->accidentService);
        $method = $reflection->getMethod('getDate');
        $method->setAccessible(true);

        $date = '2025-04-25 10:00:00';
        $result = $method->invoke($this->accidentService, $date);

        $carbonDate = Carbon::parse($date);

        $this->assertEquals([
            'date' => '2025-04-25',
            'week' => $carbonDate->week,
        ], $result);

        // Test with null
        $result = $method->invoke($this->accidentService, null);
        $this->assertNull($result);
    }

    public function test_get_priority_method()
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->accidentService);
        $method = $reflection->getMethod('getPriority');
        $method->setAccessible(true);

        $criticalAccident = ['description' => 'Test bardzo pilne'];
        $highAccident = ['description' => 'Test pilne'];
        $normalAccident = ['description' => 'Test normalny'];

        $this->assertEquals(PriorityEnum::CRITICAL, $method->invoke($this->accidentService, $criticalAccident));
        $this->assertEquals(PriorityEnum::HIGH, $method->invoke($this->accidentService, $highAccident));
        $this->assertEquals(PriorityEnum::NORMAL, $method->invoke($this->accidentService, $normalAccident));
    }
}
