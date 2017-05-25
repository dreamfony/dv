(function ($) {

  'use strict';

  Drupal.behaviors.commentBox = {
    attach: function (context, settings) {

      var $comment_box_comments = $('.comment-box-comments');

      $comment_box_comments.collapse().removeClass('in');

      $('.comment-box-toggle', context).click(function () {
        $comment_box_comments.toggle();
      });
    }
  };

})(jQuery);
