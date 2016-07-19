<?php

namespace Console\Service;

use Faker\Generator;
use Zend\Http\Request;

class GenerateService
{
    const OPPORTUNITY = 'opportunity';
    const EVENT = 'event';
    const ALL = 'all';

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
            case self::OPPORTUNITY:
                $this->generateOpportunities($number);
                break;
            case self::EVENT:
                $this->generateEvents($number);
                break;
            case self::ALL:
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
            $types[] = $this->faker->sentence($this->faker->numberBetween(1, 5));
        }

        return $types;
    }

    private function generateDate(\DateTime $date)
    {
        return [
            'date' => $date->format('d-m-Y'),
            'timestamp' => $date->getTimestamp(),
            'timezone' => $date->getTimezone()->getName(),
        ];
    }

    private function generateOpportunities($number)
    {
        for ($i = 0; $i < $number; $i++) {
            $params = [
                'id'               => $this->faker->bothify('???##########'),
                'name'             => $this->faker->name,
                'type'             => $this->faker->randomElement(['Request', 'Offering']),
                'opportunity_type' => $this->faker->randomElement(['Technology', 'Commercial', 'Research']),
                'country'          => $this->faker->countryCode,
                'date'             => $this->generateDate($this->faker->dateTimeBetween('-12 months', 'now')),
                'types'            => $this->generateRandomArray(),
                'description'      => $this->faker->paragraph($this->faker->numberBetween(1, 15)),
                'expertise'        => $this->faker->paragraph($this->faker->numberBetween(1, 15)),
                'advantage'        => $this->faker->paragraph($this->faker->numberBetween(1, 15)),
                'stage'            => $this->faker->sentence($this->faker->numberBetween(1, 5)),
                'stage_reference'  => $this->faker->sentence,
                'ipr'              => $this->faker->sentence($this->faker->numberBetween(1, 5)),
                'ipr_reference'    => $this->faker->sentence($this->faker->numberBetween(1, 5)),
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
                'type'        => $this->faker->randomElement(['Seminar', 'Brokerage Event', 'Match-making Event', 'Conference']),
                'place'       => $this->faker->country,
                'address'     => $this->faker->address,
                'latitude'    => $this->faker->latitude,
                'longitude'   => $this->faker->longitude,
                'date_from'   => $this->generateDate($this->faker->dateTimeBetween('now', '+2 days')),
                'date_to'     => $this->generateDate($this->faker->dateTimeBetween('+2 days', '+5 days')),
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
