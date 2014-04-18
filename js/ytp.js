/*********************************** 
  Accessible Controls for the YouTube Embedded Video Player
  Copyright (c) 2010 Ken Petri, Web Accessibility Center, The Ohio State University
  
  ** Licensing **
  This work is licensed under the Creative Commons Attribution-Share Alike 3.0 United States License,
  which allows copying, distributing, and adapting this work for all purposes, including commercial
  ones, so long as attribution is maintained and derivative works are similarly licensed.
  Use is otherwise bound by U.S. Copyright law.
  To view a copy of this license, visit http://creativecommons.org/licenses/by-sa/3.0/us/
  This code uses Google YouTube JavaScript Player APIs (http://code.google.com/apis/youtube/js_api_reference.html), 
  which are bound by their Terms of Service: http://code.google.com/apis/youtube/terms.html
  This code relies on SWFObject by Bobby van der Sluis (http://code.google.com/p/swfobject/), 
  which maintains an MIT License: http://www.opensource.org/licenses/mit-license.php
  
  ** Compatibility **
  This code is known to work with Firefox 3+, Opera 9+, Safari 3+, Chrome 2+, IE 6+.
  It has been tested using only the keyboard in Windows and Mac 
  and using the screen readers JAWS 10 (Windows, with Firefox and IE), NVDA (Windows, with Firefox) and VoiceOver (MacOS X.5, with Safari).
************************************/
// Utility functions
function ytTrim(str)
{
  var tempstr = str.replace(new RegExp("^\\s+"), "");
  return tempstr.replace(new RegExp("\\s+$"), "");
}
function ytStopDefaultAction(event)
{
  event.returnValue = false;
  if (typeof event.preventDefault != "undefined") 
  {
    event.preventDefault();
  }
}
// get the instances of the player div to populate
function ytGetInstances()
{
  var elementArray = new Array();
  var matchedArray = new Array();
  if (document.all)
  {
    elementArray = document.all;
  }
  else
  {
    elementArray = document.getElementsByTagName("div");
  }
  for (var i=0; i<elementArray.length; i++ )
  {
    var pattern = new RegExp("ytplayerbox");
    if (pattern.test(elementArray[i].className))
    {
      matchedArray[matchedArray.length] = elementArray[i];
    }
  }
  return matchedArray;
}
// Generic, cross-browser load and event listeners from Edwards and Adams, "JavaScript Anthology"
function ytAddLoadListener(fn){
  if (typeof window.addEventListener != 'undefined') 
  {
    window.addEventListener('load', fn, false);
  }
  else 
    if (typeof document.addEventListener != 'undefined') 
    {
      document.addEventListener('load', fn, false);
    }
    else 
      if (typeof window.attachEvent != 'undefined') 
      {
        window.attachEvent('onload', fn);
      }
      else 
      {
        var oldfn = window.onload;
        if (typeof window.onload != 'function') 
        {
          window.onload = fn;
        }
        else 
        {
          window.onload = function(){
            oldfn();
            fn();
          };
        }
      }
}
function ytAttachEventListener(target, eventType, functionRef, capture)
{
  if (typeof target.addEventListener != "undefined") 
  {
    target.addEventListener(eventType, functionRef, capture);
  }
  else 
    if (typeof target.attachEvent != "undefined") 
    {
      target.attachEvent("on" + eventType, functionRef);
    }
    else 
    {
      eventType = "on" + eventType;
      if (typeof target[eventType] == "function") 
      {
        var oldListener = target[eventType];
        target[eventType] = function(){
          oldListener();
          return functionRef();
        };
      }
      else 
      {
        target[eventType] = functionRef;
      }
    }
  return true;
}
// Rendering functions
function ytPlayerBoxDraw(aspect,ytpbox,pid)
{
  var width = "480px";
  if (aspect == "normal") 
  {
    width = "480px";
  }
  else if (aspect == "wide")
  {
    width = "640px";
  }
  if (ytpbox)
  {
    ytpbox.style.width = width;
    ytpbox.innerHTML =  "<div id=\"ytapiplayer"+ pid +"\">" +
                          "You need Flash player 8+ and JavaScript enabled to view this video." +
                        "<\/div>" +
    										"<h3 class=\"semantic\">Player Controls<\/h3>" +
                       	"<ul class=\"ytplayerbuttons\">" +
                         	"<li><a id=\"ytplaybut"+ pid +"\" href=\"/\">Play<\/a><\/li>" +
                         	"<li><a id=\"ytforwardbut"+ pid +"\" href=\"/\">Forward 20%<\/a><\/li>" +
                         	"<li><a id=\"ytbackbut"+ pid +"\" href=\"/\">Back 20%<\/a><\/li>" +
                         	"<li><a id=\"ytstopbut"+ pid +"\" href=\"/\">Stop<\/a><\/li>" +
                         	"<li><a id=\"ytvolupbut"+ pid +"\" href=\"/\">Volume Up<\/a><\/li>" +
                         	"<li><a id=\"ytvoldownbut"+ pid +"\" href=\"/\">Volume Down<\/a><\/li>" +
                         	"<li><a id=\"ytmutebut"+ pid +"\" href=\"/\">Mute<\/a><\/li>" +
                          "<li><a id=\"ytloopbut"+ pid +"\" href=\"/\">Loop<\/a><\/li>" +
                        "<\/ul>" +
                        "<h4>Currently Playing: <span id=\"ytvidtitle"+ pid +"\"><\/span><\/h4>" +
                        "<h4>Time: <span id=\"ytplayertime"+ pid +"\"><\/span><\/h4>";
  }
}
function ytPlayerInit(list,aspect,ytp,pid)
{
  var width = "480";
  if (aspect == "normal") 
  {
    width = "480";
  }
  else if (aspect = "wide")
  {
    width = "640";
  }
  var ytmovurl = list[0].url;
  if (ytmovurl) 
  {
    var ytpl = document.getElementById("ytplaybut"+ pid);
    ytAttachEventListener(ytpl, "click", function() {ytplay(pid)}, false);
    ytAttachEventListener(ytpl, "click", ytStopDefaultAction, false);
    var ytf = document.getElementById("ytforwardbut"+ pid);
    ytAttachEventListener(ytf, "click", function() {ytforward(pid)}, false);
    ytAttachEventListener(ytf, "click", ytStopDefaultAction, false);
    var ytb = document.getElementById("ytbackbut"+ pid);
    ytAttachEventListener(ytb, "click", function() {ytback(pid)}, false);
    ytAttachEventListener(ytb, "click", ytStopDefaultAction, false);
    var yts = document.getElementById("ytstopbut"+ pid);
    ytAttachEventListener(yts, "click", function() {ytstop(pid)}, false);
    ytAttachEventListener(yts, "click", ytStopDefaultAction, false);
    var ytvu = document.getElementById("ytvolupbut"+ pid);
    ytAttachEventListener(ytvu, "click", function() {ytvolup(pid)}, false);
    ytAttachEventListener(ytvu, "click", ytStopDefaultAction, false);
    var ytvd = document.getElementById("ytvoldownbut"+ pid);
    ytAttachEventListener(ytvd, "click", function() {ytvoldown(pid)}, false);
    ytAttachEventListener(ytvd, "click", ytStopDefaultAction, false);
    var ytm = document.getElementById("ytmutebut"+ pid);
    ytAttachEventListener(ytm, "click", function() {ytmute(pid)}, false);
    ytAttachEventListener(ytm, "click", ytStopDefaultAction, false);
    var ytl = document.getElementById("ytloopbut"+ pid);
    ytAttachEventListener(ytl, "click", function() {ytloop(pid)}, false);
    ytAttachEventListener(ytl, "click", ytStopDefaultAction, false);
    ytPlayerLoad(ytmovurl, width, pid);
    var titlenode = document.getElementById("ytvidtitle"+ pid);
    var titleval = document.createTextNode("\""+ list[0].text +"\"");
    titlenode.appendChild(titleval);
  }
  // only create a playlist if more than one video is specified
  if (list.length > 1) 
  {
    var listH = document.createElement("h3");
    listH.className = "playlisth";
    var listHText = document.createTextNode("Video Play List");
    listH.appendChild(listHText);
    ytp.appendChild(listH);
    var listUl = document.createElement("ul");
    listUl.className = "ytplaylistl";
    ytp.appendChild(listUl);
    for (var i=0; i<list.length; i++)
    {
      var listLi = document.createElement("li");
      if (i%2)
      {
        listLi.className = "ytlisteven";
      }
      else
      {
        listLi.className = "ytlistodd";
      }
      var listLink = document.createElement("a");
      listLink.href = "/";
      listLink.id = list[i].url;
      var listLinkText = document.createTextNode(list[i].text);
      listLink.appendChild(listLinkText);
      listLi.appendChild(listLink);
      listUl.appendChild(listLi);
      /*  Have to use old style event attachment.
          MS IE's event registration model uses attachEvent() method, which creates a reference to the 
          function, instead of a copy (like other browsers addEventListener() method). */
      listLink.onclick = function() {ytLoadNewVideo(pid, this.id, this.firstChild.nodeValue)};
      ytAttachEventListener(listLink, "click", ytStopDefaultAction, false);
    }
  }
}
function ytPlayerDispatch(ytp,ytpid) 
{
  if (ytp) 
  {
    // get movie urls, titles, and other set up variables from page
    var ytPlaylistArray = new Array();
    var ytPlayerAspect = "normal";
    var movpattern = /^ytmovieurl:.*$/;
    var movmatch = /^ytmovieurl:(.*)$/;
    var aspectpattern = /^ytplayeraspect:.*$/;
    var aspectmatch = /^ytplayeraspect:(.*)$/;
    for (var i=0; i<ytp.childNodes.length; i++)
    {
      if (ytp.childNodes[i].className) 
      {
        if (movpattern.test(ytp.childNodes[i].className)) 
        {
          var str = ytp.childNodes[i].className;
          var yturl = ytTrim(str.replace(movmatch, "$1"));
          var yttext = ytTrim(ytp.childNodes[i].firstChild.nodeValue);
          var ytlistobj = {
            url : yturl,
            text : yttext
          };
          ytPlaylistArray.push(ytlistobj);
        }
        if (aspectpattern.test(ytp.childNodes[i].className)) 
        {
          var str = ytp.childNodes[i].className;
          ytPlayerAspect = ytTrim(str.replace(aspectmatch, "$1"));
        }
      }
    }
    ytPlayerBoxDraw(ytPlayerAspect,ytp,ytpid);
    ytPlayerInit(ytPlaylistArray,ytPlayerAspect,ytp,ytpid);
    setInterval(function() { updateButtonState(ytpid); }, 250);
    setInterval(function() { updateTime(ytpid); }, 500);
  }
}
// on load, loop through player config instances, hand to dispatcher
ytAddLoadListener(function() {
  var inst = ytGetInstances();
  for (var i=0; i<inst.length; i++)
  {
    ytPlayerDispatch(inst[i],i);
  }
});

// YouTube API-specific code
var ytplayer = new Array();
function onYouTubePlayerReady(playerId)
{
  ytplayer.push(playerId);
}
function updateButtonState(ytpid)
{
  var ytp = document.getElementById("thisytp"+ytpid);
  if (new RegExp('^(' + ytplayer.join('|') + ')$').test(ytp.id))
  { 
    var mutebut = document.getElementById("ytmutebut"+ ytpid);
    var playbut = document.getElementById("ytplaybut"+ ytpid);
    if (ytp.isMuted()) 
    {
      mutebut.firstChild.nodeValue = "Unmute";
    }
    else 
    {
      mutebut.firstChild.nodeValue = "Mute";
    }
    if (ytp.getPlayerState() == 1)
    {
      playbut.firstChild.nodeValue = "Pause"
    }
    if (ytp.getPlayerState() == 2)
    {
      playbut.firstChild.nodeValue = "Play"
    }
  }
}
function prepTime(s)
{
  var hours=Math.floor(s/3600);
  var minutes=Math.floor(s/60)-(hours*60);
  var seconds=s-(hours*3600)-(minutes*60);
  if (minutes<10) minutes = "0"+minutes;
  if (seconds<10) seconds = "0"+seconds;
  hours = (hours<1) ? "" : hours + ":";
  return hours + minutes +":"+ seconds;
}
function updateTime(ytpid)
{
  var ytp = document.getElementById("thisytp"+ytpid);
  if (new RegExp('^(' + ytplayer.join('|') + ')$').test(ytp.id))
  {
    var timespan = document.getElementById("ytplayertime"+ ytpid);
    var timeval = document.createTextNode(prepTime(Math.round(ytp.getCurrentTime())) +" of "+ prepTime(Math.round(ytp.getDuration())));
    if (timespan.firstChild)
    {
      var temp = timespan.firstChild;
      temp.parentNode.removeChild(temp);
    }
    timespan.appendChild(timeval);
  }
}
var ytLoopInterval = new Array();
function ytloop(ytpid)
{
  var ytp = document.getElementById("thisytp"+ytpid);
  if (new RegExp('^(' + ytplayer.join('|') + ')$').test(ytp.id)) 
  {
  	var loopbut = document.getElementById("ytloopbut"+ytpid);
   	if (loopbut.firstChild.nodeValue == "Loop")
    {
    	loopbut.firstChild.nodeValue = "UnLoop";
      ytLoopInterval[ytpid] = window.setInterval(function() { ytp.playVideo(); }, 2000);
    }
    else
    {
    	loopbut.firstChild.nodeValue = "Loop";
      clearInterval(ytLoopInterval[ytpid]);
    }
  }
}
function ytmute(ytpid)
{
  var ytp = document.getElementById("thisytp"+ytpid);
  if (new RegExp('^(' + ytplayer.join('|') + ')$').test(ytp.id)) 
  {
    if (ytp.isMuted()) 
    {
      ytp.unMute();
    }
    else 
    {
      ytp.mute();
    }
  }
}
function ytvolup(ytpid)
{
  var ytp = document.getElementById("thisytp"+ytpid);
  if (new RegExp('^(' + ytplayer.join('|') + ')$').test(ytp.id)) 
  {
    var vol = ytp.getVolume();
    var nvol = "0";
    if (vol >= 0) 
    {
      nvol = "20"
    }
    if (vol >= 20) 
    {
      nvol = "40"
    }
    if (vol >= 40) 
    {
      nvol = "60"
    }
    if (vol >= 60) 
    {
      nvol = "80"
    }
    if (vol >= 80) 
    {
      nvol = "100"
    }
    ytp.setVolume(nvol);
  }
}
function ytvoldown(ytpid)
{
  var ytp = document.getElementById("thisytp"+ytpid);
  if (new RegExp('^(' + ytplayer.join('|') + ')$').test(ytp.id)) 
  {
    var vol = ytp.getVolume();
    var nvol = "100";
    if (vol <= 100) 
    {
      nvol = "80"
    }
    if (vol <= 80) 
    {
      nvol = "60"
    }
    if (vol <= 60) 
    {
      nvol = "40"
    }
    if (vol <= 40) 
    {
      nvol = "20"
    }
    if (vol <= 20) 
    {
      nvol = "0"
    }
    ytp.setVolume(nvol);
  }
}
function ytplay(ytpid)
{
  var ytp = document.getElementById("thisytp"+ytpid);
  if (new RegExp('^(' + ytplayer.join('|') + ')$').test(ytp.id)) 
  {
    if (ytp.getPlayerState() == "1") {
      ytp.pauseVideo();
    }
    else 
    {
      ytp.playVideo();
    }
  } 
}
function ytstop(ytpid)
{
  var ytp = document.getElementById("thisytp"+ytpid);
  if (new RegExp('^(' + ytplayer.join('|') + ')$').test(ytp.id)) 
  {
    ytp.pauseVideo();
    ytp.seekTo("0");
  }
}
function ytforward(ytpid)
{
  var ytp = document.getElementById("thisytp"+ytpid);
  if (new RegExp('^(' + ytplayer.join('|') + ')$').test(ytp.id)) 
  {
    var dur = ytp.getDuration();
    if (dur > 0) 
    {
      var nt = Math.floor(dur * .2) + ytp.getCurrentTime();
      if (nt < dur) 
      {
        ytp.seekTo(nt);
      }
      else {
        ytp.seekTo(dur);
      }
    }
  }
}
function ytback(ytpid)
{
  var ytp = document.getElementById("thisytp"+ytpid);
  if (new RegExp('^(' + ytplayer.join('|') + ')$').test(ytp.id))
  {
    var dur = ytp.getDuration();
    if (dur > 0) 
    {
      var nt = ytp.getCurrentTime() - Math.floor(dur * .2);
      if (nt > 0) 
      {
        ytp.seekTo(nt);
      }
      else 
      {
        ytp.seekTo(0);
      }
    }
  }
}
function ytLoadNewVideo(ytpid,url,titleval) 
{
  var ytp = document.getElementById("thisytp"+ytpid);
  if (new RegExp('^(' + ytplayer.join('|') + ')$').test(ytp.id))
  {
    ytp.loadVideoById(url, 0);
    var titlenode = document.getElementById("ytvidtitle"+ ytpid);
    if (titlenode.firstChild)
    {
      var temp = titlenode.firstChild;
      temp.parentNode.removeChild(temp);
    }
    var title = document.createTextNode(titleval);
    titlenode.appendChild(title);
  }
}
// Player embedding code. Uses SWFObject library.
// Start with YouTube video default, non-letterbox width of 480x360 (letter is 640x360)
// Add chrome on YouTube player: 24px high
// cc_load_policy param turns captions on by default, since there is no JavaScript equiv. for doing that
// "hd=1" attempts loading high def
// "rel=0" turns off YT search and relative links
// "fs=1" allows for full-screen button (no JavaScript equiv.)
function ytPlayerLoad(ytmovurl,width,pid)
{
  var myytpid = "thisytp"+ pid;
  var myytapiplayer = "ytapiplayer" + pid;
  var ytparams = {
    allowScriptAccess: "always",
    allowFullScreen: "true",
    cc_load_policy: "1",
    hd: "1"
  };
  var ytatts = {
    id: myytpid
  };
  swfobject.embedSWF("http://www.youtube.com/v/" +
  ytmovurl +
  "&enablejsapi=1&playerapiid="+ myytpid +"&hd=1&rel=0&fs=1", myytapiplayer, width, "384", "8", null, null, ytparams, ytatts);
}