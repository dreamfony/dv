<?php

namespace Drupal\dv_survey;

use Drupal\Component\Serialization\Yaml;
use Drupal\webform\Entity\Webform;
use Drupal\Core\Entity\EntityInterface;

/**
 * Class SurveyGenerate
 *
 * @package Drupal\dv_survey
 */
class SurveyGenerate {

  /**
   * Create.
   *
   */
  public function generate(EntityInterface $entity) {

    // get questions from $entity
    $node_questions = $entity->get('field_s_questions')->getValue();

    // convert to array
    foreach ($node_questions as $delta => $question) {
      $questions['q'.$delta] = [
        '#title' => $question['value'],
        '#type' => 'textarea',
        '#required' => FALSE
      ];
    }

    // encode yml
    $elements = Yaml::encode($questions);

    // create from
    $survey = Webform::create([
      'id' => 's-'.$entity->getCreatedTime().'-'.$entity->getOwnerId(),
      'title' => $entity->label(),
      'elements' => $elements
    ]);

    $survey->save();

    return $survey->id();

  }

}
