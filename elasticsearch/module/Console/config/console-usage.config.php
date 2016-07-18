<?php

return [
    'generate [--index=<index>] [--number=<number>]' => 'Generate random data into elasticSearch for test purpose',
    ['--index', 'Index to generate. [opportunity|event|all] (default: all)'],
    ['--number', 'Number of documents to generate. (default: 10)'],
    'delete [--index=<index>]' => 'Delete elasticSearch index type',
    ['--index', 'Index to delete. [opportunity|event|all] (default: all)'],
];
