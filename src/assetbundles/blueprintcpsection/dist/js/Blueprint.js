/**
 * Architect plugin for Craft CMS
 *
 * Index Field JS
 *
 * @author    Pennebaker
 * @copyright Copyright (c) 2018 Pennebaker
 * @link      https://pennebaker.com
 * @package   Architect
 * @since     2.0.0
 */
(function($) {
  function addClass(className, el) {
    if (el.classList)
      el.classList.add(className);
    else
      el.className += ' ' + className;
  }

  function removeClass(className, el) {
    if (el.classList)
      el.classList.remove(className);
    else
      el.className = el.className.replace(new RegExp('(^|\\b)' + className.split(' ').join('|') + '(\\b|$)', 'gi'), ' ');
  }

  function hasClass(className, el) {
    if (el.classList) {
      return el.classList.contains(className);
    } else {
      return new RegExp('(^| )' + className + '( |$)', 'gi').test(el.className);
    }
  }

  function load(fn) {
    if (document.readyState === 'complete') {
      fn();
    } else {
      document.addEventListener('readystatechange', function () {
        if (document.readyState === 'complete') {
          fn();
        }
      });
    }
  }

  load(function () {
    // The Architect Loaded
    $('.file').on('click', function(e) {
      var content = this.querySelector('.file__content')
      if (hasClass('active', this)) {
        removeClass('active', this);content.style.height = ''
      } else {
        var contentHeight = content.scrollHeight
        content.style.height = contentHeight + 'px'
        addClass('active', this);
      }
    });
  });
})(jQuery);
