<?php

namespace Drupal\activity_send_email;

/**
 * Class ReplyTo.
 *
 * @package Drupal\activity_send_email
 */
class ReplyTo {

  protected $replyto;

  protected $noreply;

  protected $filterstring;

  public function __construct($replyto, $noreply, $filterstring) {
    $this->replyto = $replyto;
    $this->noreply = $noreply;
    $this->filterstring = $filterstring;
  }

  /**
   * Return Reply email address.
   *
   * @param null $hash
   * @return string
   */
  public function getAddress($hash = NULL) {
    if ($hash) {
      return $this->getReplyTo($hash);
    }
    else {
      return $this->noreply;
    }
  }

  /**
   * Example output: dmtit6+543543543fdsfdse43+dmitit222@gmail.com
   * @param $hash
   * @return string
   */
  protected function getReplyTo($hash) {
    $address = explode('@', $this->replyto);
    return $address[0] . '+' . $hash . '+' . $this->filterstring . '@' . $address[1];
  }



}
