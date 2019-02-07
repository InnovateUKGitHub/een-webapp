<?php
/**
 * @file
 *
 * Views will look in this file for any views related hooks. Mainly, this is
 * where you put your hook_views_data() implementation.
 */

function een_common_views_data_alter(&$data) {
  $data['node_field_data']['node_created_filter'] = [
    'title' => t('Month/Year Filter'),
    'filter' => [
      'title' => t('Month/Year Filter'),
      'field' => 'created',
      'id' => 'een_common_node_created_filter',
      'help' => t('Custom view filter to allow month/year selections'),
    ],
  ];
}
