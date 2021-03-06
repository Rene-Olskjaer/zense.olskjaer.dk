
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
    options.url = '/zenseonoff.php';
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
 }

function SetVal(id,val) {
    var form = this.form;
    var options = {};
    options.url = '/zenseonoff.php';
    options.type = 'post';
    if (val==0) {
       console.log('zenseoff '+id);
       options.data = {function2call: 'zenseoff',id:id};
    } else
    {
       console.log('fade '+id+':'+val);
       options.data = {function2call: 'fade',val:val,id:id};
    }
    options.success = function () {
            if (val==0) {
              $("#"+id).attr('src','images/light_off-100.png');
            } else {
              $("#"+id).attr('src','images/light_on-100.png');

            }
        }
    $.ajax(options);
 };
