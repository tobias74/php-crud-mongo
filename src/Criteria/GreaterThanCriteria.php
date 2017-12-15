<?php 
namespace PhpCrudMongo\Criteria;


class GreaterThanCriteria extends ComparisonCriteria
{
  public function acceptVisitor($visitor)
  {
    $visitor->visitGreaterThanCriteria($this);
  }
}
