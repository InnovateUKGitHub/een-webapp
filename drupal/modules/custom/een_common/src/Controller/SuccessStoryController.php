<?php
namespace Drupal\een_common\Controller;

use Drupal\Core\Controller\ControllerBase;

class SuccessStoryController extends ControllerBase
{
    /**
     * @return array
     */
    public function index()
    {
        return [
            '#theme' => 'success_story',
        ];
    }

    public function page1()
    {
        return [
            '#theme' => 'case_study_1',
        ];
    }

    public function page2()
    {
        return [
            '#theme' => 'case_study_2',
        ];
    }

    public function page3()
    {
        return [
            '#theme' => 'case_study_3',
        ];
    }
}
