// NOTICE!! DO NOT USE ANY OF THIS JAVASCRIPT
// IT'S ALL JUST JUNK FOR OUR DOCS!
// ++++++++++++++++++++++++++++++++++++++++++

!function ($) {

  $(function(){
    jQuery.fn.exists = function(){return this.length>0;}

    if ( $('#timepicker1').exists()) {
        $('#timepicker1').timepicker();
    }

    if ( $('#timepicker2').exists()) {
        $('#timepicker2').timepicker();
    }

    if ( $('#begin_date').exists()) {
        $('#begin_date').datepicker();    
    }

    if ( $('#end_date').exists()) {
        $('#end_date').datepicker();
    }
    $('tr[rel=tooltip]').tooltip()

    $('.tooltip-test').tooltip()
    $('.popover-test').popover()

    // popover demo
    $("select[rel=popover]")
      .popover()
      .click(function(e) {
        e.preventDefault()
      })

    $("a[rel=popover]")
      .popover()
      .click(function(e) {
        e.preventDefault()
      })

    $("input[rel=popover]")
      .popover()
      .click(function(e) {
        e.preventDefault()
      })

    $("textarea[rel=popover]")
      .popover()
      .click(function(e) {
        e.preventDefault()
      })

   if ($('fileupload').exists()){
        $('fileupload').fileupload()
   }

    // request built javascript
    $('.download-btn').on('click', function () {

      var css = $("#components.download input:checked")
            .map(function () { return this.value })
            .toArray()
        , js = $("#plugins.download input:checked")
            .map(function () { return this.value })
            .toArray()
        , vars = {}
        , img = ['glyphicons-halflings.png', 'glyphicons-halflings-white.png']

    $("#variables.download input")
      .each(function () {
        $(this).val() && (vars[ $(this).prev().text() ] = $(this).val())
      })

      $.ajax({
        type: 'POST'
      , url: /\?dev/.test(window.location) ? 'http://localhost:3000' : 'http://bootstrap.herokuapp.com'
      , dataType: 'jsonpi'
      , params: {
          js: js
        , css: css
        , vars: vars
        , img: img
      }
      })
    })
  })

// Modified from the original jsonpi https://github.com/benvinegar/jquery-jsonpi
$.ajaxTransport('jsonpi', function(opts, originalOptions, jqXHR) {
  var url = opts.url;

  return {
    send: function(_, completeCallback) {
      var name = 'jQuery_iframe_' + jQuery.now()
        , iframe, form

      iframe = $('<iframe>')
        .attr('name', name)
        .appendTo('head')

      form = $('<form>')
        .attr('method', opts.type) // GET or POST
        .attr('action', url)
        .attr('target', name)

      $.each(opts.params, function(k, v) {

        $('<input>')
          .attr('type', 'hidden')
          .attr('name', k)
          .attr('value', typeof v == 'string' ? v : JSON.stringify(v))
          .appendTo(form)
      })

      form.appendTo('body').submit()
    }
  }
})

}(window.jQuery)
