// JavaScript Document
$('#nrole').change(function() {
  $("#custom_4")[$(this).val() === "full01_severity_other" ? 'show' : 'hide']("fast");
}).change();
