<?php

namespace Drupal\search_backend\Plugin\search_api\backend;

use Drupal\Core\Config\Config;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api\Backend\BackendPluginBase;

/**
 * @SearchApiBackend(
 *   id = "search_backend",
 *   label = @Translation("Backend"),
 *   description = @Translation("Index items using a Search Server.")
 * )
 */
class SearchBackend extends BackendPluginBase
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $configuration, $plugin_id, array $plugin_definition)
    {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
    }

    /**
     * {@inheritdoc}
     */
    public function search(QueryInterface $query)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(IndexInterface $index, array $ids)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAllIndexItems(IndexInterface $index, $datasource_id = NULL)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function indexItems(IndexInterface $index, array $items)
    {
        return [];
    }
}
