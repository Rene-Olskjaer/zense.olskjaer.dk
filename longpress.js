var node = document.getElementsByTagName("tr");
var longpress = false;
var presstimer = null;
var longtarget = null;

var cancel = function(e) {
    if (presstimer !== null) {
        clearTimeout(presstimer);
        presstimer = null;
    }
    
    this.classList.remove("longpress");
};

var click = function(e) {
    if (presstimer !== null) {
        clearTimeout(presstimer);
        presstimer = null;
  var x = e.clientX, y = e.clientY,
       elem = document.elementFromPoint(x, y);
    if (elem.id) { 
        window.location="edit.php?id="+elem.id;
    }

    }
    
    this.classList.remove("longpress");
    
    if (longpress) {
        return false;
    }
};

var start = function(e) {
    if (e.type === "click" && e.button !== 0) {
        return;
    }
    
    longpress = false;
    
    this.classList.add("longpress");
    
    presstimer = setTimeout(function() {
      var x = e.clientX, y = e.clientY,
       elem = document.elementFromPoint(x, y);
        longpress = true;
        window.location="edit.php?id="+elem.id;
    }, 2500);
    
    return false;
};


for (var i = 0; i < node.length; i++) {
  node[i].addEventListener("mousedown", start);
  node[i].addEventListener("touchstart", start);
  node[i].addEventListener("click", click);
  node[i].addEventListener("mouseout", cancel);
  node[i].addEventListener("touchend", cancel);
  node[i].addEventListener("touchleave", cancel);
  node[i].addEventListener("touchcancel", cancel);
}
