<?php

namespace Drupal\ds;

use Drupal\Core\Entity\Display\EntityDisplayInterface;
use Drupal\Core\Entity\EntityInterface;

interface EntityViewAlterInterface {

  /**
   * Entity View Alter.
   *
   * @param $build
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param \Drupal\Core\Entity\Display\EntityDisplayInterface $display
   */
  public function entityViewAlter(&$build, EntityInterface $entity, EntityDisplayInterface $display);

}