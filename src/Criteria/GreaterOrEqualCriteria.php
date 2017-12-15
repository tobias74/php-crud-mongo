<?php 
namespace PhpCrudMongo\Criteria;


class GreaterOrEqualCriteria extends ComparisonCriteria
{
  public function acceptVisitor($visitor)
  {
    $visitor->visitGreaterOrEqualCriteria($this);
  }
}


