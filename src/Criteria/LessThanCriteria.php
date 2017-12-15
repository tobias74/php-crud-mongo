<?php 
namespace PhpCrudMongo\Criteria;


class LessThanCriteria extends ComparisonCriteria
{
  public function acceptVisitor($visitor)
  {
    $visitor->visitLessThanCriteria($this);
  }
}

