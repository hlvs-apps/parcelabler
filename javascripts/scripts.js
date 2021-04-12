
var clipboard="";

function loadFile(filePath) {
  var result = null;
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.open("GET", filePath, false);
  xmlhttp.send();
  if (xmlhttp.status==200) {
    result = xmlhttp.responseText;
  }
  return result;
}

function fallbackCopyTextToClipboard(text) {
    var textArea = document.createElement("textarea");
    textArea.value = text;

    // Avoid scrolling to bottom
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
      var successful = document.execCommand('copy');
      var msg = successful ? 'successful' : 'unsuccessful';
      console.log('Fallback: Copying text command was ' + msg);
    } catch (err) {
      console.error('Fallback: Oops, unable to copy', err);
    }

    document.body.removeChild(textArea);
}

function copyTextToClipboard() {
    var text=clipboard;
   if (!navigator.clipboard) {
      fallbackCopyTextToClipboard(text);
      return;
  }
  navigator.clipboard.writeText(text).then(function() {
     console.log('Async: Copying to clipboard was successful!');
  }, function(err) {
      console.error('Async: Could not copy text: ', err);
  });
}

function postRequestFields(){
        var val = document.getElementById("file").value;
        if (val != "") {
            var xhttp = new XMLHttpRequest();
             xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                  document.getElementById('fields').innerHTML = this.responseText;
                }
             };
            xhttp.open("POST", "getfields.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            var request=JSON.stringify({
                                   "file": val
                                 });
            xhttp.send(request);
        }
}

function postBuild(){
        var val = document.getElementById("file").value;
        if (val != "") {
            var xhttp = new XMLHttpRequest();
             xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                  var obj = JSON.parse(this.responseText);
                  document.getElementById('errors').innerHTML = obj.error;
                  var buildShow="";
                  if(obj.build!="empty"){
                    buildShow=obj.build;
                    document.getElementById("copy-text").style.display = "block";
                  }else{
                  document.getElementById("copy-text").style.display = "none";
                  }
                  document.getElementById('output').innerHTML = buildShow;
                  clipboard=document.getElementById('output').textContent;
                  hljs.highlightAll();
                }
             };
            xhttp.open("POST", "build.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            var request=JSON.stringify({
                                   "file": val
                                 });
            xhttp.send(request);
        }
}

$(document).ready(function () {
    document.getElementById("file").addEventListener("input", (event) => postRequestFields());
    document.getElementById("submit").addEventListener("click", (event) => postBuild());
    document.getElementById("copy-text").addEventListener("click", (event) => copyTextToClipboard());
    document.getElementById("copy-text").style.display = "none";
    postRequestFields();
});
