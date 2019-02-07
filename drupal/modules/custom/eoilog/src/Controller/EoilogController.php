<?php

namespace Drupal\eoilog\Controller;

use Drupal\Core\Controller\ControllerBase;
use \Drupal\eoilog\EoiLogStorage;


/**
 * Controller for DBTNG Example.
 */
class EoilogController extends ControllerBase {

  /**
   * Render a list of entries in the database.
   */
  public function eoiList() {
    $content = [];

    $content['message'] = [
      '#markup' => $this->t('Generate a list of all entries in the database. There is no filter in the query.'),
    ];

    $rows = [];
    $headers = [t('-'), t('-'), t('Initial Log time'), t('Email'),  t('Token'), t('Code sent'), t('Code delivered'), t('Code verified')];

    foreach ($entries = EoiLogStorage::load() as $entry) {
      // Sanitize each entry.
      $rows[] = array_map('Drupal\Component\Utility\SafeMarkup::checkPlain', (array) $entry);
    }
    $content['table'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
      '#empty' => t('No entries available.'),
    ];
    // Don't cache this page.
    $content['#cache']['max-age'] = 0;

    return $content;
  }
}
