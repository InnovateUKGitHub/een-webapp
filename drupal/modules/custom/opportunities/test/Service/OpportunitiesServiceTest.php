<?php
namespace Drupal\opportunities\Test\Service;

use Drupal\elastic_search\Service\ElasticSearchService;
use Drupal\opportunities\Service\OpportunitiesService;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers Drupal\opportunities\Service\OpportunitiesService
 */
class OpportunitiesServiceTest extends UnitTestCase
{
    /** @var ElasticSearchService|\PHPUnit_Framework_MockObject_MockObject */
    private $mockService;
    /** @var OpportunitiesService */
    private $service;

    public function testSearch()
    {
        $this->mockService->expects(self::once())
            ->method('setUrl')
            ->with('opportunities')
            ->willReturn($this->mockService);
        $this->mockService->expects(self::once())
            ->method('setBody')
            ->with([
                'from'             => 0,
                'size'             => 10,
                'search'           => 'H2020',
                'opportunity_type' => ['BO' => 'BO'],
                'sort'             => [
                    ['date' => 'desc'],
                ],
                'source'           => ['type', 'title', 'summary'],
            ])
            ->willReturn($this->mockService);
        $this->mockService->expects(self::once())
            ->method('sendRequest')
            ->willReturn(['success' => true]);

        $form = [];
        self::assertEquals(['success' => true], $this->service->search($form, 'H2020', ['BO' => 'BO', 'RD' => '0'], 1, 10));

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
        ], $form);
    }

    public function testGet()
    {
        $this->mockService->expects(self::once())
            ->method('setUrl')
            ->with('opportunities/1')
            ->willReturn($this->mockService);
        $this->mockService->expects(self::once())
            ->method('setMethod')
            ->with(Request::METHOD_GET)
            ->willReturn($this->mockService);
        $this->mockService->expects(self::once())
            ->method('sendRequest')
            ->willReturn(['success' => true]);

        self::assertEquals(['success' => true], $this->service->get(1));
    }

    protected function Setup()
    {
        $this->mockService = self::getMock(ElasticSearchService::class, [], [], '', false);
        $this->service = new OpportunitiesService($this->mockService);

        parent::setUp();
    }
}