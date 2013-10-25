// JavaScript Document showhide deleted

var button = document.getElementById('hideshowleaders'); // Assumes element with id='button'

button.onclick = function() {
    var div = document.getElementById('sedsleadertabledels');
    if (div.style.display !== 'block') {
        div.style.display = 'block';
    }
    else {
        
        div.style.display = 'none';
    }
};