function attachCheckboxHandlers() {
    var el = document.getElementById('zense');
    var tops = el.getElementsByTagName('input');
    for (var i=0, len=tops.length; i<len; i++) {
        if ( tops[i].type === 'checkbox' ) {
            tops[i].onclick = updateZense;
        }
    }
}

function updateZense(e) {
    $("#progress").hide();
    var form = this.form;
    var val = this.name;
    var options = {};
    options.url = 'https://zense.olskjaer.dk/zenseonoff.php';
    options.type = 'post';
    options.beforeSend = function () {
      $("#progress").show();
    };
    options.success = function(val) {
      window.location.reload(true);
    };
    if ( this.checked ) {            
       options.data = {function2call: 'zenseon',id:val};
    } else
    {
       options.data = {function2call: 'zenseoff',id:val};
    }
    $.ajax(options);
 };

