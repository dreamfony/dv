<?php

namespace Todaymade\Daux\Extension;

use Todaymade\Daux\Tree\Root;
use League\CommonMark\Environment;

class Processor extends \Todaymade\Daux\Processor
{

  public function extendCommonMarkEnvironment(Environment $environment)
  {
    //print_r($environment);
    $test = 1;
    $environment->addInlineRenderer('Link', $this->getLinkRenderer($environment));
  }

  protected function getLinkRenderer(Environment $environment)
  {
    return new LinkRenderer($environment->getConfig('daux'));
  }

}
