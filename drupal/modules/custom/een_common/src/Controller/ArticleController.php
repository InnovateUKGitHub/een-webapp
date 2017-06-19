<?php
namespace Drupal\een_common\Controller;

use Drupal\Core\Controller\ControllerBase;

class ArticleController extends ControllerBase
{
    /**
     * @return array
     */
    public function page1()
    {
        return [
            '#theme' => 'article_page1',
        ];
    }
    
    public function page2()
    {
        return [
            '#theme' => 'article_page2',
        ];
    }
    
    public function page3()
    {
        return [
            '#theme' => 'article_page3',
        ];
    }
    
    public function page4()
    {
        return [
            '#theme' => 'article_page4',
        ];
    }
}
