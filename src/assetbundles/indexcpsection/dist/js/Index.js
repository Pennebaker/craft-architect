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
    new Clipboard('[data-clipboard-target]');

    // The Architect Loaded
    $('#allSites').on('change', function(e) {
      if ($(this).is(':checked')) {
        $('.sites [id^="site"]:not(:disabled)').prop('checked', true);
        $('.sites [id^="site"]:not(:disabled)').change();
      } else {
        $('.sites [id^="site"]:not(:disabled)').prop('checked', false);
        $('.sites [id^="site"]:not(:disabled)').change();
      }
    });
    $('.sites [id^="site"]:not(:disabled)').on('change', function(e) {
      if ($(this).is(':checked')) {
        if ($('.sites [id^="site"]:checked:not(:disabled)').length == $('.sites [id^="site"]:not(:disabled)').length) {
          $('#allSites').prop('checked', true);
        }
      } else {
        $('#allSites').prop('checked', false);
      }
    });

    $('#allSections').on('change', function(e) {
      if ($(this).is(':checked')) {
        $('.sections [id^="section"]:not(:disabled)').prop('checked', true);
        $('.sections [id^="section"]:not(:disabled)').change();
      } else {
        $('.sections [id^="section"]:not(:disabled)').prop('checked', false);
        $('.sections [id^="section"]:not(:disabled)').change();
      }
    });
    $('.sections [id^="section"]:not(:disabled)').on('change', function(e) {
      if ($(this).is(':checked')) {
        if ($('.sections [id^="section"]:checked:not(:disabled)').length == $('.sections [id^="section"]:not(:disabled)').length) {
          $('#allSections').prop('checked', true);
        }
      } else {
        $('#allSections').prop('checked', false);
      }
    });

    $('#allRoutes').on('change', function(e) {
      if ($(this).is(':checked')) {
        $('.routes [id^="route"]:not(:disabled)').prop('checked', true);
        $('.routes [id^="route"]:not(:disabled)').change();
      } else {
        $('.routes [id^="route"]:not(:disabled)').prop('checked', false);
        $('.routes [id^="route"]:not(:disabled)').change();
      }
    });
    $('.routes [id^="route"]:not(:disabled)').on('change', function(e) {
      if ($(this).is(':checked')) {
        if ($('.routes [id^="route"]:checked:not(:disabled)').length == $('.routes [id^="route"]:not(:disabled)').length) {
          $('#allRoutes').prop('checked', true);
        }
      } else {
        $('#allRoutes').prop('checked', false);
      }
    });

    $('#allFields').on('change', function(e) {
      if ($(this).is(':checked')) {
        $('.fields [id^="field"]:not(:disabled)').prop('checked', true);
        $('.fields [id^="field"]:not(:disabled)').change();
      } else {
        $('.fields [id^="field"]:not(:disabled)').prop('checked', false);
        $('.fields [id^="field"]:not(:disabled)').change();
      }
    });
    $('.fields [id^="field"]:not(:disabled)').on('change', function(e) {
      if ($(this).is(':checked')) {
        if ($('.fields [id^="field"]:checked:not(:disabled)').length == $('.fields [id^="field"]:not(:disabled)').length) {
          $('#allFields').prop('checked', true);
        }
      } else {
        $('#allFields').prop('checked', false);
      }
    });

    $('#allVolumes').on('change', function(e) {
      if ($(this).is(':checked')) {
        $('.volumes [id^="volume"]:not(:disabled)').prop('checked', true);
        $('.volumes [id^="volume"]:not(:disabled)').change();
      } else {
        $('.volumes [id^="volume"]:not(:disabled)').prop('checked', false);
        $('.volumes [id^="volume"]:not(:disabled)').change();
      }
    });
    $('.volumes [id^="volume"]:not(:disabled)').on('change', function(e) {
      if ($(this).is(':checked')) {
        if ($('.volumes [id^="volume"]:checked:not(:disabled)').length == $('.volumes [id^="volume"]:not(:disabled)').length) {
          $('#allVolumes').prop('checked', true);
        }
      } else {
        $('#allVolumes').prop('checked', false);
      }
    });

    $('#allAssetTransforms').on('change', function(e) {
      if ($(this).is(':checked')) {
        $('.assetTransforms [id^="assetTransform"]:not(:disabled)').prop('checked', true);
        $('.assetTransforms [id^="assetTransform"]:not(:disabled)').change();
      } else {
        $('.assetTransforms [id^="assetTransform"]:not(:disabled)').prop('checked', false);
        $('.assetTransforms [id^="assetTransform"]:not(:disabled)').change();
      }
    });
    $('.assetTransforms [id^="assetTransform"]:not(:disabled)').on('change', function(e) {
      if ($(this).is(':checked')) {
        if ($('.assetTransforms [id^="assetTransform"]:checked:not(:disabled)').length == $('.assetTransforms [id^="assetTransform"]:not(:disabled)').length) {
          $('#allAssetTransforms').prop('checked', true);
        }
      } else {
        $('#allAssetTransforms').prop('checked', false);
      }
    });

    $('#allGlobals').on('change', function(e) {
      if ($(this).is(':checked')) {
        $('.globals [id^="global"]:not(:disabled)').prop('checked', true);
        $('.globals [id^="global"]:not(:disabled)').change();
      } else {
        $('.globals [id^="global"]:not(:disabled)').prop('checked', false);
        $('.globals [id^="global"]:not(:disabled)').change();
      }
    });
    $('.globals [id^="global"]:not(:disabled)').on('change', function(e) {
      if ($(this).is(':checked')) {
        if ($('.globals [id^="global"]:checked:not(:disabled)').length == $('.globals [id^="global"]:not(:disabled)').length) {
          $('#allGlobals').prop('checked', true);
        }
      } else {
        $('#allGlobals').prop('checked', false);
      }
    });

    $('#allCategories').on('change', function(e) {
      if ($(this).is(':checked')) {
        $('.categories [id^="category"]:not(:disabled)').prop('checked', true);
        $('.categories [id^="category"]:not(:disabled)').change();
      } else {
        $('.categories [id^="category"]:not(:disabled)').prop('checked', false);
        $('.categories [id^="category"]:not(:disabled)').change();
      }
    });
    $('.categories [id^="category"]:not(:disabled)').on('change', function(e) {
      if ($(this).is(':checked')) {
        if ($('.categories [id^="category"]:checked:not(:disabled)').length == $('.categories [id^="category"]:not(:disabled)').length) {
          $('#allCategories').prop('checked', true);
        }
      } else {
        $('#allCategories').prop('checked', false);
      }
    });

    $('#allRoutes').on('change', function(e) {
      if ($(this).is(':checked')) {
        $('.routes [id^="route"]:not(:disabled)').prop('checked', true);
        $('.routes [id^="route"]:not(:disabled)').change();
      } else {
        $('.routes [id^="route"]:not(:disabled)').prop('checked', false);
        $('.routes [id^="route"]:not(:disabled)').change();
      }
    });
    $('.routes [id^="route"]:not(:disabled)').on('change', function(e) {
      if ($(this).is(':checked')) {
        if ($('.routes [id^="route"]:checked:not(:disabled)').length == $('.routes [id^="route"]:not(:disabled)').length) {
          $('#allRoutes').prop('checked', true);
        }
      } else {
        $('#allRoutes').prop('checked', false);
      }
    });

    $('#allTags').on('change', function(e) {
      if ($(this).is(':checked')) {
        $('.tags [id^="tag"]:not(:disabled)').prop('checked', true);
        $('.tags [id^="tag"]:not(:disabled)').change();
      } else {
        $('.tags [id^="tag"]:not(:disabled)').prop('checked', false);
        $('.tags [id^="tag"]:not(:disabled)').change();
      }
    });
    $('.tags [id^="tag"]:not(:disabled)').on('change', function(e) {
      if ($(this).is(':checked')) {
        if ($('.tags [id^="tag"]:checked:not(:disabled)').length == $('.tags [id^="tag"]:not(:disabled)').length) {
          $('#allTags').prop('checked', true);
        }
      } else {
        $('#allTags').prop('checked', false);
      }
    });

    $('#allUsers').on('change', function(e) {
      if ($(this).is(':checked')) {
        $('.users [id^="user"]:not(:disabled)').prop('checked', true);
        $('.users [id^="user"]:not(:disabled)').change();
      } else {
        $('.users [id^="user"]:not(:disabled)').prop('checked', false);
        $('.users [id^="user"]:not(:disabled)').change();
      }
    });
    $('.users [id^="user"]:not(:disabled)').on('change', function(e) {
      if ($(this).is(':checked')) {
        if ($('.users [id^="user"]:checked:not(:disabled)').length == $('.users [id^="user"]:not(:disabled)').length) {
          $('#allUsers').prop('checked', true);
        }
      } else {
        $('#allUsers').prop('checked', false);
      }
    });

    $('#allUserGroups').on('change', function(e) {
      if ($(this).is(':checked')) {
        $('.userGroups [id^="userGroup"]:not(:disabled)').prop('checked', true);
        $('.userGroups [id^="userGroup"]:not(:disabled)').change();
      } else {
        $('.userGroups [id^="userGroup"]:not(:disabled)').prop('checked', false);
        $('.userGroups [id^="userGroup"]:not(:disabled)').change();
      }
    });
    $('.userGroups [id^="userGroup"]:not(:disabled)').on('change', function(e) {
      if ($(this).is(':checked')) {
        if ($('.userGroups [id^="userGroup"]:checked:not(:disabled)').length == $('.userGroups [id^="userGroup"]:not(:disabled)').length) {
          $('#allUserGroups').prop('checked', true);
        }
      } else {
        $('#allUserGroups').prop('checked', false);
      }
    });

    $('#allProductTypes').on('change', function(e) {
      if ($(this).is(':checked')) {
        $('.productTypes [id^="productType"]:not(:disabled)').prop('checked', true);
        $('.productTypes [id^="productType"]:not(:disabled)').change();
      } else {
        $('.productTypes [id^="productType"]:not(:disabled)').prop('checked', false);
        $('.productTypes [id^="productType"]:not(:disabled)').change();
      }
    });
    $('.productTypes [id^="productType"]:not(:disabled)').on('change', function(e) {
      if ($(this).is(':checked')) {
        if ($('.productTypes [id^="productType"]:checked:not(:disabled)').length == $('.productTypes [id^="productType"]:not(:disabled)').length) {
          $('#allProductTypes').prop('checked', true);
        }
      } else {
        $('#allProductTypes').prop('checked', false);
      }
    });

    $('[data-fields] [type="checkbox"]').on('change', function(e) {
      var parentRow = $(this).closest('[data-fields]');
      if ($(this).prop('checked')) {
        var utilizedFields = parentRow.data('fields').trim().split(' ');
        utilizedFields.forEach(function(id) {
          $('.fields [data-id="' + id + '"] [type="checkbox"]').prop('checked', true);
          $('.fields [data-id="' + id + '"] [type="checkbox"]').change();
        });
      }
    });

    $('[data-groups] [type="checkbox"]').on('change', function(e) {
      var parentRow = $(this).closest('[data-groups]');
      if ($(this).prop('checked')) {
        var utilizedFields = parentRow.data('groups').trim().split(' ');
        utilizedFields.forEach(function(id) {
          $('.groups [data-id="' + id + '"] [type="checkbox"]').prop('checked', true);
          $('.groups [data-id="' + id + '"] [type="checkbox"]').change();
        });
      }
    });

    $('.field[data-id] [type="checkbox"]').on('change', function(e) {
      var parentRow = $(this).closest('[data-id]');
      var id = parentRow.data('id');
      if (!$(this).prop('checked')) {
        $('[data-fields*="' + id + '"] [type="checkbox"]').prop('checked', false);
        $('[data-fields*="' + id + '"] [type="checkbox"]').change();
      }
    });

    var checkboxes = document.querySelectorAll('#fields [type="checkbox"]');
    function canExport() {
      var somethingChecked = (document.querySelectorAll('#fields [type="checkbox"]:checked').length > 0);
      var submit = document.querySelector('#header [type="submit"]');
      if (submit) {
        if (somethingChecked) {
          removeClass('disabled', submit);
          submit.removeAttribute('disabled');
        } else {
          addClass('disabled', submit);
          submit.setAttribute('disabled', true);
        }
      }
    }
    if (window.location.pathname.endsWith('architect/export')) {
      canExport();
      Object.keys(checkboxes).forEach(function (k) {
        var checkbox = checkboxes[k];
        checkbox.addEventListener('change', canExport);
      });
    }
    var importInput = document.getElementById('importData');
    function canImport() {
      var submit = document.querySelector('#header [type="submit"]');
      if (submit) {
        if (importInput.value) {
          removeClass('disabled', submit);
          submit.removeAttribute('disabled');
        } else {
          addClass('disabled', submit);
          submit.setAttribute('disabled', true);
        }
      }
    }
    if (window.location.pathname.endsWith('architect/import')) {
      canImport();
      importInput.addEventListener('change', canImport);
      importInput.addEventListener('keyup', canImport);
      importInput.addEventListener('paste', canImport);
    }

    /*
        $('#similarFields tbody tr').each(function() {
            var leftEle = $(this).find('td:first-child > pre');
            var rightEle = $(this).find('td:last-child > pre');

            var leftStr = leftEle.html();
            var rightStr = rightEle.html();

            var diff = JsDiff.diffLines(leftStr, rightStr);

            diff.forEach(function(_diff) {
                if (_diff.removed) {
                    leftStr = leftStr.replace(_diff.value, '<span class="highlight">' + _diff.value + '</span>');
                }
                if (_diff.added) {
                    rightStr = rightStr.replace(_diff.value, '<span class="highlight">' + _diff.value + '</span>');
                }
            });

            leftEle.html(leftStr);
            rightEle.html(rightStr);
        });
     */
  });
})(jQuery);
