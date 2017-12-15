<?php 
namespace PhpCrudMongo\Criteria;




class NotEqualCriteria extends ComparisonCriteria
{
  public function acceptVisitor($visitor)
  {
    $visitor->visitNotEqualCriteria($this);
  }
}