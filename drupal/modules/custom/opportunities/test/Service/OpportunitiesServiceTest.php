<?php
namespace Drupal\opportunities\Test\Service;

use Drupal\service_connection\Service\HttpService;
use Drupal\opportunities\Service\OpportunitiesService;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \Drupal\opportunities\Service\OpportunitiesService
 */
class OpportunitiesServiceTest extends UnitTestCase
{
    /** @var HttpService|\PHPUnit_Framework_MockObject_MockObject */
    private $mockService;
    /** @var OpportunitiesService */
    private $service;

    public function testSearch()
    {
        $this->mockService->expects(self::once())
            ->method('execute')
            ->with(
                Request::METHOD_POST,
                'opportunities',
                [
                    'from'             => 0,
                    'size'             => 10,
                    'search'           => 'H2020',
                    'opportunity_type' => ['BO'],
                    'country'          => ['FR'],
                    'source'           => ['type', 'title', 'summary', 'date', 'country', 'country_code'],
                    'type'             => 1,
                ]
            )
            ->willReturn(['total' => 0]);

        $form = [];
        self::assertEquals(
            ['total' => 0, 'results' => []],
            $this->service->search($form, 'H2020', ['BO'], ['FR'], 1, 10)
        );

        self::assertEquals([
            'search'           => [
                '#value' => 'H2020',
            ],
            'opportunity_type' => [
                'BO' => [
                    '#attributes' => [
                        'checked' => 'checked',
                    ],
                ],
            ],
            'country'          => [
                'FR' => [
                    '#attributes' => [
                        'checked' => 'checked',
                    ],
                ],
            ],
        ], $form);
    }

    public function testGet()
    {
        $this->mockService->expects(self::once())
            ->method('execute')
            ->with(Request::METHOD_GET, 'opportunities/1')
            ->willReturn(['success' => true]);

        self::assertEquals(['success' => true], $this->service->get(1));
    }

    protected function Setup()
    {
        $this->mockService = self::getMock(HttpService::class, [], [], '', false);
        $this->service = new OpportunitiesService($this->mockService);

        parent::setUp();
    }
}