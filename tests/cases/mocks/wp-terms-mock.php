<?php

namespace MailbizTest;

class WpTermsMock
{

  public $data = [];

  public function addTerm($key, $terms)
  {
    $this->data[$key] = array_merge(
      $this->data[$key] ?: [],
      $terms
    );
  }

  public function getTheTerms($key, $term)
  {
    return $this->data[$key][$term];
  }

}