<?php

namespace Data\Service;

use Faker\Generator;
use Zend\Http\Request;

class GenerateService
{
    const OPPORTUNITY = 'opportunity';
    const EVENT = 'event';
    /** @var ConnectionService */
    private $connection;
    /** @var Generator */
    private $faker;

    public function __construct(ConnectionService $connection, Generator $faker)
    {
        $this->connection = $connection;
        $this->faker = $faker;
    }

    /**
     * @param string $index
     * @param int    $number
     */
    public function generate($index, $number)
    {
        switch ($index) {
            case 'opportunity':
                $this->generateOpportunities($number);
                break;
            case 'event':
                $this->generateEvents($number);
                break;
            case 'all':
            default:
                $this->generateOpportunities($number);
                $this->generateEvents($number);
        }
    }

    private function generateRandomArray()
    {
        $number = $this->faker->numberBetween(1, 5);

        $types = [];
        for ($i = 0; $i < $number; $i++) {
            $types[] = $this->faker->words($this->faker->numberBetween(1, 5));
        }
        return $types;
    }

    private function generateOpportunities($number)
    {
        for ($i = 0; $i < $number; $i++) {
            $params = [
                'id'              => $this->faker->bothify('???##########'),
                'name'            => $this->faker->name,
                'type'            => $this->faker->randomElement(['Request', 'Offering']),
                'country'         => $this->faker->countryCode,
                'date'            => $this->faker->dateTimeThisMonth,
                'types'           => $this->generateRandomArray(),
                'description'     => $this->faker->paragraph($this->faker->numberBetween(1, 15)),
                'expertise'       => $this->faker->paragraph($this->faker->numberBetween(1, 15)),
                'advantage'       => $this->faker->paragraph($this->faker->numberBetween(1, 15)),
                'stage'           => $this->faker->words($this->faker->numberBetween(1, 5)),
                'stage_reference' => $this->faker->sentence,
                'ipr'             => $this->faker->words($this->faker->numberBetween(1, 5)),
                'ipr_reference'   => $this->faker->words($this->faker->numberBetween(1, 5)),
            ];
            $this->connection->execute(Request::METHOD_POST, self::OPPORTUNITY, $params);
        }
    }

    private function generateEvents($number)
    {
        for ($i = 0; $i < $number; $i++) {
            $params = [
                'id'          => $i + 1,
                'name'        => $this->faker->name,
                'type'        => $this->faker->word,
                'place'       => $this->faker->country,
                'address'     => $this->faker->address,
                'latitude'    => $this->faker->latitude,
                'longitude'   => $this->faker->longitude,
                'date_from'   => $this->faker->dateTimeThisMonth,
                'date_to'     => $this->faker->dateTimeThisMonth,
                'description' => $this->faker->paragraph($this->faker->numberBetween(1, 15)),
                'attendee'    => $this->faker->paragraph($this->faker->numberBetween(1, 15)),
                'agenda'      => $this->faker->paragraph($this->faker->numberBetween(1, 15)),
                'cost'        => $this->faker->sentence,
                'topics'      => $this->generateRandomArray(),
            ];
            $this->connection->execute(Request::METHOD_POST, self::EVENT, $params);
        }
    }
}
