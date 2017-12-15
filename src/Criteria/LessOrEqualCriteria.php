<?php 
namespace PhpCrudMongo\Criteria;



class LessOrEqualCriteria extends ComparisonCriteria
{
  public function acceptVisitor($visitor)
  {
    $visitor->visitLessOrEqualCriteria($this);
  }
}
