$(function() {

  $('select[name="member_id"]').change(function (e) {
    var html = $(e.currentTarget).find('option:selected').html();
    var match = html.match(/.*:\s(.+),\s(.+)/);
    var name = '';
    if (match && match[1] !== "Number" && match[1] !== "missing") {
      name = match[2] + ' ' + match[1];
    }
    $('input[name="rider_name"]').val(name);
  });

  $('select[name="equine_id"]').change(function (e) {
    var html = $(e.currentTarget).find('option:selected').html();
    var match = html.match(/.*:\s(.*)/);
    var name = '';
    if (match && match[1] !== "No Number" && match[1] !== "missing") {
      name = match[1];
    }
    $('input[name="equine_name"]').val(name);
  });

	$('.disabled').attr('disabled','disabled');

  var date = new Date();
  var year = date.getFullYear();
  year += 10;

	// Datepicker
	$('.datepicker').datepicker({
		inline: true,
		changeMonth : true,
		changeYear : true,
		yearRange: '1900:' + year,
		dateFormat: 'yy-mm-dd'
	});

	$('.birthdate').datepicker({
		inline: true,
		changeMonth : true,
		changeYear : true,
		yearRange: '1900:2011'
	});

	//hover states on the static widgets
	$('#dialog_link, ul#icons li').hover(
		function() { $(this).addClass('ui-state-hover'); },
		function() { $(this).removeClass('ui-state-hover'); }
	);

	$('#items-per-page').change(function() {
		$(this).closest('form').submit();
	});

	$('#toggle-whitespace').toggle(function() {
		$('.full-width').toggleClass('nowrap');
		$('#toggle-whitespace').html('View Comfortable');
	}, function() {
		$('.full-width').toggleClass('nowrap');
		$('#toggle-whitespace').html('View Compact');
	});
	
  $('.reset-active-members').click(function () {
    return confirm('Are you sure you want to set all members to inactive?');
  });
});
