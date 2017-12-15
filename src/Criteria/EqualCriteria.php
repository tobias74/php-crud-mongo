<?php 
namespace Zeitfaden\MongoDb\Criteria;


class EqualCriteria extends ComparisonCriteria
{
  public function acceptVisitor($visitor)
  {
    $visitor->visitEqualCriteria($this);
  }
}

